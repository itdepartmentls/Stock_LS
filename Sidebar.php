<?php
/**
 * sidebar.php
 */

// ---- ກັນກໍລະນີໄຟລ໌ນີ້ຖືກ include ຊ້ຳ (ບໍ່ຄວນເກີດ, ແຕ່ກັນໄວ້) ----
if (defined('SIDEBAR_RENDERED')) {
    return;
}
define('SIDEBAR_RENDERED', true);

$proo   = $_SESSION["Namepro"] ?? '';
$userId = (string)($_SESSION["iduser"] ?? ''); // cast ເປັນ string ສະເໝີ ເພື່ອໃຫ້ strict in_array(..., true) ຂ້າງລຸ່ມນີ້ຖືກຕ້ອງ
                                                 // (ຖ້າ session ເກັບ iduser ເປັນ int, strict compare ກັບ '215'/'216' ຈະ false ຖ້າບໍ່ cast)
$myFactory = isset($_SESSION['factory']) ? trim($_SESSION['factory']) : '';
$isHQ = (strcasecmp($myFactory, 'HQ') === 0);


$adminUsers = ['404', '30', '2', '194', '793', '213', '214', '215', '216'];
$isAdmin = in_array($userId, $adminUsers);


require_once __DIR__ . '/sale_scope.php';

// ---- user ຝ່າຍຈັດຊື້ (ເຫັນສະເພາະໜ້າວຽກຂອງເຂົາ) ----
$saleScopeUsers = ['215', '216'];
$isSaleUser     = in_array($userId, $saleScopeUsers, true);


$sidebarCount = 0;
if ($conn) {
    if ($isSaleUser) {
        // ---- ໃຊ້ scope + ສະຖານະ ຈາກ sale_scope.php ບ່ອນດຽວ ----
        // ຕົວເລກ badge ຈຶ່ງຕົງກັບຈຳນວນລາຍການໃນ DataReAdmin_Sale.php ສະເໝີ
        // ຖ້າຢາກປ່ຽນສະຖານະທີ່ນັບ ໃຫ້ໄປແກ້ getSaleListStatuses() ໃນ sale_scope.php ບ່ອນດຽວ
        $sidebarCount = countSaleScopeRequests($conn, $userId);

    } elseif (!$isAdmin) {
        $sqlCount = "SELECT COUNT(a.S_Status) as cccount
                     FROM request a
                     WHERE a.S_Status IN ('1','2','3') AND a.Province = ?";
        $stmtCount = $conn->prepare($sqlCount);
        if ($stmtCount) {
            $stmtCount->bind_param("s", $proo);
            $stmtCount->execute();
            $resCount = $stmtCount->get_result();
            $rowCount = $resCount->fetch_assoc();
            $sidebarCount = $rowCount['cccount'] ?? 0;
            $stmtCount->close();
        }
    } else {
        $sqlCount = "SELECT COUNT(a.S_Status) as cccount
                     FROM request a
                     WHERE a.S_Status = '1'";
        $stmtCount = $conn->prepare($sqlCount);
        if ($stmtCount) {
            $stmtCount->execute();
            $resCount = $stmtCount->get_result();
            $rowCount = $resCount->fetch_assoc();
            $sidebarCount = $rowCount['cccount'] ?? 0;
            $stmtCount->close();
        }
    }
}


// user ຝ່າຍຈັດຊື້ ບໍ່ເຫັນເມນູນີ້ -> ບໍ່ຕ້ອງ query ໃຫ້ເສຍເວລາ
$trackingCount = 0;
if ($conn && $isHQ && !$isSaleUser) {
    $sqlTrackCount = "SELECT COUNT(DISTINCT OA_Out) as cccount
                       FROM request
                       WHERE S_Status = 1 AND OA_Out IS NOT NULL AND OA_Out <> ''";
    $stmtTrackCount = $conn->prepare($sqlTrackCount);
    if ($stmtTrackCount) {
        $stmtTrackCount->execute();
        $resTrackCount = $stmtTrackCount->get_result();
        $rowTrackCount = $resTrackCount->fetch_assoc();
        $trackingCount = $rowTrackCount['cccount'] ?? 0;
        $stmtTrackCount->close();
    }
}


require_once __DIR__ . '/inbox_pending_count.php';

