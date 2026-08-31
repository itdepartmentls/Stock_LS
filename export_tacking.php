<?php
// =========================================================
// export_tacking.php
// Export ຂໍ້ມູນລາຍງານການສົ່ງເຄື່ອງ (ຈາກຕາຕະລາງ tacking) ເປັນໄຟລ໌ Excel
// ໃຊ້ filter ດຽວກັນກັບ report_tacking.php (q, date_from, date_to, status)
// ຂຽນແບບເຂົ້າກັນໄດ້ກັບ PHP ເກົ່າ (ບໍ່ໃຊ້ closure, [], ??, get_result())
// =========================================================

@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

require_once 'conn.php';
/** @var mysqli $conn */

// ---- 1. ດຶງຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ (ຮຽງລ້າສຸດກ່ອນ) ----
$sql = "SELECT ID, `From`, Number_Bin, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
               Name_Sender, Department_HQ, Number_Nam_Sender,
               Name_Receiver, Department_Receiver, Number_Nam_Receiver,
               OA, BarCode, Item, Size, QTY, Unit, Weight, Price, Total_Cost,
               Sig_Sender, Sig_Driver, Sig_Receiver, Doc_Date
        FROM tacking ORDER BY ID DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error);
}
$stmt->execute();

$ID = $From = $NumberBin = $Carrier = $FactoryTo = $BoxToCarrier = null;
$TotalBox = $NameSender = $DepartmentHQ = $NumberNamSender = null;
$NameReceiver = $DepartmentReceiver = $NumberNamReceiver = null;
$OA = $BarCode = $Item = $Size = $QTY = $Unit = $Weight = null;
$Price = $TotalCost = $SigSender = $SigDriver = $SigReceiver = $DocDateCol = null;

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
    $DocDateCol
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
        "Doc_Date" => $DocDateCol
    );
}
$stmt->close();
$conn->close();

// ---- 2. ອ່ານຄ່າກັ່ນຕອງ (filter) ຈາກ GET - ດຽວກັນກັບ report_tacking.php ----
$q         = isset($_GET['q']) ? trim($_GET['q']) : '';
$dateFrom  = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo    = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$status    = isset($_GET['status']) ? trim($_GET['status']) : '';

function extractDateFromDocNo($docNo)
{
    if (preg_match('/GR-(\d{2})(\d{2})(\d{4})-/', $docNo, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1]; // YYYY-MM-DD
    }
    return '';
}

function containsSearch($haystack, $needle)
{
    if (function_exists('mb_stripos')) {
        return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
    }
    return stripos($haystack, $needle) !== false;
}

// ---- 3. ກັ່ນຕອງຂໍ້ມູນ (ຄົ້ນຫາ + ຊ່ວງວັນທີ່) - ດຽວກັນກັບ report_tacking.php ----
$filteredRows = array();
foreach ($allRows as $row) {
    $docDateRaw = (isset($row['Doc_Date']) && $row['Doc_Date'] !== '') ? $row['Doc_Date'] : '';
    if ($docDateRaw !== '' && preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $docDateRaw, $dm)) {
        $docDateSortable = $dm[3] . '-' . $dm[2] . '-' . $dm[1];
        $docDate = $docDateRaw;
    } else {
        $docDateSortable = extractDateFromDocNo($row['Number_Bin']);
        $docDate = $docDateSortable;
    }

    if ($q !== '') {
        $haystackParts = array(
            $row['Number_Bin'],
            $row['From'],
            $row['Carrier'],
            $row['Factory_To'],
            $row['Item'],
            $row['OA'],
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

function h($val)
{
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}

// ---- 4. ສ້າງຊື່ໄຟລ໌ ----
$fileName = 'ລາຍງານການສົ່ງເຄື່ອງ_' . date('Y-m-d_His') . '.xls';

// ---- 5. ສົ່ງ Header ໃຫ້ browser download ເປັນ Excel ----
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM ເພື່ອໃຫ້ Excel ອ່ານພາສາລາວ (UTF-8) ຖືກຕ້ອງ
echo "\xEF\xBB\xBF";
?>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #999999;
            padding: 4px 6px;
            font-family: 'Phetsarath OT', Tahoma, sans-serif;
            font-size: 12px;
            mso-number-format: "\@";
            /* ບັງຄັບໃຫ້ Excel ຖືທຸກຄໍລຳເປັນ Text (ບໍ່ຕັດເລກ 0 ໜ້າ ຫຼືປ່ຽນຮູບແບບວັນທີ) */
        }

        th {
            background-color: #064e3b;
            color: #ffffff;
            font-weight: bold;
        }

        .num {
            mso-number-format: "0";
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ເລກທີເອກະສານ</th>
                <th>ວັນທີ່</th>
                <th>ຕົ້ນທາງ</th>
                <th>ປາຍທາງ</th>
                <th>ຂົນສົ່ງໂດຍ</th>
                <th>ເບີໂທຄົນຂົນສົ່ງ</th>
                <th>PR</th>
                <th>Barcode</th>
                <th>ຊື່ສິນຄ້າ</th>
                <th>ຂະໜາດ</th>
                <th>ຈຳນວນ</th>
                <th>ໜ່ວຍ</th>
                <th>ນ້ຳໜັກ</th>
                <th>ລາຄາຂົນສົ່ງ</th>
                <th>ຜູ້ຝາກ</th>
                <th>ພະແນກຜູ້ຝາກ</th>
                <th>ເບີໂທຜູ້ຝາກ</th>
                <th>ຜູ້ຮັບ</th>
                <th>ພະແນກຜູ້ຮັບ</th>
                <th>ເບີໂທຜູ້ຮັບ</th>
                <th>ສະຖານະ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filteredRows as $row) { ?>
                <tr>
                    <td><?php echo h($row['ID']); ?></td>
                    <td><?php echo h($row['Number_Bin']); ?></td>
                    <td><?php echo h($row['DocDate']); ?></td>
                    <td><?php echo h($row['From']); ?></td>
                    <td><?php echo h($row['Factory_To']); ?></td>
                    <td><?php echo h($row['Carrier']); ?></td>
                    <td><?php echo h($row['BoxTo_Carrier']); ?></td>
                    <td><?php echo h($row['OA']); ?></td>
                    <td><?php echo h($row['BarCode']); ?></td>
                    <td><?php echo h($row['Item']); ?></td>
                    <td><?php echo h($row['Size']); ?></td>
                    <td class="num"><?php echo h($row['QTY']); ?></td>
                    <td><?php echo h($row['Unit']); ?></td>
                    <td class="num"><?php echo h($row['Weight']); ?></td>
                    <td class="num"><?php echo h($row['Total_Cost']); ?></td>
                    <td><?php echo h($row['Name_Sender']); ?></td>
                    <td><?php echo h($row['Department_HQ']); ?></td>
                    <td><?php echo h($row['Number_Nam_Sender']); ?></td>
                    <td><?php echo h($row['Name_Receiver']); ?></td>
                    <td><?php echo h($row['Department_Receiver']); ?></td>
                    <td><?php echo h($row['Number_Nam_Receiver']); ?></td>
                    <td><?php echo $row['IsReceived'] ? 'ຮັບແລ້ວ' : 'ລໍຖ້າຮັບ'; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>