<?php
// Report.php

require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
	exit;
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
	<link href="css/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

	<!-- ===== Font Awesome 6 ===== -->
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

	<!-- ===== Select2 CSS ===== -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

	<!-- ===== jQuery (ເຕັມ ບໍ່ໃຊ້ slim) ===== -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- ===== Bootstrap 5 JS Bundle ===== -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

	<!-- ===== Select2 JS ===== -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

	<!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

	<!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="css/vendor/uicons/uicons-solid-rounded.css">
	<link rel="stylesheet" href="css/vendor/uicons/uicons-regular-rounded.css">
	<link rel="stylesheet" href="css/vendor/uicons/uicons-regular-straight.css">

	<title>ການລາຍງານ</title>

	<!-- Core theme CSS -->
	<link href="css/styles.css" rel="stylesheet" />

	<link rel="stylesheet" href="css/pages/Report.css">

	<script type="text/javascript">
		$(document).ready(function() {
			if (typeof $.fn.select2 !== 'undefined') {
				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				});
			}
		});
	</script>

	<?php
	// PHP 8: ໃຊ້ Null Coalescing Operator
	$proo = $_SESSION["Namepro"] ?? '';
	$userId = $_SESSION["iduser"] ?? '';

	// ກຳນົດລາຍການຜູ້ໃຊ້ທີ່ມີສິດ Admin
	$adminUsers = ['404', '30', '2', '194', '793', '213', '214'];
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
			<?php include __DIR__ . '/../includes/sidebar.php'; ?>
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

				<!-- ===== Tabs ===== -->
				<div class="tab-custom">
					<button class="tablinks active" onclick="openCity(event, 'Userstock')" id="defaultOpen">
						<i class="fa-duotone fa-arrows-rotate"></i>
						<strong data-translate="use_of_equipment">ການນຳໃຊ້ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'HubEqument')">
						<i class="fa-duotone fa-cubes-stacked"></i>
						<strong data-translate="receive_equipment">ການຮັບ ອຸປະກອນເຂົ້າສາງ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'BerkStock')">
						<i class="fa-duotone fa-cart-shopping"></i>
						<strong data-translate="withdraw_equipment">ເບິກ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'follow')">
						<i class="fa-duotone fa-location-crosshairs"></i>
						<strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
					</button>
					<button class="tablinks" onclick="openCity(event, 'reportTotal')">
						<i class="fa-duotone fa-chart-pie"></i>
						<strong data-translate="report_summary">ສະຫຼຸບການລາຍງານ</strong>
					</button>
				</div>

				<!-- ===== Tab 1: ການນຳໃຊ້ອຸປະກອນ ===== -->
				<div id="Userstock" class="tabcontent-custom active">
					<div class="form-container">
						<form id="form1" name="form1" method="post" action="ReportUseStock.php" target="ReportUseStock">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '2', '194', '793', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i> <strong data-translate="warehouse">ສາງ</strong></strong>
										<select name="Pro" class="form-control">
											<option value="" data-translate="all_warehouses">ທັງໝົດສາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT usestock.Provinces from usestock;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Provinces']) . '">' . htmlspecialchars($row['Provinces']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php endif; ?>

								<label>
									<strong data-translate="equipment_list">ລາຍການອຸປະກອນ</strong>
									<select class="cust_id related-post form-control" name="Equment" style="color:#198754; min-width:250px;">
										<option value="" data-translate="all">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from usestock where usestock.Provinces like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> <strong data-translate="from_date">ແຕ່ວັນທີ່</strong></strong>
									<input type="date" name="start" id="start" class="form-control" style="min-width:130px;" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> <strong data-translate="to_date">ຫາວັນທີ່</strong></strong>
									<input type="date" name="end" id="end" class="form-control" style="min-width:130px;" />
								</label>

								<label>
									<button type="submit" name="button" class="btn btn-primary" onclick="form1.action='ReportUseStock.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="buttonpro" id="buttonpro" onclick="form1.action='ReportUseStock.php';" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
								<label>
									<button type="submit" name="chartUser" id="chartUser" onclick="form1.action='ReportUseStock.php';" class="btn btn-secondary">
										<i class="fa-duotone fa-chart-mixed"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe class="circle" src="ReportUseStock.php" name="ReportUseStock" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 2: ການຮັບອຸປະກອນເຂົ້າສາງ ===== -->
				<div id="HubEqument" class="tabcontent-custom">
					<div class="form-container">
						<form id="form2" name="form2" method="post" action="ReportHubStockData.php" target="ReportHubStockData">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດສາງ</option>
											<option value="Center">ສູນກາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT stock_provinceinput.Provinces from stock_provinceinput;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Provinces']) . '">' . htmlspecialchars($row['Provinces']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ລາຍການຮັບອຸປະກອນ</strong>
									<select class="cust_id related-post form-control" name="Equment1" style="color:#198754; min-width:250px;">
										<option value="">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '199', '208', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from stock;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from stock_provinceinput where stock_provinceinput.Provinces like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonhub" class="btn btn-primary" onclick="form2.action='ReportHubStockData.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="buttonproHUb" id="buttonproHUb" onclick="form2.action='ReportHubStockData.php';" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
								<label>
									<button type="submit" name="chartUserhub" id="chartUserhub" onclick="form2.action='ReportHubStockData.php';" class="btn btn-secondary">
										<i class="fa-duotone fa-chart-mixed"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe class="circle" src="ReportHubStockData.php" name="ReportHubStockData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 3: ເບິກອຸປະກອນ ===== -->
				<div id="BerkStock" class="tabcontent-custom">
					<div class="form-container">
						<form id="form33" name="form33" method="post" action="ReporberkData.php" target="ReporberkData">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດແຂວງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Province']) . '">' . htmlspecialchars($row['Province']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ລາຍການອຸປະກອນເບີກ</strong>
									<select class="cust_id related-post form-control" name="Equment1" style="color:#198754; min-width:250px;">
										<option value="">ທັງໝົດ</option>
										<?php
										$namePro = $_SESSION["Namepro"] ?? '';
										if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '2', '793', '199', '208', '213', '214'])) {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from request;");
										} else {
											$result = mysqli_query($conn, "SELECT DISTINCT Items from request where request.Province like '$namePro';");
										}
										while ($row = mysqli_fetch_assoc($result)) {
											echo '<option value="' . htmlspecialchars($row['Items']) . '">' . htmlspecialchars($row['Items']) . '</option>';
										}
										?>
									</select>
								</label>

								<label>
									<strong>ປະເພດວັນທີ່</strong>
									<select name="Choi" class="form-control">
										<option value="dateRe">ວັນທີ່ຂໍເບິກ</option>
										<option value="DateComfirm">ວັນທີ່ເບິກ</option>
									</select>
								</label>

								<label>
									<strong>ສະຖານະ</strong>
									<select name="ChoiRemark" class="form-control">
										<option value="">.....</option>
										<option value="2">ຍັງບໍ່ໄດ້ຮັບ</option>
										<option value="3">ໄດ້ຖືກຍົກເລີກ</option>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonberk" class="btn btn-primary" onclick="form33.action='ReporberkData.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="BerkStock" id="BerkStock" onclick="form33.action='ReporberkData.php'; return true;" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe src="ReporberkData.php" name="ReporberkData" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 4: ຕິດຕາມອຸປະກອນ ===== -->
				<div id="follow" class="tabcontent-custom">
					<div class="form-container">
						<form id="form34" name="form34" method="post" action="reportFollow.php" target="reportFollow">
							<div class="form-group-inline">
								<?php if (in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '195', '793', '2', '213', '214'])): ?>
									<label>
										<strong><i class="fa-thin fa-list-dropdown"></i>ສາງ</strong>
										<select name="Pro" class="form-control">
											<option value="all">ທັງໝົດສາງ</option>
											<?php
											$result = mysqli_query($conn, "SELECT DISTINCT request.Province from request;");
											while ($row = mysqli_fetch_assoc($result)) {
												echo '<option value="' . htmlspecialchars($row['Province']) . '">' . htmlspecialchars($row['Province']) . '</option>';
											}
											?>
										</select>
									</label>
								<?php else: ?>
									<input type="hidden" name="Pro" value="<?= htmlspecialchars($_SESSION["Namepro"] ?? '') ?>" />
								<?php endif; ?>

								<label>
									<strong>ສະຖານະ</strong>
									<select name="Choi" class="form-control" required>
										<option value="">---ກະລຸນາເລືອກ---</option>
										<option value="ສ້ອມແປງ">ສ້ອມແປງ</option>
										<option value="ຍົກຍ້າຍ">ຍົກຍ້າຍ</option>
										<option value="ຖອນ">ຖອນ</option>
									</select>
								</label>

								<label>
									<strong>ສະຖານະ(<span style="color: red;">ສະເພາະຢືມເຄື່ອງ</span>)</strong>
									<select name="stt" class="form-control">
										<option value="all">---ທຸກສະຖານະ---</option>
										<option value="ໃຊ້ງານໄດ້">ໃຊ້ງານໄດ້</option>
										<option value="ຕາຍ">ຕາຍ</option>
									</select>
								</label>

								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ແຕ່ວັນທີ່</strong>
									<input type="date" name="start" id="start" required class="form-control" />
								</label>
								<label>
									<strong><i class="fa-duotone fa-calendar-days"></i> ຫາວັນທີ່</strong>
									<input type="date" name="end" id="end" required class="form-control" />
								</label>

								<label>
									<button type="submit" name="buttonFollow" class="btn btn-primary" onclick="form34.action='reportFollow.php'; return true;">
										<i class="fa fa-search" aria-hidden="true"></i> ຄົ້ນຫາ
									</button>
								</label>
								<label>
									<button type="submit" name="FollowStock" id="FollowStock" onclick="form34.action='reportFollow.php'; return true;" class="btn btn-success">
										<i class="fa-sharp fa-solid fa-file-excel"></i>
									</button>
								</label>
							</div>
						</form>
					</div>

					<div align="center">
						<iframe src="reportFollow.php" name="reportFollow" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
						</iframe>
					</div>
				</div>

				<!-- ===== Tab 5: ສະຫຼຸບການລາຍງານ (Dashboard 4 ຖັນ) ===== -->
				<div id="reportTotal" class="tabcontent-custom">
					<div class="summary-wrap">

						<!-- ປຸ່ມເລືອກແຂວງ/ສາງ (ດຶງ dynamic ຈາກ request.Province) -->
						<div class="summary-provinces" id="summaryProvinces">
							<?php
							$provResult = mysqli_query($conn, "SELECT DISTINCT Province FROM request WHERE Province IS NOT NULL AND Province <> '' ORDER BY Province ASC;");
							$firstProv = true;
							while ($provRow = mysqli_fetch_assoc($provResult)) {
								$provName = $provRow['Province'];
								$activeClass = $firstProv ? ' active' : '';
								echo '<button type="button" class="prov-btn' . $activeClass . '" data-province="' . htmlspecialchars($provName) . '" onclick="loadSummary(this)">' . htmlspecialchars($provName) . '</button>';
								$firstProv = false;
							}
							?>
						</div>

						<!-- 4 ຖັນ: ຂໍເບິກ / ສັ່ງເຄື່ອງ / ຮັບເຄື່ອງ / ນຳໃຊ້ -->
						<div class="summary-columns">
							<?php
							$summaryColumns = [
								['key' => 'request', 'title' => 'ຂໍເບິກ',    'sub' => 'ຈຳນວນເບິກ (ຕາມ Item)'],
								['key' => 'order',   'title' => 'ສົ່ງເຄື່ອງ', 'sub' => 'ຈຳນວນສົ່ງ (ຕາມ Item)'],
								['key' => 'receive', 'title' => 'ຮັບເຄື່ອງ', 'sub' => 'ຈຳນວນຮັບເຄື່ອງ (ຕາມ Item)'],
								['key' => 'use',      'title' => 'ນຳໃຊ້',    'sub' => 'ຈຳນວນນຳໃຊ້ (ຕາມ Item)'],
							];
							foreach ($summaryColumns as $col):
							?>
								<div class="summary-col">
									<div class="summary-col-header">
										<span><?= htmlspecialchars($col['title']) ?></span>
										<span class="badge-label">ຄັ້ງ</span>
										<span class="col-count-badge" id="summaryCount_<?= $col['key'] ?>">–</span>
										<span class="badge-label">ອຸປະກອນ</span>
										<span class="col-amount-badge" id="summaryAmount_<?= $col['key'] ?>">–</span>
									</div>
									<div class="summary-col-title"><?= htmlspecialchars($col['sub']) ?></div>
									<div class="summary-col-search">
										<input type="text" placeholder="ຄົ້ນຫາ Item ຫຼື PR..." data-cat="<?= $col['key'] ?>" oninput="filterSummary(this)">
									</div>
									<div class="summary-plan-tabs" id="summaryPlanTabs_<?= $col['key'] ?>">
										<button type="button" class="plan-tab plan-1 active" data-cat="<?= $col['key'] ?>" data-plan="1" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ1</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
										<button type="button" class="plan-tab plan-2" data-cat="<?= $col['key'] ?>" data-plan="2" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ2</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
										<button type="button" class="plan-tab plan-3" data-cat="<?= $col['key'] ?>" data-plan="3" onclick="switchPlan(this)">
											<span class="tab-label">ແຜນ3</span>
											<span class="tab-qty">–</span>
											<span class="tab-amount">–</span>
										</button>
									</div>
									<div class="summary-col-listhead">
										<span class="h-img">ຮູບພາບ</span>
										<span class="h-pr">PR</span>
										<span class="h-item">Item</span>
										<span class="h-date">ວັນທີ່</span>
										<span class="h-qty">ຄັ້ງ</span>
										<span class="h-amount">ອຸປະກອນ</span>
									</div>
									<div class="summary-col-list" id="summaryList_<?= $col['key'] ?>">
										<div class="summary-loading">ກຳລັງໂຫຼດ...</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>

	<script>
		function openCity(evt, cityName) {
			var i, tabcontent, tablinks;
			tabcontent = document.getElementsByClassName("tabcontent-custom");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].classList.remove("active");
				tabcontent[i].style.display = "none";
			}
			tablinks = document.getElementsByClassName("tablinks");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active", "");
			}
			document.getElementById(cityName).style.display = "block";
			document.getElementById(cityName).classList.add("active");
			evt.currentTarget.className += " active";
		}

		document.getElementById("defaultOpen").click();

		function resizeIframe(obj) {
			try {
				var h = obj.contentWindow.document.documentElement.scrollHeight;
				if (h && h > 0) {
					obj.style.height = h + 'px';
				}
			} catch (e) {}
		}

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

			if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
				$('.cust_id').select2({
					placeholder: "ເລືອກລາຍການ",
					allowClear: true,
					width: '100%'
				});
			}
		});

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

		// ============================================================
		// SUMMARY DASHBOARD ("ສະຫຼຸບການລາຍງານ")
		// ============================================================
		var summaryData = { request: [], order: [], receive: [], use: [] };
		var summaryCategories = ['request', 'order', 'receive', 'use'];
		// ແຜນທີ່ກຳລັງເລືອກຢູ່ໃນແຕ່ລະຖັນ (default = ແຜນ1)
		var summaryActivePlan = { request: '1', order: '1', receive: '1', use: '1' };

		// ແຜນ (planing_number) ຄາດຫວັງຄ່າ 'ແຜນ1' / 'ແຜນ2' / 'ແຜນ3' ຈາກ backend.
		function planKey(planLabel) {
			if (!planLabel) return '1';
			if (planLabel.indexOf('3') !== -1) return '3';
			if (planLabel.indexOf('2') !== -1) return '2';
			return '1';
		}

		function escapeHtml(str) {
			return String(str == null ? '' : str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		function summaryRowHtml(row) {
			var imgTag = row.image
				? '<img src="' + row.image + '" alt="" onerror="this.replaceWith(Object.assign(document.createElement(\'div\'),{className:\'r-img-fallback\',innerHTML:\'<i class=\\\'fa-solid fa-box\\\'></i>\'}))">'
				: '<div class="r-img-fallback"><i class="fa-solid fa-box"></i></div>';
			return '' +
				'<div class="summary-row">' +
					imgTag +
					'<span class="r-pr">' + escapeHtml(row.pr || '-') + '</span>' +
					'<span class="r-item">' + escapeHtml(row.item) + '</span>' +
					'<span class="r-date">' + escapeHtml(row.date || '-') + '</span>' +
					'<span class="r-qty" title="ຈຳນວນຄັ້ງ">' + row.qty + '</span>' +
					'<span class="r-amount" title="ຈຳນວນອຸປະກອນ">' + (row.amount != null ? row.amount : 0) + '</span>' +
				'</div>';
		}

		function skeletonHtml(n) {
			var one = '<div class="summary-skeleton-row">' +
				'<div class="sk-block sk-img"></div>' +
				'<div class="sk-block sk-pr"></div>' +
				'<div class="sk-block sk-item"></div>' +
				'<div class="sk-block sk-date"></div>' +
				'<div class="sk-block sk-qty"></div>' +
			'</div>';
			return new Array(n || 5).fill(one).join('');
		}

		function sumQty(rows) {
			return (rows || []).reduce(function(sum, r) { return sum + (parseInt(r.qty, 10) || 0); }, 0);
		}

		// ຈຳນວນອຸປະກອນຕົວຈິງລວມ (SUM ຈາກ column ຈິງ ຄື request.Unit / tacking.QTY / stock_provinceinput.Unit / usestock.Unit)
		function sumAmount(rows) {
			return (rows || []).reduce(function(sum, r) { return sum + (parseFloat(r.amount) || 0); }, 0);
		}

		function groupByPlan(rows) {
			var groups = { '1': [], '2': [], '3': [] };
			(rows || []).forEach(function(r) {
				groups[planKey(r.plan)].push(r);
			});
			return groups;
		}

		function currentSearchQuery(cat) {
			var input = document.querySelector('.summary-col-search input[data-cat="' + cat + '"]');
			return input ? input.value.trim().toLowerCase() : '';
		}

		function searchFilteredRows(cat) {
			var q = currentSearchQuery(cat);
			var rows = summaryData[cat] || [];
			if (!q) return rows;
			return rows.filter(function(r) {
				var itemMatch = (r.item || '').toLowerCase().indexOf(q) !== -1;
				var prMatch = (r.pr || '').toLowerCase().indexOf(q) !== -1;
				return itemMatch || prMatch;
			});
		}

		// ວາດ tab ແຜນ1/ແຜນ2/ແຜນ3 ພ້ອມຈຳນວນຄັ້ງ + ຈຳນວນອຸປະກອນ, ແລະ ຄືນ groups ທີ່ໃຊ້ຄິດໄລ່ແລ້ວ
		function renderPlanTabs(cat, groups) {
			var wrap = document.getElementById('summaryPlanTabs_' + cat);
			if (!wrap) return;

			['1', '2', '3'].forEach(function(pk) {
				var btn = wrap.querySelector('.plan-tab[data-plan="' + pk + '"]');
				if (!btn) return;
				var rows = groups[pk] || [];
				btn.querySelector('.tab-qty').textContent = rows.length ? sumQty(rows) : '0';
				var amountEl = btn.querySelector('.tab-amount');
				if (amountEl) amountEl.textContent = rows.length ? (sumAmount(rows) + ' ອັນ') : '';
				btn.disabled = rows.length === 0;
				btn.classList.toggle('active', summaryActivePlan[cat] === pk);
			});
		}

		function renderSummaryColumn(cat) {
			var el = document.getElementById('summaryList_' + cat);
			if (!el) return;

			var filteredRows = searchFilteredRows(cat);
			var groups = groupByPlan(filteredRows);

			// ອັບເດດ badge ລວມຢູ່ຫົວຖັນ: "ຄັ້ງ" (COUNT) ແລະ "ອຸປະກອນ" (SUM ຈາກ column ຈິງ),
			// ຄິດຈາກທຸກແຜນລວມກັນ ຕາມການຄົ້ນຫາປັດຈຸບັນ
			var totalBadge = document.getElementById('summaryCount_' + cat);
			if (totalBadge) totalBadge.textContent = sumQty(filteredRows);
			var amountBadge = document.getElementById('summaryAmount_' + cat);
			if (amountBadge) amountBadge.textContent = sumAmount(filteredRows);

			renderPlanTabs(cat, groups);

			// ຖ້າແຜນທີ່ເລືອກຢູ່ບໍ່ມີຂໍ້ມູນ ແຕ່ແຜນອື່ນມີ, ໃຫ້ສະຫຼັບໄປແຜນທຳອິດທີ່ມີຂໍ້ມູນອັດຕະໂນມັດ
			var activePlan = summaryActivePlan[cat];
			if (!groups[activePlan] || groups[activePlan].length === 0) {
				var fallbackPlan = ['1', '2', '3'].find(function(pk) {
					return groups[pk] && groups[pk].length > 0;
				});
				if (fallbackPlan) {
					summaryActivePlan[cat] = fallbackPlan;
					activePlan = fallbackPlan;
					renderPlanTabs(cat, groups);
				}
			}

			var rows = groups[activePlan] || [];
			if (rows.length === 0) {
				el.innerHTML = '<div class="summary-empty"><i class="fa-solid fa-inbox"></i>ບໍ່ມີຂໍ້ມູນ</div>';
				return;
			}

			el.innerHTML = rows.map(summaryRowHtml).join('');
		}

		function switchPlan(btn) {
			var cat = btn.getAttribute('data-cat');
			var plan = btn.getAttribute('data-plan');
			if (btn.disabled) return;
			summaryActivePlan[cat] = plan;
			renderSummaryColumn(cat);
		}

		function filterSummary(input) {
			var cat = input.getAttribute('data-cat');
			renderSummaryColumn(cat);
		}

		function loadSummary(btn) {
			var province = btn.getAttribute('data-province');

			document.querySelectorAll('#summaryProvinces .prov-btn').forEach(function(b) {
				b.classList.remove('active');
			});
			btn.classList.add('active');

			summaryCategories.forEach(function(cat) {
				var el = document.getElementById('summaryList_' + cat);
				if (el) el.innerHTML = skeletonHtml(5);
				var badge = document.getElementById('summaryCount_' + cat);
				if (badge) badge.textContent = '…';
				var amountBadge = document.getElementById('summaryAmount_' + cat);
				if (amountBadge) amountBadge.textContent = '…';
				summaryActivePlan[cat] = '1';
			});

			fetch('ReporDataTotalSummary.php?Pro=' + encodeURIComponent(province))
				.then(function(res) { return res.json(); })
				.then(function(data) {
					summaryCategories.forEach(function(cat) {
						summaryData[cat] = data[cat] || [];
						renderSummaryColumn(cat);
					});
				})
				.catch(function() {
					summaryCategories.forEach(function(cat) {
						var el = document.getElementById('summaryList_' + cat);
						if (el) el.innerHTML = '<div class="summary-empty"><i class="fa-solid fa-triangle-exclamation"></i>ບໍ່ສາມາດໂຫຼດຂໍ້ມູນໄດ້</div>';
						var badge = document.getElementById('summaryCount_' + cat);
						if (badge) badge.textContent = '!';
						var amountBadge = document.getElementById('summaryAmount_' + cat);
						if (amountBadge) amountBadge.textContent = '!';
					});
				});
		}

		document.addEventListener('DOMContentLoaded', function() {
			var firstProvBtn = document.querySelector('#summaryProvinces .prov-btn');
			if (firstProvBtn) {
				loadSummary(firstProvBtn); 
			}
		});
	</script>

	<!-- Core theme JS -->
	<script src="js/scripts.js"></script>

	<!-- Translate -->
	<script src="translate/lang.js"></script>
</body>

</html>
