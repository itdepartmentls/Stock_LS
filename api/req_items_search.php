<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user']) || empty($_SESSION['Namepro'])) {
    http_response_code(401);
    echo json_encode(['results' => []]);
    exit;
}

$term = trim($_GET['q'] ?? '');
$provSession = $_SESSION['Namepro'];

// =========================================================
// ດຶງຮູບພາບຈາກ Item_code ຜ່ານ filemap.json (ຄືກັນກັບ logic ໃນ ReQ.php)
// ໝາຍເຫດ: index.php/upload.php ຢູ່ໃນໂຟນເດີ picture/ ບໍ່ແມ່ນ root
// =========================================================
$uploadsBaseDir = __DIR__ . '/../picture/uploads';
$uploadsBaseUrl = 'picture/uploads';

$imageByItemCode = [];
$mapPath = $uploadsBaseDir . '/filemap.json';
if (file_exists($mapPath)) {
    $imageMap = json_decode(file_get_contents($mapPath), true);
    if (is_array($imageMap)) {
        foreach ($imageMap as $safeName => $itemCode) {
            $path = $uploadsBaseDir . '/' . $safeName;
            if (!isset($imageByItemCode[$itemCode]) || (file_exists($path) && filemtime($path) > (isset($imageByItemCode[$itemCode]['mtime']) ? $imageByItemCode[$itemCode]['mtime'] : 0))) {
                $imageByItemCode[$itemCode] = [
                    'safeName' => $safeName,
                    'mtime'    => file_exists($path) ? filemtime($path) : 0,
                ];
            }
        }
    }
}

function getItemImageUrlLocal($itemCode, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl)
{
    $itemCode = trim((string) $itemCode);
    if ($itemCode === '' || !isset($imageByItemCode[$itemCode])) {
        return '';
    }
    $safeName = $imageByItemCode[$itemCode]['safeName'];
    $path = $uploadsBaseDir . '/' . $safeName;
    if (!file_exists($path)) {
        return '';
    }
    return $uploadsBaseUrl . '/' . rawurlencode($safeName) . '?v=' . filemtime($path);
}

// =========================================================
// ຄົ້ນຫາ stock ສະເພາະລາຍການທີ່ກົງກັບຄຳຄົ້ນຫາ (ຈຳກັດຈຳນວນ) ແທນທີ່ຈະດຶງທັງໝົດ
// oa_input subquery ຕອນນີ້ແມ່ນເບົາ ເພາະຄິດໄລ່ສະເພາະແຖວທີ່ query ໄດ້ (ບໍ່ແມ່ນທຸກແຖວໃນ stock)
// =========================================================
$like = '%' . $term . '%';
$sql = "SELECT s.*,
            (SELECT r.OA_Out FROM request r
             WHERE r.Items = s.Items AND r.Province = ?
             ORDER BY r.dateRe DESC LIMIT 1) as oa_input
        FROM stock s
        WHERE s.Items LIKE ? OR s.Item_name_Chinese LIKE ? OR s.Size LIKE ? OR s.Model LIKE ?
        ORDER BY s.Items ASC
        LIMIT 40";

$results = [];
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('sssss', $provSession, $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $laoName = $row['Items'] ?? '';
        $results[] = [
            'id'       => $laoName,
            'text'     => $laoName,
            'lao'      => $laoName,
            'chinese'  => $row['Item_name_Chinese'] ?? '',
            'size'     => $row['Size'] ?? '',
            'model'    => $row['Model'] ?? '',
            'type'     => $row['Type'] ?? '',
            'qty'      => $row['Total_Unit'] ?? '',
            'picture'  => getItemImageUrlLocal($row['Item_code'] ?? '', $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl),
            'oa'       => $row['oa_input'] ?? '',
        ];
    }
    $stmt->close();
}

echo json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
