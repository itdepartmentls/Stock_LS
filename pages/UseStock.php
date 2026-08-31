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
    <link rel="stylesheet" href="css/all.min.css">

    <!-- icons ເພີ່ມເຕີມ -->
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">

    <title>ນຳໃຊ້ອຸປະກອນ</title>

    <!-- Core theme CSS -->
    <link href="css/styles.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/pages/UseStock.css">

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
