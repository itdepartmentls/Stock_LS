<?php
// =========================================================
// save_tacking.php
// ຮັບຂໍ້ມູນຈາກຟອມ HTML (JSON) ແລ້ວບັນທຶກລົງຕາຕະລາງ tacking
// 1 ລາຍການສິນຄ້າ = 1 ແຖວ (header ຂໍ້ມູນຊ້ຳກັນທຸກແຖວ)
// ຮອງຮັບ path ຮູບພາບສິນຄ້າ ແລະ ຮູບລາຍເຊັນ (ອັບໂຫລດຜ່ານ upload_image.php ກ່ອນແລ້ວ)
// ຂຽນແບບເຂົ້າກັນໄດ້ກັບ PHP ເກົ່າ (ບໍ່ໃຊ້ closure, [], ??)
// ອັບເດດ: ເພີ່ມການບັນທຶກ Track_planing_number (ເລກທີ່ແຜນ)
// ອັບເດດ (ແກ້ bug): ບັນທຶກ "ເລກທີ່ແຜນ" ຕໍ່ແຖວ/ຕໍ່ PR (field 'plan' ຂອງແຕ່ລະ item)
//                    ແທນທີ່ຈະໃຊ້ຄ່າ header 'track-plan' ອັນດຽວຊ້ຳໃສ່ທຸກແຖວ
//                    ເພື່ອບໍ່ໃຫ້ຫຼາຍ PR ທີ່ບັນທຶກພ້ອມກັນ ຄ້າງເລກແຜນອັນດຽວກັນໝົດ
// =========================================================

header('Content-Type: application/json; charset=utf-8');

// ---- ຮັບປະກັນວ່າ response ຈະເປັນ JSON ສະເໝີ, ບໍ່ວ່າຈະເກີດ error/exception ໃດກໍ່ຕາມ
//      (ບໍ່ໃຫ້ PHP ພິມ HTML error ອອກມາປົນກັບ JSON ຈົນຝັ່ງ frontend parse ບໍ່ໄດ້) ----
error_reporting(E_ALL);
ini_set('display_errors', '0');

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(array("status" => "error", "message" => "ຂໍ້ຜິດພາດເຊີບເວີ: " . $e->getMessage()));
    exit;
});
set_error_handler(function ($errno, $errstr) {
    throw new ErrorException($errstr, 0, $errno);
});

require_once __DIR__ . '/conn.php';

if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF); // ຄືນຄ່າ false ຕອນ query ຜິດພາດ ແທນທີ່ຈະ throw exception
}

// ---- 1. ອ່ານ JSON ທີ່ສົ່ງມາຈາກ fetch() ----
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || !is_array($data)) {
    echo json_encode(array("status" => "error", "message" => "ຂໍ້ມູນທີ່ສົ່ງມາບໍ່ຖືກຕ້ອງ (Invalid JSON)"));
    exit;
}

function gv($arr, $key, $default)
{
    return isset($arr[$key]) ? $arr[$key] : $default;
}

// ---- 2. ໂໝດແກ້ໄຂ: ຖ້າມີ edit_doc_no ສົ່ງມາ, ໃຫ້ໃຊ້ເລກທີ່ເອກະສານເກົ່າ ແລະ ລຶບແຖວເກົ່າກ່ອນຂຽນໃໝ່ ----
$editDocNo = gv($data, 'edit_doc_no', '');

