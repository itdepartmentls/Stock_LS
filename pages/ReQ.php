<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if (empty($_SESSION["user"]) || empty($_SESSION["Namepro"]) || empty($_SESSION["iduser"])) {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}

// ໝາຍເຫດ: ການດຶງຮູບພາບຈາກ Item_code (ຜ່ານ picture/uploads/filemap.json) ຖືກຍ້າຍໄປໄວ້ໃນ
// req_items_search.php ແທນ (endpoint ທີ່ Select2 AJAX ເອີ້ນໃຊ້) ເພາະໜ້ານີ້ບໍ່ໄດ້ສ້າງ
// ລາຍການ dropdown ຝັ່ງ server ອີກຕໍ່ໄປ (ເບິ່ງລາຍລະອຽດຢູ່ dropdown ອຸປະກອນລຸ່ມນີ້)
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

	<title>ຂໍເບິກອຸປະກອນ</title>

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

		.btn-danger {
			background: linear-gradient(135deg, #dc3545, #c82333) !important;
			border-color: #dc3545 !important;
			color: #fff !important;
		}

		.btn-danger:hover {
			background: linear-gradient(135deg, #c82333, #bd2130) !important;
			border-color: #bd2130 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
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

		.select2-container {
			width: 100% !important;
		}

		/* ===== ຜົນການຄົ້ນຫາ Select2 (ໄຮລ໌ໄລ໌ ແລະ ລາຍລະອຽດ) ===== */
		.select2-results__option mark {
			background: #fff3a3;
			color: #1a5276;
			padding: 0 2px;
			border-radius: 3px;
		}

		.select2-results__option small.text-muted {
			font-size: 12px;
			line-height: 1.4;
		}

		.select2-results__option {
			padding-top: 6px !important;
			padding-bottom: 6px !important;
		}

		.select2-selection__rendered img {
			display: none; /* ບໍ່ໃຫ້ຮູບໄປປົນກັບ box ຂອງຄ່າທີ່ຖືກເລືອກ */
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

		.form-container .item-row {
			background: #f8f9fa;
			padding: 15px;
			border-radius: 10px;
			margin-bottom: 15px;
			border-left: 4px solid #1a5276;
		}

		.form-container .item-row .row {
			margin-bottom: 8px;
		}

		.form-container .item-row .item-number {
			font-weight: 700;
			color: #1a5276;
			font-size: 1.1rem;
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

		.line-selector {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		.line-selector select {
			width: auto;
			min-width: 70px;
			height: 40px;
			border-radius: 8px;
			border: 1px solid #ddd;
			padding: 5px 10px;
		}

		/* ===== ຕາຕະລາງຂໍ້ມູນອຸປະກອນ (ສວຍງາມ ແລະ ສີສັນ) ===== */
		.item-details-table {
			border: none !important;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 4px 14px rgba(26, 82, 118, 0.15);
			animation: fadeInDown 0.3s ease;
		}

		.item-details-table thead tr {
			background: linear-gradient(135deg, #1a5276, #0c2c55);
		}

		.item-details-table thead th {
			color: #fff !important;
			font-size: 13px;
			font-weight: 600;
			border: none !important;
			padding: 10px 12px;
			white-space: nowrap;
		}

		.item-details-table thead th i {
			color: #f1c40f;
			margin-right: 6px;
		}

		.item-details-table tbody td {
			font-size: 13px;
			font-weight: 600;
			padding: 10px 12px;
			border: none !important;
			border-bottom: 1px solid #eef2f6 !important;
			vertical-align: middle;
		}

		.item-details-table tbody tr {
			background: #ffffff;
			transition: background 0.2s ease;
		}

		.item-details-table tbody tr:hover {
			background: #eaf3fb;
		}

		.item-details-table .item-lao {
			color: #1a5276;
		}

		.item-details-table .item-chinese {
			color: #c0392b;
		}

		.item-details-table .item-size {
			color: #7d3c98;
		}

		.item-details-table .item-model {
			color: #117864;
		}

		.item-details-table .item-qty {
			color: #b9770e;
			font-weight: 700;
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

			.form-container .item-row {
				padding: 10px;
			}

			.form-container .row>div {
				padding: 5px 0;
			}

			.line-selector {
				flex-direction: column;
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

			.form-container .item-row .row {
				flex-direction: column;
			}

			.form-container .item-row .row>div {
				width: 100%;
				padding: 3px 0;
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

	// ກຳນົດຄ່າ Line
	$line = isset($_GET["Line"]) ? (int)$_GET["Line"] : 1;
	if ($line < 1) $line = 1;
	if ($line > 15) $line = 15;
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
				<a class="list-group-item list-group-item-action p-3 active" href="Dasborad.php">
					<i class="fi fi-rr-hand-holding-box"></i>
					<strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong>
					<span class="badge rounded-pill">
						<?php echo $count; ?>
					</span>
				</a>
				<?php if (!in_array($_SESSION["iduser"] ?? '', ['30', '194', '204', '213', '214', '215'])): ?>
					<a class="list-group-item list-group-item-action p-3" href="UseStock.php">
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

				<div class="form-container">
					<!-- ===== ເລືອກຈຳນວນລາຍການ ===== -->
					<div class="line-selector mb-4">
						<strong><i class="fa-solid fa-list"></i> ຕ້ອງການເພີ່ມລາຍການອຸປະກອນ:</strong>
						<select class="form-select" style="width:auto; min-width:70px; display:inline-block;" onchange="window.location='ReQ.php?Line='+this.value;">
							<?php for ($i = 1; $i <= 15; $i++): ?>
								<option value="<?= $i ?>" <?= ($line == $i) ? 'selected' : '' ?>>
									<?= $i ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>

					<!-- ===== ຟອມຂໍເບິກ ===== -->
					<form action="ReQ.php" name="form1" method="post">

						<!-- ===== ແຂວງ (Province) - ດຶງອັດຕະໂນມັດຈາກ Session, ໃຊ້ຄັ້ງດຽວທັງຟອມ ===== -->
						<div class="item-row">
							<div class="row">
								<div class="col-12">
									<label for="ProvinceDisplay" class="fw-bold">
										<i class="fa-solid fa-map-marker-alt"></i> ສຳນັກງານໃຫຍ່/ໂຮງງານ:
									</label>
									<input type="text" id="ProvinceDisplay" class="form-control" style="color:blue; background:#eef2f6;" value="<?= htmlspecialchars($_SESSION['Namepro'] ?? '') ?>" readonly />
									<input type="hidden" name="ProvinceForm" value="<?= htmlspecialchars($_SESSION['Namepro'] ?? '') ?>">
								</div>
							</div>
						</div>

						<?php for ($i = 1; $i <= $line; $i++): ?>
							<div class="item-row">
								<div class="row">
									<div class="col-12">
										<span class="item-number"><i class="fa-solid fa-hashtag"></i> ລາຍການທີ <?= $i ?></span>
										<hr class="my-2">
									</div>
								</div>

								<!-- ເລືອກອຸປະກອນ -->
								<div class="row">
									<div class="col-12">
										<label for="cust_id<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-box"></i> ລາຍການອຸປະກອນ: ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model
										</label>
										<!-- ✅ ບໍ່ຝັງລາຍການອຸປະກອນທັງໝົດເປັນ <option> ອີກຕໍ່ໄປ (ກ່ອນໜ້ານີ້ ຖ້າມີສິນຄ້າຫຼາຍພັນລາຍການ
										     ຈະຖືກຄູນດ້ວຍ 15 ຊ່ອງ ເຮັດໃຫ້ໜ້າໜັກ ແລະ Select2 ຄົ້ນຫາຊ້າ) ແທນທີ່ຈະເປັນແບບນັ້ນ
										     ໃຫ້ Select2 ດຶງລາຍການຜ່ານ AJAX (req_items_search.php) ສະເພາະທີ່ກົງກັບຄຳຄົ້ນຫາເທົ່ານັ້ນ -->
										<select class="cust_id related-post form-select" name="cust_id<?= $i ?>" id="cust_id<?= $i ?>" style="color:blue; width:100%;">
											<option value="">ເລືອກອຸປະກອນ...</option>
										</select>
										
										<!-- ຕາຕະລາງຂໍ້ມູນອຸປະກອນ -->
										<table class="table table-sm table-bordered item-details-table mt-2" id="itemDetails<?= $i ?>" style="display:none;">
											<thead>
												<tr>
													<th><i class="fi fi-rr-flag"></i> ຊື່ພາສາລາວ</th>
													<th><i class="fi fi-rr-globe"></i> ຊື່ພາສາຈີນ</th>
													<th><i class="fa-solid fa-ruler-combined"></i> ຂະຫນາດ</th>
													<th><i class="fa-solid fa-tag"></i> Model</th>
													<th><i class="fa-solid fa-cubes"></i> ຈຳນວນ</th>
													<th><i class="fa-solid fa-shapes"></i> ຫົວໜ່ວຍ</th> 
													<th><i class="fa-solid fa-image"></i> picture</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td class="item-lao">-</td>
													<td class="item-chinese">-</td>
													<td class="item-size">-</td>
													<td class="item-model">-</td>
													<td class="item-qty">-</td>
													<td class="item-type">-</td>
													<!-- ເນື້ອໃນຕາຕະລາງແຖວນີ້ຖືກເຊື່ອງໄວ້ ແລະຈະຖືກ JS ຂຽນທັບດ້ວຍ data-picture
													     ຂອງ option ທີ່ຖືກເລືອກ (ເບິ່ງ showItemDetails() ດ້ານລຸ່ມ) ຈຶ່ງບໍ່ຈຳເປັນ
													     ໃສ່ຄ່າຮູບຢູ່ນີ້ -->
													<td class="item-picture">-</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>

								<!-- ຈຳນວນ ແລະ PR ເລກທີ -->
								<div class="row mt-2">
									<div class="col-12 col-sm-1">
										<label for="unitStock<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-hashtag"></i> ຈຳນວນ:
										</label>
										<input type="number" name="unitStock<?= $i ?>" id="unitStock<?= $i ?>" class="form-control" style="color:blue;" required min="1" step="0.01" value="1" />
									</div>
									<div class="col-12 col-sm-2">
										<label for="OAout<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> PR ເລກທີ:
										</label>
										<input type="text" name="OAout<?= $i ?>" id="OAout<?= $i ?>" class="form-control" style="color:blue;" required placeholder="PR ເລກທີ" />
									</div>
									<div class="col-12 col-sm-2">
										<label for="planing_number<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> ຂື້ນແຜນ ເລກທີ່:
										</label>
										<input type="text" name="planing_number<?= $i ?>" id="planing_number<?= $i ?>" class="form-control" style="color:blue;" required placeholder="ຂື້ນແຜນ ເລກທີ່" />
									</div>
									<div class="col-12 col-sm-3">
										<label for="requirements<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> ຄວາມຕ້ອງການໃຊ້:
										</label>
										<input type="text" name="requirements<?= $i ?>" id="requirements<?= $i ?>" class="form-control" style="color:blue;" required placeholder="ຄວາມຕ້ອງການໃຊ້" />
									</div>
									<div class="col-12 col-sm-2">
										<label for="Re_requi_persoin<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> ຜູ້ຂໍ / Applicant:
										</label>
										<input type="text" name="Re_requi_persoin<?= $i ?>" id="Re_requi_persoin<?= $i ?>" class="form-control" style="color:blue;" required placeholder="ຄວາມຕ້ອງການໃຊ້" />
									</div>
									<div class="col-12 col-sm-2">
										<label for="STAFF_ID<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> ລະຫັດພະນັກງານ / STAFF ID:
										</label>
										<input type="text" name="STAFF_ID<?= $i ?>" id="STAFF_ID<?= $i ?>" class="form-control" style="color:blue;" required placeholder="ລະຫັດພະນັກງານ" />
									</div>
									<div class="col-12 col-sm-9">
										<label for="Remark_ReQui<?= $i ?>" class="fw-bold">
											<i class="fa-solid fa-file-invoice"></i> ໝາຍເຫດ / Remark:
										</label>
										<input type="text" name="Remark_ReQui<?= $i ?>" id="Remark_ReQui<?= $i ?>" class="form-control" style="color:blue;" required placeholder="ໝາຍເຫດ" />
									</div>
								</div>
							</div>
						<?php endfor; ?>

						<!-- ===== ປຸ່ມບັນທຶກ ===== -->
						<div class="text-center mt-3">
							<button type="submit" id="submit" name="sub[]" class="btn btn-success btn-lg px-5">
								<i class="fa fa-refresh" aria-hidden="true"></i> ຂໍເບິກອຸປະກອນ
							</button>
							<input type="hidden" name="hdnLine" value="<?= $line ?>">
						</div>
					</form>
				</div>

				<?php
				// ===== ການບັນທຶກຂໍ້ມູນ =====
				if (isset($_POST["sub"])) {
					error_log("DEBUG hdnLine=" . $_POST["hdnLine"] . " POST=" . print_r($_POST, true));
					date_default_timezone_set('Asia/Bangkok');
					$date_time = date('20y-m-d G:i');
					$insertOk = true;

					// ✅ Prepare statements ຄັ້ງດຽວນອກ loop (ແທນທີ່ຈະ prepare/close ຊ້ຳທຸກລາຍການ)
					// ຊ່ວຍຫຼຸດຈຳນວນຮອບການສື່ສານກັບ MySQL server ລົງຫຼາຍ ໂດຍສະເພາະເມື່ອຂໍເບິກຫຼາຍລາຍການພ້ອມກັນ
					// ແລະປ່ຽນ "LIKE" ເປັນ "=" ສຳລັບການຄົ້ນຫາຊື່ອຸປະກອນແບບກົງກັນ (ໄວກວ່າ ແລະ ໃຊ້ index ໄດ້ດີກວ່າ)
					$stmt_stock = $conn->prepare("SELECT * FROM stock WHERE Items = ?");
					$strSQL = "INSERT INTO request 
						(Items,Item_code,Vendor,Use_For,Type,Unit,Groupp,picture,Section,User_Re,Province,dateRe,OA_Out,planing_number,Requirements,Re_requi_persoin,STAFF_ID,Remark_ReQui,S_Status)
						VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'1')";
					$stmt_ins = $conn->prepare($strSQL);
					$stmt_upd_prov = $conn->prepare("UPDATE stockinput SET Province = ? WHERE Items = ?");

					if ($stmt_stock === false || $stmt_ins === false || $stmt_upd_prov === false) {
						$insertOk = false;
						echo '<div class="alert alert-danger">Prepare Error [' . htmlspecialchars($conn->error) . ']</div>';
					} else {
						// ✅ ຫໍ່ທຸກ INSERT/UPDATE ຢູ່ໃນ transaction ດຽວ ແທນທີ່ຈະ autocommit ທຸກຄຳສັ່ງ
						// (autocommit ແຕ່ລະຄຳສັ່ງເຮັດໃຫ້ MySQL ຕ້ອງ fsync ລົງ disk ທຸກເທື່ອ ຊ້າຫຼາຍເມື່ອມີຫຼາຍລາຍການ)
						$conn->begin_transaction();

						for ($i = 1; $i <= $_POST["hdnLine"]; $i++) {
							error_log("DEBUG line $i => cust_id" . $i . " = " . ($_POST["cust_id$i"] ?? '[ບໍ່ມີ]'));

							if (!empty($_POST["cust_id$i"])) {
								$Itemm = $_POST["cust_id$i"];

								$stmt_stock->bind_param("s", $Itemm);
								$stmt_stock->execute();
								$test = $stmt_stock->get_result()->fetch_assoc();

								error_log("DEBUG line $i => stock ພົບບໍ່: " . ($test ? "ພົບ (Item_code=" . $test['Item_code'] . ")" : "❌ ບໍ່ພົບ!"));

								if ($test) {
									$Vendor    = $test['Vendor'];
									$Item_code = $test['Item_code'];
									$Use_For   = $test['Use_For'];
									$Type      = $test['Type'];
									$Groupp    = $test['Groupp'];
									$picture   = $test['picture'];
									$Section   = $test['Section'];

									$custId        = $_POST["cust_id$i"];
									$unitStock     = $_POST["unitStock$i"];
									$oaOut         = $_POST["OAout$i"];
									$planingNumber = $_POST["planing_number$i"];
									$requirements  = $_POST["requirements$i"] ?? '';
									$Re_requi_persoin  = $_POST["Re_requi_persoin$i"] ?? '';
									$STAFF_ID  = $_POST["STAFF_ID$i"] ?? '';
									$Remark_ReQui = $_POST["Remark_ReQui$i"] ?? '';
									$userRe        = $_SESSION["user"];
									$namePro       = $_SESSION["Namepro"];

									$stmt_ins->bind_param(
										"ssssssssssssssssss",
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
										$oaOut,
										$planingNumber,
										$requirements,
										$Re_requi_persoin,
										$STAFF_ID,
										$Remark_ReQui

									);

									if (!$stmt_ins->execute()) {
										$insertOk = false;
										echo '<div class="alert alert-danger">Error Add [' . htmlspecialchars($stmt_ins->error) . ']</div>';
									}

									// ✅ ອັບເດດ Province ໃນ table stockinput ຂອງລາຍການ (Items) ນີ້
									$stmt_upd_prov->bind_param("ss", $namePro, $custId);
									if (!$stmt_upd_prov->execute()) {
										echo '<div class="alert alert-danger">Error Update Province [' . htmlspecialchars($stmt_upd_prov->error) . ']</div>';
									}
								}
							}
						}

						$conn->commit();

						$stmt_stock->close();
						$stmt_ins->close();
						$stmt_upd_prov->close();
					}

					if ($insertOk) {
						echo '<script>
							alert("ຂໍເບິກສຳເລັດ!");
							window.location = "Dasborad.php";
						</script>';
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

			// ✅ ເລີ່ມ Select2 ທຸກອັນ ດ້ວຍໂໝດ AJAX (ຄົ້ນຫາຜ່ານ server ແທນທີ່ຈະຝັງທຸກລາຍການໄວ້ໃນໜ້າ)
			// ກ່ອນໜ້ານີ້ ທຸກ dropdown (15 ອັນ) ຈະມີ <option> ຄົບທຸກລາຍການໃນ stock ຄືກັນໝົດ,
			// ຖ້າມີສິນຄ້າຫຼາຍພັນລາຍການ ຈະກາຍເປັນ DOM ຫຼາຍພັນ x 15 ແລະ Select2 ຕ້ອງແກະຄົ້ນຫາທຸກ
			// ຄັ້ງທີ່ພິມ (matcher function) ເຮັດໃຫ້ຊ້າຫຼາຍ. ຕອນນີ້ Select2 ຈະດຶງສະເພາະລາຍການ
			// ທີ່ກົງກັບຄຳຄົ້ນຫາ (ສູງສຸດ 40 ລາຍການ) ຜ່ານ req_items_search.php ແທນ.
			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				// ສະແດງລາຍລະອຽດ (ລາວ / ຈີນ / ຂະຫນາດ / Model) ໃນລາຍການຄົ້ນຫາ
				function formatItem(item) {
					if (!item.id) {
						return item.text;
					}

					var lao      = item.lao || item.text || '';
					var chinese  = item.chinese || '';
					var size     = item.size || '';
					var model    = item.model || '';
					var picture  = item.picture || '';

					var $row = $('<div></div>').css({
						display: 'flex',
						alignItems: 'center',
						gap: '10px'
					});

					// ຮູບຕົວຢ່າງ (ຫຼືກ່ອງເປົ່າຖ້າບໍ່ມີຮູບ ເພື່ອໃຫ້ແຖວຄົງລະດັບກັນ)
					if (picture) {
						$('<img>')
							.attr('src', picture)
							.css({
								width: '40px',
								height: '40px',
								objectFit: 'cover',
								borderRadius: '6px',
								flexShrink: 0,
								border: '1px solid #ddd'
							})
							.appendTo($row);
					} else {
						$('<div></div>').css({
							width: '40px',
							height: '40px',
							borderRadius: '6px',
							flexShrink: 0,
							background: '#f1f1f1',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							color: '#bbb',
							fontSize: '16px'
						}).html('<i class="fa-solid fa-image"></i>').appendTo($row);
					}

					var $wrapper = $('<div></div>');
					$('<div></div>').addClass('fw-bold').text(lao).appendTo($wrapper);

					var parts = [];
					if (chinese) parts.push($('<span></span>').text(chinese).html());
					if (size) parts.push('ຂະຫນາດ: ' + $('<span></span>').text(size).html());
					if (model) parts.push('Model: ' + $('<span></span>').text(model).html());

					if (parts.length) {
						$('<small></small>').addClass('text-muted d-block')
							.html(parts.join(' &nbsp;|&nbsp; '))
							.appendTo($wrapper);
					}

					$wrapper.appendTo($row);

					return $row;
				}

				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%',
					minimumInputLength: 0,
					ajax: {
						url: 'req_items_search.php',
						dataType: 'json',
						delay: 200,
						data: function(params) {
							return { q: params.term || '' };
						},
						processResults: function(data) {
							return { results: data.results || [] };
						},
						cache: true
					},
					templateResult: formatItem,
					templateSelection: function(item) {
						return item.lao || item.text || '';
					},
					escapeMarkup: function(markup) {
						return markup; // formatItem ໄດ້ escape ໃຫ້ແລ້ວ
					}
				});
			}

			document.querySelector('form[name="form1"]')?.addEventListener('submit', function(e) {
				var btn = document.getElementById('submit');
				// ໃຫ້ browser submit ຟອມກ່ອນ ຈຶ່ງຄ່ອຍ disable ປຸ່ມ (ຫຼັງຈາກນັ້ນເລັກນ້ອຍ)
				setTimeout(function() {
					btn.disabled = true;
					btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ກຳລັງດຳເນີນການ...';
				}, 0);
			});

			// ===== ສະແດງຕາຕະລາງຂໍ້ມູນອຸປະກອນ ເມື່ອເລືອກລາຍການ =====
			// ໝາຍເຫດ: ຕອນນີ້ຂໍ້ມູນມາຈາກ Select2 AJAX result (e.params.data) ໂດຍກົງ
			// ບໍ່ໄດ້ອ່ານຈາກ data-* attribute ຂອງ <option> ອີກຕໍ່ໄປ (ເພາະບໍ່ມີ option ຝັງໄວ້ລ່ວງໜ້າແລ້ວ)
			function showItemDetails(selectEl, item) {
				var id = selectEl.id.replace('cust_id', ''); // ເລກລຳດັບ ເຊັ່ນ 1, 2, 3
				var table = document.getElementById('itemDetails' + id);
				var oaInputField = document.getElementById('OAout' + id); // field PR ເລກທີ

				if (!table) return;

				if (!item || !selectEl.value) {
					table.style.display = 'none';
					if (oaInputField) oaInputField.value = ''; // ລ້າງຄ່າ ຖ້າຍົກເລີກການເລືອກ
					return;
				}

				var lao      = item.lao || '-';
				var chinese  = item.chinese || '-';
				var size     = item.size || '-';
				var model    = item.model || '-';
				var typeUnit = item.type || '-';
				var qty      = item.qty || '-';
				var picture  = item.picture || '';
				var oaInput  = item.oa || '';

				table.querySelector('.item-lao').textContent      = lao;
				table.querySelector('.item-chinese').textContent  = chinese;
				table.querySelector('.item-size').textContent     = size;
				table.querySelector('.item-model').textContent    = model;
				table.querySelector('.item-type').textContent     = typeUnit; 
				table.querySelector('.item-qty').textContent      = qty;
				table.querySelector('.item-picture').innerHTML    = picture ? '<img src="' + picture + '" style="width:128px;height:100px;object-fit:cover;border-radius:8px;">' : '-';

				// ✅ ໃສ່ຄ່າ OA (ຈາກ stockinput.OA ທີ່ join ຕາມ Items = $laoName) ລົງ field PR ເລກທີ ອັດຕະໂນມັດ
				if (oaInputField) {
					oaInputField.value = oaInput;
				}

				table.style.display = '';
			}

			// ຮອງຮັບທັງ select ທຳມະດາ ແລະ select2 (ໃຊ້ jQuery event ຖ້າມີ)
			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$(document).on('select2:select', '.cust_id', function(e) {
					showItemDetails(this, e.params.data);
				});
				$(document).on('select2:clear', '.cust_id', function() {
					showItemDetails(this, null);
				});
			} else {
				document.querySelectorAll('.cust_id').forEach(function(sel) {
					sel.addEventListener('change', function() {
						showItemDetails(this, null);
					});
				});
			}
		});
	</script>

</body>

</html>