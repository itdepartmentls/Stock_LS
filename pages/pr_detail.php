<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();

if (empty($_SESSION["user"])) {
    echo "<script>window.location='index.php';</script>";
    exit;
}

$pr_no = $_GET['pr_no'] ?? '';
if (empty($pr_no)) {
    die("ບໍ່ພົບເລກ PR");
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function getValue($row, $key, $default = '-')
{
    return isset($row[$key]) && $row[$key] !== '' ? $row[$key] : $default;
}

/*
|--------------------------------------------------------------------------
| ຮູບພາບ: ດຶງຈາກ Item_code ແບບສົດໆທຸກຄັ້ງ ຜ່ານ picture/uploads/filemap.json
|--------------------------------------------------------------------------
*/
$uploadsBaseDir = __DIR__ . '/../picture/uploads';
$uploadsBaseUrl = 'picture/uploads';

function loadImageFilemap($mapFile)
{
    if (file_exists($mapFile)) {
        $decoded = json_decode(file_get_contents($mapFile), true);
        return is_array($decoded) ? $decoded : array();
    }
    return array();
}

$imageMap = loadImageFilemap($uploadsBaseDir . '/filemap.json');
$imageByItemCode = array();
foreach ($imageMap as $safeName => $itemCode) {
    $path = $uploadsBaseDir . '/' . $safeName;
    if (!isset($imageByItemCode[$itemCode]) || (file_exists($path) && filemtime($path) > (isset($imageByItemCode[$itemCode]['mtime']) ? $imageByItemCode[$itemCode]['mtime'] : 0))) {
        $imageByItemCode[$itemCode] = array(
            'safeName' => $safeName,
            'mtime'    => file_exists($path) ? filemtime($path) : 0,
        );
    }
}

function getItemImageUrl($itemCode, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl)
{
    $itemCode = trim((string) $itemCode);
    if ($itemCode === '' || !isset($imageByItemCode[$itemCode])) {
        return null;
    }
    $safeName = $imageByItemCode[$itemCode]['safeName'];
    $path = $uploadsBaseDir . '/' . $safeName;
    if (!file_exists($path)) {
        return null;
    }
    return $uploadsBaseUrl . '/' . rawurlencode($safeName) . '?v=' . filemtime($path);
}

/*
|--------------------------------------------------------------------------
| ໜ້າ error/notice
|--------------------------------------------------------------------------
*/
function renderPrNotice($title, $message, $icon = 'fa-info-circle', $color = '#0099cc')
{
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="image/favicons.png">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/pages/pr_detail-notice.css">
    <style>:root { --notice-color: <?php echo $color; ?>; }</style>
</head>

<body>
    <div class="notice-card">
        <img src="image/logo.png" alt="Logo" onerror="this.style.display='none'">
        <div class="notice-icon"><i class="fa <?php echo $icon; ?>"></i></div>
        <div class="notice-title"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="notice-message"><?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?></div>
        <button class="btn-back" onclick="window.history.length > 1 ? window.history.back() : window.location.href='Dasborad.php'">
            <i class="fa fa-undo"></i> ກັບຄືນ
        </button>
    </div>
</body>

</html>
<?php
    exit;
}

/*
|--------------------------------------------------------------------------
| Get PR Information
|--------------------------------------------------------------------------
*/
$sql = "SELECT 
            r.*,
            s.Size,
            s.Model,
            s.packet_size,
            s.Vendor as stock_vendor,
            s.Use_For,
            s.Type as stock_type,
            sp.Unit as stock_remain,
            u.id as staff_id,
            u.user as staff_name
        FROM request r
        LEFT JOIN stock s ON r.Item_code = s.Item_code
        LEFT JOIN stock_province sp ON r.Item_code = sp.Item_code AND r.Province = sp.Provinces
        LEFT JOIN sod_users u ON r.User_Re = u.name
        WHERE r.OA_Out = ?
        ORDER BY r.id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pr_no);
$stmt->execute();
$result  = $stmt->get_result();
$allRows = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($allRows)) {
    renderPrNotice(
        'ບໍ່ພົບຂໍ້ມູນ',
        'ບໍ່ພົບຂໍ້ມູນ PR ເລກທີ ' . $pr_no,
        'fa-exclamation-triangle',
        '#e11d48'
    );
}

$items = array_values(array_filter($allRows, function ($row) {
    return (int)($row['S_Status'] ?? 0) === 1;
}));

