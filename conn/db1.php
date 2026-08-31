<?php
$host = '10.10.0.30';
$username = 'stockbrrow';
$password = 'x74zfb:6PxQNqV,D';
$database = 'stock';
$port = '3306';

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $username, $password, $database, $port);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตั้งค่า charset เป็น utf8
$conn->set_charset("utf8");
?>