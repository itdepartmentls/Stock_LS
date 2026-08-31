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

require_once __DIR__ . '/../includes/conn.php';
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
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
    <script src="qrcode.min.js"></script>

    <link rel="stylesheet" href="css/pages/pr_bin_print.css">
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
