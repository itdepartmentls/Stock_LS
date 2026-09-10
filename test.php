<?php

$host = "127.0.0.1";
$user = "root";
$pass = "99399543";
$db   = "stock_ls";
$port = 3307;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("ERROR : " . $conn->connect_error);
}

echo "Connected OK";