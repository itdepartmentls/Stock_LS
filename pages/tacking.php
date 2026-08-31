<?php
// =========================================================
// tacking.php
// ຟອມສ້າງ/ແກ້ໄຂໃບຝາກເຄື່ອງ (GR) - ສະເພາະ HQ
// ແກ້ໄຂໄດ້ຜ່ານ ?edit=DOC_NO ສະເພາະເອກະສານທີ່ຍັງ "ລໍຖ້າຮັບ" ເທົ່ານັ້ນ
// ອັບເດດ: ເພີ່ມ Sidebar + Header ໃຫ້ຄືກັນກັບໜ້າອື່ນໆ (stock.php)
// ອັບເດດ: ເພີ່ມຊ່ອງ "ເລກທີ່ແຜນ" (Track_planing_number) ຂ້າງເລກ PR
// ອັບເດດ (ແກ້ bug): ຜູກ "ເລກທີ່ແຜນ" ເຂົ້າກັບແຕ່ລະລາຍການ/ແຖວ (per-row) ແທນຄ່າດຽວທັງເອກະສານ
//                    ເພື່ອບໍ່ໃຫ້ຖືກຂຽນທັບເມື່ອເລືອກຫຼາຍ PR ພ້ອມກັນ
// ອັບເດດ (Refactor): ຍ້າຍ Sidebar ອອກໄປເປັນ sidebar.php ແຍກຕ່າງຫາກ, include ແທນ
//                    (ບໍ່ຄິດໄລ່ $sidebarCount / $trackingCount ຢູ່ໜ້ານີ້ອີກຕໍ່ໄປ)
// =========================================================
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}
$myFactory = isset($_SESSION['factory']) ? trim($_SESSION['factory']) : '';
if (strcasecmp($myFactory, 'HQ') !== 0) {
    die("ໜ້ານີ້ສະເພາະບັນຊີ HQ ເທົ່ານັ້ນ");
}

require_once __DIR__ . '/../includes/conn.php';
/** @var mysqli $conn */

// ---- ຫ້າມ browser/proxy cache ໜ້ານີ້ (ໜ້າ dynamic ທີ່ຂຶ້ນກັບ ?edit=... ຕ້ອງໂຫລດໃໝ່ທຸກຄັ້ງ) ----
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$editDocNo = isset($_GET['edit']) ? trim($_GET['edit']) : '';
$editData = null;

if ($editDocNo !== '') {
    $sqlEdit = "SELECT `From`, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
                       Name_Sender, Department_HQ, Number_Nam_Sender,
                       OA, BarCode, Item, Size, QTY, Unit, Weight, Total_Cost,
                       Sig_Sender, Sig_Driver, Sig_Receiver,
                       Item_Photo, Sig_Sender_Img, Sig_Driver_Img, Doc_Date, Track_planing_number
                FROM tacking WHERE Number_Bin = ? ORDER BY ID ASC";
    $stmtEdit = $conn->prepare($sqlEdit);
    if (!$stmtEdit) {
        die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error);
    }
    $stmtEdit->bind_param("s", $editDocNo);
    $stmtEdit->execute();

    // ປະກາດຕົວແປລ່ວງໜ້າ (ບໍ່ປ່ຽນພຶດຕິກຳ, ພຽງແຕ່ແກ້ warning "Undefined variable"
    // ຂອງ IDE/Intelephense ທີ່ບໍ່ຮູ້ຈັກ pattern by-reference ຂອງ bind_result)
    $e_From = $e_Carrier = $e_FactoryTo = $e_CarrierPh = $e_TotalBox = null;
    $e_SenderName = $e_SenderDept = $e_SenderPhone = $e_PrNo = null;
    $e_BarCode = $e_ItemName = $e_Size = $e_Qty = $e_Unit = $e_Weight = null;
    $e_TotalCost = $e_SigSender = $e_SigDriver = $e_SigReceiver = null;
    $e_ItemPhoto = $e_SigSenderImg = $e_SigDriverImg = $e_DocDate = null;
    $e_TrackPlan = null;

    $stmtEdit->bind_result(
        $e_From,
        $e_Carrier,
        $e_FactoryTo,
        $e_CarrierPh,
        $e_TotalBox,
        $e_SenderName,
        $e_SenderDept,
        $e_SenderPhone,
        $e_PrNo,
        $e_BarCode,
        $e_ItemName,
        $e_Size,
        $e_Qty,
        $e_Unit,
        $e_Weight,
        $e_TotalCost,
        $e_SigSender,
        $e_SigDriver,
        $e_SigReceiver,
        $e_ItemPhoto,
        $e_SigSenderImg,
        $e_SigDriverImg,
        $e_DocDate,
        $e_TrackPlan
    );

    $editHeader = null;
    $editItems = array();
    while ($stmtEdit->fetch()) {
        if ($editHeader === null) {
            $editHeader = array(
                'From' => $e_From,
                'Carrier' => $e_Carrier,
                'FactoryTo' => $e_FactoryTo,
                'CarrierPh' => $e_CarrierPh,
                'TotalBox' => $e_TotalBox,
                'SenderName' => $e_SenderName,
                'SenderDept' => $e_SenderDept,
                'SenderPhone' => $e_SenderPhone,
                'PrNo' => $e_PrNo,
                'TotalCost' => $e_TotalCost,
                'SigSender' => $e_SigSender,
                'SigDriver' => $e_SigDriver,
                'SigReceiver' => $e_SigReceiver,
                'SigSenderImg' => $e_SigSenderImg,
                'SigDriverImg' => $e_SigDriverImg,
                'DocDate' => $e_DocDate,
                'TrackPlan' => $e_TrackPlan
            );
        }
        // ---- ບັງຄັບໃຫ້ Qty / Weight / TotalCost ເປັນ string ຄົງທີ່ ----
        $editItems[] = array(
            'OA' => $e_PrNo,
            'BarCode' => $e_BarCode,
            'ItemName' => $e_ItemName,
            'Size' => $e_Size,
            'Qty' => is_numeric($e_Qty) ? (is_finite((float)$e_Qty) ? (string)$e_Qty : '0') : (string)$e_Qty,
            'Unit' => $e_Unit,
            'Weight' => is_numeric($e_Weight) ? (is_finite((float)$e_Weight) ? (string)$e_Weight : '0') : (string)$e_Weight,
            'ItemPhoto' => $e_ItemPhoto,
            'TrackPlan' => $e_TrackPlan
        );
    }
    $stmtEdit->close();

    if ($editHeader === null) {
        die("ບໍ່ພົບເອກະສານເລກທີ " . htmlspecialchars($editDocNo, ENT_QUOTES, 'UTF-8'));
    }
    if (isset($editHeader['TotalCost']) && is_numeric($editHeader['TotalCost'])) {
        $editHeader['TotalCost'] = is_finite((float)$editHeader['TotalCost']) ? (string)$editHeader['TotalCost'] : '0';
    }
    if ($editHeader['SigReceiver'] !== '' && $editHeader['SigReceiver'] !== null) {
        die("ເອກະສານນີ້ຖືກຮັບໄປແລ້ວ, ແກ້ໄຂບໍ່ໄດ້");
    }

    $editData = array('docNo' => $editDocNo, 'header' => $editHeader, 'items' => $editItems);
}

// ---- Insert QR link record ----
if ($editData) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $linkUrl  = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $title    = $_SESSION["Namepro"];
    $createdBy = $_SESSION["user"];
    $createdAt = $editHeader['DocDate'] ?? null;

    if ($createdAt === null || $createdAt === '') {
        $createdAt = date('Y-m-d');
    } else {
        $dt = DateTime::createFromFormat('d/m/Y', $createdAt);
        if ($dt !== false) {
            $createdAt = $dt->format('Y-m-d');
        }
    }

    $checkSql  = "SELECT COUNT(*) AS cnt FROM qr_links WHERE link = ?";
    $checkStmt = $conn->prepare($checkSql);
    if ($checkStmt) {
        $checkStmt->bind_param("s", $linkUrl);
        $checkStmt->execute();
        $checkStmt->bind_result($cnt);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($cnt == 0) {
            $insSql  = "INSERT INTO qr_links (link, title, created_by, created_at) VALUES (?, ?, ?, ?)";
            $insStmt = $conn->prepare($insSql);
            if ($insStmt) {
                $insStmt->bind_param("ssss", $linkUrl, $title, $createdBy, $createdAt);
                $insStmt->execute();
                if ($insStmt->error) {
                    error_log("QR link insert error: " . $insStmt->error);
                }
                $insStmt->close();
            } else {
                error_log("QR link prepare error: " . $conn->error);
            }
        }
    } else {
        error_log("QR link check prepare error: " . $conn->error);
    }
}

// =========================================================
// ຂໍ້ມູນສຳລັບ Header (ຊື່-ນາມສະກຸນ, ພະແນກ, ເບີໂທ ຂອງຜູ້ Login)
// ໝາຍເຫດ: Sidebar (badge ຂໍເບິກ / ຕິດຕາມການສົ່ງເຄື່ອງ) ບໍ່ຄິດໄລ່ຢູ່ນີ້ອີກຕໍ່ໄປ
// ຄິດໄລ່ຢູ່ໃນ sidebar.php ແທນ (ເບິ່ງຈຸດ include ຢູ່ໃນ HTML ຂ້າງລຸ່ມ)
// ຕ້ອງບໍ່ close() connection ຢູ່ນີ້ ເພາະ sidebar.php ຍັງຕ້ອງໃຊ້ $conn ຕໍ່
// =========================================================
$proo = $_SESSION["Namepro"] ?? '';
$userId = $_SESSION["iduser"] ?? '';

// ---- ດຶງ ຊື່-ນາມສະກຸນ (user), ພະແນກ (name), ເບີໂທ (Tel) ຂອງຜູ້ login ຈາກຕາຕະລາງ sod_users ----
$senderName = '';
$senderDept = '';
$senderPhone = '';
if ($userId !== '') {
    $sqlUserInfo = "SELECT `user`, `name`, `Tel` FROM sod_users WHERE id = ? LIMIT 1";
    $stmtUserInfo = $conn->prepare($sqlUserInfo);
    if ($stmtUserInfo) {
        $stmtUserInfo->bind_param("s", $userId);
        $stmtUserInfo->execute();
        $stmtUserInfo->bind_result($u_user, $u_name, $u_tel);
        if ($stmtUserInfo->fetch()) {
            $senderName = $u_user ?? '';
            $senderDept = $u_name ?? '';
            $senderPhone = $u_tel ?? '';
        }
        $stmtUserInfo->close();
    }
}

