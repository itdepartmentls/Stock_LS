<?php
/**
 * ReporDataTotalSummary.php
 * -------------------------------------------------------------
 * Endpoint (JSON) ສຳລັບແທັບ "ສະຫຼຸບການລາຍງານ" ໃນ Report.php.
 * ຮັບ Pro (ຊື່ແຂວງ) ແລ້ວສົ່ງກັບ 4 ລາຍການ, ຈັດກຸ່ມຕາມ Items:
 *   - request  : ຂໍເບິກ      (ຕາຕະລາງ request, ນັບຈຳນວນຄຳຮ້ອງ)
 *   - order    : ສົ່ງເຄື່ອງ  (ຕາຕະລາງ tacking ທີ່ມີເລກທີ OA ແລ້ວ)
 *   - receive  : ຮັບເຄື່ອງ   (ຕາຕະລາງ stock_provinceinput)
 *   - use      : ນຳໃຊ້       (ຕາຕະລາງ usestock)
 *
 * -----------------------------------------------------------------------
 * ປັບປຸງ (ຮູບພາບ): ຄືເດີມ, ຮູບພາບຫາຜ່ານ 2 ຂັ້ນຕອນ:
 *   ຊື່ Item (request.Items) -> Item_code (ຈາກ stock / stock_province)
 *     -> ຮູບພາບ (ຈາກ picture/uploads/filemap.json)
 * -----------------------------------------------------------------------
 *
 * ປັບປຸງ (ວັນທີ): ຄືເດີມ, ໃຊ້ MAX() ຮ່ວມກັບ GROUP BY ເພື່ອສະແດງວັນທີ່ລ້າສຸດ
 * ຂອງແຕ່ລະ Item ໃນກຸ່ມນັ້ນ, format ເປັນ d/m/Y (key: "date").
 * -----------------------------------------------------------------------
 *
 * ປັບປຸງ (ແຜນ / planing_number) — *** ສຳຄັນ, ແກ້ໄຂຄັ້ງນີ້ ***:
 * ກ່ອນໜ້ານີ້ "ແຜນ" (ແຜນ1/ແຜນ2/ແຜນ3) ຖືກຄິດໄລ່ໂດຍການ join OA ຫາ VIEW
 * `oa_plan_map` (ຫຼື fallback ເດົາຈາກໂຕອັກສອນທຳອິດຂອງ OA) — ວິທີນີ້ຜິດພາດ
 * ຮ້າຍແຮງ ເພາະ OA/PR ເກືອບທັງໝົດຂຶ້ນຕົ້ນດ້ວຍເລກ "1" ຄືກັນ, ພາໃຫ້ GROUP BY
 * ລວມທຸກແຜນເຂົ້າກຸ່ມດຽວ ແລ້ວ SUM(QTY) ບວມຜິດປົກກະຕິ (ຄືບັນຫາທີ່ພົບໃນ "ແຜນ1").
 *
 * ແກ້ໄຂໃໝ່: ບໍ່ເດົາ ແລະ ບໍ່ join ຫາ OA ຂອງຕາຕະລາງອື່ນອີກຕໍ່ໄປ. ແຕ່ລະຕາຕະລາງ
 * ມີ column ແຜນຂອງຕົນເອງຢູ່ແລ້ວ (ເກັບເປັນຕົວເລກ 1 / 2 / 3 ໃນ DB ເພື່ອຄວາມງ່າຍ):
 *   - request.planing_number
 *   - tacking.Track_planing_number
 *   - stock_provinceinput.planing_number
 *   - usestock.planing_number
 * ຄ່າຕົວເລກ (1/2/3) ຖືກແປງເປັນ 'ແຜນ1'/'ແຜນ2'/'ແຜນ3' ໂດຍກົງໃນ SQL (CASE),
 * ແລ້ວ GROUP BY ດ້ວຍ expression ດຽວກັນນັ້ນເລີຍ (ບໍ່ຕ້ອງ join, ບໍ່ຕ້ອງ VIEW,
 * ບໍ່ຕ້ອງ fallback ອີກຕໍ່ໄປ).
 * -----------------------------------------------------------------------
 *
 * ============= CONFIG: ປັບບ່ອນນີ້ຖ້າ path/ໂຄງສ້າງໄຟລ໌ຮູບຕ່າງອອກໄປ =============
 */
