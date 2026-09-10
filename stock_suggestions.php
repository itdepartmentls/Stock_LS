<?php
require_once __DIR__ . '/conn.php';
@session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['results' => []]);
    exit;
}

$term = trim($_GET['q'] ?? '');
$warehouse = trim($_GET['warehouse'] ?? '');
$isMainStock = in_array($warehouse, ['Stock Sanakham', 'Stock_HQ'], true);

// ===== ໂຫຼດ filemap ຮູບພາບ (picture/uploads/filemap.json) =====
// filemap.json ເກັບເປັນ { safeFileName: displayName }, ໂດຍ displayName ຈະຕົງກັບ Item_code
// ສ້າງ map ກັບກັນ (Item_code -> safeFileName) ເພື່ອຄົ້ນຫາໄວ
$imageBaseDir = __DIR__ . '/picture/uploads/';
$imageBaseUrl = 'picture/uploads/';
$itemCodeToImage = [];
$mapPath = $imageBaseDir . 'filemap.json';
if (file_exists($mapPath)) {
    $fileMap = json_decode(file_get_contents($mapPath), true);
    if (is_array($fileMap)) {
        foreach ($fileMap as $safeName => $displayName) {
            if ($displayName === '' || $displayName === null) continue;
            // ຖ້າຫາກມີຫຼາຍຮູບຕໍ່ Item_code ດຽວກັນ, ໃຊ້ຮູບທຳອິດທີ່ພົບ
            if (!isset($itemCodeToImage[$displayName])) {
                $itemCodeToImage[$displayName] = $safeName;
            }
        }
    }
}

function findItemImage($itemCode, $itemCodeToImage, $imageBaseDir, $imageBaseUrl)
{
    if ($itemCode === '' || $itemCode === '-' || !isset($itemCodeToImage[$itemCode])) {
        return '';
    }
    $safeName = $itemCodeToImage[$itemCode];
    $fullPath = $imageBaseDir . $safeName;
    if (!file_exists($fullPath)) {
        return '';
    }
    return $imageBaseUrl . rawurlencode($safeName) . '?v=' . filemtime($fullPath);
}

if ($isMainStock) {
    $sql = "SELECT Items, Item_name_Chinese, Type, Size, Model, Item_code
            FROM stock
            WHERE Items LIKE ? OR Item_name_Chinese LIKE ? OR Type LIKE ? OR Size LIKE ? OR Model LIKE ?
            ORDER BY Items ASC
            LIMIT 30";
    $params = array_fill(0, 5, '%' . $term . '%');
} else {
    $sql = "SELECT stock_province.Items, stock.Item_name_Chinese, stock_province.Type,
                   stock.Size, stock.Model, stock_province.Item_code
            FROM stock_province
            LEFT JOIN stock ON stock.Items = stock_province.Items
            WHERE stock_province.Provinces = ?
              AND (stock_province.Items LIKE ? OR stock_province.Type LIKE ? OR stock.Item_name_Chinese LIKE ? OR stock.Size LIKE ? OR stock.Model LIKE ?)
            ORDER BY stock_province.Items ASC
            LIMIT 30";
    $params = array_merge([$warehouse], array_fill(0, 5, '%' . $term . '%'));
}

$results = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    $firstParam = $params[0];
    $secondParam = $params[1];
    if ($isMainStock) {
        $thirdParam = $params[2];
        $fourthParam = $params[3];
        $fifthParam = $params[4];
        $stmt->bind_param('sssss', $firstParam, $secondParam, $thirdParam, $fourthParam, $fifthParam);
    } else {
        $thirdParam = $params[2];
        $fourthParam = $params[3];
        $fifthParam = $params[4];
        $sixthParam = $params[5];
        $stmt->bind_param('ssssss', $firstParam, $secondParam, $thirdParam, $fourthParam, $fifthParam, $sixthParam);
    }
    $stmt->execute();
    $queryResult = $stmt->get_result();
    $seen = [];
    while ($row = $queryResult->fetch_assoc()) {
        if (isset($seen[$row['Items']])) continue;
        $seen[$row['Items']] = true;
        $itemCode = $row['Item_code'] ?: '-';
        $results[] = [
            'name' => $row['Items'],
            'chinese_name' => $row['Item_name_Chinese'] ?: '',
            'type' => $row['Type'] ?: '-',
            'size' => $row['Size'] ?: '-',
            'model' => $row['Model'] ?: '-',
            'item_code' => $itemCode,
            'image' => findItemImage($itemCode, $itemCodeToImage, $imageBaseDir, $imageBaseUrl)
        ];
    }
    $stmt->close();
}

echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);