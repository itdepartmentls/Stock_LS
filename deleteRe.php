<?php
@session_start();
if ( $_SESSION["user"]=="" or  $_SESSION["Namepro"]=="" or $_SESSION["iduser"]==""  )
  {
	 echo "<script>window.location = 'index.php';</script>";
	 exit;
  }
?>
<?php
require_once __DIR__ . '/includes/conn.php';
$id = $_REQUEST['id'];

$stmt = $conn->prepare("DELETE FROM request WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
?>
<script>
		  
		window.location = "DataRe.php";
		
		
		  
		 
		  </script>