const PICTURE_UPLOAD_DIR  = __DIR__ . '/picture/uploads/';   // ໂຟນເດີເກັບໄຟລ໌ຮູບ (ຝັ່ງ server/filesystem)
const PICTURE_MAP_FILE    = PICTURE_UPLOAD_DIR . 'filemap.json'; // safeName => Item_code
const PICTURE_IMAGE_BASE  = 'picture/uploads/';               // path ທີ່ໃຊ້ຕໍ່ໜ້າຊື່ໄຟລ໌ ຕອນສ້າງ URL ໃຫ້ browser

/**
 * ບາງລະບົບ ຊື່ແຂວງໃນ request.Province ອາດຂຽນບໍ່ຄືກັນກັບ tacking.Factory_To
 * (ຕົວຢ່າງ: request ໃຊ້ "Luangphabang" ແຕ່ tacking ໃຊ້ "LPB" ຫຼື "Luang Prabang").
 * ຖ້າເປັນແບບນັ້ນ, ເພີ່ມ mapping ຢູ່ນີ້: 'ຊື່ໃນປຸ່ມແຂວງ' => 'ຄ່າຈິງໃນ tacking.Factory_To'
 */
const PROVINCE_TO_FACTORY_MAP = [
    'Luangphabang' => 'MuangNan',
    // ຖ້າມີແຂວງ/ສາງອື່ນທີ່ຊື່ບໍ່ກົງກັນອີກ, ເພີ່ມແຖວຄືແບບນີ້
];
/* ======================================================================== */

require_once __DIR__ . '/conn.php';
@session_start();

header('Content-Type: application/json; charset=utf-8');

if (($_SESSION["user"] ?? '') == '') {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

mysqli_set_charset($conn, 'utf8');

$Pro = $_GET['Pro'] ?? ($_POST['Pro'] ?? '');

/**
 * ແປງຄ່າຕົວເລກ (1/2/3) ຂອງ column ແຜນ ໃຫ້ເປັນ SQL CASE expression
 * ('ແຜນ1'/'ແຜນ2'/'ແຜນ3'). ຖ້າຄ່າວ່າງເປົ່າ/NULL/ບໍ່ຮູ້ຈັກ ຈະສົ່ງເປັນ
 * 'ບໍ່ລະບຸ' (ບໍ່ເດົາອີກຕໍ່ໄປ) ເພື່ອໃຫ້ເຫັນຂໍ້ມູນທີ່ຂາດຫາຍໄດ້ຊັດເຈນ
 * ແທນທີ່ຈະຖືກປົນເຂົ້າ "ແຜນ1" ແບບຜິດໆຄືເດີມ.
 *
 * ໝາຍເຫດ: TRIM() ກັນຊ່ອງຫວ່າງ, ໃຊ້ທຽບແບບ string ('1','2','3') ເພື່ອຮອງຮັບ
 * ທັງ column ປະເພດ INT ແລະ VARCHAR.
 */
function plan_case_expr(string $col): string
{
    return "CASE
                WHEN TRIM(a.$col) = '3' THEN 'ແຜນ3'
                WHEN TRIM(a.$col) = '2' THEN 'ແຜນ2'
                WHEN TRIM(a.$col) = '1' THEN 'ແຜນ1'
                WHEN TRIM(a.$col) = 'ປົກກະຕິ' THEN 'ປົກກະຕິ'
                ELSE 'ບໍ່ລະບຸ'
            END";
}

/**
 * ໂຫຼດ filemap.json ແລ້ວສ້າງ index ແບບ "Item_code (ຕົວພິມນ້ອຍ, TRIM)" => safeName
 * ໃຊ້ static cache ເພື່ອອ່ານໄຟລ໌ພຽງເທື່ອດຽວຕໍ່ request ນີ້.
 *
 * @return array<string,string>
 */
function load_picture_filemap(): array
{
    static $reverseMap = null;
    if ($reverseMap === null) {
        $reverseMap = [];
        if (file_exists(PICTURE_MAP_FILE)) {
            $decoded = json_decode((string) file_get_contents(PICTURE_MAP_FILE), true);
            if (is_array($decoded)) {
                foreach ($decoded as $safeName => $itemCode) {
                    if ($itemCode === null || $itemCode === '') {
                        continue;
                    }
                    $key = mb_strtolower(trim((string) $itemCode));
                    if ($key === '') {
                        continue;
                    }
                    $reverseMap[$key] = (string) $safeName;
                }
            }
        }
    }
    return $reverseMap;
}

/**
 * ໂຫຼດ mapping "ຊື່ Item (ຕົວພິມນ້ອຍ, TRIM)" => "Item_code" ຈາກຕາຕະລາງ
 * stock (ສາງ HQ) ແລະ stock_province (ສາງແຂວງ) ລວມກັນ.
 *
 * @return array<string,string>
 */
function load_item_name_to_code_map(mysqli $conn): array
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $sql = "SELECT Items, Item_code FROM stock WHERE Item_code IS NOT NULL AND Item_code <> ''
                UNION
                SELECT Items, Item_code FROM stock_province WHERE Item_code IS NOT NULL AND Item_code <> ''";
        $r = mysqli_query($conn, $sql);
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $key = mb_strtolower(trim((string) $row['Items']));
                if ($key === '') {
                    continue;
                }
                if (!isset($map[$key])) {
                    $map[$key] = (string) $row['Item_code'];
                }
            }
        }
    }
    return $map;
}

