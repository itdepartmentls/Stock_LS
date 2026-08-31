<?php

// check.php
session_start();

// ລົບ Session ເກົ່າ
session_unset();
session_destroy();
session_start();

require_once __DIR__ . '/includes/conn.php';

// =========================================================
// ປ້ອງກັນ Open Redirect: ຍອມຮັບສະເພາະ path ພາຍໃນເວັບ
// (ຂຶ້ນຕົ້ນດ້ວຍ "/" ດ່ຽວ, ບໍ່ແມ່ນ "//" ຫຼືມີ "://" ຢູ່ໃນຄ່າ)
// =========================================================
function isSafeRedirect($url)
{
    if ($url === '' || $url === null) {
        return false;
    }
    if (strpos($url, "\\") !== false) {
        return false;
    }
    if (strpos($url, '://') !== false) {
        return false;
    }
    if (!preg_match('#^/(?!/)#', $url)) {
        return false;
    }
    return true;
}

// ຮັບຄ່າຈາກ Form
$us   = trim($_POST['user'] ?? '');
$pass = trim($_POST['pass'] ?? '');
$redirectParam = trim($_POST['redirect'] ?? '');
if (!isSafeRedirect($redirectParam)) {
    $redirectParam = '';
}
$redirectSuffix = $redirectParam !== '' ? '&redirect=' . rawurlencode($redirectParam) : '';

// ກວດວ່າມີຂໍ້ມູນສົ່ງມາບໍ່
if ($us == '' || $pass == '') {
    echo "<script>
            alert('Please enter Username and Password');
            window.location='index.php" . ($redirectParam !== '' ? '?redirect=' . rawurlencode($redirectParam) : '') . "';
          </script>";
    exit;
}

// SQL
$sql = "SELECT
            sod_users.name AS name,
            sod_users.id AS iduser,
            sod_users.categories,
            sod_users.factory,
            sod_categories.name AS Namepro
        FROM sod_users
        LEFT JOIN sod_categories
            ON sod_users.categories = sod_categories.id
        WHERE sod_users.user = ?
        AND sod_users.pass = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare Failed : " . $conn->error);
}

$stmt->bind_param("ss", $us, $pass);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $row = $result->fetch_assoc();

    $_SESSION["user"]       = $row["name"];
    $_SESSION["iduser"]     = $row["iduser"];
    $_SESSION["categories"] = $row["categories"];
    $_SESSION["Namepro"]    = $row["Namepro"];
    $_SESSION["factory"]    = $row["factory"];

    // ຖ້າມີໜ້າທີ່ຢາກກັບຄືນ (ຕົວຢ່າງ: ສະແກນ QR ແລ້ວ session ໝົດອາຍຸ)
    // ໃຫ້ redirect ໄປໜ້ານັ້ນເລີຍ ແທນທີ່ຈະໄປ dashboard ຄືເກົ່າ
    if ($redirectParam !== '') {
        header("Location: " . $redirectParam);
        exit;
    }

    // Redirect (ພຶດຕິກຳເດີມ ເມື່ອບໍ່ມີ redirect ຖືກລະບຸ)
    if ($_SESSION["iduser"] == '404') {
        header("Location: center.php");
    } else {
        header("Location: Stockdashboard.php");
    }
    exit;
}

// Login ບໍ່ສຳເລັດ
echo "<script>
        alert('Username or Password Wrong');
        window.location='index.php?error=1" . $redirectSuffix . "';
      </script>";
exit;
?>