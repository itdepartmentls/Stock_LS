<?php
// =========================================================
// get_pr_data.php
// Endpoint ດຶງຂໍ້ມູນຈາກຕາຕະລາງ `request` (ສະເພາະ S_Status = 1)
// ໃຊ້ໂດຍ tacking.php ເພື່ອສ້າງ dropdown ເລືອກ PR ແລະ auto-fill ລາຍການສິນຄ້າ
//
// ວິທີໃຊ້:
//   GET get_pr_data.php?action=list            -> ລາຍການ OA_Out (PR No) ທັງໝົດທີ່ S_Status=1
//   GET get_pr_data.php?action=items&pr=SNK185 -> ລາຍການສິນຄ້າທັງໝົດຂອງ PR ນັ້ນ (S_Status=1)
//
// ອັບເດດ (ຮອບນີ້): action=items ດຶງຂໍ້ມູນເພີ່ມ 2 ຢ່າງ ເພື່ອ auto-fill
//   ຄໍລໍາ "ຈຳນວນ" ແລະ "ຂະໜາດ" ໃນຕາຕະລາງ tacking.php ທີ່ຫວ່າງຢູ່ກ່ອນໜ້ານີ້:
//     - Qty       -> ດຶງຈາກ request.Unit (column ນີ້ໃນ request ຈິງໆເກັບ "ຈຳນວນ", ບໍ່ແມ່ນໜ່ວຍນັບ)
//     - UnitLabel -> ດຶງຈາກ request.Type  (column ນີ້ຄືໜ່ວຍນັບ ເຊັ່ນ ອັນ/ໂຕ/ກ່ອງ)
//     - Size      -> request ບໍ່ມີ column Size, LEFT JOIN stock ດ້ວຍ Item_code ເພື່ອດຶງມາ
//   ໝາຍເຫດ: JOIN ໃຊ້ Item_code (ບໍ່ໃຊ້ Items/ຊື່) ເພື່ອປ້ອງກັນແຖວບວມ (row duplication)
//   ຄືກັນກັບການແກ້ໄຂໃນ DataReAdmin.php ກ່ອນໜ້ານີ້ (ຊື່ Items ອາດຊ້ຳກັນລະຫວ່າງເຄື່ອງຄົນລະລາຍການ)
// =========================================================
@session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// =========================================================
// JSON encoder ຂຽນເອງ (server ນີ້ json_encode() ໃຊ້ບໍ່ໄດ້ - ເບິ່ງ tacking.php)
// =========================================================
function manualJsonEncodeString($str)
{
    $str = (string) $str;
    $len = strlen($str);
    $out = '"';
    for ($i = 0; $i < $len; $i++) {
        $c = $str[$i];
        $ord = ord($c);
        if ($c === '"') {
            $out .= '\\"';
        } elseif ($c === '\\') {
            $out .= '\\\\';
        } elseif ($c === '<') {
            $out .= '\\u003C';
        } elseif ($c === '>') {
            $out .= '\\u003E';
        } elseif ($c === '&') {
            $out .= '\\u0026';
        } elseif ($c === "'") {
            $out .= '\\u0027';
        } elseif ($c === "\n") {
            $out .= '\\n';
        } elseif ($c === "\r") {
            $out .= '\\r';
        } elseif ($c === "\t") {
            $out .= '\\t';
        } elseif ($ord < 0x20) {
            $out .= sprintf('\\u%04x', $ord);
        } else {
            $out .= $c;
        }
    }
    $out .= '"';
    return $out;
}

function manualJsonEncode($val)
{
    if (is_null($val)) return 'null';
    if (is_bool($val)) return $val ? 'true' : 'false';
    if (is_int($val) || is_float($val)) return (string) $val;
    if (is_string($val)) return manualJsonEncodeString($val);
    if (is_array($val)) {
        $isList = (array_keys($val) === range(0, count($val) - 1));
        $parts = array();
        if ($isList) {
            foreach ($val as $v) {
                $parts[] = manualJsonEncode($v);
            }
            return '[' . implode(',', $parts) . ']';
        } else {
            foreach ($val as $k => $v) {
                $parts[] = manualJsonEncodeString($k) . ':' . manualJsonEncode($v);
            }
            return '{' . implode(',', $parts) . '}';
        }
    }
    return 'null';
}

