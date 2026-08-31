<?php
// ===========================================================================
// stock_dashboard_lib.php
// Logic ດຶງ+ຈັດຮູບຂໍ້ມູນສຳລັບ Dashboard ສາງ. ມີ 4 ຊຸດ:
//   1) getStockDashboardData()          -> ຈາກຕາຕະລາງ stock (ຕາມ Type/Groupp)
//   2) getStockProvinceDashboardData()  -> ຈາກຕາຕະລາງ stock_province (ຕາມແຂວງ, column: Provinces)
//   3) getUseStockDashboardData()       -> ຈາກຕາຕະລາງ usestock (ຍອດເບິກ/ໃຊ້, column: Provinces)
//   4) getRequestDashboardData()        -> ຈາກຕາຕະລາງ request (ຍອດຂໍເບິກ, column: Province ບໍ່ມີ s)
// ໃຊ້ຮ່ວມກັນລະຫວ່າງ StockDashboard.php (ໜ້າຫຼັກ, ມີ 4 tab) ແລະ
// stock_dashboard_data.php (endpoint ສົ່ງ JSON ສຳລັບ realtime refresh ຂອງທຸກ tab)
// ===========================================================================

function numExpr($col)
{
    return "CAST(REPLACE(REPLACE(TRIM($col), ',', ''), ' ', '') AS DECIMAL(18,2))";
}

function normText($t)
{
    $t = trim((string) $t);
    return $t === '' ? 'ບໍ່ລະບຸ' : $t;
}

function fmtNum($n)
{
    $rounded = round((float) $n, 2);
    if ($rounded == (int) $rounded) {
        return number_format((int) $rounded);
    }
    return number_format($rounded, 2);
}

/**
 * ດຶງ+ຈັດຮູບຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ stock, ຈັດກຸ່ມຕາມ Items+Groupp+Type
 * ຄືນຄ່າເປັນ array ພ້ອມໃຊ້ໄດ້ທັງ render HTML ແລະ json_encode.
 */
function getStockDashboardData($conn)
{
    $totalExpr = numExpr('Stock_TMD');

    $sql = "SELECT Items, Groupp, Type, Item_code, SUM($totalExpr) AS qty, COUNT(*) AS entryCount
            FROM stock
            GROUP BY Items, Groupp, Type, Item_code";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['error' => 'Prepare Failed: ' . $conn->error];
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    $totalAll = 0.0;
    $byType  = [];
    $byGroup = [];

    while ($row = $res->fetch_assoc()) {
        $items = normText($row['Items']);
        $group = normText($row['Groupp']);
        $type  = normText($row['Type']);
        $qty   = (float) $row['qty'];

        $rows[] = [
            'items'    => $items,
            'group'    => $group,
            'type'     => $type,
            'itemCode' => normText($row['Item_code']),
            'qty'      => $qty,
        ];

        $totalAll += $qty;

        if (!isset($byType[$type])) {
            $byType[$type] = ['type' => $type, 'qty' => 0.0, 'itemsSet' => []];
        }
        $byType[$type]['qty'] += $qty;
        $byType[$type]['itemsSet'][$items] = true;

        if (!isset($byGroup[$group])) {
            $byGroup[$group] = ['group' => $group, 'qty' => 0.0, 'itemsSet' => []];
        }
        $byGroup[$group]['qty'] += $qty;
        $byGroup[$group]['itemsSet'][$items] = true;
    }
    $stmt->close();

    foreach ($byType as $k => $v) {
        $byType[$k]['itemCount'] = count($v['itemsSet']);
        unset($byType[$k]['itemsSet']);
    }
    foreach ($byGroup as $k => $v) {
        $byGroup[$k]['itemCount'] = count($v['itemsSet']);
        unset($byGroup[$k]['itemsSet']);
    }

    ksort($byType, SORT_STRING);
    ksort($byGroup, SORT_STRING);

    usort($rows, function ($a, $b) {
        $cmp = strcmp($a['type'], $b['type']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['group'], $b['group']);
        if ($cmp !== 0) return $cmp;
        return strcmp($a['items'], $b['items']);
    });

    // Top 10 ລາຍການທີ່ມີຍອດຫຼາຍສຸດ (ສຳລັບກຣາບ)
    $topItems = $rows;
    usort($topItems, function ($a, $b) {
        return $b['qty'] <=> $a['qty'];
    });
    $topItems = array_slice($topItems, 0, 10);

    return [
        'generatedAt'    => date('Y-m-d H:i:s'),
        'totalAll'       => $totalAll,
        'totalItemCount' => count($rows),
        'typeCount'      => count($byType),
        'groupCount'     => count($byGroup),
        'byType'         => array_values($byType),
        'byGroup'        => array_values($byGroup),
        'rows'           => $rows,
        'topItems'       => $topItems,
    ];
}

