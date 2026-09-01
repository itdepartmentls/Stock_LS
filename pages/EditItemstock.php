<?php
require_once __DIR__ . '/../includes/conn.php';
@session_start();
if ($_SESSION["user"] == "" or $_SESSION["Namepro"] == "" or $_SESSION["iduser"] == "") {
    echo "<script>window.location = 'index.php';</script>";
    exit;
}

// =========================================================
// 🔧 ໃໝ່: ດຶງຮູບພາບຈາກ Item_code ແບບສົດໆ ຄືກັນກັບ prostock.php
// (ຄ່າ "picture" ທີ່ຄ້າງໃນ DB ອາດເປັນ path ເກົ່າທີ່ບໍ່ມີໄຟລ໌ຈິງແລ້ວ
// ຍ້ອນຊື່ໄຟລ໌ຮູບຖືກສ້າງໃໝ່ແບບສຸ່ມ (uniqid) ທຸກຄັ້ງທີ່ອັບໂຫລດ)
// =========================================================
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="image/logoETL.jpg">
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">
    <link rel="stylesheet" href="css/vendor/bootstrap/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

    <title>ການລາຍງານ</title>
    <link href="css/app.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/pages/EditItemstock.css">
    <?php
    $proo = $_SESSION["Namepro"];
    if ($_SESSION["iduser"] <> "404" and $_SESSION["iduser"] <> "30" and $_SESSION["iduser"] <> "194" and $_SESSION["iduser"] <> "793") {
        $sql = "SELECT COUNT(a.S_Status) as 'cccount' FROM request a WHERE a.S_Status in ('1') and a.Province = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $proo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $sql = "SELECT COUNT(a.S_Status) as 'cccount' FROM request a WHERE a.S_Status = '1'";
        $result = $conn->query($sql);
    }
    mysqli_set_charset(@$conn, "utf8");
    $row = $result->fetch_assoc();
    ?>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <div id="page-content-wrapper">
            <?php
            // 🔧 FIX: ກວດວ່າມີ id ຖືກສົ່ງມາແທ້ ກ່ອນຈະໃຊ້ (ແກ້ Undefined array key)
            $Itemms = isset($_REQUEST['Itemms']) ? trim($_REQUEST['Itemms']) : '';
            $id     = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : '';

            $test = null;

            if ($id === '') {
                // 🔧 FIX: ບໍ່ມີ id ສົ່ງມາເລີຍ -> ແຈ້ງເຕືອນແທນທີ່ຈະ error
                echo '<br><div class="alert alert-danger text-center">ບໍ່ພົບຂໍ້ມູນ: ບໍ່ໄດ້ລະບຸອຸປະກອນ. ກະລຸນາເປີດໜ້ານີ້ຈາກລາຍການ stock.</div>';
            } else {
                // 🔧 FIX: ໃຊ້ prepared statement ແທນການ concat string ໂດຍກົງ (ປ້ອງກັນ SQL Injection)
                $stmt_get = $conn->prepare("SELECT * FROM stock a WHERE a.id = ?");
                $stmt_get->bind_param("s", $id);
                $stmt_get->execute();
                $result = $stmt_get->get_result();
                $test = $result->fetch_assoc(); // null ຖ້າບໍ່ພົບແຖວ
                $stmt_get->close();

                if (!$test) {
                    // 🔧 FIX: ບໍ່ພົບແຖວທີ່ກົງກັນ -> ແຈ້ງເຕືອນແທນທີ່ຈະ error
                    echo '<br><div class="alert alert-danger text-center">ບໍ່ພົບຂໍ້ມູນອຸປະກອນ (id = ' . htmlspecialchars($id) . ')</div>';
                }
            }

            // 🔧 FIX: ດຶງຄ່າແບບປອດໄພ (null coalescing) ແທນ $test['Items'] ໂດຍກົງ
            $Items   = $test['Items'] ?? '';
            $Items_CN = $test['Item_name_Chinese'] ?? '';
            $Items_Size = $test['Size'] ?? '';
            $Items_Model = $test['Model'] ?? '';
            $Items_Part = $test['Items_Part'] ?? '';

            // 🔧 ໃໝ່: ໃຊ້ຮູບຈາກ filemap (ຫາຈາກ Item_code) ກ່ອນ, ຖ້າບໍ່ພົບຈຶ່ງໃຊ້ຄ່າ picture ໃນ DB ເປັນ fallback
            $resolvedPicture = getItemImageUrl($test['Item_code'] ?? '', $imageByItemCode, $uploadsBaseDir, $uploadsBaseUrl);
            $picture = $resolvedPicture ?? ($test['picture'] ?? '');
            ?>

            <?php if ($test): // 🔧 FIX: ສະແດງຟອມສະເພາະຕອນພົບຂໍ້ມູນເທົ່ານັ້ນ 
            ?>
                <div class="edit-page-bg">
                    <div class="edit-card">
                        <div class="edit-card-header">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            <h5>ແກ້ໄຂຂໍ້ມູນອຸປະກອນ</h5>
                        </div>
                        <div class="edit-card-body">
                            <form id="form1" name="form1" method="post" action="EditItemstock.php?id=<?php echo urlencode($id) ?>" enctype="multipart/form-data">
                                <input hidden type="text" name="ItemsOld" value="<?php echo htmlspecialchars($Items) ?>">
                                <input hidden type="text" name="Itemsid" value="<?php echo htmlspecialchars($id) ?>">
                                <!-- 🔧 ເກັບຄ່າ link ເກົ່າໄວ້ ເພື່ອໃຊ້ເປັນຄ່າ default ຖ້າບໍ່ໄດ້ເລືອກຮູບໃໝ່ -->
                                <input hidden type="text" name="pictureOld" value="<?php echo htmlspecialchars($picture) ?>">

                                <div class="edit-grid">
                                    <!-- ===== ຝັ່ງຮູບພາບ ===== -->
                                    <div class="pic-panel">
                                        <div class="pic-frame">
                                            <img id="picPreview" src="<?php echo htmlspecialchars($picture) ?>" style="<?php echo empty($picture) ? 'display:none;' : '' ?>">
                                            <i class="fa fa-image pic-placeholder" id="picPlaceholder" style="<?php echo empty($picture) ? '' : 'display:none;' ?>"></i>
                                        </div>
                                        <!-- 🔧 ໃໝ່: ເລືອກຮູບຈາກເຄື່ອງໂດຍກົງ ແທນການພິມ link ດ້ວຍມື -->
                                        <input type="file" name="pictureFile" id="pictureFile" class="d-none" accept="image/png,image/jpeg,image/gif,image/webp" onchange="previewPic(this)">
                                        <button type="button" class="pic-upload-btn" onclick="document.getElementById('pictureFile').click()">
                                            <i class="fa fa-upload" aria-hidden="true"></i> ເລືອກຮູບພາບໃໝ່
                                        </button>
                                        <div class="pic-hint">ຮອງຮັບ: JPG, PNG, GIF, WEBP<br>ຖ້າບໍ່ເລືອກຮູບໃໝ່ ຮູບເກົ່າຈະຄົງໄວ້</div>
                                    </div>

                                    <!-- ===== ຝັ່ງຟິວຂໍ້ມູນ ===== -->
                                    <div class="field-grid">
                                        <div class="full-width">
                                            <span class="field-label">ຊື່ອຸປະກອນ</span>
                                            <input type="text" name="ItemsNew" class="form-control" value="<?php echo htmlspecialchars($Items) ?>">
                                        </div>

                                        <div>
                                            <span class="field-label">ຊື່ພາສາຈີນ</span>
                                            <input type="text" name="ItemsNew_CN" class="form-control" value="<?php echo htmlspecialchars($Items_CN) ?>">
                                        </div>

                                        <div>
                                            <span class="field-label">ຂະໜາດ</span>
                                            <input type="text" name="ItemsNew_Size" class="form-control" value="<?php echo htmlspecialchars($Items_Size) ?>">
                                        </div>

                                        <div>
                                            <span class="field-label">Model</span>
                                            <input type="text" name="ItemsNew_Model" class="form-control" value="<?php echo htmlspecialchars($Items_Model) ?>">
                                        </div>

                                        <div>
                                            <span class="field-label">ພາກສ່ວນ</span>
                                            <input type="text" name="ItemsNew_Part" class="form-control" value="<?php echo htmlspecialchars($Items_Part) ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="edit-actions">
                                    <button type="submit" name="Edit" class="btn btn-save" onclick="return confirm('ຕ້ອງການແກ້ໄຂຂໍ້ມູນ ແທ້ບໍ່?')">
                                        <i class="fa fa-check" aria-hidden="true"></i> ບັນທຶກການແກ້ໄຂ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    function previewPic(input) {
                        if (input.files && input.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                var img = document.getElementById('picPreview');
                                var placeholder = document.getElementById('picPlaceholder');
                                img.src = e.target.result;
                                img.style.display = 'block';
                                placeholder.style.display = 'none';
                            };
                            reader.readAsDataURL(input.files[0]);
                        }
                    }
                </script>

                <?php
                if (isset($_POST["Edit"])) {
                    $itemsNew = $_POST["ItemsNew"];
                    $itemsIdPost = $_POST["Itemsid"];
                    $itemsOld = $_POST["ItemsOld"];

                    // 🔧 ໃໝ່: ຄ່າ 4 ຊ່ອງເພີ່ມເຕີມທີ່ຜູ້ໃຊ້ພິມສົ່ງມາຈາກຟອມ
                    $Items_CN    = $_POST["ItemsNew_CN"] ?? '';
                    $Items_Size  = $_POST["ItemsNew_Size"] ?? '';
                    $Items_Model = $_POST["ItemsNew_Model"] ?? '';
                    $Items_Part  = $_POST["ItemsNew_Part"] ?? '';

                    // 🔧 ໃໝ່: ຄ່າ picture ເລີ່ມຕົ້ນ = link ເກົ່າ (ບໍ່ປ່ຽນ ຖ້າບໍ່ໄດ້ອັບໂຫລດຮູບໃໝ່)
                    $pictureNew = $_POST["pictureOld"] ?? '';

                    // 🔧 ໃໝ່: ຖ້າມີການເລືອກໄຟລ໌ຮູບໃໝ່ -> ອັບໂຫລດ ແລະ ສ້າງ link ໃໝ່ອັດຕະໂນມັດ
                    if (isset($_FILES['pictureFile']) && $_FILES['pictureFile']['error'] === UPLOAD_ERR_OK && $_FILES['pictureFile']['size'] > 0) {
                        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $origName = $_FILES['pictureFile']['name'];
                        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                        if (in_array($ext, $allowedExt, true)) {
                            $uploadDir = __DIR__ . '/../picture/uploads/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            // ຊື່ໄຟລ໌ໃໝ່ບໍ່ຊ້ຳກັນ (ກັນຮູບເກົ່າ/ໃໝ່ conflict ກັນ)
                            $newFileName = 'img_' . uniqid('', true) . '.' . $ext;
                            $destPath = $uploadDir . $newFileName;

                            if (move_uploaded_file($_FILES['pictureFile']['tmp_name'], $destPath)) {
                                // 🔧 ໃຊ້ relative path ແທນ URL ເຕັມ ເພື່ອບໍ່ໃຫ້ພັງຖ້າປ່ຽນ domain/IP/port
                                $pictureNew = 'picture/uploads/' . $newFileName;

                                // 🔧 ໃໝ່: ອັບເດດ filemap.json (Item_code -> safeFileName) ນຳ
                                // ຍ້ອນ prostock.php ດຶງຮູບໂດຍອີງໃສ່ filemap.json ເທົ່ານັ້ນ,
                                // ບໍ່ໄດ້ອີງໃສ່ຄ່າ picture ໃນ DB. ຖ້າບໍ່ອັບເດດ filemap,
                                // ຮູບໃໝ່ຈະບໍ່ສະແດງຢູ່ໜ້າ list ເລີຍ.
                                $itemCodeForMap = trim((string) ($test['Item_code'] ?? ''));
                                if ($itemCodeForMap !== '') {
                                    $mapFile = $uploadDir . 'filemap.json';
                                    $mapData = file_exists($mapFile) ? json_decode(file_get_contents($mapFile), true) : [];
                                    if (!is_array($mapData)) {
                                        $mapData = [];
                                    }
                                    $mapData[$newFileName] = $itemCodeForMap;
                                    $writeOk = @file_put_contents($mapFile, json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
                                    if ($writeOk === false) {
                                        // 🔧 ບອກເຫດຜົນຢ່າງຈະແຈ້ງ ແທນທີ່ຈະລົ້ມເຫລວແບບງຽບໆ (ອາດເປັນບັນຫາ permission)
                                        echo '<div class="alert alert-warning text-center">ອັບໂຫລດຮູບສຳເລັດ ແຕ່ບໍ່ສາມາດອັບເດດ filemap.json ໄດ້ (ກວດສອບສິດ write ຂອງໂຟນເດີ picture/uploads/)</div>';
                                        error_log('EditItemstock.php: failed to write filemap.json at ' . $mapFile);
                                    }
                                } else {
                                    // 🔧 ບໍ່ມີ Item_code -> ບໍ່ສາມາດ sync ຮູບໄປໜ້າ list ໄດ້ (prostock.php/allstock.php ໃຊ້ Item_code ເປັນຕົວຈັບຄູ່)
                                    echo '<div class="alert alert-warning text-center">ອັບໂຫລດຮູບສຳເລັດ ແຕ່ອຸປະກອນນີ້ບໍ່ມີ Item_code ໃນຖານຂໍ້ມູນ ຈຶ່ງບໍ່ສາມາດສະແດງຢູ່ໜ້າ list ໄດ້</div>';
                                    error_log('EditItemstock.php: item id=' . ($test['id'] ?? '') . ' has no Item_code, cannot sync picture to filemap.json');
                                }
                            } else {
                                echo '<div class="alert alert-danger text-center">ອັບໂຫລດຮູບບໍ່ສຳເລັດ (move_uploaded_file failed)</div>';
                            }
                        } else {
                            echo '<div class="alert alert-danger text-center">ຮອງຮັບສະເພາະໄຟລ໌ JPG, PNG, GIF, WEBP ເທົ່ານັ້ນ</div>';
                        }
                    }

                    // 🔧 FIX: ໃຊ້ prepared statement ທຸກ query ແທນການ concat string ໂດຍກົງ (ປ້ອງກັນ SQL Injection)
                    // 🔧 FIX: WHERE stock.id = ? (ໃຊ້ Itemsid) ຄືເກົ່າ, ສ່ວນຕາຕະລາງອື່ນໆໃຊ້ WHERE Items = ? (ໃຊ້ ItemsOld)
                    $updates = [
                        ["UPDATE stock SET Items = ?, Item_name_Chinese = ?, Size = ?, Model = ?, Items_Part = ?, picture = ? WHERE id = ?", "sssssss", [$itemsNew, $Items_CN, $Items_Size, $Items_Model, $Items_Part, $pictureNew, $itemsIdPost]],
                        ["UPDATE stock_province SET Items = ?, picture = ? WHERE Items = ?", "sss", [$itemsNew, $pictureNew, $itemsOld]],
                        ["UPDATE stock_provinceinput SET Items = ?, picture = ? WHERE Items = ?", "sss", [$itemsNew, $pictureNew, $itemsOld]],
                        ["UPDATE stockinput SET Items = ?, picture = ? WHERE Items = ?", "sss", [$itemsNew, $pictureNew, $itemsOld]],
                        ["UPDATE request SET Items = ?, picture = ? WHERE Items = ?", "sss", [$itemsNew, $pictureNew, $itemsOld]],
                        ["UPDATE usestock SET Items = ? WHERE Items = ?", "ss", [$itemsNew, $itemsOld]],
                        ["UPDATE followequment SET Name_Come = ? WHERE Name_Come = ?", "ss", [$itemsNew, $itemsOld]],
                        ["UPDATE stocklose SET Name = ? WHERE Name = ?", "ss", [$itemsNew, $itemsOld]],
                    ];

                    // 🔧 FIX: ຖ້າ conn.php ເປີດ mysqli_report(MYSQLI_REPORT_STRICT) ໄວ້
                    // (ຄືກັບທີ່ເຮັດໃຫ້ເຫັນ "Uncaught mysqli_sql_exception" ມາກ່ອນໜ້ານີ້),
                    // prepare()/execute() ຈະ throw exception ທັນທີ ແທນທີ່ຈະ return false.
                    // ນັ້ນໝາຍວ່າ "if ($st !== false)" ຂ້າງເທິງບໍ່ເຄີຍຖືກໃຊ້ຈິງເລີຍ -
                    // ພຽງແຕ່ 1 ຕາຕະລາງມີບັນຫາ (ຄໍລໍາບໍ່ກົງ, ຊື່ຕາຕະລາງຜິດ, ...)
                    // ຈະເຮັດໃຫ້ script ຕາຍທັນທີແບບ Fatal error, ບໍ່ update ຫຍັງເລີຍ
                    // ແລະ ຜູ້ໃຊ້ບໍ່ເຫັນຂໍ້ຄວາມ success ຫຼື error ຫຍັງເລີຍ.
                    // ແກ້ໂດຍ wrap ແຕ່ລະ query ດ້ວຍ try/catch ເພື່ອບໍ່ໃຫ້ script ຕາຍທັງໝົດ
                    // ແລະ ສະແດງ error ຈິງໆອອກມາໃຫ້ຮູ້ວ່າຕາຕະລາງໃດ/ຄໍລໍາໃດເປັນບັນຫາ.
                    $updateErrors = [];
                    foreach ($updates as [$sqlText, $types, $vals]) {
                        try {
                            $st = $conn->prepare($sqlText);
                            $st->bind_param($types, ...$vals);
                            $st->execute();
                            $st->close();
                        } catch (mysqli_sql_exception $e) {
                            $updateErrors[] = $e->getMessage() . ' | SQL: ' . $sqlText;
                            error_log('EditItemstock.php update failed: ' . $e->getMessage() . ' | SQL: ' . $sqlText);
                        }
                    }

                    if (!empty($updateErrors)) {
                        // 🔧 ສະແດງ error ຈິງອອກມາ ເພື່ອໃຫ້ຮູ້ວ່າຕາຕະລາງ/ຄໍລໍາໃດຜິດ (ຈະໄດ້ໄປແກ້ໂຄງສ້າງຖານຂໍ້ມູນຄືເກົ່າ)
                        echo '<div class="alert alert-danger text-center">ອັບເດດບໍ່ສຳເລັດບາງສ່ວນ: ' . htmlspecialchars(implode(' || ', $updateErrors)) . '</div>';
                    } else {
                        echo '<script>alert("ແກ້ໄຂຂໍ້ມູນສຳເລັດ!"); window.location = "EditItemstock.php?id=' . urlencode($itemsIdPost) . '";</script>';
                    }
                }
                ?>
            <?php endif; ?>

            <script>
                function openCity(evt, cityName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }
                    document.getElementById(cityName).style.display = "block";
                    evt.currentTarget.className += " active";
                }
            </script>
        </div>
    </div>
    <script src="js/vendor/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
