<?php
@session_start();
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
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
    <link href="css/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="css/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- ===== Font Awesome 6 ===== -->
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

    <!-- ===== Select2 CSS ===== -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

    <!-- ===== jQuery (ເຕັມ ບໍ່ໃຊ້ slim) ===== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ===== Bootstrap 5 JS Bundle ===== -->
    <script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>

    <!-- ===== Select2 JS ===== -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

    <!-- icons ເພີ່ມເຕີມ -->
    <link rel="stylesheet" href="css/vendor/uicons/uicons-solid-rounded.css">
    <link rel="stylesheet" href="css/vendor/uicons/uicons-regular-rounded.css">
    <link rel="stylesheet" href="css/vendor/uicons/uicons-regular-straight.css">

    <title>ຕິດຕາມອຸປະກອນ</title>
    <!-- Favicon-->

    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/app.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/pages/center.css">

    <script language="JavaScript" type="text/JavaScript">
        function MM_jumpMenu(targ, selObj, restore) { //v3.0
            eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
            if (restore) selObj.selectedIndex = 0;
        }
        //-->
    </script>

    <?php
    require_once __DIR__ . '/../includes/conn.php';

    $proo = $_SESSION["Namepro"] ?? '';
    $userId = $_SESSION["iduser"] ?? '';



    if ($userId == "51" || $userId == "1" || $userId == "404" || $userId == "43") {
        $sql = "SELECT
COUNT(a.Team_fixed) as 'cccount'

FROM followequment a

WHERE Team_fixed ='ສາງອາໄຫຼ່'
";
    } elseif ($userId == '404' || $userId == '180' || $userId == '181' || $userId == '487' || $userId == '686') {
        $sql = "SELECT
COUNT(a.Team_fixed) as 'cccount'

FROM followequment a

WHERE Team_fixed ='IT'
";
    } elseif ($userId == '835' || $userId == '836' || $userId == '837') {
        $sql = "SELECT
COUNT(a.User_TMD<>'') as 'cccount'

FROM followequment a

WHERE a.User_TMD <> '' and Date_Goto is null
";
    } else {
        $sql = "SELECT 0 as 'cccount'";
    }
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
                <span>ຄຸ້ມຄອງເຕັກນິກ</span>
            </div>
            <div class="list-group list-group-flush">

                <a class="list-group-item list-group-item-action p-3 active" href="center.php">
                    <i class="fa-regular fa-location-check"></i>
                    <strong>ຕິດຕາມ ອຸປະກອນ</strong>
                    <span class="badge rounded-pill"><?php echo $count; ?></span>
                </a>

                <a class="list-group-item list-group-item-action p-3" href="stock2.php">
                    <i class="fa-solid fa-cubes-stacked"></i>
                    <strong>ສາງອຸປະກອນ</strong>
                </a>

                <a class="list-group-item list-group-item-action p-3" href="Report2.php">
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
            <!-- Page content-->

            <div class="container-fluid px-4 py-3">
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
                                <th width="8%">ຮູບພາບ</th>
                                <th width="8%"><i class="fa-duotone fa-file-invoice"></i> PR</th>
                                <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>

                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        require_once __DIR__ . '/../includes/conn.php';
                        $Province = $_SESSION["Namepro"];

                        if ($_SESSION["iduser"] == "51" or $_SESSION["iduser"] == "1" or $_SESSION["iduser"] == "404" or $_SESSION["iduser"] == "43") {
                            $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE (Date_Goto is null or Date_Goto ='')  and Team_fixed ='ສາງອາໄຫຼ່'
ORDER BY followequment.Date desc ";
                        } elseif ($_SESSION["iduser"] == '404' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718') {
                            $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE (Date_Goto is null or Date_Goto ='')  and Team_fixed ='IT'
ORDER BY followequment.Date desc ";
                        } 

                        $result = $conn->query($sql);

                        ?>
                        <?php
                        $nnumber = 0;
                        while ($row = $result->fetch_assoc()) {
                            $nnumber++;
                            $id = $row['id'];
                            $Name_Come = $row['Name_Come'];
                            $S_Status = $row['S_Status'];
                            if ($row['User_TMD'] <> '' and $row['STT_Fix'] == '') {
                                $show = 'class="text-body"';
                            } elseif ($row['User_TMD'] <> '' and $row['STT_Fix'] <> '') {
                                $show = 'class="text-success"';
                            } else {
                                $show = 'class="text-body"';
                            }


                        ?>
                            <tr style="font-size: 14px;">
                                <td width="2%"><?php echo $nnumber ?></td>
                                <td width="8%" <?php echo $show ?>><?php if (!empty($row['pro_picture'])): ?><img src="<?php echo htmlspecialchars($row['pro_picture']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><?php else: ?>-<?php endif; ?></td>
                                <td width="8%" <?php echo $show ?>><?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                <td width="13%" <?php echo $show ?>> <B><?php echo $row['Cat_Province'] ?></B></td>
                                <td width="25%" <?php echo $show ?>><a <?php echo $show ?> href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                <td width="20%" <?php echo $show ?>> <?php echo $row['address'] ?></td>
                                <td width="15%" <?php echo $show ?>> <?php echo $row['NumBer_S'] ?></td>
                                <td width="10%" <?php echo $show ?>><strong>
                                        <?php echo $row['S_Status'] ?></td>

                                <td width="15%" <?php echo $show ?>> <?php echo $row['Date'] ?></td>

                            <?php } ?>

                            </tr>

                            </tbody>
                    </table>
                </div>
                <div id="menu1" class=" tab-pane fade">
                    <table class="table table-hover circle  ">
                        <thead>
                            <tr class="trr" style="font-size: 15px;color:blue">

                                <th width="2%">ລຳດັບ</th>
                                <th width="8%">ຮູບພາບ</th>
                                <th width="8%"><i class="fa-duotone fa-file-invoice"></i> PR</th>
                                <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once __DIR__ . '/../includes/conn.php';
                            $Province = $_SESSION["Namepro"];

                            if ($_SESSION["iduser"] == '51' or  $_SESSION["iduser"] == '1' or $_SESSION["iduser"] == '404' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE Date_Goto <> '' and S_Status ='ສ້ອມແປງ'
ORDER BY followequment.Date_Fix desc
limit 50";
                            } else {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ສ້ອມແປງ'
ORDER BY followequment.Date_Fix desc
limit 50";
                            }

                            $result = $conn->query($sql);

                            ?>
                            <?php
                            $nnumber2 = 0;
                            while ($row = $result->fetch_assoc()) {
                                $nnumber2++;
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
                                    <td width="2%"><?php echo $nnumber2 ?></td>
                                    <td width="8%" class="text-center"><?php if (!empty($row['pro_picture'])): ?><img src="<?php echo htmlspecialchars($row['pro_picture']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><?php else: ?>-<?php endif; ?></td>
                                    <td width="8%"><?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    <td width="13%" class="text-primary"> <b> <?php echo $row['Cat_Province'] ?></b></td>
                                    <td width="30%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                    <td width="20%" <?php echo $colort ?>> <?php echo $row['address'] ?></td>
                                    <td width="10%" <?php echo $colort ?>> <?php echo $row['NumBer_S'] ?></td>
                                    <td width="10%" <?php echo $colort ?>> <?php echo $pic ?> <br></strong> </td>

                                    <td width="15%" <?php echo $colort ?>> <?php echo $row['Date'] ?></td>

                                <?php } ?>

                                </tr>

                        </tbody>
                    </table>
                </div>

                <div id="menu2" class=" tab-pane fade">
                    <table class="table table-hover circle  ">
                        <thead>
                            <tr class="trr" style="font-size: 15px;color:blue">

                                <th width="2%">ລຳດັບ</th>
                                <th width="8%">ຮູບພາບ</th>
                                <th width="8%"><i class="fa-duotone fa-file-invoice"></i> PR</th>
                                <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once __DIR__ . '/../includes/conn.php';
                            $Province = $_SESSION["Namepro"];

                            if ($_SESSION["iduser"] == '51' or  $_SESSION["iduser"] == '1' or $_SESSION["iduser"] == '404' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE Date_Goto <> '' and S_Status ='ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc
limit 20";
                            } else {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ຍົກຍ້າຍ'
ORDER BY followequment.Date_Goto desc
limit 20";
                            }

                            $result = $conn->query($sql);

                            ?>
                            <?php
                            $nnumber23 = 0;
                            while ($row = $result->fetch_assoc()) {

                                $nnumber23++;
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
                                    <td width="2%"><?php echo $nnumber23 ?></td>
                                    <td width="8%" class="text-center"><?php if (!empty($row['pro_picture'])): ?><img src="<?php echo htmlspecialchars($row['pro_picture']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><?php else: ?>-<?php endif; ?></td>
                                    <td width="8%"><?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    <td width="13%" class="text-primary"> <b> <?php echo $row['Cat_Province'] ?></b></td>
                                    <td width="30%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                    <td width="20%" style="color: coral"><strong> <?php echo $row['address'] ?></td>
                                    <td width="10%" style="color: coral"><strong> <?php echo $row['NumBer_S'] ?></td>
                                    <td width="10%" style="color: coral"><strong><img src="image/move.gif" style="width:30px;height:40px;"> <br></strong> </td>

                                    <td width="15%" style="color: coral"><strong> <?php echo $row['Date'] ?></td>

                                <?php } ?>

                                </tr>

                        </tbody>
                    </table>
                </div>

                <div id="menu3" class=" tab-pane fade">
                    <table class="table table-hover circle  ">
                        <thead>
                            <tr class="trr" style="font-size: 15px;color:blue">

                                <th width="2%">ລຳດັບ</th>
                                <th width="8%">ຮູບພາບ</th>
                                <th width="8%"><i class="fa-duotone fa-file-invoice"></i> PR</th>
                                <th width="13%"><i class="fa-regular fa-location-dot"></i> ແຂວງ</th>
                                <th width="25%"><i class="fa-duotone fa-server"></i> ລາຍການອຸປະກອນ</th>
                                <th width="20%"><i class="fa-duotone fa-map-location-dot"></i> ໃຊ້ຢູ່ສະຖານທີ່</th>
                                <th width="15%"><i class="fa-duotone fa-keyboard"></i> ຊີລຽວນາມເບີ</th>
                                <th width="10%"><i class="fa-regular fa-sensor-triangle-exclamation"></i> ສະຖານະ</th>
                                <th width="15%"><i class="fa-duotone fa-calendar-days"></i> ວັນທີ່</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once __DIR__ . '/../includes/conn.php';
                            $Province = $_SESSION["Namepro"];

                            if ($_SESSION["iduser"] == '51' or  $_SESSION["iduser"] == '1' or $_SESSION["iduser"] == '404' or $_SESSION["iduser"] == '43' or $_SESSION["iduser"] == '41' or $_SESSION["iduser"] == '378' or $_SESSION["iduser"] == '387' or $_SESSION["iduser"] == '514' or $_SESSION["iduser"] == '423' or $_SESSION["iduser"] == '166' or $_SESSION["iduser"] == '167' or $_SESSION["iduser"] == '168' or $_SESSION["iduser"] == '624' or $_SESSION["iduser"] == '190' or $_SESSION["iduser"] == '197' or $_SESSION["iduser"] == '718' or $_SESSION["iduser"] == '816' or $_SESSION["iduser"] == '835' or $_SESSION["iduser"] == '836' or $_SESSION["iduser"] == '837' or $_SESSION["iduser"] == '177' or $_SESSION["iduser"] == '180' or $_SESSION["iduser"] == '181' or $_SESSION["iduser"] == '487' or $_SESSION["iduser"] == '686') {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE Date_Goto <> '' and S_Status ='ຖອນ'
ORDER BY followequment.Date_Goto desc
limit 20";
                            } else {
                                $sql = "SELECT followequment.*, (SELECT sp.picture FROM stock_province sp WHERE sp.Items = followequment.Name_Come ORDER BY sp.Date_Pro DESC LIMIT 1) AS pro_picture
FROM followequment
WHERE followequment.Cat_Province LIKE '$Province' and Date_Goto <> '' and S_Status ='ຖອນ'
ORDER BY followequment.Date_Goto desc
limit 20";
                            }

                            $result = $conn->query($sql);

                            ?>
                            <?php
                            $nnumber234 = 0;
                            while ($row = $result->fetch_assoc()) {
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
                                $nnumber234++;

                            ?>
                                <tr style="font-size: 14px;">
                                    <td width="2%"><?php echo $nnumber234 ?></td>
                                    <td width="8%" class="text-center"><?php if (!empty($row['pro_picture'])): ?><img src="<?php echo htmlspecialchars($row['pro_picture']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;"><?php else: ?>-<?php endif; ?></td>
                                    <td width="8%"><?php echo htmlspecialchars($row['OA_f'] ?? '') ?></td>
                                    <td width="13%" class="text-primary"> <b><?php echo $row['Cat_Province'] ?></b></td>
                                    <td width="30%" class="text-primary"><a href="step.php?id=<?php echo $row['id'] ?>&Name_Come=<?php echo $row['Name_Come'] ?>&S_Status=<?php echo $row['S_Status'] ?>" target="_blank"> <?php echo $row['Name_Come'] ?></a></td>
                                    <td width="20%" style="color: deepskyblue"> <?php echo $row['address'] ?></td>
                                    <td width="10%" style="color: deepskyblue"> <?php echo $row['NumBer_S'] ?></td>
                                    <td width="10%" style="color: deepskyblue"><strong><img src="image/restore.gif" style="width:30px;height:40px;"> <br></strong> </td>

                                    <td width="15%" style="color: deepskyblue"> <?php echo $row['Date'] ?></td>

                                <?php } ?>

                                </tr>

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
