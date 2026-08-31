<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

// =========================================================
// ດຶງຮູບພາບຈາກ Item_code ແບບສົດໆທຸກຄັ້ງ (ບໍ່ໃຊ້ຄ່າ "picture" ທີ່ຄ້າງໃນ DB
// ເພາະຊື່ໄຟລ໌ຮູບຖືກສ້າງໃໝ່ແບບສຸ່ມ (uniqid) ທຸກຄັ້ງທີ່ອັບໂຫຼດ/ປ່ຽນຮູບຢູ່
// index.php/upload.php -> ຄ່າ "picture" ທີ່ບັນທຶກໄວ້ຄັ້ງດຽວຈະກາຍເປັນ path
// ເກົ່າທີ່ບໍ່ມີໄຟລ໌ຈິງແລ້ວ ເຮັດໃຫ້ຮູບບໍ່ຂຶ້ນ)
//
// ໝາຍເຫດ: index.php/upload.php ຕົວຈິງແລ້ວຢູ່ໃນໂຟນເດີ picture/ ບໍ່ແມ່ນ root,
// ດັ່ງນັ້ນ uploads/ ທີ່ຖືກໃຊ້ຈິງຈຶ່ງແມ່ນ "picture/uploads/"
// =========================================================
$uploadsBaseDir = __DIR__ . '/../picture/uploads';
$uploadsBaseUrl = 'picture/uploads';

function loadImageFilemap($mapFile)
{
	if (file_exists($mapFile)) {
		$decoded = json_decode(file_get_contents($mapFile), true);
		return is_array($decoded) ? $decoded : array();
	}
	return array();
}

// map[safeFileName] = Item_code (display name) -> ສ້າງ index ຍ້ອນກັບ
// ເພື່ອຄົ້ນຫາໄວ (Item_code -> safeFileName)
$imageMap = loadImageFilemap($uploadsBaseDir . '/filemap.json');
$imageByItemCode = array();
foreach ($imageMap as $safeName => $itemCode) {
	// ຖ້າມີຫຼາຍໄຟລ໌ຕໍ່ 1 ລະຫັດ (ບໍ່ຄວນມີຫຼັງ cleanup, ແຕ່ກັນໄວ້ກ່ອນ) ໃຫ້ໃຊ້ອັນຫຼ້າສຸດ
	$path = $uploadsBaseDir . '/' . $safeName;
	if (!isset($imageByItemCode[$itemCode]) || (file_exists($path) && filemtime($path) > (isset($imageByItemCode[$itemCode]['mtime']) ? $imageByItemCode[$itemCode]['mtime'] : 0))) {
		$imageByItemCode[$itemCode] = array(
			'safeName' => $safeName,
			'mtime'    => file_exists($path) ? filemtime($path) : 0,
		);
	}
}

/**
 * ຄືນຄ່າ URL ຮູບພາບປັດຈຸບັນຂອງ Item_code ນີ້, ຫຼື null ຖ້າບໍ່ພົບ
 */
