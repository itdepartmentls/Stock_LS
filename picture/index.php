<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mapFile = 'uploads/filemap.json';

function loadMap($mapFile)
{
  if (file_exists($mapFile)) {
    $decoded = json_decode(file_get_contents($mapFile), true);
    return is_array($decoded) ? $decoded : array();
  }
  return array();
}

// ລຶບໄຟລ໌
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $safeName = basename($_POST['filename']);
  $file = 'uploads/' . $safeName;
  $map  = loadMap($mapFile);

  if (!file_exists($file)) {
    // ໄຟລ໌ບໍ່ມີຢູ່ໃນ disk ແລ້ວ (ອາດຖືກລົບໄປແລ້ວກ່ອນໜ້ານີ້)
    // -> ລ້າງອອກຈາກ map ໄດ້ເລີຍ ບໍ່ຕ້ອງ unlink
    unset($map[$safeName]);
    file_put_contents($mapFile, json_encode($map), LOCK_EX);
    $_SESSION['status'] = 'delete_success';
  } elseif (unlink($file)) {
    // unlink() ສຳເລັດແທ້ຈິງ ຈຶ່ງລົບອອກຈາກ map
    unset($map[$safeName]);
    file_put_contents($mapFile, json_encode($map), LOCK_EX);
    $_SESSION['status'] = 'delete_success';
  } else {
    // unlink() ລົ້ມເຫຼວ (ສ່ວນຫຼາຍແມ່ນບັນຫາ permission ຂອງໄຟລ໌/ໂຟນເດີ)
    // -> ຢ່າແຕະ map ເລີຍ ເພື່ອບໍ່ໃຫ້ໄຟລ໌ "ຄ້າງ" ຈົນຖືກ migrate ກັບເຂົ້າມາໃໝ່
    $_SESSION['status'] = 'error';
    $_SESSION['error_detail'] = 'ບໍ່ສາມາດລົບໄຟລ໌ໄດ້ (ອາດແມ່ນບັນຫາ permission): ' . $safeName;
  }
  header("Location: index.php");
  exit;
}

// ລຶບຫຼາຍຮູບພ້ອມກັນ (ຮູບທີ່ຖືກ tick ເລືອກ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_delete') {
  $selected = isset($_POST['filenames']) && is_array($_POST['filenames']) ? $_POST['filenames'] : array();
  $map = loadMap($mapFile);

  $deletedCount = 0;
  $failedNames  = array();

  foreach ($selected as $rawName) {
    $safeName = basename($rawName);
    $file = 'uploads/' . $safeName;

    if (!file_exists($file)) {
      // ບໍ່ມີໄຟລ໌ຢູ່ໃນ disk ແລ້ວ -> ລ້າງອອກຈາກ map ໄດ້ເລີຍ
      unset($map[$safeName]);
      $deletedCount++;
    } elseif (unlink($file)) {
      unset($map[$safeName]);
      $deletedCount++;
    } else {
      // unlink() ລົ້ມເຫຼວ -> ຢ່າແຕະ map ສຳລັບໄຟລ໌ນີ້
      $failedNames[] = $safeName;
    }
  }

  file_put_contents($mapFile, json_encode($map), LOCK_EX);

  if (empty($failedNames)) {
    $_SESSION['status'] = 'bulk_delete_success';
    $_SESSION['bulk_delete_count'] = $deletedCount;
  } else {
    $_SESSION['status'] = 'bulk_delete_partial';
    $_SESSION['bulk_delete_count'] = $deletedCount;
    $_SESSION['error_detail'] = 'ລົບບໍ່ໄດ້ (' . count($failedNames) . ' ຮູບ, ອາດແມ່ນບັນຫາ permission): ' . implode(', ', $failedNames);
  }

  header("Location: index.php");
  exit;
}

// ແກ້ໄຂຊື່ໄຟລ໌
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename') {
  $safeName = basename($_POST['filename']);
  $newDisplayName = trim($_POST['newname']);

  // ຖ້າຜູ້ໃຊ້ພິມນາມສະກຸນຮູບຕິດຕາມມາໂດຍບໍ່ຕັ້ງໃຈ (ເຊັ່ນ "70000001.png")
  // ໃຫ້ຕັດອອກ ເພື່ອໃຫ້ display name ຕົງກັບ Item_code ໃນ allstock.php ໄດ້
  $renameExt = strtolower(pathinfo($newDisplayName, PATHINFO_EXTENSION));
  if (in_array($renameExt, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'))) {
    $newDisplayName = pathinfo($newDisplayName, PATHINFO_FILENAME);
  }

  $map = loadMap($mapFile);
  if ($newDisplayName !== '' && array_key_exists($safeName, $map)) {
    $map[$safeName] = $newDisplayName;
    file_put_contents($mapFile, json_encode($map), LOCK_EX);
    $_SESSION['status'] = 'rename_success';
  } else {
    $_SESSION['status'] = 'error';
    $_SESSION['error_detail'] = 'ບໍ່ສາມາດແກ້ໄຂຊື່ໄດ້: ຊື່ຫວ່າງ ຫຼືບໍ່ພົບໄຟລ໌';
  }
  header("Location: index.php");
  exit;
}

