<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
	echo "<script>window.location = 'index.php';</script>";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />

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
    <link rel="stylesheet" href="css/all.min.css">

    <!-- icons ເພີ່ມເຕີມ -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

    <title>ຕິດຕາມອຸປະກອນ</title>
    <!-- Favicon-->

    <!-- Core theme CSS (includes Bootstrap)-->
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

        .buttonright {
            float: right;
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

        .btn-warning:hover,
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        /* ===== Tabs ===== */
        .nav-tabs-custom {
            border-bottom: 2px solid #0C2C55;
            margin-bottom: 0;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .nav-tabs-custom .nav-item {
            padding: 0;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-radius: 0;
            background: #ffffff;
            color: #0C2C55;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            transition: all 0.2s ease;
            margin: 0;
        }

        .nav-tabs-custom .nav-link i {
            font-size: 15px;
        }

        .nav-tabs-custom .nav-link:hover {
            background: #eef2f7;
            color: #0C2C55;
        }

        .nav-tabs-custom .nav-link.active {
            background: #0C2C55;
            color: #ffffff;
        }

        .nav-tabs-custom .nav-link.active i {
            color: #ffffff;
        }

        .tab-content {
            background: #fff;
            border: 1px solid #e9ecef;
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding-top: 4px;
        }

        /* ===== ຕາຕະລາງ (ແບບບໍ່ມີເສັ້ນຂອບ grid, ມີແຕ່ເສັ້ນຄັ່ນແຖວ) ===== */
        .table.circle {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        .table.circle thead tr {
            border-bottom: 2px solid #0C2C55;
        }

        .table.circle thead th {
            border: none;
            color: #0C2C55;
            font-weight: 700;
            padding: 12px 10px;
            background: #ffffff;
            white-space: nowrap;
        }

        .table.circle tbody td {
            border: none;
            border-bottom: 1px solid #e9ecef;
            padding: 10px;
            vertical-align: middle;
        }

        .table.circle tbody tr:last-child td {
            border-bottom: none;
        }

        .table.circle.table-hover tbody tr:hover {
            background-color: #f5f8fb;
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
        }
    </style>

    <script language="JavaScript" type="text/JavaScript">
        function MM_jumpMenu(targ, selObj, restore) { //v3.0
            eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
            if (restore) selObj.selectedIndex = 0;
        }
        //-->
    </script>

    <?php
    $proo = $_SESSION["Namepro"];
    if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
        $sql = "SELECT
COUNT(a.S_Status) as 'cccount'

FROM request a

WHERE a.S_Status in ('1','2','3')  and a.Province ='$proo'
";
    } else {

        $sql = "SELECT
COUNT(a.S_Status) as 'cccount'

FROM request a

WHERE a.S_Status = '1'
";
    }
    mysqli_set_charset(@$conn, "utf8");
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    ?>

</head>

<body>
    <!-- Backdrop ສຳລັບ mobile/tablet -->
    <div id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        <!-- ===== Sidebar ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
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
            <!-- Page content-->

            <div class="container-fluid px-4 py-3">

                &nbsp;&nbsp;<a class="btn btn-secondary btn-sm" href="Createfollow.php">
                    <i class="fa fa-plus-square-o" aria-hidden="true"></i> ສ້າງໃຫມ່</a>
                <br>
                <br>

                <ul class="nav nav-tabs nav-tabs-custom row g-0" role="tablist">
                    <li class="nav-item col-6 col-sm-3">
                        <a class="nav-link active" data-bs-toggle="tab" href="#home"><i class="fa-duotone fa-timeline-arrow"></i> ກຳລັງປະຕິບັດ</a>
                    </li>
                    <li class="nav-item col-6 col-sm-3">
                        <a class="nav-link" data-bs-toggle="tab" href="#menu1"><i class="fa-duotone fa-flag-swallowtail"></i> ສ້ອມແປງສຳເລັດ <i class="fa-regular fa-screwdriver-wrench"></i></a>
                    </li>
                    <li class="nav-item col-6 col-sm-3">
                        <a class="nav-link" data-bs-toggle="tab" href="#menu2"><i class="fa-duotone fa-flag-swallowtail"></i> ຍົກຍ້າຍສຳເລັດ <i class="fa-duotone fa-minimize"></i></a>
                    </li>
                    <li class="nav-item col-6 col-sm-3">
                        <a class="nav-link" data-bs-toggle="tab" href="#menu3"><i class="fa-duotone fa-flag-swallowtail"></i> ຖອນສຳເລັດ <i class="fa-duotone fa-cart-flatbed-suitcase"></i></a>
                    </li>
                </ul>

                <div class="tab-content ">
                    <div id="home" class=" tab-pane active ">
                        <table class="table table-hover circle  ">
                            <thead>
                                <tr class="trr" style="font-size: 15px;color:blue">
                                    <th width="2%">ລຳດັບ</th>
                                    <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                    <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                    <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                    <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                    <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                    <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>
                                    <th width="12%"><i class="fa-duotone fa-file-invoice"></i> PR ເລກທີ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                date_default_timezone_set("Asia/Bangkok");
                                $Province = $_SESSION["Namepro"];

                                if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE Date_Goto is null or Date_Goto =''
ORDER BY followequment.Date desc ";
                                } elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and (Date_Goto is null or Date_Goto ='')
ORDER BY followequment.Date desc ";
                                }

                                $result = $conn->query($sql);

                                ?>
                                <?php
                                $number = 0;
                                while ($row = $result->fetch_assoc()) {

                                    $number++;
                                    $id = $row['id'];
                                    $Name_Come = $row['Name_Come'];
                                    $S_Status = $row['S_Status'];
                                    if ($row['User_TMD'] <> '' and $row['STT_Fix'] == '') {
                                        $show = 'class="text-primary"';
                                    } elseif ($row['User_TMD'] <> '' and $row['STT_Fix'] <> '') {
                                        $show = 'class="text-success"';
                                    } else {
                                        $show = 'class="text-body"';
                                    }
                                ?>
                                    <tr style="font-size: 14px;">
                                        <td width="2%"> <?php echo $number ?></td>
                                        <td width="13%" <?php echo $show ?>> <b><?php echo $row['Cat_Province'] ?></b></td>
                                        <td width="25%" <?php echo $show ?>><a <?php echo $show ?> href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"><strong> </strong> <?php echo $row['Name_Come'] ?></a></td>
                                        <td width="20%" <?php echo $show ?>><strong> </strong> <?php echo $row['address'] ?></td>
                                        <td width="15%" <?php echo $show ?>><strong> </strong> <?php echo $row['NumBer_S'] ?></td>
                                        <td width="10%" <?php echo $show ?>><strong> </strong> <?php echo $row['S_Status'] ?></td>

                                        <td width="15%" <?php echo $show ?>><strong> </strong> <?php echo $row['Date'] ?></td>
                                        <td width="12%" <?php echo $show ?>><strong> </strong> <?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="menu1" class=" tab-pane fade">
                        <table class="table table-hover circle  ">
                            <thead>
                                <tr class="trr" style="font-size: 15px;color:blue">
                                    <th width="2%">ລຳດັບ</th>
                                    <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                    <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                    <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                    <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                    <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                    <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>
                                    <th width="12%"><i class="fa-duotone fa-file-invoice"></i> PR ເລກທີ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                date_default_timezone_set("Asia/Bangkok");
                                $Province = $_SESSION["Namepro"];

                                if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "2"  or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and S_Status ='ສ້ອມແປງ'
ORDER BY followequment.Date_Fix desc
limit 50";
                                } elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "2" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ສ້ອມແປງ'
