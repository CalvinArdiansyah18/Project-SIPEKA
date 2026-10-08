<?php

$host = "localhost";
$port = "5432";
$dbname = "sipeka";
$user = "postgres";
$password = "3333";

$conn = pg_connect(
    "host=$host port=$port dbname=$dbname user=$user password=$password"
);

if ($conn === false) {
    die("Koneksi database gagal.");
}