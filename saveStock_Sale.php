<?php
// saveStock_Sale.php

require_once __DIR__ . '/conn.php';
@session_start();

if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

$allowedSaleUsers = ['215', '216'];
if (!in_array((string)$_SESSION["iduser"], $allowedSaleUsers, true)) {
	echo "<script>window.location = 'Dasborad.php';</script>";
	exit;
}

date_default_timezone_set('Asia/Bangkok');
$date_time = date('20y-m-d G:i');
mysqli_set_charset(@$conn, "utf8");

// ສົ່ງຂໍ້ຄວາມຜ່ານ URL ແທນ alert() ເພາະ browser block alert ທີ່ຢູ່ກ່ອນປ່ຽນໜ້າ
function redirectWithMessage($msg, $status = 'error')
{
	$url = "DataReAdmin_Sale.php?save_status=" . urlencode($status) . "&save_msg=" . urlencode($msg);
	echo "<meta charset='utf-8' />";
	echo "<script>window.location = " . json_encode($url, JSON_UNESCAPED_UNICODE) . ";</script>";
	exit;
}

// ຮັບຄ່າຮູບແບບ "1,250,000.50" ໄດ້ດ້ວຍ ((float) ຊື່ໆຈະໄດ້ 1)
function toMoney($v)
{
	if ($v === null) return 0.0;
	$v = str_replace([',', ' '], '', trim((string)$v));
	return is_numeric($v) ? (float)$v : 0.0;
}

define('REF_DOC_DIR', __DIR__ . '/uploads/reference_docs/');
define('REF_DOC_URL_PREFIX', 'uploads/reference_docs/');
if (!is_dir(REF_DOC_DIR)) {
	@mkdir(REF_DOC_DIR, 0755, true);
}

// ບັນທຶກແລ້ວໃຫ້ຄົງເປັນ "ກຳລັງດຳເນີນການ" ຈາກນັ້ນແອັດມິນຈຶ່ງດຳເນີນການເບີກຕໍ່
const SALE_SAVED_STATUS = '1';

$allowedExt      = ['pdf', 'jpg', 'jpeg', 'png'];
$maxFileSize     = 5 * 1024 * 1024;
$allowedCurrency = ['LAK', 'THB', 'USD', 'CNY'];
$allowedStockcut = ['Unit', 'Stock_TMD'];
$allowedFinanceOrder = ['ຍັງບໍ່ທັນໂອນ', 'ໂອນສຳເລັດ'];

// ດຶງແຫຼ່ງດຽວກັນກັບ dropdown ໃນ DataReAdmin_Sale.php ບໍ່ດັ່ງນັ້ນຄ່າໃໝ່ຈະຖືກປະຕິເສດ
// ພ້ອມເກັບສະກຸນເງິນ (UNIT_acout) ຂອງແຕ່ລະບັນຊີໄວ້ນຳ ເພື່ອກຳນົດ Currency ຢູ່ຝັ່ງ server ເອງ
$allowedBank    = [];
$bankCurrencyMap = [];
$bankResult = $conn->query("SELECT Name_Accout, MAX(UNIT_acout) AS UNIT_acout FROM tb_acouting_vender WHERE Name_Accout IS NOT NULL AND Name_Accout <> '' GROUP BY Name_Accout");
if ($bankResult) {
	while ($bankRow = $bankResult->fetch_assoc()) {
		$allowedBank[] = $bankRow['Name_Accout'];
		$bankCurrencyMap[$bankRow['Name_Accout']] = strtoupper(trim((string)$bankRow['UNIT_acout']));
	}
}
if (empty($allowedBank)) {
	$allowedBank = ['LDB', 'BCELOne', 'JDB'];
}

// id ທີ່ຮັບໄດ້ ອີງຈາກ tb_sale_status ໂດຍກົງ
$allowedSaleStatus = [];
$ssRes = $conn->query("SELECT name_status FROM tb_sale_status");
if ($ssRes) {
    while ($ssRow = $ssRes->fetch_assoc()) {
        $allowedSaleStatus[] = $ssRow['name_status'];
    }
}