/**
 * ດຶງ+ຈັດຮູບຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ stock_province, ຈັດກຸ່ມຕາມ
 * Items + Groupp + Type + Provinces. ຮອງຮັບ filter ຕາມແຂວງ (optional).
 *
 * @param mysqli $conn
 * @param string|null $provinceFilter ຊື່ແຂວງທີ່ຈະກັ່ນຕອງ (null = ເອົາໝົດ)
 */
function getStockProvinceDashboardData($conn, $provinceFilter = null)
{
    $qtyExpr = numExpr('Unit');

    $sql = "SELECT Items, Groupp, Type, Item_code, Vendor, Use_For, Section, Provinces,
                   SUM($qtyExpr) AS qty, COUNT(*) AS entryCount
            FROM stock_province";

    $params = [];
    $types  = '';
    if (!empty($provinceFilter)) {
        $sql .= " WHERE TRIM(Provinces) = ?";
        $params[] = trim($provinceFilter);
        $types .= 's';
    }

    $sql .= " GROUP BY Items, Groupp, Type, Item_code, Vendor, Use_For, Section, Provinces";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['error' => 'Prepare Failed: ' . $conn->error];
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    $totalAll = 0.0;
    $byType     = [];
    $byGroup    = [];
    $byProvince = [];

    while ($row = $res->fetch_assoc()) {
        $items    = normText($row['Items']);
        $group    = normText($row['Groupp']);
        $type     = normText($row['Type']);
        $province = normText($row['Provinces']);
        $qty      = (float) $row['qty'];

        $rows[] = [
            'items'    => $items,
            'group'    => $group,
            'type'     => $type,
            'province' => $province,
            'itemCode' => normText($row['Item_code']),
            'section'  => normText($row['Section']),
            'qty'      => $qty,
        ];

        $totalAll += $qty;

        if (!isset($byType[$type])) {
            $byType[$type] = ['type' => $type, 'qty' => 0.0, 'itemsSet' => []];
        }
        $byType[$type]['qty'] += $qty;
        $byType[$type]['itemsSet'][$items] = true;

        if (!isset($byGroup[$group])) {
            $byGroup[$group] = ['group' => $group, 'qty' => 0.0, 'itemsSet' => []];
        }
        $byGroup[$group]['qty'] += $qty;
        $byGroup[$group]['itemsSet'][$items] = true;

        if (!isset($byProvince[$province])) {
            $byProvince[$province] = ['province' => $province, 'qty' => 0.0, 'itemsSet' => [], 'groupsSet' => []];
        }
        $byProvince[$province]['qty'] += $qty;
        $byProvince[$province]['itemsSet'][$items] = true;
        $byProvince[$province]['groupsSet'][$group] = true;
    }
    $stmt->close();

    foreach ($byType as $k => $v) {
        $byType[$k]['itemCount'] = count($v['itemsSet']);
        unset($byType[$k]['itemsSet']);
    }
    foreach ($byGroup as $k => $v) {
        $byGroup[$k]['itemCount'] = count($v['itemsSet']);
        unset($byGroup[$k]['itemsSet']);
    }
    foreach ($byProvince as $k => $v) {
        $byProvince[$k]['itemCount']  = count($v['itemsSet']);
        $byProvince[$k]['groupCount'] = count($v['groupsSet']);
        unset($byProvince[$k]['itemsSet'], $byProvince[$k]['groupsSet']);
    }

    ksort($byType, SORT_STRING);
    ksort($byGroup, SORT_STRING);

    // ຈັດອັນດັບແຂວງຕາມຍອດ (ຫຼາຍ -> ໜ້ອຍ) ໃຫ້ກຣາບເບິ່ງງ່າຍ
    $byProvince = array_values($byProvince);
    usort($byProvince, function ($a, $b) { return $b['qty'] <=> $a['qty']; });

    usort($rows, function ($a, $b) {
        $cmp = strcmp($a['province'], $b['province']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['type'], $b['type']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['group'], $b['group']);
        if ($cmp !== 0) return $cmp;
        return strcmp($a['items'], $b['items']);
    });

    // Top 10 ລາຍການທີ່ມີຍອດຫຼາຍສຸດ (ສຳລັບກຣາບ)
    $topItems = $rows;
    usort($topItems, function ($a, $b) { return $b['qty'] <=> $a['qty']; });
    $topItems = array_slice($topItems, 0, 10);

    // ລາຍຊື່ແຂວງທັງໝົດ (ສຳລັບ pill filter) - ດຶງແຍກຕ່າງຫາກ ບໍ່ຂຶ້ນກັບ filter
    $provinceList = [];
    $r2 = $conn->query("SELECT DISTINCT TRIM(Provinces) AS p FROM stock_province WHERE TRIM(Provinces) <> '' ORDER BY p ASC");
    if ($r2) {
        while ($r = $r2->fetch_assoc()) {
            $provinceList[] = $r['p'];
        }
    }

    return [
        'generatedAt'     => date('Y-m-d H:i:s'),
        'totalAll'        => $totalAll,
        'totalItemCount'  => count($rows),
        'typeCount'       => count($byType),
        'groupCount'      => count($byGroup),
        'provinceCount'   => count($byProvince),
        'byType'          => array_values($byType),
        'byGroup'         => array_values($byGroup),
        'byProvince'      => $byProvince,
        'rows'            => $rows,
        'topItems'        => $topItems,
        'provinceList'    => $provinceList,
        'activeProvince'  => $provinceFilter ?: null,
    ];
}

