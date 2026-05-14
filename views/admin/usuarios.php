<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$usuarios = $conn->query("SELECT * FROM usuario ORDER BY fecha_registro DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Admin GamerZone</title>
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
        .card-dark { background: #111; border: 1px solid #222; border-radius: 16px; overflow: hidden; }
        .table { color: #fff; }
        .table thead th { color: #00ff88; border-color: #222; background: #0d0d0d; }
        .table td { border-color: #1a1a1a; vertical-align: middle; }
        .table tbody tr:hover { background: #161616; }
        .avatar { width: 40px; height: 40px; background: #0d1f0d; border: 2px solid #00ff88; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #00ff88; font-size: 1rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link"><i class="bi bi-cart"></i> Ventas</a>
            <a href="usuarios.php" class="nav-link active"><i class="bi bi-people"></i> Usuarios</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <h3 class="fw-bold mb-4" style="color:#00ff88"><i class="bi bi-people"></i> Usuarios Registrados</h3>

    <div class="card-dark">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Estado 2FA</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $u['id_usuario'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar"><?= strtoupper(substr($u['nombre'], 0, 1)) ?></div>
                            <strong><?= htmlspecialchars($u['nombre']) ?></strong>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['correo']) ?></td>
                    <td>
                        <?php if($u['rol'] === 'admin'): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-success">Cliente</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
                    <td>
                        <?php if($u['estado_2fa']): ?>
                            <span class="badge bg-warning">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>