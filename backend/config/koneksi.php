<?php
// Koneksi database. Kredensial dibaca dari .env (lihat env.php).
// Jangan tulis host/user/password langsung di file ini.

require_once __DIR__ . '/env.php';

$DB_HOST = env_atau('DB_HOST');
$DB_PORT = env_atau('DB_PORT');
$DB_NAME = env_atau('DB_NAME');
$DB_USER = env_atau('DB_USER');
$DB_PASS = env_atau('DB_PASS');

$pdo = new PDO(
    "pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME",
    $DB_USER,
    $DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