function getItemImageUrl($itemCode, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl)
{
	$itemCode = trim((string) $itemCode);
	if ($itemCode === '' || !isset($imageByItemCode[$itemCode])) {
		return null;
	}
	$safeName = $imageByItemCode[$itemCode]['safeName'];
	$path = $uploadsBaseDir . '/' . $safeName;
	if (!file_exists($path)) {
		return null;
	}
	// ຕິດ ?v=timestamp ກັນ browser cache ຮູບເກົ່າ
	return $uploadsBaseUrl . '/' . rawurlencode($safeName) . '?v=' . filemtime($path);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<link rel="shortcut icon" href="image/favicons.png">

	<!-- ===== Bootstrap 5 CSS ===== -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

	<!-- ===== Font Awesome 6 ===== -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

	<!-- ===== Select2 CSS ===== -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

	<!-- ===== jQuery (ສຳລັບ Select2 ແລະ ຟັງຊັນອື່ນໆ) ===== -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- ===== Bootstrap 5 JS Bundle ===== -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- ===== Select2 JS (ຫຼັງຈາກ jQuery) ===== -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="css/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

	<title>ນຳໃຊ້ອຸປະກອນ</title>

	<!-- Core theme CSS -->
	<link href="css/styles.css" rel="stylesheet" />

	<style type="text/css">
		/* ===== ພື້ນຖານ / RESET ===== */
		* {
			box-sizing: border-box;
		}

		html,
		body {
			overflow-x: hidden;
			max-width: 100%;
		}

		/* ===== ສີຫຼັກ (ສີຟ້າ ໄຊບາ) ===== */
		:root {
			--bs-primary: #1a5276;
			--bs-primary-rgb: 26, 82, 118;
			--bs-success: #1a5276;
			--bs-success-rgb: 26, 82, 118;
			--sidebar-width: 280px;
			--navbar-bg-start: #0C2C55;
			--navbar-bg-end: #0C2C55;
			--accent-color: #f1c40f;
		}

		body,
		td,
		th {
			font-family: "Phetsarath OT";
		}

		.right {
			float: right;
		}

		.circle {
			border-radius: 14px;
			overflow: hidden;
		}

		/* ===== Layout Wrapper ===== */
		#wrapper {
			position: relative;
			min-height: 100vh;
			width: 100%;
		}

		/* ===== Sidebar ສີຟ້າ ===== */
		#sidebar-wrapper {
			background: linear-gradient(180deg, #0C2C55 0%, #0C2C55 100%);
			color: #fff;
			box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15);
			border-right: none !important;
			min-height: 100vh;
			width: var(--sidebar-width);
			flex-shrink: 0;
			transition: margin-left 0.3s ease, transform 0.3s ease;
		}

		#sidebar-wrapper .sidebar-heading {
			background: rgba(0, 0, 0, 0.12) !important;
			border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
			color: #fff !important;
			font-weight: 600;
			padding: 1.2rem 1.25rem;
			display: flex;
			align-items: center;
			gap: 14px;
		}

		/* ===== Logo ໃຫຍ່ຂຶ້ນ ===== */
		#sidebar-wrapper .sidebar-heading img {
			width: 100px !important;
			height: auto;
			border-radius: 14px;
			background: rgba(255, 255, 255, 0.1);
			padding: 6px;
			transition: all 0.3s ease;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
		}

		#sidebar-wrapper .sidebar-heading img:hover {
			transform: scale(1.05);
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
		}

		/* ===== ຊື່ລະບົບ ===== */
		#sidebar-wrapper .sidebar-heading .system-name {
			font-size: 17px;
			font-weight: 700;
			letter-spacing: 0.5px;
			display: block;
			line-height: 1.3;
			color: #fff;
		}

		#sidebar-wrapper .sidebar-heading .system-sub {
			font-size: 12px;
			opacity: 0.75;
			display: block;
			font-weight: 400;
			letter-spacing: 0.3px;
			color: #f1c40f;
		}

		/* ===== ລາຍການເມນູ ===== */
		#sidebar-wrapper .list-group-item {
			background: transparent !important;
			color: rgba(255, 255, 255, 0.9) !important;
			border: none;
			border-radius: 0;
			padding: 0.75rem 1.25rem;
			transition: all 0.25s ease;
			font-weight: 500;
			display: flex;
			align-items: center;
			gap: 10px;
			position: relative;
			flex-wrap: wrap;
		}

		#sidebar-wrapper .list-group-item:hover {
			background: rgba(255, 255, 255, 0.12) !important;
			transform: translateX(6px);
			color: #fff !important;
		}

		#sidebar-wrapper .list-group-item .badge {
			background: linear-gradient(135deg, #ffc107, #ffb300) !important;
			color: #000 !important;
			font-weight: 700;
			font-size: 12px;
			padding: 2px 10px;
			border-radius: 20px;
			box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
			margin-left: auto;
		}

		#sidebar-wrapper .list-group-item.active {
			background: rgba(255, 255, 255, 0.15) !important;
			border-left: 4px solid #ffc107;
		}

		#sidebar-wrapper .list-group-item i,
		#sidebar-wrapper .list-group-item .fi,
		#sidebar-wrapper .list-group-item .bi {
			font-size: 18px;
			width: 24px;
			text-align: center;
			flex-shrink: 0;
		}

		/* ===== ເສັ້ນແບ່ງກຸ່ມ ===== */
		.menu-divider {
			height: 1px;
			background: rgba(255, 255, 255, 0.08);
			margin: 4px 16px;
		}

		/* ===== Navbar ສີຟ້າ ===== */
		.navbar-blue {
			background: linear-gradient(135deg, #0C2C55 0%, #0C2C55 100%) !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
			flex-wrap: wrap;
		}

		.navbar-blue .nav-link,
		.navbar-blue .navbar-brand,
		.navbar-blue .navbar-text {
			color: #fff !important;
		}

		.navbar-blue .nav-link:hover {
			color: rgba(255, 255, 255, 0.8) !important;
		}

		.navbar-blue .btn-primary {
			background-color: rgba(255, 255, 255, 0.15) !important;
			border-color: rgba(255, 255, 255, 0.25) !important;
			color: #fff !important;
		}

		.navbar-blue .btn-primary:hover {
			background-color: rgba(255, 255, 255, 0.25) !important;
		}

		.btn-success {
			background: linear-gradient(135deg, #1a5276, #0c2c55) !important;
			border-color: #1a5276 !important;
			color: #fff !important;
		}

		.btn-success:hover {
			background: linear-gradient(135deg, #0c2c55, #061a33) !important;
			border-color: #0c2c55 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(26, 82, 118, 0.4);
			color: #fff !important;
		}

		/* ===== Dropdown ===== */
		.animate-dropdown {
			border-radius: 12px;
			border: none;
			padding: 0.5rem 0;
			min-width: 200px;
			animation: fadeInDown 0.3s ease;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
		}

		@keyframes fadeInDown {
			from {
				opacity: 0;
				transform: translateY(-12px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.lang-option {
			display: flex;
			align-items: center;
			padding: 0.6rem 1.25rem;
			transition: all 0.2s ease;
		}

		.lang-option:hover {
			background: #f0fdf4;
			transform: translateX(4px);
		}

		.lang-option.active {
			background: #2d8a4e;
			color: #fff !important;
		}

		.lang-option.active .lang-name {
			color: #fff !important;
		}

		.lang-option.active .check-icon {
			color: #fff;
		}

		.lang-option .flag-icon {
			font-size: 1.4rem;
			margin-right: 12px;
		}

		.lang-option .lang-name {
			flex: 1;
			font-weight: 500;
		}

		.lang-option .check-icon {
			color: transparent;
			font-size: 1rem;
		}

		.lang-option.active .check-icon {
			color: #fff;
		}

		/* ===== User Dropdown ===== */
		.user-dropdown .dropdown-item {
			padding: 0.6rem 1.25rem;
			transition: background 0.2s;
		}

		.user-dropdown .dropdown-item:hover {
			background: #f0fdf4;
		}

		.user-dropdown .dropdown-item.text-danger:hover {
			background: #fde8e8;
		}

		/* ===== Select2 ===== */
		.select2-container .select2-selection--single {
			border-radius: 8px !important;
			border-color: #e0e0e0 !important;
			height: 45px !important;
		}

		.select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: 45px !important;
			padding-left: 15px;
		}

		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 43px !important;
		}

		/* ===== Page Content ===== */
		#page-content-wrapper {
			flex: 1;
			min-width: 0;
			width: 100%;
			background: #c3d1de;
		}

		/* ===== ພື້ນຫຼັງອ່ອນໆ ===== */
		.bg-soft-blue {
			background: linear-gradient(135deg, #e8f0f8 0%, #d4e4f0 100%);
		}

		.bg {
			background-image: linear-gradient(to top, #cfd9df 0%, #e2ebf0 100%);
			border-radius: 12px;
			padding: 15px;
		}

		.bg2 {
			background-image: linear-gradient(135deg, #fdfcfb 0%, #e2d1c3 100%);
		}

		/* ===== Toggle Button ===== */
		#sidebarToggle {
			background-color: #0C2C55 !important;
			border-color: #596e89 !important;
			white-space: nowrap;
		}

		#sidebarToggle:hover {
			background-color: rgba(255, 255, 255, 0.25) !important;
		}

		/* ===== Scrollbar ===== */
		::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}

		::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 10px;
		}

		::-webkit-scrollbar-thumb {
			background: linear-gradient(135deg, #7bc49a, #3a7a55);
			border-radius: 10px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #3a7a55;
		}

		/* ===== Form Styles ===== */
		.form-container {
			background: white;
			border-radius: 16px;
			padding: 20px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
			margin-bottom: 20px;
		}

		.form-container .row {
			margin-bottom: 12px;
		}

		.form-container label {
			font-weight: 600;
			color: #1a5276;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.form-container .col-sm-3 {
			background-color: #f8f9fa;
			padding: 10px 15px;
			border-radius: 8px 0 0 8px;
			display: flex;
			align-items: center;
		}

		.form-container .col-sm-9,
		.form-container .col-sm-6,
		.form-container .col-sm-4 {
			padding: 5px 10px;
		}

		.form-container input,
		.form-container textarea,
		.form-container select {
			border-radius: 8px !important;
			border: 1px solid #ddd !important;
			padding: 8px 12px !important;
			transition: border-color 0.3s;
		}

		.form-container input:focus,
		.form-container textarea:focus,
		.form-container select:focus {
			border-color: #1a5276 !important;
			box-shadow: 0 0 0 3px rgba(26, 82, 118, 0.1) !important;
			outline: none;
		}

		.info-box {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 8px;
			margin-bottom: 10px;
		}

		.info-box p {
			margin-bottom: 5px;
		}

		.equip-image {
			width: 100%;
			max-width: 380px;
			height: auto;
			display: block;
			margin: 0 auto 1rem auto;
			border-radius: 8px;
			border: 1px solid #ddd;
			padding: 5px;
		}

		.equip-image-placeholder {
			width: 100%;
			max-width: 380px;
			height: 200px;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 1rem auto;
			border-radius: 8px;
			border: 2px dashed #ddd;
			background: #f8f9fa;
			color: #999;
			font-size: 1.2rem;
		}

		.form-row-label {
			background-color: floralwhite;
			display: flex;
			align-items: center;
		}

		.form-row-input {
			background-color: lavender;
		}

		@media (min-width: 576px) {
			.form-row-label {
				justify-content: flex-end;
				text-align: right;
			}
		}

		@media (max-width: 575.98px) {
			.form-row-label {
				justify-content: flex-start;
				text-align: left;
			}
		}

		.btn-submit-use {
			width: 100%;
			max-width: 220px;
		}

		/* ============================================================
           RESPONSIVE — ຮອງຮັບທຸກຫນ້າຈໍ
           ============================================================ */

		/* Backdrop ສຳລັບ mobile/tablet */
		#sidebarBackdrop {
			display: none;
			position: fixed;
			inset: 0;
			background: rgba(0, 0, 0, 0.45);
			z-index: 1040;
		}

		#sidebarBackdrop.show {
			display: block;
		}

		@media (max-width: 991.98px) {
			#wrapper {
				display: block;
			}

			#sidebar-wrapper {
				position: fixed;
				top: 0;
				left: 0;
				height: 100vh;
				min-height: 100vh;
				transform: translateX(-100%);
				z-index: 1045;
				overflow-y: auto;
			}

			#wrapper.sidebar-open #sidebar-wrapper {
				transform: translateX(0);
			}

			#page-content-wrapper {
				width: 100%;
				margin-left: 0;
			}

			#sidebarToggle .toggle-label {
				display: none;
			}
		}

		@media (min-width: 992px) {
			#wrapper {
				display: flex;
			}

			#sidebarBackdrop {
				display: none !important;
			}

			#wrapper.sidebar-hidden-desktop #sidebar-wrapper {
				margin-left: calc(var(--sidebar-width) * -1);
			}
		}

		@media (max-width: 768px) {
			.right {
				float: none;
				display: inline-block;
			}

			#sidebar-wrapper .sidebar-heading img {
				width: 60px !important;
			}

			.container-fluid.px-4 {
				padding-left: 0.75rem !important;
				padding-right: 0.75rem !important;
			}

			.form-container .col-sm-3 {
				border-radius: 8px 8px 0 0;
				justify-content: center;
				text-align: center;
			}

			.form-container .col-sm-9,
			.form-container .col-sm-6,
			.form-container .col-sm-4 {
				padding: 5px 0;
			}

			.bg {
				padding: 10px;
			}

			.bg img {
				width: 100% !important;
				height: auto !important;
				margin-top: 10px;
			}

			.select2-container {
				max-width: 100% !important;
			}
		}

		@media (max-width: 576px) {
			#sidebar-wrapper {
				width: 85%;
				max-width: 300px;
			}

			#sidebar-wrapper .sidebar-heading img {
				width: 50px !important;
			}

			#sidebar-wrapper .sidebar-heading .system-name {
				font-size: 15px;
			}

			.btn-success#sidebarToggle {
				padding: 0.4rem 0.6rem;
				font-size: 0.85rem;
			}

			.form-container {
				padding: 10px;
			}

			button#Submit {
				width: 100% !important;
			}

			.select2-container .select2-selection--single {
				height: 38px !important;
			}

			.select2-container--default .select2-selection--single .select2-selection__rendered {
				line-height: 38px !important;
			}

			.equip-image-placeholder {
				height: 150px;
				font-size: 1rem;
			}
		}
	</style>

	<?php
	$proo = $_SESSION["Namepro"] ?? '';
	$userId = $_SESSION["iduser"] ?? '';

	// ກຳນົດຜູ້ໃຊ້ Admin
	$adminUsers = ['404', '30', '194', '793'];
	$isAdmin = in_array($userId, $adminUsers);

	// ນັບຈຳນວນການຂໍເບິກ
	if (!$isAdmin) {
		$sql = "SELECT COUNT(a.S_Status) as 'cccount' 
                FROM request a 
                WHERE a.S_Status IN ('1','2','3') AND a.Province = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $proo);
	} else {
		$sql = "SELECT COUNT(a.S_Status) as 'cccount' 
                FROM request a 
                WHERE a.S_Status = '1'";
		$stmt = $conn->prepare($sql);
	}

	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$count = $row['cccount'] ?? 0;
	$stmt->close();
	?>
