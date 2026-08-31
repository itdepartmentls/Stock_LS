<?php
// =========================================================
// report_tacking.php
// ລາຍງານສະຫຼຸບ + ລາຍລະອຽດ ການສົ່ງເຄື່ອງທັງໝົດ (ຈາກຕາຕະລາງ tacking)
// ລວມສະຖານະ ລໍຖ້າຮັບ/ຮັບແລ້ວ ຂອງແຕ່ລະເອກະສານ (ທຸກໂຮງງານ)
// ຂຽນແບບເຂົ້າກັນໄດ້ກັບ PHP ເກົ່າ (ບໍ່ໃຊ້ closure, [], ??, get_result())
// ອັບເດດ: ເພີ່ມ Sidebar + Header ໃຫ້ຄືກັນກັບໜ້າອື່ນໆ (tacking.php / stock.php)
// ອັບເດດ: ດຶງ Track_planing_number (ເລກທີ່ແຜນ) ມາສະແດງໃນຕາຕະລາງ, ຄ້າງຢູ່ຂ້າງຄໍລຳ PR
//         ແລະ ນຳໄປລວມໃນຄຳຄົ້ນຫາ (q) ນຳ
// =========================================================

@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

require_once __DIR__ . '/../includes/conn.php';
/** @var mysqli $conn */

// ---- 1. ດຶງຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ (ຮຽງລ້າສຸດກ່ອນ) ----
$sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
               Name_Sender, Department_HQ, Number_Nam_Sender,
               Name_Receiver, Department_Receiver, Number_Nam_Receiver,
               OA, BarCode, Item, Size, QTY, Unit, Weight, Price, Total_Cost,
               Sig_Sender, Sig_Driver, Sig_Receiver, Doc_Date, Track_planing_number
        FROM tacking ORDER BY ID DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error);
}
$stmt->execute();

// ປະກາດຕົວແປລ່ວງໜ້າ (ບໍ່ປ່ຽນພຶດຕິກຳ, ພຽງແຕ່ແກ້ warning "Undefined variable"
// ຂອງ IDE/Intelephense ທີ່ບໍ່ຮູ້ຈັກ pattern by-reference ຂອງ bind_result)
$ID = $From = $NumberBin = $Carrier = $FactoryTo = $BoxToCarrier = null;
$TotalBox = $NameSender = $DepartmentHQ = $NumberNamSender = null;
$NameReceiver = $DepartmentReceiver = $NumberNamReceiver = null;
$OA = $BarCode = $Item = $Size = $QTY = $Unit = $Weight = null;
$Price = $TotalCost = $SigSender = $SigDriver = $SigReceiver = $DocDateCol = null;
$TrackPlanCol = null;

$stmt->bind_result(
    $ID,
    $From,
    $NumberBin,
    $Carrier,
    $FactoryTo,
    $BoxToCarrier,
    $TotalBox,
    $NameSender,
    $DepartmentHQ,
    $NumberNamSender,
    $NameReceiver,
    $DepartmentReceiver,
    $NumberNamReceiver,
    $OA,
    $BarCode,
    $Item,
    $Size,
    $QTY,
    $Unit,
    $Weight,
    $Price,
    $TotalCost,
    $SigSender,
    $SigDriver,
    $SigReceiver,
    $DocDateCol,
    $TrackPlanCol
);

$allRows = array();
while ($stmt->fetch()) {
    $allRows[] = array(
        "ID" => $ID,
        "From" => $From,
        "Number_Bin" => $NumberBin,
        "Carrier" => $Carrier,
        "Factory_To" => $FactoryTo,
        "BoxTo_Carrier" => $BoxToCarrier,
        "Total_Box" => $TotalBox,
        "Name_Sender" => $NameSender,
        "Department_HQ" => $DepartmentHQ,
        "Number_Nam_Sender" => $NumberNamSender,
        "Name_Receiver" => $NameReceiver,
        "Department_Receiver" => $DepartmentReceiver,
        "Number_Nam_Receiver" => $NumberNamReceiver,
        "OA" => $OA,
        "BarCode" => $BarCode,
        "Item" => $Item,
        "Size" => $Size,
        "QTY" => $QTY,
        "Unit" => $Unit,
        "Weight" => $Weight,
        "Price" => $Price,
        "Total_Cost" => $TotalCost,
        "Sig_Sender" => $SigSender,
        "Sig_Driver" => $SigDriver,
        "Sig_Receiver" => $SigReceiver,
        "Doc_Date" => $DocDateCol,
        "Track_planing_number" => $TrackPlanCol
    );
}
$stmt->close();

