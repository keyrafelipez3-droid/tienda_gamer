<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$total_productos = $conn->query("SELECT COUNT(*) as total FROM producto")->fetch_assoc()['total'];
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuario WHERE rol='cliente'")->fetch_assoc()['total'];
$total_ventas = $conn->query("SELECT COUNT(*) as total FROM venta")->fetch_assoc()['total'];
$total_categorias = $conn->query("SELECT COUNT(*) as total FROM categoria")->fetch_assoc()['total'];
$ingresos = $conn->query("SELECT SUM(total) as suma FROM venta")->fetch_assoc()['suma'] ?? 0;
$ventas_recientes = $conn->query("SELECT v.*, u.nombre FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario ORDER BY v.fecha DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GamerZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover, .nav-link.active { color: #00ff88 !important; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .stat-card { background: #111; border: 1px solid #222; border-radius: 16px; padding: 24px; transition: all 0.3s; }
        .stat-card:hover { border-color: #00ff88; transform: translateY(-3px); }
        .stat-card .numero { font-size: 2.5rem; font-weight: 900; color: #00ff88; }
        .stat-card .label { color: #888; font-size: 0.9rem; }
        .stat-card .icono { font-size: 2rem; margin-bottom: 10px; }
        .menu-card { background: #111; border: 1px solid #222; border-radius: 16px; padding: 25px; text-align: center; transition: all 0.3s; text-decoration: none; color: #fff; display: block; }
        .menu-card:hover { border-color: #00ff88; transform: translateY(-3px); color: #00ff88; }
        .menu-card .icon { font-size: 2.5rem; margin-bottom: 10px; }
        .table { color: #fff; }
        .table thead th { color: #00ff88; border-color: #222; background: #0d0d0d; }
        .table td { border-color: #1a1a1a; vertical-align: middle; }
        .card-dark { background: #111; border: 1px solid #222; border-radius: 16px; overflow: hidden; }
        .ingreso-box { background: linear-gradient(135deg, #0d1f0d, #0a0a0a); border: 1px solid #00ff88; border-radius: 16px; padding: 24px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link"><i class="bi bi-cart"></i> Ventas</a>
            <a href="usuarios.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <h3 class="fw-bold mb-4">Panel de <span style="color:#00ff88">Administración</span></h3>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icono">📦</div>
                <div class="numero"><?= $total_productos ?></div>
                <div class="label">Productos registrados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icono">👥</div>
                <div class="numero"><?= $total_usuarios ?></div>
                <div class="label">Clientes registrados</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icono">🛒</div>
                <div class="numero"><?= $total_ventas ?></div>
                <div class="label">Ventas realizadas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icono">🏷️</div>
                <div class="numero"><?= $total_categorias ?></div>
                <div class="label">Categorías</div>
            </div>
        </div>
    </div>

    <!-- Ingresos -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="ingreso-box">
                <p class="text-muted mb-1">💰 Ingresos totales</p>
                <h2 style="color:#00ff88; font-weight:900;">Bs. <?= number_format($ingresos, 2) ?></h2>
                <small class="text-muted">Suma de todas las ventas</small>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <h5 class="fw-bold mb-3">Acciones rápidas</h5>
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <a href="productos.php" class="menu-card">
                <div class="icon">📦</div>
                <p class="fw-bold mb-0">Productos</p>
                <small class="text-muted">Gestionar catálogo</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="categorias.php" class="menu-card">
                <div class="icon">🏷️</div>
                <p class="fw-bold mb-0">Categorías</p>
                <small class="text-muted">Gestionar categorías</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="ventas.php" class="menu-card">
                <div class="icon">🛒</div>
                <p class="fw-bold mb-0">Ventas</p>
                <small class="text-muted">Ver pedidos</small>
            </a>
        </div>
        <div class="col-md-3">
            <a href="usuarios.php" class="menu-card">
                <div class="icon">👥</div>
                <p class="fw-bold mb-0">Usuarios</p>
                <small class="text-muted">Ver clientes</small>
            </a>
        </div>
    </div>

    <!-- Ventas recientes -->
    <h5 class="fw-bold mb-3">Ventas recientes</h5>
    <div class="card-dark">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if($ventas_recientes->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No hay ventas aún</td></tr>
                <?php else: ?>
                <?php while($v = $ventas_recientes->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted">#<?= $v['id_venta'] ?></td>
                    <td><?= htmlspecialchars($v['nombre']) ?></td>
                    <td class="text-muted"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <td><strong class="text-success">Bs. <?= number_format($v['total'], 2) ?></strong></td>
                    <td>
                        <?php $colores = ['Pendiente'=>'warning','Pagado'=>'info','Entregado'=>'success']; ?>
                        <span class="badge bg-<?= $colores[$v['estado_venta']] ?>"><?= $v['estado_venta'] ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>