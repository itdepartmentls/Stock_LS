<?php
// =========================================================
// pr_bin_print.php - Xprinter XP-480B - REDESIGNED v3
// - ตัวหนังสือใหญ่ขึ้น 2 ເທົ່າ
// - ກອບມົນ (rounded) ແບບໃນຮູບຕົວຢ່າງ
// - ລາຍຊື່ສິນຄ້າ ເປັນຕາຕະລາງ ແຕ່ລະໃບສະແດງ 1 ລາຍການ
// - QR Code ເປັນຕົວຫຼັກກາງ (ລົບ Barcode ອອກແລ້ວ)
// - ແກ້ບັກ layout ເກົ່າ (undefined $it, tag ປິດບໍ່ຄົບ)
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

if ($docprint === '' && $doc_no_GR === '') {
    die("ບໍ່ພົບເລກທີ່ເອກະສານ ຫຼື ເລກທີ PR");
}

if ($doc_no_GR !== '') {
    // ---- ພິມທຸກລາຍການ (Item) ໃນເອກະສານນີ້ (Number_Bin) ໃບຕໍ່ໃບ ----
    // ບໍ່ຈຳກັດສະເພາະ PR ດຽວອີກຕໍ່ໄປ ເພາະ 1 ເອກະສານອາດມີຫຼາຍລາຍການທີ່ຄົນລະ PR ກັນ
    // (ແຕ່ລະລາຍການຍັງສະແດງ PR ຂອງຕົນເອງຢູ່ໃນປ້າຍ ຜ່ານ $page['item']['PrNo'])
    $sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
                   Name_Sender, Department_HQ, Number_Nam_Sender,
                   Name_Receiver, Department_Receiver, Number_Nam_Receiver,
                   OA, BarCode, Item, Size, QTY, Unit, Weight, Item_Photo, Total_Cost,
                   Sig_Sender, Sig_Driver, Sig_Receiver,
                   Sig_Sender_Img, Sig_Driver_Img, Sig_Receiver_Img, Doc_Date
            FROM tacking WHERE Number_Bin = ? ORDER BY ID ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error); }
    $stmt->bind_param("s", $doc_no_GR);
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
            size: 60mm 80mm;
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
            width: 60mm;
            height: 80mm;
            max-width: 60mm;
            max-height: 80mm;
            padding: 0;
            margin: 10px auto;
            background: white;
            position: relative;
            box-sizing: border-box;
            page-break-after: always;
            page-break-inside: avoid;
            break-inside: avoid;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,0.18);
        }

        .label-frame {
            position: relative;
            width: calc(100% - 1.6mm);
            height: calc(100% - 1.6mm);
            margin: 0.8mm;
            border: 1.5px solid #064e3b;
            border-radius: 8px;
            padding: 1.3mm 2mm;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            background: #ffffff;
            overflow: hidden;
        }

        .label-frame::before {
            content: "";
            position: absolute;
            inset: 2px;
            border: 0.6px solid #064e3b;
            border-radius: 6px;
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
            margin-bottom: 2px;
        }

        .label-no-badge {
            display: inline-block;
            background: #064e3b;
            color: #ffffff;
            padding: 1.5px 6px;
            border-radius: 8px;
            font-size: 6.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .label-date-mini {
            font-size: 6px;
            color: #6b7280;
            font-weight: 700;
        }

        /* ===== COMPANY HEADER ===== */
        .label-company-header {
            text-align: center;
            margin: 1px 0 0 0;
        }

        .label-company-name {
            font-size: 17px;
            font-weight: 900;
            color: #064e3b;
            letter-spacing: 0.5px;
            line-height: 1;
            margin: 0;
        }

        .label-company-tagline {
            font-size: 6px;
            font-weight: 700;
            color: #9ca3af;
            letter-spacing: 2px;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .label-divider {
            height: 0;
            border-top: 1px dashed #064e3b;
            margin: 2px 0;
        }

        /* (date/PR ຍ້າຍໄປລວມຢູ່ໃນ .label-rows ຂ້າງລຸ່ມ ແທນການໃຊ້ກ່ອງແຍກ) */

        /* ===== INFO ROWS (label : value list, like reference image — no table) ===== */
        .label-rows {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin: 2px 0 3px 0;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 4px;
            border-bottom: 0.5px dotted #d1d5db;
            padding-bottom: 1.5px;
        }

        .label-row:last-child {
            border-bottom: none;
        }

        .label-row .row-label {
            font-size: 6.5px;
            font-weight: 700;
            color: #4b5563;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .label-row .row-val {
            font-size: 8px;
            font-weight: 800;
            color: #111827;
            text-align: right;
            word-break: break-word;
        }

        /* ===== QR CODE (main, big) ===== */
        .label-qr-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 3px 0 2px 0;
        }

        .label-qr-main-box {
            padding: 3px;
            background: white;
            border: 1.2px solid #064e3b;
            border-radius: 6px;
            display: inline-block;
        }

        .label-qr-main canvas,
        .label-qr-main img {
            width: 76px !important;
            height: 76px !important;
            display: block;
        }

        .label-qr-caption {
            font-size: 5.5px;
            font-weight: 800;
            color: #0d9488;
            letter-spacing: 0.8px;
            margin-top: 1px;
            text-transform: uppercase;
        }

        /* ===== FOOTER NOTE ===== */
        .label-footer-note {
            background: #fef3c7;
            border: 0.75px solid #f59e0b;
            border-radius: 4px;
            padding: 2px 5px;
            font-size: 5.5px;
            color: #92400e;
            text-align: center;
            font-weight: 700;
            margin: 1.5px 0;
            line-height: 1.2;
        }

        /* ===== BOTTOM ROW: FROM/TO + QR ===== */
        .label-bottom {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 4px;
            padding-top: 1px;
        }

        .label-from-to {
            flex: 1;
            font-size: 5.5px;
            line-height: 1.15;
        }

        .label-from-to .ft-label {
            font-weight: 800;
            color: #064e3b;
            font-size: 5.5px;
            display: block;
            margin-top: 1px;
        }

        .label-from-to .ft-label:first-child { margin-top: 0; }

        .label-from-to .ft-val {
            font-weight: 700;
            color: #1f2937;
            font-size: 6px;
            display: block;
        }

        .label-bottom-right {
            flex-shrink: 0;
            text-align: right;
            font-size: 5.5px;
            line-height: 1.25;
            max-width: 22mm;
        }

        .label-bottom-right .ft-val {
            font-weight: 800;
            color: #064e3b;
            font-size: 6.5px;
            word-break: break-all;
        }

        /* (QR ເກົ່າທີ່ມຸມຖືກຍ້າຍໄປເປັນ QR ໃຫຍ່ກາງແລ້ວ — ລຶບ CSS ເກົ່າອອກ) */

        /* ===== TOOLBAR ===== */
        #toolbar {
            max-width: 62mm;
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

        /* ===== PRINT-SETUP NOTE (screen only) ===== */
        #printSetupNote {
            max-width: 62mm;
            margin: 0 auto 10px auto;
            background: #fffbeb;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 10px;
            color: #92400e;
            line-height: 1.5;
        }
        #printSetupNote .print-note-title {
            font-weight: 800;
            font-size: 10.5px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        #printSetupNote ul {
            margin: 0;
            padding-left: 16px;
        }
        #printSetupNote li { margin-bottom: 2px; }

        /* ===== PRINT ===== */
        @media print {
            body { background: none !important; margin: 0 !important; padding: 0 !important; }
            .label-page {
                margin: 0 !important;
                width: 60mm !important;
                height: 80mm !important;
                max-height: 80mm !important;
                overflow: hidden !important;
                box-shadow: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
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
        <button onClick="doPrint()" class="toolbar-btn toolbar-btn-blue">
            <i class="fa-solid fa-print"></i> ພິມປ້າຍ
        </button>
        <?php if ($alreadyReceived) { ?>
            <span class="status-badge status-badge-success">
                <i class="fa-solid fa-check"></i> ຮັບແລ້ວ
            </span>
        <?php } elseif ($isHqViewer && !$isOwnFactory) { ?>
            
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
                        <span class="row-label">No: <?php echo str_pad($page['row'], 2, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>

                <!-- COMPANY HEADER -->
                <div class="label-company-header">
                    <h1 class="label-company-name"><?php echo h($header['FactoryTo'] ?: 'MMN'); ?></h1>
                    <div class="label-company-tagline">www.lslao.com.la</div>
                </div>

                <div class="label-divider"></div>

                <!-- INFO ROWS (label : value list, like reference image) -->
                <div class="label-rows">
                    <div class="label-row">
                        <span class="row-label">ວັນທີ / LotDate:</span>
                        <span class="row-val"><?php echo h($header['DocDate'] ?: '-'); ?></span>
                    </div>
                    <div class="label-row">
                        <span class="row-label">ລະຫັດ PR:</span>
                        <span class="row-val"><?php echo h($page['item']['PrNo'] ?? $header['PrNo']); ?></span>
                    </div>
                    <div class="label-row">
                        <span class="row-label">ຊື່ສິນຄ້າ:</span>
                        <span class="row-val"><?php echo h($page['item']['ItemName'] ?? '-'); ?></span>
                    </div>
                    <div class="label-row">
                        <span class="row-label">ລຸ້ນ / ຂະໜາດ:</span>
                        <span class="row-val"><?php echo h($page['item']['Size'] ?? '-'); ?></span>
                    </div>
                    <div class="label-row">
                        <span class="row-label">ຈຳນວນ / Quantity:</span>
                        <span class="row-val"><?php
                            echo h($page['item']['Qty'] ?? '-');
                            if (!empty($page['item']['Unit'])) echo ' ' . h($page['item']['Unit']);
                        ?></span>
                    </div>
                </div>

                <!-- QR CODE (main, big) -->
                <div class="label-qr-main">
                    <div class="label-qr-main-box">
                        <?php if (!empty($qrTarget)): ?>
                        <div class="labelQr" id="labelQr<?php echo $globalPage; ?>"></div>
                        <?php else: ?>
                        <div style="width:76px;height:76px;display:flex;align-items:center;justify-content:center;font-size:6px;color:#9ca3af;text-align:center;">QR: no link</div>
                        <?php endif; ?>
                    </div>
                    <div class="label-qr-caption">Scan to receive</div>
                </div>

                <!-- FOOTER NOTE -->
              

                <!-- BOTTOM: FROM/TO only (QR ຍ້າຍໄປເປັນຕົວໃຫຍ່ກາງແລ້ວ) -->
                <div class="label-bottom">
                    <div class="label-from-to">
                        <span class="ft-label">ຈາກ / From:<?php echo h($header['From'] ?: '-'); ?></span>
                        
                        <span class="ft-label">ໄປ / To:<?php echo h($header['FactoryTo'] ?: '-'); ?></span>
                    </div>
                    <div class="label-bottom-right">
                        <span class="ft-label">PR:</span>
                        <span class="ft-val"><?php echo h($page['item']['PrNo'] ?? $header['PrNo']); ?></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script>
    function doPrint() {
        window.print();
    }
    (function () {
        // Render QR code (main, big)
        <?php if (!empty($qrTarget)): ?>
        document.querySelectorAll('.labelQr').forEach(function (el) {
            new QRCode(el, {
                text: <?php echo json_encode($qrTarget); ?>,
                width: 76,
                height: 76,
                correctLevel: QRCode.CorrectLevel.M
            });
        });
        <?php endif; ?>
    })();

    function submitReceive() {
        const nameEl  = document.getElementById('receiverName');
        const deptEl  = document.getElementById('receiverDept');
        const phoneEl = document.getElementById('receiverPhone');
        const name = nameEl ? nameEl.value.trim() : '';

        if (!name) {
            Swal.fire({
                icon: 'warning',
                title: 'ກະລຸນາປ້ອນຊື່ຜູ້ຮັບ',
                text: 'ຊື່-ນາມສະກຸນ ຜູ້ຮັບແມ່ນຈຳເປັນຕ້ອງປ້ອນ',
                confirmButtonText: 'ຕົກລົງ'
            });
            return;
        }

        Swal.fire({
            title: 'ຢືນຢັນການຮັບເຄື່ອງ',
            text: 'ທ່ານຕ້ອງການບັນທຶກການຮັບເຄື່ອງນີ້ ຫຼື ບໍ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#d33',
            confirmButtonText: 'ບັນທຶກ',
            cancelButtonText: 'ຍົກເລີກ'
        }).then((result) => {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'update_receiver.php';

            const fields = {
                doc_no_GR: <?php echo json_encode($doc_no_GR); ?>,
                receiver_name: name,
                receiver_dept: deptEl ? deptEl.value.trim() : '',
                receiver_phone: phoneEl ? phoneEl.value.trim() : '',
                receiver_sig: '/uploads/default_signature.png'
            };
            for (const [k, v] of Object.entries(fields)) {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = k; i.value = v;
                form.appendChild(i);
            }
            document.body.appendChild(form);
            form.submit();
        });
    }
    </script>

</body>
</html>