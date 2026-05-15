<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';
require_once '../../config/totp.php';

$id_usuario = $_SESSION['usuario_id'];

if (isset($_POST['activar_totp'])) {
    $secret = generarSecretTOTP();
    $stmt_t = $conn->prepare("UPDATE usuario SET totp_secret=?, totp_activo=0 WHERE id_usuario=?");
    $stmt_t->bind_param("si", $secret, $id_usuario);
    $stmt_t->execute();
    header('Location: perfil.php?tab=seguridad&qr=1');
    exit;
}

if (isset($_POST['confirmar_totp'])) {
    $codigo = trim($_POST['totp_codigo']);
    $stmt_t = $conn->prepare("SELECT totp_secret FROM usuario WHERE id_usuario=?");
    $stmt_t->bind_param("i", $id_usuario);
    $stmt_t->execute();
    $secret = $stmt_t->get_result()->fetch_assoc()['totp_secret'];
    if ($secret && verificarCodigoTOTP($secret, $codigo)) {
        $stmt_t = $conn->prepare("UPDATE usuario SET totp_activo=1 WHERE id_usuario=?");
        $stmt_t->bind_param("i", $id_usuario);
        $stmt_t->execute();
        $_SESSION['success'] = '¡Google Authenticator activado correctamente!';
    } else {
        $_SESSION['error'] = 'Código incorrecto. Escanea el QR e intenta de nuevo.';
    }
    header('Location: perfil.php?tab=seguridad');
    exit;
}

if (isset($_POST['desactivar_totp'])) {
    $stmt_t = $conn->prepare("UPDATE usuario SET totp_secret=NULL, totp_activo=0 WHERE id_usuario=?");
    $stmt_t->bind_param("i", $id_usuario);
    $stmt_t->execute();
    $_SESSION['success'] = 'Google Authenticator desactivado.';
    header('Location: perfil.php?tab=seguridad');
    exit;
}

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

