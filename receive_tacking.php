<?php
// =========================================================
// receive_tacking.php - ສະບັບໃໝ່ (ບໍ່ໃຊ້ proxy)
// ໃຊ້ໄດ້ເພາະ 101.78.11.238:902 ແລະ 10.10.0.33:8081 ແມ່ນເວັບແອັບ
// ດຽວກັນ ຢູ່ docroot ດຽວກັນໃນ server ດຽວກັນ (ຄົນລະ NIC/port ເທົ່ານັ້ນ).
// ດັ່ງນັ້ນ ໃຊ້ relative path ລ້ວນໆ (ບໍ່ໃສ່ domain) ຮູບ ແລະ upload
// ຈະໄປຫາ origin ດຽວກັນກັບໜ້າທີ່ເປີດຢູ່ສະເໝີ, ບໍ່ຕິດບັນຫາ CORS/PNA ອີກ.
// ອັບເດດ: ເພີ່ມ Sidebar + Header ໃຫ້ຄືກັນກັບໜ້າອື່ນໆ (tacking.php / stock.php)
// =========================================================

@session_start();
if (empty($_SESSION["user"]) or empty($_SESSION["Namepro"]) or empty($_SESSION["iduser"])) {
    // ເກັບ URL ປັດຈຸບັນ (ລວມ ?doc_no=... ຈາກການສະແກນ QR) ໄວ້
    // ເພື່ອສົ່ງກັບຄືນມາໜ້ານີ້ອັດຕະໂນມັດ ຫຼັງຈາກ login ສຳເລັດ
    $redirectTo = $_SERVER['REQUEST_URI'];
    $loginUrl = 'index.php?redirect=' . rawurlencode($redirectTo);
    echo '<script>window.location = ' . json_encode($loginUrl) . ';</script>';
    exit;
}

$myFactory = isset($_SESSION["factory"]) ? trim($_SESSION["factory"]) : '';

require_once __DIR__ . '/includes/conn.php';
/** @var mysqli $conn */

// -------------------------------------------------------------------
// ຂໍ້ມູນຜູ້ຮັບ (Receiver) ທີ່ຈະດຶງມາຕື່ມໃສ່ຟອມອັດຕະໂນມັດ
// ດຶງກົງຈາກຕາຕະລາງ sod_users ຕາມ id ຜູ້ login ($_SESSION['iduser'])
// (ຄືກັນກັບວິທີທີ່ໃຊ້ໃນ tacking.php ຝັ່ງ Sender):
//   - ຊື່ຜູ້ຮັບ  <- column `user`
//   - ພະແນກ    <- column `name`
//   - ເບີໂທ    <- column `Tel`
// -------------------------------------------------------------------
$receiverNameAuto = '';
$receiverDeptAuto = '';
$receiverPhoneAuto = '';
$receiverUserId = $_SESSION['iduser'] ?? '';
if ($receiverUserId !== '') {
    $sqlReceiverInfo = "SELECT `user`, `name`, `Tel` FROM sod_users WHERE id = ? LIMIT 1";
    $stmtReceiverInfo = $conn->prepare($sqlReceiverInfo);
    if ($stmtReceiverInfo) {
        $stmtReceiverInfo->bind_param("s", $receiverUserId);
        $stmtReceiverInfo->execute();
        $stmtReceiverInfo->bind_result($ru_user, $ru_name, $ru_tel);
        if ($stmtReceiverInfo->fetch()) {
            $receiverNameAuto = $ru_user ?? '';
            $receiverDeptAuto = $ru_name ?? '';
            $receiverPhoneAuto = $ru_tel ?? '';
        }
        $stmtReceiverInfo->close();
    }
}

$docNo = isset($_GET['doc_no']) ? trim($_GET['doc_no']) : '';

if ($docNo === '') {
    die("ບໍ່ພົບເລກທີ່ເອກະສານ");
}

$sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
               Name_Sender, Department_HQ, Number_Nam_Sender,
               Name_Receiver, Department_Receiver, Number_Nam_Receiver,
               OA, BarCode, Item, Size, QTY, Unit, Weight, Item_Photo, Total_Cost,
               Sig_Sender, Sig_Driver, Sig_Receiver,
               Sig_Sender_Img, Sig_Driver_Img, Sig_Receiver_Img, Doc_Date
        FROM tacking WHERE Number_Bin = ? ORDER BY ID ASC";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error);
}
$stmt->bind_param("s", $docNo);
$stmt->execute();

// ປະກາດຕົວແປລ່ວງໜ້າ (ບໍ່ປ່ຽນພຶດຕິກຳ, ພຽງແຕ່ແກ້ warning "Undefined variable"
// ຂອງ IDE/Intelephense ທີ່ບໍ່ຮູ້ຈັກ pattern by-reference ຂອງ bind_result)
$r_ID = $r_From = $r_NumberBin = $r_Carrier = $r_FactoryTo = null;
$r_CarrierPh = $r_TotalBox = $r_SenderName = $r_SenderDept = $r_SenderPhone = null;
$r_ReceiverName = $r_ReceiverDept = $r_ReceiverPhone = $r_PrNo = $r_BarCode = null;
$r_ItemName = $r_Size = $r_Qty = $r_Unit = $r_Weight = $r_ItemPhoto = null;
$r_TotalCost = $r_SigSender = $r_SigDriver = $r_SigReceiver = null;
$r_SigSenderImg = $r_SigDriverImg = $r_SigReceiverImg = $r_DocDate = null;

$stmt->bind_result(
    $r_ID,
    $r_From,
    $r_NumberBin,
    $r_Carrier,
    $r_FactoryTo,
    $r_CarrierPh,
    $r_TotalBox,
    $r_SenderName,
    $r_SenderDept,
    $r_SenderPhone,
    $r_ReceiverName,
    $r_ReceiverDept,
    $r_ReceiverPhone,
    $r_PrNo,
    $r_BarCode,
    $r_ItemName,
    $r_Size,
    $r_Qty,
    $r_Unit,
    $r_Weight,
    $r_ItemPhoto,
    $r_TotalCost,
    $r_SigSender,
    $r_SigDriver,
    $r_SigReceiver,
    $r_SigSenderImg,
    $r_SigDriverImg,
    $r_SigReceiverImg,
    $r_DocDate
);

