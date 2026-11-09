<?php
$DB_HOST = 'localhost';
$DB_PORT = '5432';
$DB_NAME = 'db_sipekabaru';
$DB_USER = 'postgres';
$DB_PASS = '3333';

$pdo = new PDO(
    "pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME",
    $DB_USER,
    $DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);