ORDER BY followequment.Date_Fix desc
limit 50";
                                }

                                $result = $conn->query($sql);

                                ?>
                                <?php
                                $number22 = 0;
                                while ($row = $result->fetch_assoc()) {
                                    $number22++;
                                    $id = $row['id'];
                                    $Name_Come = $row['Name_Come'];
                                    $S_Status = $row['S_Status'];
                                    if ($row['User_TMD'] <> '') {

                                        $show = 'style="color: green"';
                                    }
                                    if ($row['STT_Fix'] == 'ຕາຍ') {
                                        $colort = 'style="color: red"';
                                        $pic = '<img  src="image/stop.png"style="width:40px;height:40px;"> ຕາຍ';
                                    } else {
                                        $colort = 'style="color: green"';
                                        $pic = '<img  src="image/finish.png"style="width:40px;height:40px;"> ໃຊ້ງານໄດ້';
                                    }
                                ?>
                                    <tr style="font-size: 14px;">
                                        <td width="2%"> <?php echo $number22 ?></td>
                                        <td width="13%" class="text-primary"> <b><?php echo $row['Cat_Province'] ?></b></td>
                                        <td width="25%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                        <td width="20%" <?php echo $colort ?>> <?php echo $row['address'] ?></td>
                                        <td width="15%" <?php echo $colort ?>><?php echo $row['NumBer_S'] ?></td>
                                        <td width="10%" <?php echo $colort ?>> <?php echo $pic ?> </strong> </td>

                                        <td width="15%" <?php echo $colort ?>> <?php echo $row['Date'] ?></td>
                                        <td width="12%" <?php echo $colort ?>> <?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="menu2" class=" tab-pane fade">
                        <table class="table table-hover circle  ">
                            <thead>
                                <tr class="trr" style="font-size: 15px;color:blue">
                                    <th width="2%">ລຳດັບ</th>
                                    <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                    <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                    <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                    <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                    <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                    <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>
                                    <th width="12%"><i class="fa-duotone fa-file-invoice"></i> PR ເລກທີ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                date_default_timezone_set("Asia/Bangkok");
                                $Province = $_SESSION["Namepro"];

                                if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and S_Status ='ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc
limit 20";
                                } elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "2" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc
