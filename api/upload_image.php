<?php
// =========================================================
// upload_image.php - ອັບໂຫຼດຮູບພາບ (ລາຍເຊັນຜູ້ຮັບ)
// ເກັບໄວ້ໃນ folder 'uploads/' ແຍກຕ່າງຫາກຈາກ 'picture/uploads/'
// (ບ່ອນທີ່ໃຊ້ເກັບຮູບສິນຄ້າ/ລາຍເຊັນເກົ່າ) ເພື່ອບໍ່ໃຫ້ປົນກັນ
// =========================================================

@session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION["user"])) {
    echo json_encode(array('status' => 'error', 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບ'));
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(array('status' => 'error', 'message' => 'ບໍ່ພົບໄຟລ໌ຮູບພາບ'));
    exit;
}

$file = $_FILES['image'];
$maxSize = 2 * 1024 * 1024; // 2MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(array('status' => 'error', 'message' => 'ອັບໂຫລດລົ້ມເຫລວ (ລະຫັດ ' . $file['error'] . ')'));
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(array('status' => 'error', 'message' => 'ໄຟລ໌ໃຫຍ່ເກີນ 2MB'));
    exit;
}

// --- ກວດ MIME type ---
$allowedTypes = array(
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
);

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realType = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
if ($finfo) {
    finfo_close($finfo);
}

if ($realType === false || !array_key_exists($realType, $allowedTypes)) {
    echo json_encode(array('status' => 'error', 'message' => 'ປະເພດໄຟລ໌ບໍ່ຖືກຕ້ອງ'));
    exit;
}

// --- ຢືນຢັນວ່າແມ່ນຮູບແທ້ ---
if (@getimagesize($file['tmp_name']) === false) {
    echo json_encode(array('status' => 'error', 'message' => 'ໄຟລ໌ບໍ່ແມ່ນຮູບພາບທີ່ຖືກຕ້ອງ'));
    exit;
}

// -----------------------------------------------------------
// ໂຟນເດີ 'uploads/' (ບໍ່ແມ່ນ 'picture/uploads/') — ແຍກຕ່າງຫາກ
// ຈາກຮູບສິນຄ້າ/ລາຍເຊັນເກົ່າ ເພື່ອບໍ່ໃຫ້ຊື່ໄຟລ໌ຊ້ຳກັນ ຫຼືປົນກັນ
// -----------------------------------------------------------
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// --- ຊື່ໄຟລ໌ໃໝ່ ---
$ext = $allowedTypes[$realType];
$newName = uniqid('img_', true) . '.' . $ext;
$targetFile = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    // ສົ່ງກັບ Relative Path (ຢູ່ໃນ 'uploads/' ໂດຍກົງ)
    $relativePath = '/uploads/' . $newName;

    echo json_encode(array(
        'status' => 'success',
        'path' => $relativePath
    ));
} else {
    echo json_encode(array('status' => 'error', 'message' => 'ບໍ່ສາມາດບັນທຶກໄຟລ໌ໄດ້'));
}
