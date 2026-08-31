<?php
// ===========================================================================
// stock_dashboard_data.php
// Endpoint ສົ່ງຂໍ້ມູນ dashboard ເປັນ JSON - ໃຊ້ໂດຍ JS ຝັ່ງໜ້າ StockDashboard.php
// ເພື່ອດຶງຂໍ້ມູນໃໝ່ແບບ realtime (polling) ຮອງຮັບ 4 tab:
//   ?view=type      (ຄ່າ default) -> ຂໍ້ມູນຈາກຕາຕະລາງ stock (Type/Groupp)
//   ?view=province  -> ຂໍ້ມູນຈາກຕາຕະລາງ stock_province (ຕາມແຂວງ, column: Provinces)
//                      ຮອງຮັບ &province=ຊື່ແຂວງ ເພື່ອກັ່ນຕອງເພີ່ມ
//   ?view=usestock  -> ຂໍ້ມູນຈາກຕາຕະລາງ usestock (ຍອດເບິກ/ໃຊ້, column: Provinces)
//                      ຮອງຮັບ &province=ຊື່ແຂວງ ເພື່ອກັ່ນຕອງເພີ່ມ
//   ?view=request   -> ຂໍ້ມູນຈາກຕາຕະລາງ request (ຍອດຂໍເບິກ, column: Province ບໍ່ມີ s)
//                      ຮອງຮັບ &province=ຊື່ແຂວງ ເພື່ອກັ່ນຕອງເພີ່ມ
// ===========================================================================
require_once __DIR__ . '/conn.php';
require_once __DIR__ . '/stock_dashboard_lib.php';
@session_start();

header('Content-Type: application/json; charset=utf-8');

// ຕ້ອງ login ຢູ່ ຈຶ່ງຈະດຶງຂໍ້ມູນໄດ້ (ຄືກັນກັບໜ້າ dashboard ຫຼັກ)
if (empty($_SESSION["user"]) || empty($_SESSION["Namepro"]) || empty($_SESSION["iduser"])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$view = isset($_GET['view']) ? trim($_GET['view']) : 'type';

if ($view === 'province') {
    $province = isset($_GET['province']) ? trim($_GET['province']) : null;
    if ($province === '' || $province === 'ALL') {
        $province = null;
    }
    $data = getStockProvinceDashboardData($conn, $province);
} elseif ($view === 'usestock') {
    $province = isset($_GET['province']) ? trim($_GET['province']) : null;
    if ($province === '' || $province === 'ALL') {
        $province = null;
    }
    $data = getUseStockDashboardData($conn, $province);
} elseif ($view === 'request') {
    $province = isset($_GET['province']) ? trim($_GET['province']) : null;
    if ($province === '' || $province === 'ALL') {
        $province = null;
    }
    $data = getRequestDashboardData($conn, $province);
} else {
    $data = getStockDashboardData($conn);
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);