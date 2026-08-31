<?php
require_once __DIR__ . '/includes/conn.php';
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
	<?php include __DIR__ . '/includes/head_cdn.php'; ?>
	<title>ສາງອຸປະກອນ</title>

	<style type="text/css">
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

		@media (max-width: 991.98px) {
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

		@media (max-width: 768px) {
			.form-container {
				padding: 16px 14px;
			}

			.form-container .form-label {
				font-size: 15px;
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

	<script src="js/shared.js"></script>

</body>

</html>