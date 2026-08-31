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
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- bootstrap v5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="js/pro.min.js">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/all.min.css">

    <title>ຂໍອຸປະກອນ</title>
    <!-- Favicon-->

    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <style type="text/css">
        body,
        td,
        th {
            font-family: "Phetsarath OT";
        }

        .circle {


            border-radius: 14px;

        }

        .right {
            float: right;
        }

        /* Dropdown Animation */
        .animate-dropdown {
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            padding: 0.5rem 0;
            min-width: 200px;
            animation: fadeInDown 0.3s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Language Option Styling */
        .lang-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .flag-icon {
            font-size: 1.5rem;
            margin-right: 12px;
        }

        .lang-name {
            flex: 1;
            font-weight: 500;
        }

        .check-icon {
            color: transparent;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        /* Hover Effect */
        .lang-option:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .lang-option:hover .lang-name {
            color: white;
        }

        /* Active State */
        .lang-option.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white !important;
        }

        .lang-option.active .lang-name {
            color: white;
            font-weight: 600;
        }

        .lang-option.active .check-icon {
            color: white;
        }

        /* Divider */
        .dropdown-divider {
            margin: 0.5rem 0;
            border-color: #e3e6f0;
        }
    </style>

    <?php
    $proo = $_SESSION["Namepro"];
    if ($_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "2" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "204" and $_SESSION["iduser"] <> "213" and $_SESSION["iduser"] <> "214"  and $_SESSION["iduser"] <> "215") {
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
    <div class="d-flex" id="wrapper">
        <!-- Sidebar-->
        <div class="border-end bg-white" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom bg-light"><img src="image/logoETL.jpg" class="img-fluid" alt="ETL" width="80">ລະບົບສາງ LS</div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="Dasborad.php"><i class="fa fa-handshake-o" aria-hidden="true"></i>&nbsp; <strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong> <span class="badge badge-pill badge-danger right" style="font-size: 15px;"><strong><?php echo ($row['cccount']); ?></strong></span></a>
                <?php
                if ($_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "204" and $_SESSION["iduser"] <> "213" and $_SESSION["iduser"] <> "214" and $_SESSION["iduser"] <> "215") {

                ?><a class="list-group-item list-group-item-action list-group-item-light p-3" href="UseStock.php"><i class="fa fa-tasks" aria-hidden="true"></i>&nbsp; <strong data-translate="use_equipment">ນຳໃຊ້ອຸປະກອນ</strong></a> <?php
                                                                                                                                                                                                        }

                                                                                                                                                                                                            ?>
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="follow.php"><i class="fa-regular fa-location-check"></i>&nbsp; <strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong></a>
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="stock.php"><i class="fa-solid fa-cubes-stacked"></i>&nbsp; <strong data-translate="stock_equipment">ສາງອຸປະກອນ</strong></a>
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="http://101.78.11.238:901"><i class="bi bi-tools"></i>&nbsp; <strong data-translate="Rotating_equipment">ອຸປະກອນໝູນວຽນ</strong></a>
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="Report.php"><i class="fa fa-bar-chart" aria-hidden="true"></i>&nbsp; <strong data-translate="report">Report</strong></a>
                <a class="list-group-item list-group-item-action list-group-item-light p-3" href="picture/index.php"><i class="bi bi-images" aria-hidden="true"></i>&nbsp; <strong data-translate="image_stock">Image Stock</strong></a>
                <br>
            </div>


        </div>
        <!-- Page content wrapper-->
        <div id="page-content-wrapper">
            <!-- Top navigation-->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" id="sidebarToggle"><i class="fa fa-eye-slash" aria-hidden="true"></i> Hide Menu</button>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mt-2 mt-lg-0">

                            <!--  Dropdown  -->
                            <!-- Language Dropdown with Active State -->
                            <li class="nav-item dropdown">
                                <a class="nav-link text-primary dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-translate fs-4"></i>
                                    <span class="ms-1 d-none d-lg-inline" id="currentLangText">中文</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="languageDropdown">
                                    <li>
                                        <a class="dropdown-item lang-option" href="#" data-lang="la" onclick="switchLanguage('la'); return false;">
                                            <span class="flag-icon">🇱🇦</span>
                                            <span class="lang-name">ພາສາລາວ</span>
                                            <i class="bi bi-check-circle-fill check-icon"></i>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item lang-option active" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
                                            <span class="flag-icon">🇨🇳</span>
                                            <span class="lang-name">中文</span>
                                            <i class="bi bi-check-circle-fill check-icon"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                                    <li class="nav-item active"><a class="nav-link text-primary" href="logout.php"><strong><i class="fa fa-users" aria-hidden="true"></i> User : <?= $_SESSION["user"] ?> </strong></a></li>


                                </ul>
                            </div>
                    </div>
            </nav>
            <!-- Page content-->
            <script>
                function resizeIframe(obj) {
                    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
                }
            </script>
            <?php if ($_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "2" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "204" and $_SESSION["iduser"] <> "213" and $_SESSION["iduser"] <> "214" and $_SESSION["iduser"] <> "215") {


            ?>

                <h1 class="mt-4"><a href="ReQ.php"><button type="button" name="button" class="btn btn-primary"><i class="fa-regular fa-cart-shopping"></i>&nbsp; ຂໍເບິກອຸປະກອນ</button></a></h1>

                <br>
                <iframe class="circle" src="DataRe.php" name="DataRe" height="550px;" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                </iframe>

            <?php }


            ?>
            <?php
            if ($_SESSION["iduser"] == "30" or $_SESSION["iduser"] == "194" or $_SESSION["iduser"] == "204" or $_SESSION["iduser"] == "213" or $_SESSION["iduser"] == "214" or $_SESSION["iduser"] == "215") {

            ?>



                <br>
                <iframe class="circle" src="DataReAdmin.php" name="DataReAdmin" height="550px;" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                </iframe>


            <?php } ?>

        </div>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
        
        <!-- Translate -->
<script src="translate/lang.js"></script>

<script>
    function switchLanguage(lang) {
        // ແປພາສາ
        setLanguage(lang);
        
        // ອັບເດດ active state
        document.querySelectorAll('.lang-option').forEach(function(el) {
            el.classList.remove('active');
        });
        document.querySelector('[data-lang="' + lang + '"]').classList.add('active');
        
        // ອັບເດດຂໍ້ຄວາມປຸ່ມ
        var langText = lang === 'la' ? 'ລາວ' : '中文';
        document.getElementById('currentLangText').textContent = langText;
    }

    // ຕັ້ງພາສາເລີ່ມຕົ້ນ
    document.addEventListener('DOMContentLoaded', function() {
        var currentLang = localStorage.getItem('site_lang') || 'la';
        switchLanguage(currentLang);
    });
</script>

</body>
</html>