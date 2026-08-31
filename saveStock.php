<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<meta charset="utf-8" />

<!-- Core theme CSS (includes Bootstrap)-->
<link href="css/styles.css" rel="stylesheet" />
<style type="text/css">
	body,
	td,
	th {
		font-family: "Phetsarath OT";
	}
</style>
<?php

date_default_timezone_set('Asia/Bangkok');
$date_time = date('20y-m-d G:i');

mysqli_set_charset(@$conn, "utf8");

/*
 * ອັບເດດ: ຮອງຮັບການເບິກຫຼາຍລາຍການພ້ອມກັນ.
 * DataReAdmin.php ຕອນນີ້ສົ່ງ:
 *   - selected[]        : array ຂອງ id ທີ່ຖືກເລືອກ (checkbox)
 *   - give[id]           : ຈຳນວນທີ່ຈະເບິກ ຂອງແຕ່ລະລາຍການ
 *   - Stockcut[id]       : 'Unit' ຫຼື 'Stock_TMD' (ເລືອກສາງໃດຫັກ)
 *   - Province[id], Section[id], Vendor[id], Item_code[id], Use_For[id],
 *     Type[id], Groupp[id], picture[id], Unitcenter[id], Stock_TMD[id],
 *     Unit[id], Items[id], OAtoPro[id]  : ຄ່າອື່ນໆຂອງແຕ່ລະລາຍການ
 *
 * ທັງໝົດຖືກປະມວນຜົນຢູ່ໃນ transaction ດຽວ: ຖ້າລາຍການໃດລາຍການໜຶ່ງຜິດພາດ
 * (ຈຳນວນບໍ່ພຽງພໍ, id ບໍ່ຖືກຕ້ອງ, ...) ຈະ rollback ທັງໝົດ ບໍ່ໃຫ້ຄ້າງເຄິ່ງໆກາງໆ.
 */

$selected = isset($_POST['selected']) && is_array($_POST['selected']) ? $_POST['selected'] : [];

if (empty($selected)) {
	echo "<script>alert('ກະລຸນາເລືອກລາຍການທີ່ຕ້ອງການເບິກຢ່າງໜ້ອຍ 1 ລາຍການ'); window.location = 'DataReAdmin.php';</script>";
	exit;
}

$onlyAllowedColumns = array('Unit', 'Stock_TMD');

$giveArr      = isset($_POST['give']) && is_array($_POST['give']) ? $_POST['give'] : [];
$StockcutArr  = isset($_POST['Stockcut']) && is_array($_POST['Stockcut']) ? $_POST['Stockcut'] : [];
$ProvinceArr  = isset($_POST['Province']) && is_array($_POST['Province']) ? $_POST['Province'] : [];
$SectionArr   = isset($_POST['Section']) && is_array($_POST['Section']) ? $_POST['Section'] : [];
$VendorArr    = isset($_POST['Vendor']) && is_array($_POST['Vendor']) ? $_POST['Vendor'] : [];
$ItemCodeArr  = isset($_POST['Item_code']) && is_array($_POST['Item_code']) ? $_POST['Item_code'] : [];
$UseForArr    = isset($_POST['Use_For']) && is_array($_POST['Use_For']) ? $_POST['Use_For'] : [];
$TypeArr      = isset($_POST['Type']) && is_array($_POST['Type']) ? $_POST['Type'] : [];
$GrouppArr    = isset($_POST['Groupp']) && is_array($_POST['Groupp']) ? $_POST['Groupp'] : [];
$PictureArr   = isset($_POST['picture']) && is_array($_POST['picture']) ? $_POST['picture'] : [];
$UnitcenterArr = isset($_POST['Unitcenter']) && is_array($_POST['Unitcenter']) ? $_POST['Unitcenter'] : [];
$StockTmdArr  = isset($_POST['Stock_TMD']) && is_array($_POST['Stock_TMD']) ? $_POST['Stock_TMD'] : [];
$UnitArr      = isset($_POST['Unit']) && is_array($_POST['Unit']) ? $_POST['Unit'] : [];
$ItemsArr     = isset($_POST['Items']) && is_array($_POST['Items']) ? $_POST['Items'] : [];
$OAtoProArr   = isset($_POST['OAtoPro']) && is_array($_POST['OAtoPro']) ? $_POST['OAtoPro'] : [];

