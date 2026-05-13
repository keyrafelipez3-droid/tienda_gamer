<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: #111; border: 1px solid #222; border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; }
        .auth-card h2 { color: #00ff88; font-weight: 800; }
        .form-control { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 10px; padding: 12px; }
        .form-control:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: 0 0 0 3px rgba(0,255,136,0.1); }
        .form-label { color: #aaa; font-size: 0.9rem; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 10px; padding: 12px; width: 100%; font-size: 1rem; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .brand { color: #00ff88; font-size: 1.8rem; font-weight: 800; text-align: center; margin-bottom: 10px; }
        .brand span { color: #fff; }
        a { color: #00ff88; }
        .alert-danger { background: #2a0a0a; border-color: #ff4444; color: #ff4444; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand">Gamer<span>Zone</span></div>
    <h2 class="text-center mb-1">Iniciar sesión</h2>
    <p class="text-muted text-center mb-4">Bienvenido de vuelta</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="../../controllers/auth_controller.php" method="POST">
        <input type="hidden" name="action" value="login">
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Contraseña</label>
            <input type="password" name="contrasena" class="form-control" placeholder="Tu contraseña" required>
        </div>
        <button type="submit" class="btn btn-gamer">Entrar</button>
    </form>
    <p class="text-center mt-3 text-muted">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    <p class="text-center mt-2"><a href="../../index.php">← Volver al inicio</a></p>
</div>
</body>
</html>