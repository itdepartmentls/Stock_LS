<?php
require_once __DIR__ . '/conn.php';
@session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<link rel="shortcut icon" href="image/logoETL.jpg">
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

		/* ===== ສີຫຼັກ ===== */
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

		/* ===== Sidebar ===== */
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

		#sidebar-wrapper .sidebar-heading img {
			width: 80px !important;
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

		/* ===== Navbar ===== */
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
	</style>

	<?php
	$proo = $_SESSION["Namepro"] ?? '';
	$userId = $_SESSION["iduser"] ?? '';

	if ($userId == "51" || $userId == "1" || $userId == "404" || $userId == "43") {
		$sql = "SELECT COUNT(a.Team_fixed) as 'cccount'
                FROM followequment a
                WHERE Team_fixed ='TSC ສະໜັບສະໜູນເຕັກນິກ'";
	} elseif ($userId == '190' || $userId == '197' || $userId == '718') {
		$sql = "SELECT COUNT(a.Team_fixed) as 'cccount'
                FROM followequment a
                WHERE Team_fixed ='Km21'";
	} elseif ($userId == '41' || $userId == '378' || $userId == '387' || $userId == '514' || $userId == '423') {
		$sql = "SELECT COUNT(a.Team_fixed) as 'cccount'
                FROM followequment a
                WHERE Team_fixed ='Internet'";
	} elseif ($userId == '177' || $userId == '180' || $userId == '181' || $userId == '487' || $userId == '686') {
		$sql = "SELECT COUNT(a.Team_fixed) as 'cccount'
                FROM followequment a
                WHERE Team_fixed ='Power Supply'";
	} elseif ($userId == '166' || $userId == '167' || $userId == '168' || $userId == '624') {
		$sql = "SELECT COUNT(a.Team_fixed) as 'cccount'
                FROM followequment a
                WHERE Team_fixed ='Transmission'";
	} elseif ($userId == '835' || $userId == '836' || $userId == '837') {
		$sql = "SELECT COUNT(a.User_TMD<>'') as 'cccount'
                FROM followequment a
                WHERE a.User_TMD <> '' and Date_Goto is null";
	} else {
		$sql = "SELECT 0 as 'cccount'";
	}

	mysqli_set_charset(@$conn, "utf8");
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$count = $row['cccount'] ?? 0;
	?>

</head>