$selected = isset($_POST['selected']) && is_array($_POST['selected']) ? $_POST['selected'] : [];

// ຕົວສຳຮອງ ຕອນກົດປຸ່ມບັນທຶກແຖວດຽວ (ກໍລະນີ checkbox ມາບໍ່ຮອດ server)
$singleId = isset($_POST['single_id']) ? (int)$_POST['single_id'] : 0;
if (empty($selected) && $singleId > 0) {
	$selected = [$singleId];
}
$selected = array_values(array_unique(array_map('intval', $selected)));

if (empty($selected)) {
	redirectWithMessage('ກະລຸນາເລືອກລາຍການທີ່ຕ້ອງການບັນທຶກຢ່າງໜ້ອຍ 1 ລາຍການ', 'error');
}

$PurchaseShopArr = isset($_POST['Purchase_Shop']) && is_array($_POST['Purchase_Shop']) ? $_POST['Purchase_Shop'] : [];
$PriceArr        = isset($_POST['Price']) && is_array($_POST['Price']) ? $_POST['Price'] : [];
$UnitPriceArr    = isset($_POST['Unit_Price']) && is_array($_POST['Unit_Price']) ? $_POST['Unit_Price'] : [];
$FeeArr          = isset($_POST['Fee']) && is_array($_POST['Fee']) ? $_POST['Fee'] : [];
$CurrencyArr     = isset($_POST['Currency']) && is_array($_POST['Currency']) ? $_POST['Currency'] : [];
$BankArr         = isset($_POST['Bank']) && is_array($_POST['Bank']) ? $_POST['Bank'] : [];
$SaleStatusArr = isset($_POST['Sale_Status']) && is_array($_POST['Sale_Status']) ? $_POST['Sale_Status'] : [];
$FinanceOrderArr = isset($_POST['finance_order']) && is_array($_POST['finance_order']) ? $_POST['finance_order'] : [];
$StockcutArr     = isset($_POST['Stockcut']) && is_array($_POST['Stockcut']) ? $_POST['Stockcut'] : [];
$OAtoProArr      = isset($_POST['OAtoPro']) && is_array($_POST['OAtoPro']) ? $_POST['OAtoPro'] : [];
$ItemsArr        = isset($_POST['Items']) && is_array($_POST['Items']) ? $_POST['Items'] : [];

$refDocFiles = isset($_FILES['Reference_Doc']) ? $_FILES['Reference_Doc'] : null;

// ກວດວ່າຂໍ້ມູນຖືກ max_input_vars / post_max_size ຕັດກາງທາງບໍ່
$truncatedIds = [];
foreach ($selected as $sid) {
	if (!isset($PriceArr[$sid]) && !isset($CurrencyArr[$sid]) && !isset($StockcutArr[$sid])) {
		$truncatedIds[] = $sid;
	}
}
if (!empty($truncatedIds)) {
	redirectWithMessage(
		"ຂໍ້ມູນບາງສ່ວນສົ່ງມາບໍ່ຄົບ (ID: " . implode(', ', $truncatedIds) . ")\n"
			. "ສາເຫດມັກມາຈາກ max_input_vars / post_max_size ຂອງ PHP ນ້ອຍເກີນໄປ.\n"
			. "ກະລຸນາລອງເລືອກລາຍການໜ້ອຍລົງຕໍ່ຄັ້ງ ຫຼືແຈ້ງຜູ້ດູແລລະບົບ.",
		'error'
	);
}

// ດ່ານປອດໄພຝັ່ງ server: ກັນການ POST id ນອກ scope ຂອງ user ເຂົ້າມາໂດຍກົງ
$scopeStmt   = null;
$scopeTypes  = '';
$scopeValues = [];
if (file_exists(__DIR__ . '/sale_scope.php')) {
	require_once __DIR__ . '/sale_scope.php';
	if (function_exists('getSaleUserScope') && function_exists('buildSaleScopeSql')) {
		$scope    = getSaleUserScope((string)$_SESSION["iduser"]);
		$scopeSql = buildSaleScopeSql($scope);
		$scopeStmt = $conn->prepare("SELECT a.id FROM request a WHERE a.id = ? AND (" . $scopeSql['sql'] . ")");
		if ($scopeStmt !== false) {
			$scopeTypes  = 'i' . $scopeSql['types'];
			$scopeValues = $scopeSql['values'];
		} else {
			$scopeStmt = null;
		}
	}
}