$header = null;
$items = array();
while ($stmt->fetch()) {
    if ($header === null) {
        $header = array(
            'From' => $r_From,
            'Carrier' => $r_Carrier,
            'FactoryTo' => $r_FactoryTo,
            'CarrierPh' => $r_CarrierPh,
            'TotalBox' => $r_TotalBox,
            'SenderName' => $r_SenderName,
            'SenderDept' => $r_SenderDept,
            'SenderPhone' => $r_SenderPhone,
            'ReceiverName' => $r_ReceiverName,
            'ReceiverDept' => $r_ReceiverDept,
            'ReceiverPhone' => $r_ReceiverPhone,
            'PrNo' => $r_PrNo,
            'TotalCost' => $r_TotalCost,
            'SigSender' => $r_SigSender,
            'SigDriver' => $r_SigDriver,
            'SigReceiver' => $r_SigReceiver,
            'SigSenderImg' => $r_SigSenderImg,
            'SigDriverImg' => $r_SigDriverImg,
            'SigReceiverImg' => $r_SigReceiverImg,
            'DocDate' => $r_DocDate
        );
    }
    $items[] = array(
        'BarCode' => $r_BarCode,
        'ItemName' => $r_ItemName,
        'Size' => $r_Size,
        'Qty' => $r_Qty,
        'Unit' => $r_Unit,
        'Weight' => $r_Weight,
        'ItemPhoto' => $r_ItemPhoto,
        'PrNo' => $r_PrNo
    );
}
$stmt->close();

if ($header === null) {
    $conn->close();
    die("ບໍ່ພົບເອກະສານເລກທີ " . htmlspecialchars($docNo, ENT_QUOTES, 'UTF-8'));
}

$isOwnFactory = ($myFactory !== '' && strcasecmp($header['FactoryTo'] ?? '', $myFactory) === 0);
$isHqViewer = ($myFactory !== '' && strcasecmp($myFactory, 'HQ') === 0);

if (!$isOwnFactory && !$isHqViewer) {
    $conn->close();
    die("ທ່ານບໍ່ມີສິດເຂົ້າເບິ່ງເອກະສານນີ້ (ບໍ່ແມ່ນຂອງໂຮງງານທ່ານ)");
}

$alreadyReceived = ($header['SigReceiver'] !== '' && $header['SigReceiver'] !== null);
$canReceive = $isOwnFactory && !$alreadyReceived;

function h($val)
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// -------------------------------------------------------------------
// displayImg(): ຄືນ relative path ຈາກຄ່າທີ່ເກັບໄວ້ໃນ DB, ບໍ່ວ່າຄ່ານັ້ນ
// ຈະເປັນ relative path ຢູ່ແລ້ວ ຫຼືເປັນ full URL ເກົ່າທີ່ຕິດ domain ຜິດ
// (ຈາກ bug ຮຸ່ນກ່ອນ). ຮອງຮັບ 2 ໂຟນເດີ:
//   - 'picture/uploads/'  → ຮູບເກົ່າ (ສິນຄ້າ / ລາຍເຊັນ Sender-Driver)
//   - 'uploads/'          → ຮູບໃໝ່ (ລາຍເຊັນຜູ້ຮັບ, ແຍກຕ່າງຫາກ)
// ເນື່ອງຈາກ 101.78.11.238:902 ແລະ 10.10.0.33:8081 ແມ່ນເວັບແອັບດຽວກັນ
// (docroot ດຽວກັນ), relative path ນີ້ຈະຖືກຕ້ອງສະເໝີ ບໍ່ວ່າຈະເປີດຜ່ານ
// port ໃດກໍ່ຕາມ.
// -------------------------------------------------------------------
function displayImg($val)
{
    if (empty($val)) {
        return '';
    }
    $parsed = parse_url($val);
    $path = isset($parsed['path']) ? $parsed['path'] : $val;
    $path = ltrim($path, '/');

    // ຫາ 'picture/uploads/' ກ່ອນ (ຮູບເກົ່າ)
    $pos = strpos($path, 'picture/uploads/');
    if ($pos !== false) {
        return '/' . substr($path, $pos);
    }

    // ຖ້າບໍ່ພົບ, ຫາ 'uploads/' ລ້ວນໆ (ຮູບໃໝ່ - ລາຍເຊັນຜູ້ຮັບ)
    $pos = strpos($path, 'uploads/');
    if ($pos !== false) {
        return '/' . substr($path, $pos);
    }

    return '';
}

// =========================================================
// ຂໍ້ມູນສຳລັບ Sidebar/Header (ຄືກັນກັບໜ້າ tacking.php / stock.php)
// ຕ້ອງຄິດໄລ່ກ່ອນ $conn->close() ເພາະຕ້ອງໃຊ້ connection ດຽວກັນ
// =========================================================
$proo = $_SESSION["Namepro"] ?? '';
$userId = $_SESSION["iduser"] ?? '';

$adminUsers = array('404', '30', '194', '213', '2', '41', '214', '215');
$isAdmin = in_array($userId, $adminUsers);

