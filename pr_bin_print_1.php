<?php
// =========================================================
// pr_bin_print.php - Xprinter XP-480B - REDESIGNED v3
// - ໜ້າປ້າຍຕິດກ່ອງ 4x6in ຄືຮູບຕົວຢ່າງ
// - ແຖວຂໍ້ມູນ: ວັນທີ, PR, ຊື່ສິນຄ້າ, ຂະໜາດ, ຈຳນວນ
// - QR Code ໃຫຍ່ກາງ + SCAN TO RECEIVE
// - ກອບມົນ (rounded) ສອງຊັ້ນ
// =========================================================

@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

$myFactory = isset($_SESSION["factory"]) ? trim($_SESSION["factory"]) : '';

require_once __DIR__ . '/conn.php';
$doc_no_GR = isset($_GET['doc_no_GR']) ? trim($_GET['doc_no_GR']) : '';
$docprint  = isset($_GET['doc_print'])  ? trim($_GET['doc_print'])  : '';

if ($docprint === '') {
    die("ບໍ່ພົບເລກທີ PR");
}

if ($doc_no_GR !== '') {
    $sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
                   Name_Sender, Department_HQ, Number_Nam_Sender,
                   Name_Receiver, Department_Receiver, Number_Nam_Receiver,
                   OA, BarCode, Item, Size, QTY, Unit, Weight, Item_Photo, Total_Cost,
                   Sig_Sender, Sig_Driver, Sig_Receiver,
                   Sig_Sender_Img, Sig_Driver_Img, Sig_Receiver_Img, Doc_Date
            FROM tacking WHERE OA = ? AND Number_Bin = ? ORDER BY ID ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error); }
    $stmt->bind_param("ss", $docprint, $doc_no_GR);
} else {
    $sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
                   Name_Sender, Department_HQ, Number_Nam_Sender,
                   Name_Receiver, Department_Receiver, Number_Nam_Receiver,
                   OA, BarCode, Item, Size, QTY, Unit, Weight, Item_Photo, Total_Cost,
                   Sig_Sender, Sig_Driver, Sig_Receiver,
                   Sig_Sender_Img, Sig_Driver_Img, Sig_Receiver_Img, Doc_Date
            FROM tacking WHERE OA = ? ORDER BY ID ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error); }
    $stmt->bind_param("s", $docprint);
}
$stmt->execute();
$stmt->bind_result(
    $r_ID, $r_From, $r_NumberBin, $r_Carrier, $r_FactoryTo, $r_CarrierPh,
    $r_TotalBox, $r_SenderName, $r_SenderDept, $r_SenderPhone,
    $r_ReceiverName, $r_ReceiverDept, $r_ReceiverPhone,
    $r_PrNo, $r_BarCode, $r_ItemName, $r_Size, $r_Qty, $r_Unit,
    $r_Weight, $r_ItemPhoto, $r_TotalCost,
    $r_SigSender, $r_SigDriver, $r_SigReceiver,
    $r_SigSenderImg, $r_SigDriverImg, $r_SigReceiverImg, $r_DocDate
);

$header = null;
$items  = array();
while ($stmt->fetch()) {
    if ($header === null) {
        $header = array(
            'From' => $r_From, 'Carrier' => $r_Carrier, 'FactoryTo' => $r_FactoryTo,
            'CarrierPh' => $r_CarrierPh, 'TotalBox' => $r_TotalBox,
            'SenderName' => $r_SenderName, 'SenderDept' => $r_SenderDept, 'SenderPhone' => $r_SenderPhone,
            'ReceiverName' => $r_ReceiverName, 'ReceiverDept' => $r_ReceiverDept, 'ReceiverPhone' => $r_ReceiverPhone,
            'PrNo' => $r_PrNo, 'TotalCost' => $r_TotalCost,
            'SigSender' => $r_SigSender, 'SigDriver' => $r_SigDriver, 'SigReceiver' => $r_SigReceiver,
            'SigSenderImg' => $r_SigSenderImg, 'SigDriverImg' => $r_SigDriverImg, 'SigReceiverImg' => $r_SigReceiverImg,
            'DocDate' => $r_DocDate
        );
    }
    $items[] = array(
        'PrNo' => $r_PrNo, 'BarCode' => $r_BarCode, 'ItemName' => $r_ItemName,
        'Size' => $r_Size, 'Qty' => $r_Qty, 'Unit' => $r_Unit, 'Weight' => $r_Weight,
        'ItemPhoto' => $r_ItemPhoto
    );
}
$stmt->close();