// ແທນຮູບໃໝ່
if (isset($_POST['action']) && $_POST['action'] === 'replace') {

  $safeName = basename($_POST['filename']);

  if (!empty($_FILES['newimage']['tmp_name'])) {

    $ext = strtolower(pathinfo($_FILES['newimage']['name'], PATHINFO_EXTENSION));
    $allow = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif');

    if (in_array($ext, $allow)) {

      $dir = "uploads/";

      //  ດຶງນາມສະກຸນເດີມຂອງຮູບເກົ່າ
      $oldExt = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));

      //  ສ້າງຊື່ໃໝ່ໂດຍຮັກສາຊື່ຫຼັກເດີມ ແຕ່ປ່ຽນນາມສະກຸນຕາມຮູບໃໝ່
      $baseName = pathinfo($safeName, PATHINFO_FILENAME); // ຕົວຢ່າງ: "img_123" ຈາກ "img_123.jpg"
      $newSafe = $baseName . "." . $ext;  // ຮັກສາຊື່ເດີມ ແຕ່ປ່ຽນ .jpg ເປັນ .png ໄດ້
      $oldPath = $dir . $safeName;
      $newPath = $dir . $newSafe;

      // ລຶບຮູບເກົ່າ (ຖ້າມີ) -- ຕ້ອງກວດຜົນລັບຂອງ unlink()
      // ຖ້າ unlink() ລົ້ມເຫຼວ ໃຫ້ຢຸດທັນທີ ຢ່າ move ໄຟລ໌ໃໝ່ທັບ
      // ບໍ່ດັ່ງນັ້ນຈະມີ 2 ໄຟລ໌ຄ້າງຢູ່ໃນ disk (ໄຟລ໌ເກົ່າ + ໄຟລ໌ໃໝ່)
      // ຊຶ່ງເປັນສາເຫດຫນຶ່ງທີ່ເຮັດໃຫ້ຈຳນວນຮູບເພີ່ມຂຶ້ນເລື່ອຍໆ
      if (file_exists($oldPath) && !unlink($oldPath)) {
        $_SESSION['status'] = 'error';
        $_SESSION['error_detail'] = 'ບໍ່ສາມາດລົບຮູບເກົ່າກ່ອນປ່ຽນໄດ້ (ອາດແມ່ນບັນຫາ permission): ' . $safeName;
        header("Location: index.php");
        exit;
      }

      // ຍ້າຍໄຟລ໌ໃໝ່
      if (move_uploaded_file($_FILES['newimage']['tmp_name'], $newPath)) {

        // Update map json ຖ້າຊື່ປ່ຽນ (ນາມສະກຸນຕ່າງກັນ)
        if ($newSafe !== $safeName) {
          $map = loadMap($mapFile);

          if (isset($map[$safeName])) {
            $displayName = $map[$safeName];
            unset($map[$safeName]);
            $map[$newSafe] = $displayName;
            file_put_contents($mapFile, json_encode($map), LOCK_EX);
          }
        }

        $_SESSION['status'] = "replace_success";
      } else {
        $_SESSION['status'] = 'error';
        $_SESSION['error_detail'] = 'ບໍ່ສາມາດບັນທຶກຮູບໃໝ່ໄດ້: ' . $safeName;
      }
    } else {
      $_SESSION['status'] = 'error';
      $_SESSION['error_detail'] = 'ນາມສະກຸນໄຟລ໌ບໍ່ຖືກຕ້ອງ';
    }
  }

  header("Location: index.php");
  exit;
}

// ໂຫຼດ filemap
$map = loadMap($mapFile);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Upload Images Stock</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link type="logo_png" rel="icon" href="favicons.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- ===== Font Awesome 6 ===== -->
    <link rel="stylesheet" href="../css/vendor/fontawesome/all.min.css">

  <!-- icons ເພີ່ມເຕີມ -->
	<link rel="stylesheet" href="../css/vendor/uicons/uicons-solid-rounded.css">
	<link rel="stylesheet" href="../css/vendor/uicons/uicons-regular-rounded.css">
	<link rel="stylesheet" href="../css/vendor/uicons/uicons-regular-straight.css">

  <style>
    body {
      font-family: 'Poppins', 'Noto Sans Lao', sans-serif;
    }

    .card-img-top {
      object-fit: cover;
      height: 200px;
    }

    .btn-group-card {
      display: flex;
      gap: 4px;
      justify-content: center;
      flex-wrap: wrap;
    }

    #selectedCountBadge {
      display: none;
    }
  </style>
