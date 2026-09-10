<?php
// DataReAdmin_Sale.php

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

// ແກ້ scope ຂອງແຕ່ລະ user ຢູ່ sale_scope.php ບ່ອນດຽວ (sidebar.php ໃຊ້ຮ່ວມກັນ)
require_once __DIR__ . '/sale_scope.php';

$currentUserId = (string)$_SESSION["iduser"];
$scope = getSaleUserScope($currentUserId);

mysqli_set_charset(@$conn, "utf8");

if (isset($_POST["buttonCancel"])) {
	$cancelDateTime = date('20y-m-d G:i');
	$cancelId       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$cancelIssue    = isset($_POST['Issue']) ? trim($_POST['Issue']) : '';

	if ($cancelId > 0) {
		$stmtCancel = $conn->prepare(
			"UPDATE request SET Remark = ?, User_Confirm = ?, S_Status = '3', DateComfirm = ? WHERE id = ?"
		);
		if ($stmtCancel !== false) {
			$stmtCancel->bind_param("sssi", $cancelIssue, $_SESSION["user"], $cancelDateTime, $cancelId);
			$stmtCancel->execute();
			$stmtCancel->close();
		}
	}
	echo "<script>window.location = 'DataReAdmin_Sale.php';</script>";
	exit;
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'zh';

function h($v)
{
	return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

// ====== ຮູບພາບອຸປະກອນ: ດຶງຈາກ folder /xampp/picture/uploads, ຊື່ໄຟລ໌ = Item_code ======
// ໝາຍເຫດ: ຖ້າ path ຈິງໃນເຄື່ອງ server ຂອງທ່ານບໍ່ແມ່ນ "C:\xampp\picture\uploads"
// (ເຊັ່ນ: ຖ້າຮູບຢູ່ໃນ htdocs/picture/uploads ແທນ) ໃຫ້ແກ້ $PICTURE_DISK_BASE ຢູ່ລຸ່ມນີ້
$PICTURE_URL_BASE  = '/xampp/picture/uploads/';
$PICTURE_DISK_BASE = 'C:/xampp/picture/uploads/';
$PICTURE_EXTS      = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'JPG', 'JPEG', 'PNG'];

function resolveItemPicture($itemCode, $diskBase, $urlBase, $exts)
{
	$itemCode = trim((string)$itemCode);
	if ($itemCode === '') return '';
	// ກັນຊື່ໄຟລ໌ບໍ່ໃຫ້ມີອັກຂະລະອັນຕະລາຍ / path traversal
	$safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $itemCode);
	foreach ($exts as $ext) {
		if (is_file($diskBase . $safeName . '.' . $ext)) {
			return $urlBase . rawurlencode($safeName . '.' . $ext);
		}
	}
	return '';
}

function isValidDateStr($d)
{
	if ($d === '') return false;
	$dt = DateTime::createFromFormat('Y-m-d', $d);
	return $dt && $dt->format('Y-m-d') === $d;
}

function buildFilterUrl(array $removeKeys = [])
{
	$params = $_GET;
	foreach ($removeKeys as $k) {
		unset($params[$k]);
	}
	unset($params['save_status'], $params['save_msg']);
	$qs = http_build_query($params);
	return $qs !== '' ? '?' . $qs : '?';
}

function fmtMoneyForInput($val)
{
	if ($val === null || $val === '') return '';
	$val = (float)$val;
	if ($val == 0) return '';
	$formatted = number_format($val, 2, '.', ',');
	if (strpos($formatted, '.') !== false) {
		$formatted = rtrim(rtrim($formatted, '0'), '.');
	}
	return $formatted;
}

$provinceList     = $scope['provinces'];
$selectedProvince = isset($_GET['province']) ? trim($_GET['province']) : '';
if ($selectedProvince !== '' && !in_array($selectedProvince, $provinceList, true)) {
	$selectedProvince = '';
}

$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo   = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$searchNo = isset($_GET['search_no']) ? trim($_GET['search_no']) : '';

if ($dateFrom !== '' && !isValidDateStr($dateFrom)) $dateFrom = '';
if ($dateTo !== '' && !isValidDateStr($dateTo)) $dateTo = '';
if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
	$tmp = $dateFrom;
	$dateFrom = $dateTo;
	$dateTo = $tmp;
}

$bankList = [];
$bankResult = $conn->query("SELECT Name_Accout, MAX(UNIT_acout) AS UNIT_acout FROM tb_acouting_vender WHERE Name_Accout IS NOT NULL AND Name_Accout <> '' GROUP BY Name_Accout ORDER BY Name_Accout ASC");
if ($bankResult) {
	while ($bankRow = $bankResult->fetch_assoc()) {
		$bankList[] = ['name' => $bankRow['Name_Accout'], 'currency' => strtoupper(trim((string)$bankRow['UNIT_acout']))];
	}
}
if (empty($bankList)) {
	$bankList = [
		['name' => 'LDB', 'currency' => ''],
		['name' => 'BCELOne', 'currency' => ''],
		['name' => 'JDB', 'currency' => ''],
	];
}

// ສະຖານະການໂອນ ຈາກ tb_sale_status
$saleStatusList = [];
$ssRes = $conn->query("SELECT id_sale_status, name_status FROM tb_sale_status ORDER BY id_sale_status ASC");
if ($ssRes) {
    while ($ssRow = $ssRes->fetch_assoc()) {
        $saleStatusList[] = $ssRow;
    }
}

$currencyLabelMap = [
	'LAK' => 'ກີບ (LAK)',
	'THB' => 'ບາດ (THB)',
	'USD' => 'ໂດລາ (USD)',
	'CNY' => 'ຢວນ (CNY)',
];

