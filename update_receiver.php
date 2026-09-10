<?php
// =========================================================
// update_receiver.php - ບັນທຶກຂໍ້ມູນຜູ້ຮັບ ແລະ ຮູບລາຍເຊັນ
// =========================================================

header('Content-Type: application/json; charset=utf-8');

@session_start();
if (empty($_SESSION["user"])) {
    echo json_encode(array('status' => 'error', 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບ'));
    exit;
}

$myFactory = isset($_SESSION["factory"]) ? trim($_SESSION["factory"]) : '';

require_once __DIR__ . '/conn.php';
/** @var mysqli $conn */

// ຮັບຄ່າຈາກທັງສອງແຫຼ່ງ
$number_bin    = isset($_POST['number_bin']) ? trim($_POST['number_bin']) : '';
$doc_no_GR     = isset($_POST['doc_no_GR']) ? trim($_POST['doc_no_GR']) : '';
$pr_no         = isset($_POST['pr_no']) ? trim($_POST['pr_no']) : ''; // ຈາກ pr_bin_print.php (ບໍ່ໄດ້ໃຊ້ໃນ UPDATE ອີກຕໍ່ໄປ)

$receiver_name = isset($_POST['receiver_name']) ? trim($_POST['receiver_name']) : '';
$receiver_dept = isset($_POST['receiver_dept']) ? trim($_POST['receiver_dept']) : '';
$receiver_phone = isset($_POST['receiver_phone']) ? trim($_POST['receiver_phone']) : '';
$receiver_sig  = isset($_POST['receiver_sig']) ? trim($_POST['receiver_sig']) : '';

// ກຳນົດຄ່າ number_bin ຈາກ doc_no_GR ຖ້າບໍ່ມີ
if (empty($number_bin) && !empty($doc_no_GR)) {
    $number_bin = $doc_no_GR;
}

// ຖ້າຍັງບໍ່ມີ number_bin, ລອງຫາຈາກ pr_no (ກໍລະນີເກົ່າ)
if (empty($number_bin) && !empty($pr_no)) {
    // ຄົ້ນຫາ Number_Bin ຈາກ OA (PR No.)
    $find_sql = "SELECT Number_Bin FROM tacking WHERE OA = ? LIMIT 1";
    $find_stmt = $conn->prepare($find_sql);
    if ($find_stmt) {
        $find_stmt->bind_param("s", $pr_no);
        $find_stmt->execute();
        $find_stmt->bind_result($found_number_bin);
        if ($find_stmt->fetch()) {
            $number_bin = $found_number_bin;
        }
        $find_stmt->close();
    }
}

if (empty($number_bin)) {
    echo json_encode(array('status' => 'error', 'message' => 'ບໍ່ພົບເລກທີ່ເອກະສານ'));
    exit;
}

if (empty($receiver_name)) {
    echo json_encode(array('status' => 'error', 'message' => 'ກະລຸນາລະບຸຊື່ຜູ້ຮັບ'));
    exit;
}

// ---------------------------------------------------------------
// ກວດຮູບແບບ receiver_sig: ຕ້ອງເປັນ relative path ທີ່ຊີ້ໄປຫາ
// uploads/ ເທົ່ານັ້ນ (ຄືອັນທີ່ upload_image.php ຄືນມາ),
// ບໍ່ຮັບຄ່າອື່ນ ເພື່ອປ້ອງກັນການສົ່ງ path/URL ຕາມໃຈມາເກັບໃນ DB
// ---------------------------------------------------------------
if (empty($receiver_sig) || !preg_match('#^/?uploads/[A-Za-z0-9_.\-]+$#', $receiver_sig)) {
    echo json_encode(array('status' => 'error', 'message' => 'ຮູບແບບລາຍເຊັນບໍ່ຖືກຕ້ອງ ກະລຸນາອັບໂຫລດລາຍເຊັນໃໝ່'));
    exit;
}
if ($receiver_sig[0] !== '/') {
    $receiver_sig = '/' . $receiver_sig;
}

// ກວດສອບເອກະສານ + ສິດຂອງໂຮງງານ
$sqlCheck = "SELECT Sig_Receiver, Factory_To FROM tacking WHERE Number_Bin = ? LIMIT 1";
$stmtCheck = $conn->prepare($sqlCheck);
if (!$stmtCheck) {
    echo json_encode(array('status' => 'error', 'message' => 'ຕຽມ query ບໍ່ສຳເລັດ: ' . $conn->error));
    exit;
}
$stmtCheck->bind_param("s", $number_bin);
$stmtCheck->execute();

// ປະກາດຕົວແປລ່ວງໜ້າ (ບໍ່ປ່ຽນພຶດຕິກຳ, ພຽງແຕ່ແກ້ warning ຂອງ IDE)
$existingSigReceiver = $factoryTo = null;

$stmtCheck->bind_result($existingSigReceiver, $factoryTo);
$found = $stmtCheck->fetch();
$stmtCheck->close();

if (!$found) {
    echo json_encode(array('status' => 'error', 'message' => 'ບໍ່ພົບເອກະສານເລກທີ ' . $number_bin));
    exit;
}

if (!empty($existingSigReceiver)) {
    echo json_encode(array('status' => 'error', 'message' => 'ເອກະສານນີ້ຖືກຮັບໄປແລ້ວ'));
    exit;
}

// ---------------------------------------------------------------
// ກວດວ່າຜູ້ໃຊ້ຢູ່ໂຮງງານດຽວກັນກັບ Factory_To ຂອງເອກະສານນີ້ບໍ່
// (ຄືເງື່ອນໄຂດຽວກັນກັບ $canReceive ໃນ receive_tacking.php)
// ---------------------------------------------------------------
$isOwnFactory = ($myFactory !== '' && strcasecmp($factoryTo ?? '', $myFactory) === 0);
if (!$isOwnFactory) {
    echo json_encode(array('status' => 'error', 'message' => 'ທ່ານບໍ່ມີສິດຮັບເຄື່ອງເອກະສານນີ້ (ບໍ່ແມ່ນຂອງໂຮງງານທ່ານ)'));
    exit;
}

// ---------------------------------------------------------------
// ອັບເດດຂໍ້ມູນ (ບໍ່ແຕະ column OA ເລີຍ - OA/PR No ເປັນຂໍ້ມູນເດີມຂອງ
// ລາຍການ, ບໍ່ແມ່ນຂໍ້ມູນຜູ້ຮັບ. ຂະບວນການຮັບເຄື່ອງບໍ່ຄວນປ່ຽນຄ່ານີ້ -
// ກ່ອນໜ້ານີ້ SET OA = ? ຖືກ bind ດ້ວຍ $pr_no ເຊິ່ງໜ້າ receive_tacking.php
// ບໍ່ໄດ້ສົ່ງຄ່າມາເລີຍ (ຄ່າຈຶ່ງເປັນ '' ສະເໝີ) ເຮັດໃຫ້ OA ຖືກທັບຫາຍ
// ທຸກຄັ້ງທີ່ກົດ "ບັນທຶກຮັບເຄື່ອງ")
// ---------------------------------------------------------------
$sql = "UPDATE tacking SET
        Name_Receiver = ?,
        Department_Receiver = ?,
        Number_Nam_Receiver = ?,
        Sig_Receiver = ?,
        Sig_Receiver_Img = ?
        WHERE Number_Bin = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(array('status' => 'error', 'message' => 'ຕຽມ query ບໍ່ສຳເລັດ: ' . $conn->error));
    exit;
}

$stmt->bind_param(
    "ssssss",
    $receiver_name,
    $receiver_dept,
    $receiver_phone,
    $receiver_name,
    $receiver_sig,
    $number_bin
);

if ($stmt->execute()) {
    $affected_rows = $stmt->affected_rows;

    // ບັນທຶກປະຫວັດການຮັບເຄື່ອງ (ຖ້າມີຕາຕະລາງ)
    // log_receive_history($number_bin, $receiver_name, $affected_rows);

    echo json_encode(array(
        'status' => 'success',
        'message' => "ບັນທຶກຂໍ້ມູນຜູ້ຮັບສຳເລັດ ($affected_rows ລາຍການ)",
        'affected_rows' => $affected_rows
    ));
} else {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'ບັນທຶກຂໍ້ມູນບໍ່ສຳເລັດ: ' . $stmt->error
    ));
}

$stmt->close();
$conn->close();