// ---- ຄິດໄລ່ JSON ຂອງ $editData ໄວ້ລ່ວງໜ້າ ----
function forceValidUtf8Str($str)
{
    if (@preg_match('//u', $str) === 1) {
        return $str;
    }
    $regex = '/(?:
          [\x09\x0A\x0D\x20-\x7E]
        | [\xC2-\xDF][\x80-\xBF]
        | \xE0[\xA0-\xBF][\x80-\xBF]
        | [\xE1-\xEC][\x80-\xBF]{2}
        | \xED[\x80-\x9F][\x80-\xBF]
        | [\xEE-\xEF][\x80-\xBF]{2}
        | \xF0[\x90-\xBF][\x80-\xBF]{2}
        | [\xF1-\xF3][\x80-\xBF]{3}
        | \xF4[\x80-\x8F][\x80-\xBF]{2}
    )/x';
    preg_match_all($regex, $str, $m);
    return implode('', $m[0]);
}

function fixUtf8Recursive($val)
{
    if (is_array($val)) {
        $out = array();
        foreach ($val as $k => $v) {
            $out[$k] = fixUtf8Recursive($v);
        }
        return $out;
    }
    if (is_string($val)) {
        return forceValidUtf8Str($val);
    }
    return $val;
}

function manualJsonEncodeString($str)
{
    $str = (string) $str;
    $len = strlen($str);
    $out = '"';
    for ($i = 0; $i < $len; $i++) {
        $c = $str[$i];
        $ord = ord($c);
        if ($c === '"') {
            $out .= '\\"';
        } elseif ($c === '\\') {
            $out .= '\\\\';
        } elseif ($c === '<') {
            $out .= '\\u003C';
        } elseif ($c === '>') {
            $out .= '\\u003E';
        } elseif ($c === '&') {
            $out .= '\\u0026';
        } elseif ($c === "'") {
            $out .= '\\u0027';
        } elseif ($c === "\n") {
            $out .= '\\n';
        } elseif ($c === "\r") {
            $out .= '\\r';
        } elseif ($c === "\t") {
            $out .= '\\t';
        } elseif ($ord < 0x20) {
            $out .= sprintf('\\u%04x', $ord);
        } else {
            $out .= $c;
        }
    }
    $out .= '"';
    return $out;
}

function manualJsonEncode($val)
{
    if (is_null($val)) return 'null';
    if (is_bool($val)) return $val ? 'true' : 'false';
    if (is_int($val) || is_float($val)) return (string) $val;
    if (is_string($val)) return manualJsonEncodeString($val);
    if (is_array($val)) {
        $isList = (array_keys($val) === range(0, count($val) - 1));
        $parts = array();
        if ($isList) {
            foreach ($val as $v) {
                $parts[] = manualJsonEncode($v);
            }
            return '[' . implode(',', $parts) . ']';
        } else {
            foreach ($val as $k => $v) {
                $parts[] = manualJsonEncodeString($k) . ':' . manualJsonEncode($v);
            }
            return '{' . implode(',', $parts) . '}';
        }
    }
    return 'null';
}

$editDataJson = 'null';