</head>

<body class="bg-light">

  <div class="container py-5">
    <h2 class="mb-4 text-center"><i class="fa-solid fa-cloud-arrow-up"></i> ອັບໂຫຼດຮູບພາບ (ເລືອກໄດ້ຫຼາຍໄຟລ໌)</h2>

    <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-5" id="uploadForm">
      <div class="mb-3">
        <input class="form-control" type="file" id="imageInput" name="images[]" multiple accept="image/*" required>
        <div class="form-text">
          <span id="selectedCountBadge" class="badge bg-primary"></span>
        </div>
      </div>
      <!-- ສົ່ງຈຳນວນຮູບທີ່ເລືອກໄປໃຫ້ upload.php ເພື່ອທຽບກັບຈຳນວນທີ່ server ໄດ້ຮັບຈິງ -->
      <input type="hidden" name="total_selected" id="totalSelectedInput" value="0">
      <button type="submit" class="btn btn-success"><i class="fa-solid fa-cloud-arrow-up"></i> ອັບໂຫຼດ</button>
      <a href="http://101.78.11.238:902/Dasborad.php" class="btn btn-primary"><i class="fa-solid fa-house"></i> ກັບຄືນໜ້າຫຼັກ</a>
    </form>

    <h4 class="mb-3">ຕົວຢ່າງຮູບກ່ອນອັບໂຫຼດ</h4>
    <div id="preview" class="row g-4"></div>
    <hr class="my-5">

    <h4 class="mb-3">ຄົ້ນຫາຊື່ຮູບພາບ</h4>
    <div class="mb-3 d-flex align-items-center gap-2">
      <div class="input-group" style="max-width:400px;">
        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="ຄົ້ນຫາຊື່ຮູບພາບ...">
      </div>
      <span id="searchCount" class="text-muted small"></span>
    </div>
    <div id="noResult" class="text-muted" style="display:none;">ບໍ່ພົບຮູບພາບທີ່ຄົ້ນຫາ <i class="fa-solid fa-face-frown"></i></div>

    <!-- ============ ແຖບເລືອກຮູບເພື່ອລົບຫຼາຍໆອັນພ້ອມກັນ ============ -->
    <!-- ຖ້າບໍ່ຕ້ອງການລົບຫຼາຍຮູບ ກໍ່ບໍ່ຈຳເປັນຕ້ອງກົດ "ເລືອກຫຼາຍຮູບ" ເລີຍ ໃຊ້ງານປົກກະຕິໄດ້ຄືເກົ່າ -->
    <div class="mb-3 d-flex align-items-center flex-wrap gap-2">
      <button type="button" id="toggleSelectMode" class="btn btn-outline-primary btn-sm">
        <i class="fa-solid fa-square-check"></i> ເລືອກຮູບພາບທັງໝົດ
      </button>
      <div id="bulkActionBar" class="d-flex align-items-center flex-wrap gap-2" style="display:none !important;">
        <div class="form-check ms-2">
          <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
          <label class="form-check-label" for="selectAllCheckbox">ເລືອກທັງໝົດ</label>
        </div>
        <span id="selectedCountLabel" class="text-muted small">ເລືອກແລ້ວ 0 ຮູບ</span>
        <button type="button" id="bulkDeleteBtn" class="btn btn-danger btn-sm" disabled>
          <i class="fa-solid fa-trash"></i> ລົບຮູບທີ່ເລືອກ
        </button>
        <button type="button" id="cancelSelectMode" class="btn btn-outline-secondary btn-sm">
          ຍົກເລີກ
        </button>
      </div>
    </div>

    <div class="row g-4" id="imageGrid">

      <h4 class="mb-3">
        <i class="fa-solid fa-image"></i> ຮູບພາບທີ່ອັບໂຫຼດແລ້ວ
        <span class="badge bg-success"><?php echo count($map); ?> ຮູບ</span>
      </h4>

      <?php
      $dir = 'uploads/';
      if (!is_dir($dir)) mkdir($dir, 0755, true);

      // =========================================================
      // Migrate ໄຟລ໌ເກົ່າທີ່ຢູ່ໃນ disk ແຕ່ບໍ່ຢູ່ໃນ map (ເຊັ່ນຮູບທີ່ອັບໂຫຼດຜ່ານ
      // ວິທີອື່ນທີ່ບໍ່ແມ່ນ script ນີ້) -- ນີ້ຄືສ່ວນທີ່ເຄີຍເຮັດໃຫ້ຈຳນວນຮູບ
      // "ເພີ່ມຂຶ້ນເລື່ອຍໆ" ຖ້າ delete/replace ລົ້ມເຫຼວແບບງຽບໆ ແລ້ວປ່ອຍໄຟລ໌ຄ້າງໄວ້
      // ໃນ disk ໂດຍທີ່ຖືກລົບອອກຈາກ map ໄປແລ້ວ. ຕອນນີ້ delete/replace ຖືກແກ້ໄຂ
      // ໃຫ້ບໍ່ລົບອອກຈາກ map ຖ້າ unlink() ບໍ່ສຳເລັດ ດັ່ງນັ້ນບັນຫານີ້ຈະບໍ່ເກີດຄືນອີກ.
      // =========================================================
      $mapChanged = false;
      $allFiles   = array_diff(scandir($dir), array('.', '..', 'filemap.json'));
      $mapKeys    = array_keys($map);
      foreach ($allFiles as $oldFile) {
        if (!in_array($oldFile, $mapKeys)) {
          $ext     = strtolower(pathinfo($oldFile, PATHINFO_EXTENSION));
          $newSafe = uniqid('img_') . '.' . $ext;
          if (rename($dir . $oldFile, $dir . $newSafe)) {
            $map[$newSafe] = $oldFile;
            $mapChanged = true;
          }
        }
      }
      if ($mapChanged) file_put_contents($mapFile, json_encode($map), LOCK_EX);

      // ລ້າງນາມສະກຸນທີ່ຄ້າງໃນ display name ຂອງຮູບເກົ່າ (ຈາກກ່ອນມີການແກ້ໄຂ
      // upload.php) ໃຫ້ອັດຕະໂນມັດ ເຊັ່ນ "70000001.png" -> "70000001"
      // ເພື່ອໃຫ້ຕົງກັບ Item_code ໃນ allstock.php ໂດຍບໍ່ຕ້ອງແກ້ໄຂຊື່ດ້ວຍມືເອງ
      $extMigrated = false;
      $allowExt = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'avif');
      foreach ($map as $mSafe => $mOrig) {
        $mExt = strtolower(pathinfo($mOrig, PATHINFO_EXTENSION));
        if (in_array($mExt, $allowExt)) {
          $map[$mSafe] = pathinfo($mOrig, PATHINFO_FILENAME);
          $extMigrated = true;
        }
      }
      if ($extMigrated) file_put_contents($mapFile, json_encode($map), LOCK_EX);

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

          $eSafe = htmlspecialchars($safeName,     ENT_QUOTES, 'UTF-8');
          $eOrig = htmlspecialchars($originalName, ENT_QUOTES, 'UTF-8');

          // ຕິດ ?v=timestamp ຕໍ່ທ້າຍ URL ຮູບ ເພື່ອບັງຄັບ browser ໂຫຼດຮູບໃໝ່
          // ຫຼັງຈາກປ່ຽນຮູບ (cache-busting) — ຖ້າບໍ່ໃສ່, browser ຈະສະແດງ
          // ຮູບເກົ່າຈາກ cache ເມື່ອຊື່ໄຟລ໌ (ນາມສະກຸນ) ບໍ່ປ່ຽນ
          $imgSrc = htmlspecialchars($dir . $safeName) . '?v=' . filemtime($fileUrl);

          echo '<div class="col-sm-6 col-md-4 col-lg-3 image-card" data-name="' . strtolower($eOrig) . '" data-safe="' . $eSafe . '">';
          echo '<div class="card shadow-sm h-100 position-relative">';
          echo '<div class="select-checkbox-wrap" style="display:none;position:absolute;top:8px;left:8px;z-index:5;">'
            . '<input type="checkbox" class="form-check-input select-item-checkbox" style="width:1.4em;height:1.4em;cursor:pointer;" data-safe="' . $eSafe . '">'
            . '</div>';
          echo '<img src="' . $imgSrc . '"'
            . ' class="card-img-top img-preview" style="cursor:pointer;"'
            . ' data-src="' . $imgSrc . '"'
            . ' data-label="' . $eOrig . '"'
            . ' title="ກົດເພື່ອເບິ່ງຮູບໃຫຍ່">';
          echo '<div class="card-body text-center">';
          echo '<p class="card-text small mb-1 fw-semibold">' . $eOrig . '</p>';
          echo '<p class="text-muted small">ຂະໜາດ: ' . $formattedSize . '</p>';
          echo '<div class="btn-group-card">';

          // ປຸ່ມປ່ຽນຮູບໃໝ່
          echo '<button type="button" class="btn btn-sm btn-secondary btn-replace"'
            . ' data-safe="' . $eSafe . '">'
            . '<i class="fa-solid fa-code-compare"></i> ປ່ຽນຮູບພາບ</button>';

          // ປຸ່ມແກ້ໄຂຊື່
          echo '<button type="button" class="btn btn-sm btn-warning btn-rename"'
            . ' data-safe="' . $eSafe . '"'
            . ' data-name="' . $eOrig . '">'
            . '<i class="fa-solid fa-pen-to-square"></i> ແກ້ໄຂຊື່</button>';

          // ປຸ່ມລຶບ
          echo '<form method="post" class="delete-form" style="display:inline">';
          echo '<input type="hidden" name="action" value="delete">';
          echo '<input type="hidden" name="filename" value="' . $eSafe . '">';
          echo '<button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fa-solid fa-trash"></i> ລົບ</button>';
          echo '</form>';

          echo '</div></div></div></div>';
        }
      }
      ?>
    </div>
  </div>

  <!-- Lightbox Modal -->
  <div id="lightboxModal" style="
    display:none; position:fixed; z-index:9999; top:0; left:0;
    width:100%; height:100%; background:rgba(0,0,0,0.85);
    align-items:center; justify-content:center; flex-direction:column;">
    <button id="lightboxClose" style="position:fixed;top:16px;right:20px;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;">✕</button>
    <button id="lightboxPrev" style="position:fixed;left:12px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:2rem;padding:8px 14px;border-radius:50%;cursor:pointer;">&#8249;</button>
    <img id="lightboxImg" src="" style="max-width:90vw;max-height:80vh;border-radius:8px;box-shadow:0 4px 30px rgba(0,0,0,0.5);object-fit:contain;">
    <div style="margin-top:12px;text-align:center;">
      <p id="lightboxLabel" style="color:#fff;font-size:1rem;margin:0;"></p>
      <p id="lightboxCounter" style="color:#aaa;font-size:0.85rem;margin:4px 0 0;"></p>
    </div>
    <button id="lightboxNext" style="position:fixed;right:12px;top:50%;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:2rem;padding:8px 14px;border-radius:50%;cursor:pointer;">&#8250;</button>
  </div>

  <!-- Form hidden -->
  <form id="renameForm" method="post" style="display:none">
    <input type="hidden" name="action" value="rename">
    <input type="hidden" name="filename" id="renameFilename">
    <input type="hidden" name="newname" id="renameNewname">
  </form>
  <form id="replaceForm" method="post" enctype="multipart/form-data" style="display:none">
    <input type="hidden" name="action" value="replace">
    <input type="hidden" name="filename" id="replaceFilename">
    <input type="file" name="newimage" id="replaceFileInput" accept="image/*">
  </form>
  <form id="bulkDeleteForm" method="post" style="display:none">
    <input type="hidden" name="action" value="bulk_delete">
    <!-- checkbox name="filenames[]" ຈະຖືກເພີ່ມເຂົ້າດ້ວຍ JS ກ່ອນ submit -->
  </form>

  <?php
  if (isset($_SESSION['status'])) {
    $s = $_SESSION['status'];
    if ($s === 'delete_success')
      echo "<script>Swal.fire({icon:'success',title:'ລົບຮູບພາບສຳເລັດ',timer:1500,showConfirmButton:false});</script>";
    elseif ($s === 'rename_success')
      echo "<script>Swal.fire({icon:'success',title:'ແກ້ໄຂຊື່ສຳເລັດ',timer:1500,showConfirmButton:false});</script>";
    elseif ($s === 'replace_success')
      echo "<script>Swal.fire({icon:'success',title:'ປ່ຽນຮູບສຳເລັດ',timer:1500,showConfirmButton:false});</script>";
    elseif ($s === 'bulk_delete_success') {
      $n = isset($_SESSION['bulk_delete_count']) ? (int) $_SESSION['bulk_delete_count'] : 0;
      echo "<script>Swal.fire({icon:'success',title:'ລົບຮູບພາບສຳເລັດ',text:'ລົບແລ້ວ $n ຮູບ',timer:1800,showConfirmButton:false});</script>";
    } elseif ($s === 'bulk_delete_partial') {
      $n = isset($_SESSION['bulk_delete_count']) ? (int) $_SESSION['bulk_delete_count'] : 0;
      $detail = isset($_SESSION['error_detail']) ? addslashes($_SESSION['error_detail']) : '';
      echo "<script>Swal.fire({icon:'warning',title:'ລົບສຳເລັດບາງສ່ວນ',html:'ລົບແລ້ວ $n ຮູບ<br><span style=\\'color:#c0392b;font-size:13px;\\'>$detail</span>'});</script>";
    } elseif ($s === 'error') {
      $detail = isset($_SESSION['error_detail']) ? addslashes($_SESSION['error_detail']) : 'ກະລຸນາລອງໃໝ່';
      echo "<script>Swal.fire({icon:'error',title:'ເກີດຂໍ້ຜິດພາດ',text:'$detail'});</script>";
    }
    unset($_SESSION['status']);
    unset($_SESSION['error_detail']);
    unset($_SESSION['bulk_delete_count']);
  }
  ?>

  <script>
    // ============ Preview Upload + ນັບຈຳນວນຮູບທີ່ເລືອກ ============
    var input = document.getElementById('imageInput');
    var preview = document.getElementById('preview');
    var totalSelectedInput = document.getElementById('totalSelectedInput');
    var selectedCountBadge = document.getElementById('selectedCountBadge');

    input.addEventListener('change', function() {
      preview.innerHTML = '';

      // ບັນທຶກຈຳນວນຮູບທີ່ເລືອກ ເພື່ອສົ່ງໄປໃຫ້ server ທຽບຄືນ
      totalSelectedInput.value = input.files.length;
      if (input.files.length > 0) {
        selectedCountBadge.style.display = 'inline-block';
        selectedCountBadge.textContent = 'ເລືອກແລ້ວ ' + input.files.length + ' ຮູບ';
      } else {
        selectedCountBadge.style.display = 'none';
      }

      for (var i = 0; i < input.files.length; i++) {
        (function(file) {
          var reader = new FileReader();
          reader.onload = function(e) {
            var col = document.createElement('div');
            col.className = 'col-sm-6 col-md-4 col-lg-3';
            col.innerHTML =
              '<div class="card shadow-sm h-100">' +
              '<img src="' + e.target.result + '" class="card-img-top">' +
              '<div class="card-body text-center">' +
              '<p class="card-text small mb-1">' + file.name + '</p>' +
              '<p class="text-muted small">ຂນາດ: ' + formatSize(file.size) + '</p>' +
              '</div></div>';
            preview.appendChild(col);
          };
          reader.readAsDataURL(file);
        })(input.files[i]);
      }
    });

    // ຢືນຢັນຄືນອີກຄັ້ງກ່ອນ submit (ກັນກໍລະນີ browser autofill ຫຼືອື່ນໆ)
    document.getElementById('uploadForm').addEventListener('submit', function() {
      totalSelectedInput.value = input.files.length;
    });

    function formatSize(bytes) {
      return bytes > 1024 * 1024 ? (bytes / (1024 * 1024)).toFixed(2) + ' MB' : (bytes / 1024).toFixed(2) + ' KB';
    }

    // ============ Search ============
    var searchInput = document.getElementById('searchInput');
    var noResult = document.getElementById('noResult');
    var searchCount = document.getElementById('searchCount');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        var keyword = this.value.trim().toLowerCase();
        var cards = document.querySelectorAll('.image-card');
        var visible = 0;
        cards.forEach(function(card) {
          var name = card.getAttribute('data-name') || '';
          if (keyword === '' || name.indexOf(keyword) !== -1) {
            card.style.display = '';
            visible++;
          } else {
            card.style.display = 'none';
          }
        });
        searchCount.textContent = keyword === '' ? '' : 'ພົບ ' + visible + ' ຮູບ';
        noResult.style.display = (keyword !== '' && visible === 0) ? '' : 'none';
      });
    }

    // ============ Lightbox ============
    var lightboxImages = [];
    var lightboxIndex = 0;

    function openLightbox(index) {
      lightboxIndex = index;
      updateLightbox();
      document.getElementById('lightboxModal').style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      document.getElementById('lightboxModal').style.display = 'none';
      document.body.style.overflow = '';
    }

    function updateLightbox() {
      var item = lightboxImages[lightboxIndex];
      document.getElementById('lightboxImg').src = item.src;
      document.getElementById('lightboxLabel').textContent = item.label;
      document.getElementById('lightboxCounter').textContent = (lightboxIndex + 1) + ' / ' + lightboxImages.length;
    }

    function lightboxPrev() {
      lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
      updateLightbox();
    }

    function lightboxNext() {
      lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
      updateLightbox();
    }

    // ============ DOM Ready ============
    document.addEventListener('DOMContentLoaded', function() {

      // Lightbox init
      document.querySelectorAll('.img-preview').forEach(function(img, i) {
        lightboxImages.push({
          src: img.getAttribute('data-src'),
          label: img.getAttribute('data-label')
        });
        img.addEventListener('click', function() {
          openLightbox(i);
        });
      });
      document.getElementById('lightboxClose').addEventListener('click', closeLightbox);
      document.getElementById('lightboxPrev').addEventListener('click', lightboxPrev);
      document.getElementById('lightboxNext').addEventListener('click', lightboxNext);
      document.getElementById('lightboxModal').addEventListener('click', function(e) {
        if (e.target === this) closeLightbox();
      });
      document.addEventListener('keydown', function(e) {
        var modal = document.getElementById('lightboxModal');
        if (modal.style.display === 'none') return;
        if (e.key === 'ArrowLeft') lightboxPrev();
        if (e.key === 'ArrowRight') lightboxNext();
        if (e.key === 'Escape') closeLightbox();
      });

      // ປຸ່ມປ່ຽນຮູບໃໝ່
      document.querySelectorAll('.btn-replace').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var safeName = this.getAttribute('data-safe');
          document.getElementById('replaceFilename').value = safeName;

          // trigger file picker
          var fileInput = document.getElementById('replaceFileInput');
          fileInput.value = '';
          fileInput.onchange = function() {
            if (fileInput.files.length === 0) return;
            var file = fileInput.files[0];

            // Preview ກ່ອນຢືນຢັນ
            var reader = new FileReader();
            reader.onload = function(e) {
              Swal.fire({
                title: 'ຢືນຢັນປ່ຽນຮູບ',
                html: '<p style="margin-bottom:8px;">ຮູບໃໝ່ທີ່ຈະປ່ຽນ:</p>' +
                  '<img src="' + e.target.result + '" style="max-width:100%;max-height:240px;border-radius:8px;">',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'ຢືນຢັນປ່ຽນ',
                cancelButtonText: 'ຍົກເລີກ'
              }).then(function(result) {
                if (result.isConfirmed) {
                  document.getElementById('replaceForm').submit();
                }
              });
            };
            reader.readAsDataURL(file);
          };
          fileInput.click();
        });
      });

      // ປຸ່ມລຶບ
      document.querySelectorAll('.btn-delete').forEach(function(button) {
        button.addEventListener('click', function() {
          var form = this.closest('form');
          Swal.fire({
            title: 'ທ່ານຕ້ອງການລົບຮູບນີ້ແທ້ ຫຼື ບໍ່!',
            text: 'ຮູບພາບຈະຖືກລົບຖາວອນ!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ຕົກລົງ',
            cancelButtonText: 'ຍົກເລີກ'
          }).then(function(result) {
            if (result.isConfirmed) form.submit();
          });
        });
      });

      // ປຸ່ມແກ້ໄຂຊື່
      document.querySelectorAll('.btn-rename').forEach(function(button) {
        button.addEventListener('click', function() {
          var safeName = this.getAttribute('data-safe');
          var currentName = this.getAttribute('data-name');
          Swal.fire({
            title: '✏️ ແກ້ໄຂຊື່ຮູບພາບ',
            input: 'text',
            inputValue: currentName,
            inputPlaceholder: 'ປ້ອນຊື່ໃໝ່...',
            showCancelButton: true,
            confirmButtonText: 'ບັນທຶກ',
            cancelButtonText: 'ຍົກເລີກ',
            confirmButtonColor: '#198754',
            inputValidator: function(value) {
              if (!value || value.trim() === '') return 'ກະລຸນາປ້ອນຊື່ກ່ອນ!';
            }
          }).then(function(result) {
            if (result.isConfirmed) {
              document.getElementById('renameFilename').value = safeName;
              document.getElementById('renameNewname').value = result.value.trim();
              document.getElementById('renameForm').submit();
            }
          });
        });
      });
      // ============ ເລືອກຫຼາຍຮູບເພື່ອລົບພ້ອມກັນ ============
      // ຖ້າຜູ້ໃຊ້ບໍ່ກົດ "ເລືອກຫຼາຍຮູບ" ເລີຍ ຈະບໍ່ມີຫຍັງປ່ຽນແປງ ໃຊ້ງານແບບເກົ່າໄດ້ປົກກະຕິ
      var toggleSelectModeBtn = document.getElementById('toggleSelectMode');
      var cancelSelectModeBtn = document.getElementById('cancelSelectMode');
      var bulkActionBar = document.getElementById('bulkActionBar');
      var selectAllCheckbox = document.getElementById('selectAllCheckbox');
      var selectedCountLabel = document.getElementById('selectedCountLabel');
      var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
      var bulkDeleteForm = document.getElementById('bulkDeleteForm');
      var selectMode = false;

      function getCheckboxWraps() {
        // ພຽງແຕ່ card ທີ່ກຳລັງສະແດງຢູ່ (ບໍ່ຖືກ search filter ອອກ)
        return Array.prototype.slice.call(document.querySelectorAll('.image-card .select-checkbox-wrap'));
      }
      function getItemCheckboxes(onlyVisible) {
        var boxes = Array.prototype.slice.call(document.querySelectorAll('.select-item-checkbox'));
        if (!onlyVisible) return boxes;
        return boxes.filter(function(cb) {
          var card = cb.closest('.image-card');
          return card && card.style.display !== 'none';
        });
      }
      function updateSelectedCount() {
        var checked = getItemCheckboxes(false).filter(function(cb) { return cb.checked; });
        selectedCountLabel.textContent = 'ເລືອກແລ້ວ ' + checked.length + ' ຮູບ';
        bulkDeleteBtn.disabled = checked.length === 0;
      }

      function setSelectMode(on) {
        selectMode = on;
        getCheckboxWraps().forEach(function(w) { w.style.display = on ? 'block' : 'none'; });
        bulkActionBar.style.setProperty('display', on ? 'flex' : 'none', 'important');
        toggleSelectModeBtn.style.display = on ? 'none' : 'inline-block';
        if (!on) {
          getItemCheckboxes(false).forEach(function(cb) { cb.checked = false; });
          selectAllCheckbox.checked = false;
        }
        updateSelectedCount();
      }

      if (toggleSelectModeBtn) {
        toggleSelectModeBtn.addEventListener('click', function() { setSelectMode(true); });
      }
      if (cancelSelectModeBtn) {
        cancelSelectModeBtn.addEventListener('click', function() { setSelectMode(false); });
      }

      // ຕິກ checkbox ແຕ່ລະຮູບ -> ອັບເດດຕົວນັບ
      document.addEventListener('change', function(e) {
        if (e.target && e.target.classList && e.target.classList.contains('select-item-checkbox')) {
          updateSelectedCount();
          // ຖ້າຄົບທຸກອັນທີ່ເບິ່ງເຫັນ -> ຕິກ "ເລືອກທັງໝົດ" ໃຫ້ອັດຕະໂນມັດ
          var visible = getItemCheckboxes(true);
          selectAllCheckbox.checked = visible.length > 0 && visible.every(function(cb) { return cb.checked; });
        }
      });

      // ເລືອກທັງໝົດ (ສະເພາະຮູບທີ່ກຳລັງສະແດງຢູ່ ຖ້າມີການຄົ້ນຫາກອງໄວ້)
      if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
          getItemCheckboxes(true).forEach(function(cb) { cb.checked = selectAllCheckbox.checked; });
          updateSelectedCount();
        });
      }

      // ຖ້າຄົ້ນຫາຂະນະຢູ່ໃນໂໝດເລືອກ -> ອັບເດດຕົວນັບຄືນ (ຮູບທີ່ຖືກເຊື່ອງ ບໍ່ນັບ)
      if (searchInput) {
        searchInput.addEventListener('input', function() {
          if (selectMode) updateSelectedCount();
        });
      }

      // ປຸ່ມລົບຮູບທີ່ເລືອກ
      if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
          var checked = getItemCheckboxes(false).filter(function(cb) { return cb.checked; });
          if (checked.length === 0) return;

          Swal.fire({
            title: 'ຢືນຢັນລົບ ' + checked.length + ' ຮູບ?',
            text: 'ຮູບພາບທີ່ເລືອກທັງໝົດຈະຖືກລົບຖາວອນ, ບໍ່ສາມາດກູ້ຄືນໄດ້!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ລົບທັງໝົດ',
            cancelButtonText: 'ຍົກເລີກ'
          }).then(function(result) {
            if (!result.isConfirmed) return;

            // ລ້າງ input ເກົ່າໃນ form ກ່ອນ ແລ້ວເພີ່ມ filenames[] ຕາມທີ່ຖືກເລືອກ
            bulkDeleteForm.querySelectorAll('input[name="filenames[]"]').forEach(function(el) { el.remove(); });
            checked.forEach(function(cb) {
              var hidden = document.createElement('input');
              hidden.type = 'hidden';
              hidden.name = 'filenames[]';
              hidden.value = cb.getAttribute('data-safe');
              bulkDeleteForm.appendChild(hidden);
            });
            bulkDeleteForm.submit();
          });
        });
      }

    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>