</head>

<body>
	<!-- Backdrop ສຳລັບ mobile/tablet -->
	<div id="sidebarBackdrop"></div>

	<div class="d-flex" id="wrapper">
		<!-- ===== Sidebar ສີຟ້າ ===== -->
		<div class="border-end" id="sidebar-wrapper">
			<div class="sidebar-heading border-bottom">
				<img src="image/Logo.png" class="img-fluid" alt="Logo">
				<div>
					<h2 class="system-name">ລະບົບສາງ LS</h2>
				</div>
			</div>
			<div class="list-group list-group-flush">
				<a class="list-group-item list-group-item-action p-3" href="Dasborad.php">
					<i class="fi fi-rr-hand-holding-box"></i>
					<strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong>
					<span class="badge rounded-pill">
						<?php echo $count; ?>
					</span>
				</a>
				<?php if (!in_array($_SESSION["iduser"] ?? '', ['30', '194', '204', '213', '214', '215'])): ?>
					<a class="list-group-item list-group-item-action p-3 active" href="UseStock.php">
						<i class="fa fa-tasks" aria-hidden="true"></i>
						<strong data-translate="use_equipment">ນຳໃຊ້ອຸປະກອນ</strong>
					</a>
				<?php endif; ?>
				<a class="list-group-item list-group-item-action p-3" href="follow.php">
					<i class="fi fi-sr-location-alt"></i>
					<strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
				</a>
				<a class="list-group-item list-group-item-action p-3" href="stock.php">
					<i class="fa-solid fa-cubes-stacked"></i>
					<strong data-translate="stock_equipment">ສາງອຸປະກອນ</strong>
				</a>
				<?php if (isset($_SESSION['factory']) && strcasecmp(trim($_SESSION['factory']), 'HQ') === 0): ?>
					<a class="list-group-item list-group-item-action p-3" href="tacking.php">
						<i class="fa-solid fa-truck-fast"></i>
						<strong data-translate="tacking">ຕິດຕາມການສົ່ງເຄື່ອງ</strong>
					</a>
					<a class="list-group-item list-group-item-action p-3" href="report_tacking.php">
						<i class="bi bi-bar-chart-line-fill"></i>
						<strong data-translate="report_tacking">ລາງານການສົ່ງເຄື່ອງ</strong>
					</a>
				<?php else: ?>
					<a class="list-group-item list-group-item-action p-3" href="inbox_tacking.php">
						<i class="fi fi-rs-person-dolly"></i>
						<strong data-translate="inbox_tacking">ຮັບເຄື່ອງເຂົ້າໂຮງງານ</strong>
					</a>
				<?php endif; ?>
				<div class="menu-divider"></div>
				<a class="list-group-item list-group-item-action p-3" href="http://101.78.11.238:901">
					<i class="bi bi-tools"></i>
					<strong data-translate="Rotating_equipment">ອຸປະກອນໝູນວຽນ</strong>
				</a>
				<a class="list-group-item list-group-item-action p-3" href="Report.php">
					<i class="fa fa-bar-chart" aria-hidden="true"></i>
					<strong data-translate="report">Report</strong>
				</a>
				<a class="list-group-item list-group-item-action p-3" href="picture/index.php">
					<i class="bi bi-images" aria-hidden="true"></i>
					<strong data-translate="image_stock">Image Stock</strong>
				</a>
				<br>
			</div>
		</div>

		<!-- ===== Page content wrapper ===== -->
		<div id="page-content-wrapper">

			<!-- ===== Navbar ສີຟ້າ + Logout ===== -->
			<nav class="navbar navbar-expand-lg navbar-blue border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button">
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
						<span class="toggle-label">&nbsp;Hide Menu</span>
					</button>

					<!-- ແທນທີ່ navbar-toggler-icon ດ້ວຍ Font Awesome -->
					<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<i class="fa-solid fa-bars text-white"></i>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">

							<!-- Dropdown ພາສາ -->
							<li class="nav-item dropdown">
								<a class="nav-link text-white dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="bi bi-translate fs-4"></i>
									<span class="ms-1 d-none d-lg-inline" id="currentLangText">ລາວ</span>
								</a>
								<ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="languageDropdown">
									<li>
										<a class="dropdown-item lang-option" href="#" data-lang="la" onclick="switchLanguage('la'); return false;">
											<span class="flag-icon">🇱🇦</span>
											<span class="lang-name">ພາສາລາວ</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<a class="dropdown-item lang-option active" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
											<span class="flag-icon">🇨🇳</span>
											<span class="lang-name">中文</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
								</ul>
							</li>

							<!-- Dropdown ຜູ້ໃຊ້ + Logout -->
							<li class="nav-item dropdown user-dropdown">
								<a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-user-circle"></i>&nbsp; <?= htmlspecialchars($_SESSION["user"] ?? '') ?>
								</a>
								<ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="userDropdown">
									<li><span class="dropdown-item-text"><i class="fa fa-id-badge"></i> <?= htmlspecialchars($_SESSION["user"] ?? '') ?></span></li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<a class="dropdown-item text-danger" href="logout.php">
											<i class="fas fa-sign-out-alt"></i>&nbsp; <strong>Logout</strong>
										</a>
									</li>
								</ul>
							</li>

						</ul>
					</div>
				</div>
			</nav>

			<!-- ===== ເນື້ອໃນຫຼັກ ===== -->
			<div class="container-fluid px-4 py-3 bg-soft-blue" style="min-height: 100vh;">

				<!-- ===== ຟອມເລືອກອຸປະກອນ ===== -->
				<div class="form-container">
					<form action="UseStockData.php" method="post" id="selectForm">
						<div class="row">
							<div class="col-12 text-center mb-3">
								<label for="cust_id" class="fw-bold" style="font-size: 1.1rem;">
									<i class="fa-solid fa-box"></i> ລາຍການອຸປະກອນ:
								</label>
							</div>
							<div class="col-12">
								<select class="cust_id form-select" name="Select" id="cust_id" style="color:blue; width:100%;">
									<option value="">ເລືອກລາຍການອຸປະກອນ</option>
									<?php
									$provinceee = $_SESSION["Namepro"];
									$stmt_list = $conn->prepare("
                                        SELECT * FROM stock_province
                                        WHERE Provinces LIKE ? AND Unit NOT LIKE '0'
                                        ORDER BY Items ASC
                                    ");
									$stmt_list->bind_param("s", $provinceee);
									$stmt_list->execute();
									$result_list = $stmt_list->get_result();
									while ($row_item = $result_list->fetch_assoc()) {
										$selected = (isset($_POST["Select"]) && $_POST["Select"] === $row_item['Items']) ? 'selected' : '';
										echo '<option value="' . htmlspecialchars($row_item['Items']) . '" ' . $selected . '>'
											. htmlspecialchars($row_item['Items']) . ' (ຄົງເຫຼືອ: ' . htmlspecialchars($row_item['Unit']) . ')</option>';
									}
									$stmt_list->close();
									?>
								</select>
							</div>
						</div>
					</form>
				</div>

				<?php
				if (isset($_POST["Select"]) && $_POST["Select"] !== "") {
					$provinceee = $_SESSION["Namepro"];
					$select = $_POST["Select"];

					$stmt_item = $conn->prepare("
                        SELECT * FROM stock_province
                        WHERE Provinces LIKE ? AND Items LIKE ?
                    ");
					$stmt_item->bind_param("ss", $provinceee, $select);
					$stmt_item->execute();
					$result_item = $stmt_item->get_result();

					if ($result_item->num_rows === 0) {
				?>
						<div class="alert alert-warning text-center">
							<i class="fa-solid fa-triangle-exclamation"></i>
							ບໍ່ພົບຂໍ້ມູນອຸປະກອນນີ້ໃນແຂວງຂອງທ່ານ
						</div>
					<?php
					}

					while ($row = $result_item->fetch_assoc()) {
						// ດຶງຮູບຈາກ Item_code ຜ່ານ filemap.json ແທນຄ່າ "picture" ຄ້າງໃນ DB
						// (ໂຄ້ດເກົ່າໃຊ້ file_exists() ກັບ path ດິບຈາກ DB ຊຶ່ງບໍ່ຖືກຕ້ອງອີກຕໍ່ໄປ
						// ຫຼັງຈາກລະບົບຮູບພາບປ່ຽນມາໃຊ້ຊື່ໄຟລ໌ແບບສຸ່ມ)
						$itemImageUrl = getItemImageUrl($row['Item_code'] ?? '', $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);
						$imageExists = !empty($itemImageUrl);

						// ===============================================================
						// [ແກ້ໄຂບັນຫາ] ລວມຍອດ request ຕາມ PR (OA_Out) ກ່ອນ ຄ່ອຍຫັກຍອດໃຊ້
						// ເຫດຜົນ: ຖ້າ PR ອັນດຽວກັນຖືກ request ຫຼາຍແຖວ (ຫຼາຍຄັ້ງ) ຕ້ອງລວມ Unit ຂອງທຸກແຖວ
						// ນັ້ນເຂົ້າກັນເປັນຍອດລວມຂອງ PR ນັ້ນ ກ່ອນຈະຫັກ usestock ອອກ, ບໍ່ໃຫ້ນັບການໃຊ້ຊ້ຳຫຼາຍຄັ້ງ
						//
						// [ເພີ່ມ] ດຶງ planing_number ຂອງ PR ນັ້ນມານຳ ເພື່ອສະແດງ auto-fill ໃນຟອມ
						// ===============================================================
						$stmt_oa = $conn->prepare("
						SELECT req.OA_Out,
						       MIN(req.dateRe) AS dateRe,
						       MIN(req.planing_number) AS planing_number,
						       (MIN(req.totalRequested) - COALESCE(MIN(usg.totalUsed), 0)) AS remaining
						FROM (
							SELECT OA_Out, Items, Province, dateRe, planing_number,
							       SUM(CAST(Unit AS UNSIGNED)) AS totalRequested
							FROM request
							WHERE Items LIKE ? AND Province LIKE ? AND S_Status = '4'
							GROUP BY OA_Out, Items, Province, planing_number
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
						$stmt_oa->bind_param("ss", $row['Items'], $provinceee);
						$stmt_oa->execute();
						$result_oa = $stmt_oa->get_result();
						$oaList = [];
						$oaTotalRemaining = 0;
						while ($oaRow = $result_oa->fetch_assoc()) {
							$oaList[] = $oaRow;
							$oaTotalRemaining += (int)$oaRow['remaining'];
						}
						$stmt_oa->close();
						$oaOutValue = $oaList[0]['OA_Out'] ?? '';
						// ແຜນເລກທີ່ auto-fill ຈາກ PR ໃບທຳອິດ (ຕາມລຳດັບ FIFO)
						$planingNumberAuto = $oaList[0]['planing_number'] ?? '';
						// ຈຳນວນສູງສຸດທີ່ອະນຸຍາດ = ຄ່ານ້ອຍສຸດ ລະຫວ່າງ ຍອດສາງລວມ ກັບ ຍອດລວມທີ່ PR ທຸກໃບຍັງເຫຼືອ
						$maxAllowed = min((int)$row['Unit'], $oaTotalRemaining);
					?>
						<div class="form-container bg">
							<div class="row mb-3">
								<div class="col-12">
									<h3 class="text-danger">
										<i class="fa-solid fa-database"></i>
										ສາງມີອຸປະກອນ: <?= htmlspecialchars($row['Unit']) ?> <?= htmlspecialchars($row['Type'] ?? '') ?>
									</h3>
									<hr>
									<div class="row">
										<div class="col-md-8">
											<div class="info-box">
												<p><i class="fa-solid fa-box"></i> <strong>ຊື່ອຸປະກອນ:</strong> <?= htmlspecialchars($row['Items']) ?></p>
												<p><i class="fa-solid fa-globe"></i> <strong>Vendor:</strong> <?= htmlspecialchars($row['Vendor'] ?? '-') ?></p>
												<p><i class="fa-solid fa-network-wired"></i> <strong>ພາກສ່ວນ:</strong> <?= htmlspecialchars($row['Section'] ?? '-') ?></p>
												<p><i class="fa-solid fa-code"></i> <strong>Item Code:</strong> <?= htmlspecialchars($row['Item_code'] ?? '-') ?></p>
												<p><i class="fa-solid fa-tag"></i> <strong>Type:</strong> <?= htmlspecialchars($row['Type'] ?? '-') ?></p>
											</div>
										</div>
										<div class="col-md-4 text-center">
											<?php if ($imageExists): ?>
												<a href="<?= htmlspecialchars($itemImageUrl) ?>" target="_blank">
													<img src="<?= htmlspecialchars($itemImageUrl) ?>" class="equip-image" alt="<?= htmlspecialchars($row['Items']) ?>" onerror="this.style.display='none'; this.parentElement.innerHTML=this.parentElement.innerHTML + '<div class=\'equip-image-placeholder\'><i class=\'fa-solid fa-image fa-2x me-2\'></i> ບໍ່ມີຮູບພາບ</div>';">
												</a>
											<?php else: ?>
												<div class="equip-image-placeholder">
													<i class="fa-solid fa-image fa-2x me-2"></i>
													<span>ບໍ່ມີຮູບພາບ</span>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>

							<form action="cutstock.php" id="form3" name="form3" method="post">
								<!-- ວັນທີນຳໃຊ້ -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-calendar"></i> ວັນທີນຳໃຊ້:</label>
									</div>
									<div class="col-sm-9">
										<input type="date" name="date_time" id="date_time" class="form-control" style="color:blue; background:#f0f8ff;" required value="<?= date('Y-m-d') ?>"/>
									</div>
								</div>

								<!-- ຕ້ອງການໃຊ້ ແລະ ຊື່ຜູ້ນຳໃຊ້ -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-pen-to-square"></i> ຕ້ອງການໃຊ້:</label>
									</div>
									<div class="col-sm-3">
										<input type="number" name="Unit" id="Unit" class="form-control" min="1" max="<?= htmlspecialchars($maxAllowed) ?>" style="color:blue;" required value="1" />
									</div>
									<div class="col-sm-6">
										<input type="text" name="Use_Name" id="Use_Name" class="form-control" style="color:blue;" required placeholder="ຊື່ຜູ້ນໍາໄປໃຊ້........" />
									</div>
								</div>

								<!-- ສະຖານທີ່ -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-location-dot"></i> ໃຊ້ຢູ່ສະຖາທີ່:</label>
									</div>
									<div class="col-sm-9">
										<input type="text" name="Station" id="Station" class="form-control" style="color:blue;" required placeholder="ສະຖານທີ່ທີ່ນຳໄປໃຊ້" />
									</div>
								</div>

								<!-- ຊີລຽວນາມເບີ -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-keyboard"></i> ຊີລຽວນາມເບີ:</label>
									</div>
									<div class="col-sm-9">
										<input type="text" name="serieNumber" id="serieNumber" class="form-control" style="color:blue;" required placeholder="Serial Number" />
									</div>
								</div>

								<!-- PR ເລກທີ (ແກ້ໄຂ: ສະແດງລາຍການ PR ທັງໝົດເປັນຂໍ້ມູນອ້າງອີງ, ບໍ່ໃຫ້ຜູ້ໃຊ້ພິມແກ້ໄຂ)
								     ລະບົບຈະ AUTO-SPLIT ຂ້າມ PR ໃຫ້ອັດຕະໂນມັດຢູ່ຝັ່ງ cutstock.php ຕາມ FIFO
								     ຈຶ່ງບໍ່ຈຳເປັນຕ້ອງສົ່ງ OA ຈາກຟອມນີ້ອີກຕໍ່ໄປ (server ຄິດໄລ່ໃໝ່ດ້ວຍຕົນເອງ ເພື່ອປ້ອງກັນການແກ້ໄຂຄ່າ) -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-thumbtack"></i> PR ທີ່ຈະຖືກຕັດ:</label>
									</div>
									<div class="col-sm-9">
										<?php if (count($oaList) > 0): ?>
											<div class="form-control" style="height:auto; background:#eef4fb;">
												<?php foreach ($oaList as $idx => $oa): ?>
													<span class="badge bg-secondary me-1 mb-1">
														<?= htmlspecialchars($oa['OA_Out']) ?> (ເຫຼືອ <?= htmlspecialchars($oa['remaining']) ?>)
													</span>
												<?php endforeach; ?>
											</div>
											<small class="text-muted">
												<i class="fa-solid fa-circle-info"></i> ຖ້າຈຳນວນທີ່ຂໍໃຊ້ ເກີນ PR ໃບທຳອິດ, ລະບົບຈະຕັດຂ້າມໄປ PR ໃບຕໍ່ໄປໃຫ້ອັດຕະໂນມັດ (ລວມ <?= $oaTotalRemaining ?> ໜ່ວຍ)
											</small>
										<?php else: ?>
											<input type="text" class="form-control text-danger" value="ບໍ່ພົບ PR ທີ່ຍັງເຫຼືອຈຳນວນ" readonly />
											<small class="text-danger">
												<i class="fa-solid fa-triangle-exclamation"></i> ບໍ່ມີ PR (ສະຖານະ "ສຳເລັດ") ທີ່ຍັງເຫຼືອຈຳນວນສຳລັບອຸປະກອນນີ້ — ກະລຸນາຕິດຕໍ່ຜູ້ຮັບຜິດຊອບ ຫຼື ກວດ request ໃໝ່
											</small>
										<?php endif; ?>
									</div>
								</div>

								<!-- ແຜນເລກທີ່ planing_number (ແກ້ໄຂ: auto-fill ຈາກ request.planing_number ຂອງ PR ໃບທຳອິດ, ບໍ່ໃຫ້ພິມແກ້ໄຂ) -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-ticket"></i> ແຜນເລກທີ່ :</label>
									</div>
									<div class="col-sm-9">
										<input type="text" name="planing_number" id="planing_number" class="form-control" style="color:blue; background:#eef4fb;" value="<?= htmlspecialchars($planingNumberAuto) ?>" readonly />
									</div>
								</div>

								<!-- ເລກທີ Ticket -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-ticket"></i> ເລກທີ Ticket:</label>
									</div>
									<div class="col-sm-9">
										<input type="number" name="ticket" id="ticket" class="form-control" style="color:blue;" placeholder="Ticket Number" />
									</div>
								</div>

								<!-- ໝາຍເຫດ -->
								<div class="row">
									<div class="col-sm-3">
										<label><i class="fa-regular fa-comment-pen"></i> ໝາຍເຫດ:</label>
									</div>
									<div class="col-sm-9">
										<textarea name="Remark" id="Remark" class="form-control" style="color:blue;" rows="3" placeholder="ໝາຍເຫດເພີ່ມເຕີມ..."></textarea>
									</div>
								</div>

								<!-- ປຸ່ມບັນທຶກ -->
								<div class="row mt-3">
									<div class="col-sm-8"></div>
									<div class="col-sm-4">
										<button id="Submit" style="width: 100%;" class="btn btn-success" type="submit"
											<?= count($oaList) === 0 ? 'disabled' : '' ?>
											onclick="return confirm('ຕ້ອງການຕັດ Stock ສາຂາ ແທ້ບໍ່?')">
											<i class="fa-solid fa-check"></i> ນໍາໄປໃຊ້
										</button>
									</div>
								</div>

								<!-- Hidden Fields -->
								<!-- OA ບໍ່ຖືກສົ່ງຈາກຟອມນີ້ອີກຕໍ່ໄປ - server (cutstock.php) ຄິດໄລ່ PR ທີ່ຈະຕັດເອງ ຕາມ FIFO ໃນເວລານັ້ນ -->
								<input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>" />
								<input type="hidden" name="Province" value="<?= htmlspecialchars($row['Provinces']) ?>" />
								<input type="hidden" name="Section" value="<?= htmlspecialchars($row['Section']) ?>" />
								<input type="hidden" name="Vendor" value="<?= htmlspecialchars($row['Vendor']) ?>" />
								<input type="hidden" name="Item_code" value="<?= htmlspecialchars($row['Item_code']) ?>" />
								<input type="hidden" name="Use_For" value="<?= htmlspecialchars($row['Use_For']) ?>" />
								<input type="hidden" name="Type" value="<?= htmlspecialchars($row['Type']) ?>" />
								<input type="hidden" name="Groupp" value="<?= htmlspecialchars($row['Groupp']) ?>" />
								<!-- ສົ່ງ URL ຮູບທີ່ resolve ໄດ້ໃນປັດຈຸບັນ ແທນຄ່າ "picture" ດິບຈາກ DB
								     (ຖ້າຫາຮູບບໍ່ພົບ ຈະສົ່ງຄ່າຫວ່າງເປົ່າ) -->
								<input type="hidden" name="picture" value="<?= htmlspecialchars($itemImageUrl ?? '') ?>" />
								<input type="hidden" name="Unitcenter" value="<?= htmlspecialchars($row['Unitcenter'] ?? '') ?>" />
								<input type="hidden" name="Unitpro" value="<?= htmlspecialchars($row['Unit']) ?>" />
								<input type="hidden" name="Items" value="<?= htmlspecialchars($row['Items']) ?>" />
							</form>
						</div>
					<?php
					}
					$stmt_item->close();

					// Display request data where request.Items = stock_province.Items
					$select = $_POST["Select"];
					$stmt_request = $conn->prepare("
							SELECT r.* FROM request r
							INNER JOIN stock_province sp ON r.Items = sp.Items
							WHERE r.Items LIKE ? AND r.Province LIKE ?
							ORDER BY r.dateRe DESC
						");
					$stmt_request->bind_param("ss", $select, $provinceee);
					$stmt_request->execute();
					$result_request = $stmt_request->get_result();

					if ($result_request->num_rows > 0):
					?>
						<div class="form-container">
							<h4 class="text-primary mb-3">
								<i class="fa-solid fa-clock-rotate-left"></i> ຂໍ້ມູນການຂໍຈາກ request
							</h4>
							<div class="table-responsive">
								<table class="table table-bordered table-hover" style="font-size: 13px;">
									<thead class="table-primary">
										<tr>
											<th>ລະບົບການຂໍ</th>
											<th>ຊື່ອຸປະກອນ</th>
											<th>Vendor</th>
											<th>ພາກສ່ວນ</th>
											<th>ຈຳນວນ</th>
											<th>PR ເລກທີ</th>
											<th>ສະຖານະພາບ</th>
											<th>ວັນທີຂໍ</th>
										</tr>
									</thead>
									<tbody>
										<?php while ($row_req = $result_request->fetch_assoc()): ?>
											<tr>
												<td><?= htmlspecialchars($row_req["id"]) ?></td>
												<td><?= htmlspecialchars($row_req["Items"]) ?></td>
												<td><?= htmlspecialchars($row_req["Vendor"] ?? "-") ?></td>
												<td><?= htmlspecialchars($row_req["Section"] ?? "-") ?></td>
												<td><?= htmlspecialchars($row_req["Unit"] ?? "-") ?></td>
												<td><?= htmlspecialchars($row_req["OA_Out"] ?? "-") ?></td>
												<td>
													<?php
													$status = $row_req["S_Status"] ?? "";
													$statusText = "";
													$statusClass = "";
													switch ($status) {
														case "1":
															$statusText = "ຂໍໃໝ່";
															$statusClass = "bg-warning text-dark";
															break;
														case "2":
															$statusText = "ກໍາລັງສະແດງ";
															$statusClass = "bg-danger";
															break;
														case "3":
															$statusText = "ກໍາລັງຈັດສົ່ງ";
															$statusClass = "bg-primary";
															break;
														case "4":
															$statusText = "ສຳເລັດ";
															$statusClass = "bg-success";
															break;
														default:
															$statusText = "ບໍ່ຮູ້ຈັກ";
															$statusClass = "bg-secondary";
															break;
													}
													echo '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
													?>
												</td>
												<td><?= htmlspecialchars($row_req["dateRe"] ?? "-") ?></td>
											</tr>
										<?php endwhile; ?>
									</tbody>
								</table>
							</div>
						</div>
				<?php
					endif;
					$stmt_request->close();
				}
				?>

				<?php
				if (isset($_POST["sub"])) {
					date_default_timezone_set('Asia/Bangkok');
					$date_time = date('20y-m-d G:i');
					$insertOk = true;

					for ($i = 1; $i <= (int)$_POST["hdnLine"]; $i++) {
						if (!empty($_POST["cust_id$i"])) {
							$Itemm = $_POST["cust_id$i"];

							$stmt_stock = $conn->prepare("SELECT * FROM stock WHERE Items LIKE ?");
							$stmt_stock->bind_param("s", $Itemm);
							$stmt_stock->execute();
							$test = $stmt_stock->get_result()->fetch_assoc();
							$stmt_stock->close();

							if ($test) {
								$Vendor = $test['Vendor'];
								$Item_code = $test['Item_code'];
								$Use_For = $test['Use_For'];
								$Type = $test['Type'];
								$Groupp = $test['Groupp'];
								$picture = $test['picture'];
								$Section = $test['Section'];

								$strSQL = "INSERT INTO request 
                                    (id,Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Re,Province,dateRe,OA_Out,S_Status)
                                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,'1')";
								$stmt_ins = $conn->prepare($strSQL);
								$idddDSite = $_POST["IdddDSite$i"];
								$custId = $_POST["cust_id$i"];
								$unitStock = $_POST["unitStock$i"];
								$oaOut = $_POST["OAout$i"];
								$userRe = $_SESSION["user"];
								$namePro = $_SESSION["Namepro"];
								$stmt_ins->bind_param(
									"ssssssssssssss",
									$idddDSite,
									$custId,
									$Item_code,
									$Vendor,
									$Use_For,
									$Type,
									$unitStock,
									$Groupp,
									$picture,
									$Section,
									$userRe,
									$namePro,
									$date_time,
									$oaOut
								);
								if (!$stmt_ins->execute()) {
									$insertOk = false;
									echo "Error Add [" . htmlspecialchars($stmt_ins->error) . "]";
								}
								$stmt_ins->close();
							}
						}
					}

					if ($insertOk) {
						echo "<script>alert('Import success!');</script>";
					}
				}
				?>

			</div>
		</div>
	</div>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<!-- Translate -->
	<script src="translate/lang.js"></script>

	<script>
		// ຟັງຊັນປ່ຽນພາສາ
		function switchLanguage(lang) {
			if (typeof setLanguage === 'function') {
				setLanguage(lang);
			}
			document.querySelectorAll('.lang-option').forEach(function(el) {
				el.classList.remove('active');
			});
			document.querySelector('[data-lang="' + lang + '"]')?.classList.add('active');
			var langText = lang === 'la' ? 'ລາວ' : '中文';
			document.getElementById('currentLangText').textContent = langText;
			localStorage.setItem('site_lang', lang);
		}

		// ===== Toggle Sidebar =====
		var wrapperEl = document.getElementById('wrapper');
		var sidebarBackdrop = document.getElementById('sidebarBackdrop');
		var sidebarToggleBtn = document.getElementById('sidebarToggle');

		function isDesktop() {
			return window.innerWidth >= 992;
		}

		function closeSidebar() {
			wrapperEl.classList.remove('sidebar-open');
			sidebarBackdrop.classList.remove('show');
		}

		sidebarToggleBtn?.addEventListener('click', function() {
			if (isDesktop()) {
				wrapperEl.classList.toggle('sidebar-hidden-desktop');
			} else {
				var isOpen = wrapperEl.classList.toggle('sidebar-open');
				sidebarBackdrop.classList.toggle('show', isOpen);
			}
		});

		sidebarBackdrop?.addEventListener('click', closeSidebar);

		document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(function(item) {
			item.addEventListener('click', function() {
				if (!isDesktop()) {
					closeSidebar();
				}
			});
		});

		window.addEventListener('resize', function() {
			if (isDesktop()) {
				closeSidebar();
			} else {
				wrapperEl.classList.remove('sidebar-hidden-desktop');
			}
		});

		// ===== ເມື່ອໂຫຼດໜ້າແລ້ວ =====
		document.addEventListener('DOMContentLoaded', function() {
			var currentLang = localStorage.getItem('site_lang') || 'la';
			switchLanguage(currentLang);

			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$('#cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				}).on('change', function() {
					if (this.value !== '') {
						this.form.submit();
					}
				});
			} else {
				document.getElementById('cust_id')?.addEventListener('change', function() {
					if (this.value !== '') {
						this.form.submit();
					}
				});
			}

			var unitInput = document.getElementById('Unit');
			if (unitInput) {
				var maxValue = parseInt(unitInput.getAttribute('max'));
				unitInput.addEventListener('change', function() {
					var val = parseInt(this.value);
					if (isNaN(val) || val < 1) {
						this.value = 1;
					} else if (val > maxValue) {
						alert('ຈຳນວນທີ່ປ້ອນເກີນສາງທີ່ມີ (' + maxValue + ')');
						this.value = maxValue;
					}
				});
			}
		});
	</script>

</body>

</html>