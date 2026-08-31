<?php
// qr_view.php  —  STEPS 2 & 3
// Step 3: enter a saved QR link or ID (created in Step 1) to load its record.
// Step 2: display that saved link's QR code, PLUS a separate "login QR" built
//          from the CURRENTLY LOGGED-IN user's own credentials (user + pass from
//          sod_users), so scanning it with qr_scan.php logs them in as themselves.
//
// Requires the user to be logged in (uses the session set by check.php).

require_once __DIR__ . '/../includes/inc_qr.php';

// Must be logged in.
if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// ---- lookup helpers ----
function fetchById($conn, $id) {
    $stmt = $conn->prepare("SELECT id, link, title, created_at FROM qr_links WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows ? $res->fetch_assoc() : null;
}
function loadByInput($conn, $input) {
    $input = trim($input);
    if ($input === '') return null;
    if (ctype_digit($input)) return fetchById($conn, (int)$input);
    if (stripos($input, 'qr_view.php') !== false) {
        $q = parse_url($input, PHP_URL_QUERY);
        parse_str($q, $o);
        if (isset($o['id']) && ctype_digit($o['id'])) return fetchById($conn, (int)$o['id']);
    }
    $stmt = $conn->prepare("SELECT id, link, title, created_at FROM qr_links WHERE link = ? LIMIT 1");
    $stmt->bind_param('s', $input);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows ? $res->fetch_assoc() : null;
}

$record    = null;
$lookupMsg = '';

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $record = fetchById($conn, (int)$_GET['id']);
} elseif (isset($_GET['link'])) {
    $record = loadByInput($conn, $_GET['link']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['code'])) {
    $record = loadByInput($conn, $_POST['code']);
    if (!$record) $lookupMsg = 'No saved QR found for that input.';
}

// Build the login QR text from the logged-in user's own credentials.
$loginQR = '';
if (isset($_SESSION['iduser'])) {
    $stmt = $conn->prepare("SELECT user, pass FROM sod_users WHERE id = ?");
    $stmt->bind_param('s', $_SESSION['iduser']);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($row = $r->fetch_assoc()) {
        $loginQR = 'user=' . urlencode($row['user']) . '&pass=' . urlencode($row['pass']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Display QR Link</title>
<style>
  :root { --bg:#0f172a; --card:#1e293b; --accent:#38bdf8; --ok:#22c55e; --err:#ef4444; --txt:#e2e8f0; --muted:#94a3b8; }
  * { box-sizing: border-box; }
  body { margin:0; font-family: system-ui, "Segoe UI", Roboto, Arial, sans-serif;
         background:var(--bg); color:var(--txt); min-height:100vh; display:flex;
         align-items:center; justify-content:center; padding:24px; }
  .card { background:var(--card); width:100%; max-width:520px; border-radius:16px;
          padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.35); }
  h1 { font-size:1.2rem; margin:0 0 4px; }
  .sub { color:var(--muted); font-size:.85rem; margin-bottom:16px; }
  label { display:block; font-size:.85rem; color:var(--muted); margin:0 0 6px; }
  input[type=text] { width:100%; padding:11px 12px; border-radius:10px; border:1px solid #334155;
          background:#0f172a; color:var(--txt); font-size:.95rem; margin-bottom:10px; }
  .row { display:flex; gap:8px; }
  .row input { margin-bottom:0; }
  button { padding:11px 16px; border:none; border-radius:10px; font-weight:600;
           background:var(--accent); color:#06283d; cursor:pointer; font-size:.9rem; white-space:nowrap; }
  .msg { color:var(--err); font-size:.85rem; margin:8px 0; min-height:1.1em; }
  .codes { display:flex; gap:18px; flex-wrap:wrap; justify-content:center; margin-top:8px; }
  .code-box { background:#fff; border-radius:12px; padding:14px; text-align:center; color:#0f172a; }
  .code-box .cap { font-size:.78rem; font-weight:600; margin-top:8px; color:#334155; }
  .code-box .qr { background:#fff; padding:6px; display:inline-block; }
  .linktext { text-align:center; font-size:.78rem; color:var(--accent); word-break:break-all; margin:14px 0 6px; }
  .hint { text-align:center; color:var(--muted); font-size:.72rem; margin-top:18px; }
  .who { text-align:center; color:var(--muted); font-size:.8rem; margin-bottom:14px; }
  a { color:var(--accent); }
</style>
</head>
<body>
  <div class="card">
    <h1>Display QR Link</h1>
    <div class="sub">Steps 2 &amp; 3 &mdash; enter a saved QR link/ID, then display it with a login QR</div>
    <div class="who">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user'], ENT_QUOTES); ?></strong></div>

    <form method="post" action="qr_view.php">
      <label for="code">Enter a saved QR link or ID (from Step 1)</label>
      <div class="row">
        <input type="text" id="code" name="code" placeholder="qr_view.php?id=5  or  the original link" autocomplete="off">
        <button type="submit">Load</button>
      </div>
    </form>
    <div class="msg"><?php echo htmlspecialchars($lookupMsg, ENT_QUOTES); ?></div>

    <?php if ($record): ?>
      <hr style="border:none; border-top:1px solid #334155; margin:18px 0;">
      <div class="linktext"><?php echo htmlspecialchars($record['link'], ENT_QUOTES); ?></div>
      <div class="codes">
        <div class="code-box">
          <div class="qr" id="linkQr"></div>
          <div class="cap">Saved link QR</div>
        </div>
        <div class="code-box">
          <div class="qr" id="loginQr"></div>
          <div class="cap">Your login QR</div>
        </div>
      </div>
      <div class="hint">
        "Saved link QR" opens the link. "Your login QR" logs you in when scanned by qr_scan.php.
      </div>
    <?php else: ?>
      <div class="hint">Enter a saved QR link or ID above to display it.</div>
    <?php endif; ?>
  </div>

  <script src="qrcode.min.js"></script>
  <script>
    <?php if ($record): ?>
    new QRCode(document.getElementById('linkQr'), {
      text: <?php echo json_encode($record['link']); ?>,
      width: 170, height: 170,
      colorDark: '#0f172a', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M
    });
    <?php endif; ?>

    <?php if ($loginQR !== ''): ?>
    new QRCode(document.getElementById('loginQr'), {
      text: <?php echo json_encode($loginQR); ?>,
      width: 170, height: 170,
      colorDark: '#0f172a', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M
    });
    <?php endif; ?>
  </script>
</body>
</html>
