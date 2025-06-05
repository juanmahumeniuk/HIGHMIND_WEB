<?php
// /backend/api_productos.php
require_once 'config.php';

header('Content-Type: application/json');

$stmt = $pdo->prepare("SELECT id, nombre, descripcion, precio, img, stock FROM productos WHERE activo = 1 ORDER BY id ASC");
$stmt->execute();
$productos = $stmt->fetchAll();

echo json_encode($productos);
?>