if (empty($items)) {
    $statuses = array_unique(array_map(function ($row) {
        return (int)($row['S_Status'] ?? 0);
    }, $allRows));

    if (in_array(4, $statuses, true)) {
        renderPrNotice(
            'PR ນີ້ຖືກອະນຸມັດແລ້ວ',
            'PR ເລກທີ ' . $pr_no . ' ໄດ້ຖືກກົດອະນຸມັດ ແລະ ດຳເນີນການເບີກອອກໄປແລ້ວ, ບໍ່ສາມາດເປີດເອກະສານນີ້ອີກໄດ້.',
            'fa-check-circle',
            '#009b73'
        );
    }

    renderPrNotice(
        'ບໍ່ສາມາດເປີດ PR ນີ້ໄດ້',
        'PR ເລກທີ ' . $pr_no . ' ໄດ້ຖືກກົດອະນຸມັດ ແລະ ດຳເນີນການເບີກອອກໄປແລ້ວ, ບໍ່ສາມາດເປີດເອກະສານນີ້ໄດ້.',
        'fa-check-circle',
        '#009b73'
    );
}

$firstRow    = $items[0];
$prNumber    = getValue($firstRow, 'OA_Out');
$requester   = getValue($firstRow, 'User_Re');
$province    = getValue($firstRow, 'Province');
$staffId     = getValue($firstRow, 'staff_id');
$staffName   = getValue($firstRow, 'staff_name');
$dateRequest = getValue($firstRow, 'dateRe');
$remark      = getValue($firstRow, 'Remark', '');
$reason      = getValue($firstRow, 'requirements', '');
$Re_requi_persoin   = getValue($firstRow, 'Re_requi_persoin');
$STAFF_ID   = getValue($firstRow, 'STAFF_ID');
$Remark_ReQui      = getValue($firstRow, 'Remark_ReQui', '');
if (empty($staffId) || $staffId === '-') {
    $staffId = 'LS-8842';
}

/*
|--------------------------------------------------------------------------
| ຕື່ມຂໍ້ມູນລາຍການ
|--------------------------------------------------------------------------
*/
$grandTotal1 = 0;
$grandTotal2 = 0;
$grandTotal3 = 0;

foreach ($items as &$item) {
    $itemCode     = getValue($item, 'Item_code');
    $itemProvince = getValue($item, 'Province', $province);
    $size         = getValue($item, 'Size', '');
    $model        = getValue($item, 'Model', '');
    $stockRemain  = getValue($item, 'stock_remain', 0);

    if (empty($size) && empty($model)) {
        $check_stmt = $conn->prepare(
            "SELECT Size, Model, packet_size, Vendor, Use_For, Type
             FROM stock WHERE Item_code = ?"
        );
        $check_stmt->bind_param("s", $itemCode);
        $check_stmt->execute();
        $stock_data = $check_stmt->get_result()->fetch_assoc();
        $check_stmt->close();

        if ($stock_data) {
            $size  = getValue($stock_data, 'Size', '');
            $model = getValue($stock_data, 'Model', '');
        }
    }

    if (empty($stockRemain) || $stockRemain === '-') {
        $check_stmt2 = $conn->prepare(
            "SELECT Unit FROM stock_province WHERE Item_code = ? AND Provinces = ?"
        );
        $check_stmt2->bind_param("ss", $itemCode, $itemProvince);
        $check_stmt2->execute();
        $sp_data = $check_stmt2->get_result()->fetch_assoc();
        $check_stmt2->close();

        if ($sp_data) {
            $stockRemain = getValue($sp_data, 'Unit', 0);
        }
    }

    $item['_size']        = $size;
    $item['_model']       = $model;
    $item['_stockRemain'] = $stockRemain;
    $item['_imagePath'] = getItemImageUrl($itemCode, $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);

    $unit   = (float)getValue($item, 'Unit', 0);
    $price1 = (float)getValue($item, 'Price1', 0);
    $price2 = (float)getValue($item, 'Price2', 0);
    $price3 = (float)getValue($item, 'Price3', 0);

    $amount1 = $price1 * $unit;
    $amount2 = $price2 * $unit;
    $amount3 = $price3 * $unit;

    $item['_price1']  = $price1;
    $item['_price2']  = $price2;
    $item['_price3']  = $price3;
    $item['_amount1'] = $amount1;
    $item['_amount2'] = $amount2;
    $item['_amount3'] = $amount3;

    $grandTotal1 += $amount1;
    $grandTotal2 += $amount2;
    $grandTotal3 += $amount3;
}
unset($item);

$prDate = date('d/m/Y');
if (!empty($dateRequest) && $dateRequest !== '-') {
    $timestamp = strtotime($dateRequest);
    if ($timestamp) {
        $prDate = date('d/m/Y', $timestamp);
    }
}
$expireDate = date('d/m/Y', strtotime('+2 days'));

$userFactory = isset($_SESSION['factory']) ? trim($_SESSION['factory']) : '';

