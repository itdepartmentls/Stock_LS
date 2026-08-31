<?php
require_once __DIR__ . '/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<link rel="shortcut icon" href="image/favicons.png">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

	<!-- ===== Bootstrap 5 CSS ===== -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

	<!-- ===== Font Awesome 6 ===== -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

	<!-- ===== Select2 CSS ===== -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

	<!-- ===== jQuery (ເຕັມ ບໍ່ໃຊ້ slim) ===== -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- ===== Bootstrap 5 JS Bundle ===== -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- ===== Select2 JS ===== -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="js/pro.min.js">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

	<title>ສາງອຸປະກອນ</title>

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

		/* ===== ສີຫຼັກເປັນສີຂຽວ ===== */
		:root {
			--bs-primary: #198754;
			--bs-primary-rgb: 25, 135, 84;
			--bs-success: #198754;
			--sidebar-width: 280px;
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

		/* ===== Sidebar ສີຂຽວ ===== */
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
			background: rgba(0, 0, 0, 0.15) !important;
			border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
			color: #fff !important;
			font-weight: 600;
			padding: 1rem 1.25rem;
			display: flex;
			align-items: center;
			gap: 10px;
		}

		#sidebar-wrapper .list-group-item {
			background: transparent !important;
			color: #fff !important;
			border: none;
			border-radius: 0;
			padding: 0.75rem 1.25rem;
			transition: all 0.25s ease;
			font-weight: 500;
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}

		#sidebar-wrapper .list-group-item:hover {
			background: rgba(255, 255, 255, 0.15) !important;
			transform: translateX(6px);
			color: #fff !important;
		}

		#sidebar-wrapper .list-group-item .badge {
			background-color: #ffc107;
			color: #000;
			margin-left: auto;
		}

		#sidebar-wrapper .list-group-item.active {
			background: rgba(255, 255, 255, 0.2) !important;
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

		/* ===== Navbar ສີຂຽວ ===== */
		.navbar-green {
			background: linear-gradient(135deg, #0C2C55 0%, #0C2C55 100%) !important;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
			border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
			flex-wrap: wrap;
		}

		.navbar-green .nav-link,
		.navbar-green .navbar-brand,
		.navbar-green .navbar-text {
			color: #fff !important;
		}

		.navbar-green .nav-link:hover {
			color: rgba(255, 255, 255, 0.8) !important;
		}

		.navbar-green .btn-success {
			background-color: rgba(255, 255, 255, 0.2) !important;
			border-color: rgba(255, 255, 255, 0.3) !important;
			color: #fff !important;
		}

		.navbar-green .btn-success:hover {
			background-color: rgba(255, 255, 255, 0.3) !important;
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

		/* ===== ປຸ່ມ ===== */
		.btn-success {
			background-color: #0C2C55 !important;
			border-color: #0C2C55 !important;
			color: #fff !important;
		}

		.btn-success:hover {
			background: linear-gradient(135deg, #0c2c55, #315580) !important;
			border-color: #0c2c55 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(26, 82, 118, 0.4);
			color: #fff !important;
		}

		.btn-primary {
			background-color: #0C2C55 !important;
			border-color: #0C2C55 !important;
		}

		.btn-primary:hover {
			background-color: #0A1F3F !important;
			border-color: #0A1F3F !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
		}

		.btn-secondary {
			background-color: #6c757d;
			border-color: #6c757d;
		}

		.btn-secondary:hover {
			background-color: #5a6268;
			border-color: #545b62;
			transform: translateY(-2px);
		}

		/* ===== Form Container ===== */
		.form-container {
			background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
			padding: 20px 25px;
			border-radius: 16px;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
			border: 1px solid rgba(25, 135, 84, 0.1);
			margin: 15px 0 25px 0;
			transition: all 0.3s ease;
		}

		.form-container:hover {
			box-shadow: 0 6px 30px rgba(25, 135, 84, 0.08);
			border-color: rgba(25, 135, 84, 0.2);
		}

		.form-container .form-label {
			font-size: 18px;
			font-weight: 600;
			color: #0C2C55;
			margin-bottom: 4px;
			letter-spacing: 0.3px;
			display: flex;
			align-items: center;
			gap: 4px;
		}

		.form-container .form-select-sm,
		.form-container .form-control-sm {
			font-size: 13px;
			padding: 6px 12px;
			border-radius: 8px;
			border: 1.5px solid #e0e0e0;
			transition: all 0.3s ease;
			background-color: #ffffff;
			color: #2d3436;
			width: 100%;
		}

		.form-container .form-select-sm:focus,
		.form-container .form-control-sm:focus {
			border-color: #315580;
			box-shadow: 0 0 0 3px rgba(50, 90, 143, 0.15);
			outline: none;
		}

		.form-container .btn-sm {
			padding: 6px 16px;
			font-size: 13px;
			font-weight: 600;
			border-radius: 8px;
			transition: all 0.3s ease;
			letter-spacing: 0.3px;
		}

		.form-container .btn-sm:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
		}

		.form-container .d-flex.gap-1 {
			gap: 6px;
		}

		/* ===== Toggle Button ===== */
		#sidebarToggle {
			background-color: #0C2C55 !important;
			border-color: #596e89 !important;
			color: #fff !important;
			white-space: nowrap;
		}

		#sidebarToggle:hover {
			background-color: rgba(255, 255, 255, 0.25) !important;
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
			background: #198754;
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

		/* ===== Logout button ===== */
		.logout-btn {
			color: #fff !important;
			font-weight: 600;
			transition: color 0.3s;
		}

		.logout-btn:hover {
			color: #ffc107 !important;
		}

		/* ===== iframe ===== */
		iframe {
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
			border: 1px solid #e9ecef;
			transition: box-shadow 0.3s;
			border-radius: 14px;
			background: #fff;
			width: 100%;
			max-width: 100%;
			display: block;
		}

		iframe:hover {
			box-shadow: 0 6px 30px rgba(0, 0, 0, 0.10);
		}

		/* ===== Page Content ===== */
		#page-content-wrapper {
			flex: 1;
			min-width: 0;
			width: 100%;
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
			background: linear-gradient(135deg, #198754, #146c43);
			border-radius: 10px;
		}

		::-webkit-scrollbar-thumb:hover {
			background: #146c43;
		}

		/* ============================================================
		   RESPONSIVE — ຮອງຮັບທຸກຫນ້າຈໍ
		   Desktop (>=992px): sidebar ຄົງທີ່ຢູ່ຂ້າງໆເນື້ອໃນ
		   Tablet/Mobile (<992px): sidebar ກາຍເປັນ off-canvas drawer
		   ============================================================ */

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

			/* ===== ຟອມຄົ້ນຫາ: ວາງແຖວລະໜຶ່ງຊ່ອງ, ເຕັມຄວາມກວ້າງ ===== */
			.form-container .row.g-2>.col-auto {
				width: 100%;
				flex: 0 0 100%;
			}

			.form-container .form-select-sm,
			.form-container .form-control-sm {
				width: 100% !important;
				min-width: unset !important;
			}

			.form-container .d-flex.gap-1 {
				flex-wrap: wrap;
				justify-content: stretch;
				width: 100%;
			}

			.form-container .d-flex.gap-1 .btn-sm {
				flex: 1;
				min-width: 80px;
				text-align: center;
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

			.container-fluid.px-4 {
				padding-left: 0.75rem !important;
				padding-right: 0.75rem !important;
			}

			.form-container {
				padding: 16px 14px;
			}

			.form-container .form-label {
				font-size: 15px;
			}

			iframe {
				min-height: 70vh;
			}
		}

		@media (max-width: 576px) {
			#sidebar-wrapper {
				width: 85%;
				max-width: 300px;
			}

			.btn-success#sidebarToggle {
				padding: 0.4rem 0.6rem;
				font-size: 0.85rem;
			}

			iframe {
				min-height: 65vh;
				border-radius: 10px;
			}
		}
	
		/* Equipment search suggestions */
		.equipment-suggestions { position: absolute; top: 100%; left: 0; right: 0; z-index: 1080; max-height: 310px; overflow-y: auto; background: #fff; border: 1px solid #adb5bd; border-top: 0; border-radius: 0 0 4px 4px; box-shadow: 0 8px 16px rgba(0, 0, 0, .14); }
		.equipment-suggestion { padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #edf0f2; line-height: 1.25; display: flex; align-items: center; gap: 10px; }
		.equipment-suggestion:last-child { border-bottom: 0; }
		.equipment-suggestion:hover, .equipment-suggestion.is-active { background: #e8f1ff; }
		.equipment-suggestion-thumb { width: 44px; height: 44px; flex-shrink: 0; border-radius: 6px; object-fit: cover; border: 1px solid #e5e7eb; background: #f8f9fa; }
		.equipment-suggestion-thumb-placeholder { width: 44px; height: 44px; flex-shrink: 0; border-radius: 6px; border: 1px solid #e5e7eb; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #b8bfc7; font-size: 18px; }
		.equipment-suggestion-body { flex: 1; min-width: 0; }
		.equipment-suggestion-name { font-weight: 700; color: #1f2937; }
		.equipment-suggestion-chinese, .equipment-suggestion-meta { font-size: 12px; color: #6b7280; margin-top: 2px; }
		.equipment-suggestion-meta span + span::before { content: '|'; margin: 0 7px; color: #9ca3af; }
	</style>

	<?php
	// PHP 8: ໃຊ້ Null Coalescing Operator
	$proo = $_SESSION["Namepro"] ?? '';
	$userId = $_SESSION["iduser"] ?? '';

	// ກຳນົດລາຍການຜູ້ໃຊ້ທີ່ມີສິດ Admin
	$adminUsers = ['404', '30', '194', '213', '2', '41', '214', '215'];
	$isAdmin = in_array($userId, $adminUsers);

	// ໃຊ້ Prepared Statement ປ້ອງກັນ SQL Injection
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
		<!-- ===== Sidebar ສີຂຽວ ===== -->
		<div class="border-end" id="sidebar-wrapper">
			<?php include __DIR__ . '/sidebar.php'; ?>
		</div>

		<!-- ===== Page content wrapper ===== -->
		<div id="page-content-wrapper">

			<!-- ===== Navbar ສີຂຽວ + Logout ===== -->
			<nav class="navbar navbar-expand-lg navbar-green border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button">
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
						<span class="toggle-label">&nbsp;Hide Menu</span>
					</button>

					<!-- ແທນທີ່ navbar-toggler-icon ດ້ວຍ Font Awesome -->
					<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<!-- <i class="fas fa-bars text-white"></i> -->
						<!-- ຫຼື ໃຊ້ fa-solid -->
						<i class="fa-solid fa-bars text-white"></i>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">

							<!-- ===== Dropdown ພາສາ ===== -->
							<li class="nav-item dropdown">
								<a class="nav-link text-white dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="bi bi-translate fs-4"></i>
									<span class="ms-1 d-none d-lg-inline" id="currentLangText">ລາວ</span>
								</a>
								<ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="languageDropdown">
									<li>
										<a class="dropdown-item lang-option active" href="#" data-lang="la" onclick="switchLanguage('la'); return false;">
											<span class="flag-icon">🇱🇦</span>
											<span class="lang-name">ພາສາລາວ</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
									<li>
										<hr class="dropdown-divider">
									</li>
									<li>
										<a class="dropdown-item lang-option" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
											<span class="flag-icon">🇨🇳</span>
											<span class="lang-name">中文</span>
											<i class="bi bi-check-circle-fill check-icon"></i>
										</a>
									</li>
								</ul>
							</li>

							<!-- ===== ປຸ່ມ Upload (ສຳລັບຜູ້ດູແລລະບົບ) ===== -->
							<?php if (($_SESSION["iduser"] ?? '') == "204"): ?>
								<li class="nav-item me-2">
									<a href="Uploadstock_hr_snk.php" class="btn btn-light btn-sm">
										<i class="fa fa-upload"></i> Upload Stock
									</a>
								</li>
							<?php endif; ?>

							<!-- ===== Dropdown ຜູ້ໃຊ້ ===== -->
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

			<!-- ===== Page content ===== -->
			<div class="container-fluid px-4 py-3">

				<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '213', '41', '214', '215'])): ?>

					<!-- ===== Upload Button ===== -->
					<div class="mt-2 mb-3">
						<a href="Uploadstock.php">
							<button type="button" name="button" class="btn btn-success">
								<i class="fa fa-upload"></i> Upload Stock
							</button>
						</a>
					</div>

					<!-- ===== Search Form ===== -->
					<div class="form-container">
						<form id="form122" name="form122" method="post" action="allstock.php" target="allstock">
							<div class="row g-2 align-items-end">
								<!-- ສາງ -->
								<div class="col-auto">
									<label class="form-label">
										<i class="bi bi-building"></i> <span data-translate="warehouse">ສາງ</span>
									</label>
									<select name="province" id="province" class="form-select form-select-sm" style="min-width: 150px; border-color: #0C2C55;">
										<option value="Stock Sanakham" data-translate="warehouse_main">ສາງໃຫຍ່</option>
										<option value="Vientiane" data-translate="warehouse_hr_main">ສາງ HR ສຳນັກງານໃຫຍ່</option>
										<option value="Sanakham" data-translate="warehouse_sanakham">ສາງ Sanakham</option>
										<option value="HR-Sanakham" data-translate="warehouse_hr_sanakham">ສາງ HR Sanakham</option>
										<option value="Luangphabang" data-translate="warehouse_lpb">ສາງ Luangphabang</option>
										<option value="HR Luangphabang" data-translate="warehouse_hr_lpb">ສາງ HR Luangphabang</option>
									</select>
								</div>

								<!-- ພາກສ່ວນ -->
								<div class="col-auto">
									<label class="form-label">
										<i class="bi bi-diagram-3"></i> <span data-translate="section">ພາກສ່ວນ</span>
									</label>
									<select class="form-select form-select-sm" name="Section" id="Section" style="min-width: 150px; border-color: #0C2C55;">
										<option value="ທຸກພາກສ່ວນ" data-translate="all_sections">ທຸກພາກສ່ວນ</option>
										<?php
										$resultww = mysqli_query($conn, "SELECT DISTINCT a.Section as 'Section' FROM stock a ;");
										while ($row = mysqli_fetch_assoc($resultww)) {
											echo '<option value="' . htmlspecialchars($row['Section']) . '">' . htmlspecialchars($row['Section']) . '</option>';
										}
										?>
									</select>
								</div>

								<!-- ໃຊ້ກັບກຸ່ມ -->
								<div class="col-auto">
									<label class="form-label">
										<i class="bi bi-people"></i> <span data-translate="group_use">ໃຊ້ກັບກຸ່ມ</span>
									</label>
									<select class="form-select form-select-sm" name="SectionUseGroup" id="SectionUseGroup" style="min-width: 150px; border-color: #0C2C55;">
										<option value="" data-translate="group_use">ໃຊ້ກັບກຸ່ມ</option>
										<?php
										$sql = "SELECT DISTINCT a.Groupp AS Groupp FROM stock a WHERE a.Groupp IS NOT NULL AND a.Groupp != ''";
										$result = $conn->query($sql);
										if ($result->num_rows > 0) {
											while ($row = $result->fetch_assoc()) {
												echo '<option value="' . htmlspecialchars($row['Groupp']) . '">' . htmlspecialchars($row['Groupp']) . '</option>';
											}
										}
										?>
									</select>
								</div>

								<!-- ຈຳນວນ -->
								<div class="col-auto">
									<label class="form-label">
										<i class="bi bi-hash"></i> <span data-translate="quantity">ຈຳນວນ</span>
									</label>
									<select name="resutl" id="resutl" class="form-select form-select-sm" style="min-width: 160px; border-color: #0C2C55;">
										<option value="all" data-translate="all">ທັງຫມົດ</option>
										<option value="=0">ເທົ່າ (= 0)</option>
										<option value="<= 5">ນ້ອຍກວ່າ 5 (<= 5)</option>
										<option value=">= 5">ຫຼາຍກວ່າ 5 (>= 5)</option>
										<option value="<= 10">ນ້ອຍກວ່າ 10 (<= 10)</option>
										<option value=">= 10">ຫຼາຍກວ່າ 10 (>= 10)</option>
										<option value="<= 20">ນ້ອຍກວ່າ 20 (<= 20)</option>
										<option value=">= 20">ຫຼາຍກວ່າ 20 (>= 20)</option>
									</select>
								</div>

								<!-- ຄົ້ນຫາອຸປະກອນ -->
								<div class="col-auto flex-grow-1">
									<label class="form-label">
										<i class="bi bi-search"></i> <span data-translate="equipment_name">ຄົ້ນຫາອຸປະກອນ: ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model</span>
									</label>
									<input type="text" name="input" data-equipment-search class="form-control form-control-sm"
										style="border-color: #0C2C55; min-width: 180px;"
										placeholder="ຄົ້ນຫາອຸປະກອນ..." />
								</div>

								<!-- ===== ປຸ່ມທັງໝົດ ===== -->
								<div class="col-auto">
									<div class="d-flex gap-1" style="padding-bottom: 2px;">
										<button type="submit" name="buttonpro" class="btn btn-success btn-sm px-3">
											<i class="fa fa-search"></i> Submit
										</button>
										<a href="Stockview.php" target="_blank" class="btn btn-secondary btn-sm px-3">
											<i class="fa-duotone fa-print"></i> ພິມ
										</a>
										<a href="templateStock.php" class="btn btn-success btn-sm px-3" style="background-color: #1d6f42 !important; border-color: #1d6f42 !important;">
											<i class="bi bi-file-earmark-excel"></i> Excel
										</a>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ===== iframe ===== -->
					<iframe class="circle" src="allstock.php" name="allstock" height="550px" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>

				<?php else: ?>

					<!-- ===== ປຸ່ມເພີ່ມສິນຄ້າ / Upload (Manual, ສະເພາະ user ຝ່າຍແຂວງ) =====
					     ລິ້ງໄປໜ້າ Uploadstock_province.php ເຊິ່ງມີແຕ່ຟອມ "ເພີ່ມສິນຄ້າ" ແບບ manual
					     (ບໍ່ຮອງຮັບ CSV). ຂໍ້ມູນທີ່ເພີ່ມຈະຖືກບັນທຶກລົງ stock (master) + log ລົງ stockinput
					     ຄືກັນກັບຝັ່ງ admin (Uploadstock.php) ==== -->
					<div class="mt-2 mb-3">
						<a href="Uploadstock_province.php">
							<button type="button" name="button" class="btn btn-success">
								<i class="fa fa-plus-circle"></i> ເພີ່ມສິນຄ້າໃໝ່
							</button>
						</a>
					</div>

					<!-- ===== Search Form (ຜູ້ໃຊ້ທົ່ວໄປ) ===== -->
					<div class="form-container mt-2">
						<form id="form121299" name="form121299" method="post" action="prostock.php" target="prostock">
							<div class="row g-2 align-items-end">
								<div class="col-auto">
									<label class="form-label"><strong>ສາງ</strong></label>
									<select name="province" class="form-select form-select-sm" style="min-width: 150px; border-color: #0C2C55;">

										<option value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>"><?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>
										<option value="Stock_HQ" data-translate="warehouse_main">ສາງໃຫຍ່</option>
										<option value="Sanakham" data-translate="warehouse_sanakham">ສາງ Sanakham</option>
										<option value="Luangphabang" data-translate="warehouse_lpb">ສາງ Luangphabang</option>

										</option>
									</select>
								</div>

								<div class="col-auto">
									<label class="form-label"><strong>ພາກສ່ວນ</strong></label>
									<select class="form-select form-select-sm" name="Section" id="Section" style="min-width: 150px; border-color: #0C2C55;">
										<option value="ທຸກພາກສ່ວນ">ທຸກພາກສ່ວນ</option>
										<?php
										$stmtww = $conn->prepare("SELECT DISTINCT a.Section as 'Section' FROM stock_province a where a.Provinces = ?");
										$stmtww->bind_param("s", $_SESSION["Namepro"]);
										$stmtww->execute();
										$resultww = $stmtww->get_result();
										while ($row = mysqli_fetch_assoc($resultww)) {
											echo '<option value="' . htmlspecialchars($row['Section']) . '">' . htmlspecialchars($row['Section']) . '</option>';
										}
										$stmtww->close();
										?>
									</select>
								</div>

								<div class="col-auto">
									<label class="form-label"><strong>ໃຊ້ກັບກຸ່ມ</strong></label>
									<select class="form-select form-select-sm" name="SectionUseGroup" id="SectionUseGroup" style="min-width: 150px; border-color: #0C2C55;">
										<option value="">ໃຊ້ກັບກຸ່ມ</option>
										<?php
										$stmtGroup = $conn->prepare("SELECT DISTINCT a.Groupp as 'Groupp' FROM stock_province a WHERE a.Provinces = ? AND a.Groupp IS NOT NULL AND a.Groupp != ''");
										$stmtGroup->bind_param("s", $_SESSION["Namepro"]);
										$stmtGroup->execute();
										$resultGroup = $stmtGroup->get_result();
										while ($row = mysqli_fetch_assoc($resultGroup)) {
											echo '<option value="' . htmlspecialchars($row['Groupp']) . '">' . htmlspecialchars($row['Groupp']) . '</option>';
										}
										$stmtGroup->close();
										?>
									</select>
								</div>

								<div class="col-auto">
									<label class="form-label"><strong>ຈຳນວນ</strong></label>
									<select name="resutl" id="resutl" class="form-select form-select-sm" style="min-width: 160px; border-color: #0C2C55;">
										<option value="all">ທັງຫມົດ</option>
										<option value="=0">ເທົ່າ (= 0)</option>
										<option value="<= 5">ນ້ອຍກວ່າ 5 (<= 5)</option>
										<option value=">= 5">ຫຼາຍກວ່າ 5 (>= 5)</option>
										<option value="<= 10">ນ້ອຍກວ່າ 10 (<= 10)</option>
										<option value=">= 10">ຫຼາຍກວ່າ 10 (>= 10)</option>
										<option value="<= 20">ນ້ອຍກວ່າ 20 (<= 20)</option>
										<option value=">= 20">ຫຼາຍກວ່າ 20 (>= 20)</option>
									</select>
								</div>

								<div class="col-auto flex-grow-1">
									<label class="form-label"><strong data-translate="equipment_name">ຄົ້ນຫາອຸປະກອນ: ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model</strong></label>
									<input type="text" name="input" data-equipment-search class="form-control form-control-sm"
										style="border-color: #198754; min-width: 180px;"
										placeholder="ຄົ້ນຫາຊື່, ຂະໜາດ ຫຼື Model..." />
								</div>

								<div class="col-auto">
									<div class="d-flex gap-1" style="padding-bottom: 2px;">
										<button type="submit" name="buttonpro" id="buttonpro" class="btn btn-success btn-sm px-3" onclick="form121299.action='prostock.php'; return true;">
											<i class="fa fa-search"></i> Submit
										</button>
										<button type="submit" name="buttonpro" id="buttonpro" onclick="form121299.action='printPro.php';" class="btn btn-secondary btn-sm px-3">
											<i class="fa-duotone fa-print"></i>
										</button>
										<button type="submit" name="buttonpro" id="buttonpro" onclick="form121299.action='templatePro.php';" class="btn btn-success btn-sm px-3" style="background-color: #1d6f42 !important; border-color: #1d6f42 !important;">
											<i class="fa-sharp fa-solid fa-file-excel"></i>
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<!-- ===== iframe ===== -->
					<iframe class="circle" src="prostock.php" name="prostock" height="550px" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
					</iframe>

				<?php endif; ?>

			</div>
		</div>
	</div>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<!-- Translate -->
	<script src="translate/lang.js"></script>

	<script>
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

		document.addEventListener('DOMContentLoaded', function() {
			var currentLang = localStorage.getItem('site_lang') || 'la';
			switchLanguage(currentLang);

			// ກວດສອບ Select2
			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				});
			}
		});

		function resizeIframe(obj) {
			function doResize() {
				try {
					var h = obj.contentWindow.document.documentElement.scrollHeight;
					if (h && h > 0) {
						obj.style.height = h + 'px';
					}
				} catch (e) {
					// ຖ້າ iframe ບໍ່ສາມາດເຂົ້າເຖິງໄດ້
				}
			}
			doResize();
			try {
				var imgs = obj.contentWindow.document.images;
				for (var i = 0; i < imgs.length; i++) {
					if (!imgs[i].complete) {
						imgs[i].addEventListener('load', doResize);
						imgs[i].addEventListener('error', doResize);
					}
				}
			} catch (e) {}
		}

		// ===== Toggle Sidebar (ຮອງຮັບທັງ desktop ແລະ mobile/tablet) =====
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

		// ປິດ sidebar ເມື່ອກົດທີ່ backdrop (mobile/tablet)
		sidebarBackdrop?.addEventListener('click', closeSidebar);

		// ປິດ sidebar ອັດຕະໂນມັດເມື່ອກົດເມນູ ຢູ່ mobile/tablet
		document.querySelectorAll('#sidebar-wrapper .list-group-item').forEach(function(item) {
			item.addEventListener('click', function() {
				if (!isDesktop()) {
					closeSidebar();
				}
			});
		});

		// ຖ້າປັບຂະໜາດຈໍຈາກ mobile -> desktop ໃຫ້ລ້າງ state ຂອງ mobile drawer
		window.addEventListener('resize', function() {
			if (isDesktop()) {
				closeSidebar();
			} else {
				wrapperEl.classList.remove('sidebar-hidden-desktop');
			}
		});
	
		// Search equipment by name and show its type, size and model before submit.
		(function () {
			function escapeHtml(value) {
				var el = document.createElement('div');
				el.textContent = value || '';
				return el.innerHTML;
			}

			function initialiseEquipmentSearch(input) {
				var parent = input.parentElement;
				parent.style.position = 'relative';
				var list = document.createElement('div');
				list.className = 'equipment-suggestions d-none';
				parent.appendChild(list);
				var items = [], activeIndex = -1, timer, controller;

				function closeList() { list.classList.add('d-none'); activeIndex = -1; }
				function selectItem(item) { input.value = item.name; closeList(); input.focus(); }
				function render() {
					list.innerHTML = '';
					items.forEach(function (item, index) {
						var row = document.createElement('div');
						row.className = 'equipment-suggestion' + (index === activeIndex ? ' is-active' : '');
						var thumbHtml = item.image
							? '<img class="equipment-suggestion-thumb" src="' + escapeHtml(item.image) + '" alt="" loading="lazy" onerror="this.outerHTML=\'<div class=&quot;equipment-suggestion-thumb-placeholder&quot;><i class=&quot;bi bi-image&quot;></i></div>\'">'
							: '<div class="equipment-suggestion-thumb-placeholder"><i class="bi bi-image"></i></div>';
						row.innerHTML = thumbHtml +
							'<div class="equipment-suggestion-body">' +
							'<div class="equipment-suggestion-name">' + escapeHtml(item.name) + '</div>' +
							(item.chinese_name ? '<div class="equipment-suggestion-chinese">' + escapeHtml(item.chinese_name) + '</div>' : '') +
							'<div class="equipment-suggestion-meta"><span>ປະເພດ: ' + escapeHtml(item.type) + '</span><span>ຂະໜາດ: ' + escapeHtml(item.size) + '</span><span>Model: ' + escapeHtml(item.model) + '</span></div>' +
							'</div>';
						row.addEventListener('mousedown', function (event) { event.preventDefault(); selectItem(item); });
						list.appendChild(row);
					});
					list.classList.toggle('d-none', !items.length);
				}
				function loadSuggestions() {
					if (controller) controller.abort();
					controller = new AbortController();
					var warehouse = input.closest('form').querySelector('[name="province"]');
					var url = 'stock_suggestions.php?q=' + encodeURIComponent(input.value) + '&warehouse=' + encodeURIComponent(warehouse ? warehouse.value : '');
					fetch(url, { signal: controller.signal })
						.then(function (response) { return response.ok ? response.json() : { results: [] }; })
						.then(function (data) { items = data.results || []; activeIndex = -1; render(); })
						.catch(function (error) { if (error.name !== 'AbortError') closeList(); });
				}

				input.addEventListener('focus', loadSuggestions);
				input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(loadSuggestions, 180); });
				input.addEventListener('keydown', function (event) {
					if (event.key === 'Escape') { closeList(); return; }
					if (!items.length) return;
					if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
						event.preventDefault();
						activeIndex = event.key === 'ArrowDown' ? (activeIndex + 1) % items.length : (activeIndex - 1 + items.length) % items.length;
						render();
					} else if (event.key === 'Enter' && activeIndex >= 0) {
						event.preventDefault(); selectItem(items[activeIndex]);
					}
				});
				document.addEventListener('mousedown', function (event) { if (!parent.contains(event.target)) closeList(); });
			}

			function start() { document.querySelectorAll('[data-equipment-search]').forEach(initialiseEquipmentSearch); }
			if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
		})();
	</script>

</body>

</html>