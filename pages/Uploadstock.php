<?php

// Uploadstock.php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

/* ຕັດຊ່ອງຫວ່າງ/newline ທີ່ຕິດແຖບ (trim) ໃຫ້ທຸກ field ໃນແຖວ CSV ນຶ່ງແຖວ */
function clean_row($column)
{
    foreach ($column as $k => $v) {
        $column[$k] = trim((string) $v);
    }
    return $column;
}

/* =============================================================
   ອ່ານ CSV ໃຫ້ໜັກແໜ້ນກວ່າ fopen()+fgetcsv() ແບບເດີມ.
   ============================================================= */
function read_csv_rows($filePath)
{
    $content = file_get_contents($filePath);
    if ($content === false) {
        return [];
    }

    // ຕັດ BOM (EF BB BF) ຢູ່ຫົວໄຟລ໌ ຖ້າມີ
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }

    // normalize ທຸກຮູບແບບ line-ending ໃຫ້ເປັນ "\n"
    $content = str_replace(["\r\n", "\r"], "\n", $content);

    $lines = explode("\n", $content);
    $rows = [];
    foreach ($lines as $line) {
        if (trim($line) === '') {
            continue; // ຂ້າມແຖວຫວ່າງ
        }
        $rows[] = str_getcsv($line, ",");
    }
    return $rows;
}

/* =============================================================
   ກວດວ່າແຖວທຳອິດຂອງ CSV ແມ່ນ "header" ຫຼືແມ່ນ "ຂໍ້ມູນຈິງ".
   ============================================================= */
