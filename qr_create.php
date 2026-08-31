<?php
// qr_create.php  —  STEP 1
// Enter a link, preview its QR code live, and SAVE it to the qr_links table.
// After saving you get a view link (qr_view.php?id=NNN) you can use in Step 3.

require_once __DIR__ . '/includes/inc_qr.php';

$message   = '';
$msgClass  = '';
$savedId   = null;
$savedLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $link  = trim($_POST['link'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $by    = $_SESSION['user'] ?? ($_SESSION['iduser'] ?? '');

    if ($link === '') {
        $message  = 'Please enter a link to save.';
        $msgClass = 'err';
    } else {
        $stmt = $conn->prepare("INSERT INTO qr_links (link, title, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $link, $title, $by);
        $stmt->execute();
        $savedId   = $conn->insert_id;
        $savedLink = $link;
        $message   = 'Saved as QR #' . $savedId . '.';
        $msgClass  = 'ok';
    }
}

// Recent saved links (helps with Step 3 lookup).
$recent = $conn->query("SELECT id, link, title, created_at FROM qr_links ORDER BY id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create QR Link</title>
<style>
  :root { --bg:#0f172a; --card:#1e293b; --accent:#38bdf8; --ok:#22c55e; --err:#ef4444; --txt:#e2e8f0; --muted:#94a3b8; }
  * { box-sizing: border-box; }
  body { margin:0; font-family: system-ui, "Segoe UI", Roboto, Arial, sans-serif;
         background:var(--bg); color:var(--txt); min-height:100vh; display:flex;
         align-items:center; justify-content:center; padding:24px; }
  .card { background:var(--card); width:100%; max-width:480px; border-radius:16px;
          padding:24px; box-shadow:0 10px 30px rgba(0,0,0,.35); }
  h1 { font-size:1.2rem; margin:0 0 4px; }
  .sub { color:var(--muted); font-size:.85rem; margin-bottom:16px; }
  label { display:block; font-size:.85rem; color:var(--muted); margin:0 0 6px; }
  input[type=text] { width:100%; padding:11px 12px; border-radius:10px; border:1px solid #334155;
          background:#0f172a; color:var(--txt); font-size:.95rem; margin-bottom:12px; }
  input:focus { outline:none; border-color:var(--accent); }
  button { width:100%; padding:12px; border:none; border-radius:10px; font-weight:600;
           background:var(--accent); color:#06283d; cursor:pointer; font-size:.95rem; }
  .qr-wrap { display:flex; justify-content:center; margin:10px 0; }
  #qrcode { background:#fff; padding:12px; border-radius:12px; }
  .msg { text-align:center; font-size:.88rem; margin:10px 0; min-height:1.1em; }
  .msg.ok { color:var(--ok); } .msg.err { color:var(--err); }
  .saved { background:#0b3b2e; border:1px solid #14532d; border-radius:10px;
           padding:12px; text-align:center; margin-bottom:14px; font-size:.88rem; }
  .saved a { color:var(--ok); font-weight:600; }
  .recent { margin-top:20px; border-top:1px solid #334155; padding-top:14px; }
  .recent h2 { font-size:.85rem; color:var(--muted); margin:0 0 8px; }
  .recent ul { list-style:none; padding:0; margin:0; font-size:.78rem; }
  .recent li { padding:6px 0; border-bottom:1px solid #1e293b; word-break:break-all; }
  .recent a { color:var(--accent); text-decoration:none; }
</style>

    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
            background-color: #f0f2f5;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }
        }

        table {
            font-size: 12px;
        }

        th {
            position: sticky;
            top: 0;
        }
    </style>




</head>
<body>
  <div class="card">
          <div class="flex justify-between items-center mb-6 no-print">

        </div>
    <h1>Create QR Link</h1>
	
    <div class="sub">Step 1 &mdash; enter a link, preview it, and save it</div>

    <?php if ($savedId): ?>
      <div class="saved">
        Saved! Open it later in Step 3:<br>
        <a href="qr_view.php?id=<?php echo $savedId; ?>">qr_view.php?id=<?php echo $savedId; ?></a>
      </div>
	  

	  
	  
    <?php endif; ?>

    <form method="post" action="qr_create.php">
      <label for="link">Link to encode</label>
      <input type="text" id="link" name="link" value="<?php echo htmlspecialchars($savedLink, ENT_QUOTES); ?>"
             placeholder="https://example.com/page" autocomplete="off" required>
      <label for="title">Title (optional)</label>
      <input type="text" id="title" name="title" placeholder="e.g. Goods Receipt GR-..." autocomplete="off">
      <div class="qr-wrap"><div id="qrcode"></div></div>
      <button type="submit">Save QR Link</button>
	  
    </form>

    <div class="msg <?php echo $msgClass; ?>"><?php echo htmlspecialchars($message, ENT_QUOTES); ?></div>

    <?php if ($recent && $recent->num_rows): ?>
    <div class="recent">
      <h2>Recently saved</h2>
      <ul>
        <?php while ($r = $recent->fetch_assoc()): ?>
          <li>
            <a href="qr_view.php?id=<?php echo (int)$r['id']; ?>">#<?php echo (int)$r['id']; ?></a>
            &middot; <?php echo htmlspecialchars($r['title'] ?: $r['link'], ENT_QUOTES); ?>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
    <?php endif; ?>
	
	<div class="qr-wrap">
                <a href="Dasborad.php" class="qr-wrap"><i class="qr-wrap"></i><label color='#ffffff'> ກັບຄືນໜ້າຫຼັກ</label></a>
    </div>
	
  </div>

  <script src="qrcode.min.js"></script>
  <script>
    var linkInput = document.getElementById('link');
    var qr = new QRCode(document.getElementById('qrcode'), {
      width: 200, height: 200,
      colorDark: '#0f172a', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M
    });
    function render() {
      var v = linkInput.value.trim();
      qr.clear();
      if (v) qr.makeCode(v);
    }
    linkInput.addEventListener('input', render);
    render();
  </script>
  	    
</body>
</html>
