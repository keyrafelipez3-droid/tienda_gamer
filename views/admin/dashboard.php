<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; font-size: 1.5rem; }
        .navbar-brand span { color: #fff; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .stat-card { background: #111; border: 1px solid #222; border-radius: 16px; padding: 30px; text-align: center; transition: all 0.3s; }
        .stat-card:hover { border-color: #00ff88; }
        .stat-card h2 { color: #00ff88; font-size: 2.5rem; font-weight: 900; }
        .menu-card { background: #111; border: 1px solid #222; border-radius: 16px; padding: 25px; text-align: center; transition: all 0.3s; cursor: pointer; text-decoration: none; color: #fff; display: block; }
        .menu-card:hover { border-color: #00ff88; transform: translateY(-3px); color: #00ff88; }
        .menu-card .icon { font-size: 2.5rem; margin-bottom: 10px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-2 ms-auto">
            <span class="text-muted my-auto">Hola, <strong class="text-white"><?= $_SESSION['usuario_nombre'] ?></strong></span>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-gamer btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="fw-bold mb-4">Panel de <span style="color:#00ff88">Administración</span></h3>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div style="font-size:2rem;">📦</div>
                <h2>0</h2>
                <p class="text-muted mb-0">Productos</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div style="font-size:2rem;">👥</div>
                <h2>0</h2>
                <p class="text-muted mb-0">Usuarios</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div style="font-size:2rem;">🛒</div>
                <h2>0</h2>
                <p class="text-muted mb-0">Ventas</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div style="font-size:2rem;">🏷️</div>
                <h2>0</h2>
                <p class="text-muted mb-0">Categorías</p>
            </div>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Acciones rápidas</h5>
    <div class="row g-4">
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
</div>
</body>
</html>