<?php
// Ambil URL koneksi dari variabel lingkungan (Render menyediakannya sebagai DATABASE_URL)
$url = getenv("DATABASE_URL");

if ($url === false) {
    die("Environment variable DATABASE_URL belum diset.");
}

$db = parse_url($url);

$host = $db["host"];
$port = $db["port"];
$user = $db["user"];
$pass = $db["pass"];
$name = ltrim($db["path"], "/");

// Buat koneksi PDO
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$name", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Cek & buat tabel jika belum ada
    $pdo->exec("CREATE TABLE IF NOT EXISTS notes (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        date DATE,
        description TEXT NOT NULL
    )");
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