function isInUserScope($id, $scopeStmt, $scopeTypes, $scopeValues)
{
	if ($scopeStmt === null) return true;

	$params = array_merge([$id], $scopeValues);
	$refs = [$scopeTypes];
	foreach ($params as $k => $v) {
		$refs[] = &$params[$k];
	}
	call_user_func_array([$scopeStmt, 'bind_param'], $refs);
	$scopeStmt->execute();
	$res = $scopeStmt->get_result();
	return ($res && $res->num_rows > 0);
}

$items         = [];
$errors        = [];
$uploadedFiles = [];

foreach ($selected as $id) {
	$id = (int)$id;
	if ($id <= 0) {
		$errors[] = "ID ບໍ່ຖືກຕ້ອງ";
		continue;
	}

	if (!isInUserScope($id, $scopeStmt, $scopeTypes, $scopeValues)) {
		$errors[] = "ລາຍການ #$id: ຢູ່ນອກຂອບເຂດຂໍ້ມູນຂອງທ່ານ (ບໍ່ມີສິດບັນທຶກ)";
		continue;
	}

	$Price = toMoney($PriceArr[$id] ?? null);
	if ($Price <= 0) {
		$errors[] = "ລາຍການ #$id (" . ($ItemsArr[$id] ?? '') . "): ກະລຸນາປ້ອນລາຄາລວມໃຫ້ຖືກຕ້ອງ";
		continue;
	}

	$Bank = isset($BankArr[$id]) ? trim($BankArr[$id]) : '';
	if ($Bank !== '' && !in_array($Bank, $allowedBank, true)) {
		$errors[] = "ລາຍການ #$id: ທະນາຄານທີ່ເລືອກບໍ່ຖືກຕ້ອງ";
		continue;
	}

	// ສະກຸນເງິນອີງໃສ່ UNIT_acout ຂອງບັນຊີເປັນຫຼັກ ຄ່າຈາກຟອມໃຊ້ເປັນຕົວສຳຮອງເທົ່ານັ້ນ
	$Currency = '';
	if ($Bank !== '' && !empty($bankCurrencyMap[$Bank])) {
		$Currency = $bankCurrencyMap[$Bank];
	} elseif (isset($CurrencyArr[$id])) {
		$Currency = strtoupper(trim((string)$CurrencyArr[$id]));
	}

	if ($Currency === '') {
		$errors[] = "ລາຍການ #$id (" . ($ItemsArr[$id] ?? '') . "): ກະລຸນາເລືອກທະນາຄານ/ບັນຊີ ເພື່ອກຳນົດສະກຸນເງິນ";
		continue;
	}
	if (!in_array($Currency, $allowedCurrency, true)) {
		$errors[] = "ລາຍການ #$id: ສະກຸນເງິນ ($Currency) ຂອງບັນຊີນີ້ບໍ່ຢູ່ໃນລາຍຊື່ທີ່ຮອງຮັບ";
		continue;
	}

	$Stockcut = isset($StockcutArr[$id]) ? $StockcutArr[$id] : '';
	if (!in_array($Stockcut, $allowedStockcut, true)) {
		$errors[] = "ລາຍການ #$id: ຂໍ້ມູນສາງທີ່ເລືອກບໍ່ຖືກຕ້ອງ";
		continue;
	}

	$SaleStatus = null;
		if (isset($SaleStatusArr[$id]) && trim((string)$SaleStatusArr[$id]) !== '') {
		$SaleStatus = trim((string)$SaleStatusArr[$id]);
		if (!in_array($SaleStatus, $allowedSaleStatus, true)) {
			$errors[] = "ລາຍການ #$id: ສະຖານະການໂອນທີ່ເລືອກບໍ່ຖືກຕ້ອງ";
			continue;
		}
	}

	$FinanceOrder = null;
	if (isset($FinanceOrderArr[$id]) && trim((string)$FinanceOrderArr[$id]) !== '') {
		$FinanceOrder = trim((string)$FinanceOrderArr[$id]);
		if (!in_array($FinanceOrder, $allowedFinanceOrder, true)) {
			$errors[] = "ລາຍການ #$id: ສະຖານະການໂອນເງິນທີ່ເລືອກບໍ່ຖືກຕ້ອງ";
			continue;
		}
	}

	$UnitPrice    = toMoney($UnitPriceArr[$id] ?? null);
	$Fee          = toMoney($FeeArr[$id] ?? null);
	$PurchaseShop = isset($PurchaseShopArr[$id]) ? trim($PurchaseShopArr[$id]) : '';

	$refDocPath = null;
	if ($refDocFiles && isset($refDocFiles['error'][$id]) && $refDocFiles['error'][$id] === UPLOAD_ERR_OK) {
		$tmpName  = $refDocFiles['tmp_name'][$id];
		$origName = $refDocFiles['name'][$id];
		$size     = $refDocFiles['size'][$id];
		$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

		if (!in_array($ext, $allowedExt, true)) {
			$errors[] = "ລາຍການ #$id: ປະເພດໄຟລ໌ເອກະສານອ້າງອີງບໍ່ຮອງຮັບ (ຮອງຮັບ: " . implode(', ', $allowedExt) . ")";
			continue;
		}
		if ($size > $maxFileSize) {
			$errors[] = "ລາຍການ #$id: ໄຟລ໌ເອກະສານອ້າງອີງໃຫຍ່ເກີນໄປ (ສູງສຸດ 5MB)";
			continue;
		}

		$newFileName = 'ref_' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
		$destPath    = REF_DOC_DIR . $newFileName;

		if (!move_uploaded_file($tmpName, $destPath)) {
			$errors[] = "ລາຍການ #$id: ອັບໂຫລດໄຟລ໌ເອກະສານອ້າງອີງບໍ່ສຳເລັດ";
			continue;
		}
		$refDocPath      = REF_DOC_URL_PREFIX . $newFileName;
		$uploadedFiles[] = $destPath;
	} elseif ($refDocFiles && isset($refDocFiles['error'][$id]) && $refDocFiles['error'][$id] !== UPLOAD_ERR_NO_FILE) {
		$errors[] = "ລາຍການ #$id: ອັບໂຫລດໄຟລ໌ເອກະສານອ້າງອີງບໍ່ສຳເລັດ (ລະຫັດ error: " . $refDocFiles['error'][$id] . ")";
		continue;
	}

	$items[] = [
		'id'            => $id,
		'Price'         => $Price,
		'Unit_Price'    => $UnitPrice,
		'Fee'           => $Fee,
		'Currency'      => $Currency,
		'Bank'          => $Bank,
		'Sale_Status'   => $SaleStatus,
		'Finance_Order' => $FinanceOrder,
		'Purchase_Shop' => $PurchaseShop,
		'Reference_Doc' => $refDocPath,
		'Stockcut'      => $Stockcut,
		'OAtoPro'       => $OAtoProArr[$id] ?? '',
	];
}

