<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ລຶບໄຟລ໌ + ອັບເດດ filemap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $safeName = basename($_POST['filename']);
    $file = 'uploads/' . $safeName;
    $mapFile = 'uploads/filemap.json';

    if (file_exists($file)) {
        unlink($file);

        // ລຶບອອກຈາກ filemap ດ້ວຍ
        if (file_exists($mapFile)) {
            $decoded = json_decode(file_get_contents($mapFile), true);
            $map = is_array($decoded) ? $decoded : array();
            unset($map[$safeName]);
            file_put_contents($mapFile, json_encode($map));
        }

        $_SESSION['status'] = 'success';
    } else {
        $_SESSION['status'] = 'error';
    }
    header("Location: index.php");
    exit;
}

// ໂຫຼດ filemap
$mapFile = 'uploads/filemap.json';
if (file_exists($mapFile)) {
    $decoded = json_decode(file_get_contents($mapFile), true);
    $map = is_array($decoded) ? $decoded : array();
} else {
    $map = array();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Images Stock</title>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- link icon -->
  <link type="logo_png" rel="icon" href="logo_png.png">

  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppin', 'Noto Sans Lao';
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-5">
  <h2 class="mb-4 text-center">📤 ອັບໂຫຼດຮູບພາບ (ເລືອກໄດ້ຫຼາຍໄຟລ໌)</h2>

  <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-5">
    <div class="mb-3">
      <input class="form-control" type="file" id="imageInput" name="images[]" multiple accept="image/*" required>
    </div>
    <button type="submit" class="btn btn-success">📤 ອັບໂຫຼດ</button>
    <a href="http://lslao.com.la:900/Dasborad.php" class="btn btn-primary">ກັບຄືນໜ້າຫຼັກ</a>
  </form>

  <h4 class="mb-3"> ຕົວຢ່າງຮູບກ່ອນອັບໂຫຼດ </h4>
  <div id="preview" class="row g-4"></div>

  <hr class="my-5">

  <h4 class="mb-3">🖼️ ຮູບພາບທີ່ອັບໂຫຼດແລ້ວ</h4>
  <div class="row g-4">
    <?php
    $dir = 'uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (empty($map)) {
        echo "<p class='text-muted'>ຍັງບໍ່ມີຮູບພາບທີ່ອັບໂຫຼດ</p>";
    } else {
        foreach ($map as $safeName => $originalName) {
            $fileUrl = $dir . $safeName;

            if (!file_exists($fileUrl)) continue;

            $fileSize = filesize($fileUrl);
            $formattedSize = $fileSize > 1024 * 1024
                ? round($fileSize / (1024 * 1024), 2) . " MB"
                : round($fileSize / 1024, 2) . " KB";

            echo '<div class="col-sm-6 col-md-4 col-lg-3">';
            echo '<div class="card shadow-sm h-100">';
            echo '<img src="' . htmlspecialchars($fileUrl) . '" class="card-img-top" style="object-fit: cover; height: 200px;">';
            echo '<div class="card-body text-center">';
            echo '<p class="card-text small mb-1">' . htmlspecialchars($originalName) . '</p>';
            echo '<p class="text-muted small">ຂະໜາດ: ' . $formattedSize . '</p>';
            echo '<form method="post" class="delete-form">';
            echo '<input type="hidden" name="filename" value="' . htmlspecialchars($safeName) . '">';
            echo '<button type="button" class="btn btn-sm btn-danger btn-delete"> ລົບ</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
    ?>
  </div>
</div>

<?php
if (isset($_SESSION['status'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    if ($_SESSION['status'] === 'success') {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'ລົບຮູບພາບສຳເລັດ',
                timer: 1500,
                showConfirmButton: false
            });
        </script>";
    } elseif ($_SESSION['status'] === 'error') {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'ບໍ່ພົບໄຟລ໌',
                text: 'ການລົບລົ້ມເຫຼວ',
            });
        </script>";
    }
    unset($_SESSION['status']);
}
?>

<script>
  const input = document.getElementById('imageInput');
  const preview = document.getElementById('preview');

  input.addEventListener('change', function() {
    preview.innerHTML = '';
    for (var i = 0; i < input.files.length; i++) {
      (function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var col = document.createElement('div');
          col.className = 'col-sm-6 col-md-4 col-lg-3';
          col.innerHTML =
            '<div class="card shadow-sm h-100">' +
              '<img src="' + e.target.result + '" class="card-img-top" style="object-fit: cover; height: 200px;">' +
              '<div class="card-body text-center">' +
                '<p class="card-text small mb-1">' + file.name + '</p>' +
                '<p class="text-muted small">ຂນາດ: ' + formatSize(file.size) + '</p>' +
              '</div>' +
            '</div>';
          preview.appendChild(col);
        };
        reader.readAsDataURL(file);
      })(input.files[i]);
    }
  });

  function formatSize(bytes) {
    return bytes > 1024 * 1024
      ? (bytes / (1024 * 1024)).toFixed(2) + ' MB'
      : (bytes / 1024).toFixed(2) + ' KB';
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete').forEach(function(button) {
      button.addEventListener('click', function () {
        var form = this.closest('form');
        Swal.fire({
          title: 'ທ່ານຕ້ອງການລົບຮູບນີ້ແທ້ ຫຼື ບໍ່!',
          text: "ຮູບພາບຈະຖືກລົບຖາວອນ!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'ຕົກລົງ',
          cancelButtonText: 'ຍົກເລີກ'
        }).then(function(result) {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
  });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>