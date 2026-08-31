<?php
session_start();
//unset($_SESSSION['stat']); // clear session
session_destroy(); // ทำลาย session
header( "location: index.php" );
?>