// ---- QR link ----
$qrLink = '';
$qrSql  = "SELECT link FROM qr_links WHERE title = ? LIMIT 1";
$qrStmt = $conn->prepare($qrSql);
if ($qrStmt) {
    $qrStmt->bind_param("s", $_SESSION["Namepro"]);
    $qrStmt->execute();
    $qrStmt->bind_result($qrLink);
    $qrStmt->fetch();
    $qrStmt->close();
}

$qrTarget = '';
if (!empty($qrLink)) {
    $parts  = parse_url($qrLink);
    $scheme = $parts['scheme'] ?? 'http';
    $host   = $parts['host']   ?? '';
    $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
    if ($host !== '') {
        $origin   = $scheme . '://' . $host . $port;
        $qrTarget = $origin . '/receive_tacking.php?doc_no=' . urlencode($doc_no_GR);
    }
}

$conn->close();

if ($header === null) {
    die("ບໍ່ພົບເອກະສານ PR ເລກທີ " . htmlspecialchars($docprint, ENT_QUOTES, 'UTF-8'));
}

$isOwnFactory = ($myFactory !== '' && strcasecmp($header['FactoryTo'] ?? '', $myFactory) === 0);
$isHqViewer   = ($myFactory !== '' && strcasecmp($myFactory, 'HQ') === 0);

if (!$isOwnFactory && !$isHqViewer) {
    die("ທ່ານບໍ່ມີສິດເຂົ້າເບິ່ງເອກະສານນີ້ (ບໍ່ແມ່ນຂອງໂຮງງານທ່ານ)");
}

$alreadyReceived = ($header['SigReceiver'] !== '' && $header['SigReceiver'] !== null);
$canReceive      = $isOwnFactory && !$alreadyReceived;

$printQueue = array();
foreach ($items as $idx => $it) {
    $printQueue[] = array('item' => $it, 'row' => $idx + 1, 'qty' => count($items));
}
if (empty($printQueue)) {
    $printQueue[] = array('item' => null, 'row' => 1, 'qty' => 1);
}

