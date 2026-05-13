<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - GamerZone</title>
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
        .alert-success { background: #0a2a0a; border-color: #00ff88; color: #00ff88; }
        .invalid-feedback { color: #ff4444; font-size: 0.8rem; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand">Gamer<span>Zone</span></div>
    <h2 class="text-center mb-1">Crear cuenta</h2>
    <p class="text-muted text-center mb-4">Únete a la comunidad gamer</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="../../controllers/auth_controller.php" method="POST" id="formRegister">
        <input type="hidden" name="action" value="register">

        <div class="mb-3">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="contrasena" id="pass1" class="form-control" placeholder="Mínimo 6 caracteres" required>
        </div>
        <div class="mb-4">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="confirmar" id="pass2" class="form-control" placeholder="Repite tu contraseña" required>
            <div class="invalid-feedback" id="passError"></div>
        </div>
        <button type="submit" class="btn btn-gamer">Registrarse</button>
    </form>
    <p class="text-center mt-3 text-muted">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    <p class="text-center mt-2"><a href="../../index.php">← Volver al inicio</a></p>
</div>

<script>
document.getElementById('formRegister').addEventListener('submit', function(e) {
    const p1 = document.getElementById('pass1').value;
    const p2 = document.getElementById('pass2').value;
    const err = document.getElementById('passError');
    if (p1 !== p2) {
        e.preventDefault();
        document.getElementById('pass2').classList.add('is-invalid');
        err.textContent = 'Las contraseñas no coinciden';
    }
    if (p1.length < 6) {
        e.preventDefault();
        document.getElementById('pass1').classList.add('is-invalid');
    }
});
</script>
</body>
</html>