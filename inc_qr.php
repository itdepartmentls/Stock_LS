<?php
// inc_qr.php
// Shared helper for the QR-link features: starts the session, connects to the
// database (same connection as the rest of the app), and makes sure the
// qr_links table exists. Requires conn.php which loads .env and connects.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/conn.php';

// Create the qr_links table on first use (idempotent).
$conn->query("
    CREATE TABLE IF NOT EXISTS qr_links (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        link       TEXT        NOT NULL,
        title      VARCHAR(255) DEFAULT '',
        created_by VARCHAR(255) DEFAULT '',
        created_at DATETIME    DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8
");