if ($editDocNo !== '') {
    $sqlCheck = "SELECT Sig_Receiver FROM tacking WHERE Number_Bin = ? LIMIT 1";
    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        echo json_encode(array("status" => "error", "message" => "ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error));
        exit;
    }
    $stmtCheck->bind_param("s", $editDocNo);
    $stmtCheck->execute();
    $stmtCheck->bind_result($existingSigReceiver);
    $found = $stmtCheck->fetch();
    $stmtCheck->close();

    if (!$found) {
        echo json_encode(array("status" => "error", "message" => "ບໍ່ພົບເອກະສານເລກທີ " . $editDocNo . " ທີ່ຈະແກ້ໄຂ"));
        exit;
    }
    if ($existingSigReceiver !== '' && $existingSigReceiver !== null) {
        echo json_encode(array("status" => "error", "message" => "ເອກະສານນີ້ຖືກຮັບໄປແລ້ວ, ແກ້ໄຂບໍ່ໄດ້"));
        exit;
    }

    $docNo = $editDocNo;
    $stmtDel = $conn->prepare("DELETE FROM tacking WHERE Number_Bin = ?");
    if (!$stmtDel) {
        echo json_encode(array("status" => "error", "message" => "ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error));
        exit;
    }
    $stmtDel->bind_param("s", $docNo);
    $stmtDel->execute();
    $stmtDel->close();
} else {
    // ---- ຄິດເລກທີ່ເອກະສານ (Number_Bin) ໃຫ້ອັດຕະໂນມັດ ບໍ່ໃຫ້ຊ້ຳກັນ ----
    // ຮູບແບບ: GR-DDMMYYYY-NNNN (ນັບ NNNN ຕໍ່ວັນ, ຂຶ້ນວັນໃໝ່ ນັບ 0001 ໃໝ່)
    $today  = date('dmY'); // ວັນທີ/ເດືອນ/ປີ ເຊັ່ນ 09072026
    $prefix = 'GR-' . $today . '-';

    $sqlDocNo = "SELECT Number_Bin FROM tacking WHERE Number_Bin LIKE '" . $conn->real_escape_string($prefix) . "%' ORDER BY Number_Bin DESC LIMIT 1";
    $resultDocNo = $conn->query($sqlDocNo);

    $nextNum = 1;
    if ($resultDocNo && $resultDocNo->num_rows > 0) {
        $rowDocNo = $resultDocNo->fetch_assoc();
        $lastDocNo = $rowDocNo['Number_Bin'];
        $parts = explode('-', $lastDocNo);
        $lastNum = (int) end($parts);
        $nextNum = $lastNum + 1;
    }

    $docNo = $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

// ---- 3. ດຶງຂໍ້ມູນສ່ວນ header ອື່ນໆ (ຄ່າຄືກັນທຸກແຖວ) ----
$from       = gv($data, 'From', '');
$carrier    = gv($data, 'carrier', '');
$factoryTo  = gv($data, 'Factory_To', '');
$carrierPh  = gv($data, 'carrier-phone', '');
$totalBox   = (int) preg_replace('/[^0-9]/', '', gv($data, 'total-box', '0'));

// ---- ຄ່າ "ເລກທີ່ແຜນ" ຈາກຊ່ອງ header ໃຊ້ເປັນ *fallback* ເທົ່ານັ້ນ (ຕອນນີ້ header ເປັນແຄ່ການສະແດງຜົນລວມ) ----
// ແຫຼ່ງຂໍ້ມູນຈິງແມ່ນ field 'plan' ຂອງແຕ່ລະລາຍການໃນ tableRows (ຜູກຕາມ PR ຂອງແຖວນັ້ນ)
$trackPlanDefault = gv($data, 'track-plan', '');

$senderName = gv($data, 'sender-name', '');
$senderDept = gv($data, 'sender-dept', '');
$senderPh   = gv($data, 'sender-phone', '');

$recvName   = gv($data, 'receiver-name', '');
$recvDept   = gv($data, 'receiver-dept', '');
$recvPh     = gv($data, 'receiver-phone', '');

$prNo       = gv($data, 'pr-no', '');
$totalCost  = gv($data, 'total-cost', '');

$sigSender  = gv($data, 'sig-sender-name', '');
$sigDriver  = gv($data, 'sig-carrier-name', '');
$sigRecv    = gv($data, 'sig-receiver-name', '');
$docDate    = gv($data, 'doc-date', '');

// ---- ຮູບລາຍເຊັນ (path ຈາກ upload_image.php, ບັນທຶກຄືກັນທຸກແຖວຄືກັນກັບຂໍ້ມູນ header) ----
$sigSenderImg = gv($data, 'sig-img-sender-src', '');
$sigDriverImg = gv($data, 'sig-img-carrier-src', '');
$sigRecvImg   = gv($data, 'sig-img-receiver-src', '');

// ---- 4. ດຶງລາຍການສິນຄ້າ ແລະ ຮູບພາບສິນຄ້າ (array) ----
$items  = gv($data, 'tableRows', array());
$photos = gv($data, 'photos', array());

if (empty($items) || !is_array($items)) {
    echo json_encode(array("status" => "error", "message" => "ບໍ່ພົບລາຍການສິນຄ້າໃນຕາຕະລາງ"));
    exit;
}

// ---- 5. ຕຽມ prepared statement (ປ້ອງກັນ SQL Injection) ----
$stmt = $conn->prepare("INSERT INTO tacking
    (Number_Bin, `From`, Carrier, Factory_To, BoxTo_Carrier, Total_Box,
     Name_Sender, Department_HQ, Number_Nam_Sender,
     Name_Receiver, Department_Receiver, Number_Nam_Receiver,
     OA, BarCode, Item, Size, QTY, Unit, Weight, Price, Total_Cost,
     Sig_Sender, Sig_Driver, Sig_Receiver,
     Item_Photo, Sig_Sender_Img, Sig_Driver_Img, Sig_Receiver_Img, Doc_Date, Track_planing_number)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

if (!$stmt) {
    echo json_encode(array("status" => "error", "message" => "ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error));
    exit;
}

$insertedRows = 0;
$errors = array();
$rowIndex = 0;

foreach ($items as $row) {
    // ---- ຄໍລຳ BarCode (ເລກລາຍການ) ຕ້ອງດຶງຈາກ field 'itemNo' ----
    // ເມື່ອກ່ອນດຶງຈາກ 'pt' (ເລກທີ PR) ຜິດພາດ, ເຮັດໃຫ້ຄ່າ BarCode ໃນຖານຂໍ້ມູນ
    // ກາຍເປັນເລກ PR ແທນທີ່ຈະເປັນເລກລາຍການ/barcode ຕົວຈິງ.
    $barcode  = gv($row, 'itemNo', '');
    $itemName = gv($row, 'itemName', '');
    $size     = gv($row, 'size', '');
    $qty      = (int) preg_replace('/[^0-9]/', '', gv($row, 'qty', '0'));
    $unit     = gv($row, 'unit', '');
    $weight   = gv($row, 'weight', '');
    $price    = 0; // ຟອມປັດຈຸບັນບໍ່ມີຊ່ອງລາຄາຕໍ່ລາຍການ

    // ---- ແກ້ໄຂ: ໃຊ້ເລກທີ PR ຂອງແຕ່ລະແຖວ (field 'pt') ແທນທີ່ຈະໃຊ້ $prNo ອັນດຽວຊ້ຳທຸກແຖວ ----
    // ($prNo ຈາກຊ່ອງເທິງສຸດ ໃຊ້ເປັນຄ່າ default ຖ້າແຖວນັ້ນບໍ່ມີຄ່າ 'pt' ມາເລີຍ)
    $rowPrNo = gv($row, 'pt', '');
    if ($rowPrNo === '') {
        $rowPrNo = $prNo;
    }

    // ---- ແກ້ໄຂ (bug ເລກແຜນຄ້າງອັນດຽວ): ໃຊ້ເລກທີ່ແຜນສະເພາະຂອງແຖວນີ້ (field 'plan') ----
    // ຝັ່ງ frontend ຈະຜູກ 'plan' ໄວ້ກັບແຕ່ລະລາຍການຕາມ PR ຂອງລາຍການນັ້ນຕອນເລືອກ PR
    // ຖ້າແຖວໃດບໍ່ມີ 'plan' ມາເລີຍ (ເຊັ່ນ ຟອມເກົ່າ/ແຖວເພີ່ມມືເອງ), ໃຫ້ fallback ໃຊ້ຄ່າ header ອັນດຽວ
    $rowTrackPlan = gv($row, 'plan', '');
    if ($rowTrackPlan === '') {
        $rowTrackPlan = $trackPlanDefault;
    }

    // ຈັບຄູ່ຮູບພາບສິນຄ້າກັບແຖວດ້ວຍລຳດັບ (photo slot ທີ 1 -> row ທີ 1, ...)
    $itemPhoto = '';
    if (isset($photos[$rowIndex])) {
        $itemPhoto = gv($photos[$rowIndex], 'imgSrc', '');
    }

    $stmt->bind_param(
        "ssssssssssssssssssssssssssssss", // 30 columns — all bound as strings
        $docNo,
        $from,
        $carrier,
        $factoryTo,
        $carrierPh,
        $totalBox,
        $senderName,
        $senderDept,
        $senderPh,
        $recvName,
        $recvDept,
        $recvPh,
        $rowPrNo,
        $barcode,
        $itemName,
        $size,
        $qty,
        $unit,
        $weight,
        $price,
        $totalCost,
        $sigSender,
        $sigDriver,
        $sigRecv,
        $itemPhoto,
        $sigSenderImg,
        $sigDriverImg,
        $sigRecvImg,
        $docDate,
        $rowTrackPlan
    );

    if ($stmt->execute()) {
        $insertedRows++;
    } else {
        $errors[] = $stmt->error;
    }

    $rowIndex++;
}

$stmt->close();
$conn->close();

// ---- 6. ຕອບກັບຜົນລັບເປັນ JSON ----
if ($insertedRows > 0) {
    echo json_encode(array(
        "status"   => "success",
        "message"  => "ບັນທຶກສຳເລັດ",
        "inserted" => $insertedRows,
        "doc_no"   => $docNo,
        "errors"   => $errors
    ));
} else {
    echo json_encode(array(
        "status"  => "error",
        "message" => "ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້",
        "errors"  => $errors
    ));
}