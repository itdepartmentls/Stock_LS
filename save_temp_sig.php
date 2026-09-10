<?php
// =========================================================
// save_temp_sig.php - ບັນທຶກເສັ້ນທາງຮູບລາຍເຊັນຊົ່ວຄາວ
// =========================================================

// --- ຕັ້ງ CORS Headers ---
$allowed_origin = 'http://101.78.11.238:902';
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    $allowed_origins = [
        'http://101.78.11.238:902',
        'http://localhost:902',
        'http://127.0.0.1:902'
    ];

    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
}

header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Private-Network: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

@session_start();
if (empty($_SESSION["user"])) {
    echo json_encode(array('status' => 'error', 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບ'));
    exit;
}

$docNo = isset($_POST['doc_no']) ? trim($_POST['doc_no']) : '';
$sigPath = isset($_POST['sig_path']) ? trim($_POST['sig_path']) : '';

if (empty($docNo) || empty($sigPath)) {
    echo json_encode(array('status' => 'error', 'message' => 'ຂໍ້ມູນບໍ່ຄົບ'));
    exit;
}

// ບັນທຶກໃສ່ session
$_SESSION['temp_sig_' . $docNo] = $sigPath;

echo json_encode(array('status' => 'success', 'message' => 'ບັນທຶກສຳເລັດ'));