// ---- 2. ອ່ານຄ່າກັ່ນຕອງ (filter) ຈາກ GET ----
$q         = isset($_GET['q']) ? trim($_GET['q']) : '';
$dateFrom  = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo    = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$status    = isset($_GET['status']) ? trim($_GET['status']) : '';

// ດຶງວັນທີ່ ອອກຈາກ Number_Bin ຮູບແບບ GR-DDMMYYYY-NNNN -> ຄືນເປັນ YYYY-MM-DD ເພື່ອປຽບທຽບງ່າຍ
function extractDateFromDocNo($docNo)
{
    if (preg_match('/GR-(\d{2})(\d{2})(\d{4})-/', $docNo, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1]; // YYYY-MM-DD
    }
    return '';
}

// ຄົ້ນຫາແບບ case-insensitive ທີ່ປອດໄພກັບ UTF-8 (ຄຸ້ມກັນກໍລະນີບໍ່ມີ mbstring)
function containsSearch($haystack, $needle)
{
    if (function_exists('mb_stripos')) {
        return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
    }
    return stripos($haystack, $needle) !== false;
}

// ---- 3. ກັ່ນຕອງຂໍ້ມູນ (ຄົ້ນຫາ + ຊ່ວງວັນທີ່) ----
$filteredRows = array();
foreach ($allRows as $row) {
    $docDateRaw = (isset($row['Doc_Date']) && $row['Doc_Date'] !== '') ? $row['Doc_Date'] : '';
    if ($docDateRaw !== '' && preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $docDateRaw, $dm)) {
        // ຮູບແບບ DD/MM/YYYY ຈາກຟອມ -> ແປງເປັນ YYYY-MM-DD ສຳລັບປຽບທຽບ, ຄົງ DD/MM/YYYY ໄວ້ສະແດງ
        $docDateSortable = $dm[3] . '-' . $dm[2] . '-' . $dm[1];
        $docDate = $docDateRaw;
    } else {
        $docDateSortable = extractDateFromDocNo($row['Number_Bin']);
        $docDate = $docDateSortable;
    }

    if ($q !== '') {
        // ຄົ້ນຫາຄອບຄຸມທຸກຄໍລຳທີ່ກ່ຽວຂ້ອງ (ເພີ່ມ OA/PR No, BarCode, ພະແນກ, ເບີໂທ, ຫົວໜ່ວຍ)
        $haystackParts = array(
            $row['Number_Bin'],
            $row['From'],
            $row['Carrier'],
            $row['Factory_To'],
            $row['Item'],
            $row['OA'],
            $row['Track_planing_number'],
            $row['BarCode'],
            $row['Name_Sender'],
            $row['Department_HQ'],
            $row['Number_Nam_Sender'],
            $row['Name_Receiver'],
            $row['Department_Receiver'],
            $row['Number_Nam_Receiver'],
            $row['Size'],
            $row['Unit']
        );
        $haystack = implode(' ', $haystackParts);
        if (!containsSearch($haystack, $q)) {
            continue;
        }
    }

    if ($dateFrom !== '' && $docDateSortable !== '' && $docDateSortable < $dateFrom) {
        continue;
    }
    if ($dateTo !== '' && $docDateSortable !== '' && $docDateSortable > $dateTo) {
        continue;
    }

    $isReceived = ($row['Sig_Receiver'] !== '' && $row['Sig_Receiver'] !== null);
    if ($status === 'pending' && $isReceived) {
        continue;
    }
    if ($status === 'done' && !$isReceived) {
        continue;
    }

    $row['DocDate'] = $docDate;
    $row['IsReceived'] = $isReceived;
    $filteredRows[] = $row;
}

// ---- 4. ຄິດໄລ່ຂໍ້ມູນສະຫຼຸບ (summary) ----
$totalRows       = count($filteredRows);
$uniqueDocs      = array();
$uniqueDocsDone  = array();
$totalQty        = 0;
$totalWeight     = 0;
$totalCostByDoc  = array(); // ເກັບ Total_Cost ຄັ້ງດຽວຕໍ່ 1 ເອກະສານ (ບໍ່ໃຫ້ນັບຊ້ຳ)

