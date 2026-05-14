<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#070711;color:#fff;font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
        body::before{content:'';position:absolute;top:-30%;right:-10%;width:600px;height:600px;background:radial-gradient(circle,rgba(0,255,136,0.06) 0%,transparent 70%);border-radius:50%;pointer-events:none;}
        body::after{content:'';position:absolute;bottom:-20%;left:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,0.05) 0%,transparent 70%);border-radius:50%;pointer-events:none;}
        .auth-wrapper{width:100%;max-width:440px;padding:20px;position:relative;z-index:1;}
        .back-link{display:inline-flex;align-items:center;gap:6px;color:#555;text-decoration:none;font-size:0.82rem;margin-bottom:24px;transition:color 0.2s;}
        .back-link:hover{color:#00ff88;}
        .auth-card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:24px;padding:40px;}
        .brand{font-size:1.8rem;font-weight:800;color:#00ff88;text-align:center;margin-bottom:8px;letter-spacing:1px;}
        .brand span{color:#fff;}
        .auth-title{font-size:1.4rem;font-weight:800;text-align:center;margin-bottom:6px;}
        .auth-sub{color:#555;font-size:0.875rem;text-align:center;margin-bottom:32px;}
        .form-group{margin-bottom:20px;}
        .form-label{font-size:0.78rem;font-weight:600;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:block;}
        .input-wrap{position:relative;}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#444;font-size:1rem;pointer-events:none;}
        .form-input{width:100%;background:#111120;border:1px solid #1a1a2e;color:#fff;border-radius:12px;padding:12px 14px 12px 42px;font-size:0.9rem;transition:all 0.2s;font-family:'Inter',sans-serif;}
        .form-input:focus{outline:none;border-color:#00ff88;background:#111120;box-shadow:0 0 0 3px rgba(0,255,136,0.08);}
        .form-input::placeholder{color:#333;}
        .toggle-pass{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#444;cursor:pointer;font-size:1rem;transition:color 0.2s;background:none;border:none;padding:0;}
        .toggle-pass:hover{color:#00ff88;}
        .alert-err{background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#ef4444;border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:0.875rem;}
        .alert-ok{background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:0.875rem;}
        .btn-submit{width:100%;background:#00ff88;color:#000;font-weight:800;border:none;border-radius:12px;padding:14px;font-size:1rem;cursor:pointer;transition:all 0.2s;font-family:'Inter',sans-serif;margin-top:8px;}
        .btn-submit:hover{background:#00cc6a;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,255,136,0.3);}
        .divider{display:flex;align-items:center;gap:12px;margin:24px 0;}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#1a1a2e;}
        .divider span{color:#333;font-size:0.78rem;}
        .auth-link{text-align:center;font-size:0.875rem;color:#555;}
        .auth-link a{color:#00ff88;text-decoration:none;font-weight:600;}
        .auth-link a:hover{color:#00cc6a;}
        .features{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:24px;}
        .feature{background:#111120;border:1px solid #1a1a2e;border-radius:10px;padding:10px;text-align:center;}
        .feature i{color:#00ff88;font-size:1rem;display:block;margin-bottom:4px;}
        .feature span{font-size:0.68rem;color:#555;}
    </style>
</head>
<body>
<div class="auth-wrapper">
    <a href="../../index.php" class="back-link"><i class="bi bi-arrow-left"></i> Volver al inicio</a>

    <div class="auth-card">
        <div class="brand">Gamer<span>Zone</span></div>
        <h2 class="auth-title">Bienvenido de vuelta</h2>
        <p class="auth-sub">Ingresa tus credenciales para continuar</p>

        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/auth_controller.php" method="POST" id="loginForm">
            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label class="form-label">Correo electrónico</label>
                <div class="input-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="correo" class="form-input" placeholder="tu@correo.com" required autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <div class="input-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="contrasena" id="passLogin" class="form-input" placeholder="Tu contraseña" required autocomplete="current-password">
                    <button type="button" class="toggle-pass" onclick="togglePass('passLogin', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
            </button>
        </form>

        <div class="divider"><span>¿No tienes cuenta?</span></div>
        <div class="auth-link"><a href="register.php">Crear cuenta gratis</a></div>

        <div class="features">
            <div class="feature"><i class="bi bi-shield-lock"></i><span>Login seguro</span></div>
            <div class="feature"><i class="bi bi-phone"></i><span>2FA activado</span></div>
            <div class="feature"><i class="bi bi-controller"></i><span>Gamer Zone</span></div>
        </div>
    </div>
</div>

<script>
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if(input.type === 'password') {
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