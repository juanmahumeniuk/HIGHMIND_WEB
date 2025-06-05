<?php
// /backend/config.php
$host = 'localhost'; 
$db   = 'u632054512_highmind';
$user = 'u632054512_jotamanuel';
$pass = 'AsusGTX1650';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    die('Error al conectar con la base de datos');
}
?>
