<?php
session_start();
require_once '../config/db.php';

$action = $_POST['action'] ?? '';
// ═══════════════════════════════
//  REGISTRO
// ═══════════════════════════════
if ($action === 'register') {
    $nombre    = trim($_POST['nombre']);
    $correo    = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $confirmar  = $_POST['confirmar'];

    // Validaciones
    if (empty($nombre) || empty($correo) || empty($contrasena)) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
        header('Location: ../views/auth/register.php');
        exit;
    }
    if ($contrasena !== $confirmar) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
        header('Location: ../views/auth/register.php');
        exit;
    }
    if (strlen($contrasena) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
        header('Location: ../views/auth/register.php');
        exit;
    }
    // Verificar correo duplicado
    $check = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Este correo ya está registrado.';
        header('Location: ../views/auth/register.php');
        exit;
    }

    // Encriptar contraseña
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, contrasena, rol) VALUES (?, ?, ?, 'cliente')");
    $stmt->bind_param("sss", $nombre, $correo, $hash);

    if ($stmt->execute()) {
        $_SESSION['success'] = '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.';
        header('Location: ../views/auth/login.php');
    } else {
        $_SESSION['error'] = 'Error al registrar. Intenta nuevamente.';
        header('Location: ../views/auth/register.php');
    }
    exit;
}

// ═══════════════════════════════
//  LOGIN
// ═══════════════════════════════
if ($action === 'login') {
    $correo    = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['error'] = 'Correo o contraseña incorrectos.';
        header('Location: ../views/auth/login.php');
        exit;
    }
    // Generar código 2FA
    $codigo = strval(rand(100000, 999999));
    $_SESSION['codigo_2fa'] = $codigo;
    $_SESSION['temp_user']  = $user;
    // Guardar código en BD
    $upd = $conn->prepare("UPDATE usuario SET codigo_2fa = ?, estado_2fa = 1 WHERE id_usuario = ?");
    $upd->bind_param("si", $codigo, $user['id_usuario']);
    $upd->execute();

    header('Location: ../views/auth/verify_2fa.php');
    exit;
}
// ═══════════════════════════════
//  VERIFICAR 2FA
// ═══════════════════════════════
if ($action === 'verify_2fa') {
    $codigo_ingresado = trim($_POST['codigo']);
    $codigo_correcto  = $_SESSION['codigo_2fa'] ?? '';
    $user             = $_SESSION['temp_user'] ?? null;

    if (!$user) {
        header('Location: ../views/auth/login.php');
        exit;
    }

    if ($codigo_ingresado === $codigo_correcto) {
        // Limpiar temporales
        unset($_SESSION['codigo_2fa']);
        unset($_SESSION['temp_user']);

        // Iniciar sesión real
        $_SESSION['usuario_id']  = $user['id_usuario'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];

        // Limpiar 2FA en BD
        $upd = $conn->prepare("UPDATE usuario SET codigo_2fa = NULL, estado_2fa = 0 WHERE id_usuario = ?");
        $upd->bind_param("i", $user['id_usuario']);
        $upd->execute();

        // Redirigir según rol
        if ($user['rol'] === 'admin') {
            header('Location: ../views/admin/dashboard.php');
        } else {
            header('Location: ../views/cliente/inicio.php');
        }
    } else {
        $_SESSION['error'] = 'Código incorrecto. Intenta de nuevo.';
        header('Location: ../views/auth/verify_2fa.php');
    }
    exit;
}
// ═══════════════════════════════
//  LOGOUT
// ═══════════════════════════════
if ($action === 'logout') {
    session_destroy();
    header('Location: ../../index.php');
    exit;
}
?>