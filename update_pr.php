<?php
require_once __DIR__ . '/conn.php';
@session_start();
header('Content-Type: application/json; charset=utf-8');

// ✅ ຕ້ອງ login ກ່ອນ ຈຶ່ງແກ້ໄຂໄດ້
if (empty($_SESSION["user"]) || empty($_SESSION["Namepro"]) || empty($_SESSION["iduser"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບ']);
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$newPr = isset($_POST['OA_Out']) ? trim($_POST['OA_Out']) : '';
$Province = $_SESSION["Namepro"];

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID ບໍ່ຖືກຕ້ອງ']);
    exit;
}

try {
    mysqli_set_charset(@$conn, "utf8");

    // ✅ ຈຳກັດໃຫ້ແກ້ໄຂໄດ້ສະເພາະລາຍການໃນແຂວງຂອງຕົນເອງເທົ່ານັ້ນ (ກັນ user ແກ້ລາຍການແຂວງອື່ນ)
    $stmt = $conn->prepare("UPDATE request SET OA_Out = ? WHERE id = ? AND Province = ?");
    $stmt->bind_param("sis", $newPr, $id, $Province);

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            // ອາດຈະແມ່ນຄ່າເກົ່າຄືກັນກັບຄ່າໃໝ່ (ບໍ່ມີການປ່ຽນແປງ) ຫຼື id/Province ບໍ່ກົງກັນ
            // ກວດຄືນວ່າ row ນີ້ມີຢູ່ຈິງບໍ່ ໃນແຂວງນີ້
            $check = $conn->prepare("SELECT id FROM request WHERE id = ? AND Province = ?");
            $check->bind_param("is", $id, $Province);
            $check->execute();
            $check->store_result();
            if ($check->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບລາຍການ ຫຼື ທ່ານບໍ່ມີສິດແກ້ໄຂລາຍການນີ້']);
                $check->close();
                $stmt->close();
                exit;
            }
            $check->close();
        }
        echo json_encode(['success' => true, 'OA_Out' => $newPr]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