/**
 * ດຶງ+ຈັດຮູບຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ usestock (ຍອດເບິກ/ໃຊ້ອຸປະກອນ),
 * ຈັດກຸ່ມຕາມ Items+Provinces+Type ພ້ອມ trend ຕາມວັນທີ່ (Date).
 * ຮອງຮັບ filter ຕາມແຂວງ (optional).
 *
 * @param mysqli $conn
 * @param string|null $provinceFilter ຊື່ແຂວງທີ່ຈະກັ່ນຕອງ (null = ເອົາໝົດ)
 */
function getUseStockDashboardData($conn, $provinceFilter = null)
{
    $qtyExpr = numExpr('Unit');

    $sql = "SELECT Items, Provinces, Type, Date,
                   SUM($qtyExpr) AS qty, COUNT(*) AS entryCount
            FROM usestock";

    $params = [];
    $types  = '';
    if (!empty($provinceFilter)) {
        $sql .= " WHERE TRIM(Provinces) = ?";
        $params[] = trim($provinceFilter);
        $types .= 's';
    }

    $sql .= " GROUP BY Items, Provinces, Type, Date";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['error' => 'Prepare Failed: ' . $conn->error];
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $rowsRaw = [];
    while ($row = $res->fetch_assoc()) {
        $rowsRaw[] = $row;
    }
    $stmt->close();

    // ລວມ Items+Provinces+Type ຄືນ (ຮວມທຸກວັນທີ່ເຂົ້າກັນ) ສຳລັບຕາຕະລາງ/ກຣາບຫຼັກ
    $itemsMap   = [];
    $byType     = [];
    $byProvince = [];
    $byDate     = [];
    $totalAll   = 0.0;

    foreach ($rowsRaw as $row) {
        $items    = normText($row['Items']);
        $type     = normText($row['Type']);
        $province = normText($row['Provinces']);
        $date     = (string) $row['Date'];
        $qty      = (float) $row['qty'];

        $key = $items . '|' . $province . '|' . $type;
        if (!isset($itemsMap[$key])) {
            $itemsMap[$key] = ['items' => $items, 'province' => $province, 'type' => $type, 'qty' => 0.0, 'lastDate' => $date];
        }
        $itemsMap[$key]['qty'] += $qty;
        if ($date > $itemsMap[$key]['lastDate']) {
            $itemsMap[$key]['lastDate'] = $date;
        }

        $totalAll += $qty;

        if (!isset($byType[$type])) {
            $byType[$type] = ['type' => $type, 'qty' => 0.0, 'itemsSet' => []];
        }
        $byType[$type]['qty'] += $qty;
        $byType[$type]['itemsSet'][$items] = true;

        if (!isset($byProvince[$province])) {
            $byProvince[$province] = ['province' => $province, 'qty' => 0.0, 'itemsSet' => [], 'typesSet' => []];
        }
        $byProvince[$province]['qty'] += $qty;
        $byProvince[$province]['itemsSet'][$items] = true;
        $byProvince[$province]['typesSet'][$type] = true;

        // trend ຕາມວັນທີ່ (ຂ້າມວັນທີ່ວ່າງ / 0000-00-00)
        if ($date !== '' && $date !== '0000-00-00') {
            if (!isset($byDate[$date])) {
                $byDate[$date] = 0.0;
            }
            $byDate[$date] += $qty;
        }
    }

    $rows = array_values($itemsMap);

    foreach ($byType as $k => $v) {
        $byType[$k]['itemCount'] = count($v['itemsSet']);
        unset($byType[$k]['itemsSet']);
    }
    foreach ($byProvince as $k => $v) {
        $byProvince[$k]['itemCount'] = count($v['itemsSet']);
        $byProvince[$k]['typeCount'] = count($v['typesSet']);
        unset($byProvince[$k]['itemsSet'], $byProvince[$k]['typesSet']);
    }

    ksort($byType, SORT_STRING);
    $byProvince = array_values($byProvince);
    usort($byProvince, function ($a, $b) { return $b['qty'] <=> $a['qty']; });

    usort($rows, function ($a, $b) {
        $cmp = strcmp($a['province'], $b['province']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['type'], $b['type']);
        if ($cmp !== 0) return $cmp;
        return strcmp($a['items'], $b['items']);
    });

    $topItems = $rows;
    usort($topItems, function ($a, $b) { return $b['qty'] <=> $a['qty']; });
    $topItems = array_slice($topItems, 0, 10);

    ksort($byDate, SORT_STRING);
    // ເອົາສະເພາະ 30 ວັນລ່າສຸດ ໃຫ້ trend chart ບໍ່ແໜ້ນເກີນໄປ
    $trendDates = array_slice(array_keys($byDate), -30);
    $trend = [];
    foreach ($trendDates as $d) {
        $trend[] = ['date' => $d, 'qty' => $byDate[$d]];
    }

    $provinceList = [];
    $r2 = $conn->query("SELECT DISTINCT TRIM(Provinces) AS p FROM usestock WHERE TRIM(Provinces) <> '' ORDER BY p ASC");
    if ($r2) {
        while ($r = $r2->fetch_assoc()) {
            $provinceList[] = $r['p'];
        }
    }

    return [
        'generatedAt'    => date('Y-m-d H:i:s'),
        'totalAll'       => $totalAll,
        'totalItemCount' => count($rows),
        'typeCount'      => count($byType),
        'provinceCount'  => count($byProvince),
        'byType'         => array_values($byType),
        'byProvince'     => $byProvince,
        'rows'           => $rows,
        'topItems'       => $topItems,
        'trend'          => $trend,
        'provinceList'   => $provinceList,
        'activeProvince' => $provinceFilter ?: null,
    ];
}

