<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config/koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Method harus POST"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "Email dan password wajib diisi"
    ]);
    exit;
}

$query = "
    SELECT id_user, nama, email, password
    FROM user_account
    WHERE email = $1
    LIMIT 1
";

$result = pg_query_params($conn, $query, [$email]);

if ($result === false) {
    echo json_encode([
        "success" => false,
        "message" => "Query database gagal"
    ]);
    exit;
}

if (pg_num_rows($result) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah"
    ]);
    exit;
}

$user = pg_fetch_assoc($result);

if (!password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => false,
        "message" => "Email atau password salah"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login berhasil",
    "user" => [
        "id" => $user["id"],
        "nama" => $user["nama"],
        "email" => $user["email"]
    ]
]);