<body>
	<!-- Backdrop ສຳລັບ mobile/tablet -->
	<div id="sidebarBackdrop"></div>

	<div class="d-flex" id="wrapper">
		<!-- ===== Sidebar ===== -->
		<div class="border-end" id="sidebar-wrapper">
			<div class="sidebar-heading border-bottom">
				<img src="image/logoETL.jpg" class="img-fluid" alt="ETL" width="80">
				<span>ລະບົບສາງ LS</span>
			</div>
			<div class="list-group list-group-flush">

				<a class="list-group-item list-group-item-action p-3" href="center.php">
					<i class="fa-regular fa-location-check"></i>
					<strong>ຕິດຕາມ ອຸປະກອນ</strong>
					<span class="badge rounded-pill"><?php echo $count; ?></span>
				</a>

				<a class="list-group-item list-group-item-action p-3 active" href="stock2.php">
					<i class="fa-solid fa-cubes-stacked"></i>
					<strong>ສາງອຸປະກອນ</strong>
				</a>

				<a class="list-group-item list-group-item-action p-3" href="report2.php">
					<i class="fa fa-bar-chart" aria-hidden="true"></i>
					<strong>Report</strong>
				</a>
				<br>
			</div>
		</div>

		<!-- ===== Page content wrapper ===== -->
		<div id="page-content-wrapper">

			<!-- ===== Navbar ===== -->
			<nav class="navbar navbar-expand-lg navbar-green border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button">
						<i class="fa fa-eye-slash" aria-hidden="true"></i>
						<span class="toggle-label">&nbsp;Hide Menu</span>
					</button>

					<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
						aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						<i class="fa-solid fa-bars text-white"></i>
					</button>

					<div class="collapse navbar-collapse" id="navbarSupportedContent">
						<ul class="navbar-nav ms-auto mt-2 mt-lg-0">

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

				<!-- ===== Search Form ===== -->
				<div class="form-container">
					<form id="form122" name="form122" method="post" action="allstock2.php" target="allstock2">
						<div class="row g-2 align-items-end">

							<!-- ສາງ -->
							<div class="col-auto">
								<label class="form-label"><i class="bi bi-building"></i> ສາງ</label>
								<select name="province" id="province" class="form-select form-select-sm" style="min-width: 150px; border-color: #0C2C55;">
									<option value="Stock Sanakham">ສາງໃຫຍ່</option>
									<option value="Vientiane">ສາງ HR ສຳນັກງານໃຫຍ່</option>
									<option value="Sanakham">ສາງ Sanakham</option>
									<option value="HR-Sanakham">ສາງ HR Sanakham</option>
									<option value="Luangphabang">ສາງ Luangphabang</option>
									<option value="HR Luangphabang">ສາງ HR Luangphabang</option>
								</select>
							</div>

							<!-- ພາກສ່ວນ -->
							<div class="col-auto">
								<label class="form-label"><i class="bi bi-diagram-3"></i> ພາກສ່ວນ</label>
								<select class="form-select form-select-sm" name="Section" id="Section" style="min-width: 150px; border-color: #0C2C55;">
									<option value="ທຸກພາກສ່ວນ">ທຸກພາກສ່ວນ</option>
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
								<label class="form-label"><i class="bi bi-people"></i> ໃຊ້ກັບກຸ່ມ</label>
								<select class="form-select form-select-sm" name="SectionUseGroup" id="SectionUseGroup" style="min-width: 150px; border-color: #0C2C55;">
									<option value="">ໃຊ້ກັບກຸ່ມ</option>
									<?php
									$sqlGroup = "SELECT DISTINCT a.Groupp AS Groupp FROM stock a WHERE a.Groupp IS NOT NULL AND a.Groupp != ''";
									$resultGroup = $conn->query($sqlGroup);
									if ($resultGroup && $resultGroup->num_rows > 0) {
										while ($rowGroup = $resultGroup->fetch_assoc()) {
											echo '<option value="' . htmlspecialchars($rowGroup['Groupp']) . '">' . htmlspecialchars($rowGroup['Groupp']) . '</option>';
										}
									}
									?>
								</select>
							</div>

							<!-- ຈຳນວນ -->
							<div class="col-auto">
								<label class="form-label"><i class="bi bi-hash"></i> ຈຳນວນ</label>
								<select name="resutl" id="resutl" class="form-select form-select-sm" style="min-width: 160px; border-color: #0C2C55;">
									<option value="all">ທັງຫມົດ</option>
									<option value="=0">ເທົ່າ (= 0)</option>
									<option value="<= 5">ນ້ອຍກວ່າຫຼືເທົ່າ 5 (<= 5)</option>
									<option value=">= 5">ຫຼາຍກວ່າຫຼືເທົ່າ 5 (>= 5)</option>
									<option value="<= 10">ນ້ອຍກວ່າຫຼືເທົ່າ 10 (<= 10)</option>
									<option value=">= 10">ຫຼາຍກວ່າຫຼືເທົ່າ 10 (>= 10)</option>
									<option value="<= 20">ນ້ອຍກວ່າຫຼືເທົ່າ 20 (<= 20)</option>
									<option value=">= 20">ຫຼາຍກວ່າຫຼືເທົ່າ 20 (>= 20)</option>
								</select>
							</div>

							<!-- ຊື່ ອຸປະກອນ -->
							<div class="col-auto flex-grow-1">
								<label class="form-label"><i class="bi bi-search"></i> ຊື່ ອຸປະກອນ</label>
								<input type="text" name="input" id="input" class="form-control form-control-sm" style="border-color: #0C2C55; min-width: 180px;" />
							</div>

							<!-- ===== ປຸ່ມທັງໝົດ ===== -->
							<div class="col-auto">
								<div class="d-flex gap-1" style="padding-bottom: 2px;">
									<button type="submit" name="buttonpro" class="btn btn-success btn-sm px-3">
										<i class="fa fa-search"></i> Submit
									</button>
									<a href="Stockview.php" target="_blank" class="btn btn-secondary btn-sm px-3">
										<i class="fa-duotone fa-print"></i>
									</a>
									<a href="templateStock.php" class="btn btn-success btn-sm px-3" style="background-color: #1d6f42 !important; border-color: #1d6f42 !important;">
										<i class="fa fa-file-excel-o" aria-hidden="true"></i>
									</a>
								</div>
							</div>

						</div>
					</form>
				</div>

				<!-- ===== iframe ===== -->
				<iframe class="circle" src="allstock2.php" name="allstock2" height="850px" width="100%" frameborder="0" scrolling="yes" onload="resizeIframe(this)">
				</iframe>

			</div>
		</div>
	</div>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
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
	</script>

</body>

</html>