if ($editData) {
    $safeHeader = fixUtf8Recursive($editData['header']);
    $safeItems  = fixUtf8Recursive($editData['items']);
    $safeDocNo  = forceValidUtf8Str($editData['docNo']);

    $editDataJson = manualJsonEncode(array(
        'docNo'  => $safeDocNo,
        'header' => $safeHeader,
        'items'  => $safeItems
    ));
}
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <link rel="shortcut icon" href="image/favicons.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ໃບຝາກເຄື່ອງ GR | LS To Factory</title>

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

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style type="text/css">
    /* ==========================================================================
       ໂຄງສ້າງພື້ນຖານ & ໂຕນສີ (Design Tokens)
       ========================================================================== */
    :root {
        /* ໂຕນສີຫຼັກ - ໃຊ້ Navy ເປັນສີໂຄງສ້າງ, Green ເປັນສີເນັ້ນ (accent) */
        --brand-navy: #0C2C55;
        --brand-navy-dark: #081d3a;
        --brand-green: #198754;
        --brand-green-dark: #146c43;
        --brand-green-light: #e9f7ef;
        --brand-gold: #ffc107;

        --bs-primary: var(--brand-green);
        --bs-primary-rgb: 25, 135, 84;
        --bs-success: var(--brand-green);

        /* ພື້ນຫຼັງ & ຂໍ້ຄວາມ */
        --surface: #ffffff;
        --surface-muted: #f6f8fa;
        --text-main: #1f2937;
        --text-muted: #6b7280;
        --border-soft: #e5e7eb;
        --border-strong: #cbd5e1;

        --sidebar-width: 280px;
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --shadow-sm: 0 1px 3px rgba(16, 24, 40, 0.08);
        --shadow-md: 0 4px 14px rgba(16, 24, 40, 0.10);
        --shadow-lg: 0 10px 30px rgba(16, 24, 40, 0.14);
        --transition-base: all 0.25s ease;
    }

    * { box-sizing: border-box; }
    html, body { overflow-x: hidden; max-width: 100%; }
    body {
        font-family: 'Noto Sans Lao', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: var(--surface-muted);
        color: var(--text-main);
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .right { float: right; }
    .circle { border-radius: var(--radius-md); overflow: hidden; }

    /* ==========================================================================
       Layout Wrapper
       ========================================================================== */
    #wrapper { position: relative; min-height: 100vh; width: 100%; }

    /* ==========================================================================
       Sidebar
       ========================================================================== */
    #sidebar-wrapper {
        background: linear-gradient(180deg, var(--brand-navy) 0%, var(--brand-navy-dark) 100%);
        color: #fff;
        box-shadow: 2px 0 12px rgba(0, 0, 0, 0.18);
        border-right: none !important;
        min-height: 100vh;
        width: var(--sidebar-width);
        flex-shrink: 0;
        transition: margin-left 0.3s ease, transform 0.3s ease;
    }
    #sidebar-wrapper .sidebar-heading {
        background: rgba(0, 0, 0, 0.18) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
        color: #fff !important;
        font-weight: 600;
        padding: 1.1rem 1.25rem;
        display: flex; align-items: center; gap: 10px;
    }
    #sidebar-wrapper .sidebar-heading img {
        width: 100px !important; height: auto; border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.08); padding: 6px;
        transition: var(--transition-base); box-shadow: var(--shadow-sm);
    }
    #sidebar-wrapper .sidebar-heading img:hover {
        transform: scale(1.05); box-shadow: var(--shadow-md);
    }
    #sidebar-wrapper .list-group-item {
        background: transparent !important;
        color: rgba(255, 255, 255, 0.88) !important;
        border: none; border-radius: 0;
        padding: 0.75rem 1.25rem;
        transition: var(--transition-base);
        font-weight: 500;
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        border-left: 4px solid transparent;
    }
    #sidebar-wrapper .list-group-item:hover {
        background: rgba(255, 255, 255, 0.10) !important;
        transform: translateX(4px);
        color: #fff !important;
    }
    #sidebar-wrapper .list-group-item .badge {
        background-color: var(--brand-gold); color: #1f2937; margin-left: auto;
        font-weight: 600;
    }
    #sidebar-wrapper .list-group-item.active {
        background: rgba(255, 255, 255, 0.14) !important;
        border-left: 4px solid var(--brand-gold);
        color: #fff !important;
    }
    #sidebar-wrapper .list-group-item i,
    #sidebar-wrapper .list-group-item .fi,
    #sidebar-wrapper .list-group-item .bi {
        font-size: 18px; width: 24px; text-align: center; flex-shrink: 0;
    }

    /* ==========================================================================
       Navbar
       ========================================================================== */
    .navbar-green {
        background: linear-gradient(135deg, var(--brand-navy) 0%, var(--brand-navy-dark) 100%) !important;
        box-shadow: var(--shadow-sm);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
        flex-wrap: wrap;
    }
    .navbar-green .nav-link, .navbar-green .navbar-brand, .navbar-green .navbar-text {
        color: #fff !important;
    }
    .navbar-green .nav-link:hover { color: rgba(255, 255, 255, 0.75) !important; }
    .navbar-green .btn-success {
        background-color: var(--brand-green) !important;
        border-color: var(--brand-green) !important;
        color: #fff !important;
        box-shadow: var(--shadow-sm);
        transition: var(--transition-base);
    }
    .navbar-green .btn-success:hover {
        background-color: var(--brand-green-dark) !important;
        box-shadow: var(--shadow-md);
    }
    #sidebarToggle {
        background-color: rgba(255, 255, 255, 0.10) !important;
        border-color: rgba(255, 255, 255, 0.25) !important;
        color: #fff !important; white-space: nowrap;
        transition: var(--transition-base);
    }
    #sidebarToggle:hover { background-color: rgba(255, 255, 255, 0.22) !important; }

    /* ==========================================================================
       Dropdown ພາສາ / ຜູ້ໃຊ້
       ========================================================================== */
    .animate-dropdown {
        border-radius: var(--radius-md); border: none; padding: 0.5rem 0; min-width: 200px;
        animation: fadeInDown 0.25s ease; box-shadow: var(--shadow-lg);
    }
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .lang-option { display: flex; align-items: center; padding: 0.6rem 1.25rem; transition: var(--transition-base); }
    .lang-option:hover { background: var(--brand-green-light); transform: translateX(4px); }
    .lang-option.active { background: var(--brand-green); color: #fff !important; }
    .lang-option.active .lang-name { color: #fff !important; }
    .lang-option .flag-icon { font-size: 1.4rem; margin-right: 12px; }
    .lang-option .lang-name { flex: 1; font-weight: 500; }
    .lang-option .check-icon { color: transparent; font-size: 1rem; }
    .lang-option.active .check-icon { color: #fff; }

    .user-dropdown .dropdown-item { padding: 0.6rem 1.25rem; transition: background 0.2s; }
    .user-dropdown .dropdown-item:hover { background: var(--brand-green-light); }
    .user-dropdown .dropdown-item.text-danger:hover { background: #fdeaea; }
    .logout-btn { color: #fff !important; font-weight: 600; transition: color 0.25s; }
    .logout-btn:hover { color: var(--brand-gold) !important; }

    #page-content-wrapper { flex: 1; min-width: 0; width: 100%; }

    /* ==========================================================================
       Scrollbar
       ========================================================================== */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--surface-muted); border-radius: 10px; }
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark));
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover { background: var(--brand-green-dark); }

    #sidebarBackdrop { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 1040; }
    #sidebarBackdrop.show { display: block; }

    /* ==========================================================================
       Responsive - Layout
       ========================================================================== */
    @media (max-width: 991.98px) {
        #wrapper { display: block; }
        #sidebar-wrapper {
            position: fixed; top: 0; left: 0; height: 100vh; min-height: 100vh;
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
        .container-fluid.px-4 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
    }
    @media (max-width: 576px) {
        #sidebar-wrapper { width: 85%; max-width: 300px; }
        .btn-success#sidebarToggle { padding: 0.4rem 0.6rem; font-size: 0.85rem; }
    }

    /* ==========================================================================
       ໜ້າເອກະສານ (Document / Form Page)
       ========================================================================== */
    .page {
        width: 210mm; min-height: 297mm; padding: 15mm; margin: 10mm auto;
        background: var(--surface); box-shadow: var(--shadow-lg); border-radius: var(--radius-sm);
        position: relative; display: flex; flex-direction: column; overflow: hidden;
    }
    @media screen and (max-width: 1024px) {
        .page { width: 95%; padding: 10mm; margin: 5mm auto; min-height: auto; }
    }
    @media screen and (max-width: 768px) {
        .page {
            width: 98%; padding: 8px; margin: 4px auto; min-height: auto;
            box-shadow: none; border: 1px solid var(--border-soft); border-radius: var(--radius-sm);
        }
        .grid-cols-2 { grid-template-columns: 1fr !important; }
        .grid-cols-4 { grid-template-columns: 1fr 1fr !important; }
        .grid-cols-5 { grid-template-columns: 1fr 1fr !important; }
        .grid-cols-3 { grid-template-columns: 1fr !important; }
        .flex-wrap { flex-wrap: wrap; }
        .text-2xl { font-size: 1.25rem !important; }
        .text-xl { font-size: 1.1rem !important; }
        .w-20 { width: 3rem !important; }
        .h-20 { height: 3rem !important; }
        #toolbar { flex-wrap: wrap; justify-content: center; gap: 4px; padding: 4px 8px; }
        #toolbar a, #toolbar button { padding: 6px 12px !important; font-size: 11px !important; min-height: 36px; white-space: nowrap; }
        .data-table th, .data-table td { font-size: 9px !important; padding: 4px 2px !important; }
        .data-table .w-8 { width: 20px !important; min-width: 20px; }
        .data-table .w-12 { width: 28px !important; min-width: 28px; }
        .data-table .w-16 { width: 36px !important; min-width: 36px; }
        .data-table .w-20 { width: 40px !important; min-width: 40px; }
        .data-table .w-24 { width: 48px !important; min-width: 48px; }
        .data-table .w-64 { width: 60px !important; min-width: 60px; max-width: 80px; }
        .cell-input { font-size: 9px !important; min-height: 1.2em; word-break: break-all; }
        .photo-card .w-full.h-\[250px\] { height: 150px !important; }
        .photo-card .grid-cols-2 { grid-template-columns: 1fr !important; }
        .signature-block .grid-cols-3 { grid-template-columns: 1fr !important; gap: 12px; }
        .signature-block .flex { flex-wrap: wrap; gap: 8px; }
        .signature-block .w-36 { width: 60px !important; }
        #total-cost { width: 60px !important; font-size: 11px; }
        .watermark-img { width: 120px !important; height: 120px !important; }
        #save-status { font-size: 9px !important; padding: 4px 8px !important; margin-left: 0 !important; width: 100%; text-align: center; }
    }
    @media screen and (max-width: 480px) {
        .page { padding: 4px; border-radius: 4px; }
        .grid-cols-4 { grid-template-columns: 1fr 1fr !important; gap: 4px; }
        .grid-cols-5 { grid-template-columns: 1fr 1fr !important; gap: 4px; }
        .flex.justify-between.items-start { flex-direction: column !important; align-items: stretch !important; gap: 8px; }
        .flex.justify-between.items-start .text-right { text-align: left !important; }
        .flex.gap-4 { gap: 8px !important; flex-wrap: wrap; }
        #toolbar a, #toolbar button { padding: 4px 8px !important; font-size: 10px !important; min-height: 30px; border-radius: var(--radius-sm) !important; }
        #toolbar { gap: 3px; padding: 3px 4px; }
        .data-table th, .data-table td { font-size: 7px !important; padding: 2px 1px !important; }
        .data-table .w-8 { width: 14px !important; min-width: 14px; }
        .data-table .w-12 { width: 18px !important; min-width: 18px; }
        .data-table .w-16 { width: 22px !important; min-width: 22px; }
        .data-table .w-20 { width: 26px !important; min-width: 26px; }
        .data-table .w-24 { width: 30px !important; min-width: 30px; }
        .data-table .w-64 { width: 40px !important; min-width: 40px; max-width: 50px; }
        .cell-input { font-size: 7px !important; min-height: 1em; }
        .photo-card .w-full.h-\[250px\] { height: 100px !important; }
        .border.p-2 { padding: 4px !important; }
        .bg-emerald-50\/50.p-2\.5 { padding: 6px !important; }
        .rounded-lg { border-radius: 4px !important; }
        .no-print .bg-amber-100 { padding: 6px 8px !important; font-size: 10px !important; }
        .signature-block .border.p-2 { padding: 6px !important; }
        #total-cost { width: 40px !important; font-size: 9px; }
        .watermark-img { width: 80px !important; height: 80px !important; }
        #save-status { font-size: 8px !important; padding: 3px 6px !important; }
        #comp-name { font-size: 1rem !important; }
    }

    /* ==========================================================================
       Watermark
       ========================================================================== */
    .watermark {
        position: absolute; top: 53%; left: 50%; transform: translate(-50%, -50%);
        width: 320px; height: 320px; opacity: 0.06; pointer-events: none; z-index: 0;
    }
    @media screen and (max-width: 768px) { .watermark { width: 160px !important; height: 160px !important; } }
    @media screen and (max-width: 480px) { .watermark { width: 100px !important; height: 100px !important; } }
    .print-watermark { display: none; }
    .page-content { position: relative; z-index: 10; display: flex; flex-direction: column; height: 100%; flex-grow: 1; overflow: hidden; }
    @media screen and (max-width: 768px) { .page-content { overflow-x: hidden; } }
    .photo-card { page-break-inside: avoid; break-inside: avoid; }

    @media print {
        body { background: none; margin: 0; padding: 0; }
        #sidebar-wrapper, #sidebarBackdrop, .navbar-green { display: none !important; }
        #page-content-wrapper { width: 100% !important; }
        .page {
            margin: 0; border: none; box-shadow: none;
            width: 4in !important; max-width: 4.25in !important; min-height: 6in !important;
            page-break-after: always; overflow: visible !important; padding: 3mm !important;
        }
        .no-print { display: none !important; }
        .watermark { display: none !important; }
        .print-watermark {
            display: block !important; position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%); width: 320px; height: 320px; opacity: 0.06; z-index: -1;
        }
        .data-table tbody tr { page-break-inside: avoid; break-inside: avoid; }
        .signature-block { page-break-inside: avoid; break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
    }

    /* ==========================================================================
       Editable Fields
       ========================================================================== */
    .editable-header:hover {
        background-color: var(--brand-green-dark) !important;
        outline: 2px dashed #6ee7b7; cursor: text; border-radius: 4px;
    }
    .editable:hover {
        background-color: var(--brand-green-light) !important;
        outline: 1px dashed var(--brand-green); cursor: text; border-radius: 3px;
    }
    [contenteditable="true"]:empty:before { content: attr(placeholder); color: #9ca3af; font-weight: normal; }

    .theme-green { background-color: var(--brand-green-dark); }
    .text-green { color: var(--brand-green-dark); }
    .border-green { border-color: var(--brand-green-dark); }

    /* ==========================================================================
       ຕາຕະລາງຂໍ້ມູນ (Data Table) - ອ່ານງ່າຍ, contrast ຊັດເຈນ
       ========================================================================== */
    .data-table { border-collapse: separate; border-spacing: 0; width: 100%; }
    .data-table th {
        background-color: var(--brand-green-dark) !important;
        color: #fff !important; font-size: 11px; font-weight: 600;
        letter-spacing: 0.02em; padding: 9px 6px;
        border-bottom: 2px solid var(--brand-navy-dark);
    }
    @media screen and (max-width: 768px) { .data-table th { font-size: 8px !important; padding: 4px 2px !important; } }
    @media screen and (max-width: 480px) { .data-table th { font-size: 6px !important; padding: 2px 1px !important; } }

    .data-table td {
        padding: 7px 6px; font-size: 11px; border: 1px solid var(--border-strong);
        vertical-align: top; color: var(--text-main); background-color: var(--surface);
        transition: background-color 0.15s ease;
    }
    .data-table tbody tr:nth-child(even) td { background-color: var(--surface-muted); }
    .data-table tbody tr:hover td { background-color: var(--brand-green-light); }
    @media screen and (max-width: 768px) { .data-table td { padding: 4px 2px !important; font-size: 8px !important; } }
    @media screen and (max-width: 480px) { .data-table td { padding: 2px 1px !important; font-size: 6px !important; } }

    .cell-input {
        width: 100%; min-height: 1.5em; outline: none; word-break: break-word;
        white-space: pre-wrap; border-radius: 3px; transition: background-color 0.15s ease;
    }
    .cell-input:focus { background-color: #fef9c3; box-shadow: 0 0 0 2px var(--brand-gold) inset; }

    /* ==========================================================================
       Toolbar & ອື່ນໆ
       ========================================================================== */
    #toolbar {
        max-width: 210mm; margin: 12px auto 0 auto; display: flex; justify-content: center;
        align-items: center; flex-wrap: wrap; gap: 8px; padding: 0 8px;
    }
    @media screen and (max-width: 768px) { #toolbar { gap: 4px; padding: 0 4px; } }
    #save-status {
        display: inline-flex; border-radius: var(--radius-sm); font-weight: 500;
    }
    @media screen and (max-width: 480px) { #save-status { display: flex; justify-content: center; width: 100%; } }

    .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    @media screen and (max-width: 768px) { .table-wrapper { margin: 0 -4px; padding: 0 4px; } }

    .watermark-img { max-width: 100%; height: auto; }
    .no-print button, .no-print a { touch-action: manipulation; }

    .space-y-1\.5>*+* { margin-top: 0.375rem; }
    .space-y-3>*+* { margin-top: 0.75rem; }
    @media screen and (max-width: 480px) {
        .space-y-1\.5>*+* { margin-top: 0.2rem; }
        .space-y-3>*+* { margin-top: 0.4rem; }
        .mb-4 { margin-bottom: 0.5rem !important; }
        .mt-4 { margin-top: 0.5rem !important; }
        .pb-4 { padding-bottom: 0.5rem !important; }
        .pt-2 { padding-top: 0.25rem !important; }
    }

    #pr-dropdown {
        font-family: 'Noto Sans Lao', sans-serif;
        border-radius: var(--radius-md); box-shadow: var(--shadow-md);
    }
    #pr-dropdown::-webkit-scrollbar { width: 6px; }
    #pr-dropdown::-webkit-scrollbar-thumb { background-color: #a7f3d0; border-radius: 4px; }
</style>
</head>

<body class="pb-10">

    <div id="sidebarBackdrop"></div>

    <div class="d-flex" id="wrapper">
        <!-- ===== Sidebar (ລວມສູນ, ໃຊ້ຮ່ວມກັນທຸກໜ້າ) ===== -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        <?php
        // ---- ປິດ connection ຫຼັງ sidebar.php ໃຊ້ແລ້ວ (ບໍ່ໃຊ້ $conn ຕໍ່ໃນສ່ວນ HTML ທີ່ເຫຼືອ) ----
        $conn->close();
        ?>

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

            <!-- ===== ເນື້ອໃນໜ້າ: ຟອມໃບຝາກເຄື່ອງ (GR) ===== -->
            <div class="container-fluid px-4 pt-3">

                <img class="print-watermark watermark-img object-contain" src="image/Logo.png" alt="Watermark Logo">

                <script type="application/json" id="edit-data-json">
                    <?php echo $editDataJson; ?>
                </script>

                <?php if ($editData) { ?>
                    <div class="no-print" style="max-width:210mm;margin:10px auto 0 auto;padding:0 8px;">
                        <div class="bg-amber-100 border border-amber-300 text-amber-800 text-sm font-bold rounded-lg px-4 py-2 text-center">
                            <i class="fa-solid fa-floppy-disk"></i> ກຳລັງແກ້ໄຂເອກະສານເລກທີ <?php echo htmlspecialchars($editData['docNo'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php } ?>

                <div id="toolbar" class="no-print">
                    <!-- <a href="Dasborad.php" class="bg-orange-600 hover:bg-orange-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all flex items-center gap-1.5"><i class="fa-solid fa-house"></i> ກັບຄືນ</a> -->
                    <button id="btn-save" onclick="saveToDatabase()" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-floppy-disk"></i> <span id="btn-save-label"><?php echo $editData ? 'ບັນທຶກການແກ້ໄຂ' : 'ບັນທຶກ'; ?></span>
                    </button>
                    <button onclick="window.print()" class="bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all">
                        <i class="fa-solid fa-print"></i> ພິມ
                    </button>
                    <span id="save-status" class="text-xs px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 font-medium items-center gap-1.5 transition-all ml-2">ຍັງບໍ່ໄດ້ບັນທຶກ</span>
                </div>

                <!-- ໜ້າທີ 1: ໃບຝາກເຄື່ອງ (GR) -->
                <div class="page" id="page1">
                    <div class="watermark flex items-center justify-center">
                        <img class="watermark-img w-full h-full object-contain" src="image/Logo.png" alt="Watermark Logo">
                    </div>

                    <div class="page-content flex-grow">
                        <table class="w-full border-0 layout-table" style="border-spacing: 0; border-collapse: collapse;">
                            <thead class="border-0">
                                <tr>
                                    <td class="p-0 border-0 align-top" style="padding: 0; border: none; background: transparent;">
                                        <div class="flex justify-between items-start border-b-4 border-emerald-900 pb-4 mb-4">
                                            <div class="flex gap-4 flex-wrap">
                                                <div class="relative w-20 h-20 border rounded overflow-hidden bg-gray-50 flex items-center justify-center shrink-0">
                                                    <img id="logo-preview" src="image/Logo.png" class="w-full h-full object-contain" title="ໂລໂກ້ບໍລິສັດ ຄົງທີ່, ແກ້ໄຂບໍ່ໄດ້">
                                                </div>
                                                <div class="min-w-[120px]">
                                                    <h1 id="comp-name" class="text-2xl font-bold text-emerald-900" contenteditable="false" title="ຂໍ້ມູນບໍລິສັດ ຄົງທີ່, ແກ້ໄຂບໍ່ໄດ້">ບໍລິສັດ ຜະລິດແປ້ງມັນຕົ້ນແອວເອັສ ຈຳກັດ</h1>
                                                    <p id="comp-address" class="text-sm text-gray-600" contenteditable="false" title="ຂໍ້ມູນບໍລິສັດ ຄົງທີ່, ແກ້ໄຂບໍ່ໄດ້">ສຳນັກງານໃຫຍ່, ສີບຸນເຮືອງ ເມືອງ ຈັນທະບູລີ ນະຄອນຫຼວງວຽງຈັນ</p>
                                                    <p id="comp-contact" class="text-xs text-gray-500" contenteditable="false" title="ຂໍ້ມູນບໍລິສັດ ຄົງທີ່, ແກ້ໄຂບໍ່ໄດ້">ໂທ: +856 20 29 611 111 | Email: ls.starch@lslao.com.la</p>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <h2 id="form-title" class="text-xl font-black text-white bg-emerald-900 px-4 py-1.5 rounded" contenteditable="false" placeholder="ຫົວຂໍ້ເອກະສານ" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້ເອກະສານ">ໃບຝາກເຄື່ອງ (GR)</h2>
                                                <p class="mt-2 text-xs text-gray-600">ເລກທີ: <span id="doc-no" class="font-bold text-gray-800 bg-gray-100 px-1 rounded" contenteditable="false" title="ອອກເລກໃຫ້ອັດຕະໂນມັດຕອນບັນທຶກ">ຈະອອກເລກຫຼັງບັນທຶກ</span></p>
                                                <p class="text-xs text-gray-600">ວັນທີ: <span id="doc-date" class="font-bold text-gray-800 bg-gray-100 px-1 rounded" contenteditable="false" title="ອອກວັນທີ່ໃຫ້ອັດຕະໂນມັດ ບໍ່ສາມາດແກ້ໄຂໄດ້"></span></p>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mb-3">
                                            <div class="bg-emerald-50/50 p-2.5 rounded-lg border border-emerald-100">
                                                <label class="text-[10px] font-bold text-emerald-800 block mb-1">ຝາກຈາກ ຕົ້ນທາງ (From):</label>
                                                <input type="text" name="From" id="From" placeholder="ຕົ້ນທາງ..."
                                                    class="text-sm font-semibold w-full border border-emerald-300 rounded px-2 py-1.5 bg-white text-blue-700 outline-none focus:ring-2 focus:ring-emerald-400">
                                            </div>
                                            <div class="bg-orange-50/50 p-2.5 rounded-lg border border-orange-100">
                                                <label class="text-[10px] font-bold text-orange-800 block mb-1">ປາຍທາງ (To):</label>
                                                <select name="Factory_To" id="Factory_To"
                                                    class="text-sm font-semibold w-full border border-orange-300 rounded px-2 py-1.5 bg-white text-blue-700 outline-none focus:ring-2 focus:ring-orange-400">
                                                    <option value="HQ">ສຳນັກງານໃຫຍ່</option>
                                                    <option value="Sanakham">ໂຮງງານຊະນະຄາມ</option>
                                                    <option value="MuangNan">ໂຮງງານເມືອງນານ</option>
                                                    <option value="Vientiane">ສາງສຳນັກງານໃຫຍ່</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-5 gap-2 mb-3 text-xs">
                                            <div class="border p-2 rounded">
                                                <label class="block text-[10px] text-gray-500 font-bold">ເລກທີ PR / PR No:</label>
                                                <div class="relative">
                                                    <input type="text" id="pr-no" autocomplete="off" placeholder="ພິມ ຫຼື ຄົ້ນຫາເລກ PR..."
                                                        class="font-normal text-gray-800 outline-none w-full min-h-[1.5em] bg-white border border-gray-200 rounded px-1 py-0.5"
                                                        oninput="onPrInput()" onfocus="renderPrDropdown('')" onblur="setTimeout(hidePrDropdown, 150)">
                                                    <div id="pr-dropdown"
                                                        class="hidden absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded shadow-lg max-h-56 overflow-y-auto">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border p-2 rounded bg-sky-50/30 border-sky-200">
                                                <label class="block text-[10px] text-sky-800 font-bold">ແຜນເລກທີ່ / Planning :</label>
                                                <div id="track-plan" contenteditable="true" class="font-normal text-gray-800 outline-none w-full min-h-[1.5em]" placeholder="ລະບຸແຜນເລກທີ່..." title="ລວມແຜນເລກຂອງທຸກ PR ທີ່ຢູ່ໃນຕາຕະລາງ - ແກ້ໄຂມືອາດຖືກຂຽນທັບເມື່ອເລືອກ PR ໃໝ່"></div>
                                            </div>
                                            <div class="border p-2 rounded">
                                                <label class="block text-[10px] text-gray-500 font-bold">ຂົນສົ່ງໂດຍ (Carrier/Driver):</label>
                                                <div id="carrier" contenteditable="true" class="font-normal text-gray-800 outline-none w-full min-h-[1.5em]" placeholder="ລະບຸບໍລິສັດຂົນສົ່ງ/ຄົນຂັບ..."></div>
                                            </div>
                                            <div class="border p-2 rounded">
                                                <label class="block text-[10px] text-gray-500 font-bold"><span class="editable" contenteditable="true" placeholder="ຫົວຂໍ້...">ເບີໂທຜູ້ຂົນສົ່ງ:</span></label>
                                                <div id="carrier-phone" contenteditable="true" class="font-normal text-gray-800 outline-none w-full min-h-[1.5em]" placeholder="ລະບຸເບີໂທ..."></div>
                                            </div>
                                            <div class="border p-2 rounded bg-emerald-50/30 border-emerald-200">
                                                <label class="block text-[10px] text-emerald-800 font-bold">ຈຳນວນ ກ່ອງ / Total Box:</label>
                                                <div id="total-box" contenteditable="true" class="font-bold text-emerald-900 outline-none w-full min-h-[1.5em]" placeholder="ລະບຸຈຳນວນກ່ອງ..."></div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div class="border border-emerald-100 rounded-lg p-3 bg-emerald-50/10">
                                                <p class="text-xs font-bold text-emerald-800 border-b border-emerald-100 pb-1 mb-2">ລາຍລະອຽດ ຜູ້ຝາກ (Sender Details)</p>
                                                <div class="space-y-1.5 text-xs">
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ຊື່-ນາມສະກຸນ:</span>
                                                        <div id="sender-name" contenteditable="true" class="border-b border-gray-200 w-full bg-transparent focus:border-emerald-600 outline-none pb-0.5 min-h-[1.5em]" placeholder="ລະບຸຊື່..."><?= htmlspecialchars($senderName, ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ພະແນກ:</span>
                                                        <div id="sender-dept" contenteditable="true" class="border-b border-gray-200 w-full bg-transparent focus:border-emerald-600 outline-none pb-0.5 min-h-[1.5em]" placeholder="ລະບຸພະແນກ..."><?= htmlspecialchars($senderDept, ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ເບີໂທຕິດຕໍ່:</span>
                                                        <div id="sender-phone" contenteditable="true" class="border-b border-gray-200 w-full bg-transparent focus:border-emerald-600 outline-none pb-0.5 min-h-[1.5em]" placeholder="ລະບຸເບີໂທ..."><?= htmlspecialchars($senderPhone, ENT_QUOTES, 'UTF-8') ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="border border-orange-100 rounded-lg p-3 bg-gray-50">
                                                <p class="text-xs font-bold text-orange-800 border-b border-orange-100 pb-1 mb-1">ລາຍລະອຽດ ຜູ້ຮັບ (Receiver Details)</p>
                                                <p class="text-[10px] text-gray-400 italic mb-2">ຈະປ້ອນຢູ່ Inbox ຂອງໂຮງງານປາຍທາງ, ແກ້ໄຂຢູ່ນີ້ບໍ່ໄດ້</p>
                                                <div class="space-y-1.5 text-xs">
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ຊື່-ນາມສະກຸນ:</span>
                                                        <div id="receiver-name" contenteditable="false" class="border-b border-gray-200 w-full bg-transparent min-h-[1.5em]"></div>
                                                    </div>
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ພະແນກ:</span>
                                                        <div id="receiver-dept" contenteditable="false" class="border-b border-gray-200 w-full bg-transparent min-h-[1.5em]"></div>
                                                    </div>
                                                    <div class="flex items-start"><span class="text-gray-500 w-20 shrink-0">ເບີໂທຕິດຕໍ່:</span>
                                                        <div id="receiver-phone" contenteditable="false" class="border-b border-gray-200 w-full bg-transparent min-h-[1.5em]"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <tr>
                                    <td class="p-0 border-0 align-top" style="padding: 0; border: none; background: transparent;">
                                        <div class="flex-grow">
                                            <div class="table-wrapper">
                                                <table class="w-full border-collapse border border-gray-300 data-table">
                                                    <thead>
                                                        <tr>
                                                            <th class="border w-8" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ລ/ດ</th>
                                                            <th class="border w-24" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ເລກທີ PR</th>
                                                            <th class="border w-20" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ເລກລາຍການ</th>
                                                            <th class="border w-64" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ຊື່ລາຍການສິນຄ້າ</th>
                                                            <th class="border w-16" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ຂະໜາດ</th>
                                                            <th class="border w-12" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ຈຳນວນ</th>
                                                            <th class="border w-12" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ໜ່ວຍ</th>
                                                            <th class="border w-16" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ນ້ຳໜັກ</th>
                                                            <th class="border w-16" contenteditable="false" placeholder="-" title="ຄລິກເພື່ອແກ້ໄຂຫົວຂໍ້">ໝາຍເຫດ</th>
                                                            <th class="border w-8 no-print table-col-delete">ລຶບ</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="item-table-body">
                                                        <tr>
                                                            <td class="text-center font-bold text-gray-700">1</td>
                                                            <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ..."></div></td>
                                                            <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ..."></div></td>
                                                            <td><div contenteditable="true" class="cell-input font-semibold" placeholder="ຊື່ສິນຄ້າ..."></div></td>
                                                            <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                                                            <td><div contenteditable="true" class="cell-input text-center" placeholder="0"></div></td>
                                                            <td><div contenteditable="true" class="cell-input text-center" placeholder="..."></div></td>
                                                            <td><div contenteditable="true" class="cell-input text-right" placeholder="0"></div></td>
                                                            <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                                                            <td class="text-center no-print table-col-delete"><button onclick="removeTableRow(this)" class="text-red-500 hover:text-red-700 font-bold"><i class="fa-solid fa-box-archive"></i></button></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="no-print flex justify-start mt-2">
                                                <button id="btn-add-row" onclick="addNewTableRow()" class="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold px-3 py-1.5 rounded shadow-sm">
                                                    <i class="fa-solid fa-plus"></i> ເພີ່ມລາຍການ
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="border-0">
                                <tr>
                                    <td class="p-0 border-0 align-bottom" style="padding: 0; border: none; background: transparent;">
                                        <div class="mt-4 signature-block pt-2">
                                            <div class="flex flex-wrap justify-between items-center mb-4 bg-emerald-50/30 p-2 rounded border border-emerald-100 gap-2">
                                                <span id="footnote" class="text-[10px] text-gray-500" contenteditable="false" placeholder="ໝາຍເຫດເພີ່ມເຕີມ...">* ກະລຸນາກວດສອບເຄື່ອງ ແລະ ລົງລາຍເຊັນເພື່ອເປັນຫຼັກຖານໃນການຮັບ-ສົ່ງ</span>
                                                <div class="bg-emerald-900 text-white p-1.5 rounded flex flex-wrap gap-4 items-center">
                                                    <span class="text-xs font-semibold text-white" contenteditable="false" placeholder="ລາຄາຂົນສົ່ງ...">ລາຄາຂົນສົ່ງທັງໝົດ (Total Cost):</span>
                                                    <div id="total-cost" contenteditable="true" class="bg-transparent border-b border-white text-right font-bold w-24 outline-none min-h-[1.5em] text-white" placeholder="0"></div>
                                                    <span class="text-xs text-white">LAK</span>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-4 text-center text-xs pt-2">
                                                <div class="space-y-3 border p-2 rounded bg-gray-50/50 flex flex-col justify-between">
                                                    <p id="sig-sender-title" class="font-bold text-gray-600" contenteditable="false" placeholder="ຕຳແໜ່ງ..." title="ຄລິກເພື່ອແກ້ໄຂຕຳແໜ່ງ">ຜູ້ຝາກເຄື່ອງ (Sender)</p>
                                                    <div class="relative flex flex-col items-center justify-center min-h-[60px] my-1 group/sig">
                                                        <img id="sig-img-sender" class="hidden max-h-[70px] object-contain transition-all" style="width: 100px;">
                                                        <button id="sig-upload-btn-sender" onclick="document.getElementById('sig-file-sender').click()" class="no-print opacity-0 group-hover/sig:opacity-100 absolute bg-emerald-800 hover:bg-emerald-900 text-white text-[10px] px-2 py-1 rounded shadow-sm transition-all z-20">
                                                            ＋ ໃສ່ລາຍເຊັນ
                                                        </button>
                                                        <input type="file" id="sig-file-sender" class="hidden" accept="image/*" onchange="uploadSignature(this, 'sig-img-sender', 'sig-controls-sender')">
                                                        <div id="sig-controls-sender" class="no-print hidden absolute -bottom-5 flex gap-1 bg-white border border-gray-200 p-0.5 rounded shadow-md z-30">
                                                            <button onclick="resizeSig('sig-img-sender', -10)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-[9px] font-bold">- ຫຍໍ້</button>
                                                            <button onclick="resizeSig('sig-img-sender', 10)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-[9px] font-bold">+ ຂະຫຍາຍ</button>
                                                            <button onclick="clearSignature('sig-img-sender', 'sig-file-sender', 'sig-controls-sender')" class="px-1.5 py-0.5 bg-red-100 hover:bg-red-200 text-red-600 rounded text-[9px]">ລຶບ</button>
                                                        </div>
                                                    </div>
                                                    <div class="border-b border-gray-400 w-36 mx-auto"></div>
                                                    <p id="sig-sender-name" class="font-bold text-gray-800" contenteditable="true" placeholder="ຊື່ຜູ້ເຊັນ..." title="ຄລິກເພື່ອແກ້ໄຂຊື່"></p>
                                                    <p id="sig-sender-date" class="text-[10px] text-gray-400" contenteditable="true" placeholder="ວັນທີ..." title="ຄລິກເພື່ອແກ້ໄຂວັນທີ"></p>
                                                </div>

                                                <div class="space-y-3 border p-2 rounded bg-gray-50/50 flex flex-col justify-between">
                                                    <p id="sig-carrier-title" class="font-bold text-gray-600" contenteditable="false" placeholder="ຕຳແໜ່ງ..." title="ຄລິກເພື່ອແກ້ໄຂຕຳແໜ່ງ">ພະນັກງານຂົນສົ່ງ (Driver)</p>
                                                    <div class="relative flex flex-col items-center justify-center min-h-[60px] my-1 group/sig">
                                                        <img id="sig-img-carrier" class="hidden max-h-[70px] object-contain transition-all" style="width: 100px;">
                                                        <button id="sig-upload-btn-carrier" onclick="document.getElementById('sig-file-carrier').click()" class="no-print opacity-0 group-hover/sig:opacity-100 absolute bg-emerald-800 hover:bg-emerald-900 text-white text-[10px] px-2 py-1 rounded shadow-sm transition-all z-20">
                                                            ＋ ໃສ່ລາຍເຊັນ
                                                        </button>
                                                        <input type="file" id="sig-file-carrier" class="hidden" accept="image/*" onchange="uploadSignature(this, 'sig-img-carrier', 'sig-controls-carrier')">
                                                        <div id="sig-controls-carrier" class="no-print hidden absolute -bottom-5 flex gap-1 bg-white border border-gray-200 p-0.5 rounded shadow-md z-30">
                                                            <button onclick="resizeSig('sig-img-carrier', -10)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-[9px] font-bold">- ຫຍໍ້</button>
                                                            <button onclick="resizeSig('sig-img-carrier', 10)" class="px-1.5 py-0.5 bg-gray-100 hover:bg-gray-200 rounded text-[9px] font-bold">+ ຂະຫຍາຍ</button>
                                                            <button onclick="clearSignature('sig-img-carrier', 'sig-file-carrier', 'sig-controls-carrier')" class="px-1.5 py-0.5 bg-red-100 hover:bg-red-200 text-red-600 rounded text-[9px]">ລຶບ</button>
                                                        </div>
                                                    </div>
                                                    <div class="border-b border-gray-400 w-36 mx-auto"></div>
                                                    <p id="sig-carrier-name" class="text-gray-500" contenteditable="true" placeholder="ຊື່ຜູ້ເຊັນ..." title="ຄລິກເພື່ອແກ້ໄຂຊື່"></p>
                                                    <p id="sig-carrier-date" class="text-[10px] text-gray-400" contenteditable="true" placeholder="ວັນທີ..." title="ຄລິກເພື່ອແກ້ໄຂວັນທີ"></p>
                                                </div>

                                                <div class="space-y-3 border border-emerald-100 p-2 rounded bg-emerald-50/20 flex flex-col justify-between">
                                                    <p id="sig-receiver-title" class="font-bold text-emerald-900" contenteditable="false" title="ຈະພິມຢູ່ Inbox ຂອງໂຮງງານປາຍທາງ">ຜູ້ຮັບເຄື່ອງ (Receiver)</p>
                                                    <div class="relative flex flex-col items-center justify-center min-h-[60px] my-1 group/sig">
                                                        <img id="sig-img-receiver" class="hidden max-h-[70px] object-contain transition-all" style="width: 100px;">
                                                        <p class="text-[10px] text-gray-400 italic">ຈະໃສ່ລາຍເຊັນຢູ່ Inbox ຂອງໂຮງງານປາຍທາງ</p>
                                                    </div>
                                                    <div class="border-b border-emerald-400 w-36 mx-auto"></div>
                                                    <p id="sig-receiver-name" class="font-bold text-gray-400 bg-gray-50" contenteditable="false" placeholder="ຈະພິມຢູ່ Inbox" title="ຈະພິມຢູ່ Inbox ຂອງໂຮງງານປາຍທາງ"></p>
                                                    <p id="sig-receiver-date" class="text-[10px] text-gray-400" contenteditable="false"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ໜ້າທີ 2: ເອກະສານແນບ ຮູບພາບສິນຄ້າ -->
                <div class="page" id="page2">
                    <div class="watermark flex items-center justify-center">
                        <img class="watermark-img w-full h-full object-contain" src="image/Logo.png" alt="Watermark Logo">
                    </div>

                    <div class="page-content">
                        <div class="border-b-2 border-emerald-900 pb-2 mb-4 flex flex-wrap justify-between items-center gap-2">
                            <h2 id="photo-title" class="text-lg font-bold text-emerald-900 uppercase" contenteditable="false" placeholder="ຫົວຂໍ້...">ເອກະສານແນບ: ຮູບພາບສິນຄ້າ (Product Photos)</h2>
                            <span class="text-xs text-gray-400">ເອກະສານອ້າງອີງສິນຄ້າ</span>
                        </div>

                        <div id="photo-slots-container" class="space-y-6 flex-grow">
                            <div class="photo-card border-2 border-dashed border-emerald-200 rounded-xl p-4 bg-gray-50 flex flex-col items-center relative">
                                <div class="w-full h-[250px] bg-white border rounded shadow-inner flex items-center justify-center overflow-hidden mb-4 relative group">
                                    <img id="p-img-1" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='260'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' font-family='sans-serif' font-size='18' text-anchor='middle' fill='%239ca3af' dy='.3em'%3EClick to Upload Image%3C/text%3E%3C/svg%3E" class="h-full object-contain cursor-pointer w-full photo-input-trigger" onclick="document.getElementById('p-input-1').click()">
                                    <input type="file" id="p-input-1" class="hidden photo-input-file" onchange="previewProd(this, 'p-img-1')">
                                </div>
                                <div class="w-full grid grid-cols-2 gap-4">
                                    <div class="border-b border-gray-300">
                                        <label class="text-[10px] text-gray-500 block">ໝາຍເລກລາຍການ (Item ID):</label>
                                        <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-id" placeholder="ລະບຸ..."></div>
                                    </div>
                                    <div class="border-b border-gray-300">
                                        <label class="text-[10px] text-gray-500 block">ຊື່ສິນຄ້າ (Product Name):</label>
                                        <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-name" placeholder="ລະບຸ..."></div>
                                    </div>
                                </div>
                                <button onclick="removePhotoSlot(this)" class="no-print photo-del-btn mt-3 w-full bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all">
                                    <i class="fa-solid fa-box-archive"></i> ລຶບຊ່ອງຮູບພາບນີ້
                                </button>
                            </div>
                        </div>

                        <div class="no-print flex justify-center py-4">
                            <button id="btn-add-photo" onclick="addNewPhotoSlot()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg flex items-center text-sm shadow-md transition-all">
                                <i class="fa-solid fa-plus"></i> ເພີ່ມຊ່ອງໃສ່ຮູບພາບສິນຄ້າອື່ນໆ
                            </button>
                        </div>

                        <p class="text-center text-[9px] text-gray-400 mt-4 italic">ເອກະສານນີ້ແມ່ນສ່ວນໜຶ່ງຂອງໃບຝາກເຄື່ອງ ລະບຸເລກທີ.</p>
                    </div>
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

    <script>
        const PLACEHOLDER_IMG = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='260'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' font-family='sans-serif' font-size='18' text-anchor='middle' fill='%239ca3af' dy='.3em'%3EClick to Upload Image%3C/text%3E%3C/svg%3E";

        (function() {
            const el = document.getElementById('edit-data-json');
            const raw = el ? el.textContent.trim() : '';
            if (raw && raw !== 'null') {
                try {
                    window.EDIT_DATA = JSON.parse(raw);
                } catch (e) {
                    window.EDIT_DATA = null;
                    const warn = document.createElement('div');
                    warn.style.cssText = 'max-width:210mm;margin:5px auto;background:#fee2e2;border:1px solid #ef4444;border-radius:6px;padding:8px;font-size:11px;font-family:monospace;white-space:pre-wrap;word-break:break-all;';
                    warn.textContent = 'JSON.parse ລົ້ມເຫລວ: ' + e.message + ' | raw(300)=' + raw.substring(0, 300);
                    document.body.insertBefore(warn, document.body.firstChild);
                }
            } else {
                window.EDIT_DATA = null;
            }
        })();

        if (!window.EDIT_DATA) {
            (function() {
                const today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const yyyy = today.getFullYear();
                document.getElementById('doc-date').innerText = dd + '/' + mm + '/' + yyyy;
            })();
        }

        window.photoCount = 1;

        function showAlert(icon, title, text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#047857',
                confirmButtonText: 'ຕົກລົງ'
            });
        }

        function setStatus(kind, text) {
            const statusBox = document.getElementById('save-status');
            if (!statusBox) return;
            const colors = {
                idle: ['border-gray-200', 'bg-gray-50', 'text-gray-500'],
                saving: ['border-yellow-200', 'bg-yellow-50', 'text-yellow-800'],
                success: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-800'],
                error: ['border-red-200', 'bg-red-50', 'text-red-800']
            };
            const c = colors[kind] || colors.idle;
            statusBox.className = `text-xs px-3 py-2 rounded-lg border ${c[0]} ${c[1]} ${c[2]} font-medium inline-flex items-center gap-1.5 transition-all ml-2`;
            statusBox.innerText = text;
        }

        // ---- ສະແດງເລກແຜນລວມ (union) ຈາກທຸກແຖວໃນຕາຕະລາງລົງໃນຊ່ອງ header "#track-plan" ----
        // ໝາຍເຫດ: ຫຼັງແກ້ໄຂ, ຊ່ອງນີ້ເປັນພຽງ "ການສະແດງຜົນ" ເທົ່ານັ້ນ
        // ແຫຼ່ງຂໍ້ມູນຈິງແມ່ນ data-plan ທີ່ຜູກຢູ່ແຕ່ລະ <tr> (ຕໍ່ PR/ຕໍ່ລາຍການ)
        window.updateTrackPlanDisplay = function() {
            const trackPlanEl = document.getElementById('track-plan');
            if (!trackPlanEl) return;
            const rows = document.querySelectorAll('#item-table-body tr');
            const plans = [];
            rows.forEach(r => {
                const p = (r.dataset.plan || '').trim();
                if (p && plans.indexOf(p) === -1) plans.push(p);
            });
            trackPlanEl.innerText = plans.join(', ');
        }

        window.compileCurrentState = function() {
            const state = {};
            const simpleIds = [
                'comp-name', 'comp-address', 'comp-contact', 'form-title', 'doc-no', 'doc-date',
                'carrier', 'carrier-phone', 'total-box', 'track-plan',
                'sender-name', 'sender-dept', 'sender-phone', 'receiver-name', 'receiver-dept', 'receiver-phone',
                'footnote', 'total-cost', 'sig-sender-title', 'sig-sender-name', 'sig-sender-date',
                'sig-carrier-title', 'sig-carrier-name', 'sig-carrier-date',
                'sig-receiver-title', 'sig-receiver-name', 'sig-receiver-date',
                'photo-title'
            ];

            simpleIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) state[id] = el.innerText || '';
            });

            const fromEl = document.getElementById('From');
            if (fromEl) state['From'] = fromEl.value || '';

            const factoryToEl = document.getElementById('Factory_To');
            if (factoryToEl) state['Factory_To'] = factoryToEl.value || '';

            const prNoEl = document.getElementById('pr-no');
            if (prNoEl) state['pr-no'] = prNoEl.value || '';

            // ຄ່າ header "ເລກທີ່ແຜນ" ໃຊ້ເປັນ fallback ຂອງແຖວທີ່ບໍ່ມີ data-plan ຜູກມາ (ເຊັ່ນ ແຖວເພີ່ມມືເອງ)
            const headerTrackPlan = (document.getElementById('track-plan') ? document.getElementById('track-plan').innerText : '').trim();

            const tableRows = [];
            const rows = document.querySelectorAll('#item-table-body tr');
            rows.forEach((row) => {
                const inputs = row.querySelectorAll('.cell-input');
                if (inputs.length >= 8) {
                    const rowPlan = (row.dataset.plan && row.dataset.plan.trim()) ? row.dataset.plan.trim() : headerTrackPlan;
                    tableRows.push({
                        idx: row.cells[0].innerText,
                        pt: inputs[0].innerText,
                        itemNo: inputs[1].innerText,
                        itemName: inputs[2].innerText,
                        size: inputs[3].innerText,
                        qty: inputs[4].innerText,
                        unit: inputs[5].innerText,
                        weight: inputs[6].innerText,
                        remark: inputs[7].innerText,
                        plan: rowPlan
                    });
                }
            });
            state['tableRows'] = tableRows;

            const photos = [];
            const cards = document.querySelectorAll('#photo-slots-container .photo-card');
            cards.forEach(card => {
                const img = card.querySelector('img');
                const itemIdEl = card.querySelector('.photo-item-id');
                const itemNameEl = card.querySelector('.photo-item-name');
                photos.push({
                    imgSrc: img && img.src && !img.src.includes('Upload Image') ? img.src : '',
                    itemId: itemIdEl ? itemIdEl.innerText : '',
                    itemName: itemNameEl ? itemNameEl.innerText : ''
                });
            });
            state['photos'] = photos;

            const sigRoles = ['sender', 'carrier', 'receiver'];
            sigRoles.forEach(role => {
                const img = document.getElementById(`sig-img-${role}`);
                if (img) {
                    state[`sig-img-${role}-src`] = img.src || '';
                    state[`sig-img-${role}-width`] = img.style.width || '100px';
                    state[`sig-img-${role}-hidden`] = img.classList.contains('hidden');
                }
            });

            return state;
        }

        window.saveToDatabase = async function() {
            const saveBtn = document.getElementById('btn-save');
            const saveBtnLabel = document.getElementById('btn-save-label');

            if (saveBtn && saveBtn.disabled) {
                return;
            }

            const state = window.compileCurrentState();

            if (!state['From'] || !state['From'].trim()) {
                showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸ "ຕົ້ນທາງ (From)" ກ່ອນບັນທຶກ');
                document.getElementById('From').focus();
                return;
            }

            if (!state['carrier'] || !state['carrier'].trim()) {
                showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸ "ຂົນສົ່ງໂດຍ (Carrier/Driver)" ກ່ອນບັນທຶກ');
                document.getElementById('carrier').focus();
                return;
            }

            if (!state['sender-name'] || !state['sender-name'].trim()) {
                showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸ "ຊື່ຜູ້ຝາກ" ກ່ອນບັນທຶກ');
                document.getElementById('sender-name').focus();
                return;
            }

            if (!state['tableRows'] || state['tableRows'].length === 0) {
                showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາເພີ່ມຢ່າງໜ້ອຍ 1 ລາຍການສິນຄ້າກ່ອນບັນທຶກ');
                return;
            }

            for (let i = 0; i < state['tableRows'].length; i++) {
                const row = state['tableRows'][i];
                if (!row.itemName || !row.itemName.trim()) {
                    showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸ "ຊື່ລາຍການສິນຄ້າ" ໃນແຖວທີ ' + (i + 1) + ' ກ່ອນບັນທຶກ');
                    return;
                }
                if (!row.qty || !row.qty.trim() || parseInt(row.qty.replace(/[^0-9]/g, '')) <= 0) {
                    showAlert('warning', 'ຂໍ້ມູນຍັງບໍ່ຄົບ', 'ກະລຸນາລະບຸ "ຈຳນວນ" ໃນແຖວທີ ' + (i + 1) + ' ໃຫ້ຖືກຕ້ອງ (ຫຼາຍກວ່າ 0) ກ່ອນບັນທຶກ');
                    return;
                }
            }

            if (window.EDIT_DATA) {
                state['edit_doc_no'] = window.EDIT_DATA.docNo;
            }

            setStatus('saving', 'ກຳລັງບັນທຶກ...');
            if (saveBtn) {
                saveBtn.disabled = true;
            }
            if (saveBtnLabel) {
                saveBtnLabel.innerText = 'ກຳລັງບັນທຶກ...';
            }

            try {
                const res = await fetch('save_tacking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(state)
                });
                const result = await res.json();

                if (result.status === 'success') {
                    const docNoEl = document.getElementById('doc-no');
                    if (docNoEl && result.doc_no) docNoEl.innerText = result.doc_no;
                    setStatus('success', `ບັນທຶກສຳເລັດ (${result.inserted} ລາຍການ) - ເລກທີ ${result.doc_no}`);
                    if (saveBtnLabel) {
                        saveBtnLabel.innerText = 'ບັນທຶກແລ້ວ ✓';
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'ບັນທຶກສຳເລັດ',
                        html: `ເລກທີ່ເອກະສານ: <b>${result.doc_no}</b><br>ຈຳນວນລາຍການ: ${result.inserted}<br><br>ກຳລັງໂຫລດຂໍ້ມູນທີ່ບັນທຶກໄວ້...`,
                        confirmButtonColor: '#047857',
                        confirmButtonText: 'ຕົກລົງ',
                        allowOutsideClick: false
                    }).then(() => {
                        if (result.doc_no) {
                            window.location.href = 'tacking.php?edit=' + encodeURIComponent(result.doc_no);
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    setStatus('error', 'ຜິດພາດ: ' + result.message);
                    if (saveBtn) {
                        saveBtn.disabled = false;
                    }
                    if (saveBtnLabel) {
                        saveBtnLabel.innerText = window.EDIT_DATA ? 'ບັນທຶກການແກ້ໄຂ' : 'ບັນທຶກ';
                    }
                    showAlert('error', 'ບັນທຶກບໍ່ສຳເລັດ', result.message);
                }
            } catch (err) {
                setStatus('error', 'ເຊື່ອມຕໍ່ເຊີບເວີບໍ່ໄດ້');
                if (saveBtn) {
                    saveBtn.disabled = false;
                }
                if (saveBtnLabel) {
                    saveBtnLabel.innerText = window.EDIT_DATA ? 'ບັນທຶກການແກ້ໄຂ' : 'ບັນທຶກ';
                }
                showAlert('error', 'ເຊື່ອມຕໍ່ບໍ່ໄດ້', 'ບໍ່ສາມາດເຊື່ອມຕໍ່ເຊີບເວີໄດ້: ' + err.message);
                console.error(err);
            }
        }

        window.previewProd = function(input, imgId) {
            if (input.files && input.files[0]) {
                const imgEl = document.getElementById(imgId);
                const formData = new FormData();
                formData.append('image', input.files[0]);
                imgEl.style.opacity = '0.4';
                fetch('upload_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(result => {
                        imgEl.style.opacity = '1';
                        if (result.status === 'success') {
                            imgEl.src = result.path;
                        } else {
                            showAlert('error', 'ອັບໂຫລດຮູບບໍ່ສຳເລັດ', result.message);
                        }
                    })
                    .catch(err => {
                        imgEl.style.opacity = '1';
                        showAlert('error', 'ອັບໂຫລດຮູບບໍ່ສຳເລັດ', err.message);
                    });
            }
        }

        window.clearImage = function(imgId, inputId) {
            document.getElementById(imgId).src = PLACEHOLDER_IMG;
            document.getElementById(inputId).value = "";
        }

        window.addNewTableRow = function() {
            const tbody = document.getElementById('item-table-body');
            const newIndex = tbody.rows.length + 1;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center font-bold text-gray-700">${newIndex}</td>
                <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ..."></div></td>
                <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ..."></div></td>
                <td><div contenteditable="true" class="cell-input font-semibold" placeholder="ຊື່ສິນຄ້າ..."></div></td>
                <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                <td><div contenteditable="true" class="cell-input text-center" placeholder="0"></div></td>
                <td><div contenteditable="true" class="cell-input text-center" placeholder="..."></div></td>
                <td><div contenteditable="true" class="cell-input text-right" placeholder="0"></div></td>
                <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                <td class="text-center no-print table-col-delete"><button onclick="removeTableRow(this)" class="text-red-500 hover:text-red-700 font-bold"><i class="fa-solid fa-box-archive"></i></button></td>
            `;
            // ແຖວທີ່ເພີ່ມມືເອງ ບໍ່ມີ PR/ແຜນຜູກມາ, ຈະໃຊ້ຄ່າ header "ເລກທີ່ແຜນ" ເປັນ fallback ຕອນບັນທຶກ
            tbody.appendChild(tr);
        }

        window.removeTableRow = function(button) {
            const row = button.closest('tr');
            const tbody = document.getElementById('item-table-body');
            if (tbody.rows.length > 1) {
                row.remove();
                Array.from(tbody.rows).forEach((r, idx) => {
                    r.cells[0].innerText = idx + 1;
                });
                updateTrackPlanDisplay();
            } else {
                setStatus('error', 'ຕ້ອງມີຢ່າງໜ້ອຍ 1 ລາຍການ');
                setTimeout(() => setStatus('idle', 'ຍັງບໍ່ໄດ້ບັນທຶກ'), 2000);
            }
        }

        window.addNewPhotoSlot = function() {
            window.photoCount++;
            const container = document.getElementById('photo-slots-container');
            const div = document.createElement('div');
            div.className = "photo-card border-2 border-dashed border-emerald-200 rounded-xl p-4 bg-gray-50 flex flex-col items-center relative transition-all";
            div.innerHTML = `
                
                <div class="w-full h-[250px] bg-white border rounded shadow-inner flex items-center justify-center overflow-hidden mb-4 relative group">
                    <img id="p-img-${window.photoCount}" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='260'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' font-family='sans-serif' font-size='18' text-anchor='middle' fill='%239ca3af' dy='.3em'%3EClick to Upload Image%3C/text%3E%3C/svg%3E" class="h-full object-contain cursor-pointer w-full photo-input-trigger" onclick="document.getElementById('p-input-${window.photoCount}').click()">
                    <input type="file" id="p-input-${window.photoCount}" class="hidden photo-input-file" onchange="previewProd(this, 'p-img-${window.photoCount}')">
                </div>
                <div class="w-full grid grid-cols-2 gap-4">
                    <div class="border-b border-gray-300">
                        <label class="text-[10px] text-gray-500 block">ໝາຍເລກລາຍການ (Item ID):</label>
                        <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-id" placeholder="ລະບຸ..."></div>
                    </div>
                    <div class="border-b border-gray-300">
                        <label class="text-[10px] text-gray-500 block">ຊື່ສິນຄ້າ (Product Name):</label>
                        <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-name" placeholder="ລະບຸ..."></div>
                    </div>
                </div>
                <button onclick="removePhotoSlot(this)" class="no-print photo-del-btn mt-3 w-full bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all">
                    <i class="fa-solid fa-box-archive"></i> ລຶບຊ່ອງຮູບພາບນີ້
                </button>
            `;
            container.appendChild(div);
        }

        window.removePhotoSlot = function(button) {
            const card = button.closest('.photo-card');
            const container = document.getElementById('photo-slots-container');
            if (container.children.length > 1) {
                card.remove();
            } else {
                setStatus('error', 'ຕ້ອງມີຢ່າງໜ້ອຍ 1 ຮູບພາບແນບ');
                setTimeout(() => setStatus('idle', 'ຍັງບໍ່ໄດ້ບັນທຶກ'), 2000);
            }
        }

        window.uploadSignature = function(input, imgId, controlsId) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('image', input.files[0]);
                fetch('upload_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const img = document.getElementById(imgId);
                            img.src = result.path;
                            img.classList.remove('hidden');
                            document.getElementById(controlsId).classList.remove('hidden');
                        } else {
                            showAlert('error', 'ອັບໂຫລດລາຍເຊັນບໍ່ສຳເລັດ', result.message);
                        }
                    })
                    .catch(err => showAlert('error', 'ອັບໂຫລດລາຍເຊັນບໍ່ສຳເລັດ', err.message));
            }
        }

        window.resizeSig = function(imgId, delta) {
            const img = document.getElementById(imgId);
            let currentWidth = parseInt(img.style.width) || 100;
            currentWidth = Math.max(50, Math.min(200, currentWidth + delta));
            img.style.width = currentWidth + 'px';
        }

        window.clearSignature = function(imgId, inputId, controlsId) {
            const img = document.getElementById(imgId);
            img.src = '';
            img.classList.add('hidden');
            document.getElementById(inputId).value = '';
            document.getElementById(controlsId).classList.add('hidden');
        }

        window.PR_LIST = [];

        window.loadPrList = async function(keepValue) {
            const input = document.getElementById('pr-no');
            if (!input) return;
            try {
                const res = await fetch('get_pr_data.php?action=list');
                const result = await res.json();
                if (result.status === 'success' && Array.isArray(result.data)) {
                    window.PR_LIST = result.data;
                } else {
                    window.PR_LIST = [];
                    console.error('loadPrList error:', result.message);
                }
            } catch (err) {
                window.PR_LIST = [];
                console.error('loadPrList fetch error:', err);
            }

            if (keepValue) {
                input.value = keepValue;
                if (!window.PR_LIST.includes(keepValue)) {
                    window.PR_LIST.push(keepValue);
                }
            }
        }

        window.renderPrDropdown = function(filterText) {
            const dropdown = document.getElementById('pr-dropdown');
            if (!dropdown) return;
            const q = (filterText || '').trim().toLowerCase();
            const filtered = q ?
                window.PR_LIST.filter(pr => pr.toLowerCase().includes(q)) :
                window.PR_LIST;

            if (filtered.length === 0) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">-- ບໍ່ພົບ PR --</div>';
            } else {
                dropdown.innerHTML = filtered.slice(0, 50).map(pr =>
                    `<div class="px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-800 cursor-pointer border-b border-gray-100 last:border-b-0" onmousedown="selectPr('${pr.replace(/'/g, "\\'")}')">${escapeHtml(pr)}</div>`
                ).join('');
            }
            dropdown.classList.remove('hidden');
        }

        window.onPrInput = function() {
            renderPrDropdown(document.getElementById('pr-no').value);
        }

        window.hidePrDropdown = function() {
            const dropdown = document.getElementById('pr-dropdown');
            if (dropdown) dropdown.classList.add('hidden');
        }

        window.selectPr = function(pr) {
            const input = document.getElementById('pr-no');
            input.value = pr;
            hidePrDropdown();
            onPrSelected();
        }

        window.setPrNoSelectValue = function(val) {
            const input = document.getElementById('pr-no');
            if (!input) return;
            input.value = val || '';
            if (val && !window.PR_LIST.includes(val)) {
                window.PR_LIST.push(val);
            }
        }

        // ---- ດຶງ "ເລກທີ່ແຜນ" (planing_number) ຈາກຕາຕະລາງ request ຕາມ PR ທີ່ເລືອກ ----
        // ອັບເດດ: *ຄືນຄ່າ* (return) ເລກແຜນ ແທນທີ່ຈະຂຽນທັບ header ໂດຍກົງ,
        // ເພື່ອບໍ່ໃຫ້ໄປລຶບ/ທັບເລກແຜນຂອງ PR ອື່ນທີ່ຖືກເລືອກກ່ອນໜ້ານີ້ໃນຕາຕະລາງ
        window.fetchPlaningNumber = async function(prNo) {
            if (!prNo) return '';
            try {
                const res = await fetch('get_planing_number.php?pr=' + encodeURIComponent(prNo));
                const result = await res.json();
                if (result.status === 'success' && result.planing_number) {
                    return result.planing_number;
                }
            } catch (err) {
                console.error('fetchPlaningNumber error:', err);
            }
            return '';
        }

        window.onPrSelected = async function() {
            const input = document.getElementById('pr-no');
            const prNo = input ? input.value.trim() : '';
            if (!prNo) return;

            // ດຶງເລກແຜນຂອງ PR ນີ້ໂດຍສະເພາະ (ບໍ່ໄປແຕະຄ່າຂອງ PR ອື່ນທີ່ຢູ່ໃນຕາຕະລາງແລ້ວ)
            const planNo = await fetchPlaningNumber(prNo);

            try {
                const res = await fetch('get_pr_data.php?action=items&pr=' + encodeURIComponent(prNo));
                const result = await res.json();
                if (result.status !== 'success') {
                    showAlert('error', 'ດຶງຂໍ້ມູນ PR ບໍ່ສຳເລັດ', result.message || '');
                    return;
                }
                if (!Array.isArray(result.data) || result.data.length === 0) {
                    showAlert('info', 'ບໍ່ພົບລາຍການ', 'ບໍ່ພົບລາຍການສິນຄ້າສຳລັບ PR ເລກທີ ' + prNo);
                    return;
                }
                addRowsFromPrItems(prNo, result.data, planNo);
                updateTrackPlanDisplay();
            } catch (err) {
                showAlert('error', 'ເຊື່ອມຕໍ່ບໍ່ໄດ້', 'ບໍ່ສາມາດດຶງຂໍ້ມູນ PR ໄດ້: ' + err.message);
            }
        }

        function addRowsFromPrItems(prNo, items, planNo) {
            const tbody = document.getElementById('item-table-body');

            if (tbody.rows.length === 1) {
                const firstInputs = tbody.rows[0].querySelectorAll('.cell-input');
                const isEmpty = Array.from(firstInputs).every(el => !el.innerText.trim());
                if (isEmpty) {
                    tbody.innerHTML = '';
                }
            }

            items.forEach(it => {
                const newIndex = tbody.rows.length + 1;
                const tr = document.createElement('tr');
                // ★ ຜູກ PR ແລະ ເລກແຜນ ໄວ້ກັບແຖວນີ້ໂດຍສະເພາະ (per-row), ບໍ່ໃຫ້ຖືກຂຽນທັບໂດຍ PR ອື່ນທີ່ເລືອກຕໍ່ມາ
                tr.dataset.pr = prNo;
                tr.dataset.plan = planNo || '';
                tr.innerHTML = `
                    <td class="text-center font-bold text-gray-700">${newIndex}</td>
                    <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ...">${escapeHtml(prNo)}</div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ...">${escapeHtml(it.Item_code)}</div></td>
                    <td><div contenteditable="true" class="cell-input font-semibold" placeholder="ຊື່ສິນຄ້າ...">${escapeHtml(it.Items)}</div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="...">${escapeHtml(it.Size)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-center" placeholder="0">${escapeHtml(it.Qty)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-center" placeholder="...">${escapeHtml(it.UnitLabel)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-right" placeholder="0"></div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                    <td class="text-center no-print table-col-delete"><button onclick="removeTableRow(this)" class="text-red-500 hover:text-red-700 font-bold"><i class="fa-solid fa-box-archive"></i></button></td>
                `;
                tbody.appendChild(tr);
            });
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.innerText = str == null ? '' : String(str);
            return div.innerHTML;
        }

        function setTextIfExists(id, val) {
            const el = document.getElementById(id);
            if (el) el.innerText = val || '';
        }

        function loadEditData() {
            if (!window.EDIT_DATA) {
                loadPrList(null);
                return;
            }
            const d = window.EDIT_DATA;
            const h = d.header;

            document.getElementById('doc-no').innerText = d.docNo;
            setTextIfExists('doc-date', h.DocDate);
            document.getElementById('From').value = h.From || '';
            document.getElementById('Factory_To').value = h.FactoryTo || '';

            const prNoInput = document.getElementById('pr-no');
            if (prNoInput) {
                let displayPr = h.PrNo || '';
                try {
                    const itemsArr = Array.isArray(d.items) ? d.items : [];
                    const distinctPrs = Array.from(new Set(
                        itemsArr.map(it => (it && it.OA ? String(it.OA) : '').trim()).filter(Boolean)
                    ));
                    if (distinctPrs.length > 0) displayPr = distinctPrs.join(', ');
                } catch (e) {
                    console.error('ຄິດໄລ່ລາຍການ PR ຜິດພາດ:', e);
                }
                loadPrList(null).then(function() {
                    prNoInput.value = displayPr;
                });
                prNoInput.value = displayPr;
            }
            setTextIfExists('carrier', h.Carrier);
            setTextIfExists('carrier-phone', h.CarrierPh);
            setTextIfExists('total-box', h.TotalBox);
            setTextIfExists('sender-name', h.SenderName);
            setTextIfExists('sender-dept', h.SenderDept);
            setTextIfExists('sender-phone', h.SenderPhone);
            setTextIfExists('total-cost', h.TotalCost);
            setTextIfExists('sig-sender-name', h.SigSender);
            setTextIfExists('sig-carrier-name', h.SigDriver);

            if (h.SigSenderImg) {
                const img = document.getElementById('sig-img-sender');
                img.src = h.SigSenderImg;
                img.classList.remove('hidden');
                document.getElementById('sig-controls-sender').classList.remove('hidden');
            }
            if (h.SigDriverImg) {
                const img = document.getElementById('sig-img-carrier');
                img.src = h.SigDriverImg;
                img.classList.remove('hidden');
                document.getElementById('sig-controls-carrier').classList.remove('hidden');
            }

            const tbody = document.getElementById('item-table-body');
            tbody.innerHTML = '';
            d.items.forEach((it, idx) => {
                const tr = document.createElement('tr');
                // ★ ຜູກ PR ແລະ ເລກແຜນຂອງແຕ່ລະລາຍການ (ດຶງມາຈາກຖານຂໍ້ມູນ, ບັນທຶກໄວ້ຕໍ່ແຖວ)
                tr.dataset.pr = it.OA || h.PrNo || '';
                tr.dataset.plan = it.TrackPlan || '';
                tr.innerHTML = `
                    <td class="text-center font-bold text-gray-700">${idx + 1}</td>
                    <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ...">${escapeHtml(it.OA || h.PrNo)}</div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="ລະບຸ...">${escapeHtml(it.BarCode)}</div></td>
                    <td><div contenteditable="true" class="cell-input font-semibold" placeholder="ຊື່ສິນຄ້າ...">${escapeHtml(it.ItemName)}</div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="...">${escapeHtml(it.Size)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-center" placeholder="0">${escapeHtml(it.Qty)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-center" placeholder="...">${escapeHtml(it.Unit)}</div></td>
                    <td><div contenteditable="true" class="cell-input text-right" placeholder="0">${escapeHtml(it.Weight)}</div></td>
                    <td><div contenteditable="true" class="cell-input" placeholder="..."></div></td>
                    <td class="text-center no-print table-col-delete"><button onclick="removeTableRow(this)" class="text-red-500 hover:text-red-700 font-bold"><i class="fa-solid fa-box-archive"></i></button></td>
                `;
                tbody.appendChild(tr);
            });

            // ສະແດງເລກແຜນລວມ (union ຂອງທຸກແຖວ) ຢູ່ຊ່ອງ header ແທນທີ່ຈະໃຊ້ h.TrackPlan ຄ່າດຽວ
            updateTrackPlanDisplay();

            const withPhoto = d.items.filter(it => it.ItemPhoto);
            if (withPhoto.length > 0) {
                const container = document.getElementById('photo-slots-container');
                container.innerHTML = '';
                window.photoCount = 0;
                withPhoto.forEach(it => {
                    window.photoCount++;
                    const div = document.createElement('div');
                    div.className = "photo-card border-2 border-dashed border-emerald-200 rounded-xl p-4 bg-gray-50 flex flex-col items-center relative";
                    div.innerHTML = `
                        <div class="w-full h-[250px] bg-white border rounded shadow-inner flex items-center justify-center overflow-hidden mb-4 relative group">
                            <img id="p-img-${window.photoCount}" src="${escapeHtml(it.ItemPhoto)}" class="h-full object-contain cursor-pointer w-full photo-input-trigger" onclick="document.getElementById('p-input-${window.photoCount}').click()">
                            <input type="file" id="p-input-${window.photoCount}" class="hidden photo-input-file" onchange="previewProd(this, 'p-img-${window.photoCount}')">
                        </div>
                        <div class="w-full grid grid-cols-2 gap-4">
                            <div class="border-b border-gray-300">
                                <label class="text-[10px] text-gray-500 block">ໝາຍເລກລາຍການ (Item ID):</label>
                                <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-id" placeholder="ລະບຸ..."></div>
                            </div>
                            <div class="border-b border-gray-300">
                                <label class="text-[10px] text-gray-500 block">ຊື່ສິນຄ້າ (Product Name):</label>
                                <div contenteditable="true" class="w-full font-bold outline-none bg-transparent text-sm min-h-[1.5em] photo-item-name">${escapeHtml(it.ItemName)}</div>
                            </div>
                        </div>
                        <button onclick="removePhotoSlot(this)" class="no-print photo-del-btn mt-3 w-full bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-md transition-all">
                            <i class="fa-solid fa-box-archive"></i> ລຶບຊ່ອງຮູບພາບນີ້
                        </button>
                    `;
                    container.appendChild(div);
                });
            }
        }

        loadEditData();
    </script>
</body>

</html>