function forceValidUtf8Str($str)
{
    if ($str === null) return '';
    if (@preg_match('//u', $str) === 1) {
        return $str;
    }
    $regex = '/(?:
          [\x09\x0A\x0D\x20-\x7E]
        | [\xC2-\xDF][\x80-\xBF]
        | \xE0[\xA0-\xBF][\x80-\xBF]
        | [\xE1-\xEC][\x80-\xBF]{2}
        | \xED[\x80-\x9F][\x80-\xBF]
        | [\xEE-\xEF][\x80-\xBF]{2}
        | \xF0[\x90-\xBF][\x80-\xBF]{2}
        | [\xF1-\xF3][\x80-\xBF]{3}
        | \xF4[\x80-\x8F][\x80-\xBF]{2}
    )/x';
    preg_match_all($regex, $str, $m);
    return implode('', $m[0]);
}

function sendJson($arr)
{
    echo manualJsonEncode($arr);
    exit;
}

// ---- ກວດສິດເຂົ້າໃຊ້ (ຄືກັນກັບ tacking.php: ຕ້ອງ login ແລະ ຕ້ອງເປັນ HQ) ----
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || !isset($_SESSION["iduser"]) || $_SESSION["iduser"] == "") {
    http_response_code(401);
    sendJson(array('status' => 'error', 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບ'));
}
$myFactory = isset($_SESSION['factory']) ? trim($_SESSION['factory']) : '';
if (strcasecmp($myFactory, 'HQ') !== 0) {
    http_response_code(403);
    sendJson(array('status' => 'error', 'message' => 'ໜ້ານີ້ສະເພາະບັນຊີ HQ ເທົ່ານັ້ນ'));
}

require_once __DIR__ . '/includes/conn.php';
/** @var mysqli $conn */

$action = isset($_GET['action']) ? trim($_GET['action']) : '';

if ($action === 'list') {
    // ---- ດຶງລາຍການ PR (OA_Out) ທັງໝົດ ສະເພາະ S_Status = 1, ບໍ່ເອົາຄ່າຊ້ຳ ----
    $sql = "SELECT DISTINCT OA_Out FROM request WHERE S_Status = 1 AND OA_Out IS NOT NULL AND OA_Out <> '' ORDER BY OA_Out ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendJson(array('status' => 'error', 'message' => 'ຕຽມ query ບໍ່ສຳເລັດ: ' . $conn->error));
    }
    $stmt->execute();
    $oaOut = null;
    $stmt->bind_result($oaOut);
    $list = array();
    while ($stmt->fetch()) {
        $list[] = forceValidUtf8Str($oaOut);
    }
    $stmt->close();
    $conn->close();
    sendJson(array('status' => 'success', 'data' => $list));
} elseif ($action === 'items') {
    // ---- ດຶງລາຍການສິນຄ້າທັງໝົດຂອງ PR (OA_Out) ໜຶ່ງ, ສະເພາະ S_Status = 1 ----
    // ອັບເດດ: ເພີ່ມ a.Unit (-> Qty), a.Type (-> UnitLabel), ແລະ LEFT JOIN stock
    //         ດ້ວຍ Item_code ເພື່ອດຶງ Size ມາ auto-fill ໃນຕາຕະລາງ tacking.php
    $pr = isset($_GET['pr']) ? trim($_GET['pr']) : '';
    if ($pr === '') {
        sendJson(array('status' => 'error', 'message' => 'ບໍ່ໄດ້ລະບຸເລກທີ PR'));
    }
    $sql = "SELECT a.Item_code, a.Items, a.Unit, a.Type, s.Size
            FROM request a
            LEFT JOIN stock s ON a.Item_code = s.Item_code
            WHERE a.OA_Out = ? AND a.S_Status = 1
            ORDER BY a.id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        sendJson(array('status' => 'error', 'message' => 'ຕຽມ query ບໍ່ສຳເລັດ: ' . $conn->error));
    }
    $stmt->bind_param("s", $pr);
    $stmt->execute();
    $r_ItemCode = $r_Items = $r_Qty = $r_UnitLabel = $r_Size = null;
    $stmt->bind_result($r_ItemCode, $r_Items, $r_Qty, $r_UnitLabel, $r_Size);
    $items = array();
    while ($stmt->fetch()) {
        $items[] = array(
            'Item_code' => forceValidUtf8Str($r_ItemCode),
            'Items' => forceValidUtf8Str($r_Items),
            'Qty' => forceValidUtf8Str($r_Qty),           // request.Unit ຈິງແມ່ນຈຳນວນ
            'UnitLabel' => forceValidUtf8Str($r_UnitLabel), // request.Type ຈິງແມ່ນໜ່ວຍນັບ
            'Size' => forceValidUtf8Str($r_Size)          // ດຶງມາຈາກ stock ຜ່ານ Item_code
        );
    }
    $stmt->close();
    $conn->close();
    sendJson(array('status' => 'success', 'data' => $items));
} else {
    sendJson(array('status' => 'error', 'message' => 'ບໍ່ຮູ້ຈັກ action: ' . $action));
}