$statusStyleMap = [
	'1' => ['label' => 'ກຳລັງດຳເນີນການ',            'bg' => '#fff7e6', 'color' => '#b95c00', 'border' => '#ffd699', 'icon' => 'fa-hourglass-half'],
	'2' => ['label' => 'ກຳລັງຈັດສົ່ງ',               'bg' => '#e8f1ff', 'color' => '#1d5fd6', 'border' => '#b9d4ff', 'icon' => 'fa-truck-fast'],
	'3' => ['label' => 'ຍົກເລີກ',                     'bg' => '#ffeef0', 'color' => '#c81e3a', 'border' => '#ffc2cb', 'icon' => 'fa-circle-xmark'],
	'4' => ['label' => 'ເຄື່ອງຮອດໂຮງງານສຳເລັດແລ້ວ', 'bg' => '#e8faf0', 'color' => '#0c8a4b', 'border' => '#b4eecd', 'icon' => 'fa-circle-check'],
];

$saveStatus = isset($_GET['save_status']) ? $_GET['save_status'] : '';
$saveMsg    = isset($_GET['save_msg']) ? $_GET['save_msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="image/logoETL.jpg">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

	<script src="js/pro.min.js" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<title>Stock</title>

	<style type="text/css">
		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}

		body {
			background: #ffffff !important;
			margin: 0;
		}

		.navbarr {
			position: fixed;
		}

		.circle {
			border-radius: 14px;
		}

		.cll {
			background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
			--bs-table-bg: transparent;
			--bs-table-color: mediumblue;
		}

		.cll th {
			background-color: transparent !important;
			color: mediumblue !important;
		}

		.status-badge {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 6px;
			padding: 6px 14px;
			border-radius: 999px;
			font-size: 12.5px;
			font-weight: 600;
			line-height: 1.4;
			white-space: normal;
			word-break: break-word;
			text-align: center;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
			letter-spacing: .2px;
			max-width: 100%;
		}

		.status-badge i {
			font-size: 12px;
		}

		.province-filter-card {
			border: 1px solid #e3e6ea;
			border-radius: 12px;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
			transition: box-shadow .2s ease;
		}

		.province-filter-card:hover {
			box-shadow: 0 6px 20px rgba(0, 0, 0, 0.09);
		}

		.province-filter-card .form-select,
		.province-filter-card .form-control {
			border-color: #d0d5dd;
			background-color: #f9fafb;
		}

		.province-filter-card .form-select:hover,
		.province-filter-card .form-control:hover {
			border-color: #4481eb;
		}

		.province-filter-card .form-select:focus,
		.province-filter-card .form-control:focus {
			border-color: #4481eb;
			box-shadow: 0 0 0 0.2rem rgba(68, 129, 235, 0.15);
			background-color: #fff;
		}

		.province-filter-label {
			font-size: 14px;
			font-weight: 600;
			color: #344054;
			letter-spacing: .3px;
		}

		.province-filter-label i {
			color: #4481eb;
		}

		.province-active-badge {
			font-size: 12px;
			font-weight: 500;
			background-color: #eef4ff;
			color: #4481eb;
			padding: 6px 12px;
		}

		.province-active-badge a {
			color: #98a2b3;
			text-decoration: none;
		}

		.province-active-badge a:hover {
			color: #d92d20;
		}

		.filter-clear-all {
			font-size: 13px;
			color: #d92d20;
			text-decoration: none;
			font-weight: 600;
		}

		.filter-clear-all:hover {
			text-decoration: underline;
		}

		.bulk-action-bar {
			position: sticky;
			top: 0;
			z-index: 5;
			width: 100%;
			margin: 0 0 10px 0;
			background: #fff;
			border: 1px solid #e3e6ea;
			border-radius: 12px;
			box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
			padding: 10px 16px;
		}

		.bulk-action-bar label {
			font-weight: 600;
			color: #344054;
		}

		.rowCheck,
		.masterCheck {
			width: 18px;
			height: 18px;
		}

		.pending-count-badge {
			font-size: 13px;
			font-weight: 600;
			background-color: #e6f7ff;
			color: #0d8ecf;
			padding: 6px 12px;
		}

		.readonly-calc {
			background-color: #f2f4f7 !important;
			cursor: not-allowed;
		}

		.currency-text {
			font-size: 14px;
			font-weight: 600;
			padding: 7px 14px;
			letter-spacing: .2px;
			white-space: nowrap;
		}

		/* ===== ຕາຕະລາງລາຍການ - ອອກແບບໃໝ່ໃຫ້ອ່ານງ່າຍຂຶ້ນ ===== */
		.table-shell {
			width: 100%;
			margin: 0;
			background: #fff;
			border-radius: 16px;
			overflow: hidden auto;
			box-shadow: 0 8px 24px rgba(16, 24, 40, 0.08);
			border: 1px solid #e5e9f0;
			max-height: 78vh;
		}

		.sale-table {
			margin-bottom: 0;
			border-collapse: separate;
			border-spacing: 0;
		}

		.sale-table thead th {
			background: #1f2a44 !important;
			color: #f5f7fa !important;
			font-weight: 600;
			font-size: 13.5px;
			letter-spacing: .2px;
			padding: 15px 10px;
			border-bottom: none !important;
			vertical-align: middle;
			position: sticky;
			top: 0;
			z-index: 2;
		}

		.sale-table tbody tr {
			transition: background-color .12s ease;
		}

		.sale-table tbody tr:nth-child(even) {
			background-color: #f8fafc;
		}

		.sale-table tbody tr:hover {
			background-color: #eef4ff;
		}

		.sale-table td {
			padding: 13px 10px;
			vertical-align: middle;
			border-color: #edf0f5 !important;
			font-size: 13.5px;
			color: #364256;
		}

		.cell-stack {
			display: flex;
			flex-direction: column;
			gap: 5px;
			min-width: 0;
		}

		.cell-line {
			display: flex;
			align-items: flex-start;
			gap: 6px;
			line-height: 1.45;
			min-width: 0;
		}

		.cell-line-body {
			display: flex;
			flex-direction: column;
			gap: 1px;
			min-width: 0;
			flex: 1 1 auto;
		}

		.cell-line i {
			color: #8f9bb3;
			width: 14px;
			flex-shrink: 0;
			text-align: center;
			margin-top: 2px;
		}

		.cell-label {
			color: #5c6b85;
			font-weight: 500;
			white-space: nowrap;
		}

		.cell-value {
			color: #0f1b33;
			font-weight: 600;
			overflow-wrap: break-word;
			word-break: normal;
		}

		.cell-value-code {
			color: #0f1b33;
			font-weight: 600;
			display: block;
			max-width: 100%;
			white-space: nowrap;
			overflow: hidden;
			text-overflow: ellipsis;
			cursor: help;
		}

		.cell-title {
			color: #0f1b33;
			font-weight: 700;
			word-break: break-word;
			font-size: 14px;
		}

		.money-input {
			text-align: right;
			font-variant-numeric: tabular-nums;
			font-weight: 600;
		}

		.action-cell {
			min-width: 130px;
		}

		.pic-cell {
			min-width: 100px;
		}

		.pic-thumb-wrap {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 84px;
			height: 84px;
			border-radius: 12px;
			overflow: hidden;
			border: 1px solid #e2e6ee;
			background: #f2f4f8;
			box-shadow: 0 1px 3px rgba(16, 24, 40, 0.08);
			transition: transform .15s ease, box-shadow .15s ease;
		}

		.pic-thumb-wrap:hover {
			transform: scale(1.06);
			box-shadow: 0 4px 10px rgba(16, 24, 40, 0.18);
		}

		.pic-thumb-wrap img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			display: block;
		}

		.pic-placeholder {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 84px;
			height: 84px;
			border-radius: 12px;
			border: 1px dashed #d0d5dd;
			background: #f9fafb;
			color: #b0b7c3;
			font-size: 26px;
		}
	</style>
