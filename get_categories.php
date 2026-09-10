<?php
require_once __DIR__ . '/conn.php';
@session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["user"]) || $_SESSION["user"] === "") {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

mysqli_set_charset($conn, "utf8");

$sql = "SELECT DISTINCT Category 
        FROM stock 
        WHERE Category IS NOT NULL AND TRIM(Category) <> ''
        ORDER BY Category ASC";

$result = $conn->query($sql);

$categories = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['Category'];
    }
}

echo json_encode(['categories' => $categories]);