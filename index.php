<?php

// index.php
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

// ຮັບຄ່າ ?redirect=... ກ່ອນ (ຕ້ອງອ່ານກ່ອນເຊັກ session ເພື່ອບໍ່ໃຫ້ຄ່າຫາຍ)
$redirectParam = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';
if (!isSafeRedirect($redirectParam)) {
    $redirectParam = '';
}

// ຖ້າ login ຄ້າງໄວ້ຢູ່ແລ້ວ (session ຍັງບໍ່ໝົດອາຍຸ) ບໍ່ຕ້ອງໃຫ້ login ຊ້ຳ
// ໃຊ້ key ດຽວກັນກັບ check.php ຈິງໆ ('user'/'iduser')
// ແລະຖ້າມີ redirect (ມາຈາກສະແກນ QR) ໃຫ້ພາໄປໜ້ານັ້ນເລີຍ ແທນ dashboard
if (!empty($_SESSION['user']) && !empty($_SESSION['iduser'])) {
    if ($redirectParam !== '') {
        header("Location: " . $redirectParam);
    } elseif ($_SESSION['iduser'] == '404') {
        header("Location: center.php");
    } else {
        header("Location: Dasborad.php");
    }
    exit();
}

// ===== ຕັ້ງຄ່າຮູບພື້ນຫຼັງ =====
// ວາງໄຟລ໌ຮູບຂອງທ່ານໄວ້ໃນໂຟນເດີ image/ ແລ້ວປ່ຽນຊື່ຕໍ່ໄປນີ້ໃຫ້ກົງກັບຊື່ໄຟລ໌ຮູບຂອງທ່ານ
// ຖ້າຫາໄຟລ໌ບໍ່ພົບ ລະບົບຈະໃຊ້ Gradient ສີພື້ນຫຼັງແທນໂດຍອັດຕະໂນມັດ
$bgImagePath = "image/img_stock.png";
$hasBgImage  = file_exists(__DIR__ . "/" . $bgImagePath);
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລະບົບຈັດການສາງ LS</title>
    <link rel="shortcut icon" href="image/favicons.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/vendor/fontawesome/all.min.css">

     


    <link rel="stylesheet" href="css/pages/index.css">
    <style>
        /* dynamic background: depends on $hasBgImage (PHP) */
        body {
            <?php if ($hasBgImage): ?>background-image: url('<?php echo htmlspecialchars($bgImagePath); ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            <?php else: ?>background: linear-gradient(135deg, var(--bg-grad-1) 0%, var(--bg-grad-2) 50%, var(--bg-grad-3) 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            <?php endif; ?>
        }

        body::before {
            <?php if ($hasBgImage): ?>background: linear-gradient(135deg, rgba(6, 56, 31, 0.72), rgba(15, 122, 68, 0.55), rgba(15, 81, 50, 0.5));
            <?php else: ?>background: transparent;
            <?php endif; ?>
        }
    </style>
</head>

<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="login-card">
        <div class="logo-section">
            <img src="image/logoETL.jpg" alt="ETL">
        </div>

        <div class="login-header">
            <h3>ລະບົບຈັດການສາງ LS</h3>
            <p>ກະລຸນາເຂົ້າສູ່ລະບົບ</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ</span>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="check.php" method="post" autocomplete="off">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectParam, ENT_QUOTES, 'UTF-8'); ?>">

            <div class="form-group">
                <label for="user">Username</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fa-solid fa-user"></i></span>
                    <input id="user" name="user" type="text" class="form-control"
                        placeholder="ປ້ອນຊື່ຜູ້ໃຊ້" required>
                </div>
            </div>

            <div class="form-group">
                <label for="pass">Password</label>
                <div class="input-wrap">
                    <span class="icon"><i class="fa-solid fa-lock"></i></span>
                    <input id="pass" name="pass" type="password" class="form-control"
                        placeholder="ປ້ອນລະຫັດຜ່ານ" required>
                    <button type="button" class="toggle-password" id="togglePassword"
                        aria-label="ສະແດງ/ເຊື່ອງລະຫັດຜ່ານ">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">ເຂົ້າສູ່ລະບົບ</button>
                <button type="button" class="btn btn-light" onclick="window.location.href='logout.php'">ລ້າງຂໍ້ມູນການເຂົ້າສູ່ລະບົບ</button>
            </div>
        </form>

        <div class="footer">© 2026 IT Department LS</div>
        <div class="footer">version: 1.0.0</div>
    </div>

    <script>
        // ສະແດງ / ເຊື່ອງລະຫັດຜ່ານ
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('pass');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>