<?php
// qr_scan.php
// QR-code login scanner.
//  - GET : shows a camera view that scans a QR code.
//  - POST: receives the scanned text, parses user+pass, verifies against the
//          sod_users table (the "separate user section"), and logs the user in
//          exactly like check.php does (sets the same session vars, same redirect).
//
// The scanned QR must contain user + pass, e.g.:
//    user=alice&pass=secret
//    http://localhost/qr_scan.php?user=alice&pass=secret
//    (any URL whose query string has user= and pass=)
//
// Optional: open as  http://localhost/qr_scan.php?return=somepage.php
// to land on that page after a successful scan (relative path, no open-redirect).

// ---------- AJAX login handling ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    // Fresh session, same as check.php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    session_unset();
    session_destroy();
    session_start();

    require_once __DIR__ . '/includes/conn.php';

    // Parse credentials out of the scanned string (URL or raw query string).
    $data = (string) ($_POST['data'] ?? '');
    $q = $data;
    $u = parse_url($data);
    if (isset($u['query']) && $u['query'] !== '') {
        $q = $u['query'];
    }
    parse_str($q, $out);
    $us   = isset($out['user']) ? trim($out['user']) : '';
    $pass = isset($out['pass']) ? $out['pass'] : '';

    if ($us === '' || $pass === '') {
        echo json_encode(['ok' => false, 'error' => 'QR missing user or password']);
        exit;
    }

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
        echo json_encode(['ok' => false, 'error' => 'Database error']);
        exit;
    }
    $stmt->bind_param('ss', $us, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['user']       = $row['name'];
        $_SESSION['iduser']     = $row['iduser'];
        $_SESSION['categories'] = $row['categories'];
        $_SESSION['Namepro']    = $row['Namepro'];
        $_SESSION['factory']    = $row['factory'];

        $redirect = 'Dasborard.php';
        $ret = trim($_POST['return'] ?? '');
        if ($ret !== '' && strpos($ret, '://') === false && strpos($ret, '//') !== 0) {
            $redirect = $ret;
        } elseif ($_SESSION['iduser'] == '404') {
            $redirect = 'center.php';
        }
        echo json_encode(['ok' => true, 'user' => $row['name'], 'redirect' => $redirect]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Invalid user or password']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR Login Scanner</title>
<style>
  :root { --bg:#0f172a; --card:#1e293b; --accent:#38bdf8; --ok:#22c55e; --err:#ef4444; --txt:#e2e8f0; --muted:#94a3b8; }
  * { box-sizing: border-box; }
  body { margin:0; font-family: system-ui, "Segoe UI", Roboto, Arial, sans-serif;
         background:var(--bg); color:var(--txt); min-height:100vh; display:flex;
         align-items:center; justify-content:center; padding:20px; }
  .card { background:var(--card); width:100%; max-width:480px; border-radius:16px;
          padding:20px; box-shadow:0 10px 30px rgba(0,0,0,.35); text-align:center; }
  h1 { font-size:1.15rem; margin:0 0 4px; }
  .sub { color:var(--muted); font-size:.82rem; margin-bottom:14px; }
  #video { width:100%; max-height:340px; background:#000; border-radius:12px; transform:scaleX(-1); }
  #canvas { display:none; }
  .status { margin:12px 0 6px; font-size:.9rem; min-height:1.3em; }
  .status.err { color:var(--err); }
  .status.ok { color:var(--ok); }
  .manual { margin-top:14px; border-top:1px solid #334155; padding-top:14px; }
  .manual input { width:100%; padding:10px 12px; border-radius:10px; border:1px solid #334155;
                  background:#0f172a; color:var(--txt); font-size:.9rem; margin-bottom:8px; }
  .manual button { width:100%; padding:11px; border:none; border-radius:10px; font-weight:600;
                   background:var(--accent); color:#06283d; cursor:pointer; }
  .hint { color:var(--muted); font-size:.72rem; margin-top:10px; }
</style>
</head>
<body>
  <div class="card">
    <h1>QR Login Scanner</h1>
    <div class="sub">Point the camera at a user's QR badge to log in</div>

    <video id="video" playsinline></video>
    <canvas id="canvas"></canvas>

    <div class="status" id="status">Starting camera…</div>

    <div class="manual">
      <div class="hint">No camera? Paste the scanned text below:</div>
      <input type="text" id="manualInput" placeholder="user=alice&pass=secret">
      <button id="manualBtn" type="button">Login</button>
    </div>
  </div>

  <script src="jsqr.js"></script>
  <script>
    var video = document.getElementById('video');
    var canvas = document.getElementById('canvas');
    var ctx = canvas.getContext('2d');
    var statusEl = document.getElementById('status');
    var scanning = false;
    var busy = false;

    // Optional ?return= target forwarded to the backend.
    var returnParam = new URLSearchParams(location.search).get('return') || '';

    function setStatus(text, cls) {
      statusEl.textContent = text;
      statusEl.className = 'status' + (cls ? ' ' + cls : '');
    }

    function sendLogin(data) {
      if (busy) return;
      busy = true;
      setStatus('Verifying…');
      var body = 'data=' + encodeURIComponent(data);
      if (returnParam) body += '&return=' + encodeURIComponent(returnParam);
      fetch('qr_scan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
      })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.ok) {
          setStatus('Logged in as ' + res.user + '. Redirecting…', 'ok');
          window.location.href = res.redirect;
        } else {
          setStatus(res.error || 'Login failed', 'err');
          busy = false;
          if (scanning) requestAnimationFrame(tick);
        }
      })
      .catch(function () {
        setStatus('Network error. Try again.', 'err');
        busy = false;
        if (scanning) requestAnimationFrame(tick);
      });
    }

    function tick() {
      if (!scanning) return;
      if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var image = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var code = jsQR(image.data, image.width, image.height);
        if (code && code.data) {
          scanning = false;
          sendLogin(code.data);
          return;
        }
      }
      if (scanning) requestAnimationFrame(tick);
    }

    function startCamera(constraints) {
      return navigator.mediaDevices.getUserMedia(constraints);
    }

    startCamera({ video: { facingMode: 'environment' } })
      .catch(function () {
        // Fall back to any available camera (e.g. a desktop webcam).
        return startCamera({ video: true });
      })
      .then(function (stream) {
        video.srcObject = stream;
        video.play();
        scanning = true;
        setStatus('Scanning… point at a QR badge');
        requestAnimationFrame(tick);
      })
      .catch(function (err) {
        setStatus('Camera unavailable: ' + err.name + '. Use the box below.', 'err');
      });

    document.getElementById('manualBtn').addEventListener('click', function () {
      var v = document.getElementById('manualInput').value.trim();
      if (!v) { setStatus('Enter the scanned text first.', 'err'); return; }
      sendLogin(v);
    });
  </script>
</body>
</html>