limit 20";
                                }

                                $result = $conn->query($sql);

                                ?>
                                <?php
                                $number23 = 0;
                                while ($row = $result->fetch_assoc()) {
                                    $number23++;
                                    $id = $row['id'];
                                    $Name_Come = $row['Name_Come'];
                                    $S_Status = $row['S_Status'];
                                    if ($row['User_TMD'] <> '') {

                                        $show = 'style="color: green"';
                                    }
                                    if ($row['STT_Fix'] == 'ຕາຍ') {
                                        $colort = 'style="color: red"';
                                    } else {
                                        $colort = 'style="color: green"';
                                    }
                                ?>
                                    <tr style="font-size: 14px;">
                                        <td width="2%"> <?php echo $number23 ?></td>
                                        <td width="13%" class="text-primary"> <b><?php echo $row['Cat_Province'] ?></b></td>
                                        <td width="25%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                        <td width="20%" style="color: coral"> <?php echo $row['address'] ?></td>
                                        <td width="15%" style="color: coral"> <?php echo $row['NumBer_S'] ?></td>
                                        <td width="10%" style="color: coral"><strong><img src="image/move.gif" style="width:30px;height:40px;"> <br></strong> </td>

                                        <td width="15%" style="color: coral"> <?php echo $row['Date'] ?></td>
                                        <td width="12%" style="color: coral"> <?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="menu3" class=" tab-pane fade">
                        <table class="table table-hover circle  ">
                            <thead>
                                <tr class="trr" style="font-size: 15px;color:blue">
                                    <th width="2%">ລຳດັບ</th>
                                    <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                    <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                    <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                    <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                    <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                    <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>
                                    <th width="12%"><i class="fa-duotone fa-file-invoice"></i> PR ເລກທີ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                date_default_timezone_set("Asia/Bangkok");
                                $Province = $_SESSION["Namepro"];

                                if ($_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "2" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE Date_Goto <> '' and S_Status ='ຖອນ'
ORDER BY followequment.Date_Goto desc
limit 20";
                                } elseif ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "2" and $_SESSION["iduser"] <> "793") {
                                    $sql = "SELECT*FROM followequment 
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ຖອນ'
ORDER BY followequment.Date_Goto desc
limit 20";
                                }

                                $result = $conn->query($sql);

                                ?>
                                <?php
                                $number234 = 0;
                                while ($row = $result->fetch_assoc()) {
                                    $number234++;
                                    $id = $row['id'];
                                    $Name_Come = $row['Name_Come'];
                                    $S_Status = $row['S_Status'];
                                    if ($row['User_TMD'] <> '') {

                                        $show = 'style="color: green"';
                                    }
                                    if ($row['STT_Fix'] == 'ຕາຍ') {
                                        $colort = 'style="color: red"';
                                    } else {
                                        $colort = 'style="color: green"';
                                    }
                                ?>
                                    <tr style="font-size: 14px;">
                                        <td width="2%"> <?php echo $number234 ?></td>
                                        <td width="13%" class="text-primary"> <b> <?php echo $row['Cat_Province'] ?></b></td>
                                        <td width="25%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                        <td width="20%" style="color: deepskyblue"> <?php echo $row['address'] ?></td>
                                        <td width="15%" style="color: deepskyblue"> <?php echo $row['NumBer_S'] ?></td>
                                        <td width="10%" style="color: deepskyblue"><strong><img src="image/restore.gif" style="width:30px;height:40px;"> <br></strong> </td>

                                        <td width="15%" style="color: deepskyblue"> <?php echo $row['Date'] ?></td>
                                        <td width="12%" style="color: deepskyblue"> <?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- /container-fluid -->

            </div>
            <!-- /page-content-wrapper -->
        </div>
        <!-- /wrapper -->

        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>

        <script>
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

            document.addEventListener('DOMContentLoaded', function() {
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