/**
 * ຄົ້ນຫາ safeName ຂອງຮູບພາບ ຈາກຊື່ອຸປະກອນ (Item).
 */
function lookup_picture_image(mysqli $conn, string $itemName): ?string
{
    $nameToCode = load_item_name_to_code_map($conn);
    $nameKey = mb_strtolower(trim($itemName));
    if ($nameKey === '' || !isset($nameToCode[$nameKey])) {
        return null;
    }
    $itemCode = $nameToCode[$nameKey];

    $codeToFile = load_picture_filemap();
    $codeKey = mb_strtolower(trim($itemCode));
    if ($codeKey === '' || !isset($codeToFile[$codeKey])) {
        return null;
    }

    $safeName = $codeToFile[$codeKey];
    if (!file_exists(PICTURE_UPLOAD_DIR . $safeName)) {
        return null;
    }
    return $safeName;
}

/**
 * ໂໝດກວດສອບ: ເປີດດ້ວຍ ...?debug=1 (ບໍ່ຕ້ອງໃສ່ Pro ກໍ່ໄດ້)
 * ສະແດງຄ່າແຜນທີ່ພົບຈິງໃນແຕ່ລະຕາຕະລາງ (distinct values ຂອງ column ແຜນ
 * ຂອງມັນເອງ) ພ້ອມນັບຈຳນວນແຖວທີ່ "ບໍ່ລະບຸ" (ຄ່າວ່າງ/ບໍ່ຮູ້ຈັກ) ເພື່ອຊ່ວຍ
 * ກວດຄວາມສະອາດຂອງຂໍ້ມູນ.
 */
