<?php
require_once __DIR__ . '/../includes/conn.php';
require_once __DIR__ . '/../includes/stock_dashboard_lib.php';
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

// ===== ຈຳກັດການເຂົ້າເຖິງໜ້ານີ້: ສະເພາະ Admin ເທົ່ານັ້ນ =====
// (ໃຊ້ລາຍຊື່ iduser ດຽວກັນກັບທີ່ໃຊ້ກຳນົດສິດ Admin ຢູ່ໃນໜ້ານີ້)
$userId = $_SESSION["iduser"] ?? '';
$adminUsers = ['404', '30', '194', '213', '2', '41', '214', '215'];
$isAdmin = in_array($userId, $adminUsers);
if (!$isAdmin) {
    // Staff ທົ່ວໄປ ບໍ່ມີສິດເຂົ້າ Dashboard ນີ້ -> ສົ່ງກັບໄປໜ້າຂໍເບິກອຸປະກອນແທນ
    echo "<script>window.location = 'Dasborad.php';</script>";
    exit;
}

// ດຶງຂໍ້ມູນເທື່ອທຳອິດ (ສຳລັບສະແດງທັນທີ ແລະ ໃຊ້ສ້າງກຣາບເບື້ອງຕົ້ນ ໂດຍບໍ່ຕ້ອງລໍ fetch)
$initialData = getStockDashboardData($conn);
$initialDataJson = json_encode($initialData, JSON_UNESCAPED_UNICODE);

// ຂໍ້ມູນ tab ຕາມແຂວງ - ດຶງໄວ້ພ້ອມກັນເລີຍ ເພື່ອສະຫຼັບ tab ໄດ້ໄວທັນທີໂດຍບໍ່ຕ້ອງລໍ fetch
$initialProvinceData = getStockProvinceDashboardData($conn, null);
$initialProvinceDataJson = json_encode($initialProvinceData, JSON_UNESCAPED_UNICODE);

// ຂໍ້ມູນ tab ຍອດເບິກ/ໃຊ້ (usestock) - ດຶງໄວ້ພ້ອມກັນເລີຍ ຄືກັນ
$initialUseStockData = getUseStockDashboardData($conn, null);
$initialUseStockDataJson = json_encode($initialUseStockData, JSON_UNESCAPED_UNICODE);

// ຂໍ້ມູນ tab ຍອດຂໍເບິກ (request) - ດຶງໄວ້ພ້ອມກັນເລີຍ ຄືກັນ (column ແຂວງ = "Province" ບໍ່ມີ s)
$initialRequestData = getRequestDashboardData($conn, null);
$initialRequestDataJson = json_encode($initialRequestData, JSON_UNESCAPED_UNICODE);