$pendingCount = 0;
if ($conn && !$isHQ && !$isSaleUser) {
    $pendingCount = getPendingTackingCount($conn, $myFactory);
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

function sb_active($pages, $currentPage)
{
    $pages = is_array($pages) ? $pages : [$pages];
    return in_array($currentPage, $pages, true) ? ' active' : '';
}
?>
<div class="border-end" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom">
        <img src="image/Logo.png" class="img-fluid" alt="ETL" width="50">
        <span>ລະບົບສາງ LS</span>
    </div>
    <div class="list-group list-group-flush">

        <?php if (!$isSaleUser): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('StockDashboard.php', $currentPage) ?>" href="StockDashboard.php">
                <i class="fa-solid fa-chart-simple"></i>
                <strong>Dashboard ສາງ</strong>
            </a>
        <?php endif; ?>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('Dasborad.php', $currentPage) ?>" href="Dasborad.php">
            <i class="fi fi-rr-hand-holding-box"></i>
            <strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong>
            <span class="badge rounded-pill"><?php echo $sidebarCount; ?></span>
        </a>

        <?php if (!$isSaleUser && !in_array($userId, ['30', '194', '204', '213', '214'])): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('UseStock.php', $currentPage) ?>" href="UseStock.php">
                <i class="fa fa-tasks" aria-hidden="true"></i>
                <strong data-translate="use_equipment">ນຳໃຊ້ອຸປະກອນ</strong>
            </a>
        <?php endif; ?>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('follow.php', $currentPage) ?>" href="follow.php">
            <i class="fi fi-sr-location-alt"></i>
            <strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
        </a>

        <?php if (!$isSaleUser): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('stock.php', $currentPage) ?>" href="stock.php">
                <i class="fa-solid fa-cubes-stacked"></i>
                <strong data-translate="stock_equipment">ສາງອຸປະກອນ</strong>
            </a>
        <?php endif; ?>

        <?php if ($isHQ): ?>
            <?php if (!$isSaleUser): ?>
                <a class="list-group-item list-group-item-action p-3<?= sb_active('tacking.php', $currentPage) ?>" href="tacking.php">
                    <i class="fa-solid fa-truck-fast"></i>
                    <strong data-translate="tacking">ຕິດຕາມການສົ່ງເຄື່ອງ</strong>
                    <span class="badge rounded-pill"><?php echo $trackingCount; ?></span>
                </a>
            <?php endif; ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('report_tacking.php', $currentPage) ?>" href="report_tacking.php">
                <i class="bi bi-bar-chart-line-fill"></i>
                <strong data-translate="report_tacking">ລາງານການສົ່ງເຄື່ອງ</strong>
            </a>
        <?php elseif (!$isSaleUser): ?>
            <a class="list-group-item list-group-item-action p-3" href="inbox_tacking.php">
                  <i class="fi fi-rs-person-dolly"></i>
                  <strong data-translate="inbox_tacking">ຮັບເຄື່ອງເຂົ້າໂຮງງານ</strong>
                  <?php if ($pendingCount > 0): ?>
                        <span class="badge rounded-pill"><?php echo $pendingCount > 99 ? '99+' : $pendingCount; ?></span>
                  <?php endif; ?>
            </a>
      <?php endif; ?>

        <?php if (!$isSaleUser): ?>
            <a class="list-group-item list-group-item-action p-3" href="http://101.78.11.238:901">
                <i class="bi bi-tools"></i>
                <strong data-translate="Rotating_equipment">ອຸປະກອນໝູນວຽນ</strong>
            </a>
        <?php endif; ?>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('Report.php', $currentPage) ?>" href="Report.php">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
            <strong data-translate="report">ລາຍງານ</strong>
        </a>

        <?php if (!$isSaleUser): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('index.php', $currentPage) ?>" href="picture/index.php">
                <i class="bi bi-images" aria-hidden="true"></i>
                <strong data-translate="image_stock">ຮູບພາບສາງ</strong>
            </a>
            <a class="list-group-item list-group-item-action p-3" href="qr_create.php">
                <i class="bi bi-qr-code" aria-hidden="true"></i>
                <strong data-translate="create QR code invoice">ສ້າງລະຫັດ QR ບີນ</strong>
            </a>
        <?php endif; ?>
        <br>
    </div>
</div>