if (($_GET['debug'] ?? '') === '1') {
    $provinces = [];
    $r = mysqli_query($conn, "SELECT DISTINCT Province FROM request WHERE Province IS NOT NULL AND Province <> '' ORDER BY Province");
    if ($r) { while ($row = mysqli_fetch_assoc($r)) { $provinces[] = $row['Province']; } }

    $factories = [];
    $r2 = mysqli_query($conn, "SELECT DISTINCT Factory_To FROM tacking WHERE Factory_To IS NOT NULL AND Factory_To <> '' ORDER BY Factory_To");
    if ($r2) { while ($row = mysqli_fetch_assoc($r2)) { $factories[] = $row['Factory_To']; } }

    // ---- ກວດຄ່າແຜນຈິງຂອງແຕ່ລະຕາຕະລາງ (distinct raw values + ຈຳນວນທີ່ບໍ່ລະບຸ) ----
    $planColumns = [
        'request'              => 'planing_number',
        'tacking'               => 'Track_planing_number',
        'stock_provinceinput'   => 'planing_number',
        'usestock'              => 'planing_number',
    ];
    $planCheck = [];
    foreach ($planColumns as $tbl => $col) {
        $distinct = [];
        $rd = mysqli_query($conn, "SELECT DISTINCT TRIM($col) AS v, COUNT(*) AS c FROM $tbl GROUP BY TRIM($col) ORDER BY c DESC LIMIT 20");
        if ($rd) {
            while ($row = mysqli_fetch_assoc($rd)) {
                $distinct[] = ['value' => $row['v'], 'count' => (int) $row['c']];
            }
        }
        $unspecified = 0;
        $ru = mysqli_query($conn, "SELECT COUNT(*) c FROM $tbl WHERE $col IS NULL OR TRIM($col) NOT IN ('1','2','3')");
        if ($ru) { $unspecified = (int) (mysqli_fetch_assoc($ru)['c'] ?? 0); }
        $planCheck[$tbl] = [
            'column'               => $col,
            'distinct_values_sample' => $distinct,
            'rows_not_1_2_or_3'    => $unspecified,
        ];
    }

    // ---- ກວດ filemap.json ----
    $mapFileExists   = file_exists(PICTURE_MAP_FILE);
    $codeToFile      = load_picture_filemap();
    $pictureCodesSample = array_slice(array_keys($codeToFile), 0, 5);
    $pictureCodesCount  = count($codeToFile);

    $nameToCode = load_item_name_to_code_map($conn);
    $nameToCodeCount = count($nameToCode);
    $nameToCodeSample = array_slice($nameToCode, 0, 5, true);

    // ---- ກວດ column ຈຳນວນຕົວຈິງທີ່ຜູ້ໃຊ້ແຈ້ງມາ (ກ່ອນຕັດສິນໃຈ SUM) ----
    // ກວດທັງ data type (ຈາກ information_schema) ແລະ ຄ່າຕົວຢ່າງ, ເພື່ອຢືນຢັນວ່າ
    // column ນັ້ນເປັນຕົວເລກແທ້ (ບໍ່ແມ່ນ text ຫົວໜ່ວຍນັບ ເຊັ່ນ "ອັນ","ກ.ກ.")
    $quantityColumnCandidates = [
        'request'              => ['Unit', 'Give'],
        'tacking'               => ['QTY'],
        'stock_provinceinput'   => ['Unit'],
        'usestock'              => ['Unit'],
    ];
    $quantityColumnCheck = [];
    foreach ($quantityColumnCandidates as $tbl => $cols) {
        foreach ($cols as $col) {
            $entryKey = "$tbl.$col";
            $dataType = null;
            $rt = mysqli_query($conn, "SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS
                                         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$tbl' AND COLUMN_NAME = '$col'");
            if ($rt && ($rowT = mysqli_fetch_assoc($rt))) {
                $dataType = $rowT['DATA_TYPE'] . ' (' . $rowT['COLUMN_TYPE'] . ')';
            }

            $samples = [];
            $rs = mysqli_query($conn, "SELECT DISTINCT $col AS v, COUNT(*) AS c FROM $tbl WHERE $col IS NOT NULL AND $col <> '' GROUP BY $col ORDER BY c DESC LIMIT 10");
            if ($rs) {
                while ($rowS = mysqli_fetch_assoc($rs)) {
                    $samples[] = ['value' => $rowS['v'], 'count' => (int) $rowS['c']];
                }
            }

            $quantityColumnCheck[$entryKey] = [
                'column_exists'   => $dataType !== null,
                'data_type'       => $dataType,
                'sample_values'   => $samples,
            ];
        }
    }

    echo json_encode([
        'debug' => true,
        'note' => 'ແຜນ (plan) ອ່ານໂດຍກົງຈາກ column ຂອງແຕ່ລະຕາຕະລາງເອງ (1/2/3 -> ແຜນ1/ແຜນ2/ແຜນ3), ບໍ່ join/ບໍ່ເດົາຈາກ OA ອີກຕໍ່ໄປ. ຖ້າ "rows_not_1_2_or_3" ສູງ = ຂໍ້ມູນໃນ column ນັ້ນຂອງຕາຕະລາງນັ້ນຍັງບໍ່ສະອາດ (ວ່າງເປົ່າ/ຄ່າອື່ນທີ່ບໍ່ແມ່ນ 1,2,3), ຄວນກວດແກ້ຂໍ້ມູນຕົ້ນທາງ.',
        'province_buttons_from_request' => $provinces,
        'factory_to_values_in_tacking' => $factories,
        'plan_column_check' => $planCheck,

        'picture_filemap_config' => [
            'upload_dir_used' => PICTURE_UPLOAD_DIR,
            'map_file_used'   => PICTURE_MAP_FILE,
        ],
        'picture_filemap_exists'      => $mapFileExists,
        'picture_filemap_codes_count' => $pictureCodesCount,
        'picture_filemap_codes_sample' => $pictureCodesSample,

        'item_name_to_code_map_count'  => $nameToCodeCount,
        'item_name_to_code_map_sample' => $nameToCodeSample,

        'quantity_column_check' => $quantityColumnCheck,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * ດຶງລາຍການຈັດກຸ່ມຕາມ Items + ແຜນ ຈາກຕາຕະລາງໃດໜຶ່ງ.
 * ແຜນອ່ານໂດຍກົງຈາກ column ຂອງຕາຕະລາງນັ້ນເອງ ($planCol, ຄ່າ 1/2/3),
 * ບໍ່ join ຫາ OA ຂອງຕາຕະລາງອື່ນ ແລະ ບໍ່ເດົາຈາກໂຕອັກສອນທຳອິດຂອງ OA ອີກຕໍ່ໄປ.
 * ຮູບພາບແມ່ນຄົ້ນຫາຫຼັງຈາກ query (ຈາກ filemap.json), ບໍ່ແມ່ນ join SQL.
 *
 * @param mysqli $conn
 * @param string $table         ຊື່ຕາຕະລາງ (request / tacking / stock_provinceinput / usestock)
 * @param string $provinceCol   ຊື່ຄໍລໍາແຂວງ/ສາງໃນຕາຕະລາງນັ້ນ (Province / Provinces / Factory_To)
 * @param string $itemCol       ຊື່ຄໍລໍາອຸປະກອນ (Items ຫຼື Item)
 * @param string $oaCol         ຊື່ຄໍລໍາ PR/OA ໃນຕາຕະລາງນັ້ນ (ໃຊ້ສະແດງເປັນ "pr" ໃນຜົນລັບ ເທົ່ານັ້ນ, ບໍ່ໄດ້ໃຊ້ຫາແຜນອີກຕໍ່ໄປ)
 * @param string $planCol       ຊື່ຄໍລໍາແຜນ "ແທ້" ຂອງຕາຕະລາງນັ້ນເອງ (planing_number / Track_planing_number)
 * @param string $extraWhere    ເງື່ອນໄຂເພີ່ມເຕີມ (ຕົວຢ່າງ: "AND a.OA_Out <> ''")
 * @param string $province      ຄ່າແຂວງ/ສາງທີ່ຈະ filter (bound ດ້ວຍ prepared statement)
 * @param string $qtyExpr       SQL expression ສຳລັບຄິດໄລ່ "ຈຳນວນ": ຄ່າເລີ່ມຕົ້ນ COUNT(*) (ນັບແຖວ);
 *                              ຖ້າຕາຕະລາງມີຄໍລໍາຈຳນວນແທ້ (ເຊັ່ນ tacking.QTY) ໃຫ້ສົ່ງ "SUM(a.QTY)"
 * @param string $dateCol       ຊື່ຄໍລໍາວັນທີ ຂອງຕາຕະລາງນັ້ນ. ຖ້າວ່າງເປົ່າ ຈະບໍ່ດຶງວັນທີ (date = null).
 * @param string $amountCol     ຊື່ຄໍລໍາ "ຈຳນວນອຸປະກອນຕົວຈິງ" ຂອງຕາຕະລາງນັ້ນ (ຕົວຢ່າງ: request.Unit,
 *                              tacking.QTY, stock_provinceinput.Unit, usestock.Unit). ຄ່າຈະຖືກ SUM
 *                              (ຫຼັງ CAST ເປັນຕົວເລກ) ແລ້ວສົ່ງອອກເປັນ key "amount". ຖ້າວ່າງເປົ່າ ຈະສົ່ງ 0.
 * @return array
 */
function fetch_summary_group(mysqli $conn, string $table, string $provinceCol, string $itemCol, string $oaCol, string $planCol, string $extraWhere, string $province, string $qtyExpr = 'COUNT(*)', string $dateCol = '', string $amountCol = ''): array
{
    $prSelect   = "MAX(a.$oaCol)";
    $dateSelect = $dateCol !== '' ? "MAX(a.$dateCol)" : "NULL";
    $planExpr   = plan_case_expr($planCol);

    // ຈຳນວນອຸປະກອນຕົວຈິງ (ບໍ່ແມ່ນຈຳນວນຄັ້ງ): SUM ຈາກ column ຕົວຈິງທີ່ລະບຸ.
    // Column ເຫຼົ່ານີ້ຖືກເກັບເປັນ varchar ແຕ່ຄ່າຂ້າງໃນເປັນຕົວເລກ (ຢືນຢັນຈາກ debug),
    // ຈຶ່ງໃຊ້ CAST(...AS DECIMAL) ເພື່ອບວກແບບຕົວເລກ (ບໍ່ດັ່ງນັ້ນ MySQL ຈະບວກແບບ string
    // ຜິດ). NULLIF ກັນຄ່າຫວ່າງເປົ່າ '' ບໍ່ໃຫ້ CAST ພັງ (ຈະກາຍເປັນ NULL ແລ້ວຖືກ SUM ຂ້າມໄປ).
    $amountSelect = $amountCol !== ''
        ? "SUM(CAST(NULLIF(TRIM(a.$amountCol), '') AS DECIMAL(18,2)))"
        : "NULL";

    // ຖ້າຕາຕະລາງນີ້ແມ່ນ tacking, ໃຫ້ໃຊ້ mapping ຊື່ (ຖ້າມີກຳນົດໄວ້) ແທນຊື່ແຂວງເດີມ
    $lookupValue = $province;
    if ($table === 'tacking' && isset(PROVINCE_TO_FACTORY_MAP[$province])) {
        $lookupValue = PROVINCE_TO_FACTORY_MAP[$province];
    }

    // GROUP BY ໃຊ້ expression ດຽວກັນກັບ SELECT ($planExpr) ໂດຍກົງ ເພື່ອໃຫ້ 1 Item
    // + 1 ແຜນ = 1 ແຖວແທ້ໆ (ບໍ່ຄືເດີມທີ່ຕ້ອງ join+fallback ແລ້ວ merge ຊ້ຳໃນ PHP)
    $sql = "SELECT a.$itemCol AS Item,
                   $prSelect AS Pr,
                   $qtyExpr AS Qty,
                   $amountSelect AS Amount,
                   $planExpr AS PlanNumber,
                   $dateSelect AS Dt
            FROM $table a
            WHERE TRIM(a.$provinceCol) = TRIM(?) $extraWhere
            GROUP BY a.$itemCol, $planExpr
            ORDER BY PlanNumber ASC, a.$itemCol ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // ຖ້າ query ຜິດພາດ (ຕົວຢ່າງ: ຕາຕະລາງ/ຄໍລໍາບໍ່ມີ) ໃຫ້ສົ່ງ array ຫວ່າງແທນທີ່ຈະພັງໜ້າ JSON
        return [];
    }
    $stmt->bind_param('s', $lookupValue);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $safeImg = lookup_picture_image($conn, (string) $row['Item']);

        // ຈັດຮູບແບບວັນທີເປັນ d/m/Y ໃຫ້ browser ອ່ານງ່າຍ. ຖ້າຄ່າວັນທີບໍ່ຖືກຕ້ອງ/ວ່າງເປົ່າ ໃຫ້ສົ່ງເປັນ ''
        $rawDate = $row['Dt'] ?? null;
        $formattedDate = '';
        if ($rawDate !== null && $rawDate !== '' && $rawDate !== '0000-00-00' && $rawDate !== '0000-00-00 00:00:00') {
            $ts = strtotime((string) $rawDate);
            if ($ts !== false) {
                $formattedDate = date('d/m/Y', $ts);
            }
        }

        // ຈຳນວນອຸປະກອນຕົວຈິງ: ຖ້າ SUM ໄດ້ NULL (ບໍ່ມີ amountCol ຫຼືທຸກຄ່າວ່າງເປົ່າ/ບໍ່ແມ່ນຕົວເລກ)
        // ໃຫ້ສົ່ງເປັນ 0. ໃຊ້ (+0) ເພື່ອໃຫ້ JSON ອອກເປັນ int/float ບໍ່ແມ່ນ string ທີ່ຕິດມາຈາກ DB driver.
        $amountRaw = $row['Amount'] ?? null;
        $amountVal = $amountRaw !== null ? ($amountRaw + 0) : 0;

        $rows[] = [
            'item'   => $row['Item'],
            'pr'     => $row['Pr'] ?: '',
            'qty'    => (int) $row['Qty'],       // ຈຳນວນຄັ້ງ/ແຖວ (COUNT)
            'amount' => $amountVal,               // ຈຳນວນອຸປະກອນຕົວຈິງ (SUM)
            'image'  => $safeImg ? (PICTURE_IMAGE_BASE . $safeImg) : null,
            'plan'   => $row['PlanNumber'],
            'date'   => $formattedDate,
        ];
    }
    $stmt->close();

    return $rows;
}

$response = [
    // ຂໍເບິກ: ທຸກຄຳຮ້ອງໃນ request, ແຜນ = request.planing_number ໂດຍກົງ, ວັນທີ = DateComfirm,
    // amount = SUM(request.Unit) = ຈຳນວນອຸປະກອນຕົວຈິງທີ່ຂໍ (ບໍ່ແມ່ນ Give ເຊິ່ງແມ່ນຈຳນວນທີ່ອະນຸມັດ)
    'request' => fetch_summary_group($conn, 'request', 'Province', 'Items', 'OA_Out', 'planing_number', '', $Pro, 'COUNT(*)', 'DateComfirm', 'Unit'),

    // ສົ່ງເຄື່ອງ: ດຶງຈາກຕາຕະລາງ tacking, filter ດ້ວຍ Factory_To (ຜ່ານ mapping),
    // ແຜນ = tacking.Track_planing_number ໂດຍກົງ, ຈຳນວນ = COUNT(*) (ນັບ 1 OA = 1, ບໍ່ SUM QTY ອີກຕໍ່ໄປ), ວັນທີ = Doc_Date,
    // amount = SUM(tacking.QTY) = ຈຳນວນອຸປະກອນຕົວຈິງທີ່ສົ່ງ
    'order'   => fetch_summary_group($conn, 'tacking', 'Factory_To', 'Item', 'OA', 'Track_planing_number', '', $Pro, 'COUNT(*)', 'Doc_Date', 'QTY'),

    // ຮັບເຄື່ອງ: ຈາກ stock_provinceinput, ແຜນ = stock_provinceinput.planing_number ໂດຍກົງ, ວັນທີ = Date_Pro,
    // amount = SUM(stock_provinceinput.Unit) = ຈຳນວນອຸປະກອນຕົວຈິງທີ່ຮັບ
    'receive' => fetch_summary_group($conn, 'stock_provinceinput', 'Provinces', 'Items', 'OA', 'planing_number', '', $Pro, 'COUNT(*)', 'Date_Pro', 'Unit'),

    // ນຳໃຊ້: ຈາກ usestock, ແຜນ = usestock.planing_number ໂດຍກົງ, ວັນທີ = Date,
    // amount = SUM(usestock.Unit) = ຈຳນວນອຸປະກອນຕົວຈິງທີ່ນຳໃຊ້
    'use'     => fetch_summary_group($conn, 'usestock', 'Provinces', 'Items', 'OA', 'planing_number', '', $Pro, 'COUNT(*)', 'Date', 'Unit'),
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);