// ===== ກຳນົດໜ້າປັດຈຸບັນ ເພື່ອໃຊ້ highlight ເມນູ sidebar ໃຫ້ຖືກຕ້ອງ =====
$currentPage = basename($_SERVER['PHP_SELF']);
function navActive($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="shortcut icon" href="image/favicons.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
	<link rel="stylesheet" href="css/all.min.css">

    <!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
	<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

      <!-- Core theme CSS -->
	<link href="css/styles.css" rel="stylesheet" />

    <title>Dashboard Stock | LS</title>

    <link href="css/styles.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/pages/StockDashboard.css">

    <?php
	// PHP 8: ໃຊ້ Null Coalescing Operator
	// ໝາຍເຫດ: $userId / $adminUsers / $isAdmin ຖືກກຳນົດໄວ້ແລ້ວຢູ່ເທິງສຸດຂອງໄຟລ໌
	// (ໃຊ້ຮ່ວມກັນສຳລັບການຈຳກັດສິດເຂົ້າໜ້ານີ້)
	$proo = $_SESSION["Namepro"] ?? '';

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
    <!-- <div class="d-flex" id="wrapper"> -->

        <!-- ===== Page content wrapper ===== -->
		<div id="page-content-wrapper">

			<!-- ===== Navbar ສີຂຽວ + Logout ===== -->
			<nav class="navbar navbar-expand-lg navbar-green border-bottom">
				<div class="container-fluid">
					<button class="btn btn-success" id="sidebarToggle" type="button" aria-expanded="true" aria-controls="sidebar-wrapper">
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
							<!-- <?php if (($_SESSION["iduser"] ?? '') == "204"): ?>
								<li class="nav-item me-2">
									<a href="Uploadstock_hr_snk.php" class="btn btn-light btn-sm">
										<i class="fa fa-upload"></i> Upload Stock
									</a>
								</li>
							<?php endif; ?> -->

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

            <div class="container-fluid px-4 py-3">

                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-secondary">
                        <i class="fa-solid fa-warehouse"></i>
                        ຍອດລວມທັງໝົດ ທີ່ສູນກາງເອົາເຂົ້າລະບົບ
                    </span>
                    <span class="live-badge"><span class="live-dot"></span> REALTIME</span>
                    <span id="lastUpdated">ອັບເດດລ່າສຸດ: <?php echo htmlspecialchars($initialData['generatedAt']); ?></span>
                </div>

                <!-- ===== Tabs: ຕາມປະເພດ/ກຸ່ມ vs ຕາມແຂວງ vs usestock vs request ===== -->
                <ul class="nav mb-4" id="dashboardTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-type-btn" data-bs-toggle="tab" data-bs-target="#tab-type" type="button" role="tab">
                            <i class="fa-solid fa-tags"></i> ຍອດຕາມປະເພດ / ກຸ່ມ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-province-btn" data-bs-toggle="tab" data-bs-target="#tab-province" type="button" role="tab">
                            <i class="fa-solid fa-map-location-dot"></i> ຍອດຕາມແຂວງ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-usestock-btn" data-bs-toggle="tab" data-bs-target="#tab-usestock" type="button" role="tab">
                            <i class="fa-solid fa-truck-ramp-box"></i> ຍອດການນຳໃຊ້ (usestock)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-request-btn" data-bs-toggle="tab" data-bs-target="#tab-request" type="button" role="tab">
                            <i class="fi fi-rr-hand-holding-box"></i> ຍອດຂໍເບິກ (request)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                <!-- ============================================================ -->
                <!-- TAB 1: ຕາມ Type / Groupp (stock)                              -->
                <!-- ============================================================ -->
                <div class="tab-pane fade show active" id="tab-type" role="tabpanel">

                    <!-- ===== Summary Cards ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-total">
                                <i class="fa-solid fa-boxes-stacked stat-icon"></i>
                                <div class="stat-label">ຍອດຈຳນວນທັງໝົດ (ລວມ)</div>
                                <div class="stat-value" id="statTotalAll"><?php echo fmtNum($initialData['totalAll']); ?></div>
                                <div class="stat-sub">SUM(Stock_TMD) ຈາກ stock</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-items">
                                <i class="fa-solid fa-boxes-packing stat-icon"></i>
                                <div class="stat-label">ຈຳນວນລາຍການສິນຄ້າ</div>
                                <div class="stat-value" id="statItemCount"><?php echo fmtNum($initialData['totalItemCount']); ?></div>
                                <div class="stat-sub">Items ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-types">
                                <i class="fa-solid fa-tags stat-icon"></i>
                                <div class="stat-label">ຈຳນວນປະເພດ (Type)</div>
                                <div class="stat-value" id="statTypeCount"><?php echo count($initialData['byType']); ?></div>
                                <div class="stat-sub">Type ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-groups">
                                <i class="fa-solid fa-layer-group stat-icon"></i>
                                <div class="stat-label">ຈຳນວນກຸ່ມ (Groupp)</div>
                                <div class="stat-value" id="statGroupCount"><?php echo count($initialData['byGroup']); ?></div>
                                <div class="stat-sub">Groupp ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Charts ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-column"></i> ຍອດລວມຕາມປະເພດ (Type)</h6>
                                <div class="chart-box"><canvas id="chartByType"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-pie"></i> ສັດສ່ວນຕາມກຸ່ມ (Groupp)</h6>
                                <div class="chart-box"><canvas id="chartByGroup"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-ranking-star"></i> Top 10 ລາຍການທີ່ເອົາເຂົ້າລະບົບຫຼາຍສຸດ</h6>
                                <div class="chart-box tall"><canvas id="chartTopItems"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Summary by Type / Group ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-tags"></i> ສະຫຼຸບຕາມປະເພດ (Type)</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ປະເພດ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byTypeTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-layer-group"></i> ສະຫຼຸບຕາມກຸ່ມ (Groupp)</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ກຸ່ມ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byGroupTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Detail Table ===== -->
                    <div class="table-wrapper-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h6 class="mb-0"><i class="fa-solid fa-list"></i> ລາຍລະອຽດ Items ທັງໝົດ (ຈັດຮຽງຕາມປະເພດ)</h6>
                            <input type="text" id="searchBox" class="form-control" style="max-width:260px;" placeholder="ຄົ້ນຫາຊື່ລາຍການ / ລະຫັດ / ກຸ່ມ / ປະເພດ...">
                        </div>
                        <div class="table-responsive" style="max-height:65vh;">
                            <table class="table table-hover table-bordered dash-table" id="dashTable">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">ລ/ດ</th>
                                        <th>ປະເພດ (Type)</th>
                                        <th>ກຸ່ມ (Groupp)</th>
                                        <th>ລະຫັດ (Item_code)</th>
                                        <th>ຊື່ລາຍການ (Items)</th>
                                        <th class="text-end">ຍອດເອົາເຂົ້າລະບົບ</th>
                                    </tr>
                                </thead>
                                <tbody id="dashTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <!-- END TAB 1 -->

                <!-- ============================================================ -->
                <!-- TAB 2: ຕາມແຂວງ (stock_province)                              -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-province" role="tabpanel">

                    <!-- ===== Province Filter Pills ===== -->
                    <div class="table-wrapper-card mb-3 py-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-filter text-secondary"></i>
                            <strong class="text-secondary" style="font-size:13px;">ກັ່ນຕອງຕາມແຂວງ:</strong>
                        </div>
                        <div id="provincePillBar"></div>
                    </div>

                    <!-- ===== Summary Cards ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-total">
                                <i class="fa-solid fa-boxes-stacked stat-icon"></i>
                                <div class="stat-label">ຍອດຈຳນວນລວມທັງໝົດ</div>
                                <div class="stat-value" id="statTotalAllP"><?php echo fmtNum($initialProvinceData['totalAll']); ?></div>
                                <div class="stat-sub">SUM(Unit) ຈາກ stock_province</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-items">
                                <i class="fa-solid fa-boxes-packing stat-icon"></i>
                                <div class="stat-label">ຈຳນວນລາຍການສິນຄ້າ</div>
                                <div class="stat-value" id="statItemCountP"><?php echo fmtNum($initialProvinceData['totalItemCount']); ?></div>
                                <div class="stat-sub">Items ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-groups">
                                <i class="fa-solid fa-layer-group stat-icon"></i>
                                <div class="stat-label">ຈຳນວນກຸ່ມ (Groupp)</div>
                                <div class="stat-value" id="statGroupCountP"><?php echo count($initialProvinceData['byGroup']); ?></div>
                                <div class="stat-sub">Groupp ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-province">
                                <i class="fa-solid fa-map-location-dot stat-icon"></i>
                                <div class="stat-label">ຈຳນວນແຂວງ</div>
                                <div class="stat-value" id="statProvinceCount"><?php echo count($initialProvinceData['byProvince']); ?></div>
                                <div class="stat-sub">Provinces ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Charts ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-7">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-column"></i> ຍອດລວມແຍກຕາມແຂວງ (Provinces)</h6>
                                <div class="chart-box tall"><canvas id="chartByProvince"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-pie"></i> ສັດສ່ວນຕາມກຸ່ມ (Groupp)</h6>
                                <div class="chart-box tall"><canvas id="chartByGroupP"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-ranking-star"></i> Top 10 ລາຍການທີ່ມີຍອດຫຼາຍສຸດ<span id="topItemsScopeLabel"></span></h6>
                                <div class="chart-box tall"><canvas id="chartTopItemsP"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Summary by Province / Group ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-map-location-dot"></i> ສະຫຼຸບຕາມແຂວງ</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ແຂວງ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຈຳນວນກຸ່ມ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byProvinceTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-layer-group"></i> ສະຫຼຸບຕາມກຸ່ມ (Groupp)</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ກຸ່ມ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byGroupTableBodyP"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Detail Table ===== -->
                    <div class="table-wrapper-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h6 class="mb-0"><i class="fa-solid fa-list"></i> ລາຍລະອຽດ Items ທັງໝົດ (ຈັດຮຽງຕາມແຂວງ)</h6>
                            <input type="text" id="searchBoxP" class="form-control" style="max-width:260px;" placeholder="ຄົ້ນຫາຊື່ລາຍການ / ລະຫັດ / ກຸ່ມ / ແຂວງ...">
                        </div>
                        <div class="table-responsive" style="max-height:65vh;">
                            <table class="table table-hover table-bordered dash-table" id="dashTableP">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">ລ/ດ</th>
                                        <th>ແຂວງ (Provinces)</th>
                                        <th>ກຸ່ມ (Groupp)</th>
                                        <th>ປະເພດ (Type)</th>
                                        <th>ລະຫັດ (Item_code)</th>
                                        <th>ຊື່ລາຍການ (Items)</th>
                                        <th class="text-end">ຍອດ</th>
                                    </tr>
                                </thead>
                                <tbody id="dashTableBodyP"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <!-- END TAB 2 -->

                <!-- ============================================================ -->
                <!-- TAB 3: ຍອດເບິກ/ໃຊ້ (usestock)                                -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-usestock" role="tabpanel">

                    <!-- ===== Province Filter Pills ===== -->
                    <div class="table-wrapper-card mb-3 py-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-filter text-secondary"></i>
                            <strong class="text-secondary" style="font-size:13px;">ກັ່ນຕອງຕາມແຂວງ:</strong>
                        </div>
                        <div id="provincePillBarU"></div>
                    </div>

                    <!-- ===== Summary Cards ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-total">
                                <i class="fa-solid fa-truck-ramp-box stat-icon"></i>
                                <div class="stat-label">ຍອດຈຳນວນການນຳໃຊ້ (ລວມ)</div>
                                <div class="stat-value" id="statTotalAllU"><?php echo fmtNum($initialUseStockData['totalAll']); ?></div>
                                <div class="stat-sub">SUM(Unit) ຈາກ usestock</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-items">
                                <i class="fa-solid fa-boxes-packing stat-icon"></i>
                                <div class="stat-label">ຈຳນວນລາຍການສິນຄ້າ</div>
                                <div class="stat-value" id="statItemCountU"><?php echo fmtNum($initialUseStockData['totalItemCount']); ?></div>
                                <div class="stat-sub">Items ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-types">
                                <i class="fa-solid fa-tags stat-icon"></i>
                                <div class="stat-label">ຈຳນວນປະເພດ (Type)</div>
                                <div class="stat-value" id="statTypeCountU"><?php echo count($initialUseStockData['byType']); ?></div>
                                <div class="stat-sub">Type ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-province">
                                <i class="fa-solid fa-map-location-dot stat-icon"></i>
                                <div class="stat-label">ຈຳນວນແຂວງ</div>
                                <div class="stat-value" id="statProvinceCountU"><?php echo count($initialUseStockData['byProvince']); ?></div>
                                <div class="stat-sub">Provinces ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Trend Chart (ຕາມວັນທີ່) ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-line"></i> ແນວໂນ້ມການນຳໃຊ້ ຕາມວັນທີ່ (30 ວັນລ່າສຸດ)<span id="usestockScopeLabel"></span></h6>
                                <div class="chart-box"><canvas id="chartUseTrend"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Charts ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-7">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-column"></i> ຍອດການນຳໃຊ້ ແຍກຕາມແຂວງ</h6>
                                <div class="chart-box tall"><canvas id="chartByProvinceU"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-pie"></i> ສັດສ່ວນຕາມປະເພດ (Type)</h6>
                                <div class="chart-box tall"><canvas id="chartByTypeU"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-ranking-star"></i> Top 10 ລາຍການທີ່ຖືກການນຳໃຊ້ຫຼາຍສຸດ<span id="topItemsScopeLabelU"></span></h6>
                                <div class="chart-box tall"><canvas id="chartTopItemsU"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Summary by Province / Type ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-map-location-dot"></i> ສະຫຼຸບຕາມແຂວງ</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ແຂວງ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຈຳນວນປະເພດ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byProvinceTableBodyU"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-tags"></i> ສະຫຼຸບຕາມປະເພດ (Type)</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ປະເພດ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຍອດລວມ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byTypeTableBodyU"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Detail Table ===== -->
                    <div class="table-wrapper-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h6 class="mb-0"><i class="fa-solid fa-list"></i> ລາຍລະອຽດ Items ທັງໝົດ (ຈັດຮຽງຕາມແຂວງ)</h6>
                            <input type="text" id="searchBoxU" class="form-control" style="max-width:260px;" placeholder="ຄົ້ນຫາຊື່ລາຍການ / ປະເພດ / ແຂວງ...">
                        </div>
                        <div class="table-responsive" style="max-height:65vh;">
                            <table class="table table-hover table-bordered dash-table" id="dashTableU">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">ລ/ດ</th>
                                        <th>ແຂວງ (Provinces)</th>
                                        <th>ປະເພດ (Type)</th>
                                        <th>ຊື່ລາຍການ (Items)</th>
                                        <th>ວັນທີ່ລ່າສຸດ</th>
                                        <th class="text-end">ຍອດເບິກ/ໃຊ້</th>
                                    </tr>
                                </thead>
                                <tbody id="dashTableBodyU"></tbody>
                            </table>
                        </div>
                        <div class="text-muted mt-2" style="font-size:12px;">
                            <i class="fa-solid fa-circle-info"></i> ຕາຕະລາງ usestock ບໍ່ມີ column Item_code, ຈຶ່ງບໍ່ສະແດງລະຫັດໃນ tab ນີ້
                        </div>
                    </div>

                </div>
                <!-- END TAB 3 -->

                <!-- ============================================================ -->
                <!-- TAB 4: ຍອດຂໍເບິກ (request) - column ແຂວງ: "Province" ບໍ່ມີ s -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="tab-request" role="tabpanel">

                    <!-- ===== Province Filter Pills ===== -->
                    <div class="table-wrapper-card mb-3 py-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa-solid fa-filter text-secondary"></i>
                            <strong class="text-secondary" style="font-size:13px;">ກັ່ນຕອງຕາມແຂວງ:</strong>
                        </div>
                        <div id="provincePillBarR"></div>
                    </div>

                    <!-- ===== Summary Cards ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-total">
                                <i class="fi fi-rr-hand-holding-box stat-icon"></i>
                                <div class="stat-label">ຍອດຈຳນວນຂໍເບິກທັງໝົດ</div>
                                <div class="stat-value" id="statTotalAllR"><?php echo fmtNum($initialRequestData['totalAll']); ?></div>
                                <div class="stat-sub">SUM(Unit) ຈາກ request</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-give">
                                <i class="fa-solid fa-hand-holding-heart stat-icon"></i>
                                <div class="stat-label">ອະນຸມັດ/ຈ່າຍແລ້ວ</div>
                                <div class="stat-value" id="statTotalGiveR"><?php echo fmtNum($initialRequestData['totalGive']); ?></div>
                                <div class="stat-sub">SUM(Give) ຈາກ request</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-pending">
                                <i class="fa-solid fa-hourglass-half stat-icon"></i>
                                <div class="stat-label">ຍັງຄ້າງ (ຍັງບໍ່ໄດ້ຈ່າຍ)</div>
                                <div class="stat-value" id="statTotalPendingR"><?php echo fmtNum($initialRequestData['totalPending']); ?></div>
                                <div class="stat-sub">Unit - Give</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card card-province">
                                <i class="fa-solid fa-map-location-dot stat-icon"></i>
                                <div class="stat-label">ຈຳນວນແຂວງ</div>
                                <div class="stat-value" id="statProvinceCountR"><?php echo count($initialRequestData['byProvince']); ?></div>
                                <div class="stat-sub">Province ທີ່ບໍ່ຊ້ຳກັນ</div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Charts ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-7">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-column"></i> ຂໍເບິກ vs ອະນຸມັດ ແຍກຕາມແຂວງ</h6>
                                <div class="chart-box tall"><canvas id="chartByProvinceR"></canvas></div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-chart-pie"></i> ສັດສ່ວນຕາມກຸ່ມ (Groupp)</h6>
                                <div class="chart-box tall"><canvas id="chartByGroupR"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="chart-card">
                                <h6><i class="fa-solid fa-ranking-star"></i> Top 10 ລາຍການທີ່ຖືກຂໍເບິກຫຼາຍສຸດ<span id="topItemsScopeLabelR"></span></h6>
                                <div class="chart-box tall"><canvas id="chartTopItemsR"></canvas></div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Summary by Province / Group ===== -->
                    <div class="row g-3 mb-4">
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-map-location-dot"></i> ສະຫຼຸບຕາມແຂວງ</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ແຂວງ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຂໍເບິກ</th>
                                                <th class="text-end">ອະນຸມັດ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byProvinceTableBodyR"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="table-wrapper-card h-100">
                                <h6 class="mb-3"><i class="fa-solid fa-layer-group"></i> ສະຫຼຸບຕາມກຸ່ມ (Groupp)</h6>
                                <div class="table-responsive" style="max-height:320px;">
                                    <table class="table table-hover table-bordered mini-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>ກຸ່ມ</th>
                                                <th class="text-end">ຈຳນວນຊະນິດ</th>
                                                <th class="text-end">ຂໍເບິກ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="byGroupTableBodyR"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Detail Table ===== -->
                    <div class="table-wrapper-card">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h6 class="mb-0"><i class="fa-solid fa-list"></i> ລາຍລະອຽດ Items ທັງໝົດ (ຈັດຮຽງຕາມແຂວງ)</h6>
                            <input type="text" id="searchBoxR" class="form-control" style="max-width:260px;" placeholder="ຄົ້ນຫາຊື່ລາຍການ / ລະຫັດ / ກຸ່ມ / ແຂວງ...">
                        </div>
                        <div class="table-responsive" style="max-height:65vh;">
                            <table class="table table-hover table-bordered dash-table" id="dashTableR">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">ລ/ດ</th>
                                        <th>ແຂວງ (Province)</th>
                                        <th>ກຸ່ມ (Groupp)</th>
                                        <th>ປະເພດ (Type)</th>
                                        <th>ລະຫັດ (Item_code)</th>
                                        <th>ຊື່ລາຍການ (Items)</th>
                                        <th class="text-end">ຂໍເບິກ</th>
                                        <th class="text-end">ອະນຸມັດ</th>
                                        <th class="text-end">ຄ້າງ</th>
                                    </tr>
                                </thead>
                                <tbody id="dashTableBodyR"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <!-- END TAB 4 -->

                </div>
                <!-- END tab-content -->

            </div>
        </div>
    </div>

    <script>
        // ==============================================================
        // Realtime Dashboard (4 tab): render ຄັ້ງທຳອິດຈາກ PHP, ຈາກນັ້ນ poll
        // stock_dashboard_data.php ທຸກໆ 7 ວິນາທີ ເພື່ອອັບເດດ card/ກຣາບ/
        // ຕາຕະລາງ ຂອງ tab ທີ່ active ຢູ່ໃນປັດຈຸບັນ ໂດຍບໍ່ຕ້ອງ refresh ໜ້າ.
        // ==============================================================

        const REFRESH_MS = 7000;
        const ENDPOINT = 'stock_dashboard_data.php';

        // ----- Tab 1: Type/Group state -----
        let currentData = <?php echo $initialDataJson; ?>;
        let lastSignature = null;

        // ----- Tab 2: Province state -----
        let currentProvinceData = <?php echo $initialProvinceDataJson; ?>;
        let activeProvince = null; // null = ທັງໝົດ
        let lastSignatureProvince = null;

        // ----- Tab 3: usestock state -----
        let currentUseStockData = <?php echo $initialUseStockDataJson; ?>;
        let activeProvinceU = null; // null = ທັງໝົດ
        let lastSignatureUseStock = null;

        // ----- Tab 4: request state -----
        let currentRequestData = <?php echo $initialRequestDataJson; ?>;
        let activeProvinceR = null; // null = ທັງໝົດ
        let lastSignatureRequest = null;

        let activeTab = 'type'; // 'type' | 'province' | 'usestock' | 'request'

        function fmtNum(n) {
            const rounded = Math.round(n * 100) / 100;
            if (rounded === Math.trunc(rounded)) {
                return Math.trunc(rounded).toLocaleString('en-US');
            }
            return rounded.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function flashEl(el) {
            el.classList.remove('flash');
            void el.offsetWidth; // force reflow so animation restarts
            el.classList.add('flash');
        }

        function escapeHtml(s) {
            const div = document.createElement('div');
            div.textContent = s ?? '';
            return div.innerHTML;
        }
        function escapeAttr(s) {
            return String(s ?? '').replace(/'/g, "\\'");
        }

        const palette = [
            '#198754', '#0d6efd', '#fd7e14', '#6f42c1', '#dc3545',
            '#20c997', '#ffc107', '#0dcaf0', '#6c757d', '#d63384',
            '#146c43', '#0a58ca', '#d9670b', '#59339d', '#a52834'
        ];
        function colorAt(i) { return palette[i % palette.length]; }

        function setLastUpdated(ts) {
            document.getElementById('lastUpdated').textContent = 'ອັບເດດລ່າສຸດ: ' + ts;
        }

        // ============================================================
        // ===== TAB 1: ຕາມ Type / Groupp =====
        // ============================================================
        let chartByType, chartByGroup, chartTopItems;

        function buildCharts(data) {
            const typeLabels = data.byType.map(t => t.type);
            const typeQty = data.byType.map(t => t.qty);
            const typeColors = typeLabels.map((_, i) => colorAt(i));

            const ctxType = document.getElementById('chartByType').getContext('2d');
            chartByType = new Chart(ctxType, {
                type: 'bar',
                data: { labels: typeLabels, datasets: [{ label: 'ຍອດລວມ', data: typeQty, backgroundColor: typeColors, borderRadius: 8, maxBarThickness: 46 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const groupLabels = data.byGroup.map(g => g.group);
            const groupQty = data.byGroup.map(g => g.qty);
            const groupColors = groupLabels.map((_, i) => colorAt(i + 5));

            const ctxGroup = document.getElementById('chartByGroup').getContext('2d');
            chartByGroup = new Chart(ctxGroup, {
                type: 'doughnut',
                data: { labels: groupLabels, datasets: [{ data: groupQty, backgroundColor: groupColors, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '58%' }
            });

            const topLabels = data.topItems.map(i => i.items);
            const topQty = data.topItems.map(i => i.qty);

            const ctxTop = document.getElementById('chartTopItems').getContext('2d');
            chartTopItems = new Chart(ctxTop, {
                type: 'bar',
                data: { labels: topLabels, datasets: [{ label: 'ຍອດເອົາເຂົ້າລະບົບ', data: topQty, backgroundColor: '#0d6efd', borderRadius: 8, maxBarThickness: 28 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        function updateCharts(data) {
            chartByType.data.labels = data.byType.map(t => t.type);
            chartByType.data.datasets[0].data = data.byType.map(t => t.qty);
            chartByType.data.datasets[0].backgroundColor = chartByType.data.labels.map((_, i) => colorAt(i));
            chartByType.update();

            chartByGroup.data.labels = data.byGroup.map(g => g.group);
            chartByGroup.data.datasets[0].data = data.byGroup.map(g => g.qty);
            chartByGroup.data.datasets[0].backgroundColor = chartByGroup.data.labels.map((_, i) => colorAt(i + 5));
            chartByGroup.update();

            chartTopItems.data.labels = data.topItems.map(i => i.items);
            chartTopItems.data.datasets[0].data = data.topItems.map(i => i.qty);
            chartTopItems.update();
        }

        function renderMiniTables(data) {
            document.getElementById('byTypeTableBody').innerHTML = data.byType.map(t => `
                <tr>
                    <td><span class="type-badge">${escapeHtml(t.type)}</span></td>
                    <td class="text-end">${t.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(t.qty)}</td>
                </tr>
            `).join('');

            document.getElementById('byGroupTableBody').innerHTML = data.byGroup.map(g => `
                <tr>
                    <td><span class="group-badge">${escapeHtml(g.group)}</span></td>
                    <td class="text-end">${g.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(g.qty)}</td>
                </tr>
            `).join('');
        }

        function rowKey(r) { return r.type + '|' + r.group + '|' + r.items + '|' + (r.itemCode || ''); }

        function renderDetailTable(data, previousRowsMap) {
            const byTypeMap = {};
            data.byType.forEach(t => byTypeMap[t.type] = t);

            const body = document.getElementById('dashTableBody');
            let html = '';
            let lastType = null;
            let idx = 0;

            if (data.rows.length === 0) {
                body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">ບໍ່ພົບຂໍ້ມູນ</td></tr>';
                return;
            }

            data.rows.forEach(row => {
                idx++;
                if (row.type !== lastType) {
                    lastType = row.type;
                    const tData = byTypeMap[row.type];
                    html += `
                        <tr class="type-group-row">
                            <td colspan="6">
                                <i class="fa-solid fa-tag"></i> ${escapeHtml(row.type)}
                                <span class="text-muted fw-normal">(ຍອດລວມປະເພດນີ້: ${fmtNum(tData ? tData.qty : 0)})</span>
                            </td>
                        </tr>`;
                }

                const key = rowKey(row);
                const isNew = previousRowsMap && !previousRowsMap.has(key);
                const rowClass = isNew ? ' class="row-new"' : '';

                html += `
                    <tr${rowClass}>
                        <td>${idx}</td>
                        <td><span class="type-badge">${escapeHtml(row.type)}</span></td>
                        <td><span class="group-badge">${escapeHtml(row.group)}</span></td>
                        <td><span class="code-badge">${escapeHtml(row.itemCode || '')}</span></td>
                        <td>${escapeHtml(row.items)}</td>
                        <td class="text-end fw-bold">${fmtNum(row.qty)}</td>
                    </tr>`;
            });

            body.innerHTML = html;
            applySearchFilter();
        }

        function updateStatCards(data) {
            const map = {
                statTotalAll: fmtNum(data.totalAll),
                statItemCount: fmtNum(data.totalItemCount),
                statTypeCount: data.byType.length,
                statGroupCount: data.byGroup.length
            };
            Object.keys(map).forEach(id => {
                const el = document.getElementById(id);
                if (el.textContent !== String(map[id])) {
                    el.textContent = map[id];
                    flashEl(el);
                }
            });
        }

        function buildRowsMap(rows) {
            const m = new Map();
            rows.forEach(r => m.set(rowKey(r), r.qty));
            return m;
        }

        function applySearchFilter() {
            const filter = document.getElementById('searchBox').value.trim().toLowerCase();
            document.querySelectorAll('#dashTableBody tr').forEach(tr => {
                if (tr.classList.contains('type-group-row')) return;
                tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        async function fetchAndRefreshType() {
            try {
                const res = await fetch(ENDPOINT + '?view=type', { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.error) return;

                const signature = JSON.stringify(data.rows) + '|' + data.totalAll;
                if (signature === lastSignature) {
                    setLastUpdated(data.generatedAt);
                    return;
                }

                const previousRowsMap = buildRowsMap(currentData.rows);
                currentData = data;
                lastSignature = signature;

                updateStatCards(data);
                updateCharts(data);
                renderMiniTables(data);
                renderDetailTable(data, previousRowsMap);
                setLastUpdated(data.generatedAt);
            } catch (e) {
                console.warn('Dashboard refresh failed (type):', e);
            }
        }

        // ============================================================
        // ===== TAB 2: ຕາມແຂວງ =====
        // ============================================================
        let chartByProvince, chartByGroupP, chartTopItemsP;

        function buildChartsProvince(data) {
            const provLabels = data.byProvince.map(p => p.province);
            const provQty = data.byProvince.map(p => p.qty);
            const provColors = provLabels.map((_, i) => colorAt(i));

            const ctxProv = document.getElementById('chartByProvince').getContext('2d');
            chartByProvince = new Chart(ctxProv, {
                type: 'bar',
                data: { labels: provLabels, datasets: [{ label: 'ຍອດລວມ', data: provQty, backgroundColor: provColors, borderRadius: 8, maxBarThickness: 40 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => 'ຍອດ: ' + fmtNum(ctx.raw) } } },
                    onClick: (evt, els) => { if (els.length) selectProvince(provLabels[els[0].index]); },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const groupLabels = data.byGroup.map(g => g.group);
            const groupQty = data.byGroup.map(g => g.qty);
            const groupColors = groupLabels.map((_, i) => colorAt(i + 5));

            const ctxGroup = document.getElementById('chartByGroupP').getContext('2d');
            chartByGroupP = new Chart(ctxGroup, {
                type: 'doughnut',
                data: { labels: groupLabels, datasets: [{ data: groupQty, backgroundColor: groupColors, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '58%' }
            });

            const topLabels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            const topQty = data.topItems.map(i => i.qty);

            const ctxTop = document.getElementById('chartTopItemsP').getContext('2d');
            chartTopItemsP = new Chart(ctxTop, {
                type: 'bar',
                data: { labels: topLabels, datasets: [{ label: 'ຍອດ', data: topQty, backgroundColor: '#0d6efd', borderRadius: 8, maxBarThickness: 26 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            document.getElementById('topItemsScopeLabel').textContent = activeProvince ? ' - ແຂວງ ' + activeProvince : ' (ທຸກແຂວງ)';
        }

        function updateChartsProvince(data) {
            const provLabels = data.byProvince.map(p => p.province);
            chartByProvince.data.labels = provLabels;
            chartByProvince.data.datasets[0].data = data.byProvince.map(p => p.qty);
            chartByProvince.data.datasets[0].backgroundColor = provLabels.map((_, i) => colorAt(i));
            chartByProvince.update();

            chartByGroupP.data.labels = data.byGroup.map(g => g.group);
            chartByGroupP.data.datasets[0].data = data.byGroup.map(g => g.qty);
            chartByGroupP.data.datasets[0].backgroundColor = chartByGroupP.data.labels.map((_, i) => colorAt(i + 5));
            chartByGroupP.update();

            chartTopItemsP.data.labels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            chartTopItemsP.data.datasets[0].data = data.topItems.map(i => i.qty);
            chartTopItemsP.update();

            document.getElementById('topItemsScopeLabel').textContent = activeProvince ? ' - ແຂວງ ' + activeProvince : ' (ທຸກແຂວງ)';
        }

        function renderPillBar(data) {
            const bar = document.getElementById('provincePillBar');
            let html = `<span class="province-pill ${!activeProvince ? 'active' : ''}" data-province="">
                            <i class="fa-solid fa-globe"></i> ທັງໝົດ
                        </span>`;
            (data.provinceList || []).forEach(p => {
                const isActive = activeProvince === p;
                html += `<span class="province-pill ${isActive ? 'active' : ''}" data-province="${escapeHtml(p)}">${escapeHtml(p)}</span>`;
            });
            bar.innerHTML = html;
            bar.querySelectorAll('.province-pill').forEach(pill => {
                pill.addEventListener('click', () => selectProvince(pill.dataset.province || null));
            });
        }

        function renderMiniTablesProvince(data) {
            document.getElementById('byProvinceTableBody').innerHTML = data.byProvince.map(p => `
                <tr style="cursor:pointer" onclick="selectProvince('${escapeAttr(p.province)}')">
                    <td><span class="province-badge">${escapeHtml(p.province)}</span></td>
                    <td class="text-end">${p.itemCount}</td>
                    <td class="text-end">${p.groupCount}</td>
                    <td class="text-end fw-bold">${fmtNum(p.qty)}</td>
                </tr>
            `).join('');

            document.getElementById('byGroupTableBodyP').innerHTML = data.byGroup.map(g => `
                <tr>
                    <td><span class="group-badge">${escapeHtml(g.group)}</span></td>
                    <td class="text-end">${g.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(g.qty)}</td>
                </tr>
            `).join('');
        }

        function rowKeyP(r) { return r.province + '|' + r.type + '|' + r.group + '|' + r.items + '|' + (r.itemCode || ''); }

        function renderDetailTableProvince(data, previousRowsMap) {
            const byProvinceMap = {};
            data.byProvince.forEach(p => byProvinceMap[p.province] = p);

            const body = document.getElementById('dashTableBodyP');
            let html = '';
            let lastProvince = null;
            let idx = 0;

            if (data.rows.length === 0) {
                body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">ບໍ່ພົບຂໍ້ມູນ</td></tr>';
                return;
            }

            data.rows.forEach(row => {
                idx++;
                if (row.province !== lastProvince) {
                    lastProvince = row.province;
                    const pData = byProvinceMap[row.province];
                    html += `
                        <tr class="province-group-row">
                            <td colspan="7">
                                <i class="fa-solid fa-map-location-dot"></i> ${escapeHtml(row.province)}
                                <span class="text-muted fw-normal">(ຍອດລວມແຂວງນີ້: ${fmtNum(pData ? pData.qty : 0)})</span>
                            </td>
                        </tr>`;
                }

                const key = rowKeyP(row);
                const isNew = previousRowsMap && !previousRowsMap.has(key);
                const rowClass = isNew ? ' class="row-new"' : '';

                html += `
                    <tr${rowClass}>
                        <td>${idx}</td>
                        <td><span class="province-badge">${escapeHtml(row.province)}</span></td>
                        <td><span class="group-badge">${escapeHtml(row.group)}</span></td>
                        <td><span class="type-badge">${escapeHtml(row.type)}</span></td>
                        <td><span class="code-badge">${escapeHtml(row.itemCode || '')}</span></td>
                        <td>${escapeHtml(row.items)}</td>
                        <td class="text-end fw-bold">${fmtNum(row.qty)}</td>
                    </tr>`;
            });

            body.innerHTML = html;
            applySearchFilterProvince();
        }

        function updateStatCardsProvince(data) {
            const map = {
                statTotalAllP: fmtNum(data.totalAll),
                statItemCountP: fmtNum(data.totalItemCount),
                statGroupCountP: data.byGroup.length,
                statProvinceCount: data.byProvince.length
            };
            Object.keys(map).forEach(id => {
                const el = document.getElementById(id);
                if (el.textContent !== String(map[id])) {
                    el.textContent = map[id];
                    flashEl(el);
                }
            });
        }

        function buildRowsMapP(rows) {
            const m = new Map();
            rows.forEach(r => m.set(rowKeyP(r), r.qty));
            return m;
        }

        function applySearchFilterProvince() {
            const filter = document.getElementById('searchBoxP').value.trim().toLowerCase();
            document.querySelectorAll('#dashTableBodyP tr').forEach(tr => {
                if (tr.classList.contains('province-group-row')) return;
                tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        function renderAllProvince(data, previousRowsMap) {
            updateStatCardsProvince(data);
            renderPillBar(data);
            if (!chartByProvince) {
                buildChartsProvince(data);
            } else {
                updateChartsProvince(data);
            }
            renderMiniTablesProvince(data);
            renderDetailTableProvince(data, previousRowsMap);
            setLastUpdated(data.generatedAt);
        }

        async function fetchAndRefreshProvince() {
            try {
                const url = ENDPOINT + '?view=province' + (activeProvince ? ('&province=' + encodeURIComponent(activeProvince)) : '');
                const res = await fetch(url, { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.error) return;

                const signature = JSON.stringify(data.rows) + '|' + data.totalAll + '|' + activeProvince;
                if (signature === lastSignatureProvince) {
                    setLastUpdated(data.generatedAt);
                    return;
                }

                const previousRowsMap = buildRowsMapP(currentProvinceData.rows);
                currentProvinceData = data;
                lastSignatureProvince = signature;
                renderAllProvince(data, previousRowsMap);
            } catch (e) {
                console.warn('Dashboard refresh failed (province):', e);
            }
        }

        async function selectProvince(province) {
            activeProvince = province || null;
            lastSignatureProvince = null; // ບັງຄັບ render ໃໝ່ທັນທີ
            await fetchAndRefreshProvince();
        }
        window.selectProvince = selectProvince;

        // ============================================================
        // ===== TAB 3: ຍອດເບິກ/ໃຊ້ (usestock) =====
        // ============================================================
        let chartUseTrend, chartByProvinceU, chartByTypeU, chartTopItemsU;

        function buildChartsUseStock(data) {
            // ແນວໂນ້ມຕາມວັນທີ່
            const trendLabels = data.trend.map(t => t.date);
            const trendQty = data.trend.map(t => t.qty);

            const ctxTrend = document.getElementById('chartUseTrend').getContext('2d');
            chartUseTrend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'ຍອດເບິກ/ໃຊ້',
                        data: trendQty,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#0d6efd'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const provLabels = data.byProvince.map(p => p.province);
            const provQty = data.byProvince.map(p => p.qty);
            const provColors = provLabels.map((_, i) => colorAt(i));

            const ctxProv = document.getElementById('chartByProvinceU').getContext('2d');
            chartByProvinceU = new Chart(ctxProv, {
                type: 'bar',
                data: { labels: provLabels, datasets: [{ label: 'ຍອດເບິກ/ໃຊ້', data: provQty, backgroundColor: provColors, borderRadius: 8, maxBarThickness: 40 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => 'ຍອດ: ' + fmtNum(ctx.raw) } } },
                    onClick: (evt, els) => { if (els.length) selectProvinceU(provLabels[els[0].index]); },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const typeLabels = data.byType.map(t => t.type);
            const typeQty = data.byType.map(t => t.qty);
            const typeColors = typeLabels.map((_, i) => colorAt(i + 5));

            const ctxType = document.getElementById('chartByTypeU').getContext('2d');
            chartByTypeU = new Chart(ctxType, {
                type: 'doughnut',
                data: { labels: typeLabels, datasets: [{ data: typeQty, backgroundColor: typeColors, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '58%' }
            });

            const topLabels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            const topQty = data.topItems.map(i => i.qty);

            const ctxTop = document.getElementById('chartTopItemsU').getContext('2d');
            chartTopItemsU = new Chart(ctxTop, {
                type: 'bar',
                data: { labels: topLabels, datasets: [{ label: 'ຍອດ', data: topQty, backgroundColor: '#fd7e14', borderRadius: 8, maxBarThickness: 26 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const scopeTxt = activeProvinceU ? ' - ແຂວງ ' + activeProvinceU : ' (ທຸກແຂວງ)';
            document.getElementById('usestockScopeLabel').textContent = scopeTxt;
            document.getElementById('topItemsScopeLabelU').textContent = scopeTxt;
        }

        function updateChartsUseStock(data) {
            chartUseTrend.data.labels = data.trend.map(t => t.date);
            chartUseTrend.data.datasets[0].data = data.trend.map(t => t.qty);
            chartUseTrend.update();

            const provLabels = data.byProvince.map(p => p.province);
            chartByProvinceU.data.labels = provLabels;
            chartByProvinceU.data.datasets[0].data = data.byProvince.map(p => p.qty);
            chartByProvinceU.data.datasets[0].backgroundColor = provLabels.map((_, i) => colorAt(i));
            chartByProvinceU.update();

            chartByTypeU.data.labels = data.byType.map(t => t.type);
            chartByTypeU.data.datasets[0].data = data.byType.map(t => t.qty);
            chartByTypeU.data.datasets[0].backgroundColor = chartByTypeU.data.labels.map((_, i) => colorAt(i + 5));
            chartByTypeU.update();

            chartTopItemsU.data.labels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            chartTopItemsU.data.datasets[0].data = data.topItems.map(i => i.qty);
            chartTopItemsU.update();

            const scopeTxt = activeProvinceU ? ' - ແຂວງ ' + activeProvinceU : ' (ທຸກແຂວງ)';
            document.getElementById('usestockScopeLabel').textContent = scopeTxt;
            document.getElementById('topItemsScopeLabelU').textContent = scopeTxt;
        }

        function renderPillBarU(data) {
            const bar = document.getElementById('provincePillBarU');
            let html = `<span class="province-pill ${!activeProvinceU ? 'active' : ''}" data-province="">
                            <i class="fa-solid fa-globe"></i> ທັງໝົດ
                        </span>`;
            (data.provinceList || []).forEach(p => {
                const isActive = activeProvinceU === p;
                html += `<span class="province-pill ${isActive ? 'active' : ''}" data-province="${escapeHtml(p)}">${escapeHtml(p)}</span>`;
            });
            bar.innerHTML = html;
            bar.querySelectorAll('.province-pill').forEach(pill => {
                pill.addEventListener('click', () => selectProvinceU(pill.dataset.province || null));
            });
        }

        function renderMiniTablesUseStock(data) {
            document.getElementById('byProvinceTableBodyU').innerHTML = data.byProvince.map(p => `
                <tr style="cursor:pointer" onclick="selectProvinceU('${escapeAttr(p.province)}')">
                    <td><span class="province-badge">${escapeHtml(p.province)}</span></td>
                    <td class="text-end">${p.itemCount}</td>
                    <td class="text-end">${p.typeCount}</td>
                    <td class="text-end fw-bold">${fmtNum(p.qty)}</td>
                </tr>
            `).join('');

            document.getElementById('byTypeTableBodyU').innerHTML = data.byType.map(t => `
                <tr>
                    <td><span class="type-badge">${escapeHtml(t.type)}</span></td>
                    <td class="text-end">${t.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(t.qty)}</td>
                </tr>
            `).join('');
        }

        function rowKeyU(r) { return r.province + '|' + r.type + '|' + r.items; }

        function renderDetailTableUseStock(data, previousRowsMap) {
            const byProvinceMap = {};
            data.byProvince.forEach(p => byProvinceMap[p.province] = p);

            const body = document.getElementById('dashTableBodyU');
            let html = '';
            let lastProvince = null;
            let idx = 0;

            if (data.rows.length === 0) {
                body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">ບໍ່ພົບຂໍ້ມູນ</td></tr>';
                return;
            }

            data.rows.forEach(row => {
                idx++;
                if (row.province !== lastProvince) {
                    lastProvince = row.province;
                    const pData = byProvinceMap[row.province];
                    html += `
                        <tr class="province-group-row">
                            <td colspan="6">
                                <i class="fa-solid fa-map-location-dot"></i> ${escapeHtml(row.province)}
                                <span class="text-muted fw-normal">(ຍອດລວມແຂວງນີ້: ${fmtNum(pData ? pData.qty : 0)})</span>
                            </td>
                        </tr>`;
                }

                const key = rowKeyU(row);
                const isNew = previousRowsMap && !previousRowsMap.has(key);
                const rowClass = isNew ? ' class="row-new"' : '';

                html += `
                    <tr${rowClass}>
                        <td>${idx}</td>
                        <td><span class="province-badge">${escapeHtml(row.province)}</span></td>
                        <td><span class="type-badge">${escapeHtml(row.type)}</span></td>
                        <td>${escapeHtml(row.items)}</td>
                        <td>${escapeHtml(row.lastDate)}</td>
                        <td class="text-end fw-bold">${fmtNum(row.qty)}</td>
                    </tr>`;
            });

            body.innerHTML = html;
            applySearchFilterUseStock();
        }

        function updateStatCardsUseStock(data) {
            const map = {
                statTotalAllU: fmtNum(data.totalAll),
                statItemCountU: fmtNum(data.totalItemCount),
                statTypeCountU: data.byType.length,
                statProvinceCountU: data.byProvince.length
            };
            Object.keys(map).forEach(id => {
                const el = document.getElementById(id);
                if (el.textContent !== String(map[id])) {
                    el.textContent = map[id];
                    flashEl(el);
                }
            });
        }

        function buildRowsMapU(rows) {
            const m = new Map();
            rows.forEach(r => m.set(rowKeyU(r), r.qty));
            return m;
        }

        function applySearchFilterUseStock() {
            const filter = document.getElementById('searchBoxU').value.trim().toLowerCase();
            document.querySelectorAll('#dashTableBodyU tr').forEach(tr => {
                if (tr.classList.contains('province-group-row')) return;
                tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        function renderAllUseStock(data, previousRowsMap) {
            updateStatCardsUseStock(data);
            renderPillBarU(data);
            if (!chartUseTrend) {
                buildChartsUseStock(data);
            } else {
                updateChartsUseStock(data);
            }
            renderMiniTablesUseStock(data);
            renderDetailTableUseStock(data, previousRowsMap);
            setLastUpdated(data.generatedAt);
        }

        async function fetchAndRefreshUseStock() {
            try {
                const url = ENDPOINT + '?view=usestock' + (activeProvinceU ? ('&province=' + encodeURIComponent(activeProvinceU)) : '');
                const res = await fetch(url, { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.error) return;

                const signature = JSON.stringify(data.rows) + '|' + JSON.stringify(data.trend) + '|' + data.totalAll + '|' + activeProvinceU;
                if (signature === lastSignatureUseStock) {
                    setLastUpdated(data.generatedAt);
                    return;
                }

                const previousRowsMap = buildRowsMapU(currentUseStockData.rows);
                currentUseStockData = data;
                lastSignatureUseStock = signature;
                renderAllUseStock(data, previousRowsMap);
            } catch (e) {
                console.warn('Dashboard refresh failed (usestock):', e);
            }
        }

        async function selectProvinceU(province) {
            activeProvinceU = province || null;
            lastSignatureUseStock = null; // ບັງຄັບ render ໃໝ່ທັນທີ
            await fetchAndRefreshUseStock();
        }
        window.selectProvinceU = selectProvinceU;

        // ============================================================
        // ===== TAB 4: ຍອດຂໍເບິກ (request) - column ແຂວງ "Province" ບໍ່ມີ s =====
        // ============================================================
        let chartByProvinceR, chartByGroupR, chartTopItemsR;

        function buildChartsRequest(data) {
            const provLabels = data.byProvince.map(p => p.province);
            const provQtyReq = data.byProvince.map(p => p.qty);
            const provQtyGive = data.byProvince.map(p => p.qtyGive);

            const ctxProv = document.getElementById('chartByProvinceR').getContext('2d');
            chartByProvinceR = new Chart(ctxProv, {
                type: 'bar',
                data: {
                    labels: provLabels,
                    datasets: [
                        { label: 'ຂໍເບິກ', data: provQtyReq, backgroundColor: '#fd7e14', borderRadius: 8, maxBarThickness: 30 },
                        { label: 'ອະນຸມັດ', data: provQtyGive, backgroundColor: '#0dcaf0', borderRadius: 8, maxBarThickness: 30 }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': ' + fmtNum(ctx.raw) } } },
                    onClick: (evt, els) => { if (els.length) selectProvinceR(provLabels[els[0].index]); },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            const groupLabels = data.byGroup.map(g => g.group);
            const groupQty = data.byGroup.map(g => g.qty);
            const groupColors = groupLabels.map((_, i) => colorAt(i + 5));

            const ctxGroup = document.getElementById('chartByGroupR').getContext('2d');
            chartByGroupR = new Chart(ctxGroup, {
                type: 'doughnut',
                data: { labels: groupLabels, datasets: [{ data: groupQty, backgroundColor: groupColors, borderWidth: 2, borderColor: '#fff' }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } }, cutout: '58%' }
            });

            const topLabels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            const topQty = data.topItems.map(i => i.qty);

            const ctxTop = document.getElementById('chartTopItemsR').getContext('2d');
            chartTopItemsR = new Chart(ctxTop, {
                type: 'bar',
                data: { labels: topLabels, datasets: [{ label: 'ຂໍເບິກ', data: topQty, backgroundColor: '#fd7e14', borderRadius: 8, maxBarThickness: 26 }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            document.getElementById('topItemsScopeLabelR').textContent = activeProvinceR ? ' - ແຂວງ ' + activeProvinceR : ' (ທຸກແຂວງ)';
        }

        function updateChartsRequest(data) {
            const provLabels = data.byProvince.map(p => p.province);
            chartByProvinceR.data.labels = provLabels;
            chartByProvinceR.data.datasets[0].data = data.byProvince.map(p => p.qty);
            chartByProvinceR.data.datasets[1].data = data.byProvince.map(p => p.qtyGive);
            chartByProvinceR.update();

            chartByGroupR.data.labels = data.byGroup.map(g => g.group);
            chartByGroupR.data.datasets[0].data = data.byGroup.map(g => g.qty);
            chartByGroupR.data.datasets[0].backgroundColor = chartByGroupR.data.labels.map((_, i) => colorAt(i + 5));
            chartByGroupR.update();

            chartTopItemsR.data.labels = data.topItems.map(i => i.items + ' (' + i.province + ')');
            chartTopItemsR.data.datasets[0].data = data.topItems.map(i => i.qty);
            chartTopItemsR.update();

            document.getElementById('topItemsScopeLabelR').textContent = activeProvinceR ? ' - ແຂວງ ' + activeProvinceR : ' (ທຸກແຂວງ)';
        }

        function renderPillBarR(data) {
            const bar = document.getElementById('provincePillBarR');
            let html = `<span class="province-pill ${!activeProvinceR ? 'active' : ''}" data-province="">
                            <i class="fa-solid fa-globe"></i> ທັງໝົດ
                        </span>`;
            (data.provinceList || []).forEach(p => {
                const isActive = activeProvinceR === p;
                html += `<span class="province-pill ${isActive ? 'active' : ''}" data-province="${escapeHtml(p)}">${escapeHtml(p)}</span>`;
            });
            bar.innerHTML = html;
            bar.querySelectorAll('.province-pill').forEach(pill => {
                pill.addEventListener('click', () => selectProvinceR(pill.dataset.province || null));
            });
        }

        function renderMiniTablesRequest(data) {
            document.getElementById('byProvinceTableBodyR').innerHTML = data.byProvince.map(p => `
                <tr style="cursor:pointer" onclick="selectProvinceR('${escapeAttr(p.province)}')">
                    <td><span class="province-badge">${escapeHtml(p.province)}</span></td>
                    <td class="text-end">${p.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(p.qty)}</td>
                    <td class="text-end"><span class="give-badge">${fmtNum(p.qtyGive)}</span></td>
                </tr>
            `).join('');

            document.getElementById('byGroupTableBodyR').innerHTML = data.byGroup.map(g => `
                <tr>
                    <td><span class="group-badge">${escapeHtml(g.group)}</span></td>
                    <td class="text-end">${g.itemCount}</td>
                    <td class="text-end fw-bold">${fmtNum(g.qty)}</td>
                </tr>
            `).join('');
        }

        function rowKeyR(r) { return r.province + '|' + r.type + '|' + r.group + '|' + r.items + '|' + (r.itemCode || ''); }

        function renderDetailTableRequest(data, previousRowsMap) {
            const byProvinceMap = {};
            data.byProvince.forEach(p => byProvinceMap[p.province] = p);

            const body = document.getElementById('dashTableBodyR');
            let html = '';
            let lastProvince = null;
            let idx = 0;

            if (data.rows.length === 0) {
                body.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-4">ບໍ່ພົບຂໍ້ມູນ</td></tr>';
                return;
            }

            data.rows.forEach(row => {
                idx++;
                if (row.province !== lastProvince) {
                    lastProvince = row.province;
                    const pData = byProvinceMap[row.province];
                    html += `
                        <tr class="province-group-row">
                            <td colspan="9">
                                <i class="fa-solid fa-map-location-dot"></i> ${escapeHtml(row.province)}
                                <span class="text-muted fw-normal">(ຂໍເບິກລວມແຂວງນີ້: ${fmtNum(pData ? pData.qty : 0)} | ອະນຸມັດ: ${fmtNum(pData ? pData.qtyGive : 0)})</span>
                            </td>
                        </tr>`;
                }

                const key = rowKeyR(row);
                const isNew = previousRowsMap && !previousRowsMap.has(key);
                const rowClass = isNew ? ' class="row-new"' : '';

                html += `
                    <tr${rowClass}>
                        <td>${idx}</td>
                        <td><span class="province-badge">${escapeHtml(row.province)}</span></td>
                        <td><span class="group-badge">${escapeHtml(row.group)}</span></td>
                        <td><span class="type-badge">${escapeHtml(row.type)}</span></td>
                        <td><span class="code-badge">${escapeHtml(row.itemCode || '')}</span></td>
                        <td>${escapeHtml(row.items)}</td>
                        <td class="text-end fw-bold">${fmtNum(row.qty)}</td>
                        <td class="text-end"><span class="give-badge">${fmtNum(row.qtyGive)}</span></td>
                        <td class="text-end"><span class="pending-badge">${fmtNum(row.qtyPending)}</span></td>
                    </tr>`;
            });

            body.innerHTML = html;
            applySearchFilterRequest();
        }

        function updateStatCardsRequest(data) {
            const map = {
                statTotalAllR: fmtNum(data.totalAll),
                statTotalGiveR: fmtNum(data.totalGive),
                statTotalPendingR: fmtNum(data.totalPending),
                statProvinceCountR: data.byProvince.length
            };
            Object.keys(map).forEach(id => {
                const el = document.getElementById(id);
                if (el.textContent !== String(map[id])) {
                    el.textContent = map[id];
                    flashEl(el);
                }
            });
        }

        function buildRowsMapR(rows) {
            const m = new Map();
            rows.forEach(r => m.set(rowKeyR(r), r.qty));
            return m;
        }

        function applySearchFilterRequest() {
            const filter = document.getElementById('searchBoxR').value.trim().toLowerCase();
            document.querySelectorAll('#dashTableBodyR tr').forEach(tr => {
                if (tr.classList.contains('province-group-row')) return;
                tr.style.display = tr.innerText.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        function renderAllRequest(data, previousRowsMap) {
            updateStatCardsRequest(data);
            renderPillBarR(data);
            if (!chartByProvinceR) {
                buildChartsRequest(data);
            } else {
                updateChartsRequest(data);
            }
            renderMiniTablesRequest(data);
            renderDetailTableRequest(data, previousRowsMap);
            setLastUpdated(data.generatedAt);
        }

        async function fetchAndRefreshRequest() {
            try {
                const url = ENDPOINT + '?view=request' + (activeProvinceR ? ('&province=' + encodeURIComponent(activeProvinceR)) : '');
                const res = await fetch(url, { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.error) return;

                const signature = JSON.stringify(data.rows) + '|' + data.totalAll + '|' + data.totalGive + '|' + activeProvinceR;
                if (signature === lastSignatureRequest) {
                    setLastUpdated(data.generatedAt);
                    return;
                }

                const previousRowsMap = buildRowsMapR(currentRequestData.rows);
                currentRequestData = data;
                lastSignatureRequest = signature;
                renderAllRequest(data, previousRowsMap);
            } catch (e) {
                console.warn('Dashboard refresh failed (request):', e);
            }
        }

        async function selectProvinceR(province) {
            activeProvinceR = province || null;
            lastSignatureRequest = null; // ບັງຄັບ render ໃໝ່ທັນທີ
            await fetchAndRefreshRequest();
        }
        window.selectProvinceR = selectProvinceR;

        // ============================================================
        // ===== ອັບເດດຕາມ tab ທີ່ active ຢູ່ (poll ສະເພາະ tab ນັ້ນ) =====
        // ============================================================
        function pollActiveTab() {
            if (activeTab === 'type') {
                fetchAndRefreshType();
            } else if (activeTab === 'province') {
                fetchAndRefreshProvince();
            } else if (activeTab === 'usestock') {
                fetchAndRefreshUseStock();
            } else {
                fetchAndRefreshRequest();
            }
        }

        document.getElementById('tab-type-btn').addEventListener('shown.bs.tab', () => {
            activeTab = 'type';
            fetchAndRefreshType();
        });
        document.getElementById('tab-province-btn').addEventListener('shown.bs.tab', () => {
            activeTab = 'province';
            fetchAndRefreshProvince();
        });
        document.getElementById('tab-usestock-btn').addEventListener('shown.bs.tab', () => {
            activeTab = 'usestock';
            fetchAndRefreshUseStock();
        });
        document.getElementById('tab-request-btn').addEventListener('shown.bs.tab', () => {
            activeTab = 'request';
            fetchAndRefreshRequest();
        });

        // ===== Init =====
        document.addEventListener('DOMContentLoaded', function () {
            buildCharts(currentData);
            renderMiniTables(currentData);
            renderDetailTable(currentData, null);
            lastSignature = JSON.stringify(currentData.rows) + '|' + currentData.totalAll;

            renderAllProvince(currentProvinceData, null);
            lastSignatureProvince = JSON.stringify(currentProvinceData.rows) + '|' + currentProvinceData.totalAll + '|' + activeProvince;

            renderAllUseStock(currentUseStockData, null);
            lastSignatureUseStock = JSON.stringify(currentUseStockData.rows) + '|' + JSON.stringify(currentUseStockData.trend) + '|' + currentUseStockData.totalAll + '|' + activeProvinceU;

            renderAllRequest(currentRequestData, null);
            lastSignatureRequest = JSON.stringify(currentRequestData.rows) + '|' + currentRequestData.totalAll + '|' + currentRequestData.totalGive + '|' + activeProvinceR;

            document.getElementById('searchBox').addEventListener('keyup', applySearchFilter);
            document.getElementById('searchBoxP').addEventListener('keyup', applySearchFilterProvince);
            document.getElementById('searchBoxU').addEventListener('keyup', applySearchFilterUseStock);
            document.getElementById('searchBoxR').addEventListener('keyup', applySearchFilterRequest);

            setInterval(pollActiveTab, REFRESH_MS);
        });

      // -------------------------------//-------------------------------------------------------------//
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

		function updateToggleIcon(hidden) {
			if (!sidebarToggleBtn) return;
			var icon = sidebarToggleBtn.querySelector('i');
			var label = sidebarToggleBtn.querySelector('.toggle-label');
			if (icon) icon.className = hidden ? 'fa fa-eye' : 'fa fa-eye-slash';
			if (label) label.textContent = hidden ? '\u00A0Show Menu' : '\u00A0Hide Menu';
			sidebarToggleBtn.setAttribute('aria-expanded', String(!hidden));
		}

		function closeSidebar() {
			wrapperEl.classList.remove('sidebar-open');
			sidebarBackdrop.classList.remove('show');
			document.body.style.overflow = '';
			updateToggleIcon(true);
		}

		sidebarToggleBtn?.addEventListener('click', function() {
			if (isDesktop()) {
				wrapperEl.classList.toggle('sidebar-hidden-desktop');
				var hiddenDesktop = wrapperEl.classList.contains('sidebar-hidden-desktop');
				updateToggleIcon(hiddenDesktop);
			} else {
				var isOpen = wrapperEl.classList.toggle('sidebar-open');
				sidebarBackdrop.classList.toggle('show', isOpen);
				document.body.style.overflow = isOpen ? 'hidden' : '';
				updateToggleIcon(!isOpen);
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
				wrapperEl.classList.remove('sidebar-open');
			} else {
				wrapperEl.classList.remove('sidebar-hidden-desktop');
				updateToggleIcon(false);
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
						row.innerHTML = '<div class="equipment-suggestion-name">' + escapeHtml(item.name) + '</div>' +
							(item.chinese_name ? '<div class="equipment-suggestion-chinese">' + escapeHtml(item.chinese_name) + '</div>' : '') +
							'<div class="equipment-suggestion-meta"><span>ປະເພດ: ' + escapeHtml(item.type) + '</span><span>ຂະໜາດ: ' + escapeHtml(item.size) + '</span><span>Model: ' + escapeHtml(item.model) + '</span></div>';
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

    <!-- Core theme JS -->
	<script src="js/scripts.js"></script>
      <!-- Translate -->
	<script src="translate/lang.js"></script>
</body>

</html>
