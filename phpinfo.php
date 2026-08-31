<?php
@session_start();
if (empty($_SESSION["user"])) {
	echo "<script>window.location = 'index.php';</script>";
	exit;
}
phpinfo();
?>