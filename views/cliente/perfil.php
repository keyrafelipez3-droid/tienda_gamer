<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

$id_usuario = $_SESSION['usuario_id'];

// Actualizar perfil
if (isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);

    if (empty($nombre) || empty($correo)) {
        $_SESSION['error'] = 'Nombre y correo son obligatorios.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'El correo no es válido.';
    } else {
        $check = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo=? AND id_usuario!=?");
        $check->bind_param("si", $correo, $id_usuario);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $_SESSION['error'] = 'Este correo ya está en uso por otra cuenta.';
        } else {
            $stmt = $conn->prepare("UPDATE usuario SET nombre=?, correo=? WHERE id_usuario=?");
            $stmt->bind_param("ssi", $nombre, $correo, $id_usuario);
            $stmt->execute();
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['success'] = 'Perfil actualizado correctamente.';
        }
    }
    header('Location: perfil.php');
    exit;
}

// Cambiar contraseña
if (isset($_POST['cambiar_pass'])) {
    $actual = $_POST['pass_actual'];
    $nueva = $_POST['pass_nueva'];
    $confirm = $_POST['pass_confirm'];

    $stmt = $conn->prepare("SELECT contrasena FROM usuario WHERE id_usuario=?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_assoc()['contrasena'];

    if (!password_verify($actual, $hash)) {
        $_SESSION['error'] = 'La contraseña actual es incorrecta.';
    } elseif (strlen($nueva) < 6) {
        $_SESSION['error'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirm) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
    } else {
        $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuario SET contrasena=? WHERE id_usuario=?");
        $stmt->bind_param("si", $nuevo_hash, $id_usuario);
        $stmt->execute();
        $_SESSION['success'] = 'Contraseña actualizada correctamente.';
    }
    header('Location: perfil.php');
    exit;
}

// Datos del usuario
$stmt = $conn->prepare("SELECT * FROM usuario WHERE id_usuario=?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Estadísticas
$total_compras = $conn->prepare("SELECT COUNT(*) as t FROM venta WHERE id_usuario=?");
$total_compras->bind_param("i", $id_usuario);
$total_compras->execute();
$total_compras = $total_compras->get_result()->fetch_assoc()['t'];

$total_gastado = $conn->prepare("SELECT SUM(total) as s FROM venta WHERE id_usuario=?");
$total_gastado->bind_param("i", $id_usuario);
$total_gastado->execute();
$total_gastado = $total_gastado->get_result()->fetch_assoc()['s'] ?? 0;

$total_favs = $conn->prepare("SELECT COUNT(*) as t FROM favorito WHERE id_usuario=?");
$total_favs->bind_param("i", $id_usuario);
$total_favs->execute();
$total_favs = $total_favs->get_result()->fetch_assoc()['t'];

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #070711;
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #1a1a2e;
            border-radius: 2px;
        }

        .navbar {
            background: rgba(13, 13, 26, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #1a1a2e;
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: #00ff88;
            text-decoration: none;
        }

        .nav-brand span {
            color: #fff;
        }

        .nav-icon-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            position: relative;
        }

        .nav-icon-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #00ff88;
            color: #000;
            font-size: 0.6rem;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-logout-sm {
            background: rgba(255, 68, 68, 0.08);
            border: 1px solid rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout-sm:hover {
            background: rgba(255, 68, 68, 0.15);
        }

        .content {
            padding: 40px 0;
        }

        /* PERFIL HEADER */
        .profile-header {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.06), rgba(99, 102, 241, 0.04));
            border: 1px solid rgba(0, 255, 136, 0.12);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .avatar-big {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: rgba(0, 255, 136, 0.1);
            border: 2px solid rgba(0, 255, 136, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: #00ff88;
            flex-shrink: 0;
        }

        .profile-info h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .profile-info p {
            color: #555;
            font-size: 0.875rem;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 255, 136, 0.08);
            border: 1px solid rgba(0, 255, 136, 0.2);
            color: #00ff88;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }

        /* STATS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
        }

        .stat-card:hover {
            border-color: rgba(0, 255, 136, 0.2);
        }

        .stat-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: #00ff88;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.78rem;
            color: #555;
            margin-top: 6px;
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        /* TABS */
        .tab-nav {
            display: flex;
            gap: 4px;
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 24px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #555;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .tab-btn.active {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            border: 1px solid rgba(0, 255, 136, 0.2);
        }

        .tab-btn:hover:not(.active) {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* FORM CARD */
        .form-card {
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 16px;
            padding: 28px;
        }

        .form-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #1a1a2e;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .form-input {
            width: 100%;
            background: #111120;
            border: 1px solid #1a1a2e;
            color: #fff;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.875rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.08);
        }

        .form-input::placeholder {
            color: #333;
        }

        .input-wrap {
            position: relative;
        }

        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-pass:hover {
            color: #00ff88;
        }

        .btn-save {
            background: #00ff88;
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save:hover {
            background: #00cc6a;
            transform: translateY(-1px);
        }

        .alert-ok {
            background: rgba(0, 255, 136, 0.06);
            border: 1px solid rgba(0, 255, 136, 0.2);
            color: #00ff88;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }

        .alert-err {
            background: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }

        /* INFO ROWS */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #111;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.78rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* LINKS NAV */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .quick-link {
            background: #111120;
            border: 1px solid #1a1a2e;
            border-radius: 12px;
            padding: 16px;
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s;
        }

        .quick-link:hover {
            border-color: rgba(0, 255, 136, 0.3);
            color: #00ff88;
            background: rgba(0, 255, 136, 0.03);
        }

        .quick-link i {
            font-size: 1.2rem;
            color: #00ff88;
        }

        .quick-link .q-title {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .quick-link .q-sub {
            font-size: 0.72rem;
            color: #555;
        }

        @media(max-width:768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .quick-links {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
                <div class="d-flex align-items-center gap-2">
                    <a href="productos.php" class="nav-icon-btn"><i class="bi bi-grid"></i></a>
                    <a href="favoritos.php" class="nav-icon-btn"><i class="bi bi-heart"></i></a>
                    <a href="carrito.php" class="nav-icon-btn">
                        <i class="bi bi-cart3"></i>
                        <?php if ($cant_carrito > 0): ?>
                            <span class="nav-badge"><?= $cant_carrito ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="historial.php" class="nav-icon-btn"><i class="bi bi-bag-check"></i></a>
                    <form action="../../controllers/auth_controller.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button class="btn-logout-sm"><i class="bi bi-box-arrow-right me-1"></i>Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="content">
        <div class="container">

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-ok"><i
                        class="bi bi-check-circle-fill"></i><?= $_SESSION['success'];
                        unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-err"><i
                        class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- HEADER PERFIL -->
            <div class="profile-header">
                <div class="avatar-big"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['nombre']) ?></h2>
                    <p><?= htmlspecialchars($user['correo']) ?></p>
                    <div class="profile-badge">
                        <i class="bi bi-controller"></i>
                        Cliente GamerZone desde <?= date('Y', strtotime($user['fecha_registro'])) ?>
                    </div>
                </div>
                <div class="ms-auto d-none d-md-block" style="text-align:right;">
                    <div style="font-size:0.72rem;color:#555;margin-bottom:4px;">Miembro desde</div>
                    <div style="font-weight:700;"><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></div>
                </div>
            </div>

            <!-- STATS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-num"><?= $total_compras ?></div>
                    <div class="stat-label">Compras realizadas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-num" style="font-size:1.3rem;">Bs.<?= number_format($total_gastado, 0) ?></div>
                    <div class="stat-label">Total invertido</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">❤️</div>
                    <div class="stat-num"><?= $total_favs ?></div>
                    <div class="stat-label">Favoritos guardados</div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- TABS -->
                    <div class="tab-nav">
                        <button class="tab-btn active" onclick="showTab('perfil', this)"><i
                                class="bi bi-person me-1"></i>Mi Perfil</button>
                        <button class="tab-btn" onclick="showTab('seguridad', this)"><i
                                class="bi bi-shield-lock me-1"></i>Seguridad</button>
                        <button class="tab-btn" onclick="showTab('info', this)"><i
                                class="bi bi-info-circle me-1"></i>Información</button>
                    </div>

                    <!-- TAB: PERFIL -->
                    <div id="tab-perfil" class="tab-pane active">
                        <div class="form-card">
                            <div class="form-card-title"><i class="bi bi-person-circle me-2"
                                    style="color:#00ff88"></i>Datos personales</div>
                            <form method="POST">
                                <input type="hidden" name="actualizar_perfil" value="1">
                                <div class="mb-4">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" name="nombre" class="form-input"
                                        value="<?= htmlspecialchars($user['nombre']) ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Correo electrónico</label>
                                    <input type="email" name="correo" class="form-input"
                                        value="<?= htmlspecialchars($user['correo']) ?>" required>
                                </div>
                                <button type="submit" class="btn-save"><i class="bi bi-check-lg"></i>Guardar
                                    cambios</button>
                            </form>
                        </div>
                    </div>

                    <!-- TAB: SEGURIDAD -->
                    <div id="tab-seguridad" class="tab-pane">
                        <div class="form-card">
                            <div class="form-card-title"><i class="bi bi-shield-lock me-2"
                                    style="color:#00ff88"></i>Cambiar contraseña</div>
                            <form method="POST">
                                <input type="hidden" name="cambiar_pass" value="1">
                                <div class="mb-3">
                                    <label class="form-label">Contraseña actual</label>
                                    <div class="input-wrap">
                                        <input type="password" name="pass_actual" id="passActual" class="form-input"
                                            placeholder="Tu contraseña actual" required>
                                        <button type="button" class="toggle-pass"
                                            onclick="togglePass('passActual',this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nueva contraseña</label>
                                    <div class="input-wrap">
                                        <input type="password" name="pass_nueva" id="passNueva" class="form-input"
                                            placeholder="Mínimo 6 caracteres" required minlength="6">
                                        <button type="button" class="toggle-pass"
                                            onclick="togglePass('passNueva',this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Confirmar nueva contraseña</label>
                                    <div class="input-wrap">
                                        <input type="password" name="pass_confirm" id="passConfirm" class="form-input"
                                            placeholder="Repite la nueva contraseña" required>
                                        <button type="button" class="toggle-pass"
                                            onclick="togglePass('passConfirm',this)"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                                <button type="submit" class="btn-save"><i class="bi bi-lock"></i>Cambiar
                                    contraseña</button>
                            </form>
                        </div>
                        <div class="form-card mt-4">
                            <div class="form-card-title"><i class="bi bi-phone me-2"
                                    style="color:#00ff88"></i>Autenticación 2FA</div>
                            <div class="info-row">
                                <div>
                                    <div class="info-label">Estado 2FA</div>
                                    <div class="info-value" style="color:#00ff88;margin-top:4px;"><i
                                            class="bi bi-check-circle-fill me-1"></i>Activo en tu cuenta</div>
                                </div>
                                <span
                                    style="background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:8px;padding:4px 12px;font-size:0.78rem;font-weight:600;">Protegido</span>
                            </div>
                            <p style="color:#555;font-size:0.82rem;margin-top:12px;line-height:1.6;">Tu cuenta está
                                protegida con verificación en dos pasos. Cada vez que inicies sesión recibirás un código
                                de acceso único.</p>
                        </div>
                    </div>

                    <!-- TAB: INFO -->
                    <div id="tab-info" class="tab-pane">
                        <div class="form-card">
                            <div class="form-card-title"><i class="bi bi-info-circle me-2"
                                    style="color:#00ff88"></i>Información de la cuenta</div>
                            <div class="info-row">
                                <span class="info-label">ID de usuario</span>
                                <span class="info-value" style="color:#555;">#<?= $user['id_usuario'] ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Nombre</span>
                                <span class="info-value"><?= htmlspecialchars($user['nombre']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Correo</span>
                                <span class="info-value"><?= htmlspecialchars($user['correo']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Rol</span>
                                <span class="info-value"><span
                                        style="background:rgba(0,255,136,0.08);color:#00ff88;border-radius:6px;padding:2px 10px;font-size:0.78rem;">Cliente</span></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Fecha de registro</span>
                                <span
                                    class="info-value"><?= date('d \d\e F, Y', strtotime($user['fecha_registro'])) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Autenticación 2FA</span>
                                <span class="info-value" style="color:#00ff88;"><i
                                        class="bi bi-shield-check me-1"></i>Activa</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <div class="col-lg-4">
                    <div class="form-card mb-4">
                        <div class="form-card-title"><i class="bi bi-grid me-2" style="color:#00ff88"></i>Accesos
                            rápidos</div>
                        <div class="quick-links">
                            <a href="productos.php" class="quick-link">
                                <i class="bi bi-grid"></i>
                                <div>
                                    <div class="q-title">Tienda</div>
                                    <div class="q-sub">Ver productos</div>
                                </div>
                            </a>
                            <a href="carrito.php" class="quick-link">
                                <i class="bi bi-cart3"></i>
                                <div>
                                    <div class="q-title">Carrito</div>
                                    <div class="q-sub"><?= $cant_carrito ?> items</div>
                                </div>
                            </a>
                            <a href="favoritos.php" class="quick-link">
                                <i class="bi bi-heart"></i>
                                <div>
                                    <div class="q-title">Favoritos</div>
                                    <div class="q-sub"><?= $total_favs ?> guardados</div>
                                </div>
                            </a>
                            <a href="historial.php" class="quick-link">
                                <i class="bi bi-bag-check"></i>
                                <div>
                                    <div class="q-title">Pedidos</div>
                                    <div class="q-sub"><?= $total_compras ?> realizados</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="form-card">
                        <div class="form-card-title"><i class="bi bi-trophy me-2" style="color:#f59e0b"></i>Nivel de
                            cliente</div>
                        <div style="text-align:center;padding:16px 0;">
                            <div style="font-size:3rem;margin-bottom:12px;">
                                <?= $total_compras >= 10 ? '🏆' : ($total_compras >= 5 ? '🥈' : ($total_compras >= 1 ? '🥉' : '🎮')) ?>
                            </div>
                            <div style="font-size:1.1rem;font-weight:800;color:#f59e0b;margin-bottom:4px;">
                                <?= $total_compras >= 10 ? 'Gold Gamer' : ($total_compras >= 5 ? 'Silver Gamer' : ($total_compras >= 1 ? 'Bronze Gamer' : 'New Gamer')) ?>
                            </div>
                            <div style="font-size:0.78rem;color:#555;"><?= $total_compras ?>
                                compra<?= $total_compras != 1 ? 's' : '' ?> realizadas</div>
                            <?php if ($total_compras < 10): ?>
                                <div
                                    style="margin-top:12px;background:#111120;border-radius:8px;height:6px;overflow:hidden;">
                                    <div
                                        style="height:100%;background:linear-gradient(90deg,#f59e0b,#ef4444);border-radius:8px;width:<?= min(100, ($total_compras / 10) * 100) ?>%;transition:width 1s;">
                                    </div>
                                </div>
                                <div style="font-size:0.72rem;color:#444;margin-top:6px;"><?= max(0, 10 - $total_compras) ?>
                                    compras para Gold</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(name, btn) {
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            btn.classList.add('active');
        }
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
                btn.style.color = '#00ff88';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
                btn.style.color = '#444';
            }
        }
    </script>
</body>

</html>