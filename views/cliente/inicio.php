<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; font-size: 1.5rem; }
        .navbar-brand span { color: #fff; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover { color: #00ff88 !important; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .welcome-box { background: #111; border: 1px solid #00ff88; border-radius: 20px; padding: 40px; margin-top: 40px; text-align: center; }
        .welcome-box h2 { color: #00ff88; font-weight: 800; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">Gamer<span>Zone</span></a>
        <div class="d-flex gap-2 ms-auto">
            <span class="text-muted my-auto">Hola, <strong class="text-white"><?= $_SESSION['usuario_nombre'] ?></strong></span>
            <a href="../../controllers/auth_controller.php?action=logout" 
               onclick="this.href+=''; document.cookie=''; return true;"
               class="btn btn-gamer btn-sm"
               onclick="document.querySelector('form').submit()">
            </a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-gamer btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    <div class="welcome-box">
        <div style="font-size:4rem;">🎮</div>
        <h2>¡Bienvenido, <?= $_SESSION['usuario_nombre'] ?>!</h2>
        <p class="text-muted mt-2">Estás dentro del sistema como <strong class="text-success">cliente</strong></p>
        <p class="text-muted">Pronto aquí podrás ver productos, tu carrito y tus favoritos.</p>
        <a href="../../index.php" class="btn btn-gamer mt-3 px-4">Ver tienda</a>
    </div>
</div>
</body>
</html>