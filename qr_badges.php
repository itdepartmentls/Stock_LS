<?php
// qr_badges.php
// Generates a printable QR badge for every user in the sod_users table.
// Each badge QR encodes "user=USERNAME&pass=PASSWORD" so it can be scanned by
// qr_scan.php to log that user in.
//
// NOTE: the password is stored in plaintext in sod_users and is embedded in the
// QR image. Anyone who photographs/prints the badge gets the password. This is
// the behavior you chose (QR = username + password). If you later want a safer
// "badge only" model, switch the QR to encode just the username.

require_once __DIR__ . '/includes/conn.php';

$sql = "SELECT
            sod_users.id,
            sod_users.user,
            sod_users.pass,
            sod_users.name,
            sod_users.factory,
            sod_categories.name AS cat_name
        FROM sod_users
        LEFT JOIN sod_categories ON sod_users.categories = sod_categories.id
        ORDER BY sod_users.name ASC";

$res = $conn->query($sql);
$users = [];
while ($row = $res->fetch_assoc()) {
    $users[] = [
        'id'       => $row['id'],
        'user'     => $row['user'],
        'name'     => $row['name'],
        'factory'  => $row['factory'],
        'cat_name' => $row['cat_name'],
        // The exact string the scanner will parse (url-encoded values).
        'qr'       => 'user=' . urlencode($row['user']) . '&pass=' . urlencode($row['pass']),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User QR Badges</title>
<style>
  body { font-family: system-ui, "Segoe UI", Arial, sans-serif; background:#f1f5f9;
         color:#0f172a; margin:0; padding:20px; }
  h1 { font-size:1.2rem; }
  .bar { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
  .bar a, .bar button { text-decoration:none; background:#0ea5e9; color:#fff; border:none;
        padding:9px 14px; border-radius:8px; font-size:.85rem; cursor:pointer; }
  .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:16px; }
  .badge { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px;
           text-align:center; box-shadow:0 2px 8px rgba(0,0,0,.05); }
  .badge .name { font-weight:700; font-size:1rem; }
  .badge .meta { color:#475569; font-size:.78rem; margin:2px 0 10px; word-break:break-all; }
  .badge .qr { display:inline-block; padding:8px; background:#fff; border:1px solid #e2e8f0; border-radius:8px; }
  .warn { background:#fef3c7; color:#92400e; padding:10px 14px; border-radius:8px;
          font-size:.8rem; margin-bottom:16px; max-width:680px; }
  @media print {
    body { background:#fff; padding:0; }
    .bar, .warn { display:none; }
    .grid { gap:10px; }
    .badge { box-shadow:none; border:1px solid #cbd5e1; page-break-inside:avoid; }
  }
</style>
</head>
<body>
  <h1>User QR Badges</h1>
  <div class="warn">
    Security note: each badge QR contains the user's plaintext password
    (<code>user=…&amp;pass=…</code>). Treat printed badges like passwords. Scan them with
    <code>qr_scan.php</code> to log in.
  </div>
  <div class="bar">
    <button onclick="window.print()">Print badges</button>
    <a href="qr_scan.php">Open scanner</a>
  </div>

  <div class="grid" id="grid"></div>

  <script src="qrcode.min.js"></script>
  <script>
    var users = <?php echo json_encode($users); ?>;
    var grid = document.getElementById('grid');
    users.forEach(function (u, i) {
      var card = document.createElement('div');
      card.className = 'badge';
      var qrId = 'qr-' + i;
      card.innerHTML =
        '<div class="name">' + escapeHtml(u.name) + '</div>' +
        '<div class="meta">@' + escapeHtml(u.user) +
          (u.factory ? ' &middot; ' + escapeHtml(u.factory) : '') + '</div>' +
        '<div class="qr" id="' + qrId + '"></div>';
      grid.appendChild(card);
      new QRCode(document.getElementById(qrId), {
        text: u.qr, width: 160, height: 160,
        colorDark: '#0f172a', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    });

    function escapeHtml(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }
  </script>
</body>
</html>
