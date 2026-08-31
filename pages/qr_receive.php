<?php
// qr_receive.php
// Page that generates a QR code from a link you type in, plus a "Section 1"
// login/password area that passes the credentials along with that link.
//
// Open as:  http://localhost/qr_receive.php?doc_no=GR-16072026-0009
// The link box is prefilled from ?doc_no=, but you can change it to any URL.

$defaultDocNo = 'GR-16072026-0009';
$docNo = isset($_GET['doc_no']) && trim($_GET['doc_no']) !== ''
    ? trim($_GET['doc_no'])
    : $defaultDocNo;

$defaultLink = 'http://localhost/receive_tacking.php?doc_no=' . urlencode($docNo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Receive Tracking QR</title>
<link rel="stylesheet" href="css/pages/qr_receive.css">
</head>
<body>
  <div class="card">
    <h1>Receive Tracking QR</h1>
    <div class="doc">Document: <strong><?php echo htmlspecialchars($docNo, ENT_QUOTES); ?></strong></div>

    <label for="linkInput">Link to encode</label>
    <input type="text" id="linkInput" value="<?php echo htmlspecialchars($defaultLink, ENT_QUOTES); ?>" placeholder="https://example.com" spellcheck="false">
    <button class="btn-gen" id="genBtn" type="button">Generate QR Code</button>

    <div class="qr-wrap">
      <div id="qrcode" title="Click to open the link"></div>
    </div>
    <div class="hint">Click the QR code to open the link</div>
    <div class="url" id="urlText"></div>
    <button class="btn-copy" id="copyBtn" type="button">Copy link</button>

    <hr>

    <h2 class="section-title"><span class="badge">Section 1</span> Login &amp; Pass</h2>
    <label for="login">Login</label>
    <input type="text" id="login" autocomplete="username" placeholder="Enter login">
    <label for="pass">Password</label>
    <input type="password" class="pass" id="pass" autocomplete="current-password" placeholder="Enter password">
    <button class="btn-pass" id="passBtn" type="button">Pass Section 1</button>
    <div class="msg" id="msg"></div>
  </div>

  <script src="qrcode.min.js"></script>
  <script>
    var defaultLink = <?php echo json_encode($defaultLink); ?>;
    var qr, currentLink = "";

    function setLink(url) {
      currentLink = (url || "").trim();
      document.getElementById("urlText").textContent = currentLink || "(empty)";
      if (!currentLink) { document.getElementById("msg").textContent = "Enter a link first."; return; }
      if (!qr) {
        qr = new QRCode(document.getElementById("qrcode"), {
          width: 220, height: 220,
          colorDark: "#0f172a", colorLight: "#ffffff",
          correctLevel: QRCode.CorrectLevel.M
        });
      }
      qr.clear();
      qr.makeCode(currentLink);
    }

    function withParams(url, params) {
      try {
        var u = new URL(url);
        for (var k in params) { if (params.hasOwnProperty(k)) u.searchParams.set(k, params[k]); }
        return u.toString();
      } catch (e) {
        var sep = url.indexOf("?") === -1 ? "?" : "&";
        var parts = [];
        for (var k in params) { if (params.hasOwnProperty(k)) parts.push(encodeURIComponent(k) + "=" + encodeURIComponent(params[k])); }
        return url + sep + parts.join("&");
      }
    }

    // Initial render
    setLink(defaultLink);

    // Regenerate when typing (live) or via button
    document.getElementById("linkInput").addEventListener("input", function () {
      setLink(this.value);
    });
    document.getElementById("genBtn").addEventListener("click", function () {
      setLink(document.getElementById("linkInput").value);
    });

    // Click QR -> open the current link
    document.getElementById("qrcode").addEventListener("click", function () {
      if (currentLink) window.open(currentLink, "_blank");
    });

    // Copy current link
    document.getElementById("copyBtn").addEventListener("click", function () {
      var msg = document.getElementById("msg");
      if (!currentLink) { msg.textContent = "Nothing to copy."; return; }
      navigator.clipboard.writeText(currentLink).then(function () {
        msg.textContent = "Link copied to clipboard.";
      }).catch(function () { msg.textContent = currentLink; });
    });

    // Pass Section 1: open the link with login + password appended
    document.getElementById("passBtn").addEventListener("click", function () {
      var msg = document.getElementById("msg");
      if (!currentLink) { msg.textContent = "Enter a link first."; return; }
      var login = document.getElementById("login").value.trim();
      var pass = document.getElementById("pass").value;
      if (!login || !pass) { msg.textContent = "Please enter both login and password."; return; }
      var target = withParams(currentLink, { login: login, pass: pass });
      window.open(target, "_blank");
      msg.textContent = "Passing Section 1 to the link…";
    });
  </script>
</body>
</html>
