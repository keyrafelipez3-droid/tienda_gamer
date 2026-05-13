<?php session_start();
if(!isset($_SESSION['temp_user'])) header('Location: login.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: #111; border: 1px solid #222; border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; }
        .form-control { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 10px; padding: 12px; font-size: 1.5rem; text-align: center; letter-spacing: 8px; font-weight: 700; }
        .form-control:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: 0 0 0 3px rgba(0,255,136,0.1); }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 10px; padding: 12px; width: 100%; font-size: 1rem; }
        .btn-gamer:hover { background: #00cc6a; }
        .brand { color: #00ff88; font-size: 1.8rem; font-weight: 800; text-align: center; margin-bottom: 10px; }
        .brand span { color: #fff; }
        .codigo-box { background: #0d1f0d; border: 2px dashed #00ff88; border-radius: 16px; padding: 20px; text-align: center; margin: 20px 0; }
        .codigo-box .codigo { font-size: 2.5rem; font-weight: 900; color: #00ff88; letter-spacing: 10px; }
        .alert-danger { background: #2a0a0a; border-color: #ff4444; color: #ff4444; }
        a { color: #00ff88; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="brand">Gamer<span>Zone</span></div>
    <h2 class="text-center mb-1" style="color:#00ff88; font-weight:800;">Verificación 2FA</h2>
    <p class="text-muted text-center mb-3">Ingresa el código de seguridad</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Código visible en pantalla -->
    <div class="codigo-box">
        <p class="text-muted mb-1" style="font-size:0.85rem;">Tu código de acceso es:</p>
        <div class="codigo"><?= $_SESSION['codigo_2fa'] ?? '------' ?></div>
        <small class="text-muted">Ingresa este código abajo para continuar</small>
    </div>

    <form action="../../controllers/auth_controller.php" method="POST">
        <input type="hidden" name="action" value="verify_2fa">
        <div class="mb-4">
            <input type="text" name="codigo" class="form-control" placeholder="000000" maxlength="6" required autofocus>
        </div>
        <button type="submit" class="btn btn-gamer">Verificar y Entrar</button>
    </form>
    <p class="text-center mt-3"><a href="login.php">← Volver al login</a></p>
</div>
</body>
</html>