function h($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="image/favicons.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ປ້າຍຕິດກ່ອງ - <?php echo h($docprint); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="qrcode.min.js"></script>

    <style>
        @page {
            size: 4in 6in;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background: #e5e7eb;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* ===== LABEL PAGE ===== */
        .label-page {
            width: 4in;
            min-height: 6in;
            max-width: 4.25in;
            padding: 0;
            margin: 14px auto;
            background: white;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
            page-break-inside: avoid;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
        }

        .label-frame {
            position: relative;
            width: calc(100% - 0.28in);
            height: calc(100% - 0.28in);
            margin: 0.14in;
            border: 2.5px solid #064e3b;
            border-radius: 20px;
            padding: 0.16in 0.20in;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            background: #ffffff;
        }

        .label-frame::before {
            content: "";
            position: absolute;
            inset: 5px;
            border: 0.8px solid #064e3b;
            border-radius: 14px;
            pointer-events: none;
        }

        .label-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 2;
        }

        /* ===== TOP BAR ===== */
        .label-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .label-no-badge {
            display: inline-block;
            background: #064e3b;
            color: #ffffff;
            padding: 3px 12px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        /* ===== COMPANY HEADER ===== */
        .label-company-header {
            text-align: center;
            margin: 2px 0 2px 0;
        }

        .label-company-img {
            margin-bottom: 4px;
        }

        .label-company-img img {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
        }

        .label-company-name {
            font-size: 44px;
            font-weight: 900;
            color: #064e3b;
            letter-spacing: 2px;
            line-height: 1;
            margin: 0;
        }

        .label-company-tagline {
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            letter-spacing: 4px;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .label-divider {
            height: 0;
            border-top: 1.5px dashed #064e3b;
            margin: 8px 0 6px 0;
        }

        /* ===== INFO ROWS (key-value) ===== */
        .label-info-list {
            margin: 4px 0 6px 0;
        }

        .label-info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 5px 0;
            border-bottom: 1px dotted #9ca3af;
        }

        .label-info-row:last-child {
            border-bottom: none;
        }

        .label-info-label {
            font-size: 11px;
            font-weight: 700;
            color: #4b5563;
            flex-shrink: 0;
            margin-right: 10px;
        }

        .label-info-value {
            font-size: 12px;
            font-weight: 800;
            color: #111827;
            text-align: right;
            flex: 1;
            word-break: break-word;
        }

        /* ===== QR CODE (main, big) ===== */
        .label-qr-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 8px 0 6px 0;
        }

        .label-qr-main-box {
            padding: 6px;
            background: white;
            border: 1.5px solid #064e3b;
            border-radius: 8px;
            display: inline-block;
        }

        .label-qr-main canvas,
        .label-qr-main img {
            width: 130px !important;
            height: 130px !important;
            display: block;
        }

        .label-qr-caption {
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            letter-spacing: 1px;
            margin-top: 4px;
            text-transform: uppercase;
        }

        /* ===== FOOTER NOTE (yellow box) ===== */
        .label-footer-note {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 10px;
            color: #92400e;
            text-align: center;
            font-weight: 700;
            margin: 6px 0;
            line-height: 1.4;
        }

        /* ===== BOTTOM ROW ===== */
        .label-bottom {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 10px;
            padding-top: 6px;
        }

        .label-from-to {
            flex: 1;
            font-size: 10px;
            line-height: 1.4;
        }

        .label-from-to .ft-line {
            margin-bottom: 3px;
        }

        .label-from-to .ft-line:last-child {
            margin-bottom: 0;
        }

        .label-from-to .ft-label {
            font-weight: 800;
            color: #064e3b;
            font-size: 10px;
        }

        .label-from-to .ft-val {
            font-weight: 700;
            color: #1f2937;
            font-size: 11px;
        }

        .label-pr-bottom {
            flex-shrink: 0;
            text-align: right;
            font-size: 10px;
            line-height: 1.3;
        }

        .label-pr-bottom .ft-label {
            font-weight: 800;
            color: #064e3b;
            font-size: 10px;
        }

        .label-pr-bottom .ft-val {
            font-weight: 900;
            color: #064e3b;
            font-size: 14px;
        }

        /* ===== TOOLBAR ===== */
        #toolbar {
            max-width: 4.25in;
            margin: 6px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            padding: 4px;
        }

        .toolbar-btn {
            padding: 6px 14px;
            border-radius: 7px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: white;
            text-decoration: none;
            font-size: 11px !important;
            transition: filter .15s;
        }
        .toolbar-btn:hover { filter: brightness(1.1); }
        .toolbar-btn-gray  { background: #4b5563; }
        .toolbar-btn-green { background: #065f46; }
        .toolbar-btn-blue  { background: #1d4ed8; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 10px !important;
            font-weight: 700;
            border: 1px solid;
        }
        .status-badge-success { background: #ecfdf5; border-color: #10b981; color: #065f46; }
        .status-badge-warning { background: #fffbeb; border-color: #f59e0b; color: #92400e; }

        /* ===== PRINT ===== */
        @media print {
            body { background: none !important; margin: 0 !important; padding: 0 !important; }
            .label-page {
                margin: 0 !important;
                width: 4in !important;
                min-height: 6in !important;
                box-shadow: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body>

    <!-- ===== TOOLBAR ===== -->
    <div id="toolbar" class="no-print">
        <a href="inbox_tacking.php" class="toolbar-btn toolbar-btn-gray">
            <i class="fa-solid fa-arrow-left"></i> ກັບຄືນ
        </a>
        <?php if ($canReceive) { ?>
            <button onClick="submitReceive()" class="toolbar-btn toolbar-btn-green">
                <i class="fa-solid fa-floppy-disk"></i> ບັນທຶກຮັບ
            </button>
        <?php } ?>
        <button onClick="window.print()" class="toolbar-btn toolbar-btn-blue">
            <i class="fa-solid fa-print"></i> ພິມປ້າຍ
        </button>
        <?php if ($alreadyReceived) { ?>
            <span class="status-badge status-badge-success">
                <i class="fa-solid fa-check"></i> ຮັບແລ້ວ
            </span>
        <?php } elseif ($isHqViewer && !$isOwnFactory) { ?>
            <span class="status-badge status-badge-warning">
                <i class="fa-solid fa-clock"></i> ລໍຖ້າຮັບ
            </span>
        <?php } ?>
    </div>

    <!-- ===== LABELS ===== -->
    <?php foreach ($printQueue as $pageIndex => $page): $globalPage = $pageIndex + 1; ?>
    <div class="label-page" id="label<?php echo $globalPage; ?>">
        <div class="label-frame">
            <div class="label-content">

                <!-- TOP BAR -->
                <div class="label-top">
                    <div></div>
                    <div>
                        <span class="label-no-badge">No: <?php echo str_pad($page['row'], 2, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>

                <!-- COMPANY HEADER with Image -->
                <div class="label-company-header">
                    <div class="label-company-img">
                        <img src="image.png" alt="Company Logo" style="max-width:100%;max-height:120px;display:block;margin:0 auto;">
                    </div>
                    <h1 class="label-company-name"><?php echo h($header['FactoryTo'] ?: 'MMN'); ?></h1>
                    <div class="label-company-tagline">INDUSTRY CO.,LTD</div>
                </div>

                <div class="label-divider"></div>

                <!-- INFO ROWS -->
                <div class="label-info-list">
                    <div class="label-info-row">
                        <span class="label-info-label">ວັນທີ / LotDate:</span>
                        <span class="label-info-value"><?php echo h($header['DocDate'] ?: '-'); ?></span>
                    </div>
                    <div class="label-info-row">
                        <span class="label-info-label">ລະຫັດ PR:</span>
                        <span class="label-info-value"><?php echo h($page['item']['PrNo'] ?? $header['PrNo']); ?></span>
                    </div>
                    <div class="label-info-row">
                        <span class="label-info-label">ຊື່ສິນຄ້າ:</span>
                        <span class="label-info-value"><?php echo h($page['item']['ItemName'] ?? '-'); ?></span>
                    </div>
                    <div class="label-info-row">
                        <span class="label-info-label">ລຸ່ນ / ຂະໜາດ:</span>
                        <span class="label-info-value"><?php echo h($page['item']['Size'] ?? '-'); ?></span>
                    </div>
                    <div class="label-info-row">
                        <span class="label-info-label">ຈຳນວນ / Quantity:</span>
                        <span class="label-info-value"><?php echo h($page['item']['Qty'] ?? '-'); ?></span>
                    </div>
                </div>

                <!-- QR CODE (main, big) -->
                <div class="label-qr-main">
                    <div class="label-qr-main-box">
                        <?php if (!empty($qrTarget)): ?>
                        <div class="labelQr" id="labelQr<?php echo $globalPage; ?>"></div>
                        <?php else: ?>
                        <div style="width:130px;height:130px;display:flex;align-items:center;justify-content:center;font-size:9px;color:#9ca3af;text-align:center;">QR: no link</div>
                        <?php endif; ?>
                    </div>
                    <div class="label-qr-caption">SCAN TO RECEIVE</div>
                </div>

                <!-- FOOTER NOTE -->
                <div class="label-footer-note">
                    ບິດຊອງ ໃໝ່ — ກ່ອນໃຊ້ ກະລຸນາກວດສອບ ແລະ ທົດລອງກ່ອນ
                </div>

                <!-- BOTTOM -->
                <div class="label-bottom">
                    <div class="label-from-to">
                        <div class="ft-line">
                            <span class="ft-label">ຈາກ / From:</span>
                            <span class="ft-val"><?php echo h($header['From'] ?: '-'); ?></span>
                        </div>
                        <div class="ft-line">
                            <span class="ft-label">ໄປ / To:</span>