<?php
/* ==========================================================================
   get_next_itemcode.php
   ---------------------------------------------------------------------
   AJAX endpoint: ຮັບ ?groupp=ຊື່ກຸ່ມ ແລ້ວຄືນ (JSON) ເລກ Item_code ຕໍ່ໄປ
   ຂອງກຸ່ມນັ້ນ ໂດຍອີງໃສ່ເລກທີ່ຫຼາຍທີ່ສຸດ (MAX) ທີ່ມີຢູ່ໃນຕາຕະລາງ stock
   ແລ້ວ +1.

   ໂຄງສ້າງ Item_code = [ເລກລະຫັດກຸ່ມ 1 ຫຼັກ] + [running number 7 ຫຼັກ]
   ຕົວຢ່າງ: ອຸປະກອນ Lab (prefix 7) -> 70000109 -> ຕໍ່ໄປ 70000110
   ========================================================================== */

require_once __DIR__ . '/../includes/conn.php';
@session_start();

header('Content-Type: application/json; charset=utf-8');

// ຕ້ອງ login ກ່ອນ ຈຶ່ງຈະໃຊ້ endpoint ນີ້ໄດ້
if (($_SESSION["user"] ?? '') === '' || ($_SESSION["Namepro"] ?? '') === '' || ($_SESSION["iduser"] ?? '') === '') {
    http_response_code(403);
    echo json_encode(['error' => 'ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນ']);
    exit;
}

/* ===== Mapping ຊື່ກຸ່ມ -> ເລກລະຫັດກຸ່ມ (prefix 1 ຫຼັກ) =====
   ຕ້ອງກົງກັບຄ່າໃນ column stock.Groupp ແບບ 100% (ຕົວອັກສອນ/space) */
$groupPrefixMap = [
    'ຍານພາຫະນະ'              => 1,
    'ອຸປະກອນສຳນັກງານ'         => 2,
    'ອຸປະກອນຄວາມປອດໄພ'        => 3,
    'ອຸປະກອນທົ່ວໄປ'           => 4,
    'ອຸປະກອນໄຟຟ້າ'            => 5,
    'ອຸປະກອນລາຍການຜະລິດ'      => 6,
    'ອຸປະກອນ Lab'             => 7,
    'ອຸປະກອນກໍ່ສ້າງ'           => 8,
    'ເຄື່ອງມືຊ່າງ'             => 9,
];

$groupp = trim($_GET['groupp'] ?? '');

if ($groupp === '') {
    http_response_code(400);
    echo json_encode(['error' => 'ບໍ່ໄດ້ລະບຸກຸ່ມ']);
    exit;
}

if (!isset($groupPrefixMap[$groupp])) {
    http_response_code(400);
    echo json_encode(['error' => 'ບໍ່ຮູ້ຈັກຊື່ກຸ່ມນີ້']);
    exit;
}

$prefixDigit = $groupPrefixMap[$groupp];
$rangeStart  = $prefixDigit * 10000000;   // e.g. prefix 7 -> 70000000
$rangeEnd    = $rangeStart + 9999999;     // e.g. 79999999

try {
    mysqli_set_charset(@$conn, "utf8");

    $sql = "SELECT MAX(CAST(Item_code AS UNSIGNED)) AS max_code
              FROM stock
             WHERE Groupp = ?
               AND Item_code REGEXP '^[0-9]+$'
               AND CAST(Item_code AS UNSIGNED) BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $groupp, $rangeStart, $rangeEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $maxCode = $row['max_code'] ?? null;

    if ($maxCode === null || (int) $maxCode < $rangeStart) {
        // ຍັງບໍ່ທັນມີເລກໃນກຸ່ມນີ້ - ເລີ່ມຕົ້ນເລກທຳອິດ
        $nextCode = $rangeStart + 1;
    } else {
        $nextCode = (int) $maxCode + 1;
    }

    if ($nextCode > $rangeEnd) {
        echo json_encode(['error' => 'ເລກ Item_code ຂອງກຸ່ມນີ້ເຕັມແລ້ວ (ຮອດ ' . $rangeEnd . ') ກະລຸນາຕິດຕໍ່ຜູ້ດູແລລະບົບ']);
        exit;
    }

    echo json_encode([
        'groupp'    => $groupp,
        'item_code' => (string) $nextCode,
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}