<?php
session_start();
require_once '../config/db.php';
require_once '../config/mailer.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ═══════════════════════════════
//  REGISTRO
// ═══════════════════════════════
if ($action === 'register') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmar'];

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
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El correo no tiene un formato válido.';
        header('Location: ../views/auth/register.php');
        exit;
    }

    $check = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo=?");
    $check->bind_param("s", $correo);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Este correo ya está registrado.';
        header('Location: ../views/auth/register.php');
        exit;
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $rol = 'cliente';
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, contrasena, rol) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $nombre, $correo, $hash, $rol);

    if ($stmt->execute()) {
        // Enviar correo de bienvenida si SMTP está configurado
        $resultado = enviarBienvenida($correo, $nombre);
        if ($resultado['success']) {
            $_SESSION['success'] = "¡Cuenta creada! Te enviamos un correo de bienvenida a <strong>$correo</strong>.";
        } else {
            $_SESSION['success'] = '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.';
        }
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
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo=?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['error'] = 'Correo o contraseña incorrectos.';
        header('Location: ../views/auth/login.php');
        exit;
    }

    $_SESSION['temp_user'] = $user;

    // Si tiene TOTP activo usar Google Authenticator
    if (!empty($user['totp_secret']) && $user['totp_activo']) {
        $_SESSION['usar_totp'] = true;
        $_SESSION['codigo_2fa'] = null;
        $_SESSION['correo_enviado'] = false;
        header('Location: ../views/auth/verify_2fa.php');
        exit;
    }

    // Usar código normal
    $codigo = strval(rand(100000, 999999));
    $_SESSION['codigo_2fa'] = $codigo;
    $_SESSION['usar_totp'] = false;

    $upd = $conn->prepare("UPDATE usuario SET codigo_2fa=?, estado_2fa=1 WHERE id_usuario=?");
    $upd->bind_param("si", $codigo, $user['id_usuario']);
    $upd->execute();

    $resultado = enviarCodigo2FA($user['correo'], $user['nombre'], $codigo);
    if ($resultado['success']) {
        $_SESSION['msg_2fa'] = "Código enviado a <strong>{$user['correo']}</strong>";
        $_SESSION['correo_enviado'] = true;
    } else {
        $_SESSION['correo_enviado'] = false;
    }

    header('Location: ../views/auth/verify_2fa.php');
    exit;
}

// ═══════════════════════════════
//  VERIFICAR 2FA
// ═══════════════════════════════
if ($action === 'verify_2fa') {
    $codigo_ingresado = trim($_POST['codigo']);
    $user = $_SESSION['temp_user'] ?? null;

    if (!$user) {
        header('Location: ../views/auth/login.php');
        exit;
    }

    $valido = false;

    if ($_SESSION['usar_totp'] ?? false) {
        // Verificar con Google Authenticator
        require_once '../config/totp.php';
        $valido = verificarCodigoTOTP($user['totp_secret'], $codigo_ingresado);
    } else {
        // Verificar código normal
        $valido = ($codigo_ingresado === ($_SESSION['codigo_2fa'] ?? ''));
    }

    if ($valido) {
        unset(
            $_SESSION['codigo_2fa'],
            $_SESSION['temp_user'],
            $_SESSION['msg_2fa'],
            $_SESSION['correo_enviado'],
            $_SESSION['usar_totp']
        );

        $_SESSION['usuario_id'] = $user['id_usuario'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_rol'] = $user['rol'];

        $upd = $conn->prepare("UPDATE usuario SET codigo_2fa=NULL, estado_2fa=0 WHERE id_usuario=?");
        $upd->bind_param("i", $user['id_usuario']);
        $upd->execute();

        if ($user['rol'] === 'super_admin' || $user['rol'] === 'admin') {
            header('Location: ../views/admin/dashboard.php');
        } else {
            header('Location: ../views/cliente/productos.php');
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