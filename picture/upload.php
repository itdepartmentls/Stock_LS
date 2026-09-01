<?php
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ອັບໂຫຼດຮູບພາບ</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao&display=swap" rel="stylesheet">
</head>
<body style="font-family: Noto Sans Lao, sans-serif;">';

$uploadDir = 'uploads/';
$allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif');
$maxSize = 10 * 1024 * 1024; // 10MB (ຕ້ອງບໍ່ເກີນ upload_max_filesize ໃນ php.ini)

echo '<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao&family=Poppins&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="../css/vendor/bootstrap/bootstrap.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
echo '<div class="container mt-4">';
echo '<style>
    body {
        font-family: "Noto Sans Lao", Poppins, sans-serif;
    }
</style>';

// =========================================================
// ຈຳນວນຮູບທີ່ user ເລືອກຢູ່ browser (ສົ່ງມາຈາກ hidden input ໃນ index.php)
// ໃຊ້ທຽບກັບຈຳນວນທີ່ server ໄດ້ຮັບຈິງ ເພື່ອກວດວ່າຖືກ php.ini ບລັອກຫຼືບໍ່
// =========================================================
$selectedCount = isset($_POST['total_selected']) ? (int) $_POST['total_selected'] : null;

// =========================================================
// ກວດເບິ່ງວ່າ request ຖືກ PHP ບລັອກແຕ່ຕົ້ນຫຼືບໍ່ (post_max_size ເກີນ)
// ກໍລະນີນີ້ $_FILES ຈະ "ຫວ່າງເປົ່າ" ທັງທີ່ user ເລືອກຮູບແລ້ວ
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && empty($_POST['action'])) {
    $postMax = ini_get('post_max_size');
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'ຂໍ້ມູນທີ່ອັບໂຫລດໃຫຍ່ເກີນໄປ',
            html: 'ຂະໜາດລວມຂອງຮູບພາບທັງໝົດເກີນຄ່າ post_max_size (' + '$postMax' + ') ຂອງ server.<br>ກະລຸນາອັບໂຫຼດເທື່ອລະໜ້ອຍ ຫຼືແຈ້ງ Admin ໃຫ້ເພີ່ມຄ່ານີ້ໃນ php.ini ແລ້ວ Restart Apache',
            confirmButtonText: 'ຕົກລົງ'
        }).then(function() {
            window.location.href = 'index.php';
        });
    </script>";
    echo '</div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $successCount = 0;
    $skippedType = array();
    $skippedSize = array();
    $failedMove  = array();
    $totalFiles  = count($files['name']);

    // ໂຫຼດ filemap
    $mapFile = $uploadDir . 'filemap.json';
    if (file_exists($mapFile)) {
        $decoded = json_decode(file_get_contents($mapFile), true);
        $map = is_array($decoded) ? $decoded : array();
    } else {
        $map = array();
    }

    // ສ້າງ index ຍ້ອນກັບ (display name -> safe filename) ໄວ້ຄົ້ນຫາໄວ ເພື່ອກັນ
    // ບໍ່ໃຫ້ລະຫັດດຽວກັນ (ຊື່ໄຟລ໌ຕົ້ນສະບັບດຽວກັນ) ຖືກອັບໂຫຼດຊ້ຳຫຼາຍລາຍການ
    // ໃນ filemap.json — ຖ້າອັບໂຫຼດຮູບທີ່ມີລະຫັດຊ້ຳ ຈະ "ທັບ" ຮູບເກົ່າແທນ
    $replacedCount = 0;
    $reverseIndex = array();
    foreach ($map as $safe => $display) {
        $reverseIndex[$display] = $safe;
    }

    for ($i = 0; $i < $totalFiles; $i++) {
        $name  = $files['name'][$i];
        $type  = $files['type'][$i];
        $tmp   = $files['tmp_name'][$i];
        $error = $files['error'][$i];
        $size  = $files['size'][$i];

        if ($error === UPLOAD_ERR_OK) {
            if (!in_array($type, $allowedTypes)) {
                $skippedType[] = $name;
                continue;
            }

            if ($size > $maxSize) {
                $skippedSize[] = $name;
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $displayName = pathinfo($name, PATHINFO_FILENAME);
            $newName    = uniqid('img_', true) . '.' . $ext;
            $targetFile = $uploadDir . $newName;

            if (move_uploaded_file($tmp, $targetFile)) {
                // ຖ້າລະຫັດ (displayName) ນີ້ມີຮູບຢູ່ໃນ map ແລ້ວ ໃຫ້ລຶບໄຟລ໌ເກົ່າອອກ
                // ແລະລຶບລາຍການເກົ່າອອກຈາກ map ກ່ອນ ເພື່ອບໍ່ໃຫ້ຄ້າງເປັນຄູ່ຊ້ຳ
                if (isset($reverseIndex[$displayName])) {
                    $oldSafe = $reverseIndex[$displayName];
                    $oldPath = $uploadDir . $oldSafe;
                    if ($oldSafe !== $newName && file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                    unset($map[$oldSafe]);
                    $replacedCount++;
                }

                $map[$newName] = $displayName;
                $reverseIndex[$displayName] = $newName;
                $successCount++;
            } else {
                $failedMove[] = $name;
            }
        } elseif ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            $skippedSize[] = $name . ' (ໃຫຍ່ກວ່າ upload_max_filesize ຂອງ server)';
        } else {
            $failedMove[] = $name . ' (error code: ' . $error . ')';
        }
    }

    // ບັນທຶກ filemap ຫຼັງ loop
    // LOCK_EX ກັນບໍ່ໃຫ້ upload.php ແລະ index.php (delete/rename/replace)
    // ຂຽນ filemap.json ພ້ອມກັນຈົນຂໍ້ມູນເສຍ/ຄ້າງ (ຊຶ່ງເປັນສາເຫດໜຶ່ງທີ່ເຮັດໃຫ້
    // ຮູບເກົ່າ "ບໍ່ຫາຍ" ຫຼືຖືກນັບຊ້ຳ)
    file_put_contents($mapFile, json_encode($map), LOCK_EX);

    // =========================================================
    // ສ້າງລາຍງານສະຫຼຸບ: ຈຳນວນທີ່ເລືອກ vs ຈຳນວນທີ່ server ຮັບ vs ຈຳນວນທີ່ບັນທຶກສຳເລັດ
    // =========================================================
    $droppedByServer = 0;
    if ($selectedCount !== null && $selectedCount > $totalFiles) {
        $droppedByServer = $selectedCount - $totalFiles;
    }

    $htmlLines = array();
    $htmlLines[] = "<p>✅ ບັນທຶກສຳເລັດ: <b>$successCount</b> ຮູບ</p>";
    if ($replacedCount > 0) {
        $htmlLines[] = "<p>🔄 ໃນນັ້ນມີ <b>$replacedCount</b> ຮູບທີ່ທັບຮູບເກົ່າ (ລະຫັດຊ້ຳກັບຮູບທີ່ມີຢູ່ແລ້ວ)</p>";
    }
    if ($selectedCount !== null) {
        $htmlLines[] = "<p>📌 ຈຳນວນທີ່ທ່ານເລືອກໃນເຄື່ອງ: <b>$selectedCount</b> ຮູບ</p>";
    }

    if ($droppedByServer > 0) {
        $maxFileUploads = ini_get('max_file_uploads');
        $postMax = ini_get('post_max_size');
        $htmlLines[] = "<p style='color:#c0392b;'>⚠️ ມີ <b>$droppedByServer</b> ຮູບບໍ່ໄດ້ຮັບການສົ່ງເຖິງ server ເລີຍ (ຖືກຕັດອອກກ່ອນ)."
            . "<br>ສາເຫດອາດແມ່ນ server ຈຳກັດ max_file_uploads=$maxFileUploads ຫຼື post_max_size=$postMax."
            . "<br>ກະລຸນາແຈ້ງ Admin ໃຫ້ເພີ່ມຄ່ານີ້ໃນ php.ini ແລ້ວ Restart Apache</p>";
    }

    if (!empty($skippedType)) {
        $c = count($skippedType);
        $htmlLines[] = "<p style='color:#c0392b;'>❌ ປະເພດໄຟລ໌ບໍ່ຖືກຕ້ອງ ($c ຮູບ): " . htmlspecialchars(implode(', ', $skippedType)) . "</p>";
    }
    if (!empty($skippedSize)) {
        $c = count($skippedSize);
        $htmlLines[] = "<p style='color:#c0392b;'>❌ ຂະໜາດໃຫຍ່ເກີນ " . round($maxSize / 1024 / 1024) . "MB ($c ຮູບ): " . htmlspecialchars(implode(', ', $skippedSize)) . "</p>";
    }
    if (!empty($failedMove)) {
        $c = count($failedMove);
        $htmlLines[] = "<p style='color:#c0392b;'>❌ ບັນທຶກລົ້ມເຫຼວ ($c ຮູບ): " . htmlspecialchars(implode(', ', $failedMove)) . "</p>";
    }

    $summaryHtml = implode('', $htmlLines);
    $allSuccess = $successCount > 0 && $droppedByServer === 0 && empty($skippedType) && empty($skippedSize) && empty($failedMove);

    if ($successCount > 0 || $droppedByServer > 0 || !empty($skippedType) || !empty($skippedSize) || !empty($failedMove)) {
        $icon = $allSuccess ? 'success' : 'warning';
        $title = $allSuccess ? 'ອັບໂຫລດຮູບພາບສຳເລັດ' : 'ອັບໂຫລດສຳເລັດບາງສ່ວນ';
        echo "<script>
            Swal.fire({
                icon: '$icon',
                title: '$title',
                html: `$summaryHtml`,
                confirmButtonText: 'ຕົກລົງ'
            }).then(function() {
                window.location.href = 'index.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({icon:'error',title:'ບໍ່ມີຮູບພາບຖືກອັບໂຫລດ'}).then(function() {
                window.location.href = 'index.php';
            });
        </script>";
    }
}
echo '</div>';
?>