</head>

<body>

	<?php if ($saveMsg !== '') {
		$alertClass = 'alert-secondary';
		if ($saveStatus === 'success') $alertClass = 'alert-success';
		elseif ($saveStatus === 'partial') $alertClass = 'alert-warning';
		elseif ($saveStatus === 'error') $alertClass = 'alert-danger';

		$alertIcon = 'fa-circle-exclamation';
		if ($saveStatus === 'success') $alertIcon = 'fa-circle-check';
		elseif ($saveStatus === 'partial') $alertIcon = 'fa-triangle-exclamation';
	?>
		<div class="container-fluid px-0 mt-3">
			<div class="alert <?php echo $alertClass ?> alert-dismissible fade show d-flex align-items-start gap-2" role="alert" style="white-space: pre-line;">
				<i class="fa-solid <?php echo $alertIcon ?> mt-1"></i>
				<div><?php echo nl2br(h($saveMsg)) ?></div>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
	<?php } ?>

	<div class="container-fluid px-0 mt-3 mb-4">
		<form method="get" id="provinceFilterForm" class="province-filter-card p-3 bg-white">
			<input type="hidden" name="lang" id="filterLangInput" value="<?php echo h($lang) ?>">

			<div class="d-flex flex-wrap align-items-end gap-3">
				<div>
					<label for="provinceSelect" class="col-form-label province-filter-label mb-0 d-flex align-items-center gap-2">
						<i class="fa-solid fa-filter"></i>
						<span data-translate="filterByWarehouse">Filter ຕາມສາງ/ແຂວງ</span>
					</label>
					<select name="province" id="provinceSelect" class="form-select" style="min-width:200px;">
						<option value="" data-translate="allProvinces">-- ທັງໝົດ (ໃນສິດຂອງທ່ານ) --</option>
						<?php foreach ($provinceList as $p): ?>
							<option value="<?php echo h($p) ?>" <?php echo ($selectedProvince === $p) ? 'selected' : '' ?>><?php echo h($p) ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label for="dateFromInput" class="col-form-label province-filter-label mb-0 d-flex align-items-center gap-2">
						<i class="fa-regular fa-calendar-days"></i>
						<span data-translate="dateFrom">ວັນທີເລີ່ມ</span>
					</label>
					<input type="date" name="date_from" id="dateFromInput" class="form-control" value="<?php echo h($dateFrom) ?>">
				</div>

				<div>
					<label for="dateToInput" class="col-form-label province-filter-label mb-0 d-flex align-items-center gap-2">
						<i class="fa-regular fa-calendar-days"></i>
						<span data-translate="dateTo">ວັນທີສິ້ນສຸດ</span>
					</label>
					<input type="date" name="date_to" id="dateToInput" class="form-control" value="<?php echo h($dateTo) ?>">
				</div>

				<div class="flex-grow-1" style="min-width:220px;">
					<label for="searchNoInput" class="col-form-label province-filter-label mb-0 d-flex align-items-center gap-2">
						<i class="fa-solid fa-magnifying-glass"></i>
						<span data-translate="searchNo">ຄົ້ນຫາ ແຜນເລກທີ່ / PR / ຊື່ອຸປະກອນ</span>
					</label>
					<input type="text" name="search_no" id="searchNoInput" class="form-control" placeholder="ແຜນເລກທີ່ ຫຼື PR ຫຼື ຊື່ອຸປະກອນ" value="<?php echo h($searchNo) ?>">
				</div>

				<div>
					<button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
						<i class="fa-solid fa-magnifying-glass"></i>
						<span data-translate="search">ຄົ້ນຫາ</span>
					</button>
				</div>

				<?php if ($selectedProvince !== '' || $dateFrom !== '' || $dateTo !== '' || $searchNo !== ''): ?>
					<div>
						<a href="<?php echo h(buildFilterUrl(['province', 'date_from', 'date_to', 'search_no'])) ?>" class="filter-clear-all d-flex align-items-center gap-1">
							<i class="fa-solid fa-xmark"></i>
							<span data-translate="clearAllFilters">ລ້າງ Filter ທັງໝົດ</span>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<div class="d-flex flex-wrap gap-2 mt-2">
				<?php if ($selectedProvince !== ''): ?>
					<span class="badge rounded-pill province-active-badge d-flex align-items-center gap-1">
						<i class="fa-solid fa-location-dot"></i> <?php echo h($selectedProvince) ?>
						<a href="<?php echo h(buildFilterUrl(['province'])) ?>" class="ms-1" title="ລ້າງ filter ແຂວງ"><i class="fa-solid fa-xmark"></i></a>
					</span>
				<?php endif; ?>

				<?php if ($dateFrom !== '' || $dateTo !== ''): ?>
					<span class="badge rounded-pill province-active-badge d-flex align-items-center gap-1">
						<i class="fa-regular fa-calendar-days"></i>
						<?php echo h($dateFrom !== '' ? $dateFrom : '...') ?> &rarr; <?php echo h($dateTo !== '' ? $dateTo : '...') ?>
						<a href="<?php echo h(buildFilterUrl(['date_from', 'date_to'])) ?>" class="ms-1" title="ລ້າງ filter ວັນທີ"><i class="fa-solid fa-xmark"></i></a>
					</span>
				<?php endif; ?>

				<?php if ($searchNo !== ''): ?>
					<span class="badge rounded-pill province-active-badge d-flex align-items-center gap-1">
						<i class="fa-solid fa-magnifying-glass"></i> <?php echo h($searchNo) ?>
						<a href="<?php echo h(buildFilterUrl(['search_no'])) ?>" class="ms-1" title="ລ້າງ filter ຄົ້ນຫາ"><i class="fa-solid fa-xmark"></i></a>
					</span>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<script>
		(function() {
			var urlParams = new URLSearchParams(window.location.search);
			var currentLang = urlParams.get('lang') || localStorage.getItem('site_lang') || 'zh';
			var langInput = document.getElementById('filterLangInput');
			if (langInput) langInput.value = currentLang;
		})();
	</script>

	<?php
	$whereConds = [buildSaleStatusSql('a')];
	$bindTypes  = '';
	$bindValues = [];

	if ($selectedProvince !== '') {
		$whereConds[] = "a.Province = ?";
		$bindTypes   .= 's';
		$bindValues[] = $selectedProvince;
	}
	if ($dateFrom !== '') {
		$whereConds[] = "DATE(a.dateRe) >= ?";
		$bindTypes   .= 's';
		$bindValues[] = $dateFrom;
	}
	if ($dateTo !== '') {
		$whereConds[] = "DATE(a.dateRe) <= ?";
		$bindTypes   .= 's';
		$bindValues[] = $dateTo;
	}
	if ($searchNo !== '') {
		$whereConds[] = "(a.OA_Out LIKE ? OR a.planing_number LIKE ? OR a.Items LIKE ?)";
		$likeVal      = '%' . $searchNo . '%';
		$bindTypes   .= 'sss';
		$bindValues[] = $likeVal;
		$bindValues[] = $likeVal;
		$bindValues[] = $likeVal;
	}

	$scopeSql     = buildSaleScopeSql($scope);
	$whereConds[] = $scopeSql['sql'];
	$bindTypes   .= $scopeSql['types'];
	foreach ($scopeSql['values'] as $v) {
		$bindValues[] = $v;
	}

	// GROUP BY a.id ກັນແຖວຊ້ຳຈາກ LEFT JOIN (ບໍ່ດັ່ງນັ້ນຈຳນວນລາຍການຈະບວມ)
	$sql = "SELECT
	a.id, a.Items, a.OA_Out, a.planing_number, a.Province, a.Item_code,
	a.Use_For, a.Section, a.S_Status, a.Stockcut, a.Vendor, a.Groupp,
	a.picture, a.Unit, a.Type, a.Purchase_Shop, a.Price, a.Unit_Price,
	a.Fee, a.Currency, a.Bank, a.Reference_Doc, a.User_Re, a.Sale_Status,
	a.finance_onder,
	DATE(a.dateRe) AS 'dateRe',
	MAX(c.Stock_TMD) AS Stock_TMD,
	MAX(b.Unit) AS 'Unitpro',
	MAX(c.Unit) AS 'Unitcenter',
	MAX(v.UNIT_acout) AS BankCurrency,
	(SELECT AVG(r2.Price) FROM request r2 WHERE r2.Item_code = a.Item_code AND r2.Price > 0) AS AvgHistPrice