// ---- ກວດ + ຮວບຮວມຂໍ້ມູນແຕ່ລະລາຍການ ກ່ອນແຕະຖານຂໍ້ມູນຫຍັງທັງນັ້ນ ----
$items = [];       // ລາຍການທີ່ຜ່ານການກວດແລ້ວ, ພ້ອມປະມວນຜົນ
$errors = [];       // ຂໍ້ຄວາມ error ຂອງແຕ່ລະລາຍການທີ່ບໍ່ຜ່ານ

foreach ($selected as $id) {
	$id = (int)$id;
	if ($id <= 0) {
		$errors[] = "ID ບໍ່ຖືກຕ້ອງ";
		continue;
	}

	$Stockcut = isset($StockcutArr[$id]) ? $StockcutArr[$id] : '';
	if (!in_array($Stockcut, $onlyAllowedColumns, true)) {
		$errors[] = "ລາຍການ #$id: ຂໍ້ມູນສາງທີ່ເລືອກບໍ່ຖືກຕ້ອງ";
		continue;
	}

	$give = isset($giveArr[$id]) ? (float)$giveArr[$id] : 0;
	if ($give <= 0) {
		$errors[] = "ລາຍການ #$id: ກະລຸນາປ້ອນຈຳນວນທີ່ຈະເບິກໃຫ້ຖືກຕ້ອງ";
		continue;
	}

	// ຄ່າ Unit[id] / Stock_TMD[id] ຄືຈຳນວນທີ່ມີໃນສາງກາງ ຕອນໂຫລດໜ້າ (hidden field)
	$rawAvail = $Stockcut === 'Unit' ? ($UnitArr[$id] ?? 0) : ($StockTmdArr[$id] ?? 0);
	$availableQty = (float)$rawAvail;

	if ($availableQty < $give) {
		$whereName = $Stockcut === 'Unit' ? 'ສາງອຸປະກອນສຳນັກງານໃຫຍ່' : 'ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່';
		$errors[] = "ລາຍການ #$id (" . ($ItemsArr[$id] ?? '') . "): ຈຳນວນໃນ '$whereName' ມີພຽງ $availableQty ອັນ ບໍ່ພຽງພໍກັບຈຳນວນທີ່ຈະເບິກ ($give ອັນ)";
		continue;
	}

	$items[] = [
		'id'        => $id,
		'give'      => $give,
		'Stockcut'  => $Stockcut,
		'Items'     => $ItemsArr[$id] ?? '',
		'Province'  => $ProvinceArr[$id] ?? '',
		'Section'   => $SectionArr[$id] ?? '',
		'Vendor'    => $VendorArr[$id] ?? '',
		'Item_code' => $ItemCodeArr[$id] ?? '',
		'Use_For'   => $UseForArr[$id] ?? '',
		'Type'      => $TypeArr[$id] ?? '',
		'Groupp'    => $GrouppArr[$id] ?? '',
		'picture'   => $PictureArr[$id] ?? '',
		'OAtoPro'   => $OAtoProArr[$id] ?? '',
	];
}

if (!empty($errors)) {
	$msg = implode("\\n", $errors);
	echo "<script>alert(" . json_encode($msg, JSON_UNESCAPED_UNICODE) . "); window.location = 'DataReAdmin.php';</script>";
	exit;
}

if (empty($items)) {
	echo "<script>alert('ບໍ່ມີລາຍການທີ່ຜ່ານການກວດສອບ'); window.location = 'DataReAdmin.php';</script>";
	exit;
}

// ---- ປະມວນຜົນທັງໝົດຢູ່ໃນ transaction ດຽວ: all-or-nothing ----
$conn->begin_transaction();