$sidebarCount = 0;
if (!$isAdmin) {
    $sqlCount = "SELECT COUNT(a.S_Status) as 'cccount'
                FROM request a
                WHERE a.S_Status IN ('1','2','3') AND a.Province = ?";
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount) {
        $stmtCount->bind_param("s", $proo);
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        $rowCount = $resCount->fetch_assoc();
        $sidebarCount = isset($rowCount['cccount']) ? $rowCount['cccount'] : 0;
        $stmtCount->close();
    }
} else {
    $sqlCount = "SELECT COUNT(a.S_Status) as 'cccount'
                FROM request a
                WHERE a.S_Status = '1'";
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount) {
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        $rowCount = $resCount->fetch_assoc();
        $sidebarCount = isset($rowCount['cccount']) ? $rowCount['cccount'] : 0;
        $stmtCount->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="image/favicons.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຮັບເຄື່ອງ - <?php echo h($docNo); ?></title>

    <!-- ===== Bootstrap 5 CSS (ຄືກັນກັບໜ້າອື່ນໆ) ===== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <link rel="stylesheet" href="js/pro.min.js">
    <link rel="stylesheet" href="css/all.css">
    <link rel="stylesheet" href="css/all.min.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-solid-rounded/css/uicons-solid-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-straight/css/uicons-regular-straight.css">
    <link href="css/styles.css" rel="stylesheet" />

    <!-- ===== ໄຟລ໌ສະເພາະໜ້າ receive (Tailwind + Sweetalert2 + ຟອນລາວ) ===== -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style type="text/css">
        /* ===================================================================
           ສ່ວນທີ 1: CSS Sidebar + Header ດຽວກັນກັບໜ້າອື່ນໆ (tacking.php ແລະ ອື່ນໆ)
        =================================================================== */
        * { box-sizing: border-box; }
        html, body { overflow-x: hidden; max-width: 100%; }
        :root {
            --bs-primary: #198754;
            --bs-primary-rgb: 25, 135, 84;
            --bs-success: #198754;
            --sidebar-width: 280px;
        }
        .right { float: right; }
        .circle { border-radius: 14px; overflow: hidden; }
        #wrapper { position: relative; min-height: 100vh; width: 100%; }
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
            display: flex; align-items: center; gap: 10px;
        }
        #sidebar-wrapper .list-group-item {
            background: transparent !important;
            color: #fff !important;
            border: none; border-radius: 0;
            padding: 0.75rem 1.25rem;
            transition: all 0.25s ease;
            font-weight: 500;
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        }
        #sidebar-wrapper .list-group-item:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(6px);
            color: #fff !important;
        }
        #sidebar-wrapper .list-group-item .badge { background-color: #ffc107; color: #000; margin-left: auto; }
        #sidebar-wrapper .list-group-item.active {
            background: rgba(255, 255, 255, 0.2) !important;
            border-left: 4px solid #ffc107;
        }
        #sidebar-wrapper .list-group-item i,
        #sidebar-wrapper .list-group-item .fi,
        #sidebar-wrapper .list-group-item .bi { font-size: 18px; width: 24px; text-align: center; flex-shrink: 0; }
        .navbar-green {
            background: linear-gradient(135deg, #0C2C55 0%, #0C2C55 100%) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            flex-wrap: wrap;
        }
        .navbar-green .nav-link, .navbar-green .navbar-brand, .navbar-green .navbar-text { color: #fff !important; }
        .navbar-green .nav-link:hover { color: rgba(255, 255, 255, 0.8) !important; }
        .navbar-green .btn-success {
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #fff !important;
        }
        .navbar-green .btn-success:hover { background-color: rgba(255, 255, 255, 0.3) !important; }
        #sidebar-wrapper .sidebar-heading img {
            width: 100px !important; height: auto; border-radius: 14px;
            background: rgba(255, 255, 255, 0.1); padding: 6px;
            transition: all 0.3s ease; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        #sidebar-wrapper .sidebar-heading img:hover { transform: scale(1.05); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); }
        #sidebarToggle { background-color: #0C2C55 !important; border-color: #596e89 !important; color: #fff !important; white-space: nowrap; }
        #sidebarToggle:hover { background-color: rgba(255, 255, 255, 0.25) !important; }
        .animate-dropdown {
            border-radius: 12px; border: none; padding: 0.5rem 0; min-width: 200px;
            animation: fadeInDown 0.3s ease; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-12px);} to { opacity: 1; transform: translateY(0);} }
        .lang-option { display: flex; align-items: center; padding: 0.6rem 1.25rem; transition: all 0.2s ease; }
        .lang-option:hover { background: #f0fdf4; transform: translateX(4px); }
        .lang-option.active { background: #198754; color: #fff !important; }
        .lang-option.active .lang-name { color: #fff !important; }
        .lang-option .flag-icon { font-size: 1.4rem; margin-right: 12px; }
        .lang-option .lang-name { flex: 1; font-weight: 500; }
        .lang-option .check-icon { color: transparent; font-size: 1rem; }
        .lang-option.active .check-icon { color: #fff; }
        .user-dropdown .dropdown-item { padding: 0.6rem 1.25rem; transition: background 0.2s; }
        .user-dropdown .dropdown-item:hover { background: #f0fdf4; }
        .user-dropdown .dropdown-item.text-danger:hover { background: #fde8e8; }
        .logout-btn { color: #fff !important; font-weight: 600; transition: color 0.3s; }
        .logout-btn:hover { color: #ffc107 !important; }
        #page-content-wrapper { flex: 1; min-width: 0; width: 100%; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #198754, #146c43); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #146c43; }
        #sidebarBackdrop { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 1040; }
        #sidebarBackdrop.show { display: block; }
        @media (max-width: 991.98px) {
            #wrapper { display: block; }
            #sidebar-wrapper {
                position: fixed; top: 0; left: 0; bottom: 0; right: auto;
                height: auto; min-height: 0;
                transform: translateX(-100%); z-index: 1045; overflow-y: auto;
            }
            #wrapper.sidebar-open #sidebar-wrapper { transform: translateX(0); }
            #page-content-wrapper { width: 100%; margin-left: 0; }
            #sidebarToggle .toggle-label { display: none; }
        }
        @media (min-width: 992px) {
            #wrapper { display: flex; }
            #sidebarBackdrop { display: none !important; }
            #wrapper.sidebar-hidden-desktop #sidebar-wrapper { margin-left: calc(var(--sidebar-width) * -1); }
        }
        @media (max-width: 768px) {
            .right { float: none; display: inline-block; }
        }
        @media (max-width: 576px) {
            #sidebar-wrapper { width: 85%; max-width: 300px; }
            .btn-success#sidebarToggle { padding: 0.4rem 0.6rem; font-size: 0.85rem; }
        }

        /* ===================================================================
           ສ່ວນທີ 2: CSS ສະເພາະຟອມ ຮັບເຄື່ອງ (ຈາກໄຟລ໌ເດີມ) - ບໍ່ໄດ້ແກ້ໄຂ
        =================================================================== */
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f0f2f5;
            -webkit-print-color-adjust: exact;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 10mm auto;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* Responsive Styles */
        @media screen and (max-width: 1024px) {
            .page {
                width: 95%;
                padding: 10mm;
                margin: 5mm auto;
                min-height: auto;
            }

            .grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
            }

            .grid-cols-4 {
                grid-template-columns: 1fr 1fr !important;
            }

            .grid-cols-3 {
                grid-template-columns: 1fr 1fr !important;
            }
        }

        @media screen and (max-width: 768px) {
            .page {
                width: 98%;
                padding: 6px;
                margin: 4px auto;
                min-height: auto;
                box-shadow: none;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
            }

            /* ປັບ Grid Layout */
            .grid-cols-2 {
                grid-template-columns: 1fr !important;
            }

            .grid-cols-4 {
                grid-template-columns: 1fr 1fr !important;
                gap: 4px !important;
            }

            .grid-cols-3 {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }

            /* ປັບຂະໜາດຕົວໜັງສື */
            .text-2xl {
                font-size: 1.25rem !important;
            }

            .text-xl {
                font-size: 1.1rem !important;
            }

            .text-sm {
                font-size: 11px !important;
            }

            .text-xs {
                font-size: 9px !important;
            }

            /* ປັບຊ່ອງຫວ່າງ */
            .p-2\.5 {
                padding: 6px !important;
            }

            .p-3 {
                padding: 6px !important;
            }

            .p-1\.5 {
                padding: 4px !important;
            }

            .mb-4 {
                margin-bottom: 8px !important;
            }

            .mb-3 {
                margin-bottom: 6px !important;
            }

            .gap-4 {
                gap: 6px !important;
            }

            .gap-2 {
                gap: 4px !important;
            }

            /* ປັບ header */
            .flex.justify-between.items-start {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
                padding-bottom: 8px !important;
            }

            .flex.justify-between.items-start .text-right {
                text-align: left !important;
            }

            .flex.gap-4 {
                gap: 8px !important;
                flex-wrap: wrap !important;
            }

            .w-20 {
                width: 50px !important;
                height: 50px !important;
            }

            /* ປັບຕາຕະລາງ */
            .data-table th,
            .data-table td {
                font-size: 8px !important;
                padding: 4px 2px !important;
            }

            .data-table .w-8 {
                width: 20px !important;
                min-width: 20px;
            }

            .data-table .w-12 {
                width: 25px !important;
                min-width: 25px;
            }

            .data-table .w-16 {
                width: 30px !important;
                min-width: 30px;
            }

            .data-table .w-24 {
                width: 40px !important;
                min-width: 40px;
            }

            .data-table .w-64 {
                width: 50px !important;
                min-width: 50px;
                max-width: 70px;
            }

            /* ປັບແຖບເຄື່ອງມື */
            #toolbar {
                gap: 4px !important;
                padding: 4px !important;
                flex-wrap: wrap !important;
                justify-content: center !important;
            }

            #toolbar a,
            #toolbar button,
            #toolbar span {
                font-size: 9px !important;
                padding: 4px 8px !important;
                min-height: 28px !important;
            }

            /* ປັບຟອມປ້ອນຂໍ້ມູນ */
            input[type="text"] {
                font-size: 12px !important;
                padding: 4px 6px !important;
            }

            /* ປັບຊ່ອງລາຍເຊັນ */
            .min-h-[60px] {
                min-height: 40px !important;
            }

            .w-36 {
                width: 60px !important;
            }

            .max-h-\[70px\] {
                max-height: 50px !important;
            }

            .border.p-2 {
                padding: 4px !important;
            }

            /* ປັບຮູບພາບ */
            .w-full.h-\[250px\] {
                height: 150px !important;
            }

            /* ປັບ watermark */
            .watermark {
                width: 150px !important;
                height: 150px !important;
            }

            .watermark-img {
                width: 100% !important;
                height: 100% !important;
            }

            /* ປັບ receiver box */
            .receiver-box {
                border-width: 2px !important;
            }

            /* ປັບຊ່ອງສະແດງຮູບ */
            .border-2.border-dashed {
                border-width: 1px !important;
            }

            .rounded-xl {
                border-radius: 6px !important;
            }

            .p-4 {
                padding: 8px !important;
            }

            .grid-cols-2.gap-4 {
                grid-template-columns: 1fr !important;
                gap: 4px !important;
            }
        }

        @media screen and (max-width: 480px) {
            .page {
                padding: 4px;
                border-radius: 4px;
            }

            .grid-cols-4 {
                grid-template-columns: 1fr !important;
            }

            .grid-cols-3 {
                grid-template-columns: 1fr !important;
            }

            .data-table th,
            .data-table td {
                font-size: 6px !important;
                padding: 2px 1px !important;
            }

            .data-table .w-8 {
                width: 14px !important;
                min-width: 14px;
            }

            .data-table .w-12 {
                width: 18px !important;
                min-width: 18px;
            }

            .data-table .w-16 {
                width: 20px !important;
                min-width: 20px;
            }

            .data-table .w-24 {
                width: 28px !important;
                min-width: 28px;
            }

            .data-table .w-64 {
                width: 35px !important;
                min-width: 35px;
                max-width: 45px;
            }

            .text-2xl {
                font-size: 1rem !important;
            }

            .text-xl {
                font-size: 0.9rem !important;
            }

            #toolbar a,
            #toolbar button,
            #toolbar span {
                font-size: 7px !important;
                padding: 3px 5px !important;
                min-height: 22px !important;
            }

            .w-full.h-\[250px\] {
                height: 100px !important;
            }

            .watermark {
                width: 100px !important;
                height: 100px !important;
            }

            .flex.items-start span.w-20 {
                width: 40px !important;
                font-size: 8px !important;
            }

            .space-y-1\.5>*+* {
                margin-top: 2px !important;
            }

            .border-b.border-gray-400.w-36 {
                width: 40px !important;
            }

            .gap-4.items-center {
                gap: 4px !important;
                flex-wrap: wrap !important;
            }

            .bg-emerald-900.p-1\.5.rounded {
                padding: 4px 6px !important;
            }

            .bg-emerald-900 .text-xs {
                font-size: 7px !important;
            }

            .bg-emerald-900 .font-bold {
                font-size: 9px !important;
            }
        }

        .watermark {
            position: absolute;
            top: 53%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 320px;
            height: 320px;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }

        @media screen and (max-width: 768px) {
            .watermark {
                width: 160px !important;
                height: 160px !important;
            }
        }

        @media screen and (max-width: 480px) {
            .watermark {
                width: 100px !important;
                height: 100px !important;
            }
        }

        .page-content {
            position: relative;
            z-index: 10;
        }

        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }

            #sidebar-wrapper,
            #sidebarBackdrop,
            .navbar-green {
                display: none !important;
            }

            #page-content-wrapper {
                width: 100% !important;
            }

            .page {
                margin: 0;
                border: none;
                box-shadow: none;
                width: 210mm;
                min-height: 297mm;
                padding: 15mm;
            }

            .no-print {
                display: none !important;
            }

            .watermark {
                display: none !important;
            }
        }

        .data-table th {
            background-color: #064e3b !important;
            color: white !important;
            font-size: 11px;
            padding: 8px 4px;
        }

        @media screen and (max-width: 768px) {
            .data-table th {
                font-size: 8px !important;
                padding: 4px 2px !important;
            }
        }

        @media screen and (max-width: 480px) {
            .data-table th {
                font-size: 6px !important;
                padding: 2px 1px !important;
            }
        }

        .data-table td {
            padding: 6px 6px;
            font-size: 11px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        @media screen and (max-width: 768px) {
            .data-table td {
                padding: 4px 2px !important;
                font-size: 8px !important;
            }
        }

        @media screen and (max-width: 480px) {
            .data-table td {
                padding: 2px 1px !important;
                font-size: 6px !important;
            }
        }

        #toolbar {
            max-width: 210mm;
            margin: 10px auto 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 8px;
        }

        @media screen and (max-width: 768px) {
            #toolbar {
                gap: 4px;
                padding: 0 4px;
            }
        }

        .receiver-box {
            border: 2px solid #10b981;
            background: #ffffff;
        }

        @media screen and (max-width: 480px) {
            .receiver-box {
                border-width: 1px !important;
            }
        }

        /* ປັບ scroll ຕາຕະລາງ */
        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media screen and (max-width: 768px) {
            .table-wrapper {
                margin: 0 -4px;
                padding: 0 4px;
            }
        }

        /* ປັບປຸ່ມກົດໃຫ້ໃຊ້ງານງ່າຍຂຶ້ນ */
        .no-print button,
        .no-print a {
            touch-action: manipulation;
        }

        /* ປ້ອງກັນ content overflow */
        .page-content {
            overflow: hidden;
        }

        @media screen and (max-width: 768px) {
            .page-content {
                overflow-x: hidden;
            }
        }

        /* ຊ່ອງທີ່ດຶງຂໍ້ມູນມາອັດຕະໂນມັດ (ບໍ່ໃຫ້ພິມ) */
        input.auto-filled {
            background-color: #f3f4f6;
            color: #374151;
            cursor: not-allowed;
        }

        .field-edit-btn {
            font-size: 10px;
            color: #2563eb;
            cursor: pointer;
        }

        .field-edit-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="pb-10">

    <div id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        <!-- ===== Sidebar ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom">
                <img src="image/Logo.png" class="img-fluid" alt="ETL" width="50">
                <span>ລະບົບສາງ LS</span>
            </div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action p-3 " href="StockDashboard.php">
                    <i class="fa-solid fa-chart-simple"></i>
                    <strong>Dashboard ສາງ</strong>
                </a>
                <a class="list-group-item list-group-item-action p-3" href="Dasborad.php">
                    <i class="fi fi-rr-hand-holding-box"></i>
                    <strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong>
                    <span class="badge rounded-pill"><?php echo $sidebarCount; ?></span>
                </a>
                <?php if (!in_array($_SESSION["iduser"] ?? '', array('30', '194', '204', '213', '214', '215'))): ?>
                    <a class="list-group-item list-group-item-action p-3" href="UseStock.php">
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
                        <i class="fa-solid fa-chart-line"></i>
                        <strong data-translate="report_tacking">ລາງານການສົ່ງເຄື່ອງ</strong>
                    </a>
                <?php else: ?>
                    <a class="list-group-item list-group-item-action p-3 active" href="inbox_tacking.php">
                        <i class="fi fi-rs-person-dolly"></i>
                        <strong data-translate="inbox_tacking">ຮັບເຄື່ອງເຂົ້າໂຮງງານ</strong>
                    </a>
                <?php endif; ?>
                <a class="list-group-item list-group-item-action p-3" href="http://101.78.11.238:901">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <strong data-translate="Rotating_equipment">ອຸປະກອນໝູນວຽນ</strong>
                </a>
                <a class="list-group-item list-group-item-action p-3" href="Report.php">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    <strong data-translate="report">Report</strong>
                </a>
                <a class="list-group-item list-group-item-action p-3" href="picture/index.php">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                    <strong data-translate="image_stock">Image Stock</strong>
                </a>
                <br>
            </div>
        </div>

        <!-- ===== Page content wrapper ===== -->
        <div id="page-content-wrapper">

            <!-- ===== Navbar ===== -->
            <nav class="navbar navbar-expand-lg navbar-green border-bottom no-print">
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
                            <li class="nav-item dropdown">
                                <a class="nav-link text-white dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-language fs-4"></i>
                                    <span class="ms-1 d-none d-lg-inline" id="currentLangText">ລາວ</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="languageDropdown">
                                    <li>
                                        <a class="dropdown-item lang-option active" href="#" data-lang="la" onclick="switchLanguage('la'); return false;">
                                            <span class="flag-icon">🇱🇦</span>
                                            <span class="lang-name">ພາສາລາວ</span>
                                            <i class="fa-solid fa-circle-check check-icon"></i>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item lang-option" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
                                            <span class="flag-icon">🇨🇳</span>
                                            <span class="lang-name">中文</span>
                                            <i class="fa-solid fa-circle-check check-icon"></i>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php if (($_SESSION["iduser"] ?? '') == "204"): ?>
                                <li class="nav-item me-2">
                                    <a href="Uploadstock_hr_snk.php" class="btn btn-light btn-sm">
                                        <i class="fa fa-upload"></i> Upload Stock
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item dropdown user-dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-user-circle"></i>&nbsp; <?= htmlspecialchars($_SESSION["user"] ?? '') ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm animate-dropdown" aria-labelledby="userDropdown">
                                    <li><span class="dropdown-item-text"><i class="fa fa-id-badge"></i> <?= htmlspecialchars($_SESSION["user"] ?? '') ?></span></li>
                                    <li><hr class="dropdown-divider"></li>
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

            <!-- ===== ເນື້ອໃນໜ້າ: ຟອມຮັບເຄື່ອງ (ຄືເນື້ອໃນເດີມທັງໝົດ) ===== -->
            <div class="container-fluid px-4 pt-3">

                <div id="toolbar" class="no-print">
                    <a href="inbox_tacking.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-arrow-left"></i> ກັບຄືນ</a>
                    <?php if ($canReceive) { ?>
                        <button onclick="submitReceive()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fi fi-sr-disk"></i> ບັນທຶກຮັບເຄື່ອງ</button>
                    <?php } ?>
                    <button onclick="window.print()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-print"></i> ພິມ</button>
                    <?php if ($alreadyReceived) { ?>
                        <span class="text-xs px-3 py-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 font-bold"><i class="fi fi-rr-checkbox"></i> ຮັບເຄື່ອງແລ້ວ</span>
                    <?php } elseif ($isHqViewer && !$isOwnFactory) { ?>
                        <span class="text-xs px-3 py-2 rounded-lg border border-orange-200 bg-orange-50 text-orange-800 font-bold"><i class="fi fi-sr-clock-five"></i> ລໍຖ້າຮັບ (<?php echo h($header['FactoryTo']); ?>)</span>
                    <?php } ?>
                </div>

                <div class="page" id="page1">
                    <div class="watermark flex items-center justify-center">
                        <img class="watermark-img w-full h-full object-contain" src="image/Logo.png" alt="Watermark Logo">
                    </div>

                    <div class="page-content">
                        <!-- Header -->
                        <div class="flex justify-between items-start border-b-4 border-emerald-900 pb-4 mb-4">
                            <div class="flex gap-4 flex-wrap">
                                <div class="relative w-20 h-20 border rounded overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                    <img src="image/Logo.png" class="w-full h-full object-contain">
                                </div>
                                <div class="min-w-[120px]">
                                    <h1 class="text-2xl font-bold text-emerald-900">ບໍລິສັດ ຜະລິດແປ້ງມັນຕົ້ນແອວເອັສ ຈຳກັດ</h1>
                                    <p class="text-sm text-gray-600">ສຳນັກງານໃຫຍ່, ສີບຸນເຮືອງ ເມືອງ ຈັນທະບູລີ ນະຄອນຫຼວງວຽງຈັນ</p>
                                    <p class="text-xs text-gray-500">ໂທ: 020 5555 5555 | Email: lslao.com.la</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <h2 class="text-xl font-black text-white bg-emerald-900 px-4 py-1.5 rounded">ໃບຝາກເຄື່ອງ (GR)</h2>
                                <p class="mt-2 text-xs text-gray-600">ເລກທີ: <span class="font-bold text-gray-800 bg-gray-100 px-1 rounded"><?php echo h($docNo); ?></span></p>
                                <p class="text-xs text-gray-600">ວັນທີ: <span class="font-bold text-gray-800 bg-gray-100 px-1 rounded"><?php echo h($header['DocDate']); ?></span></p>
                            </div>
                        </div>

                        <!-- From/To -->
                        <div class="grid grid-cols-2 gap-4 mb-3">
                            <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100">
                                <label class="text-[10px] font-bold text-emerald-800 block mb-1">ຝາກຈາກ ຕົ້ນທາງ (From):</label>
                                <div class="text-sm font-semibold w-full border border-emerald-200 rounded px-2 py-1.5 bg-white text-blue-700"><?php echo h($header['From']); ?></div>
                            </div>
                            <div class="bg-orange-50/50 p-2.5 rounded-lg border border-orange-100">
                                <label class="text-[10px] font-bold text-orange-800 block mb-1">ປາຍທາງ (To):</label>
                                <div class="text-sm font-semibold w-full border border-orange-200 rounded px-2 py-1.5 bg-white text-blue-700"><?php echo h($header['FactoryTo']); ?></div>
                            </div>
                        </div>

                        <!-- Info boxes -->
                        <div class="grid grid-cols-4 gap-2 mb-3 text-xs">
                            <div class="border p-2 rounded">
                                <label class="block text-[10px] text-gray-500 font-bold">PR No:</label>
                                <div class="font-bold text-gray-800"><?php echo h($header['PrNo']); ?></div>
                            </div>
                            <div class="border p-2 rounded">
                                <label class="block text-[10px] text-gray-500 font-bold">Carrier/Driver:</label>
                                <div class="font-semibold text-gray-800"><?php echo h($header['Carrier']); ?></div>
                            </div>
                            <div class="border p-2 rounded">
                                <label class="block text-[10px] text-gray-500 font-bold">ເບີໂທ:</label>
                                <div class="font-semibold text-gray-800"><?php echo h($header['CarrierPh']); ?></div>
                            </div>
                            <div class="border p-2 rounded bg-emerald-50/30 border-emerald-200">
                                <label class="block text-[10px] text-emerald-800 font-bold">Total Box:</label>
                                <div class="font-bold text-emerald-900"><?php echo h($header['TotalBox']); ?></div>
                            </div>
                        </div>

                        <!-- Sender/Receiver Details -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="border border-emerald-100 rounded-lg p-3 bg-emerald-50/10">
                                <p class="text-xs font-bold text-emerald-800 border-b border-emerald-100 pb-1 mb-2">ຜູ້ຝາກ (Sender)</p>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ຊື່:</span><span class="font-semibold"><?php echo h($header['SenderName']); ?></span></div>
                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ພະແນກ:</span><span class="font-semibold"><?php echo h($header['SenderDept']); ?></span></div>
                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ເບີໂທ:</span><span class="font-semibold"><?php echo h($header['SenderPhone']); ?></span></div>
                                </div>
                            </div>
                            <div class="border-2 border-emerald-400 rounded-lg p-3 receiver-box">
                                <p class="text-xs font-bold text-emerald-800 border-b border-emerald-200 pb-1 mb-2">ຜູ້ຮັບ (Receiver) <?php echo $canReceive ? '<span class="text-orange-600 text-[10px]">← ກວດຄວາມຖືກຕ້ອງ / ໃສ່ລາຍເຊັນ</span>' : ''; ?></p>
                                <?php if (!$canReceive) { ?>
                                    <div class="space-y-1.5 text-xs">
                                        <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ຊື່:</span><span class="font-semibold"><?php echo $alreadyReceived ? h($header['ReceiverName']) : '(ຍັງບໍ່ໄດ້ຮັບ)'; ?></span></div>
                                        <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ພະແນກ:</span><span class="font-semibold"><?php echo h($header['ReceiverDept']); ?></span></div>
                                        <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ເບີໂທ:</span><span class="font-semibold"><?php echo h($header['ReceiverPhone']); ?></span></div>
                                    </div>
                                <?php } else { ?>
                                    <div class="space-y-2 text-xs">
                                        <div>
                                            <label class="flex items-center justify-between text-[10px] text-gray-500 mb-0.5">
                                                <span>ຊື່ຜູ້ຮັບ *</span>
                                                <span class="field-edit-btn no-print" onclick="toggleEdit('receiverName')"><i class="fa-solid fa-pen"></i> ແກ້ໄຂ</span>
                                            </label>
                                            <input type="text" id="receiverName" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm auto-filled" value="<?php echo h($receiverNameAuto); ?>" placeholder="ຊື່-ນາມສະກຸນ..." readonly>
                                        </div>
                                        <div>
                                            <label class="flex items-center justify-between text-[10px] text-gray-500 mb-0.5">
                                                <span>ພະແນກ</span>
                                                <span class="field-edit-btn no-print" onclick="toggleEdit('receiverDept')"><i class="fa-solid fa-pen"></i> ແກ້ໄຂ</span>
                                            </label>
                                            <input type="text" id="receiverDept" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm auto-filled" value="<?php echo h($receiverDeptAuto); ?>" placeholder="ພະແນກ..." readonly>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] text-gray-500 mb-0.5">ເບີໂທ</label>
                                            <input type="text" id="receiverPhone" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm" value="<?php echo h($receiverPhoneAuto); ?>" placeholder="ເບີໂທ...">
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-wrapper">
                            <table class="w-full border-collapse border border-gray-300 data-table mb-4">
                                <thead>
                                    <tr>
                                        <th class="border border-emerald-800 w-8">ລ/ດ</th>
                                        <th class="border border-emerald-800 w-20">ເລກທີ PR</th>
                                        <th class="border border-emerald-800 w-24">ເລກລາຍການ</th>
                                        <th class="border border-emerald-800 w-64">ຊື່ສິນຄ້າ</th>
                                        <th class="border border-emerald-800 w-16">ຂະໜາດ</th>
                                        <th class="border border-emerald-800 w-12">ຈຳນວນ</th>
                                        <th class="border border-emerald-800 w-12">ໜ່ວຍ</th>
                                        <th class="border border-emerald-800 w-16">ນ້ຳໜັກ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $idx => $it) { ?>
                                        <tr>
                                            <td class="text-center font-bold text-gray-700"><?php echo $idx + 1; ?></td>
                                            <!-- ເລກທີ PR: ຄ່າຂອງແຕ່ລະລາຍການ (ບໍ່ຊ້ຳກັນທັງໃບ) -->
                                            <!-- ຄລິກເພື່ອພິມປ້າຍທຸກລາຍການພາຍໃຕ້ PR ນີ້ (pr_bin_print.php ຄົ້ນຫາດ້ວຍ OA + Number_Bin) -->
                                            <td class="text-center">
                                                <a href="pr_bin_print.php?doc_print=<?php echo urlencode($it['PrNo']); ?>&doc_no_GR=<?php echo urlencode($docNo); ?>" class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-bold px-3 py-1.5 rounded text-xs shadow whitespace-nowrap"><?php echo h($it['PrNo']); ?></a>
                                            </td>
                                            <!-- ເລກລາຍການ / Barcode ຂອງແຕ່ລະລາຍການ (ຂໍ້ຄວາມທຳມະດາ, ບໍ່ລິ້ງ) -->
                                            <td class="font-semibold text-gray-700"><?php echo h($it['BarCode']); ?></td>
                                            <td class="font-semibold"><?php echo h($it['ItemName']); ?></td>
                                            <td><?php echo h($it['Size']); ?></td>
                                            <td class="text-center"><?php echo h($it['Qty']); ?></td>
                                            <td class="text-center"><?php echo h($it['Unit']); ?></td>
                                            <td class="text-right"><?php echo h($it['Weight']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Total Cost -->
                        <div class="flex flex-wrap justify-end mb-6">
                            <div class="bg-emerald-900 text-white p-1.5 rounded flex flex-wrap gap-4 items-center">
                                <span class="text-xs font-semibold">Total Cost:</span>
                                <span class="font-bold"><?php echo h($header['TotalCost']); ?></span>
                                <span class="text-xs">LAK</span>
                            </div>
                        </div>

                        <!-- Signatures -->
                        <div class="grid grid-cols-3 gap-4 text-center text-xs pt-2">
                            <div class="space-y-3 border p-2 rounded bg-gray-50/50 flex flex-col justify-between">
                                <p class="font-bold text-gray-600">Sender</p>
                                <div class="flex flex-col items-center justify-center min-h-[60px] my-1">
                                    <?php if (!empty($header['SigSenderImg'])) { ?>
                                        <img src="<?php echo h(displayImg($header['SigSenderImg'])); ?>" class="max-h-[70px] object-contain" style="width:100px;">
                                    <?php } ?>
                                </div>
                                <div class="border-b border-gray-400 w-36 mx-auto"></div>
                                <p class="font-bold text-gray-800"><?php echo h($header['SigSender']); ?></p>
                            </div>
                            <div class="space-y-3 border p-2 rounded bg-gray-50/50 flex flex-col justify-between">
                                <p class="font-bold text-gray-600">Driver</p>
                                <div class="flex flex-col items-center justify-center min-h-[60px] my-1">
                                    <?php if (!empty($header['SigDriverImg'])) { ?>
                                        <img src="<?php echo h(displayImg($header['SigDriverImg'])); ?>" class="max-h-[70px] object-contain" style="width:100px;">
                                    <?php } ?>
                                </div>
                                <div class="border-b border-gray-400 w-36 mx-auto"></div>
                                <p class="text-gray-500"><?php echo h($header['SigDriver']); ?></p>
                            </div>
                            <div class="space-y-3 border-2 border-emerald-400 p-2 rounded receiver-box flex flex-col justify-between">
                                <p class="font-bold text-emerald-900">Receiver</p>
                                <div class="relative flex flex-col items-center justify-center min-h-[60px] my-1">
                                    <?php if ($canReceive) { ?>
                                        <img id="receiverSigPreview" class="hidden max-h-[70px] object-contain" style="width:100px;">
                                        <p id="receiverSigPlaceholder" class="text-[10px] text-gray-400">ຍັງບໍ່ໄດ້ໃສ່ລາຍເຊັນ</p>
                                        <input type="file" id="receiverSigFile" accept="image/*" class="hidden no-print" onchange="uploadReceiverSig(this)">
                                        <button type="button" onclick="document.getElementById('receiverSigFile').click()" class="no-print bg-blue-600 hover:bg-blue-700 text-white text-[10px] px-2 py-1 rounded mt-1">ໃສ່ລາຍເຊັນ</button>
                                    <?php } elseif (!empty($header['SigReceiverImg'])) { ?>
                                        <img src="<?php echo h(displayImg($header['SigReceiverImg'])); ?>" class="max-h-[70px] object-contain" style="width:100px;">
                                    <?php } ?>
                                </div>
                                <div class="border-b border-emerald-400 w-36 mx-auto"></div>
                                <p class="font-bold text-gray-800"><?php echo $alreadyReceived ? h($header['SigReceiver']) : ''; ?></p>
                                <p class="text-[10px] text-gray-400"><?php echo $alreadyReceived ? h($header['DocDate']) : ''; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Photos Page -->
                <?php
                $photoItems = array_values(array_filter($items, function ($it) {
                    return !empty($it['ItemPhoto']);
                }));
                ?>
                <?php if (!empty($photoItems)) { ?>
                    <div class="page" id="page2">
                        <div class="watermark flex items-center justify-center">
                            <img class="watermark-img w-full h-full object-contain" src="image/Logo.png" alt="Watermark Logo">
                        </div>
                        <div class="page-content">
                            <div class="border-b-2 border-emerald-900 pb-2 mb-4 flex flex-wrap justify-between items-center gap-2">
                                <h2 class="text-lg font-bold text-emerald-900 uppercase">ເອກະສານແນບ: ຮູບພາບສິນຄ້າ</h2>
                                <span class="text-xs text-gray-400">Product Photos</span>
                            </div>
                            <div class="space-y-6 flex-grow">
                                <?php foreach ($photoItems as $it) { ?>
                                    <div class="border-2 border-dashed border-emerald-200 rounded-xl p-4 bg-gray-50 flex flex-col items-center relative">
                                        <div class="w-full h-[250px] bg-white border rounded shadow-inner flex items-center justify-center overflow-hidden mb-4">
                                            <img src="<?php echo h(displayImg($it['ItemPhoto'])); ?>" class="h-full object-contain w-full">
                                        </div>
                                        <div class="w-full grid grid-cols-2 gap-4">
                                            <div class="border-b border-gray-300">
                                                <label class="text-[10px] text-gray-500 block">Item ID:</label>
                                                <div class="w-full font-bold text-sm min-h-[1.5em]"><?php echo h($it['BarCode']); ?></div>
                                            </div>
                                            <div class="border-b border-gray-300">
                                                <label class="text-[10px] text-gray-500 block">Product Name:</label>
                                                <div class="w-full font-bold text-sm min-h-[1.5em]"><?php echo h($it['ItemName']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <p class="text-center text-[9px] text-gray-400 mt-4 italic">ເອກະສານນີ້ແມ່ນສ່ວນໜຶ່ງຂອງໃບຝາກເຄື່ອງ ເລກທີ <?php echo h($docNo); ?>.</p>
                        </div>
                    </div>
                <?php } ?>

            </div><!-- /container-fluid -->
        </div><!-- /page-content-wrapper -->
    </div><!-- /wrapper -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('wrapper');
            const toggleBtn = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');

            function isDesktop() {
                return window.innerWidth >= 992;
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (isDesktop()) {
                        wrapper.classList.toggle('sidebar-hidden-desktop');
                    } else {
                        wrapper.classList.toggle('sidebar-open');
                        backdrop.classList.toggle('show');
                    }
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    wrapper.classList.remove('sidebar-open');
                    backdrop.classList.remove('show');
                });
            }
        });

        function switchLanguage(lang) {
            document.querySelectorAll('.lang-option').forEach(el => el.classList.remove('active'));
            const selected = document.querySelector('.lang-option[data-lang="' + lang + '"]');
            if (selected) selected.classList.add('active');
            const langText = document.getElementById('currentLangText');
            if (langText) langText.innerText = (lang === 'zh') ? '中文' : 'ລາວ';
        }
    </script>

    <script>
        let currentDocNo = <?php echo json_encode($docNo); ?>;
        let currentSigPath = '';

        const canReceiveFlag = <?php echo json_encode($canReceive); ?>;
        const alreadyReceivedFlag = <?php echo json_encode($alreadyReceived); ?>;
        const existingSigPath = <?php echo json_encode(displayImg($header['SigReceiverImg'] ?? '')); ?>;

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#047857',
                confirmButtonText: 'ຕົກລົງ'
            });
        }

        // ເປີດ/ປິດການແກ້ໄຂ ຊ່ອງທີ່ດຶງຂໍ້ມູນມາອັດຕະໂນມັດ (ຊື່ / ພະແນກ)
        // ໃຊ້ໃນກໍລະນີຂໍ້ມູນຈາກ session ບໍ່ຖືກຕ້ອງ ຫຼືຕ້ອງການແກ້ໄຂ
        function toggleEdit(fieldId) {
            const el = document.getElementById(fieldId);
            if (!el) return;
            el.readOnly = !el.readOnly;
            el.classList.toggle('auto-filled', el.readOnly);
            if (!el.readOnly) {
                el.focus();
            }
        }

        function loadExistingSignature() {
            if (alreadyReceivedFlag && existingSigPath) {
                const img = document.getElementById('receiverSigPreview');
                if (img) {
                    img.src = existingSigPath;
                    img.classList.remove('hidden');
                    const placeholder = document.getElementById('receiverSigPlaceholder');
                    if (placeholder) {
                        placeholder.classList.add('hidden');
                    }
                    currentSigPath = existingSigPath;
                }
            }
        }

        function uploadReceiverSig(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('image', input.files[0]);

                const uploadUrl = 'upload_image.php';

                Swal.fire({
                    title: 'ກຳລັງອັບໂຫຼດ...',
                    text: 'ກະລຸນາລໍຖ້າ',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(uploadUrl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error('HTTP error! status: ' + res.status);
                        }
                        return res.json();
                    })
                    .then(result => {
                        Swal.close();

                        if (result.status === 'success') {
                            currentSigPath = result.path;
                            const img = document.getElementById('receiverSigPreview');
                            img.src = result.path;
                            img.classList.remove('hidden');
                            document.getElementById('receiverSigPlaceholder').classList.add('hidden');

                            showAlert('success', 'ອັບໂຫຼດສຳເລັດ', 'ຮູບລາຍເຊັນຖືກອັບໂຫຼດແລ້ວ');
                        } else {
                            showAlert('error', 'ອັບໂຫລດລາຍເຊັນບໍ່ສຳເລັດ', result.message);
                        }
                    })
                    .catch(err => {
                        Swal.close();
                        console.error('Upload error:', err);
                        showAlert('error', 'ອັບໂຫລດລາຍເຊັນບໍ່ສຳເລັດ', err.message);
                    });
            }
        }

        // ໃນ receive_tacking.php - ປັບປຸງຟັງຊັນ submitReceive()
        async function submitReceive() {
            const name = document.getElementById('receiverName').value.trim();
            const dept = document.getElementById('receiverDept').value.trim();
            const phone = document.getElementById('receiverPhone').value.trim();

            if (!name) {
                showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸຊື່ຜູ້ຮັບ');
                return;
            }

            if (!currentSigPath) {
                showAlert('warning', 'ກະລຸນາໃສ່ລາຍເຊັນ', 'ກະລຸນາອັບໂຫຼດຮູບລາຍເຊັນຜູ້ຮັບ');
                return;
            }

            Swal.fire({
                title: 'ກຳລັງບັນທຶກ...',
                text: 'ກະລຸນາລໍຖ້າ',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const formData = new URLSearchParams();
                formData.append('number_bin', currentDocNo); // ໃຊ້ number_bin ສຳລັບ receive_tacking.php
                formData.append('receiver_name', name);
                formData.append('receiver_dept', dept);
                formData.append('receiver_phone', phone);
                formData.append('receiver_sig', currentSigPath);

                const res = await fetch('update_receiver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    credentials: 'include',
                    body: formData.toString()
                });

                if (!res.ok) {
                    throw new Error('HTTP error! status: ' + res.status);
                }

                const result = await res.json();
                Swal.close();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ຮັບເຄື່ອງສຳເລັດ',
                        text: result.message,
                        confirmButtonColor: '#047857'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    showAlert('error', 'ບໍ່ສຳເລັດ', result.message);
                }
            } catch (err) {
                Swal.close();
                console.error('Submit error:', err);
                showAlert('error', 'ເຊື່ອມຕໍ່ບໍ່ໄດ້', err.message);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadExistingSignature();
        });
    </script>
</body>

</html>