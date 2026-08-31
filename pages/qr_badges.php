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

require_once __DIR__ . '/../includes/conn.php';

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
<link rel="stylesheet" href="css/pages/qr_badges.css">
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