try {
	foreach ($items as $it) {
		$id        = $it['id'];
		$give      = $it['give'];
		$Stockcut  = $it['Stockcut']; // 'Unit' ຫຼື 'Stock_TMD' -> whitelisted ແລ້ວດ້ານເທິງ
		$Items     = $it['Items'];
		$Province  = $it['Province'];
		$OAtoPro   = $it['OAtoPro'];

		// 1) ອັບເດດ request: ໝາຍວ່າເບິກແລ້ວ
		$stmtReq = $conn->prepare(
			"UPDATE request SET Give = ?, DateComfirm = ?, OA_topro = ?, User_Confirm = ?, S_Status = '2' WHERE id = ? AND S_Status = '1'"
		);
		if ($stmtReq === false) {
			throw new Exception("ຕຽມ query request ບໍ່ສຳເລັດ (ID: $id)");
		}
		$stmtReq->bind_param("ssssi", $give, $date_time, $OAtoPro, $_SESSION["user"], $id);
		$stmtReq->execute();
		$reqAffected = $stmtReq->affected_rows;
		$stmtReq->close();

		if ($reqAffected === 0) {
			// id ບໍ່ພົບ ຫຼືອາດຖືກດຳເນີນການ (ຫຼືຍົກເລິກ) ໄປແລ້ວ ໂດຍຄົນອື່ນ/ແທັບອື່ນກ່ອນໜ້ານີ້
			throw new Exception("ບໍ່ພົບລາຍການ ID: $id ຫຼືອາດຖືກດຳເນີນການໄປແລ້ວ");
		}

		// 2) ຫັກຈຳນວນອອກຈາກສາງກາງ (stock) — $Stockcut ຜ່ານ whitelist ແລ້ວ ປອດໄພຈາກ injection
		$stmtCut = $conn->prepare("UPDATE stock SET `$Stockcut` = `$Stockcut` - ? WHERE Items = ?");
		if ($stmtCut === false) {
			throw new Exception("ຕຽມ query stock ບໍ່ສຳເລັດ (ID: $id)");
		}
		$stmtCut->bind_param("ds", $give, $Items);
		$stmtCut->execute();
		$stmtCut->close();

		// 3) stock_province: ແກ້ໄຂ bug ເກົ່າ (ເມື່ອກ່ອນມີແຕ່ SELECT ບໍ່ໄດ້ update/insert ຫຍັງເລີຍ)
		//    ເມື່ອສາງກາງ (ສຳນັກງານໃຫຍ່) ອະນຸມັດເບິກອອກໃຫ້ແຂວງ, ຈຳນວນສາງຂອງແຂວງນັ້ນຄວນຖືກ +ເພີ່ມ
		//    ຫມາຍເຫດ: ຖ້າ business logic ຂອງທ່ານແຕກຕ່າງຈາກນີ້ (ຕົວຢ່າງ: ຄວນຫັກແທນທີ່ຈະເພີ່ມ)
		//    ກະລຸນາແຈ້ງໃຫ້ປັບ query ນີ້ໃໝ່.
		$stmtCheck = $conn->prepare("SELECT id FROM stock_province WHERE Items = ? AND Provinces = ? FOR UPDATE");
		if ($stmtCheck === false) {
			throw new Exception("ຕຽມ query stock_province (check) ບໍ່ສຳເລັດ (ID: $id)");
		}
		$stmtCheck->bind_param("ss", $Items, $Province);
		$stmtCheck->execute();
		$checkResult = $stmtCheck->get_result();
		$existingRow = $checkResult->fetch_assoc();
		$stmtCheck->close();

		if ($existingRow) {
			$stmtUp = $conn->prepare("UPDATE stock_province SET Unit = Unit + ? WHERE Items = ? AND Provinces = ?");
			if ($stmtUp === false) {
				throw new Exception("ຕຽມ query stock_province (update) ບໍ່ສຳເລັດ (ID: $id)");
			}
			$stmtUp->bind_param("dss", $give, $Items, $Province);
			$stmtUp->execute();
			$stmtUp->close();
		} else {
			$stmtIns = $conn->prepare("INSERT INTO stock_province (Items, Provinces, Unit) VALUES (?, ?, ?)");
			if ($stmtIns === false) {
				throw new Exception("ຕຽມ query stock_province (insert) ບໍ່ສຳເລັດ (ID: $id)");
			}
			$stmtIns->bind_param("ssd", $Items, $Province, $give);
			$stmtIns->execute();
			$stmtIns->close();
		}
	}

	$conn->commit();

	$count = count($items);
	echo "<script>
		alert('ເບິກລາຍການສຳເລັດ ທັງໝົດ $count ລາຍການ');
		window.location = \"DataReAdmin.php\";
	</script>";
	exit;
} catch (Exception $e) {
	$conn->rollback();
	$msg = "ບໍ່ສາມາດເບິກໄດ້, ບໍ່ມີການປ່ຽນແປງໃດໆຖືກບັນທຶກ. ສາເຫດ: " . $e->getMessage();
	echo "<script>alert(" . json_encode($msg, JSON_UNESCAPED_UNICODE) . "); window.location = 'DataReAdmin.php';</script>";
	exit;
}