$factoryAddresses = array(
    'HQ' => array(
        'company' => 'LS Tapioca Starch Factory Co.,Ltd.',
        'address' => 'ສຳນັກງານໃຫຍ່ ສີບຸນເຮືອງ, ເມືອງ ຈັນທະບູລີ, ນະຄອນຫຼວງວຽງຈັນ.'
    ),
    'Sanakham' => array(
        'company' => 'LS Tapioca Starch Factory Co.,Ltd.',
        'address' => 'Khokkadoor Village, Sanakham District, Vientiane Capital.'
    ),
    'MuangNan' => array(
        'company' => 'LS Tapioca Starch Factory Co.,Ltd.',
        'address' => 'Huay La Village, MuangNan District, Luang Prabang Province'
    ),
);

$factoryKey     = isset($factoryAddresses[$userFactory]) ? $userFactory : 'HQ';
$companyName    = $factoryAddresses[$factoryKey]['company'];
$companyAddress = $factoryAddresses[$factoryKey]['address'];
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="image/favicons.png">
    <title>ລາຍການຂໍຊື້ PR | <?php echo e($prNumber); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="css/pages/pr_detail.css">
</head>

<body>

    <!-- TOP BUTTONS -->
    <div class="top-bar">
        <button class="btn-back">
            <a href="Dasborad.php"><i class="fa fa-undo"></i> ກັບຄືນ</a>
        </button>
        <button onclick="doPrint()" class="btn-print">
            <i class="fa fa-print"></i> ພິມ / ພິມ PDF (A4 Landscape)
        </button>
    </div>

    <!-- A4 PAPER -->
    <div class="paper" id="prPaper">

        <!-- HEADER -->
        <div class="header-border">
            <div class="header">
                <img src="image/logo.png" class="logo" alt="LS AGRI Logo">
                <div class="company">
                    <h1>LS Tapioca Starch Factory Co.,Ltd.</h1>
                    <p><?php echo e($companyAddress); ?></p>
                    <div class="pr-title">ລາຍການຂໍຊື້ (Purchase Request)</div>
                </div>
                <div class="pr-box">
                    <strong>PR: <?php echo e($prNumber); ?></strong><br>
                    ວັນທີຂໍຊື້: <?php echo e($prDate); ?><br>
                    ໃຊ້ງານຮອດ: <?php echo e($expireDate); ?>
                </div>
            </div>
        </div>

        <!-- PRODUCT TABLE -->
        <table class="pr-table">
            <thead>
                <tr class="info-head-row">
                    <th colspan="15">
                        <div class="info">
                            <div class="info-box">
                                <div class="info-label">ຜູ້ຂໍ / Applicant</div>
                                <div class="info-value"><?php echo e($Re_requi_persoin); ?></div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">ລະຫັດພະນັກງານ / STAFF ID</div>
                                <div class="info-value"><?php echo e($STAFF_ID); ?></div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">ສຳນັກງານ / Province</div>
                                <div class="info-value"><?php echo e($province); ?></div>
                            </div>
                            <div class="info-box">
                                <div class="info-label">ວັນທີ / PR DATE</div>
                                <div class="info-value"><?php echo e($prDate); ?> / <?php echo e($prNumber); ?></div>
                            </div>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th rowspan="2">ລຳດັບ</th>
                    <th rowspan="2">ລະຫັດ</th>
                    <th rowspan="2">ລາຍການ</th>
                    <th rowspan="2">ຈຳນວນ</th>
                    <th rowspan="2">ຫົວໜ່ວຍ</th>
                    <th rowspan="2">ຮູບພາບ</th>
                    <th rowspan="2">ຂະໜາດ</th>
                    <th rowspan="2">ຮຸ່ນ</th>
                    <th rowspan="2">ຄົງເຫຼືອ</th>
                    <th colspan="2" class="supplier1">ຜູ້ສະໜອງ 1</th>
                    <th colspan="2" class="supplier2">ຜູ້ສະໜອງ 2</th>
                    <th colspan="2" class="supplier3">ຜູ້ສະໜອງ 3</th>
                </tr>
                <tr>
                    <th class="supplier1">ລາຄາ</th>
                    <th class="supplier1">ຈຳນວນເງິນ</th>
                    <th class="supplier2">ລາຄາ</th>
                    <th class="supplier2">ຈຳນວນເງິນ</th>
                    <th class="supplier3">ລາຄາ</th>
                    <th class="supplier3">ຈຳນວນເງິນ</th>
                </tr>
            </thead>
            <!-- ✅ tfoot ຈະສະແດງທຸກໜ້າເມື່ອພິມ -->
            <tfoot>
                <tr>
                    <td colspan="15" class="sign-foot-cell">
                        <!-- NOTES -->
                        <div class="bottom-grid">
                            <div class="note-box">
                                <strong>ⓘ ເຫດຜົນ / Reason:</strong><br>
                                <?php echo !empty($reason) && $reason !== '-' ? nl2br(e($reason)) : '-'; ?>
                            </div>
                            <div class="note-box remark-box">
                                <strong>📝 ໝາຍເຫດ / Remark:</strong><br>
                                <?php echo !empty($Remark_ReQui) && $Remark_ReQui !== '-' ? nl2br(e($Remark_ReQui)) : '-'; ?>
                            </div>
                        </div>

                        <!-- SIGNATURE -->
                        <div class="signature">
                            <div class="sign-item">
                                <div class="sign-box">
                                    <div class="sign-title">ຜູ້ຂໍ / Applicant</div>
                                </div>
                                <div class="sign-date">ວັນທີ <?php echo e($prDate); ?></div>
                            </div>
                            <div class="sign-item">
                                <div class="sign-box">
                                    <div class="sign-title">ສາງ / Store: <?php echo e($requester); ?></div>
                                </div>
                                <div class="sign-date">ວັນທີ <?php echo e($prDate); ?></div>
                            </div>
                            <div class="sign-item">
                                <div class="sign-box">
                                    <div class="sign-title">ຈັດຊື້ / Purchase</div>
                                </div>
                                <div class="sign-date">ວັນທີ <?php echo e($prDate); ?></div>
                            </div>
                            <div class="sign-item">
                                <div class="sign-box">
                                    <div class="sign-title">ຜູ້ຈັດການໂຮງງານ / Manager</div>
                                </div>
                                <div class="sign-date">ວັນທີ <?php echo e($prDate); ?></div>
                            </div>
                        </div>

                        <!-- FOOTER -->
                        <div class="footer">
                            <span>LS Tapioca Starch Factory Co.,Ltd.</span>
                            <span>Purchase Request Form (PR System)</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo e(getValue($item, 'Item_code')); ?></td>
                        <td class="item-name"><?php echo e(getValue($item, 'Items')); ?></td>
                        <td><?php echo e(getValue($item, 'Unit', 0)); ?></td>
                        <td><?php echo e(getValue($item, 'Type', '')); ?></td>
                        <td class="image-cell">
                            <?php if (!empty($item['_imagePath'])): ?>
                                <img src="<?php echo e($item['_imagePath']); ?>" class="item-image" alt="<?php echo e(getValue($item, 'Items')); ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='<strong><?php echo e(getValue($item, 'Items')); ?></strong>';">
                            <?php else: ?>
                                <strong><?php echo e(getValue($item, 'Items')); ?></strong>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($item['_size']); ?></td>
                        <td><?php echo e($item['_model']); ?></td>
                        <td><?php echo e($item['_stockRemain']); ?></td>

                        <td class="supplier1 price"><?php echo number_format($item['_price1']); ?></td>
                        <td class="supplier1 amount"><?php echo number_format($item['_amount1']); ?></td>

                        <td class="supplier2 price"><?php echo number_format($item['_price2']); ?></td>
                        <td class="supplier2 amount"><?php echo number_format($item['_amount2']); ?></td>

                        <td class="supplier3 price"><?php echo number_format($item['_price3']); ?></td>
                        <td class="supplier3 amount"><?php echo number_format($item['_amount3']); ?></td>
                    </tr>
                <?php endforeach; ?>

                <tr class="total-row">
                    <td colspan="9" class="total-title">TOTAL / TOTAL AMOUNT</td>
                    <td colspan="2"><?php echo number_format($grandTotal1); ?> LAK</td>
                    <td colspan="2"><?php echo number_format($grandTotal2); ?> LAK</td>
                    <td colspan="2"><?php echo number_format($grandTotal3); ?> LAK</td>
                </tr>
            </tbody>
        </table>

    </div>

    <script>
        (function () {
            var paper = document.getElementById('prPaper');
            var MM_TO_PX = 96 / 25.4;
            var PAGE_HEIGHT_MM = 210;
            var PAGE_MARGIN_MM = 24;
            var MAX_CONTENT_PX = (PAGE_HEIGHT_MM - PAGE_MARGIN_MM) * MM_TO_PX;
            var MIN_SCALE = 0.72;

            function supportsZoom() {
                return 'zoom' in document.documentElement.style;
            }

            function fitToOnePage() {
                if (!paper || !supportsZoom()) return;

                paper.style.zoom = 1;
                void paper.offsetHeight;

                var contentHeight = paper.scrollHeight;

                if (contentHeight > MAX_CONTENT_PX) {
                    var scale = MAX_CONTENT_PX / contentHeight;

                    if (scale >= MIN_SCALE) {
                        paper.style.zoom = scale;
                    }
                }
            }

            function resetZoom() {
                if (paper) paper.style.zoom = 1;
            }

            window.addEventListener('beforeprint', function() {
                fitToOnePage();
            });
            
            window.addEventListener('afterprint', resetZoom);

            window.doPrint = function () {
                fitToOnePage();
                window.print();
            };
        })();
    </script>

</body>

</html>
