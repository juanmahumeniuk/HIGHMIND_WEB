<?php
// /backend/usuarios.php
session_start();
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register': register(); break;
    case 'login':    login(); break;
    case 'logout':   logout(); break;
    case 'check':    check(); break;
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}

// -------- FUNCIONES --------

function register() {
    global $pdo;
    $email = strtolower(trim($_POST['email'] ?? ''));
    $nombre = trim($_POST['nombre'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || strlen($nombre) < 2) {
        echo json_encode(['ok' => false, 'msg' => 'Datos inválidos']);
        return;
    }
    // ¿Ya existe?
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'Email ya registrado']);
        return;
    }
    $passhash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (email, nombre, password) VALUES (?, ?, ?)");
    $stmt->execute([$email, $nombre, $passhash]);
    echo json_encode(['ok' => true, 'msg' => 'Usuario registrado']);
}

function login() {
    global $pdo;
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email'] = $email;
        echo json_encode(['ok' => true, 'nombre' => $user['nombre'], 'email' => $email]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Email o contraseña incorrectos']);
    }
}

function logout() {
    session_destroy();
    echo json_encode(['ok' => true, 'msg' => 'Sesión cerrada']);
}

function check() {
    if (isset($_SESSION['usuario_id'])) {
        echo json_encode([
            'ok' => true,
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email']
        ]);
    } else {
        echo json_encode(['ok' => false]);
    }
}
?>
