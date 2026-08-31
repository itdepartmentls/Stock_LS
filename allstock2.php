<?php
require_once __DIR__ . '/includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

/* ==========================================================================
   ດຶງຮູບອຸປະກອນຈາກລະບົບ upload ໃນ picture/ (index.php ຢູ່ folder picture/)
   - filemap.json ເກັບ mapping: safeName(img_xxx.jpg) => displayName
   - ໃນລະບົບ upload, ຜູ້ໃຊ້ຕັ້ງ displayName = Item_code (ເຊັ່ນ 70000001)
   - ດັ່ງນັ້ນ allstock.php ພຽງແຕ່ອ່ານ filemap.json ແລ້ວຈັບຄູ່
     displayName == Item_code ເພື່ອຫາຊື່ໄຟລ໌ຮູບຈິງ
   ========================================================================== */
$pictureDir = __DIR__ . '/picture/uploads/';   // path filesystem (ໃຊ້ file_exists)
$pictureUrl = 'picture/uploads/';              // path URL (ໃຊ້ໃສ່ <img src>)
$imageMapFile = $pictureDir . 'filemap.json';

$imageMap = [];   // key = Item_code (lowercase, trim) => safeName ຮູບ
if (file_exists($imageMapFile)) {
    $decodedMap = json_decode(file_get_contents($imageMapFile), true);
    if (is_array($decodedMap)) {
        foreach ($decodedMap as $safeName => $displayName) {
            $key = strtolower(trim($displayName));
            if ($key !== '') {
                $imageMap[$key] = $safeName;
            }
        }
    }
}

/**
 * ຫາ path ຮູບອຸປະກອນຈາກ Item_code
 * ຖ້າພົບໃນ picture/uploads/ ໃຫ້ໃຊ້ຮູບນັ້ນ, ຖ້າບໍ່ພົບ fallback ໄປໃຊ້ column picture ໃນ DB
 */
