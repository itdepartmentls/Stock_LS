<?php
/**
 * cleanup_duplicates.php
 * -----------------------------------------------------------------
 * ສະຄຣິບນີ້ໃຊ້ "ຄັ້ງດຽວ" ເພື່ອທຳຄວາມສະອາດ uploads/filemap.json ທີ່ມີຮູບ
 * ຊ້ຳກັນ (ລະຫັດ/ຊື່ສະແດງດຽວກັນ ແຕ່ມີຫຼາຍໄຟລ໌ຈິງ).
 *
 * ກົດ: ຖ້າລະຫັດດຽວກັນມີຫຼາຍໄຟລ໌ -> ເກັບໄວ້ພຽງ "ໄຟລ໌ຫຼ້າສຸດ" (mtime ຫຼ້າສຸດ)
 *      ໄຟລ໌ອື່ນທີ່ຊ້ຳຈະຖືກລຶບອອກຈາກ disk ແທ້ໆ ແລະລຶບອອກຈາກ filemap.json
 *
 * ວິທີໃຊ້:
 *   1. ອັບໂຫຼດໄຟລ໌ນີ້ໄປໄວ້ຢູ່ folder ດຽວກັບ index.php / upload.php (ຮາກຂອງ
 *      ໂປຣເຈັກ ບ່ອນທີ່ມີໂຟນເດີ uploads/)
 *   2. ເປີດຜ່ານ browser ຄັ້ງດຽວ (ຕົວຢ່າງ: http://yourserver/cleanup_duplicates.php)
 *   3. ກວດຜົນລັບລາຍງານ ແລ້ວ "ລຶບໄຟລ໌ນີ້ອອກຈາກ server" ຫຼັງໃຊ້ແລ້ວ
 *      (ເພື່ອປ້ອງກັນການເອີ້ນຄືນໂດຍບໍ່ຕັ້ງໃຈ)
 *
 * ໝາຍເຫດ: ສະຄຣິບນີ້ຈະສ້າງ backup ຂອງ filemap.json ເດີມໄວ້ທີ່
 *          uploads/filemap.backup.<timestamp>.json ກ່ອນຂຽນທັບ
 * -----------------------------------------------------------------
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

$uploadDir = 'uploads/';
$mapFile   = $uploadDir . 'filemap.json';

echo '<!DOCTYPE html><html lang="lo"><head><meta charset="UTF-8">';
echo '<title>ທຳຄວາມສະອາດຮູບຊ້ຳ</title>';
echo '<style>body{font-family:sans-serif;max-width:900px;margin:30px auto;padding:0 16px;line-height:1.6}
h2{color:#198754}h3{margin-top:28px}table{border-collapse:collapse;width:100%;margin-top:8px}
td,th{border:1px solid #ddd;padding:6px 10px;font-size:14px}th{background:#f5f5f5}
.kept{color:#198754;font-weight:600}.removed{color:#c0392b}
.summary{background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:16px;margin:16px 0}
</style></head><body>';

echo '<h2>ທຳຄວາມສະອາດຮູບຊ້ຳໃນ filemap.json</h2>';

if (!file_exists($mapFile)) {
    echo "<p style='color:#c0392b;'>❌ ບໍ່ພົບໄຟລ໌ $mapFile</p></body></html>";
    exit;
}

$decoded = json_decode(file_get_contents($mapFile), true);
$map = is_array($decoded) ? $decoded : array();

if (empty($map)) {
    echo "<p>filemap.json ຫວ່າງເປົ່າ, ບໍ່ມີຫຍັງໃຫ້ທຳຄວາມສະອາດ.</p></body></html>";
    exit;
}

// -------------------------------------------------------------
// 1) ຈັດກຸ່ມ entries ຕາມ display name (ລະຫັດສະແດງ)
// -------------------------------------------------------------
$groups = array(); // displayName => array of safeName
foreach ($map as $safeName => $displayName) {
    $groups[$displayName][] = $safeName;
}

// -------------------------------------------------------------
// 2) ສຳລັບແຕ່ລະກຸ່ມທີ່ມີຫຼາຍກວ່າ 1 ໄຟລ໌ -> ຫາໄຟລ໌ "ຫຼ້າສຸດ" ດ້ວຍ filemtime
//    (ໄຟລ໌ທີ່ບໍ່ມີຢູ່ໃນ disk ອີກແລ້ວ ຈະຖືກຖິ້ມອອກຈາກ map ໄປພ້ອມ)
// -------------------------------------------------------------
$newMap = array();
$removedFiles = array();
$keptFiles = array();
$missingCleaned = array();

foreach ($groups as $displayName => $safeNames) {
    // ຫາ mtime ຈິງຂອງແຕ່ລະໄຟລ໌ (ໄຟລ໌ທີ່ບໍ່ມີແລ້ວຈະຖືກຂ້າມ)
    $candidates = array();
    foreach ($safeNames as $safeName) {
        $path = $uploadDir . $safeName;
        if (file_exists($path)) {
            $candidates[$safeName] = filemtime($path);
        } else {
            $missingCleaned[] = $safeName . ' (' . $displayName . ')';
        }
    }

    if (empty($candidates)) {
        // ບໍ່ມີໄຟລ໌ຈິງເຫຼືອເລີຍສຳລັບລະຫັດນີ້ -> ບໍ່ຕ້ອງເພີ່ມເຂົ້າ newMap
        continue;
    }

    // ເອົາໄຟລ໌ທີ່ມີ mtime ຫຼ້າສຸດ (ອັບໂຫຼດຄັ້ງສຸດທ້າຍ)
    arsort($candidates); // ຮຽງຈາກ mtime ຫຼາຍສຸດ (ໃໝ່ສຸດ) ຫາໜ້ອຍສຸດ
    $keepSafe = array_key_first($candidates);

    $newMap[$keepSafe] = $displayName;
    $keptFiles[] = $keepSafe . ' (' . $displayName . ')';

    foreach ($candidates as $safeName => $mtime) {
        if ($safeName !== $keepSafe) {
            $path = $uploadDir . $safeName;
            if (unlink($path)) {
                $removedFiles[] = $safeName . ' (' . $displayName . ')';
            } else {
                $removedFiles[] = $safeName . ' (' . $displayName . ') — ⚠️ unlink() ລົ້ມເຫຼວ, ຍັງຄ້າງໃນ disk';
                // ຖ້າລົບບໍ່ໄດ້ ຢ່າໃສ່ໃນ newMap ຄືເກົ່າ (ຈະຖືກ index.php migrate ກັບເຂົ້າມາ
                // ອີກຄັ້ງ, ແຕ່ຢ່າງໜ້ອຍຈະບໍ່ຊ້ຳກັບລາຍການ keepSafe ໃນ map ປັດຈຸບັນ)
            }
        }
    }
}

$totalBefore = count($map);
$totalAfter  = count($newMap);
$totalRemoved = count($removedFiles);

// -------------------------------------------------------------
// 3) Backup filemap.json ເດີມ ແລ້ວຂຽນທັບດ້ວຍ newMap
// -------------------------------------------------------------
$backupFile = $uploadDir . 'filemap.backup.' . date('Ymd_His') . '.json';
copy($mapFile, $backupFile);

ksort($newMap);
file_put_contents($mapFile, json_encode($newMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

// -------------------------------------------------------------
// 4) ລາຍງານຜົນ
// -------------------------------------------------------------
echo '<div class="summary">';
echo "<p>📦 ຈຳນວນລາຍການໃນ filemap.json ກ່ອນທຳຄວາມສະອາດ: <b>$totalBefore</b></p>";
echo "<p>✅ ຈຳນວນລາຍການຫຼັງທຳຄວາມສະອາດ (1 ຮູບ/ລະຫັດ): <b>$totalAfter</b></p>";
echo "<p>🗑️ ຈຳນວນໄຟລ໌ຊ້ຳທີ່ຖືກລຶບອອກຈາກ disk: <b>$totalRemoved</b></p>";
echo "<p>💾 Backup ຂອງ filemap.json ເດີມຖືກເກັບໄວ້ທີ່: <code>" . htmlspecialchars($backupFile) . "</code></p>";
echo '</div>';

if (!empty($missingCleaned)) {
    echo '<h3>ລາຍການໃນ map ເດີມທີ່ບໍ່ພົບໄຟລ໌ຈິງ (ຖືກລ້າງອອກ)</h3><ul>';
    foreach ($missingCleaned as $line) echo '<li>' . htmlspecialchars($line) . '</li>';
    echo '</ul>';
}

if (!empty($removedFiles)) {
    echo '<h3 class="removed">ໄຟລ໌ຊ້ຳທີ່ຖືກລຶບ</h3><ul>';
    foreach ($removedFiles as $line) echo '<li class="removed">' . htmlspecialchars($line) . '</li>';
    echo '</ul>';
}

echo '<h3 class="kept">ໄຟລ໌ທີ່ຖືກເກັບໄວ້ (ຫຼ້າສຸດຕໍ່ລະຫັດ)</h3><ul>';
foreach ($keptFiles as $line) echo '<li class="kept">' . htmlspecialchars($line) . '</li>';
echo '</ul>';

echo '<hr><p style="color:#c0392b;"><b>⚠️ ສຳຄັນ:</b> ກະລຸນາລຶບໄຟລ໌ <code>cleanup_duplicates.php</code> ນີ້ອອກຈາກ server ຫຼັງໃຊ້ແລ້ວ ເພື່ອປ້ອງກັນການເອີ້ນຄືນໂດຍບໍ່ຕັ້ງໃຈ.</p>';
echo '<p><a href="index.php">← ກັບຄືນໜ້າຫຼັກ</a></p>';
echo '</body></html>';