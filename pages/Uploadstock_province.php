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

    <title>ເພີ່ມສິນຄ້າ (Manual)</title>

    <!-- Core theme CSS -->
    <link href="css/styles.css" rel="stylesheet" />

    <link rel="stylesheet" href="css/pages/Uploadstock_province.css">

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

    date_default_timezone_set('Asia/Bangkok');
    $date_time = date('20y-m-d G:i');
    $addType = "";
    $addMessage = "";

    if (isset($_SESSION['flash_addType'])) {
        $addType = $_SESSION['flash_addType'];
        $addMessage = $_SESSION['flash_addMessage'];
        unset($_SESSION['flash_addType'], $_SESSION['flash_addMessage']);
    }

    set_time_limit(3600);

    /* ==========================================================================
       ເພີ່ມສິນຄ້າ (Manual) - ໂດຍ user ຝ່າຍແຂວງ
       ບັນທຶກລົງ stock (master) + log ລົງ stockinput (Remarkk = 'Add-Manual-Province')
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
               (ໃຊ້ໂຟນເດີ picture/uploads/ ຮ່ວມກັນກັບ Uploadstock.php)
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
               ຄຳນວນຄືນຈາກ MAX(Item_code) ຂອງກຸ່ມນັ້ນສະເໝີ ເພື່ອບໍ່ໃຫ້ Item_code ຊໍ້າກັນ
               (ຄືກັນກັບ Uploadstock.php) */
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
                   values (NULL,?,?,?,?,?,?,?,?,?,?,?,?,'Add-Manual-Province')"
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
                   stock.picture ຈະໃຊ້ບໍ່ໄດ້ອີກຕໍ່ໄປ (ຮູບຫາຍ/ບໍ່ຂຶ້ນ). ຈຶ່ງຕ້ອງລົງທະບຽນໄວ້
                   ທັນທີຫຼັງອັບໂຫລດສຳເລັດ. */
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
        echo "<script>window.location = 'Uploadstock_province.php';</script>";
        exit;
    }
    ?>
</head>

<body>
    <!-- Backdrop ສຳລັບ mobile/tablet -->
    <div id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        <!-- ===== Sidebar ສີຟ້າ ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom">
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
                    <a class="list-group-item list-group-item-action p-3" href="UseStock.php">
                        <i class="fa fa-tasks" aria-hidden="true"></i>
                        <strong data-translate="use_equipment">ນຳໃຊ້ອຸປະກອນ</strong>
                    </a>
                <?php endif; ?>
                <a class="list-group-item list-group-item-action p-3" href="follow.php">
                    <i class="fi fi-sr-location-alt"></i>
                    <strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
                </a>
                <a class="list-group-item list-group-item-action p-3 active" href="stock.php">
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
                <div class="menu-divider"></div>
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
            </div>
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

                <div class="card-soft">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h3 class="mb-0"><i class="fa fa-plus-circle" aria-hidden="true"></i> ເພີ່ມສິນຄ້າໃໝ່</h3>
                    </div>

                    <div id="response" class="alert <?php if (!empty($addType)) {
                                                        echo $addType === 'success' ? 'alert-success' : 'alert-danger';
                                                    } ?> <?php if (!empty($addMessage)) {
                                                                echo 'd-block';
                                                            } else {
                                                                echo 'd-none';
                                                            } ?>">
                        <?php if (!empty($addMessage)) {
                            echo $addMessage;
                        } ?>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <button type="button" class="btn btn-primary" style="min-width:200px" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i> ເພີ່ມສິນຄ້າ
                        </button>
                    </div>

                    <p class="text-muted mt-3 mb-0">ໜ້ານີ້ຮອງຮັບພຽງແຕ່ການເພີ່ມສິນຄ້າແບບ manual (ປ້ອນຂໍ້ມູນເອງ) ເທົ່ານັ້ນ, ບໍ່ຮອງຮັບການນຳເຂົ້າ CSV.</p>
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
        // ໃຊ້ endpoint ດຽວກັນກັບ Uploadstock.php: get_next_itemcode.php
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
        });
    </script>
</body>

</html>