/**
 * ດຶງ+ຈັດຮູບຂໍ້ມູນທັງໝົດຈາກຕາຕະລາງ request (ຍອດຂໍເບິກອຸປະກອນ),
 * ຈັດກຸ່ມຕາມ Items + Groupp + Type + Province (ໝາຍເຫດ: column ຊື່ "Province" ບໍ່ມີ s
 * ຕ່າງຈາກ stock_province / usestock ທີ່ໃຊ້ "Provinces").
 * ຄິດໄລ່ທັງ "ຂໍເບິກ" (Unit), "ອະນຸມັດ/ຈ່າຍແລ້ວ" (Give) ແລະ "ຄ້າງ" (Unit - Give).
 * ຮອງຮັບ filter ຕາມແຂວງ (optional).
 *
 * @param mysqli $conn
 * @param string|null $provinceFilter ຊື່ແຂວງທີ່ຈະກັ່ນຕອງ (null = ເອົາໝົດ)
 */
function getRequestDashboardData($conn, $provinceFilter = null)
{
    $unitExpr = numExpr('Unit');
    $giveExpr = numExpr('Give');

    $sql = "SELECT Items, Groupp, Type, Item_code, Vendor, Use_For, Section, Province,
                   SUM($unitExpr) AS qtyRequest, SUM($giveExpr) AS qtyGive, COUNT(*) AS entryCount
            FROM request";

    $params = [];
    $types  = '';
    if (!empty($provinceFilter)) {
        $sql .= " WHERE TRIM(Province) = ?";
        $params[] = trim($provinceFilter);
        $types .= 's';
    }

    $sql .= " GROUP BY Items, Groupp, Type, Item_code, Vendor, Use_For, Section, Province";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['error' => 'Prepare Failed: ' . $conn->error];
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    $totalRequest = 0.0;
    $totalGive    = 0.0;
    $byType     = [];
    $byGroup    = [];
    $byProvince = [];

    while ($row = $res->fetch_assoc()) {
        $items      = normText($row['Items']);
        $group      = normText($row['Groupp']);
        $type       = normText($row['Type']);
        $province   = normText($row['Province']);
        $qtyRequest = (float) $row['qtyRequest'];
        $qtyGive    = (float) $row['qtyGive'];
        $qtyPending = $qtyRequest - $qtyGive;

        $rows[] = [
            'items'      => $items,
            'group'      => $group,
            'type'       => $type,
            'province'   => $province,
            'itemCode'   => normText($row['Item_code']),
            'vendor'     => normText($row['Vendor']),
            'useFor'     => normText($row['Use_For']),
            'section'    => normText($row['Section']),
            'qty'        => $qtyRequest,
            'qtyGive'    => $qtyGive,
            'qtyPending' => $qtyPending,
        ];

        $totalRequest += $qtyRequest;
        $totalGive    += $qtyGive;

        if (!isset($byType[$type])) {
            $byType[$type] = ['type' => $type, 'qty' => 0.0, 'qtyGive' => 0.0, 'itemsSet' => []];
        }
        $byType[$type]['qty']     += $qtyRequest;
        $byType[$type]['qtyGive'] += $qtyGive;
        $byType[$type]['itemsSet'][$items] = true;

        if (!isset($byGroup[$group])) {
            $byGroup[$group] = ['group' => $group, 'qty' => 0.0, 'qtyGive' => 0.0, 'itemsSet' => []];
        }
        $byGroup[$group]['qty']     += $qtyRequest;
        $byGroup[$group]['qtyGive'] += $qtyGive;
        $byGroup[$group]['itemsSet'][$items] = true;

        if (!isset($byProvince[$province])) {
            $byProvince[$province] = ['province' => $province, 'qty' => 0.0, 'qtyGive' => 0.0, 'itemsSet' => [], 'groupsSet' => []];
        }
        $byProvince[$province]['qty']     += $qtyRequest;
        $byProvince[$province]['qtyGive'] += $qtyGive;
        $byProvince[$province]['itemsSet'][$items] = true;
        $byProvince[$province]['groupsSet'][$group] = true;
    }
    $stmt->close();

    foreach ($byType as $k => $v) {
        $byType[$k]['itemCount'] = count($v['itemsSet']);
        unset($byType[$k]['itemsSet']);
    }
    foreach ($byGroup as $k => $v) {
        $byGroup[$k]['itemCount'] = count($v['itemsSet']);
        unset($byGroup[$k]['itemsSet']);
    }
    foreach ($byProvince as $k => $v) {
        $byProvince[$k]['itemCount']  = count($v['itemsSet']);
        $byProvince[$k]['groupCount'] = count($v['groupsSet']);
        unset($byProvince[$k]['itemsSet'], $byProvince[$k]['groupsSet']);
    }

    ksort($byType, SORT_STRING);
    ksort($byGroup, SORT_STRING);

    $byProvince = array_values($byProvince);
    usort($byProvince, function ($a, $b) { return $b['qty'] <=> $a['qty']; });

    usort($rows, function ($a, $b) {
        $cmp = strcmp($a['province'], $b['province']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['type'], $b['type']);
        if ($cmp !== 0) return $cmp;
        $cmp = strcmp($a['group'], $b['group']);
        if ($cmp !== 0) return $cmp;
        return strcmp($a['items'], $b['items']);
    });

    $topItems = $rows;
    usort($topItems, function ($a, $b) { return $b['qty'] <=> $a['qty']; });
    $topItems = array_slice($topItems, 0, 10);

    // ລາຍຊື່ແຂວງທັງໝົດ (ສຳລັບ pill filter) - column "Province" ບໍ່ມີ s
    $provinceList = [];
    $r2 = $conn->query("SELECT DISTINCT TRIM(Province) AS p FROM request WHERE TRIM(Province) <> '' ORDER BY p ASC");
    if ($r2) {
        while ($r = $r2->fetch_assoc()) {
            $provinceList[] = $r['p'];
        }
    }

    return [
        'generatedAt'    => date('Y-m-d H:i:s'),
        'totalAll'       => $totalRequest,
        'totalGive'      => $totalGive,
        'totalPending'   => $totalRequest - $totalGive,
        'totalItemCount' => count($rows),
        'typeCount'      => count($byType),
        'groupCount'     => count($byGroup),
        'provinceCount'  => count($byProvince),
        'byType'         => array_values($byType),
        'byGroup'        => array_values($byGroup),
        'byProvince'     => $byProvince,
        'rows'           => $rows,
        'topItems'       => $topItems,
        'provinceList'   => $provinceList,
        'activeProvince' => $provinceFilter ?: null,
    ];
}