foreach ($filteredRows as $row) {
    $uniqueDocs[$row['Number_Bin']] = true;
    if ($row['IsReceived']) {
        $uniqueDocsDone[$row['Number_Bin']] = true;
    }
    $totalQty += (int) preg_replace('/[^0-9]/', '', $row['QTY']);
    $totalWeight += (float) preg_replace('/[^0-9.]/', '', $row['Weight']);
    if (!isset($totalCostByDoc[$row['Number_Bin']])) {
        $costClean = (float) preg_replace('/[^0-9.]/', '', $row['Total_Cost']);
        $totalCostByDoc[$row['Number_Bin']] = $costClean;
    }
}

$totalDocs = count($uniqueDocs);
$totalDocsDone = count($uniqueDocsDone);
$totalDocsPending = $totalDocs - $totalDocsDone;
$totalCost = array_sum($totalCostByDoc);

// ---- 5. ສ້າງ query string ປັດຈຸບັນ (ໃຊ້ຕິດຄັກ export Excel ໃຫ້ filter ຄືກັນກັບໜ້າຈໍ) ----
$exportQuery = http_build_query(array(
    'q' => $q,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'status' => $status
));

function h($val)
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
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
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="image/favicons.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລາຍງານການສົ່ງເຄື່ອງ | LS To Factory</title>

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

    <!-- ===== ໄຟລ໌ສະເພາະໜ້າ report (Tailwind + ຟອນລາວ) ===== -->
    <script src="https://cdn.tailwindcss.com"></script>
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
           ສ່ວນທີ 2: CSS ສະເພາະໜ້າ report_tacking (ຈາກໄຟລ໌ເດີມ) - ບໍ່ໄດ້ແກ້ໄຂ
        =================================================================== */
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f0f2f5;
        }

        @media print {
            .no-print,
            #sidebar-wrapper,
            #sidebarBackdrop,
            .navbar-green {
                display: none !important;
            }

            #page-content-wrapper {
                width: 100% !important;
            }

            body {
                background: white;
            }
        }

        /* Responsive Styles */
        @media screen and (max-width: 1024px) {
            .md\:grid-cols-6 {
                grid-template-columns: repeat(3, 1fr) !important;
            }

            .md\:col-span-2 {
                grid-column: span 2 !important;
            }

            .md\:grid-cols-5 {
                grid-template-columns: 1fr 1fr !important;
            }
        }

        @media screen and (max-width: 768px) {
            /* ປັບຟອມຄົ້ນຫາ */
            .md\:grid-cols-5 {
                grid-template-columns: 1fr !important;
            }

            .md\:col-span-2 {
                grid-column: span 1 !important;
            }

            /* ປັບບັດສະຫຼຸບ */
            .grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
            }

            .md\:grid-cols-6 {
                grid-template-columns: 1fr 1fr !important;
            }

            /* ປັບຂະໜາດຕົວໜັງສື */
            .text-2xl {
                font-size: 1.25rem !important;
            }

            h1.text-2xl {
                font-size: 1.25rem !important;
            }

            /* ປັບປຸ່ມກົດ */
            .flex.gap-2 {
                gap: 4px !important;
                flex-wrap: wrap !important;
            }

            .flex.gap-2 a,
            .flex.gap-2 button {
                font-size: 10px !important;
                padding: 6px 10px !important;
            }

            /* ປັບຊ່ອງຫວ່າງ */
            .p-4 {
                padding: 12px !important;
            }

            .mb-6 {
                margin-bottom: 12px !important;
            }

            .gap-3 {
                gap: 6px !important;
            }

            /* ປັບຕາຕະລາງໃຫ້ scroll */
            .overflow-x-auto {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            table {
                font-size: 9px !important;
                min-width: 700px;
            }

            table th,
            table td {
                padding: 4px 4px !important;
                font-size: 8px !important;
                white-space: nowrap;
            }

            /* ປັບປຸ່ມສະຖານະ */
            .text-xs.px-2.py-1 {
                font-size: 7px !important;
                padding: 2px 4px !important;
            }

            /* ປັບການຈັດວາງຫົວຂໍ້ */
            .flex.justify-between.items-center {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 8px !important;
            }

            .flex.justify-between.items-center .flex.gap-2 {
                justify-content: center !important;
            }

            /* ປັບຟອມຄົ້ນຫາ */
            .grid.grid-cols-1.md\:grid-cols-5 {
                grid-template-columns: 1fr !important;
            }

            .grid.grid-cols-1.md\:grid-cols-5 .flex.gap-2 {
                flex-wrap: wrap !important;
            }

            .grid.grid-cols-1.md\:grid-cols-5 .flex.gap-2 button,
            .grid.grid-cols-1.md\:grid-cols-5 .flex.gap-2 a {
                flex: 1 !important;
                text-align: center !important;
                font-size: 10px !important;
                padding: 6px 8px !important;
            }
        }

        @media screen and (max-width: 480px) {

            /* ປັບບັດສະຫຼຸບໃຫ້ເປັນ 1 ຖັນ */
            .grid-cols-2 {
                grid-template-columns: 1fr !important;
            }

            .md\:grid-cols-6 {
                grid-template-columns: 1fr 1fr !important;
            }

            .text-2xl {
                font-size: 1rem !important;
            }

            .text-xs.text-gray-500 {
                font-size: 9px !important;
            }

            /* ປັບຟອມຄົ້ນຫາ */
            .p-4 {
                padding: 8px !important;
            }

            input,
            select {
                font-size: 12px !important;
                padding: 6px 8px !important;
            }

            /* ປັບຕາຕະລາງ */
            table {
                font-size: 7px !important;
                min-width: 600px;
            }

            table th,
            table td {
                padding: 2px 2px !important;
                font-size: 6px !important;
            }

            /* ປັບປຸ່ມກົດ */
            .flex.gap-2 a,
            .flex.gap-2 button {
                font-size: 8px !important;
                padding: 4px 6px !important;
            }

            .inline-block.px-3.py-1\.5 {
                padding: 4px 6px !important;
                font-size: 7px !important;
            }

            /* ປັບຊ່ອງຫວ່າງ */
            .mb-4 {
                margin-bottom: 6px !important;
            }

            .gap-4 {
                gap: 4px !important;
            }

            /* ປັບປຸ່ມ filter */
            .px-3.py-1\.5 {
                padding: 3px 6px !important;
                font-size: 8px !important;
            }
        }

        /* ປັບຕາຕະລາງໃຫ້ສວຍງາມຂຶ້ນ */
        table {
            font-size: 12px;
            border-collapse: collapse;
            width: 100%;
        }

        th {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* ປັບຮູບແບບ scrollbar */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* ປັບຮູບແບບບັດ */
        .border-l-4 {
            border-left-width: 4px;
        }

        @media screen and (max-width: 480px) {
            .border-l-4 {
                border-left-width: 3px;
            }

            .rounded-lg {
                border-radius: 6px !important;
            }

            .shadow {
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            }
        }
    </style>
</head>

<body>

    <div id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        <!-- ===== Sidebar ===== -->
        <div class="border-end" id="sidebar-wrapper">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
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
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item lang-option" href="#" data-lang="zh" onclick="switchLanguage('zh'); return false;">
                                            <span class="flag-icon">🇨🇳</span>
                                            <span class="lang-name">中文</span>
                                            <i class="bi bi-check-circle-fill check-icon"></i>
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

            <!-- ===== ເນື້ອໃນໜ້າ: ລາຍງານການສົ່ງເຄື່ອງ (ຄືເນື້ອໃນເດີມທັງໝົດ) ===== -->
            <div class="container-fluid p-4 md:p-8">

                <div class="max-w-full mx-auto">
                    <div class="flex justify-between items-center mb-6 no-print">
                        <h1 class="text-2xl font-bold text-emerald-900"><i class="fa-solid fa-chart-bar"></i> ລາຍງານການສົ່ງເຄື່ອງ</h1>
                        <div class="flex gap-2">
                            <a href="export_tacking.php?<?php echo h($exportQuery); ?>" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-sharp fa-solid fa-file-excel"></i> ດາວໂຫລດ Excel</a>
                            <button onclick="window.print()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-print"></i> ພິມ</button>
                            <!-- <a href="Dasborad.php" class="bg-gray-600 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md"><i class="fa-solid fa-arrow-left"></i> ກັບຄືນ</a> -->
                        </div>
                    </div>

                    <!-- ຟອມຄົ້ນຫາ / ກັ່ນຕອງ -->
                    <form method="get" action="report_tacking.php" class="no-print bg-white rounded-lg shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-600 mb-1">ຄົ້ນຫາ (ເລກທີ, PR, ເລກທີ່ແຜນ, ຕົ້ນທາງ, ຂົນສົ່ງ, ສິນຄ້າ, ຊື່ຜູ້ຝາກ/ຮັບ, ພະແນກ, ເບີໂທ)</label>
                            <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="ພິມຄຳຄົ້ນຫາ..." class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">ວັນທີ່ ຈາກ</label>
                            <input type="date" name="date_from" value="<?php echo h($dateFrom); ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">ວັນທີ່ ຫາ</label>
                            <input type="date" name="date_to" value="<?php echo h($dateTo); ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md w-full"><i class="fa-solid fa-magnifying-glass"></i> ຄົ້ນຫາ</button>
                            <a href="report_tacking.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold px-4 py-2 rounded-lg text-sm shadow-md">ລ້າງ</a>
                        </div>
                        <input type="hidden" name="status" id="statusInput" value="<?php echo h($status); ?>">
                    </form>

                    <!-- ປຸ່ມກັ່ນຕອງສະຖານະ -->
                    <div class="mb-4 flex flex-wrap gap-2 no-print">
                        <a href="?<?php echo h(http_build_query(array('q' => $q, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => ''))); ?>" class="px-3 py-1.5 rounded text-xs font-bold <?php echo $status === '' ? 'bg-gray-700 text-white' : 'bg-gray-200 text-gray-700'; ?>">ທັງໝົດ</a>
                        <a href="?<?php echo h(http_build_query(array('q' => $q, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => 'pending'))); ?>" class="px-3 py-1.5 rounded text-xs font-bold <?php echo $status === 'pending' ? 'bg-orange-700 text-white' : 'bg-orange-100 text-orange-800'; ?>">ລໍຖ້າຮັບ</a>
                        <a href="?<?php echo h(http_build_query(array('q' => $q, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'status' => 'done'))); ?>" class="px-3 py-1.5 rounded text-xs font-bold <?php echo $status === 'done' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800'; ?>">ຮັບແລ້ວ</a>
                    </div>

                    <!-- ບັດສະຫຼຸບ -->
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-600">
                            <p class="text-xs text-gray-500 font-bold">ຈຳນວນເອກະສານ</p>
                            <p class="text-2xl font-black text-emerald-900"><?php echo number_format($totalDocs); ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                            <p class="text-xs text-gray-500 font-bold">ລໍຖ້າຮັບ</p>
                            <p class="text-2xl font-black text-orange-700"><?php echo number_format($totalDocsPending); ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
                            <p class="text-xs text-gray-500 font-bold">ຮັບແລ້ວ</p>
                            <p class="text-2xl font-black text-emerald-700"><?php echo number_format($totalDocsDone); ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-600">
                            <p class="text-xs text-gray-500 font-bold">ຈຳນວນລາຍການ</p>
                            <p class="text-2xl font-black text-blue-900"><?php echo number_format($totalRows); ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-600">
                            <p class="text-xs text-gray-500 font-bold">ຈຳນວນລວມ (QTY)</p>
                            <p class="text-2xl font-black text-orange-900"><?php echo number_format($totalQty); ?></p>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-600">
                            <p class="text-xs text-gray-500 font-bold">ລາຄາຂົນສົ່ງລວມ</p>
                            <p class="text-2xl font-black text-red-900"><?php echo number_format($totalCost); ?></p>
                        </div>
                    </div>

                    <?php if ($totalRows === 0) { ?>
                        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
                            ບໍ່ພົບຂໍ້ມູນຕາມເງື່ອນໄຂຄົ້ນຫາ
                        </div>
                    <?php } else { ?>
                        <!-- ຕາຕະລາງລາຍລະອຽດ -->
                        <div class="bg-white rounded-lg shadow overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead class="bg-emerald-900 text-white">
                                    <tr>
                                        <th class="p-2 border border-emerald-800 text-left">ID</th>
                                        <th class="p-2 border border-emerald-800 text-left">ເລກທີເອກະສານ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ວັນທີ່</th>
                                        <th class="p-2 border border-emerald-800 text-left">ຕົ້ນທາງ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ປາຍທາງ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ຂົນສົ່ງໂດຍ</th>
                                        <th class="p-2 border border-emerald-800 text-left">PR</th>
                                        <th class="p-2 border border-emerald-800 text-left">ແຜນເລກທີ່</th>
                                        <th class="p-2 border border-emerald-800 text-left">ຊື່ສິນຄ້າ</th>
                                        <th class="p-2 border border-emerald-800 text-right">ຂະໜາດ</th>
                                        <th class="p-2 border border-emerald-800 text-right">ຈຳນວນ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ໜ່ວຍ</th>
                                        <th class="p-2 border border-emerald-800 text-right">ນ້ຳໜັກ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ຜູ້ຝາກ</th>
                                        <th class="p-2 border border-emerald-800 text-left">ຜູ້ຮັບ</th>
                                        <th class="p-2 border border-emerald-800 text-right">ລາຄາຂົນສົ່ງ</th>
                                        <th class="p-2 border border-emerald-800 text-center no-print">ສະຖານະ</th>
                                        <th class="p-2 border border-emerald-800 text-center no-print">ຈັດການ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $rowNum = 0;
                                    foreach ($filteredRows as $row) {
                                        $rowNum++;
                                        $bgClass = ($rowNum % 2 === 0) ? 'bg-gray-50' : 'bg-white';
                                    ?>
                                        <tr class="<?php echo $bgClass; ?> hover:bg-emerald-50">
                                            <td class="p-2 border border-gray-200"><?php echo h($row['ID']); ?></td>
                                            <td class="p-2 border border-gray-200 font-bold text-emerald-800"><?php echo h($row['Number_Bin']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['DocDate']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['From']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Factory_To']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Carrier']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['OA']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Track_planing_number']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Item']); ?></td>
                                            <td class="p-2 border border-gray-200 text-right"><?php echo h($row['Size']); ?></td>
                                            <td class="p-2 border border-gray-200 text-right"><?php echo h($row['QTY']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Unit']); ?></td>
                                            <td class="p-2 border border-gray-200 text-right"><?php echo h($row['Weight']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Name_Sender']); ?></td>
                                            <td class="p-2 border border-gray-200"><?php echo h($row['Name_Receiver']); ?></td>
                                            <td class="p-2 border border-gray-200 text-right"><?php echo h($row['Total_Cost']); ?></td>
                                            <td class="p-2 border border-gray-200 text-center no-print">
                                                <?php if ($row['IsReceived']) { ?>
                                                    <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold whitespace-nowrap"><i class="fa-solid fa-box-check"></i> ຮັບແລ້ວ</span>
                                                <?php } else { ?>
                                                    <span class="text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-800 font-bold whitespace-nowrap"><i class="fa-solid fa-clock"></i> ລໍຖ້າຮັບ</span>
                                                <?php } ?>
                                            </td>
                                            <td class="p-2 border border-gray-200 text-center no-print">
                                                <div class="flex flex-nowrap justify-center items-center gap-1">
                                                    <a href="receive_tacking.php?doc_no=<?php echo urlencode($row['Number_Bin']); ?>" class="inline-flex items-center gap-1 bg-gray-600 hover:bg-gray-700 text-white font-bold px-2 py-1 rounded text-xs shadow whitespace-nowrap">
                                                        <i class="fa-solid fa-eye"></i><span class="hidden sm:inline"> ເບິ່ງ</span>
                                                    </a>
                                                    <?php if (!$row['IsReceived']) { ?>
                                                        <a href="tacking.php?edit=<?php echo urlencode($row['Number_Bin']); ?>" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-bold px-2 py-1 rounded text-xs shadow whitespace-nowrap">
                                                            <i class="fa-solid fa-pen"></i><span class="hidden sm:inline"> ແກ້ໄຂ</span>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>

                    <p class="text-xs text-gray-400 mt-4 no-print">ອອກລາຍງານເມື່ອ: <?php echo date('d/m/Y H:i'); ?></p>
                </div>

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

</body>

</html>
<?php
$conn->close();
?>