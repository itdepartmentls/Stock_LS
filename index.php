<?php

// index.php
session_start();

require_once __DIR__ . '/conn.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

     


    <style>
        :root {
            /* ໂຕນສີຂຽວເອກະລັກບໍລິສັດ (ຂຽວເຂັ້ມ) */
            --primary: #0f5132;
            --primary-hover: #0b3d26;
            --primary-light: #e6f4ec;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d8e6de;
            --bg-grad-1: #06381f;
            --bg-grad-2: #0f7a44;
            --bg-grad-3: #1fae6b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: "Kanit", "Phetsarath OT", sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--text);
            position: relative;
            overflow-x: hidden;

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

        /* ຊັ້ນເຄືອບສີເພື່ອໃຫ້ຂໍ້ຄວາມອ່ານງ່າຍເມື່ອໃຊ້ຮູບພື້ນຫຼັງ (ໂຕນຂຽວເຂັ້ມ) */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            <?php if ($hasBgImage): ?>background: linear-gradient(135deg, rgba(6, 56, 31, 0.72), rgba(15, 122, 68, 0.55), rgba(15, 81, 50, 0.5));
            <?php else: ?>background: transparent;
            <?php endif; ?>z-index: 0;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        /* ອົງປະກອບປະດັບ (blob) ໃຫ້ຄວາມຮູ້ສຶກທັນສະໄໝ */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.35;
            z-index: 0;
            pointer-events: none;
        }

        .blob-1 {
            width: 320px;
            height: 320px;
            background: #d1fae5;
            top: -80px;
            left: -80px;
        }

        .blob-2 {
            width: 280px;
            height: 280px;
            background: #065f46;
            bottom: -60px;
            right: -60px;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 22px;
            padding: 40px 36px;
            box-shadow: 0 25px 70px rgba(6, 56, 31, 0.35);
            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            margin-bottom: 18px;
        }

        .logo-section img {
            max-width: 220px;
            width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-header h3 {
            font-size: 24px;
            font-weight: 600;
            color: var(--text);
        }

        .login-header p {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 300;
            color: var(--muted);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            height: 48px;
            padding: 10px 14px 10px 42px;
            font-family: inherit;
            font-size: 15px;
            color: var(--text);
            background: rgba(249, 250, 251, 0.9);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(15, 81, 50, 0.14);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 15px;
            padding: 0;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .btn {
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        }

        .btn-success {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 8px 20px rgba(15, 81, 50, 0.35);
            margin-bottom: 12px;
        }

        .btn-success:hover {
            background: var(--primary-hover);
            box-shadow: 0 10px 24px rgba(15, 81, 50, 0.45);
        }

        .btn-success:active {
            transform: scale(0.98);
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.7);
            color: var(--text);
            border: 1.5px solid var(--border);
        }

        .btn-light:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 22px;
            font-size: 12px;
            font-weight: 300;
            color: var(--muted);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 32px 22px;
                border-radius: 18px;
            }
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