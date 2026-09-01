<?php
/**
 * sidebar.php
 * -------------------------------------------------------------
 * Sidebar ລວມສູນ ໃຊ້ຮ່ວມກັນທຸກໜ້າ (StockDashboard.php, Dasborad.php,
 * tacking.php, Report.php, stock.php, follow.php, UseStock.php,
 * picture/index.php, ...).
 *
 * ວິທີໃຊ້:
 *   1. ວາງໄຟລ໌ນີ້ໄວ້ຢູ່ folder ຮາກດຽວກັນກັບໜ້າອື່ນໆ (ຄືກັນກັບ conn.php)
 *   2. ໃນແຕ່ລະໜ້າ: ຢ່າ close() connection ກ່ອນຮອດບ່ອນທີ່ຈະ include ໄຟລ໌ນີ້
 *      (ເພາະໄຟລ໌ນີ້ຕ້ອງໃຊ້ $conn ເພື່ອນັບ badge)
 *   3. ລຶບ HTML sidebar ເດີມ (block <div id="sidebar-wrapper">...</div>) ອອກ
 *      ຈາກໜ້ານັ້ນ ແລ້ວແທນທີ່ດ້ວຍ:
 *         <?php include __DIR__ . '/sidebar.php'; ?>
 *   4. ລຶບ code ຄິດໄລ່ $sidebarCount / $adminUsers / $isAdmin ອອກຈາກໜ້ານັ້ນ
 *      ໄດ້ເລີຍ (ໄຟລ໌ນີ້ຄິດໄລ່ໃຫ້ຄືນໃໝ່, ບໍ່ຊ້ຳກັນອີກຕໍ່ໄປ)
 *
 * ຕ້ອງການຕົວແປ session ທີ່ set ໄວ້ແລ້ວກ່ອນ include:
 *   $_SESSION['user'], $_SESSION['iduser'], $_SESSION['Namepro'], $_SESSION['factory']
 * ຕ້ອງການ: $conn (mysqli, ຍັງບໍ່ໄດ້ close())
 * -------------------------------------------------------------
 */

// ---- ກັນກໍລະນີໄຟລ໌ນີ້ຖືກ include ຊ້ຳ (ບໍ່ຄວນເກີດ, ແຕ່ກັນໄວ້) ----
if (defined('SIDEBAR_RENDERED')) {
    return;
}
define('SIDEBAR_RENDERED', true);

$proo   = $_SESSION["Namepro"] ?? '';
$userId = $_SESSION["iduser"] ?? '';
$myFactory = isset($_SESSION['factory']) ? trim($_SESSION['factory']) : '';
$isHQ = (strcasecmp($myFactory, 'HQ') === 0);

// ===========================================================
// Badge 1: "ຂໍເບິກອຸປະກອນ" — ຈຳນວນຄຳຮ້ອງທີ່ຍັງລໍຖ້າດຳເນີນການ (request.S_Status IN 1,2,3)
// ===========================================================
$adminUsers = ['404', '30', '2', '194', '793', '213', '214', '215'];
$isAdmin = in_array($userId, $adminUsers);

