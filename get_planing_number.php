<?php
// =========================================================
// get_planing_number.php
// ຄົ້ນຫາ "ເລກທີ່ແຜນ" (planing_number) ຈາກຕາຕະລາງ request
// ໂດຍອີງໃສ່ ເລກທີ PR (column OA_Out) - ໃຊ້ໂດຍ tacking.php
// ເພື່ອດຶງເລກແຜນມາໃສ່ອັດຕະໂນມັດຕອນເລືອກ PR
// =========================================================
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array("status" => "error", "message" => "ບໍ່ໄດ້ເຂົ້າສູ່ລະບົບ"));
    exit;
}

header('Content-Type: application/json; charset=utf-8');

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
    mysqli_report(MYSQLI_REPORT_OFF);
}

$pr = isset($_GET['pr']) ? trim($_GET['pr']) : '';

if ($pr === '') {
    echo json_encode(array("status" => "error", "message" => "ບໍ່ໄດ້ລະບຸເລກ PR"));
    exit;
}

// ---- ຄົ້ນຫາ planing_number ຈາກຕາຕະລາງ request ຕາມ OA_Out = ເລກ PR ----
// ຖ້າມີຫຼາຍແຖວກົງກັນ (ຫຼາຍແຜນ ຫຼື ຫຼາຍປະຫວັດສຳລັບ PR ດຽວກັນ),
// ເອົາແຖວທີ່ dateRe ຫຼ້າສຸດ ແລະ ຕ້ອງມີຄ່າ planing_number ບໍ່ຫວ່າງ
$sql = "SELECT planing_number
        FROM request
        WHERE OA_Out = ?
          AND planing_number IS NOT NULL
          AND planing_number <> ''
        ORDER BY dateRe DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(array("status" => "error", "message" => "ຕຽມ query ບໍ່ສຳເລັດ: " . $conn->error));
    exit;
}

$stmt->bind_param("s", $pr);
$stmt->execute();
$stmt->bind_result($planNo);
$found = $stmt->fetch();
$stmt->close();
$conn->close();

if ($found && $planNo !== null && $planNo !== '') {
    echo json_encode(array(
        "status" => "success",
        "planing_number" => $planNo
    ));
} else {
    echo json_encode(array(
        "status" => "success",
        "planing_number" => ""
    ));
}