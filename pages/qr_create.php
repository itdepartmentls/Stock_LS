<?php
// qr_create.php  —  STEP 1
// Enter a link, preview its QR code live, and SAVE it to the qr_links table.
// After saving you get a view link (qr_view.php?id=NNN) you can use in Step 3.

require_once __DIR__ . '/../includes/inc_qr.php';

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
<link rel="stylesheet" href="css/pages/qr_create.css">





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
