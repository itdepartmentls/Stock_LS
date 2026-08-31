<?php
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
    <link rel="stylesheet" href="js/pro.min.js">
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

        .btn-primary {
            background-color: #2d8a4e !important;
            border-color: #2d8a4e !important;
        }

        .btn-primary:hover {
            background-color: #237a42 !important;
            border-color: #237a42 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 138, 78, 0.3);
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

        /* ===== Select2 ===== */
        .select2-container .select2-selection--single {
            border-radius: 8px !important;
            border-color: #e0e0e0 !important;
            height: 38px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
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

        .card-soft {
            background: transparent;
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 20px;
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

        /* ============================================================
           RESPONSIVE — ຮອງຮັບທຸກຫນ້າຈໍ
           Desktop (>=992px): sidebar ຄົງທີ່ຢູ່ຂ້າງໆເນື້ອໃນ
           Tablet/Mobile (<992px): sidebar ກາຍເປັນ off-canvas drawer
           ============================================================ */

        /* Backdrop ສຳລັບປິດ sidebar ເມື່ອກົດນອກ (mobile/tablet) */
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

            /* ປຸ່ມ Hide/Show ຢູ່ desktop: ເລື່ອນ sidebar ອອກແທນ overlay */
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

            iframe {
                min-height: 70vh;
            }

            h4 {
                font-size: 1.1rem;
            }

            .navbar .container-fluid {
                gap: 0.5rem;
            }

            .d-flex.justify-content-between.align-items-center.mb-3 {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.5rem;
            }

            .d-flex.justify-content-between.align-items-center.mb-3>div:last-child {
                text-align: center;
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

            iframe {
                min-height: 65vh;
                border-radius: 10px;
            }

            .card-soft {
                padding: 10px;
            }
        }
    </style>

    <?php
    $proo = $_SESSION["Namepro"] ?? '';
    $userId = $_SESSION["iduser"] ?? '';

    // ກຳນົດຜູ້ໃຊ້ Admin
    $adminUsers = ['404', '30', '194', '793'];
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
        <!-- ===== Sidebar ສີຟ້າ ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <!-- <div class="sidebar-heading border-bottom">
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
            </div> -->

            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
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
                        <!-- <i class="fas fa-bars text-white"></i> -->
                        <!-- ຫຼື ໃຊ້ fa-solid -->
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

                <?php if (!in_array($_SESSION["iduser"] ?? '', ['404', '30', '194', '793'])): ?>
                    <!-- ===== ປຸ່ມນຳໃຊ້ອຸປະກອນ ===== -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <a href="usestockData.php" class="text-decoration-none">
                                <button type="button" name="button" class="btn btn-success px-4">
                                    <i class="fa-solid fa-router"></i>&nbsp; ນຳໃຊ້ອຸປະກອນ
                                </button>
                            </a>
                        </div>
                        <div>
                            <span class="text-muted small">
                                <i class="bi bi-info-circle"></i> ສະບາຍດີ, <?= htmlspecialchars($_SESSION["user"] ?? '') ?>
                            </span>
                        </div>
                    </div>

                    <!-- ===== iframe ສະແດງຂໍ້ມູນ ===== -->
                    <div class="card-soft">
                        <iframe class="circle" src="DatauseStock.php" name="DatauseStock" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                        </iframe>
                    </div>
                <?php else: ?>
                    <!-- ===== iframe ສຳລັບ Admin ===== -->
                    <div class="card-soft">
                        <iframe class="circle" src="DatauseStock.php" name="DatauseStock" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                        </iframe>
                    </div>
                <?php endif; ?>

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

        // ຟັງຊັນປັບຂະໜາດ iframe (ຮອງຮັບ min-height ຢູ່ mobile ຈາກ CSS)
        function resizeIframe(obj) {
            try {
                var h = obj.contentWindow.document.documentElement.scrollHeight;
                if (h && h > 0) {
                    obj.style.height = h + 'px';
                }
            } catch (e) {
                // ຖ້າ iframe ບໍ່ສາມາດເຂົ້າເຖິງໄດ້ (cross-origin) ໃຫ້ໃຊ້ຄ່າ default/min-height ຈາກ CSS
            }
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

        // ເມື່ອໂຫຼດໜ້າແລ້ວ
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
    </script>

</body>

</html>