$sidebarCount = 0;
if ($conn) {
    if (!$isAdmin) {
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

// ===========================================================
// Badge 2: "ຕິດຕາມການສົ່ງເຄື່ອງ" — ຈຳນວນ PR ທີ່ຍັງລໍຖ້າສົ່ງ (S_Status = 1)
// ໃຊ້ເງື່ອນໄຂດຽວກັນກັບ get_pr_data.php?action=list ເພື່ອໃຫ້ຕົງກັນກັບ dropdown PR
// ສະແດງສະເພາະຝັ່ງ HQ (ຄືກັນກັບເມນູ "ຕິດຕາມການສົ່ງເຄື່ອງ" ທີ່ສະແດງສະເພາະ HQ)
// ===========================================================
$trackingCount = 0;
if ($conn && $isHQ) {
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

// ===========================================================
// Badge 3: "ຮັບເຄື່ອງເຂົ້າໂຮງງານ" — ຈຳນວນເອກະສານ (Number_Bin) ທີ່ຍັງລໍຖ້າຮັບ
// ຈາກຕາຕະລາງ tacking (Factory_To = ໂຮງງານຕົນເອງ, Sig_Receiver ຍັງຫວ່າງ)
// ໃຊ້ logic ດຽວກັນກັບ inbox_tacking.php ຜ່ານ getPendingTackingCount()
// ສະແດງສະເພາະຝັ່ງທີ່ບໍ່ແມ່ນ HQ (ຄືກັນກັບເມນູ "ຮັບເຄື່ອງເຂົ້າໂຮງງານ" ທີ່ສະແດງສະເພາະ non-HQ)
// ===========================================================
require_once __DIR__ . '/inbox_pending_count.php';

$pendingCount = 0;
if ($conn && !$isHQ) {
    $pendingCount = getPendingTackingCount($conn, $myFactory);
}


// ===========================================================
// Auto-detect ໜ້າປັດຈຸບັນ ເພື່ອໃສ່ class "active" ໃຫ້ອັດຕະໂນມັດ
// (ບໍ່ຕ້ອງໄປແກ້ hardcode "active" ໃນແຕ່ລະໜ້າອີກຕໍ່ໄປ)
// ===========================================================
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

        <a class="list-group-item list-group-item-action p-3<?= sb_active('StockDashboard.php', $currentPage) ?>" href="StockDashboard.php">
            <i class="fa-solid fa-chart-simple"></i>
            <strong>Dashboard ສາງ</strong>
        </a>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('Dasborad.php', $currentPage) ?>" href="Dasborad.php">
            <i class="fi fi-rr-hand-holding-box"></i>
            <strong data-translate="request_equipment">ຂໍເບິກອຸປະກອນ</strong>
            <span class="badge rounded-pill"><?php echo $sidebarCount; ?></span>
        </a>

        <?php if (!in_array($userId, ['30', '194', '204', '213', '214', '215'])): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('UseStock.php', $currentPage) ?>" href="UseStock.php">
                <i class="fa fa-tasks" aria-hidden="true"></i>
                <strong data-translate="use_equipment">ນຳໃຊ້ອຸປະກອນ</strong>
            </a>
        <?php endif; ?>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('follow.php', $currentPage) ?>" href="follow.php">
            <i class="fi fi-sr-location-alt"></i>
            <strong data-translate="track_equipment">ຕິດຕາມ ອຸປະກອນ</strong>
        </a>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('stock.php', $currentPage) ?>" href="stock.php">
            <i class="fa-solid fa-cubes-stacked"></i>
            <strong data-translate="stock_equipment">ສາງອຸປະກອນ</strong>
        </a>

        <?php if ($isHQ): ?>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('tacking.php', $currentPage) ?>" href="tacking.php">
                <i class="fa-solid fa-truck-fast"></i>
                <strong data-translate="tacking">ຕິດຕາມການສົ່ງເຄື່ອງ</strong>
                <span class="badge rounded-pill"><?php echo $trackingCount; ?></span>
            </a>
            <a class="list-group-item list-group-item-action p-3<?= sb_active('report_tacking.php', $currentPage) ?>" href="report_tacking.php">
                <i class="bi bi-bar-chart-line-fill"></i>
                <strong data-translate="report_tacking">ລາງານການສົ່ງເຄື່ອງ</strong>
            </a>
        <?php else: ?>
            <a class="list-group-item list-group-item-action p-3" href="inbox_tacking.php">
                  <i class="fi fi-rs-person-dolly"></i>
                  <strong data-translate="inbox_tacking">ຮັບເຄື່ອງເຂົ້າໂຮງງານ</strong>
                  <?php if ($pendingCount > 0): ?>
                        <span class="badge rounded-pill"><?php echo $pendingCount > 99 ? '99+' : $pendingCount; ?></span>
                  <?php endif; ?>
            </a>
      <?php endif; ?>

        <a class="list-group-item list-group-item-action p-3" href="http://101.78.11.238:901">
            <i class="bi bi-tools"></i>
            <strong data-translate="Rotating_equipment">ອຸປະກອນໝູນວຽນ</strong>
        </a>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('Report.php', $currentPage) ?>" href="Report.php">
            <i class="fa fa-bar-chart" aria-hidden="true"></i>
            <strong data-translate="report">ລາຍງານ</strong>
        </a>

        <a class="list-group-item list-group-item-action p-3<?= sb_active('index.php', $currentPage) ?>" href="picture/index.php">
            <i class="bi bi-images" aria-hidden="true"></i>
            <strong data-translate="image_stock">ຮູບພາບສາງ</strong>
        </a>
        <a class="list-group-item list-group-item-action p-3" href="qr_create.php">
            <i class="bi bi-qr-code" aria-hidden="true"></i>
            <strong data-translate="create QR code invoice">ສ້າງລະຫັດ QR ບີນ</strong>
        </a>
        <br>
    </div>
</div>