FROM request a
LEFT JOIN stock_province b ON a.Item_code = b.Item_code AND a.Province = b.Provinces
LEFT JOIN stock c ON a.Item_code = c.Item_code
LEFT JOIN tb_acouting_vender v ON a.Bank = v.Name_Accout
WHERE " . implode(' AND ', $whereConds) . "
GROUP BY a.id
ORDER BY a.dateRe ASC";

	$stmt = $conn->prepare($sql);
	if ($stmt === false) {
		die("Error preparing query: " . $conn->error);
	}
	if ($bindTypes !== '') {
		$bindRefs = [$bindTypes];
		foreach ($bindValues as $k => $v) {
			$bindRefs[] = &$bindValues[$k];
		}
		call_user_func_array([$stmt, 'bind_param'], $bindRefs);
	}
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows > 0) {
		$modalsHtml   = '';
		$totalPending = $result->num_rows;
	?>
		<div align="center" style="padding: 0;">

			<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2">
				<div class="d-flex align-items-center gap-2">
					<input type="checkbox" id="checkAll" class="masterCheck" onclick="toggleAll(this)">
					<label for="checkAll" data-translate="selectAll">ເລືອກທັງໝົດ</label>
					<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
						<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
					</span>
				</div>
				<button type="button" class="btn btn-success d-flex align-items-center gap-2" onclick="return handleBulkSave()">
					<i class="fa-solid fa-floppy-disk"></i>
					<span data-translate="withdrawSelected">ບັນທຶກລາຍການທັງໝົດ</span>
				</button>
			</div>

			<form action="saveStock_Sale.php" id="bulkForm" name="bulkForm" method="post" enctype="multipart/form-data">
				<input type="hidden" name="single_id" id="singleIdField" value="">

				<div class="table-shell">
				<table class="table sale-table align-middle" style="table-layout:fixed;">
					<thead>
						<tr align="center">
							<th width="2%"></th>
							<th width="12%" data-translate="warehouse"><i class="fa-solid fa-location-dot me-1"></i>ສາງ</th>
							<th width="7%" data-translate="picture">ຮູບພາບ</th>
							<th width="14%" data-translate="equipment"><i class="fa-light fa-forklift me-1"></i>ອຸປະກອນ</th>
							<th width="11%" data-translate="S_Status"><i class="fa-solid fa-truck-fast me-1"></i>ສະຖານະການຈັດສົ່ງ</th>
							<th width="9%" data-translate="purchaseShop">ຂໍ້ມູນ/ຊື່ຮ້ານທີ່ຈັດຊື້</th>
							<th width="8%" data-translate="price">ລາຄາ</th>
							<th width="8%" data-translate="unitPrice">ລາຄາສະເລ່ຍ</th>
							<th width="6%" data-translate="fee">ຄ່າທຳນຽມ</th>
							<th width="8%" data-translate="currency">ສະກຸນເງິນ</th>
							<th width="8%" data-translate="bank">ທະນາຄານ</th>
							<th width="9%" data-translate="Sale_Status">ສະຖານະການຈັດຊື້</th>
							<th width="9%" data-translate="Sale_Status">ສະຖານະການເງີນໂອນ</th>
							<th width="10%">ຈັດການ <i class="fa-light fa-warehouse fa-lg"></i></th>
						</tr>
					</thead>
					<tbody>
						<?php while ($row = $result->fetch_assoc()) {
							$id = $row['id'];

							$curStockcut = (string)($row['Stockcut'] ?? '');
							if ($curStockcut !== 'Stock_TMD') $curStockcut = 'Unit';
						?>
							<tr>
								<td align="center">
									<input type="checkbox" name="selected[]" value="<?php echo $id ?>" class="rowCheck">
									<!-- ຂໍ້ມູນທີ່ຝາກໄປໃຫ້ saveStock_Sale.php (ໜ້ານີ້ບໍ່ໄດ້ໃຊ້ເອງ)
									     OAtoPro -> UPDATE OA_topro, Items -> ຊື່ອຸປະກອນໃນຂໍ້ຄວາມ error -->
									<input type="hidden" name="Items[<?php echo $id ?>]" value="<?php echo h($row['Items']) ?>" />
									<input type="hidden" name="OAtoPro[<?php echo $id ?>]" value="<?php echo h($row['OA_Out']) ?>" />
								</td>

								<td width="12%">
									<div class="cell-stack">
										<div class="cell-line"><i class="fa-solid fa-location-dot"></i><span class="cell-title"><?php echo h($row['Province']) ?></span></div>
										<div class="cell-line"><i class="fa-light fa-memo-circle-info"></i><div class="cell-line-body"><span class="cell-label" data-translate="prNumber">PR ເລກທີ:</span><span class="cell-value-code" title="<?php echo h($row['OA_Out']) ?>"><?php echo h($row['OA_Out']) ?></span></div></div>
									</div>
								</td>

								<td width="7%" align="center" class="pic-cell">
									<?php
									$itemPic = resolveItemPicture($row['Item_code'] ?? '', $PICTURE_DISK_BASE, $PICTURE_URL_BASE, $PICTURE_EXTS);
									if ($itemPic === '' && !empty($row['picture'])) $itemPic = $row['picture']; // fallback ຄ່າເກົ່າໃນ DB ຖ້າຫາໄຟລ໌ບໍ່ພົບ
									?>
									<?php if ($itemPic !== ''): ?>
										<a href="<?php echo h($itemPic) ?>" target="_blank" class="pic-thumb-wrap" title="ເບິ່ງຮູບໃຫຍ່">
											<img src="<?php echo h($itemPic) ?>" alt="picture">
										</a>
									<?php else: ?>
										<span class="pic-placeholder" title="ບໍ່ມີຮູບພາບ"><i class="fa-regular fa-image"></i></span>
									<?php endif; ?>
								</td>

								<td width="14%">
									<div class="cell-stack">
										<div class="cell-line"><i class="fa-light fa-forklift"></i><span class="cell-title"><?php echo h($row['Items']) ?></span></div>
										<div class="cell-line"><i class="fa-solid fa-diagram-project"></i><span class="cell-label" data-translate="planNumber">ແຜນເລກທີ່:</span><span class="cell-value"><?php echo h($row['planing_number']) ?></span></div>
										<div class="cell-line"><i class="fa-solid fa-globe"></i><span class="cell-label" data-translate="vendor">ຂໍ້ມູນຮ້ານທີ່ຈັດຊື້:</span><span class="cell-value"><?php echo h($row['Vendor']) ?></span></div>
										<div class="cell-line"><i class="fa-light fa-network-wired"></i><div class="cell-line-body"><span class="cell-label" data-translate="section">ພາກສ່ວນ:</span><span class="cell-value"><?php echo h($row['Section']) ?></span></div></div>
										<div class="cell-line"><i class="fa-regular fa-user-tie-hair"></i><span class="cell-value"><?php echo h($row['User_Re']) ?></span></div>
										<div class="cell-line"><i class="fa-duotone fa-calendar-days"></i><span class="cell-value"><?php echo h($row['dateRe']) ?></span></div>
									</div>
								</td>

								<td width="11%" align="center" style="text-align:center;">
									<?php
									$sKey = (string)$row['S_Status'];
									if (isset($statusStyleMap[$sKey])) {
										$s = $statusStyleMap[$sKey];
										echo '<span class="status-badge" style="background-color:' . $s['bg'] . ';color:' . $s['color'] . ';border:1px solid ' . $s['border'] . ';">'
											. '<i class="fa-solid ' . $s['icon'] . '"></i> ' . h($s['label']) . '</span>';
									} else {
										echo h($row['S_Status']);
									}
									?>
								</td>

								<td width="9%" align="center">
									<input type="text" name="Purchase_Shop[<?php echo $id ?>]" class="form-control form-control-sm" placeholder="ຊື່ຮ້ານ/ຂໍ້ມູນຮ້ານ" value="<?php echo h($row['Purchase_Shop']) ?>">
								</td>

								<td width="8%" align="center">
									<input type="text" inputmode="decimal" name="Price[<?php echo $id ?>]" class="form-control form-control-sm price-input money-input" placeholder="0" autocomplete="off" value="<?php echo fmtMoneyForInput($row['Price']) ?>">
								</td>

								<td width="8%" align="center">
									<input type="text" name="Unit_Price[<?php echo $id ?>]" class="form-control form-control-sm money-input readonly-calc" placeholder="0" autocomplete="off" readonly title="ຄ່າສະເລ່ຍຈາກປະຫວັດການຊື້ (ຄິດໄລ່ອັດຕະໂນມັດ)" value="<?php echo fmtMoneyForInput($row['AvgHistPrice']) ?>">
								</td>

								<td width="6%" align="center">
									<input type="text" inputmode="decimal" name="Fee[<?php echo $id ?>]" class="form-control form-control-sm money-input" placeholder="0" autocomplete="off" value="<?php echo fmtMoneyForInput($row['Fee']) ?>">
								</td>

								<td width="8%" align="center">
									<?php
									// ສະກຸນເງິນມາຈາກ tb_acouting_vender.UNIT_acout ຂອງບັນຊີທີ່ເລືອກ ບໍ່ໄດ້ໃຫ້ເລືອກເອງ
									$rowCurrency = strtoupper(trim((string)($row['BankCurrency'] ?? '')));
									if ($rowCurrency === '') $rowCurrency = strtoupper(trim((string)($row['Currency'] ?? '')));
									?>
									<span class="currency-text badge rounded-pill <?php echo $rowCurrency !== '' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light text-muted' ?>">
										<?php if ($rowCurrency !== ''): ?>
											<?php echo h($currencyLabelMap[$rowCurrency] ?? $rowCurrency) ?>
										<?php else: ?>
											<i class="fa-solid fa-money-check-dollar"></i>
										<?php endif; ?>
									</span>
									<input type="hidden" name="Currency[<?php echo $id ?>]" class="currency-value" value="<?php echo h($rowCurrency) ?>">
								</td>

								<td width="8%" align="center">
									<select name="Bank[<?php echo $id ?>]" class="form-select form-select-sm bank-select">
										<option value="" data-currency="">-- ເລືອກ --</option>
										<?php foreach ($bankList as $bankOption): ?>
											<option value="<?php echo h($bankOption['name']) ?>" data-currency="<?php echo h($bankOption['currency']) ?>" <?php echo ((string)$row['Bank'] === (string)$bankOption['name']) ? 'selected' : '' ?>><?php echo h($bankOption['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</td>

								<td width="9%" align="center">
									<select name="Sale_Status[<?php echo $id ?>]" class="form-select form-select-sm">
										<option value="">-- ເລືອກ --</option>
										<?php foreach ($saleStatusList as $ss): ?>
											<option value="<?php echo h($ss['name_status']) ?>"
											<?php echo ((string)$row['Sale_Status'] === (string)$ss['name_status']) ? 'selected' : '' ?>>
											<?php echo h($ss['name_status']) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
								<td width="9%" align="center">
									<select name="finance_order[<?php echo $id ?>]" class="form-select form-select-sm">
										<option value="">-- ເລືອກ --</option>
										<option value="ຍັງບໍ່ທັນໂອນ" <?php echo ((string)($row['finance_onder'] ?? '') === 'ຍັງບໍ່ທັນໂອນ') ? 'selected' : '' ?>>ຍັງບໍ່ທັນໂອນ</option>
										<option value="ໂອນສຳເລັດ" <?php echo ((string)($row['finance_onder'] ?? '') === 'ໂອນສຳເລັດ') ? 'selected' : '' ?>>ໂອນສຳເລັດ</option>
									</select>
								</td>
								<td width="10%" align="center" class="action-cell">
									<div class="d-flex flex-column align-items-center gap-2">
										<select class="form-select form-select-sm" name="Stockcut[<?php echo $id ?>]">
											<option value="Unit" <?php echo ($curStockcut === 'Unit') ? 'selected' : '' ?> title="ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່">ສາງອຸປະກອນ</option>
											<option value="Stock_TMD" <?php echo ($curStockcut === 'Stock_TMD') ? 'selected' : '' ?> title="ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່">ສາງເຄື່ອງໃຊ້</option>
										</select>
										<button type="button" id="Submit<?php echo $id ?>" class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-1 w-100" style="font-size: 12px;white-space:nowrap;" onclick="return submitSingle(<?php echo $id ?>, 'bulkForm')">
											<i class="fa-solid fa-floppy-disk"></i><span>ບັນທຶກ</span>
										</button>
									</div>
								</td>
							</tr>
						<?php
							// modal ເກັບໄວ້ໃນ buffer ແລ້ວ echo ຫຼັງ </form> (ຫ້າມ form ຊ້ອນ form)
							ob_start();
						?>
							<div class="modal fade" id="myModal<?php echo $id ?>" tabindex="-1" role="dialog">
								<div class="modal-dialog modal-lg">
									<div class="modal-content">
										<div class="modal-header">
											<h4 class="modal-title text-primary"><span data-translate="cancel">ການຍົກເລິກ</span></h4>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<div class="modal-body">
											<form id="formCancel<?php echo $id ?>" method="post" action="DataReAdmin_Sale.php">
												<input type="hidden" name="id" value="<?php echo $id ?>" />
												<label for="Issue<?php echo $id ?>" class="fw-bold">
													<span data-translate="cancelReason">ສາເຫດການຍົກເລິກ</span> —
													<span style="color: blue"><?php echo h($row['Items']) ?></span>
												</label>
												<textarea style="height: 100px;" name="Issue" id="Issue<?php echo $id ?>" class="form-control" required></textarea><br>
												<button type="submit" name="buttonCancel" class="btn btn-warning text-danger">
													<i class="fa-solid fa-ban"></i> <strong data-translate="cancelRequest">ຍົກເລິກການຂໍອຸປະກອນ</strong>
												</button>
											</form>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-xmark"></i> Close</button>
										</div>
									</div>
								</div>
							</div>
						<?php
							$modalsHtml .= ob_get_clean();
						} ?>
					</tbody>
				</table>
				</div>

				<div class="bulk-action-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2">
					<div class="d-flex align-items-center gap-2">
						<input type="checkbox" id="checkAllBottom" class="masterCheck" onclick="toggleAll(this)">
						<label for="checkAllBottom" data-translate="selectAll">ເລືອກທັງໝົດ</label>
						<span class="badge rounded-pill pending-count-badge d-flex align-items-center gap-1">
							<i class="fa-regular fa-clock"></i> ຄ້າງ <?php echo $totalPending ?> ລາຍການ
						</span>
					</div>
					<button type="button" class="btn btn-success d-flex align-items-center gap-2" onclick="return handleBulkSave()">
						<i class="fa-solid fa-floppy-disk"></i>
						<span data-translate="withdrawSelected">ບັນທຶກລາຍການທັງໝົດ</span>
					</button>
				</div>
			</form>
		</div>
		<?php echo $modalsHtml; ?>
	<?php } else { ?>
		<div class="text-center my-5" style="color:#00BFFF;font-size:24px;">ບໍ່ມີລາຍການເບິກອຸປະກອນ</div>
	<?php } ?>

	<script src="translate/lang.js"></script>
	<script>
		(function() {
			if (typeof translations === 'undefined' || typeof setLanguage !== 'function') {
				console.error('lang.js not loaded!');
				return;
			}

			function applyLang() {
				var urlParams = new URLSearchParams(window.location.search);
				var lang = urlParams.get('lang') || localStorage.getItem('site_lang') || 'zh';
				setLanguage(lang);
			}

			window.addEventListener('message', function(e) {
				if (e.data.type === 'CHANGE_LANGUAGE') setLanguage(e.data.lang);
			});

			applyLang();
			setTimeout(applyLang, 300);
			setTimeout(applyLang, 1000);
		})();
	</script>

	<script>
		window.addEventListener('error', function(e) {
			console.error('JS Error caught:', e.message, e.filename, e.lineno, e.colno);
			alert('ພົບ JavaScript Error:\nຂໍ້ຄວາມ: ' + e.message + '\nໄຟລ໌: ' + (e.filename || '-') + ' ແຖວ ' + (e.lineno || '-'));
		});

		// ນັບຈາກ selected[] ສະເໝີ ຢ່ານັບ .rowCheck ເພາະຈະຕິດ master checkbox ມານຳ
		function getRowCheckboxes() {
			return document.querySelectorAll('input[name="selected[]"]');
		}

		function getCheckedRows() {
			return document.querySelectorAll('input[name="selected[]"]:checked');
		}

		function toggleAll(masterCb) {
			getRowCheckboxes().forEach(function(el) {
				el.checked = masterCb.checked;
			});
			document.querySelectorAll('.masterCheck').forEach(function(m) {
				m.checked = masterCb.checked;
				m.indeterminate = false;
			});
		}

		document.addEventListener('change', function(e) {
			if (!e.target || e.target.name !== 'selected[]') return;
			var all = getRowCheckboxes().length;
			var checked = getCheckedRows().length;
			document.querySelectorAll('.masterCheck').forEach(function(m) {
				m.checked = (all > 0 && checked === all);
				m.indeterminate = (checked > 0 && checked < all);
			});
		});

		// ເລືອກທະນາຄານ/ບັນຊີ -> ຕັ້ງສະກຸນເງິນຂອງແຖວນັ້ນຕາມ UNIT_acout ໃຫ້ອັດຕະໂນມັດ
		var CURRENCY_LABELS = {
			LAK: 'ກີບ (LAK)',
			THB: 'ບາດ (THB)',
			USD: 'ໂດລາ (USD)',
			CNY: 'ຢວນ (CNY)'
		};

		document.addEventListener('change', function(e) {
			var el = e.target;
			if (!el || !el.classList || !el.classList.contains('bank-select')) return;

			var tr = el.closest('tr');
			if (!tr) return;

			var field = tr.querySelector('.currency-value');
			var text = tr.querySelector('.currency-text');
			if (!field || !text) return;

			var opt = el.options[el.selectedIndex];
			var cur = opt ? (opt.getAttribute('data-currency') || '').trim().toUpperCase() : '';

			field.value = cur;

			if (cur !== '') {
				var label = CURRENCY_LABELS[cur] || cur;
				text.innerHTML = label.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
			} else {
				text.innerHTML = '<i class="fa-solid fa-coins"></i>';
			}

			text.className = 'currency-text badge rounded-pill ' +
				(cur !== '' ? 'bg-primary-subtle text-primary-emphasis' : 'bg-light text-muted');
		});

		function formatMoneyDisplay(raw) {
			raw = raw.replace(/[^0-9.]/g, '');
			var parts = raw.split('.');
			var intPart = parts[0] || '';
			var decPart = parts.length > 1 ? parts.slice(1).join('').slice(0, 2) : null;

			intPart = intPart.replace(/^0+(?=\d)/, '');
			intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

			return decPart !== null ? intPart + '.' + decPart : intPart;
		}

		function parseMoneyValue(str) {
			if (!str) return 0;
			var val = parseFloat(String(str).replace(/,/g, ''));
			return isNaN(val) ? 0 : val;
		}

		document.querySelectorAll('.money-input:not([readonly])').forEach(function(el) {
			el.addEventListener('input', function() {
				var atEnd = el.selectionStart === el.value.length;
				el.value = formatMoneyDisplay(el.value);
				if (atEnd) el.setSelectionRange(el.value.length, el.value.length);
			});
		});

		function convertMoneyInputsToPlain(formId) {
			document.querySelectorAll('#' + formId + ' .money-input').forEach(function(el) {
				el.value = parseMoneyValue(el.value);
			});
		}

		// disable ແຖວທີ່ບໍ່ໄດ້ເລືອກ -> browser ບໍ່ສົ່ງໄປ server -> ບໍ່ຊົນ max_input_vars
		function pruneUnselectedRows(formId) {
			var form = document.getElementById(formId);
			if (!form) return;
			form.querySelectorAll('input[name="selected[]"]').forEach(function(cb) {
				var tr = cb.closest('tr');
				if (!tr) return;
				var keep = cb.checked;
				tr.querySelectorAll('input, select, textarea').forEach(function(el) {
					el.disabled = !keep;
				});
			});
		}

		function restoreAllRows(formId) {
			var form = document.getElementById(formId);
			if (!form) return;
			form.querySelectorAll('input, select, textarea').forEach(function(el) {
				el.disabled = false;
			});
		}

		window.addEventListener('pageshow', function() {
			restoreAllRows('bulkForm');
		});

		(function() {
			var f = document.getElementById('bulkForm');
			if (!f) return;
			f.addEventListener('submit', function() {
				convertMoneyInputsToPlain('bulkForm');
				pruneUnselectedRows('bulkForm');
			});
		})();

		function validateBulk() {
			var checked = getCheckedRows();
			if (checked.length === 0) {
				alert('ກະລຸນາເລືອກລາຍການທີ່ຕ້ອງການບັນທຶກຢ່າງໜ້ອຍ 1 ລາຍການ');
				return null;
			}

			var missing = [];
			checked.forEach(function(cb) {
				var priceInput = document.querySelector('input[name="Price[' + cb.value + ']"]');
				if (!priceInput || priceInput.value.trim() === '' || parseMoneyValue(priceInput.value) <= 0) {
					missing.push(cb.value);
					if (priceInput) priceInput.classList.add('is-invalid');
				} else {
					priceInput.classList.remove('is-invalid');
				}
			});

			if (missing.length > 0) {
				alert('ກະລຸນາປ້ອນລາຄາລວມ (ຫຼາຍກວ່າ 0) ໃຫ້ຄົບທຸກລາຍການທີ່ເລືອກ\nລາຍການ: ' + missing.join(', '));
				var firstBad = document.querySelector('input[name="Price[' + missing[0] + ']"]');
				if (firstBad) firstBad.scrollIntoView({
					behavior: 'smooth',
					block: 'center'
				});
				return null;
			}

			return checked;
		}

		function handleBulkSave() {
			var checked = validateBulk();
			if (!checked) return false;

			if (confirm('ຕ້ອງການບັນທຶກ ' + checked.length + ' ລາຍການ ແທ້ບໍ່?')) {
				var singleIdField = document.getElementById('singleIdField');
				if (singleIdField) singleIdField.value = '';
				convertMoneyInputsToPlain('bulkForm');
				pruneUnselectedRows('bulkForm');
				document.getElementById('bulkForm').submit();
			}
			return false;
		}

		function submitSingle(id, formId) {
			try {
				var priceInput = document.querySelector('#' + formId + ' input[name="Price[' + id + ']"]');
				if (!priceInput || priceInput.value.trim() === '' || parseMoneyValue(priceInput.value) <= 0) {
					alert('ກະລຸນາປ້ອນລາຄາລວມໃຫ້ຖືກຕ້ອງ');
					if (priceInput) priceInput.classList.add('is-invalid');
					return false;
				}
				priceInput.classList.remove('is-invalid');

				if (!confirm('ຕ້ອງການບັນທຶກລາຍການ ' + id + ' ແທ້ບໍ່?')) return false;

				document.querySelectorAll('#' + formId + ' input[name="selected[]"]').forEach(function(cb) {
					cb.checked = (String(cb.value) === String(id));
				});

				var singleIdField = document.getElementById('singleIdField');
				if (singleIdField) singleIdField.value = id;

				convertMoneyInputsToPlain(formId);
				pruneUnselectedRows(formId);

				var formEl = document.getElementById(formId);
				if (!formEl) {
					alert('ບໍ່ພົບ form #' + formId + ' (ບັນທຶກບໍ່ໄດ້)');
					return false;
				}
				formEl.submit();
				return false;
			} catch (err) {
				console.error('submitSingle error:', err);
				restoreAllRows(formId);
				alert('ເກີດຂໍ້ຜິດພາດຕອນບັນທຶກລາຍການ ' + id + ':\n' + err.message);
				return false;
			}
		}
	</script>
</body>

</html>