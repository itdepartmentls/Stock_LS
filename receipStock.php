<?php
require_once __DIR__ . '/conn.php';
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
$idd       = isset($_POST["id"]) ? $_POST["id"] : '';
$S_Status  = isset($_POST["S_Status"]) ? $_POST["S_Status"] : '';
$Items     = isset($_POST["Items"]) ? $_POST["Items"] : '';
$Item_code = isset($_POST["Item_code"]) ? $_POST["Item_code"] : '';
$Vendor    = isset($_POST["Vendor"]) ? $_POST["Vendor"] : '';
$Use_For   = isset($_POST["Use_For"]) ? $_POST["Use_For"] : '';
$Type      = isset($_POST["Type"]) ? $_POST["Type"] : '';
$give      = isset($_POST["give"]) ? $_POST["give"] : '';
$Groupp    = isset($_POST["Groupp"]) ? $_POST["Groupp"] : '';
$picture   = isset($_POST["picture"]) ? $_POST["picture"] : '';
$Section   = isset($_POST["Section"]) ? $_POST["Section"] : '';
$Province  = isset($_POST["Province"]) ? $_POST["Province"] : '';
$OA        = isset($_POST["OA"]) ? $_POST["OA"] : '';
$planing_number  = isset($_POST["planing_number"]) ? $_POST["planing_number"] : '';

date_default_timezone_set('Asia/Bangkok');
$date_time = date('20y-m-d G:i');

$hubValue = $_SESSION["user"] . " " . $date_time;

// ---- ອັບເດດ request ດ້ວຍ prepared statement ----
$stmt44 = $conn->prepare(
	"UPDATE request SET hub = ?, S_Status = '4' WHERE id = ? AND S_Status = ?"
);
if ($stmt44 === false) {
	echo "<script>alert('ຕຽມ query ບໍ່ສຳເລັດ'); window.location = 'DataRe.php';</script>";
	exit;
}
$stmt44->bind_param("sss", $hubValue, $idd, $S_Status);
$stmt44->execute();
$affected = $stmt44->affected_rows;
$stmt44->close();

// ---- ແກ້ໄຂຫຼັກ: ຖ້າບໍ່ມີແຖວຖືກອັບເດດ ໃຫ້ແຈ້ງເຕືອນຈິງ ແທນທີ່ຈະບອກວ່າ "ສຳເລັດ" ແບບຫລອກ ----
if ($affected === 0) {
	echo "<script>alert('ບໍ່ສາມາດຮັບລາຍການໄດ້: ອາດຈະຖືກຮັບໄປແລ້ວ ຫຼືສະຖານະປ່ຽນໄປແລ້ວ (ID: " . htmlspecialchars($idd, ENT_QUOTES) . ")'); window.location = 'DataRe.php';</script>";
	exit;
}

// ---- ອັບເດດ / ເພີ່ມ stock_province ----
$stmtCheck = $conn->prepare("SELECT * FROM stock_province WHERE Items = ? AND Provinces = ?");
$stmtCheck->bind_param("ss", $Items, $Province);
$stmtCheck->execute();
$checkResult = $stmtCheck->get_result();

if ($checkResult->num_rows > 0) {
	$stmt1 = $conn->prepare(
		"UPDATE stock_province SET Unit = Unit + ?, User_Stock = ?, Date_Pro = ?, planing_number = ? WHERE Items = ? AND Provinces = ?"
	);
	$stmt1->bind_param("ssssss", $give, $_SESSION["user"], $date_time, $planing_number, $Items, $Province);
	$stmt1->execute();
	$stmt1->close();

	$stmt1123 = $conn->prepare(
		"INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,hub,OA,planing_number)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
	);
	$stmt1123->bind_param(
		"sssssssssssssss",
		$Items, $Item_code, $Vendor, $Use_For, $Type, $give, $Groupp, $picture,
		$Section, $_SESSION["user"], $Province, $date_time, $hubValue, $OA, $planing_number
	);
	$stmt1123->execute();
	$stmt1123->close();

	echo "Record updated successfully";
} else {
	$stmt11 = $conn->prepare(
		"INSERT INTO stock_province (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,hub,planing_number)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
	);
	$stmt11->bind_param(
		"ssssssssssssss",
		$Items,
		$Item_code,
		$Vendor,
		$Use_For,
		$Type,
		$give,
		$Groupp,
		$picture,
		$Section,
		$_SESSION["user"],
		$Province,
		$date_time,
		$hubValue,
		$planing_number
	);
	$stmt11->execute();
	$stmt11->close();

	$stmt112 = $conn->prepare(
		"INSERT INTO stock_provinceinput (Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Stock,Provinces,Date_Pro,hub,OA,planing_number)
		 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
	);
	$stmt112->bind_param(
		"sssssssssssssss",
		$Items,
		$Item_code,
		$Vendor,
		$Use_For,
		$Type,
		$give,
		$Groupp,
		$picture,
		$Section,
		$_SESSION["user"],
		$Province,
		$date_time,
		$hubValue,
		$OA,
		$planing_number
	);
	$stmt112->execute();
	$stmt112->close();

	echo "New record created successfully";
}
$stmtCheck->close();
?>

<script>
	window.location = "DataRe.php";
</script>