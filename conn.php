<?php
date_default_timezone_set("Asia/Bangkok");

require_once __DIR__ . '/inc_env.php';

env_load(__DIR__ . '/.env');

$dbHost = env('DB_HOST', 'localhost');
$dbPort = (int) env('DB_PORT', 3307);
$dbUser = env('DB_USER', 'root');
$dbPass = env('DB_PASS', '');
$dbName = env('DB_NAME', 'stock_ls');

/*
 * ສຳຄັນ: ຕ້ອງຕັ້ງອັນນີ້ກ່ອນເປີດການເຊື່ອມຕໍ່ (new mysqli).
 * ຖ້າບໍ່ຕັ້ງ, mysqli ຈະບໍ່ throw exception ເມື່ອ query/execute() ຜິດພາດ,
 * ເຮັດໃຫ້ try/catch (mysqli_sql_exception) ໃນໜ້າອື່ນໆ (ເຊັ່ນ Uploadstock.php)
 * ບໍ່ເຄີຍຖືກເອີ້ນເລີຍ ແລະ script ຈະຂຶ້ນວ່າ "ສຳເລັດ" ທັງທີ່ບໍ່ໄດ້ບັນທຶກຫຍັງລົງ DB ຈິງ.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");