if (isset($_POST['cambiar_pass'])) {
    $actual  = $_POST['pass_actual'];
    $nueva   = $_POST['pass_nueva'];
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

$stmt = $conn->prepare("SELECT * FROM usuario WHERE id_usuario=?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

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

// Nivel
if ($total_compras >= 10) {
    $rank_label = 'Gold Gamer'; $rank_key = 'gold';
} elseif ($total_compras >= 5) {
    $rank_label = 'Silver Gamer'; $rank_key = 'silver';
} elseif ($total_compras >= 1) {
    $rank_label = 'Bronze Gamer'; $rank_key = 'bronze';
} else {
    $rank_label = 'Rookie'; $rank_key = 'rookie';
}
$rank_next = $total_compras >= 10 ? 10 : ($total_compras >= 5 ? 10 : ($total_compras >= 1 ? 5 : 1));
$rank_pct  = $total_compras >= 10 ? 100 : min(99, round(($total_compras / $rank_next) * 100));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #080808;
            --card:     #111111;
            --raised:   #181818;
            --border:   #252525;
            --green:    #d4a843;
            --green-dim:rgba(212,168,67,0.08);
            --green-mid:rgba(212,168,67,0.15);
            --purple:   #7c6af7;
            --gold:     #f5a623;
            --silver:   #94a3c8;
            --bronze:   #cd7f32;
            --red:      #ef4444;
            --text:     #f0f0f8;
            --muted:    #55556a;
            --sub:      #8888a8;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

        /* ── NAVBAR ── */
        .navbar {
            background: rgba(7,7,17,0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 200;
        }
        .nav-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--green);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .nav-brand span { color: var(--text); }
        .nav-actions { display: flex; align-items: center; gap: 4px; }
        .nav-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            color: var(--sub);
            text-decoration: none;
            font-size: 1rem;
            transition: all .18s;
            position: relative;
        }
        .nav-btn:hover { color: var(--text); background: rgba(255,255,255,.05); }
        .nav-badge {
            position: absolute;
            top: 4px; right: 4px;
            background: var(--green);
            color: #000;
            font-size: 0.5rem;
            font-weight: 800;
            width: 14px; height: 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-exit {
            background: rgba(239,68,68,.07);
            border: 1px solid rgba(239,68,68,.18);
            color: #f87171;
            border-radius: 10px;
            padding: 7px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-exit:hover { background: rgba(239,68,68,.14); color: #fca5a5; }

        /* ── ALERTS ── */
        .alert-ok, .alert-err {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.875rem;
            margin-bottom: 20px;
        }
        .alert-ok  { background: rgba(212,168,67,.06); border: 1px solid rgba(212,168,67,.2); color: var(--green); }
        .alert-err { background: rgba(239,68,68,.06);  border: 1px solid rgba(239,68,68,.2);  color: var(--red); }

        /* ── PROFILE HEADER ── */
        .profile-header {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .profile-header-top {
            padding: 28px 32px 24px;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            border-top: 3px solid var(--green);
        }
        .avatar {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(212,168,67,.12), rgba(212,168,67,.12));
            border: 1.5px solid rgba(212,168,67,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--green);
            flex-shrink: 0;
        }
        .profile-meta { flex: 1; min-width: 0; }
        .profile-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .profile-email { color: var(--sub); font-size: 0.82rem; margin-bottom: 8px; }
        .profile-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--green-dim);
            border: 1px solid rgba(212,168,67,.18);
            color: var(--green);
            border-radius: 6px;
            padding: 3px 10px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }
        .profile-since {
            text-align: right;
            flex-shrink: 0;
        }
        .profile-since-label { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
        .profile-since-date  { font-size: 0.95rem; font-weight: 700; color: var(--text); }

        /* stats strip inside header */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-top: 1px solid var(--border);
        }
        .stat-cell {
            padding: 16px 24px;
            border-right: 1px solid var(--border);
        }
        .stat-cell:last-child { border-right: none; }
        .stat-val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1;
            margin-bottom: 3px;
        }
        .stat-val.accent { color: var(--green); }
        .stat-lbl { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; }

        /* ── LAYOUT ── */
        .page-body { padding: 32px 0 60px; }

        /* ── TABS ── */
        .tab-row {
            display: flex;
            gap: 2px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 20px;
        }
        .tab-btn {
            flex: 1;
            padding: 9px 12px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            font-family: 'Inter', sans-serif;
            letter-spacing: .2px;
        }
        .tab-btn.active {
            background: var(--green-mid);
            color: var(--green);
            border: 1px solid rgba(212,168,67,.2);
        }
        .tab-btn:hover:not(.active) { color: var(--text); background: rgba(255,255,255,.03); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ── CARD ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }
        .card + .card { margin-top: 16px; }
        .card-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-title i { color: var(--green); }

        /* ── FORM ── */
        .f-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--sub);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 7px;
        }
        .f-input {
            width: 100%;
            background: var(--raised);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            transition: border-color .15s, box-shadow .15s;
            font-family: 'Inter', sans-serif;
        }
        .f-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(212,168,67,.07);
        }
        .f-input::placeholder { color: var(--muted); }
        .f-input:invalid:not(:placeholder-shown) { border-color: var(--red); }
        .f-input:valid:not(:placeholder-shown)   { border-color: rgba(212,168,67,.35); }
        .input-wrap { position: relative; }
        .input-wrap .f-input { padding-right: 42px; }
        .toggle-pass {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 0.95rem;
            padding: 0;
            transition: color .15s;
        }
        .toggle-pass:hover { color: var(--green); }

        .btn-primary {
            background: var(--green);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 10px 22px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all .18s;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-primary:hover { background: #c89a30; transform: translateY(-1px); }

        /* ── INFO ROWS ── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #0f0f1e;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row-label { font-size: 0.72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; }
        .info-row-val   { font-size: 0.85rem; font-weight: 600; }

        /* ── SECURITY: status bar ── */
        .totp-status {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #0f0f1e;
            margin-bottom: 16px;
        }
        .status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-dot.on  { background: var(--green); box-shadow: 0 0 6px var(--green); }
        .status-dot.off { background: var(--muted); }

        .totp-badge {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: .5px;
            padding: 4px 12px;
            border-radius: 6px;
        }
        .totp-badge.on  { background: rgba(212,168,67,.08); border: 1px solid rgba(212,168,67,.2); color: var(--green); }
        .totp-badge.off { background: rgba(85,85,106,.1);   border: 1px solid var(--border);      color: var(--muted); }

        /* QR setup */
        .qr-instructions {
            background: rgba(245,158,11,.06);
            border: 1px solid rgba(245,158,11,.18);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            font-size: 0.78rem;
            color: #c8a24a;
            line-height: 1.8;
        }
        .qr-instructions strong { color: var(--text); }
        .qr-wrapper {
            background: #fff;
            border-radius: 14px;
            padding: 14px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .code-input {
            background: var(--raised);
            border: 2px solid rgba(212,168,67,.2);
            color: var(--green);
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 12px;
            width: 220px;
            font-family: 'Space Grotesk', monospace;
            outline: none;
            transition: border-color .15s;
        }
        .code-input:focus { border-color: var(--green); }
        .btn-danger-sm {
            background: rgba(239,68,68,.07);
            border: 1px solid rgba(239,68,68,.2);
            color: #f87171;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-danger-sm:hover { background: rgba(239,68,68,.14); }
        .btn-ghost {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 0.75rem;
            cursor: pointer;
            transition: color .15s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-ghost:hover { color: var(--sub); }

        /* no-2fa promo */
        .totp-promo {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 4px 0 20px;
        }
        .totp-promo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--green-dim);
            border: 1px solid rgba(212,168,67,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green);
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .totp-promo-text { font-size: 0.82rem; color: var(--sub); line-height: 1.7; }
        .totp-promo-text strong { color: var(--text); }
        .store-links { display: flex; gap: 8px; margin: 14px 0 20px; flex-wrap: wrap; }
        .store-link {
            background: var(--raised);
            border: 1px solid var(--border);
            color: var(--sub);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 0.75rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all .18s;
        }
        .store-link:hover { border-color: rgba(212,168,67,.2); color: var(--text); }

        /* ── SIDEBAR ── */
        .quick-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .quick-item {
            background: var(--raised);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            text-decoration: none;
            color: var(--text);
            transition: all .18s;
        }
        .quick-item:hover {
            border-color: rgba(212,168,67,.22);
            background: rgba(212,168,67,.03);
            color: var(--text);
        }
        .quick-item-label { font-size: 0.8rem; font-weight: 600; margin-bottom: 2px; }
        .quick-item-sub   { font-size: 0.7rem; color: var(--muted); }
        .quick-item-icon  { font-size: 1.1rem; color: var(--green); margin-bottom: 8px; }

        /* ── RANK CARD ── */
        .rank-card { text-align: center; padding: 8px 0 4px; }

        .rank-hex {
            width: 60px;
            height: 60px;
            clip-path: polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 0.6rem;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #000;
        }
        .rank-hex.rookie  { background: linear-gradient(135deg,#3a3a5c,#5a5a7a); color: #aaa; }
        .rank-hex.bronze  { background: linear-gradient(135deg,#cd7f32,#e8a060); }
        .rank-hex.silver  { background: linear-gradient(135deg,#94a3c8,#d1d8f0); }
        .rank-hex.gold    { background: linear-gradient(135deg,#f5a623,#fcd34d); }

        .rank-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .rank-name.rookie { color: var(--sub); }
        .rank-name.bronze { color: #cd7f32; }
        .rank-name.silver { color: var(--silver); }
        .rank-name.gold   { color: var(--gold); }

        .rank-sub { font-size: 0.72rem; color: var(--muted); margin-bottom: 14px; }

        .rank-progress-wrap {
            height: 5px;
            background: var(--raised);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .rank-progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 1.2s ease;
        }
        .rank-progress-bar.bronze { background: linear-gradient(90deg, #cd7f32, #e8a060); }
        .rank-progress-bar.silver { background: linear-gradient(90deg, #94a3c8, #cbd5e1); }
        .rank-progress-bar.gold   { background: linear-gradient(90deg, #f5a623, #fcd34d); }
        .rank-progress-bar.rookie { background: linear-gradient(90deg, #3a3a5c, #5a5a7a); }

        .rank-progress-label { font-size: 0.68rem; color: var(--muted); }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .stats-strip { grid-template-columns: 1fr; }
            .stats-strip .stat-cell { border-right: none; border-bottom: 1px solid var(--border); }
            .stats-strip .stat-cell:last-child { border-bottom: none; }
            .quick-grid { grid-template-columns: 1fr; }
            .profile-header-top { padding: 20px; }
            .profile-since { display: none; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
            <div class="nav-actions">
                <a href="productos.php" class="nav-btn" title="Tienda"><i class="bi bi-grid-fill"></i></a>
                <a href="favoritos.php" class="nav-btn" title="Favoritos"><i class="bi bi-heart"></i></a>
                <a href="carrito.php" class="nav-btn" title="Carrito">
                    <i class="bi bi-cart3"></i>
                    <?php if ($cant_carrito > 0): ?>
                        <span class="nav-badge"><?= $cant_carrito ?></span>
                    <?php endif; ?>
                </a>
                <a href="historial.php" class="nav-btn" title="Pedidos"><i class="bi bi-bag-check"></i></a>
                <form action="../../controllers/auth_controller.php" method="POST" style="margin-left:8px;">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn-exit"><i class="bi bi-box-arrow-right"></i>Salir</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<div class="page-body">
    <div class="container">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- PROFILE HEADER -->
        <div class="profile-header">
            <div class="profile-header-top">
                <div class="avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
                <div class="profile-meta">
                    <div class="profile-name"><?= htmlspecialchars($user['nombre']) ?></div>
                    <div class="profile-email"><?= htmlspecialchars($user['correo']) ?></div>
                    <div class="profile-tag"><i class="bi bi-controller"></i> Cliente desde <?= date('Y', strtotime($user['fecha_registro'])) ?></div>
                </div>
                <div class="profile-since d-none d-md-block">
                    <div class="profile-since-label">Miembro desde</div>
                    <div class="profile-since-date"><?= date('d/m/Y', strtotime($user['fecha_registro'])) ?></div>
                </div>
            </div>
            <div class="stats-strip">
                <div class="stat-cell">
                    <div class="stat-val"><?= $total_compras ?></div>
                    <div class="stat-lbl">Compras</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-val accent" style="font-size:1.15rem;">Bs.<?= number_format($total_gastado, 0, '.', ',') ?></div>
                    <div class="stat-lbl">Total gastado</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-val"><?= $total_favs ?></div>
                    <div class="stat-lbl">Favoritos</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- MAIN -->
            <div class="col-lg-8">

                <!-- TABS -->
                <div class="tab-row">
                    <button class="tab-btn active" onclick="showTab('perfil',this)"><i class="bi bi-person me-1"></i>Mi Perfil</button>
                    <button class="tab-btn" onclick="showTab('seguridad',this)"><i class="bi bi-shield-lock me-1"></i>Seguridad</button>
                    <button class="tab-btn" onclick="showTab('info',this)"><i class="bi bi-info-circle me-1"></i>Información</button>
                </div>

                <!-- TAB: PERFIL -->
                <div id="tab-perfil" class="tab-pane active">
                    <div class="card">
                        <div class="card-title"><i class="bi bi-person-circle"></i> Datos personales</div>
                        <form method="POST">
                            <input type="hidden" name="actualizar_perfil" value="1">
                            <div class="mb-4">
                                <label class="f-label">Nombre completo</label>
                                <input type="text" name="nombre" class="f-input"
                                    value="<?= htmlspecialchars($user['nombre']) ?>"
                                    pattern="[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{2,50}"
                                    title="Solo letras y espacios, entre 2 y 50 caracteres" required>
                            </div>
                            <div class="mb-4">
                                <label class="f-label">Correo electrónico</label>
                                <input type="email" name="correo" class="f-input"
                                    value="<?= htmlspecialchars($user['correo']) ?>"
                                    required>
                            </div>
                            <button type="submit" class="btn-primary"><i class="bi bi-check-lg"></i>Guardar cambios</button>
                        </form>
                    </div>
                </div>

                <!-- TAB: SEGURIDAD -->
                <div id="tab-seguridad" class="tab-pane">
                    <!-- Cambiar contraseña -->
                    <div class="card">
                        <div class="card-title"><i class="bi bi-lock"></i> Cambiar contraseña</div>
                        <form method="POST">
                            <input type="hidden" name="cambiar_pass" value="1">
                            <div class="mb-3">
                                <label class="f-label">Contraseña actual</label>
                                <div class="input-wrap">
                                    <input type="password" name="pass_actual" id="pa" class="f-input" placeholder="••••••••" required>
                                    <button type="button" class="toggle-pass" onclick="tp('pa',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="f-label">Nueva contraseña</label>
                                <div class="input-wrap">
                                    <input type="password" name="pass_nueva" id="pn" class="f-input"
                                        placeholder="Mínimo 6 caracteres"
                                        pattern="(?=.*[0-9])(?=.*[a-zA-Z]).{6,}"
                                        title="Mínimo 6 caracteres, letras y números" required minlength="6">
                                    <button type="button" class="toggle-pass" onclick="tp('pn',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="f-label">Confirmar nueva contraseña</label>
                                <div class="input-wrap">
                                    <input type="password" name="pass_confirm" id="pc" class="f-input" placeholder="Repite la contraseña" required>
                                    <button type="button" class="toggle-pass" onclick="tp('pc',this)"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary"><i class="bi bi-lock-fill"></i>Actualizar contraseña</button>
                        </form>
                    </div>

                    <!-- 2FA -->
                    <div class="card">
                        <div class="card-title"><i class="bi bi-shield-check"></i> Autenticación de dos factores</div>

                        <?php
                        $sf = $conn->prepare("SELECT totp_secret, totp_activo FROM usuario WHERE id_usuario=?");
                        $sf->bind_param("i", $id_usuario);
                        $sf->execute();
                        $td = $sf->get_result()->fetch_assoc();
                        $totp_activo = $td['totp_activo'];
                        $totp_secret = $td['totp_secret'];
                        ?>

                        <?php if ($totp_activo): ?>
                            <div class="totp-status">
                                <div>
                                    <div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Estado</div>
                                    <div style="font-size:0.875rem;font-weight:600;color:var(--green);display:flex;align-items:center;">
                                        <span class="status-dot on"></span>Google Authenticator activo
                                    </div>
                                </div>
                                <span class="totp-badge on">Protegido</span>
                            </div>
                            <p style="color:var(--sub);font-size:0.8rem;line-height:1.7;margin-bottom:20px;">
                                Tu cuenta requiere el código de Google Authenticator al iniciar sesión.
                            </p>
                            <form method="POST">
                                <button type="submit" name="desactivar_totp" class="btn-danger-sm">
                                    <i class="bi bi-x-circle"></i>Desactivar 2FA
                                </button>
                            </form>

                        <?php elseif ($totp_secret): ?>
                            <div class="totp-status">
                                <div>
                                    <div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">Estado</div>
                                    <div style="font-size:0.875rem;font-weight:600;color:#f59e0b;display:flex;align-items:center;">
                                        <span class="status-dot" style="background:#f59e0b;margin-right:8px;"></span>Pendiente de confirmar
                                    </div>
                                </div>
                                <span class="totp-badge" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#f59e0b;">Escanea el QR</span>
                            </div>
                            <div class="qr-instructions">
                                1. Abre <strong>Google Authenticator</strong> en tu celular<br>
                                2. Toca <strong>"+"</strong> → <strong>"Escanear código QR"</strong><br>
                                3. Escanea el código de abajo y escribe el código de 6 dígitos
                            </div>
                            <div style="text-align:center;">
                                <?php try {
                                    $qr = getQRCodeUrl($user['correo'], $totp_secret);
                                    echo "<div class='qr-wrapper'><img src='$qr' style='width:176px;height:176px;display:block;'></div><br>";
                                } catch (Exception $e) {
                                    echo "<p style='color:var(--red);font-size:0.8rem;margin-bottom:16px;'>Error generando QR: ".htmlspecialchars($e->getMessage())."</p>";
                                } ?>
                                <form method="POST" style="margin-bottom:10px;">
                                    <div style="margin-bottom:14px;">
                                        <input type="text" name="totp_codigo" class="code-input"
                                            placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                                            inputmode="numeric" required autofocus>
                                    </div>
                                    <button type="submit" name="confirmar_totp" class="btn-primary">
                                        <i class="bi bi-check-lg"></i>Confirmar y activar
                                    </button>
                                </form>
                                <form method="POST">
                                    <button type="submit" name="activar_totp" class="btn-ghost">
                                        <i class="bi bi-arrow-repeat"></i>Generar nuevo QR
                                    </button>
                                </form>
                            </div>

                        <?php else: ?>
                            <div class="totp-promo">
                                <div class="totp-promo-icon"><i class="bi bi-shield-lock-fill"></i></div>
                                <div class="totp-promo-text">
                                    Protege tu cuenta con <strong>Google Authenticator</strong>. Genera códigos de un solo uso sin necesitar internet, añadiendo una capa extra de seguridad al inicio de sesión.
                                </div>
                            </div>
                            <div class="store-links">
                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="store-link">
                                    <i class="bi bi-google-play"></i> Google Play
                                </a>
                                <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="store-link">
                                    <i class="bi bi-apple"></i> App Store
                                </a>
                            </div>
                            <form method="POST">
                                <button type="submit" name="activar_totp" class="btn-primary">
                                    <i class="bi bi-qr-code-scan"></i>Configurar Google Authenticator
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TAB: INFO -->
                <div id="tab-info" class="tab-pane">
                    <div class="card">
                        <div class="card-title"><i class="bi bi-info-circle"></i> Información de la cuenta</div>
                        <div class="info-row">
                            <span class="info-row-label">ID de usuario</span>
                            <span class="info-row-val" style="color:var(--muted);font-family:monospace;">#<?= $user['id_usuario'] ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Nombre</span>
                            <span class="info-row-val"><?= htmlspecialchars($user['nombre']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Correo</span>
                            <span class="info-row-val"><?= htmlspecialchars($user['correo']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Rol</span>
                            <span class="info-row-val">
                                <span style="background:var(--green-dim);border:1px solid rgba(212,168,67,.18);color:var(--green);border-radius:6px;padding:2px 10px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;">Cliente</span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Miembro desde</span>
                            <span class="info-row-val"><?= date('d \d\e F \d\e Y', strtotime($user['fecha_registro'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-row-label">Autenticación 2FA</span>
                            <span class="info-row-val">
                                <?php if ($totp_activo ?? false): ?>
                                    <span style="color:var(--green);display:flex;align-items:center;gap:6px;"><span class="status-dot on"></span>Activa (Google Auth)</span>
                                <?php else: ?>
                                    <span style="color:var(--muted);display:flex;align-items:center;gap:6px;"><span class="status-dot off"></span>No configurada</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4">
                <!-- Accesos rápidos -->
                <div class="card mb-4">
                    <div class="card-title"><i class="bi bi-grid"></i> Accesos rápidos</div>
                    <div class="quick-grid">
                        <a href="productos.php" class="quick-item">
                            <div class="quick-item-icon"><i class="bi bi-grid-fill"></i></div>
                            <div class="quick-item-label">Tienda</div>
                            <div class="quick-item-sub">Ver productos</div>
                        </a>
                        <a href="carrito.php" class="quick-item">
                            <div class="quick-item-icon"><i class="bi bi-cart3"></i></div>
                            <div class="quick-item-label">Carrito</div>
                            <div class="quick-item-sub"><?= $cant_carrito ?> ítem<?= $cant_carrito !== 1 ? 's' : '' ?></div>
                        </a>
                        <a href="favoritos.php" class="quick-item">
                            <div class="quick-item-icon"><i class="bi bi-heart-fill"></i></div>
                            <div class="quick-item-label">Favoritos</div>
                            <div class="quick-item-sub"><?= $total_favs ?> guardado<?= $total_favs !== 1 ? 's' : '' ?></div>
                        </a>
                        <a href="historial.php" class="quick-item">
                            <div class="quick-item-icon"><i class="bi bi-bag-check-fill"></i></div>
                            <div class="quick-item-label">Pedidos</div>
                            <div class="quick-item-sub"><?= $total_compras ?> realizad<?= $total_compras !== 1 ? 'os' : 'o' ?></div>
                        </a>
                    </div>
                </div>

                <!-- Nivel -->
                <div class="card">
                    <div class="card-title"><i class="bi bi-bar-chart-fill" style="color:var(--gold);"></i> Nivel de jugador</div>
                    <div class="rank-card">
                        <div class="rank-hex <?= $rank_key ?>"><?= strtoupper(substr($rank_key,0,2)) ?></div>
                        <div class="rank-name <?= $rank_key ?>"><?= $rank_label ?></div>
                        <div class="rank-sub"><?= $total_compras ?> compra<?= $total_compras !== 1 ? 's' : '' ?> realizadas</div>
                        <?php if ($total_compras < 10): ?>
                        <div class="rank-progress-wrap">
                            <div class="rank-progress-bar <?= $rank_key ?>" style="width:<?= $rank_pct ?>%;"></div>
                        </div>
                        <div class="rank-progress-label">
                            <?php
                            $next_rank = $total_compras >= 5 ? 'Gold' : ($total_compras >= 1 ? 'Silver' : 'Bronze');
                            $needed    = $total_compras >= 5 ? (10 - $total_compras) : ($total_compras >= 1 ? (5 - $total_compras) : 1);
                            echo "$needed compra".($needed !== 1?'s':'')." más para $next_rank";
                            ?>
                        </div>
                        <?php else: ?>
                        <div class="rank-progress-label" style="color:var(--gold);">Nivel máximo alcanzado</div>
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
    function tp(id, btn) {
        const inp = document.getElementById(id);
        const ico = btn.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            ico.className = 'bi bi-eye-slash';
            btn.style.color = 'var(--green)';
        } else {
            inp.type = 'password';
            ico.className = 'bi bi-eye';
            btn.style.color = '';
        }
    }
    // Abrir tab según URL
    const tp_param = new URLSearchParams(window.location.search).get('tab');
    if (tp_param) {
        const btn = document.querySelector(`[onclick*="showTab('${tp_param}'"]`);
        if (btn) showTab(tp_param, btn);
    }
</script>
</body>
</html>
