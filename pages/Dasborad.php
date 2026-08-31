<?php

require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or  $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

// ✅ ຕ້ອງເອີ້ນຫຼັງຈາກ conn.php ແລະ session_start() ແລ້ວເທົ່ານັ້ນ
// (getPendingTackingCount ຕ້ອງການ $conn ແລະ $_SESSION["factory"])
require_once __DIR__ . '/../includes/inbox_pending_count.php';
$pendingCount = getPendingTackingCount($conn, trim($_SESSION["factory"] ?? ''));
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

    <!-- ===== jQuery (ສຳລັບ Select2 ແລະ ຟັງຊັນອື່ນໆ) ===== -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ===== Bootstrap 5 JS Bundle ===== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ===== Select2 JS (ຫຼັງຈາກ jQuery) ===== -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- ===== ໄຟລ໌ຂອງທ່ານ ===== -->
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

    <!-- icons ເພີ່ມເຕີມ -->
    <link rel="stylesheet" href="css/vendor/uicons/uicons-solid-rounded.css">
    <link rel="stylesheet" href="css/vendor/uicons/uicons-regular-rounded.css">
    <link rel="stylesheet" href="css/vendor/uicons/uicons-regular-straight.css">

    <title>ຂໍອຸປະກອນ</title>

    <!-- Core theme CSS -->
    <link href="css/styles.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/pages/Dasborad.css">

    <?php
    // PHP 8: ໃຊ້ Null Coalescing Operator ?? ແທນ isset()
    $proo = $_SESSION["Namepro"] ?? '';

    // ກຳນົດລາຍການຜູ້ໃຊ້ທີ່ມີສິດ Admin
    $adminUsers = ['30', '194', '204', '213', '214', '215'];
    $isAdmin = in_array($_SESSION["iduser"] ?? '', $adminUsers);

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
        <!-- ===== Sidebar ສີຂຽວຈ້າງ + Logo ໃຫຍ່ ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <!-- ===== Page content wrapper ===== -->
        <div id="page-content-wrapper">

            <!-- ===== Navbar ສີຂຽວຈ້າງ + ປຸ່ມ Logout ===== -->
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
            <div class="container-fluid px-4 py-3">

                <?php if (!in_array($_SESSION["iduser"] ?? '', ['30', '194', '204', '213', '214', '215'])): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mt-2">
                            <a href="ReQ.php" class="text-decoration-none">
                                <button type="button" name="button" class="btn btn-success">
                                    <i class="fi fi-rr-hand-holding-box"></i>&nbsp; ຂໍເບິກອຸປະກອນ
                                </button>
                            </a>
                        </h4>
                    </div>
                    <iframe class="circle" src="DataRe.php" name="DataRe" height="550px" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                    </iframe>
                <?php else: ?>
                    <iframe class="circle" src="DataReAdmin.php" name="DataReAdmin" height="550px" width="100%" frameborder="0" scrolling="no" onload="resizeIframe(this)">
                    </iframe>
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
