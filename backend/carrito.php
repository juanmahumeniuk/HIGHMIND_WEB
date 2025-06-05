<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':     addItem($usuario_id); break;
    case 'remove':  removeItem($usuario_id); break;
    case 'update':  updateItem($usuario_id); break;
    case 'clear':   clearCarrito($usuario_id); break;
    case 'get':     getCarrito($usuario_id); break;
    default:
        echo json_encode(['ok' => false, 'msg' => 'Acción no válida']);
        break;
}

// --------- FUNCIONES ---------

function addItem($usuario_id) {
    global $pdo;
    $producto_id = intval($_POST['id']);
    $cantidad = intval($_POST['qty'] ?? 1);
    if ($cantidad < 1) $cantidad = 1;

    // ¿Ya hay item? Actualiza, sino inserta
    $stmt = $pdo->prepare("SELECT cantidad FROM carrito_items WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
    if ($row = $stmt->fetch()) {
        $stmt2 = $pdo->prepare("UPDATE carrito_items SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?");
        $stmt2->execute([$cantidad, $usuario_id, $producto_id]);
    } else {
        $stmt2 = $pdo->prepare("INSERT INTO carrito_items (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
        $stmt2->execute([$usuario_id, $producto_id, $cantidad]);
    }
    echo json_encode(['ok' => true]);
}

function removeItem($usuario_id) {
    global $pdo;
    $producto_id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$usuario_id, $producto_id]);
    echo json_encode(['ok' => true]);
}

function updateItem($usuario_id) {
    global $pdo;
    $producto_id = intval($_POST['id']);
    $cantidad = intval($_POST['qty']);
    if ($cantidad < 1) $cantidad = 1;
    $stmt = $pdo->prepare("UPDATE carrito_items SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
    $stmt->execute([$cantidad, $usuario_id, $producto_id]);
    echo json_encode(['ok' => true]);
}

function clearCarrito($usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM carrito_items WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    echo json_encode(['ok' => true]);
}

function getCarrito($usuario_id) {
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT ci.producto_id, ci.cantidad, p.nombre, p.precio, p.img
         FROM carrito_items ci
         JOIN productos p ON p.id = ci.producto_id
         WHERE ci.usuario_id = ?"
    );
    $stmt->execute([$usuario_id]);
    $carrito = $stmt->fetchAll();
    $subtotal = 0;
    foreach ($carrito as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
    echo json_encode([
        'ok' => true,
        'carrito' => $carrito,
        'subtotal' => $subtotal,
        'total_items' => array_sum(array_column($carrito, 'cantidad'))
    ]);
}
?>