function csv_first_row_is_header($rows)
{
    if (empty($rows) || !isset($rows[0][0])) {
        return false;
    }
    $firstCell = mb_strtolower(trim($rows[0][0]), 'UTF-8');
    $knownHeaders = ['items', 'item', 'ຊື່ສິນຄ້າ', 'ຊື່ອຸປະກອນ'];
    return in_array($firstCell, $knownHeaders, true);
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

    <title>ອັບໂຫລດສາງ</title>

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

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
            border-color: #f39c12 !important;
            color: #fff !important;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e67e22, #d35400) !important;
            border-color: #e67e22 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4);
            color: #fff !important;
        }

        /* ===== ປຸ່ມດາວໂຫລດແມ່ແບບ (Template) ===== */
        .btn-outline-template {
            background: #fff;
            border: 1.5px solid #1a5276;
            color: #1a5276 !important;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-outline-template:hover {
            background: #1a5276;
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 82, 118, 0.3);
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

        /* ===== ຄົ້ນຫາອຸປະກອນ - select2 highlight ===== */
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

        /* ============================================================
           RESPONSIVE — ຮອງຮັບທຸກຫນ້າຈໍ
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

            .card-soft {
                padding: 10px;
            }

            .d-flex.flex-wrap.gap-2 .btn {
                width: 100% !important;
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

                <?php
                date_default_timezone_set('Asia/Bangkok');
                $date_time = date('20y-m-d G:i');
                $col = 1;
                $type = "";
                $message = "";
                $addType = "";
                $addMessage = "";

                // ຖ້າມີຂໍ້ຄວາມຄ້າງໄວ້ຈາກ redirect
                if (isset($_SESSION['flash_type'])) {
                    $type = $_SESSION['flash_type'];
                    $message = $_SESSION['flash_message'];
                    unset($_SESSION['flash_type'], $_SESSION['flash_message']);
                }
                if (isset($_SESSION['flash_addType'])) {
                    $addType = $_SESSION['flash_addType'];
                    $addMessage = $_SESSION['flash_addMessage'];
                    unset($_SESSION['flash_addType'], $_SESSION['flash_addMessage']);
                }

                set_time_limit(3600);

                /* ==========================================================================
                   ເພີ່ມຂໍ້ມູນໃໝ່ (.CSV)
                   ========================================================================== */
                if (isset($_POST["import"])) {

                    $fileName = $_FILES["file"]["tmp_name"];
                    if ($_FILES["file"]["size"] > 0) {
                        try {
                            $conn->begin_transaction();

                            $dup = 0;
                            $added = 0;
                            $col = 0;

                            mysqli_set_charset(@$conn, "utf8");

                            $stmtCheck = $conn->prepare("SELECT id FROM stock WHERE Items = ?");
                            $stmtInsertStock = $conn->prepare(
                                "INSERT INTO stock
                                (id, Items, Item_name_Chinese, Model, Size, packet_size, Item_code,
                                 Vendor, Use_For, Type, Unit, Stock_TMD, Total_Unit, Groupp, Category,
                                 picture, Section, User_Stock, Total_Price, name_of_price)
                             VALUES
                                (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                            );
                            $stmtInsertLog = $conn->prepare(
                                "INSERT into stockinput (id,Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
                               values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,'Upload')"
                            );

                            $csvRows = read_csv_rows($fileName);
                            if (csv_first_row_is_header($csvRows)) {
                                array_shift($csvRows);
                            }

                            foreach ($csvRows as $column) {
                                $column = clean_row($column);

                                $Items              = $column[0]  ?? '';
                                $Item_name_Chinese  = $column[1]  ?? '';
                                $Model              = $column[2]  ?? '';
                                $Size               = $column[3]  ?? '';
                                $packet_size        = $column[4]  ?? '';
                                $Item_code          = $column[5]  ?? '';
                                $Vendor             = $column[6]  ?? '';
                                $Use_For            = $column[7]  ?? '';
                                $Type               = $column[8]  ?? '';
                                $Unit               = is_numeric($column[9]  ?? null) ? $column[9]  : 0;
                                $Stock_TMD          = is_numeric($column[10] ?? null) ? $column[10] : 0;
                                $Total_Unit         = is_numeric($column[11] ?? null) ? $column[11] : 0;
                                $Groupp             = $column[12] ?? '';
                                $Category           = $column[13] ?? '';
                                $picture            = $column[14] ?? '';
                                $Section            = $column[15] ?? '';
                                $Total_Price        = is_numeric($column[16] ?? null) ? $column[16] : 0;
                                $name_of_pric       = $column[17] ?? '';
                                $User_Stock         = $_SESSION["user"] . " " . $date_time;

                                if ($Items === '') {
                                    $col++;
                                    continue;
                                }

                                $stmtCheck->bind_param("s", $Items);
                                $stmtCheck->execute();
                                $stmtCheck->store_result();

                                if ($stmtCheck->num_rows > 0) {
                                    $dup++;
                                } else {
                                    $stmtInsertStock->bind_param(
                                        "sssssssssdddsssssds",
                                        $Items,
                                        $Item_name_Chinese,
                                        $Model,
                                        $Size,
                                        $packet_size,
                                        $Item_code,
                                        $Vendor,
                                        $Use_For,
                                        $Type,
                                        $Unit,
                                        $Stock_TMD,
                                        $Total_Unit,
                                        $Groupp,
                                        $Category,
                                        $picture,
                                        $Section,
                                        $User_Stock,
                                        $Total_Price,
                                        $name_of_pric
                                    );
                                    $stmtInsertStock->execute();

                                    $stmtInsertLog->bind_param(
                                        "sssssdssssss",
                                        $Items,
                                        $Item_code,
                                        $Vendor,
                                        $Use_For,
                                        $Type,
                                        $Unit,
                                        $Stock_TMD,
                                        $Groupp,
                                        $picture,
                                        $Section,
                                        $date_time,
                                        $_SESSION["user"]
                                    );
                                    $stmtInsertLog->execute();

                                    $added++;
                                }
                                $stmtCheck->free_result();

                                $col++;
                                echo $col . '-';
                                if ($col % 100 == 0) {
                                    echo '<br>';
                                }
                            }
                            $conn->commit();

                            $type = "success";
                            $message = "ການນຳເຂົ້າຂໍ້ມູນສຳເລັດ — ເພີ່ມໃໝ່ " . $added . " ແຖວ, ຂ້າມ (ຊໍ້າ) " . $dup . " ແຖວ";
                        } catch (mysqli_sql_exception $e) {
                            @$conn->rollback();
                            $type = "error";
                            $message = "ນຳເຂົ້າຂໍ້ມູນລົ້ມເຫລວ (DB error): " . $e->getMessage();
                        }
                    } elseif (($_FILES["file"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $type = "error";
                        $message = "ອັບໂຫລດໄຟລ໌ບໍ່ສຳເລັດ (ລະຫັດ error: " . $_FILES["file"]["error"] . ")";
                    }

                    $_SESSION['flash_type'] = $type;
                    $_SESSION['flash_message'] = $message;
                    echo "<script>window.location = 'Uploadstock.php';</script>";
                    exit;
                }

                /* ==========================================================================
                   ອັບເດດຂໍ້ມູນ (.CSV)
                   ========================================================================== */
                if (isset($_POST["stock"])) {

                    $fileName = $_FILES["file"]["tmp_name"];
                    if ($_FILES["file"]["size"] > 0) {
                        try {
                            $conn->begin_transaction();

                            $updated = 0;
                            $notFound = [];
                            $col = 0;

                            mysqli_set_charset(@$conn, "utf8");

                            $stmtUpdate = $conn->prepare(
                                "UPDATE stock SET
                                Stock_TMD = Stock_TMD + ?,
                                User_Stock = ?
                             WHERE Items = ?"
                            );
                            $stmtInsertLog = $conn->prepare(
                                "INSERT into stockinput (id,Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
                               values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,'Upload')"
                            );

                            $csvRows = read_csv_rows($fileName);
                            if (csv_first_row_is_header($csvRows)) {
                                array_shift($csvRows);
                            }

                            foreach ($csvRows as $column) {
                                $column = clean_row($column);

                                $Items              = $column[0]  ?? '';
                                $Item_name_Chinese  = $column[1]  ?? '';
                                $Model              = $column[2]  ?? '';
                                $Size               = $column[3]  ?? '';
                                $packet_size        = $column[4]  ?? '';
                                $Item_code          = $column[5]  ?? '';
                                $Vendor             = $column[6]  ?? '';
                                $Use_For            = $column[7]  ?? '';
                                $Type               = $column[8]  ?? '';
                                $Unit               = is_numeric($column[9]  ?? null) ? $column[9]  : 0;
                                $Stock_TMD          = is_numeric($column[10] ?? null) ? $column[10] : 0;
                                $Total_Unit         = is_numeric($column[11] ?? null) ? $column[11] : 0;
                                $Groupp             = $column[12] ?? '';
                                $Category           = $column[13] ?? '';
                                $picture            = $column[14] ?? '';
                                $Section            = $column[15] ?? '';
                                $Total_Price        = is_numeric($column[16] ?? null) ? $column[16] : 0;
                                $name_of_pric       = $column[17] ?? '';
                                $User_Stock         = $_SESSION["user"] . " " . $date_time;

                                if ($Items === '') {
                                    $col++;
                                    continue;
                                }

                                $stmtUpdate->bind_param(
                                    "dss",
                                    $Stock_TMD,
                                    $User_Stock,
                                    $Items
                                );
                                $stmtUpdate->execute();

                                if ($stmtUpdate->affected_rows > 0) {
                                    $updated++;

                                    $stmtInsertLog->bind_param(
                                        "sssssdssssss",
                                        $Items,
                                        $Item_code,
                                        $Vendor,
                                        $Use_For,
                                        $Type,
                                        $Unit,
                                        $Stock_TMD,
                                        $Groupp,
                                        $picture,
                                        $Section,
                                        $date_time,
                                        $_SESSION["user"]
                                    );
                                    $stmtInsertLog->execute();
                                } else {
                                    $notFound[] = $Items;
                                }

                                $col++;
                                echo $col . '-';
                                if ($col % 100 == 0) {
                                    echo '<br>';
                                }
                            }
                            $conn->commit();

                            $type = "success";
                            $message = "ການອັບເດດຂໍ້ມູນສຳເລັດ — ອັບເດດແທ້ " . $updated . " ແຖວ";
                            if (count($notFound) > 0) {
                                $message .= "<br><span style='color:#a33'>⚠ ບໍ່ພົບໃນ DB ຈຶ່ງບໍ່ອັບເດດ (" . count($notFound) . " ລາຍການ): "
                                    . htmlspecialchars(implode(', ', array_slice($notFound, 0, 30)))
                                    . (count($notFound) > 30 ? ' ...' : '') . "</span>";
                            }
                        } catch (mysqli_sql_exception $e) {
                            @$conn->rollback();
                            $type = "error";
                            $message = "ອັບເດດຂໍ້ມູນລົ້ມເຫລວ (DB error): " . $e->getMessage();
                        }
                    } elseif (($_FILES["file"]["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                        $type = "error";
                        $message = "ອັບໂຫລດໄຟລ໌ບໍ່ສຳເລັດ (ລະຫັດ error: " . $_FILES["file"]["error"] . ")";
                    }

                    $_SESSION['flash_type'] = $type;
                    $_SESSION['flash_message'] = $message;
                    echo "<script>window.location = 'Uploadstock.php';</script>";
                    exit;
                }

                /* ==========================================================================
                   ເພີ່ມສິນຄ້າ (Manual)
                   ========================================================================== */
                if (isset($_POST["addproduct"])) {
                    try {
                        $conn->begin_transaction();

                        $Items              = trim($_POST["Items"]);
                        $Item_name_Chinese  = $_POST["Item_name_Chinese"];
                        $Model              = $_POST["Model"];
                        $Size               = $_POST["Size"];
                        $packet_size        = $_POST["packet_size"];
                        $Item_code          = $_POST["Item_code"];
                        $Vendor             = $_POST["Vendor"];
                        $Use_For            = $_POST["Use_For"];
                        $Type               = $_POST["Type"];
                        $Unit               = $_POST["Unit"] !== "" ? $_POST["Unit"] : 0;
                        $Stock_TMD          = $_POST["Stock_TMD"] !== "" ? $_POST["Stock_TMD"] : 0;
                        $Total_Unit         = $_POST["Total_Unit"] !== "" ? $_POST["Total_Unit"] : 0;
                        $Groupp             = $_POST["Groupp"];
                        $Category           = $_POST["Category"];
                        $Section            = $_POST["Section"];
                        $Total_Price        = $_POST["Total_Price"] !== "" ? $_POST["Total_Price"] : 0;
                        $name_of_pric       = $_POST["name_of_pric"];
                        $User_Stock         = $_SESSION["user"] . " " . $date_time;

                        mysqli_set_charset(@$conn, "utf8");

                        /* =====================================================================
                           ອັບໂຫລດຮູບພາບ (ຖ້າມີການເລືອກໄຟລ໌ຈາກ <input type="file" name="picture_file">)
                           ໄຟລ໌ຈະຖືກເກັບໄວ້ໃນໂຟນເດີ picture/uploads/ ດ້ວຍຊື່ບໍ່ຊ້ຳກັນ
                           ===================================================================== */
                        $picture             = '';
                        $pictureUploadError  = '';

                        if (isset($_FILES['picture_file']) && $_FILES['picture_file']['error'] === UPLOAD_ERR_OK) {
                            $uploadDir = __DIR__ . '/../picture/uploads/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }

                            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
                            $origName   = $_FILES['picture_file']['name'];
                            $ext        = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                            if (in_array($ext, $allowedExt, true)) {
                                $newFileName = 'img_' . uniqid() . '.' . $ext;
                                $destPath    = $uploadDir . $newFileName;

                                if (move_uploaded_file($_FILES['picture_file']['tmp_name'], $destPath)) {
                                    // ເກັບ path ແບບ relative ໄວ້ໃນ DB (ໃຊ້ສະແດງຮູບຢູ່ໜ້າອື່ນໆ)
                                    $picture = 'picture/uploads/' . $newFileName;
                                } else {
                                    $pictureUploadError = "ອັບໂຫລດຮູບພາບບໍ່ສຳເລັດ (ບໍ່ສາມາດຍ້າຍໄຟລ໌ໄດ້)";
                                }
                            } else {
                                $pictureUploadError = "ຊະນິດໄຟລ໌ຮູບບໍ່ຮອງຮັບ (ຮອງຮັບ: jpg, jpeg, png, gif, webp, avif)";
                            }
                        } elseif (isset($_FILES['picture_file']) && $_FILES['picture_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                            $pictureUploadError = "ອັບໂຫລດຮູບພາບບໍ່ສຳເລັດ (ລະຫັດ error: " . $_FILES['picture_file']['error'] . ")";
                        }

                        /* ===== ກວດ/ຄຳນວນ Item_code ຄືນຢູ່ຝັ່ງ server ອີກຄັ້ງ (safety-net) =====
                           ເພື່ອບໍ່ໃຫ້ Item_code ຊໍ້າກັນ ເຖິງແມ່ນຄ່າຈາກ front-end ຈະຖືກສົ່ງມາຜິດ
                           ຫຼືມີ 2 ຄົນເພີ່ມພ້ອມກັນ. ຄຳນວນຄືນຈາກ MAX(Item_code) ຂອງກຸ່ມນັ້ນສະເໝີ. */
                        $groupPrefixMap = [
                            'ຍານພາຫະນະ'              => 1,
                            'ອຸປະກອນສຳນັກງານ'         => 2,
                            'ອຸປະກອນຄວາມປອດໄພ'        => 3,
                            'ອຸປະກອນທົ່ວໄປ'           => 4,
                            'ອຸປະກອນໄຟຟ້າ'            => 5,
                            'ອຸປະກອນລາຍການຜະລິດ'      => 6,
                            'ອຸປະກອນ Lab'             => 7,
                            'ອຸປະກອນກໍ່ສ້າງ'           => 8,
                            'ເຄື່ອງມືຊ່າງ'             => 9,
                        ];

                        if (isset($groupPrefixMap[$Groupp])) {
                            $prefixDigit = $groupPrefixMap[$Groupp];
                            $rangeStart  = $prefixDigit * 10000000;
                            $rangeEnd    = $rangeStart + 9999999;

                            // ລ໋ອກແຖວຂອງກຸ່ມນີ້ (FOR UPDATE) ເພື່ອປ້ອງກັນ race condition ລະຫວ່າງ 2 ຄົນເພີ່ມພ້ອມກັນ
                            $stmtLock = $conn->prepare(
                                "SELECT MAX(CAST(Item_code AS UNSIGNED)) AS max_code
                                   FROM stock
                                  WHERE Groupp = ?
                                    AND Item_code REGEXP '^[0-9]+$'
                                    AND CAST(Item_code AS UNSIGNED) BETWEEN ? AND ?
                                    FOR UPDATE"
                            );
                            $stmtLock->bind_param("sii", $Groupp, $rangeStart, $rangeEnd);
                            $stmtLock->execute();
                            $resLock = $stmtLock->get_result();
                            $rowLock = $resLock->fetch_assoc();
                            $stmtLock->close();

                            $maxCode = $rowLock['max_code'] ?? null;
                            if ($maxCode === null || (int) $maxCode < $rangeStart) {
                                $Item_code = (string) ($rangeStart + 1);
                            } else {
                                $Item_code = (string) ((int) $maxCode + 1);
                            }
                        }
                        // ຖ້າ Groupp ບໍ່ຢູ່ໃນ 9 ກຸ່ມທີ່ຮູ້ຈັກ, ໃຊ້ຄ່າ Item_code ທີ່ສົ່ງມາຈາກຟອມແທນ (fallback)

                        $stmt = $conn->prepare(
                            "INSERT INTO stock
                            (id, Items, Item_name_Chinese, Model, Size, packet_size, Item_code,
                             Vendor, Use_For, Type, Unit, Stock_TMD, Total_Unit, Groupp, Category,
                             picture, Section, User_Stock, Total_Price, name_of_price)
                         VALUES
                            (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                        );
                        $stmt->bind_param(
                            "sssssssssdddsssssds",
                            $Items,
                            $Item_name_Chinese,
                            $Model,
                            $Size,
                            $packet_size,
                            $Item_code,
                            $Vendor,
                            $Use_For,
                            $Type,
                            $Unit,
                            $Stock_TMD,
                            $Total_Unit,
                            $Groupp,
                            $Category,
                            $picture,
                            $Section,
                            $User_Stock,
                            $Total_Price,
                            $name_of_pric
                        );

                        if ($stmt->execute()) {
                            $newId = $conn->insert_id;

                            $stmt2 = $conn->prepare(
                                "INSERT into stockinput (id,Items,Item_code,Vendor,Use_For,Type,Unit,Stock_TMD,Groupp,picture,Section,Date,User,Remarkk)
                               values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,'Add-Manual')"
                            );
                            $stmt2->bind_param(
                                "sssssdssssss",
                                $Items,
                                $Item_code,
                                $Vendor,
                                $Use_For,
                                $Type,
                                $Unit,
                                $Stock_TMD,
                                $Groupp,
                                $picture,
                                $Section,
                                $date_time,
                                $_SESSION["user"]
                            );
                            $stmt2->execute();

                            /* =================================================================
                               ລົງທະບຽນໄຟລ໌ຮູບໃນ picture/uploads/filemap.json
                               =================================================================
                               ໜ້າ "Image Stock" (picture/index.php) ຈະ scan ໂຟນເດີ picture/uploads/
                               ທຸກຄັ້ງທີ່ຖືກເປີດ ແລະ ຖ້າພົບໄຟລ໌ໃດທີ່ "ບໍ່ຢູ່ໃນ filemap.json" ມັນຈະຖືວ່າ
                               ເປັນໄຟລ໌ແປກໜ້າ ແລ້ວ "ປ່ຽນຊື່ໄຟລ໌ໃໝ່ແບບສຸ່ມ" ໃຫ້ອັດຕະໂນມັດ (migrate).
                               ຖ້າເຮົາອັບໂຫລດຮູບຈາກໜ້ານີ້ໂດຍບໍ່ລົງທະບຽນໄວ້ໃນ filemap.json, ຄັ້ງຕໍ່ໄປທີ່
                               ມີໃຜເປີດໜ້າ Image Stock, ໄຟລ໌ຈະຖືກປ່ຽນຊື່ ແລະ path ທີ່ບັນທຶກໄວ້ໃນ
                               stock.picture ຈະໃຊ້ບໍ່ໄດ້ອີກຕໍ່ໄປ (ຮູບຫາຍ/ບໍ່ຂຶ້ນ) — ນີ້ຄືສາເຫດຫຼັກທີ່
                               ຮູບພາບບໍ່ຂຶ້ນຫຼັງອັບໂຫລດ. ຈຶ່ງຕ້ອງລົງທະບຽນໄວ້ທັນທີຫຼັງອັບໂຫລດສຳເລັດ. */
                            if ($picture !== '') {
                                $pictureMapFile = __DIR__ . '/../picture/uploads/filemap.json';
                                $pictureMap = [];
                                if (file_exists($pictureMapFile)) {
                                    $decodedPicMap = json_decode(file_get_contents($pictureMapFile), true);
                                    $pictureMap = is_array($decodedPicMap) ? $decodedPicMap : [];
                                }
                                // ໃຊ້ Item_code ເປັນ "display name" ເພື່ອໃຫ້ຕົງກັບ convention
                                // ຂອງລະບົບ Image Stock (ເບິ່ງ comment ໃນ picture/index.php)
                                $pictureSafeName = basename($picture); // ຕັດ "picture/uploads/" ອອກ, ເອົາສະເພາະຊື່ໄຟລ໌
                                $pictureMap[$pictureSafeName] = $Item_code;
                                file_put_contents($pictureMapFile, json_encode($pictureMap), LOCK_EX);
                            }

                            $addType    = "success";
                            $addMessage = "ເພີ່ມສິນຄ້າ '" . htmlspecialchars($Items) . "' ສຳເລັດແລ້ວ (ID: $newId, Item_code: " . htmlspecialchars($Item_code) . ")";

                            if ($pictureUploadError !== '') {
                                $addMessage .= "<br><span style='color:#a33'>⚠ " . $pictureUploadError . " — ບັນທຶກສິນຄ້າໄດ້ ແຕ່ບໍ່ມີຮູບພາບ</span>";
                            }
                        } else {
                            $addType    = "error";
                            $addMessage = "ມີບັນຫາໃນການເພີ່ມສິນຄ້າ: " . $stmt->error;
                        }
                        $stmt->close();
                        $conn->commit();
                    } catch (mysqli_sql_exception $e) {
                        @$conn->rollback();
                        $addType    = "error";
                        $addMessage = "ມີບັນຫາໃນການເພີ່ມສິນຄ້າ (DB error): " . $e->getMessage();
                    }

                    $_SESSION['flash_addType'] = $addType;
                    $_SESSION['flash_addMessage'] = $addMessage;
                    echo "<script>window.location = 'Uploadstock.php';</script>";
                    exit;
                }
                ?>

                <div class="card-soft">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h3 class="mb-0"><i class="fa fa-database" aria-hidden="true"></i> ນຳຂໍ້ມູນເຂົ້າ Stock</h3>
                        <a href="template/Template_Upload_Stock.csv" download class="btn btn-outline-template">
                            <i class="fa fa-file-excel-o" aria-hidden="true"></i> ດາວໂຫລດ Template Upload Stock (.CSV)
                        </a>
                    </div>

                    <div id="response" class="alert <?php if (!empty($type)) {
                                                        echo $type === 'success' ? 'alert-success' : 'alert-danger';
                                                    } ?> <?php if (!empty($message)) {
                                                                echo 'd-block';
                                                            } else {
                                                                echo 'd-none';
                                                            } ?>">
                        <?php if (!empty($message)) {
                            echo $message;
                        } ?>
                    </div>

                    <form class="form-horizontal" action="" method="post"
                        name="frmCSVImport" id="frmCSVImport" enctype="multipart/form-data">
                        <div class="input-row">
                            <label class="control-label"><strong>ເລືອກ CSV File</strong></label>
                            <input type="file" name="file" class="form-control"
                                id="file" accept=".csv" required>
                            <br>

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <button type="submit" id="submitImport" name="import" class="btn btn-success" style="min-width:200px" onclick="return confirm('ຕ້ອງການນຳເຂົ້າຂໍ້ມູນໃໝ່ຈາກ CSV ນີ້ແທ້ບໍ່?')">
                                    <i class="fa fa-plus-square-o" aria-hidden="true"></i> ເພີ່ມຂໍ້ມູນໃຫມ່ (.CSV)
                                </button>
                                <button type="submit" id="submitStock" name="stock" class="btn btn-warning" style="min-width:200px" onclick="return confirm('ຕ້ອງການອັບເດດຂໍ້ມູນຈາກ CSV ນີ້ແທ້ບໍ່?')">
                                    <i class="fa fa-upload" aria-hidden="true"></i> ອັບເດດຂໍ້ມູນ (.CSV)
                                </button>
                                <button type="button" class="btn btn-primary" style="min-width:200px" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i> ເພີ່ມສິນຄ້າ
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($addMessage)) { ?>
                        <div class="alert <?php echo $addType === 'success' ? 'alert-success' : 'alert-danger'; ?> mt-3">
                            <?php echo $addMessage; ?>
                        </div>
                    <?php } ?>
                </div>

                <!-- Modal ເພີ່ມສິນຄ້າ -->
                <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <form action="" method="post" id="frmAddProduct" enctype="multipart/form-data">
                            <div class="modal-content">
                                <div class="modal-header" style="background: linear-gradient(135deg, #0C2C55, #1a5276);">
                                    <h5 class="modal-title text-white" id="addProductLabel">
                                        <i class="fa fa-plus-square-o"></i> ເພີ່ມສິນຄ້າໃໝ່ (Stock)
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label>ຊື່ສິນຄ້າ (Items) <span class="text-danger">*</span></label>
                                            <input type="text" name="Items" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>ຊື່ສິນຄ້າ (ພາສາຈີນ)</label>
                                            <input type="text" name="Item_name_Chinese" class="form-control">
                                        </div>

                                        <div class="col-md-4">
                                            <label>Model</label>
                                            <input type="text" name="Model" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Size</label>
                                            <input type="text" name="Size" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Packet Size</label>
                                            <input type="text" name="packet_size" class="form-control" placeholder="ຕົວຢ່າງ 1x1">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Groupp (ກຸ່ມ) <span class="text-danger">*</span></label>
                                            <select name="Groupp" id="Groupp" class="form-select" required>
                                                <option value="">-- ເລືອກກຸ່ມ --</option>
                                                <option value="ຍານພາຫະນະ">ຍານພາຫະນະ</option>
                                                <option value="ອຸປະກອນສຳນັກງານ">ອຸປະກອນສຳນັກງານ</option>
                                                <option value="ອຸປະກອນຄວາມປອດໄພ">ອຸປະກອນຄວາມປອດໄພ</option>
                                                <option value="ອຸປະກອນທົ່ວໄປ">ອຸປະກອນທົ່ວໄປ</option>
                                                <option value="ອຸປະກອນໄຟຟ້າ">ອຸປະກອນໄຟຟ້າ</option>
                                                <option value="ອຸປະກອນລາຍການຜະລິດ">ອຸປະກອນລາຍການຜະລິດ</option>
                                                <option value="ອຸປະກອນ Lab">ອຸປະກອນ Lab</option>
                                                <option value="ອຸປະກອນກໍ່ສ້າງ">ອຸປະກອນກໍ່ສ້າງ</option>
                                                <option value="ເຄື່ອງມືຊ່າງ">ເຄື່ອງມືຊ່າງ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Item Code <span class="text-muted" style="font-size:12px;">(ອອກເລກອັດຕະໂນມັດ ຫຼັງເລືອກກຸ່ມ)</span></label>
                                            <input type="text" name="Item_code" id="Item_code" class="form-control" readonly placeholder="ເລືອກກຸ່ມກ່ອນ...">
                                        </div>

                                        <div class="col-md-6">
                                            <label>ລາຄາຕໍ່ຫົວໜ່ວຍ (Vendor field)</label>
                                            <input type="text" name="Vendor" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Use For</label>
                                            <input type="text" name="Use_For" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Type / ປະເພດ</label>
                                            <input type="text" name="Type" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Category (ໝວດໝູ່)</label>
                                            <select name="Category" id="Category" class="form-control" style="width:100%"></select>
                                        </div>

                                        <div class="col-md-3">
                                            <label>Unit (ຈຳນວນ)</label>
                                            <input type="number" step="1" name="Unit" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Stock_TMD</label>
                                            <input type="number" step="1" name="Stock_TMD" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Total Unit</label>
                                            <input type="number" step="1" name="Total_Unit" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label>Total Price</label>
                                            <input type="number" step="0.01" name="Total_Price" class="form-control" value="0">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Section (ພາກສ່ວນ)</label>
                                            <input type="text" name="Section" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>ຊື່ຫົວໜ່ວຍລາຄາ (name_of_pric)</label>
                                            <input type="text" name="name_of_pric" class="form-control">
                                        </div>

                                        <div class="col-md-12">
                                            <label>ຮູບພາບ (ເລືອກໄຟລ໌ຮູບ)</label>
                                            <input type="file" name="picture_file" id="picture_file" class="form-control" accept="image/*">
                                            <small class="text-muted">ຮອງຮັບ: jpg, jpeg, png, gif, webp, avif </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ຍົກເລີກ</button>
                                    <button type="submit" name="addproduct" class="btn btn-success"
                                        onclick="return confirm('ຕ້ອງການເພີ່ມສິນຄ້ານີ້ແທ້ບໍ່?')">
                                        <i class="fa fa-save"></i> ບັນທຶກ
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-soft mt-4">
                    <hr>
                    <form action="Uploadstock.php" method="post" id="searchForm">
                        <label for="cust_id">
                            <h4><strong>ຄົ້ນຫາອຸປະກອນ: ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model</strong></h4>
                            <select class="cust_id related-post form-select" name="Select" id="cust_id" style="color:blue;width:100%;">
                                <option value="">ເລືອກລາຍການອຸປະກອນ</option>

                                <?php
                                $result = mysqli_query($conn, "
                                SELECT Items, Item_name_Chinese, Size, Model, Total_Unit, picture
                                    FROM stock
                                        order by stock.Items ;
                            ");

                                while ($row = mysqli_fetch_assoc($result)) {
                                    $laoName     = $row['Items'] ?? '';
                                    $chineseName = $row['Item_name_Chinese'] ?? '';
                                    $size        = $row['Size'] ?? '';
                                    $Model       = $row['Model'] ?? '';
                                    $qty         = $row['Total_Unit'] ?? '';
                                    $picture     = $row['picture'] ?? '';
                                    $searchText  = $laoName . ' ' . $chineseName . ' ' . $size . ' ' . $Model;
                                    echo '<option value="' . htmlspecialchars($laoName) . '" '
                                        . 'data-lao="' . htmlspecialchars($laoName) . '" '
                                        . 'data-chinese="' . htmlspecialchars($chineseName) . '" '
                                        . 'data-size="' . htmlspecialchars($size) . '" '
                                        . 'data-model="' . htmlspecialchars($Model) . '" '
                                        . 'data-qty="' . htmlspecialchars($qty) . '" '
                                        . 'data-picture="' . htmlspecialchars($picture) . '" '
                                        . 'data-search="' . htmlspecialchars($searchText) . '">'
                                        . htmlspecialchars($laoName) . '</option>';
                                }
                                ?>
                            </select>
                        </label>

                        <?php if (isset($_POST["Select"])) { ?>
                            <?php
                            $select = $_POST["Select"];
                            $stmtSel = $conn->prepare("SELECT * FROM stock WHERE stock.Items = ?");
                            $stmtSel->bind_param("s", $select);
                            $stmtSel->execute();
                            $result = $stmtSel->get_result();

                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <div class="container-fluid mt-3" style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                                    <div class="row">
                                        <div align="left" class="col-sm-12" style="font-size: 15px;">
                                            <strong>
                                                <hr><i class="fa-light fa-forklift fa-lg"></i> <?php echo htmlspecialchars($row['Items']) ?>
                                                <a class="right" href="<?php echo htmlspecialchars($row['picture']) ?>" target="_blank">
                                                    <img src="<?php echo htmlspecialchars($row['picture']) ?>" style="width:250px;height:250px;object-fit:contain;background:#f5f5f5;border-radius:8px;" onerror="this.onerror=null;this.style.display='none';this.insertAdjacentHTML('afterend','<span class=&quot;text-muted&quot;>ບໍ່ມີຮູບ</span>');">
                                                </a>
                                                <br><i class="fi fi-rr-globe"></i> ຊື່ພາສາຈີນ: <?php echo htmlspecialchars($row['Item_name_Chinese']) ?>
                                                <br><i class="fa-solid fa-ruler-combined"></i> ຂະໜາດ: <?php echo htmlspecialchars($row['Size']) ?>
                                                <br><i class="fa-solid fa-tag"></i> Model: <?php echo htmlspecialchars($row['Model']) ?>
                                                <br><i class="fa-light fa-network-wired fa-lg"></i> ພາກສ່ວນ: <?php echo htmlspecialchars($row['Section']) ?>
                                            </strong>
                                            <form action="Uploadstock.php" id="form3" name="form3" method="post">
                                                <div class="row mt-3">
                                                    <input hidden="id" type="text" name="id" id="id" style="color:blue; width:100px " value="<?php echo $row['id'] ?>" />
                                                    <div class="col-sm-3" style="background-color:lavender; border-radius: 8px; padding: 10px;">
                                                        <label><strong>ສາງອຸປະກອນພະແນກສາງສຳນັກງານໃຫຍ່</strong>
                                                            <input type="text" name="Unit" id="Unit" class="form-control" value="<?php echo htmlspecialchars($row['Unit']) ?>" style="color:blue;" /></label>
                                                    </div>
                                                    <div class="col-sm-3" style="background-color:lavender; border-radius: 8px; padding: 10px;">
                                                        <label><strong>ສາງເຄື່ອງໃຊ້ຫ້ອງການສຳນັກງານໃຫຍ່</strong>
                                                            <input type="text" name="Stock_TMD" id="Stock_TMD" class="form-control" style="color:blue;" value="<?php echo htmlspecialchars($row['Stock_TMD']) ?>" /></label>
                                                    </div>
                                                    <div class="col-sm-3" style="background-color:lavender; border-radius: 8px; padding: 10px;">
                                                        <label><strong>ປະເພດ</strong>
                                                            <input type="text" name="Typee" id="Typee" class="form-control" style="color:blue;" value="<?php echo htmlspecialchars($row['Type']) ?>" /></label>
                                                    </div>
                                                    <div class="col-sm-3 d-flex align-items-end">
                                                        <button id="btnSaveStock" style="width: 100%;" class="btn btn-success" name="btnupdate" type="Submit" onclick="return confirm('ຕ້ອງການອັບເດດ Stock ແທ້ບໍ່?')">
                                                            <i class="fa-duotone fa-file-pen fa-lg"></i> ບັນທືກ
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </form>
                </div>

                <?php if (isset($_POST["btnupdate"])) {
                    $idd = $_POST["id"];
                    $Unit = $_POST["Unit"];
                    $Stock_TMD = $_POST["Stock_TMD"];
                    $Typee = $_POST["Typee"];
                    $User_Stock = $_SESSION["user"] . " " . $date_time;

                    $stmtUpd = $conn->prepare(
                        "UPDATE stock SET Unit = ?, Stock_TMD = ?, Type = ?, User_Stock = ? WHERE stock.id = ?"
                    );
                    $stmtUpd->bind_param("ssssi", $Unit, $Stock_TMD, $Typee, $User_Stock, $idd);
                    $stmtUpd->execute();
                ?>
                    <script>
                        window.location = "Uploadstock.php";
                    </script>
                <?php } ?>
            </div>
        </div>
    </div>

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

        // ===== ອອກເລກ Item_code ອັດຕະໂນມັດ ຕາມກຸ່ມ (Groupp) ທີ່ເລືອກ =====
        (function() {
            var groupSelect = document.getElementById('Groupp');
            var itemCodeInput = document.getElementById('Item_code');

            if (!groupSelect || !itemCodeInput) {
                return;
            }

            function fetchNextItemCode(groupp) {
                if (!groupp) {
                    itemCodeInput.value = '';
                    itemCodeInput.placeholder = 'ເລືອກກຸ່ມກ່ອນ...';
                    return;
                }
                itemCodeInput.value = '';
                itemCodeInput.placeholder = 'ກຳລັງອອກເລກ...';

                fetch('get_next_itemcode.php?groupp=' + encodeURIComponent(groupp), {
                        credentials: 'same-origin'
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data && data.item_code) {
                            itemCodeInput.value = data.item_code;
                            itemCodeInput.placeholder = '';
                        } else {
                            itemCodeInput.placeholder = (data && data.error) ? data.error : 'ບໍ່ສາມາດອອກເລກໄດ້';
                        }
                    })
                    .catch(function() {
                        itemCodeInput.placeholder = 'ມີຂໍ້ຜິດພາດ, ລອງໃໝ່ອີກຄັ້ງ';
                    });
            }

            groupSelect.addEventListener('change', function() {
                fetchNextItemCode(this.value);
            });

            // ລ້າງຄ່າຄືນ ທຸກຄັ້ງທີ່ modal ຖືກເປີດຂຶ້ນໃໝ່ ເພື່ອບໍ່ໃຫ້ຄ້າງເລກເກົ່າ
            var addProductModal = document.getElementById('addProductModal');
            addProductModal?.addEventListener('show.bs.modal', function() {
                document.getElementById('frmAddProduct')?.reset();
                itemCodeInput.value = '';
                itemCodeInput.placeholder = 'ເລືອກກຸ່ມກ່ອນ...';
            });
        })();

        $(function () {
            function loadCategoryOptions(selectedValue) {
                $.getJSON('get_categories.php')
                    .done(function (data) {
                        var cats = data.categories || [];
                        var $cat = $('#Category');

                        $cat.empty();
                        $cat.append('<option value=""></option>');
                        cats.forEach(function (c) {
                            $cat.append($('<option>', { value: c, text: c }));
                        });

                        if ($cat.hasClass('select2-hidden-accessible')) {
                            $cat.select2('destroy');
                        }

                        $cat.select2({
                            dropdownParent: $('#addProductModal'),
                            placeholder: 'ເລືອກ ຫຼື ພິມ Category ໃໝ່',
                            allowClear: true,
                            width: '100%',
                            tags: true // ອະນຸຍາດພິມຄ່າໃໝ່ທີ່ບໍ່ມີໃນລາຍການ, ຖ້າບໍ່ຢາກໃຫ້ພິມ ໃຫ້ລຶບແຖວນີ້ອອກ
                        });

                        if (selectedValue) {
                            if ($cat.find("option[value='" + selectedValue + "']").length === 0) {
                                $cat.append($('<option>', { value: selectedValue, text: selectedValue, selected: true }));
                            }
                            $cat.val(selectedValue).trigger('change');
                        }
                    });
            }

            // ໂຫລດ list ໃໝ່ທຸກຄັ້ງທີ່ modal ຖືກເປີດ
            $('#addProductModal').on('show.bs.modal', function () {
                loadCategoryOptions();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var currentLang = localStorage.getItem('site_lang') || 'la';
            switchLanguage(currentLang);

            // ເມື່ອໂຫຼດໜ້າແລ້ວ ໃຫ້ reset ປຸ່ມ submit ຄືນ
            $("#submitImport, #submitStock").prop("disabled", false);

            // ປ້ອງກັນການກົດປຸ່ມ submit ຫຼາຍເທື່ອຊ້ຳກັນ
            $("#submitImport, #submitStock").on("click", function() {
                var btn = this;
                setTimeout(function() {
                    btn.disabled = true;
                }, 0);
            });

            // ກວດສອບ Select2
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                // ຄົ້ນຫາໄດ້ທັງ ຊື່ພາສາລາວ, ຊື່ພາສາຈີນ, ຂະຫນາດ, ແລະ Model
                function itemMatcher(params, data) {
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (typeof data.text === 'undefined' || !data.element) {
                        return null;
                    }
                    var searchable = data.element.getAttribute('data-search') || data.text;
                    if (searchable.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }
                    return null;
                }

                function highlightMatch(text, term) {
                    var $div = $('<div></div>').text(text);
                    var escaped = $div.html();
                    if (!term) return escaped;
                    var escapedTerm = $('<div></div>').text(term).html()
                        .replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    var re = new RegExp('(' + escapedTerm + ')', 'ig');
                    return escaped.replace(re, '<mark>$1</mark>');
                }

                function formatItem(item, term) {
                    if (!item.element || !item.id) {
                        return item.text;
                    }
                    var el = item.element;

                    var lao = el.getAttribute('data-lao') || item.text || '';
                    var chinese = el.getAttribute('data-chinese') || '';
                    var size = el.getAttribute('data-size') || '';
                    var model = el.getAttribute('data-model') || '';

                    var $wrapper = $('<div></div>');
                    $('<div></div>').addClass('fw-bold')
                        .html(highlightMatch(lao, term))
                        .appendTo($wrapper);

                    var parts = [];
                    if (chinese) parts.push(highlightMatch(chinese, term));
                    if (size) parts.push('ຂະຫນາດ: ' + highlightMatch(size, term));
                    if (model) parts.push('Model: ' + highlightMatch(model, term));

                    if (parts.length) {
                        $('<small></small>').addClass('text-muted d-block')
                            .html(parts.join(' &nbsp;|&nbsp; '))
                            .appendTo($wrapper);
                    }

                    return $wrapper;
                }

                $('.cust_id').select2({
                    placeholder: "ເລືອກລາຍການ",
                    allowClear: true,
                    width: '100%',
                    matcher: itemMatcher,
                    templateResult: function(data) {
                        var $field = $('.select2-container--open .select2-search__field');
                        var term = $field.length ? $field.val() : '';
                        return formatItem(data, term);
                    },
                    escapeMarkup: function(markup) {
                        return markup;
                    }
                });

                // ເມື່ອເລືອກລາຍການ ໃຫ້ submit ຟອມທັນທີ
                $('.cust_id').on('select2:select', function() {
                    if (this.value) {
                        this.form.submit();
                    }
                });
            }
        });
    </script>
</body>

</html>