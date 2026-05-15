<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'super_admin'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id_venta = intval($_POST['id_venta']);
    $estado = $_POST['estado_venta'];
    $stmt = $conn->prepare("UPDATE venta SET estado_venta=? WHERE id_venta=?");
    $stmt->bind_param("si", $estado, $id_venta);
    $stmt->execute();
    $_SESSION['success'] = "Estado de la venta #$id_venta actualizado a <strong>$estado</strong>.";
    header('Location: ventas.php');
    exit;
}

// Filtros
$buscar = trim($_GET['buscar'] ?? '');
$estado_f = $_GET['estado'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($buscar) {
    $where .= " AND (u.nombre LIKE ? OR u.correo LIKE ? OR v.id_venta LIKE ?)";
    $like = "%$buscar%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}
if ($estado_f) {
    $where .= " AND v.estado_venta=?";
    $params[] = $estado_f;
    $types .= "s";
}
if ($fecha_desde) {
    $where .= " AND DATE(v.fecha)>=?";
    $params[] = $fecha_desde;
    $types .= "s";
}
if ($fecha_hasta) {
    $where .= " AND DATE(v.fecha)<=?";
    $params[] = $fecha_hasta;
    $types .= "s";
}

$sql = "SELECT v.*, u.nombre as cliente, u.correo FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario $where ORDER BY v.fecha DESC";
$stmt = $conn->prepare($sql);
if ($params)
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$ventas = $stmt->get_result();
$total_filtradas = $ventas->num_rows;

$total_ventas = $conn->query("SELECT COUNT(*) as t FROM venta")->fetch_assoc()['t'];
$ventas_hoy = $conn->query("SELECT COUNT(*) as t FROM venta WHERE DATE(fecha)=CURDATE()")->fetch_assoc()['t'];
$ingresos_total = $conn->query("SELECT SUM(total) as s FROM venta")->fetch_assoc()['s'] ?? 0;
$pendientes = $conn->query("SELECT COUNT(*) as t FROM venta WHERE estado_venta='Pendiente'")->fetch_assoc()['t'];

function imgSrc($img, $prefix = '../../assets/')
{
    if (!$img)
        return null;
    return (strpos($img, 'http') === 0) ? $img : $prefix . $img;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - GamerZone Admin</title>
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
            background: #080808;
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: #111111;
            border-right: 1px solid #252525;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 24px 20px;
            border-bottom: 1px solid #252525;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #d4a843;
        }

        .brand-name span {
            color: #fff;
        }

        .brand-role {
            font-size: 0.75rem;
            color: #555;
            margin-top: 4px;
        }

        .brand-role.super {
            color: #a855f7;
        }

        .sidebar-nav {
            padding: 16px 0;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section {
            padding: 8px 20px 4px;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #444;
            font-weight: 600;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
        }

        .sidebar-link.active {
            color: #d4a843;
            background: rgba(212, 168, 67, 0.06);
            border-left-color: #d4a843;
        }

        .sidebar-link i {
            font-size: 1rem;
            width: 20px;
        }

        .badge-count {
            margin-left: auto;
            background: #f59e0b;
            color: #000;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 700;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #252525;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .user-av {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(212, 168, 67, 0.1);
            border: 1px solid rgba(212, 168, 67, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d4a843;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .user-name {
            font-size: 0.82rem;
            font-weight: 600;
        }

        .user-role {
            font-size: 0.7rem;
            color: #555;
        }

        .btn-logout {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-logout:hover {
            background: rgba(255, 68, 68, 0.2);
        }

        .main {
            margin-left: 260px;
            min-height: 100vh;
        }

        .topbar {
            background: #111111;
            border-bottom: 1px solid #252525;
            padding: 18px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .topbar-title span {
            color: #d4a843;
        }

        .breadcrumb-nav {
            font-size: 0.75rem;
            color: #444;
            margin-top: 2px;
        }

        .breadcrumb-nav a {
            color: #555;
            text-decoration: none;
        }

        .breadcrumb-nav a:hover {
            color: #d4a843;
        }

        .content {
            padding: 32px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .mini-stat {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mini-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .mini-stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: #d4a843;
        }

        .mini-stat-label {
            font-size: 0.78rem;
            color: #555;
        }

        /* FILTROS */
        .filter-card {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .filter-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-input {
            background: #181818;
            border: 1px solid #252525;
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.2s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #d4a843;
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.08);
        }

        .filter-input::placeholder {
            color: #333;
        }

        .filter-select {
            background: #181818;
            border: 1px solid #252525;
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            width: 100%;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #d4a843;
        }

        .filter-select option {
            background: #181818;
        }

        .btn-filter {
            background: #d4a843;
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-filter:hover {
            background: #c89a30;
        }

        .btn-clear-f {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #252525;
            color: #aaa;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-clear-f:hover {
            color: #fff;
            border-color: #333;
        }

        .results-info {
            font-size: 0.82rem;
            color: #555;
            margin-bottom: 16px;
        }

        .results-info strong {
            color: #d4a843;
        }

        /* TABLE */
        .table-card {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 16px;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid #252525;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .table-title {
            font-size: 0.95rem;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            padding: 12px 20px;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #444;
            font-weight: 600;
            border-bottom: 1px solid #252525;
            background: #0a0a14;
            text-align: left;
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 20px;
            border-bottom: 1px solid #0f0f1f;
            font-size: 0.875rem;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .status-Pendiente {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .status-Pagado {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .status-Entregado {
            background: rgba(212, 168, 67, 0.1);
            color: #d4a843;
            border: 1px solid rgba(212, 168, 67, 0.2);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.82rem;
            background: transparent;
        }

        .icon-btn-info {
            border-color: rgba(59, 130, 246, 0.3);
            color: #3b82f6;
        }

        .icon-btn-info:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .icon-btn-edit {
            border-color: rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        .icon-btn-edit:hover {
            background: rgba(245, 158, 11, 0.1);
        }

        .alert-ok {
            background: rgba(212, 168, 67, 0.06);
            border: 1px solid rgba(212, 168, 67, 0.2);
            color: #d4a843;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-content {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 16px;
            color: #fff;
        }

        .modal-header {
            border-bottom: 1px solid #252525;
            padding: 20px 24px;
        }

        .modal-footer {
            border-top: 1px solid #252525;
            padding: 16px 24px;
        }

        .modal-body {
            padding: 24px;
        }

        .form-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: #aaa;
            margin-bottom: 6px;
        }

        .form-select {
            background: #181818;
            border: 1px solid #252525;
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            width: 100%;
        }

        .form-select:focus {
            background: #181818;
            border-color: #d4a843;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.08);
            outline: none;
        }

        .form-select option {
            background: #181818;
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #252525;
            color: #aaa;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .btn-warning-c {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-warning-c:hover {
            background: rgba(245, 158, 11, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 60px 24px;
            color: #444;
        }

        .empty-state i {
            font-size: 3rem;
            display: block;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #252525;
            border-radius: 2px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-name">Gamer<span>Zone</span></div>
            <div class="brand-role <?= $es_super ? 'super' : '' ?>">
                <?= $es_super ? 'Super Administrador' : 'Administrador' ?></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Principal</div>
            <a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <div class="nav-section">Gestión</div>
            <a href="productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
            <a href="categorias.php" class="sidebar-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="sidebar-link active">
                <i class="bi bi-bag"></i> Ventas
                <?php if ($pendientes > 0): ?><span class="badge-count"><?= $pendientes ?></span><?php endif; ?>
            </a>
            <a href="usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
            <div class="nav-section">Sistema</div>
            <a href="../../index.php" class="sidebar-link"><i class="bi bi-globe"></i> Ver Tienda</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-av"><?= strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)) ?></div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                    <div class="user-role"><?= ucfirst(str_replace('_', ' ', $_SESSION['usuario_rol'])) ?></div>
                </div>
            </div>
            <form action="../../controllers/auth_controller.php" method="POST">
                <input type="hidden" name="action" value="logout">
                <button class="btn-logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</button>
            </form>
        </div>
    </div>

    <div class="main">
        <div class="topbar">
            <div>
                <div class="topbar-title"><i class="bi bi-bag" style="color:#d4a843"></i> <span>Ventas</span></div>
                <div class="breadcrumb-nav"><a href="dashboard.php">Dashboard</a> / Ventas</div>
            </div>
            <?php if ($pendientes > 0): ?>
                <div
                    style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;padding:8px 14px;font-size:0.8rem;color:#f59e0b;">
                    <i class="bi bi-clock me-1"></i><?= $pendientes ?> venta<?= $pendientes > 1 ? 's' : '' ?>
                    pendiente<?= $pendientes > 1 ? 's' : '' ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-ok"><i
                        class="bi bi-check-circle-fill"></i><?= $_SESSION['success'];
                        unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- STATS -->
            <div class="stats-row">
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background:rgba(212,168,67,0.1);"><i class="bi bi-bag-check" style="color:#d4a843;font-size:1.1rem;"></i></div>
                    <div>
                        <div class="mini-stat-num"><?= $total_ventas ?></div>
                        <div class="mini-stat-label">Ventas totales</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background:rgba(59,130,246,0.1);"><i class="bi bi-calendar-check" style="color:#3b82f6;font-size:1.1rem;"></i></div>
                    <div>
                        <div class="mini-stat-num" style="color:#3b82f6"><?= $ventas_hoy ?></div>
                        <div class="mini-stat-label">Ventas hoy</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background:rgba(245,158,11,0.1);"><i class="bi bi-clock" style="color:#f59e0b;font-size:1.1rem;"></i></div>
                    <div>
                        <div class="mini-stat-num" style="color:#f59e0b"><?= $pendientes ?></div>
                        <div class="mini-stat-label">Pendientes</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background:rgba(212,168,67,0.1);"><i class="bi bi-cash-coin" style="color:#d4a843;font-size:1.1rem;"></i></div>
                    <div>
                        <div class="mini-stat-num" style="font-size:1.2rem;">Bs.<?= number_format($ingresos_total, 0) ?>
                        </div>
                        <div class="mini-stat-label">Ingresos totales</div>
                    </div>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="filter-card">
                <div class="filter-title"><i class="bi bi-funnel" style="color:#d4a843"></i> Filtrar ventas</div>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Buscar cliente o
                            #pedido</label>
                        <div class="position-relative">
                            <i class="bi bi-search"
                                style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#444;font-size:0.85rem;"></i>
                            <input type="text" name="buscar" class="filter-input" style="padding-left:36px;"
                                placeholder="Nombre, correo o #ID..." value="<?= htmlspecialchars($buscar) ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Estado</label>
                        <select name="estado" class="filter-select">
                            <option value="">Todos</option>
                            <option value="Pendiente" <?= $estado_f == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Pagado" <?= $estado_f == 'Pagado' ? 'selected' : '' ?>>Pagado</option>
                            <option value="Entregado" <?= $estado_f == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input" value="<?= $fecha_desde ?>">
                    </div>
                    <div class="col-md-2">
                        <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input" value="<?= $fecha_hasta ?>">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Buscar</button>
                        <?php if ($buscar || $estado_f || $fecha_desde || $fecha_hasta): ?>
                            <a href="ventas.php" class="btn-clear-f"><i class="bi bi-x-lg"></i> Limpiar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="results-info">
                Mostrando <strong><?= $total_filtradas ?></strong> venta<?= $total_filtradas != 1 ? 's' : '' ?>
                <?php if ($buscar): ?> para "<strong
                        style="color:#fff"><?= htmlspecialchars($buscar) ?></strong>"<?php endif; ?>
                <?php if ($estado_f): ?> · Estado: <strong style="color:#fff"><?= $estado_f ?></strong><?php endif; ?>
                <?php if ($fecha_desde || $fecha_hasta): ?> · Período: <strong
                        style="color:#fff"><?= $fecha_desde ?: '...' ?> —
                        <?= $fecha_hasta ?: 'hoy' ?></strong><?php endif; ?>
            </div>

            <!-- TABLA -->
            <div class="table-card">
                <div class="table-header">
                    <div class="table-title"><i class="bi bi-bag" style="color:#d4a843;margin-right:8px;"></i>Registro
                        de Ventas</div>
                    <span style="font-size:0.78rem;color:#555;"><?= $total_filtradas ?>
                        resultado<?= $total_filtradas != 1 ? 's' : '' ?></span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Correo</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total_filtradas === 0): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-search"></i>
                                        <?= ($buscar || $estado_f || $fecha_desde || $fecha_hasta) ? 'No se encontraron ventas con esos filtros' : 'No hay ventas registradas' ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else:
                            while ($v = $ventas->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <span style="color:#555;font-size:0.78rem;">#<?= $v['id_venta'] ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;"><?= htmlspecialchars($v['cliente']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:0.78rem;color:#555;"><?= htmlspecialchars($v['correo']) ?></div>
                                    </td>
                                    <td style="color:#555;font-size:0.8rem;"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>
                                    </td>
                                    <td><strong style="color:#d4a843;">Bs. <?= number_format($v['total'], 2) ?></strong></td>
                                    <td><span class="status-<?= $v['estado_venta'] ?>"><?= $v['estado_venta'] ?></span></td>
                                    <td>
                                        <div style="display:flex;gap:6px;">
                                            <button class="icon-btn icon-btn-info" data-bs-toggle="modal"
                                                data-bs-target="#modalDetalle" data-id="<?= $v['id_venta'] ?>"
                                                data-cliente="<?= htmlspecialchars($v['cliente']) ?>"
                                                data-correo="<?= htmlspecialchars($v['correo']) ?>"
                                                data-fecha="<?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>"
                                                data-total="<?= number_format($v['total'], 2) ?>"
                                                data-estado="<?= $v['estado_venta'] ?>" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="icon-btn icon-btn-edit" data-bs-toggle="modal"
                                                data-bs-target="#modalEstado" data-id="<?= $v['id_venta'] ?>"
                                                data-estado="<?= $v['estado_venta'] ?>" title="Cambiar estado">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detalle -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:#3b82f6;font-weight:700;"><i
                            class="bi bi-receipt me-2"></i>Detalle del Pedido</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- INFO CLIENTE -->
                    <div style="background:#181818;border-radius:12px;padding:16px;margin-bottom:20px;">
                        <div
                            style="font-size:0.72rem;color:#444;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
                            Información del cliente</div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Pedido</div>
                                <div id="detId" style="font-weight:700;color:#d4a843;"></div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Cliente</div>
                                <div id="detCliente" style="font-weight:700;"></div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Correo</div>
                                <div id="detCorreo" style="font-size:0.82rem;color:#888;"></div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Fecha</div>
                                <div id="detFecha" style="font-size:0.82rem;"></div>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Estado</div>
                                <div id="detEstado"></div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:0.72rem;color:#555;margin-bottom:2px;">Total</div>
                                <div id="detTotal" style="font-size:1.3rem;font-weight:800;color:#d4a843;"></div>
                            </div>
                        </div>
                    </div>
                    <div
                        style="font-size:0.82rem;color:#555;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">
                        Productos del pedido</div>
                    <div id="detProductos">
                        <div style="text-align:center;color:#444;padding:24px;">Cargando...</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Estado -->
    <div class="modal fade" id="modalEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="color:#f59e0b;font-weight:700;"><i
                            class="bi bi-arrow-repeat me-2"></i>Cambiar Estado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="cambiar_estado" value="1">
                    <input type="hidden" name="id_venta" id="estadoId">
                    <div class="modal-body">
                        <label class="form-label">Nuevo estado de la venta</label>
                        <select name="estado_venta" id="estadoSelect" class="form-select" style="margin-top:8px;">
                            <option value="Pendiente">Pendiente</option>
                            <option value="Pagado">Pagado</option>
                            <option value="Entregado">Entregado</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-warning-c"><i
                                class="bi bi-check-lg me-1"></i>Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('modalEstado').addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('estadoId').value = btn.dataset.id;
            document.getElementById('estadoSelect').value = btn.dataset.estado;
        });

        document.getElementById('modalDetalle').addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('detId').textContent = '#' + btn.dataset.id;
            document.getElementById('detCliente').textContent = btn.dataset.cliente;
            document.getElementById('detCorreo').textContent = btn.dataset.correo;
            document.getElementById('detFecha').textContent = btn.dataset.fecha;
            document.getElementById('detTotal').textContent = 'Bs. ' + btn.dataset.total;

            const estados = { Pendiente: '#f59e0b', Pagado: '#3b82f6', Entregado: '#d4a843' };
            const col = estados[btn.dataset.estado] || '#888';
            document.getElementById('detEstado').innerHTML = `<span style="background:${col}18;color:${col};border:1px solid ${col}40;border-radius:6px;padding:3px 10px;font-size:0.78rem;font-weight:600;">${btn.dataset.estado}</span>`;

            document.getElementById('detProductos').innerHTML = '<div style="text-align:center;color:#444;padding:24px;"><i class="bi bi-hourglass-split me-2"></i>Cargando productos...</div>';
            fetch('get_detalle_venta.php?id=' + btn.dataset.id)
                .then(r => r.text())
                .then(html => document.getElementById('detProductos').innerHTML = html);
        });
    </script>
</body>

</html>