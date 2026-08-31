<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

date_default_timezone_set('Asia/Bangkok');
$date_time = date('Y-m-d H:i');

// ===============================================================
// ຮັບ ແລະ ກັດຄ່າ Input ພື້ນຖານ
// ໝາຍເຫດ: ບໍ່ຮັບ OA ຈາກຟອມອີກຕໍ່ໄປ — server ຄິດໄລ່ PR ທີ່ຈະຕັດເອງ ເພື່ອຄວາມປອດໄພ
// ===============================================================
$Items          = $_POST["Items"] ?? '';
$Province       = $_POST["Province"] ?? '';
$Unit           = $_POST["Unit"] ?? '';
$Type           = $_POST["Type"] ?? '';
$Station        = $_POST["Station"] ?? '';
$serieNumber    = $_POST["serieNumber"] ?? '';
$ticket         = $_POST["ticket"] ?? '';
$Remark         = $_POST["Remark"] ?? '';
$Section        = $_POST["Section"] ?? '';
$Use_Name       = $_POST["Use_Name"] ?? '';
$planing_number = $_POST["planing_number"] ?? '';
$userSession    = $_SESSION["user"];

$errorMsg = '';
$requestedUnit = 0;

// ===============================================================
// Validate ພື້ນຖານ
// ===============================================================
if ($Items === '' || $Province === '') {
	$errorMsg = 'ຂໍ້ມູນອຸປະກອນ ຫຼື ແຂວງບໍ່ຄົບຖ້ວນ';
} elseif (!ctype_digit((string)$Unit) || (int)$Unit <= 0) {
	$errorMsg = 'ຈຳນວນນຳໃຊ້ບໍ່ຖືກຕ້ອງ';
} else {
	$requestedUnit = (int)$Unit;
}

// ===============================================================
// [AUTO-SPLIT] ດຶງ PR ທຸກໃບທີ່ Status = 4 ແລະ ຍັງເຫຼືອຈຳນວນ, ຈັດລຽງ FIFO (dateRe ASC)
// ຄິດໄລ່ໃໝ່ຢູ່ນີ້ (ບໍ່ເຊື່ອຄ່າຈາກຟອມ) ເພື່ອປ້ອງກັນ race condition / ການແກ້ໄຂ HTML
// ===============================================================
$plan = [];          // ແຜນການຕັດ: [ ['OA_Out'=>.., 'take'=>..], ... ]
$totalAvailable = 0;

if ($errorMsg === '') {
	$stmt_oa = $conn->prepare("
		SELECT req.OA_Out,
		       MIN(req.dateRe) AS dateRe,
		       (req.totalRequested - COALESCE(usg.totalUsed, 0)) AS remaining
		FROM (
			SELECT OA_Out, Items, Province, dateRe,
			       SUM(CAST(Unit AS UNSIGNED)) AS totalRequested
			FROM request
			WHERE Items LIKE ? AND Province LIKE ? AND S_Status = '4'
			GROUP BY OA_Out, Items, Province
		) req
		LEFT JOIN (
			SELECT OA, Items, Provinces, SUM(CAST(Unit AS UNSIGNED)) AS totalUsed
			FROM usestock
			GROUP BY OA, Items, Provinces
		) usg ON usg.OA = req.OA_Out AND usg.Items = req.Items AND usg.Provinces = req.Province
		GROUP BY req.OA_Out
		HAVING remaining > 0
		ORDER BY dateRe ASC
	");
	$stmt_oa->bind_param("ss", $Items, $Province);
	$stmt_oa->execute();
	$resultOa = $stmt_oa->get_result();

	$remainingNeeded = $requestedUnit;
	while (($oaRow = $resultOa->fetch_assoc()) && $remainingNeeded > 0) {
		$avail = (int)$oaRow['remaining'];
		$totalAvailable += $avail;
		$take = min($avail, $remainingNeeded);
		if ($take > 0) {
			$plan[] = ['OA_Out' => $oaRow['OA_Out'], 'take' => $take];
			$remainingNeeded -= $take;
		}
	}
	// ດຶງຕໍ່ (ບໍ່ break) ເພື່ອນັບ totalAvailable ໃຫ້ຄົບ ສຳລັບຂໍ້ຄວາມ error ທີ່ຖືກຕ້ອງ
	while ($oaRow = $resultOa->fetch_assoc()) {
		$totalAvailable += (int)$oaRow['remaining'];
	}
	$stmt_oa->close();

	if (empty($plan)) {
		$errorMsg = 'ບໍ່ພົບ PR (ສະຖານະ "ສຳເລັດ") ທີ່ຍັງເຫຼືອຈຳນວນ ສຳລັບອຸປະກອນນີ້';
	} elseif ($remainingNeeded > 0) {
		$errorMsg = 'ຈຳນວນທີ່ຂໍນຳໃຊ້ (' . $requestedUnit . ') ເກີນຍອດເຫຼືອລວມຂອງ PR ທຸກໃບ (ລວມເຫຼືອ = ' . $totalAvailable . ')';
	}
}

// ===============================================================
// ຖ້າຜ່ານທຸກການກວດສອບ → ບັນທຶກ (ອາດຫຼາຍແຖວ, 1 ແຖວ/1 PR) ດ້ວຍ Transaction
// planing_number ຖືກບັນທຶກລົງທຸກແຖວທີ່ split ອອກມາ (ຄ່າດຽວກັນ ເພາະມາຈາກຟອມດຽວ)
// ===============================================================
if ($errorMsg === '') {
	$conn->begin_transaction();
	try {
		$stmt_ins = $conn->prepare("
			INSERT INTO usestock
				(Items, Date, Provinces, Unit, Type, Station, Series_Number, OA, Ticket, Remark, User, Section, Use_Name, planing_number)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
		");

		foreach ($plan as $p) {
			$unitStr = (string)$p['take'];
			$stmt_ins->bind_param(
				"ssssssssssssss",
				$Items,
				$date_time,
				$Province,
				$unitStr,
				$Type,
				$Station,
				$serieNumber,
				$p['OA_Out'],
				$ticket,
				$Remark,
				$userSession,
				$Section,
				$Use_Name,
				$planing_number
			);
			if (!$stmt_ins->execute()) {
				throw new Exception('Insert usestock ລົ້ມເຫລວ (PR ' . $p['OA_Out'] . '): ' . $stmt_ins->error);
			}
		}
		$stmt_ins->close();

		$stmt_upd = $conn->prepare("
			UPDATE stock_province
			SET Unit = Unit - ?
			WHERE Items LIKE ? AND Provinces LIKE ?
		");
		$totalUnitStr = (string)$requestedUnit;
		$stmt_upd->bind_param("sss", $totalUnitStr, $Items, $Province);
		if (!$stmt_upd->execute()) {
			throw new Exception('Update stock_province ລົ້ມເຫລວ: ' . $stmt_upd->error);
		}
		$stmt_upd->close();

		$conn->commit();
	} catch (Exception $e) {
		$conn->rollback();
		$errorMsg = 'ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກ: ' . $e->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="lo">

<head>
	<meta charset="utf-8" />
	<title>ຕັດ Stock</title>
</head>

<body>
	<?php if ($errorMsg !== ''): ?>
		<script>
			alert(<?= json_encode($errorMsg) ?>);
			window.location = "UseStock.php";
		</script>
	<?php else: ?>
		<script>
			alert('ຕັດ Stock ສຳເລັດແລ້ວ');
			window.location = "UseStock.php";
		</script>
	<?php endif; ?>
</body>

</html>