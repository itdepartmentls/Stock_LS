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
$allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
$maxSize = 2 * 1024 * 1024; // 2MB

echo '<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao&family=Poppins&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
echo '<div class="container mt-4">';
echo '<style>
    body {
        font-family: "Noto Sans Lao", Poppins, sans-serif;
    }
</style>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = $_FILES['images'];
    $successCount = 0;

    for ($i = 0; $i < count($files['name']); $i++) {
        $name = $files['name'][$i];
        $type = $files['type'][$i];
        $tmp = $files['tmp_name'][$i];
        $error = $files['error'][$i];
        $size = $files['size'][$i];

        if ($error === UPLOAD_ERR_OK) {
            if (!in_array($type, $allowedTypes)) {
                echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'ປະເພດໄຟລ໌ບໍ່ຖືກຕ້ອງ',
                        text: '❌ \"$name\" ບໍ່ແມ່ນຮູບພາບທີ່ອະນຸຍາດ'
                    });
                </script>";
                continue;
            }

            if ($size > $maxSize) {
                echo "<script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'ໄຟລ໌ໃຫຍ່ເກີນໄປ',
                        text: '❌ \"$name\" ຂະໜາດເກີນ 2MB',
                        confirmButtonText: 'ຕົກລົງ'
                        }).then(() => {
                            window.location.href = 'index.php';
                    });
                </script>";
                continue;
            }

            $name = $files['name'][$i];
            $newName = basename($name);
            $targetFile = $uploadDir . $newName;

            // ກວດສອບຊື່ຊ້ຳ
            $counter = 1;
            while (file_exists($targetFile)) {
                $pathInfo = pathinfo($name);
                $newName = $pathInfo['filename'] . "_$counter." . $pathInfo['extension'];
                $targetFile = $uploadDir . $newName;
                $counter++;
            }

            if (move_uploaded_file($tmp, $targetFile)) {
                $successCount++;
            } else {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'ບໍ່ສາມາດອັບໂຫລດໄດ້',
                        text: '❌ \"$name\" ບໍ່ສາມາດບັນທຶກໄດ້'
                    });
                </script>";
            }
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'ເກີດຂໍ້ຜິດພາດ',
                    text: '❌ ການອັບໂຫລດ \"$name\" ລົ້ມເຫຼວ'
                });
            </script>";
        }
    }

    // ແຈ້ງຜົນລວມຫຼັງອັບໂຫຼດສຳເລັດ
    if ($successCount > 0) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'ອັບໂຫລດຮູບພາບສຳເລັດ',
                text: '✅ ຈຳນວນ $successCount ໄຟລ໌ຖືກບັນທຶກໄວ້ແລ້ວ',
                confirmButtonText: 'ຕົກລົງ'
            }).then(() => {
                window.location.href = 'index.php';
            });
        </script>";
    }
}
echo '</div>';
?>