function getStockImage($itemCode, $imageMap, $pictureDir, $pictureUrl, $fallbackPicture = '')
{
    $key = strtolower(trim($itemCode));
    if (isset($imageMap[$key])) {
        $safeName = $imageMap[$key];
        $fullPath = $pictureDir . $safeName;
        if (file_exists($fullPath)) {
            // ຕິດ ?v=timestamp ເພື່ອບໍ່ໃຫ້ browser ສະແດງຮູບເກົ່າຈາກ cache
            // ຫຼັງຈາກຮູບຖືກປ່ຽນແທນຈາກ picture/index.php
            return $pictureUrl . $safeName . '?v=' . filemtime($fullPath);
        }
    }
    return $fallbackPicture; // ບໍ່ພົບໃນລະບົບ upload -> ໃຊ້ຄ່າເດີມຈາກ DB (ຫຼືຫວ່າງ)
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <?php include __DIR__ . '/includes/head_cdn.php'; ?>

    <title>ສາງອຸປະກອນ</title>

    <style type="text/css">
        .cooloorr {
            background-image: linear-gradient(to top, #accbee 0%, #e7f0fd 100%);
        }

        .item-details {
            line-height: 1.45;
        }

        .item-details .item-title {
            font-size: 15px;
            margin-bottom: 2px;
        }

        .item-details .item-line {
            font-size: 13px;
        }

        .item-details i {
            width: 18px;
            color: #111;
        }

        .legacy-item-details {
            display: none;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>

    <!-- Sidebar-->

    <!-- Page content wrapper-->

    <!-- Top navigation-->

    <!-- Page content-->

    <table class="table table-bordered  ">
        <thead>
            <tr class="text-primary cooloorr" style="font-size: 15px;">
                <th width="10%">Item Code</th>
                <th width="20%">Items</th>
                <!-- <th>ພາກສ່ວນ</th>-->
                <th width="10%" data-translate="store_info">ລາຄາຕໍ່ຫົວໜ່ວຍ</th>

                <th width="20%" data-translate="stock_quantity">ຈຳນວນໃນ Stoc</th>
                <th width="20%" data-translate="hq_stock_quantity">ຈຳນວນສາງສຳນັກງານໃຫຍ່</th>

                <th width="20%" data-translate="equipment_image">ຮູບອູປະກອນ</th>


            </tr>
        </thead>


        <tbody>
            <?php
            $conns = $conn;

            $input               = $_POST["input"] ?? '';
            $prostock            = $_POST['province'] ?? '';
            $Section             = $_POST["Section"] ?? '';
            $resutl              = $_POST["resutl"] ?? '';
            $prostock_province   = $_POST['province'] ?? '';
            $SectionUseGroup     = $_POST['SectionUseGroup'] ?? '';

            $hasGroup = !empty($SectionUseGroup) && $SectionUseGroup != 'ໃຊ້ກັບກຸ່ມ';

            /* ==========================================================================
               ສ້າງ SQL ດ້ວຍ prepared statement (WHERE clause ແບບ dynamic ແຕ່ປອດໄພ)
               - $table ຖືກເລືອກຈາກ 2 ຄ່າຄົງທີ່ເທົ່ານັ້ນ (stock / stock_province) ບໍ່ແມ່ນ user input ໂດຍກົງ
               - ທຸກຄ່າຈາກ $_POST ຖືກ bind ຜ່ານ ? placeholder, ບໍ່ຕໍ່ string ໃສ່ SQL ອີກຕໍ່ໄປ
               - $resutl (comparison ເຊັ່ນ '>5') ຖືກກວດ format ດ້ວຍ regex ກ່ອນນຳໃຊ້ ເພື່ອປິດຊ່ອງ SQL Injection ເກົ່າ
               ========================================================================== */

            $isHQ  = ($prostock === 'Stock Sanakham');
            $table = $isHQ ? 'stock' : 'stock_province';
            $selectFields = $isHQ
                ? 'stock.*'
                : 'stock_province.*, stock.Item_name_Chinese, stock.Size, stock.Model';
            $fromClause = $isHQ
                ? 'stock'
                : 'stock_province LEFT JOIN stock ON stock.Items = stock_province.Items';

            $where  = [];
            $params = [];
            $types  = '';

            if (!$isHQ) {
                $where[]  = "$table.Provinces = ?";
                $params[] = $prostock_province;
                $types   .= 's';
            }

            if ($Section !== '' && $Section !== 'ທຸກພາກສ່ວນ') {
                $where[]  = "$table.Section = ?";
                $params[] = $Section;
                $types   .= 's';
            }

            if ($input !== '') {

                $searchPattern = ($Section !== '' && $Section !== 'ທຸກພາກສ່ວນ')
                    ? $input . '%'
                    : '%' . $input . '%';

                if ($isHQ) {

                    $where[] = "(
                        $table.Item_code LIKE ?
                        OR $table.Items LIKE ?
                        OR $table.Item_name_Chinese LIKE ?
                        OR $table.Type LIKE ?
                        OR $table.Size LIKE ?
                        OR $table.Model LIKE ?
                    )";

                    array_push(
                        $params,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern
                    );

                    $types .= "ssssss";

                } else {

                    $where[] = "(
                        $table.Item_code LIKE ?
                        OR $table.Items LIKE ?
                        OR stock.Item_name_Chinese LIKE ?
                        OR $table.Type LIKE ?
                        OR stock.Size LIKE ?
                        OR stock.Model LIKE ?
                    )";

                    array_push(
                        $params,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern,
                        $searchPattern
                    );

                    $types .= "ssssss";
                }
            }

            if ($hasGroup) {
                $where[]  = "$table.Groupp = ?";
                $params[] = $SectionUseGroup;
                $types   .= 's';
            }

            if ($resutl !== '' && $resutl !== 'all') {
                // ອະນຸຍາດແຕ່ຮູບແບບ operator+ຕົວເລກ ເຊັ່ນ '>5', '<=10', '=0' ເທົ່ານັ້ນ
                if (preg_match('/^(<=|>=|=|<|>)\s*(\d+(\.\d+)?)$/', trim($resutl), $m)) {
                    $where[]  = "$table.Unit " . $m[1] . " ?";
                    $params[] = $m[2];
                    $types   .= 'd';
                }
                // ຖ້າ format ບໍ່ຖືກຕ້ອງ: ບໍ່ໃສ່ filter ນີ້ (ບໍ່ die, ບໍ່ inject)
            }

            $sql2 = "SELECT $selectFields FROM $fromClause";
            if (!empty($where)) {
                $sql2 .= " WHERE " . implode(' AND ', $where);
            }

            $result23 = null;
            $stmt2 = $conns->prepare($sql2);

            if ($stmt2 === false) {
                die("Error preparing query: " . $conns->error);
            }

            if (!empty($params)) {
                $bindArgs = [];
                $bindArgs[] = $types;
                foreach ($params as $k => $v) {
                    $bindArgs[] = &$params[$k];
                }
                call_user_func_array([$stmt2, 'bind_param'], $bindArgs);
            }

            $stmt2->execute();
            $result23 = $stmt2->get_result();

            if ($result23 instanceof mysqli_result) {
                while ($row23 = $result23->fetch_assoc()) {
                    $Itemms = $row23['Items'];
                    $id = $row23['id'];

                    $unit = $row23['Unit'];
                    $stock_tmd = $row23['Stock_TMD'] ?? 0;

                    if ($unit <= 5 && $stock_tmd <= 5) {
                        $bgcolor = "#F0C0C1";
                        $cl = "red";
                    } else {
                        $bgcolor = "#FFFFFF";
                        $cl = "black";
                    }

                    // ຫາຮູບອຸປະກອນຈາກລະບົບ upload (picture/uploads) ໂດຍຈັບຄູ່ Item_code
                    $picUrl = getStockImage(
                        $row23['Item_code'] ?? '',
                        $imageMap,
                        $pictureDir,
                        $pictureUrl,
                        $row23['picture'] ?? ''
                    );
            ?>
                    <tr bgcolor="<?php echo $bgcolor ?>" style="font-size: 13px;">
                        <td width="10%"><strong><?php echo htmlspecialchars($row23['Item_code'] ?? '') ?></strong></td>
                        <td width="20%" class="item-details-cell">
                            <div class="item-details">
                                <div class="item-title">
                                    <i class="fa fa-link" aria-hidden="true"></i>
                                    <a style="color: blue" href='EditItemstock.php?Itemms=<?php echo urlencode($Itemms) ?>&id=<?php echo urlencode($id) ?>'><strong><?php echo htmlspecialchars($row23['Items'] ?? '') ?></strong></a>
                                </div>
                                <?php if (!empty($row23['Item_name_Chinese'])): ?>
                                    <div class="item-line"><i class="fa fa-globe" aria-hidden="true"></i> <?php echo htmlspecialchars($row23['Item_name_Chinese']) ?></div>
                                <?php endif; ?>
                                <div class="item-line"><i class="fa fa-arrows-h" aria-hidden="true"></i> ຂະໜາດ: <?php echo htmlspecialchars($row23['Size'] ?? '-') ?: '-' ?></div>
                                <div class="item-line"><i class="fa fa-tag" aria-hidden="true"></i> Model: <?php echo htmlspecialchars($row23['Model'] ?? '-') ?: '-' ?></div>
                                <div class="item-line"><i class="fa fa-sitemap" aria-hidden="true"></i> ປະເພດ: <?php echo htmlspecialchars($row23['Use_For'] ?? '-') ?: '-' ?></div>
                            </div>
                            <div class="legacy-item-details"><p><a style="color: blue" href='EditItemstock.php?Itemms=<?php echo urlencode($Itemms) ?>&id=<?php echo urlencode($id) ?>'>
                                    <strong><?php echo htmlspecialchars($row23['Items']) ?></a></p><i class="fa-solid fa-network-wired"></i> ອຸປະກອນ: <?php echo htmlspecialchars($row23['Use_For']) ?><br><i class="fa-duotone fa-layer-group"></i> ໃຊ້ກັບກຸ່ມ: <?php echo htmlspecialchars($row23['Groupp']) ?></strong>
                        </td>
                        <!-- ໝາຍເຫດ: column database ຊື່ 'Vendor' ແຕ່ຖືກໃຊ້ເກັບ "ລາຄາຕໍ່ຫົວໜ່ວຍ" ຕົວຈິງ (ບໍ່ແມ່ນຊື່ຜູ້ສະໜອງ) -->
                        <td width="10%"><strong><?php echo htmlspecialchars($row23['Vendor']) ?></strong></td>
                        <td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo htmlspecialchars($row23['Unit']) ?> <?php echo htmlspecialchars($row23['Type']) ?></strong></td>
                        <td width="20%" align="center" style="color: <?php echo $cl ?>"><strong><?php echo htmlspecialchars($row23['Stock_TMD'] ?? '') ?> <?php echo htmlspecialchars($row23['Type']) ?></strong></td>
                        <td width="20%">
                            <?php if (!empty($picUrl)): ?>
                                <a href="<?php echo htmlspecialchars($picUrl) ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($picUrl) ?>" style="width:128px;height:100px;object-fit:cover;border-radius:10px;border:1px solid #dee2e6;box-shadow:0 2px 6px rgba(0,0,0,0.12);"></a>
                            <?php else: ?>
                                <span class="text-muted">ບໍ່ມີຮູບ</span>
                            <?php endif; ?>
                        </td>
                    </tr>

            <?php
                }
            } else {
                echo "<tr><td colspan='6'>No results found or an unexpected error occurred.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <!-- Rest of your HTML content -->
    <!-- Bootstrap core JS-->
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <script src="js/shared.js"></script>

    <!-- Translate -->
    <script src="translate/lang.js"></script>
</body>

</html>