if (!empty($errors)) {
	foreach ($uploadedFiles as $f) {
		@unlink($f);
	}
	redirectWithMessage(implode("\n", $errors), 'error');
}

if (empty($items)) {
	redirectWithMessage('ບໍ່ມີລາຍການທີ່ຜ່ານການກວດສອບ', 'error');
}

// ປະມວນຜົນແຍກແຕ່ລະລາຍການ ລາຍການທີ່ຜິດຈະບໍ່ກະທົບລາຍການທີ່ຖືກຕ້ອງ
$savedItems  = [];
$failedItems = [];
$savedStatus = SALE_SAVED_STATUS;

$sqlUpdate = "UPDATE request SET
		Purchase_Shop = ?,
		Price         = ?,
		Unit_Price    = ?,
		Fee           = ?,
		Currency      = ?,
		Bank          = ?,
		Sale_Status   = ?,
		finance_onder = COALESCE(?, finance_onder),
		Reference_Doc = COALESCE(?, Reference_Doc),
		Stockcut      = ?,
		OA_topro      = ?,
		S_Status      = ?,
		User_Confirm  = ?,
		DateComfirm   = ?
	WHERE id = ? AND (S_Status IS NULL OR S_Status <> '3')";

foreach ($items as $it) {
	$id = $it['id'];

	$stmtReq = $conn->prepare($sqlUpdate);
	if ($stmtReq === false) {
		$failedItems[] = "ລາຍການ #$id: ຕຽມ query ບໍ່ສຳເລັດ - " . $conn->error;
		if (!empty($it['Reference_Doc'])) @unlink(__DIR__ . '/' . $it['Reference_Doc']);
		continue;
	}

	$stmtReq->bind_param(
		"sdddssssssssssi",
		$it['Purchase_Shop'],
		$it['Price'],
		$it['Unit_Price'],
		$it['Fee'],
		$it['Currency'],
		$it['Bank'],
		$it['Sale_Status'],
		$it['Finance_Order'],
		$it['Reference_Doc'],
		$it['Stockcut'],
		$it['OAtoPro'],
		$savedStatus,
		$_SESSION["user"],
		$date_time,
		$id
	);

	if (!$stmtReq->execute()) {
		$failedItems[] = "ລາຍການ #$id: ບັນທຶກບໍ່ສຳເລັດ - " . $stmtReq->error;
		$stmtReq->close();
		if (!empty($it['Reference_Doc'])) @unlink(__DIR__ . '/' . $it['Reference_Doc']);
		continue;
	}

	$reqAffected = $stmtReq->affected_rows;
	$stmtReq->close();

	if ($reqAffected > 0) {
		$savedItems[] = $id;
		continue;
	}

	// affected_rows = 0 ໄດ້ 3 ກໍລະນີ: ບໍ່ພົບແຖວ / ຖືກຍົກເລີກແລ້ວ / ຄ່າບໍ່ປ່ຽນ
	$curStatus = null;
	$rowExists = false;

	$stmtChk = $conn->prepare("SELECT S_Status FROM request WHERE id = ?");
	if ($stmtChk !== false) {
		$stmtChk->bind_param("i", $id);
		$stmtChk->execute();
		$resChk = $stmtChk->get_result();
		if ($resChk && ($r = $resChk->fetch_assoc())) {
			$rowExists = true;
			$curStatus = (string)$r['S_Status'];
		}
		$stmtChk->close();
	}

	$itemLabel = ($ItemsArr[$id] ?? '');
	$label     = "ລາຍການ #$id" . ($itemLabel !== '' ? " ($itemLabel)" : '');

	if (!$rowExists) {
		$failedItems[] = "$label: ບໍ່ພົບລາຍການນີ້ໃນຖານຂໍ້ມູນ (ອາດຖືກລຶບໄປແລ້ວ)";
		if (!empty($it['Reference_Doc'])) @unlink(__DIR__ . '/' . $it['Reference_Doc']);
		continue;
	}

	if ($curStatus === '3') {
		$failedItems[] = "$label: ຖືກຍົກເລີກໄປແລ້ວກ່ອນໜ້ານີ້ (ບັນທຶກທັບບໍ່ໄດ້)";
		if (!empty($it['Reference_Doc'])) @unlink(__DIR__ . '/' . $it['Reference_Doc']);
		continue;
	}

	$savedItems[] = $id;
}

$savedCount  = count($savedItems);
$failedCount = count($failedItems);

if ($failedCount === 0) {
	redirectWithMessage("ບັນທຶກລາຍການສຳເລັດ ທັງໝົດ $savedCount ລາຍການ", 'success');
}

$summaryLines = [];
if ($savedCount > 0) {
	$summaryLines[] = "ບັນທຶກສຳເລັດ $savedCount ລາຍການ";
}
$summaryLines[] = "ບັນທຶກລົ້ມເຫລວ $failedCount ລາຍການ:";
foreach ($failedItems as $f) {
	$summaryLines[] = "- $f";
}
redirectWithMessage(implode("\n", $summaryLines), $savedCount > 0 ? 'partial' : 'error');