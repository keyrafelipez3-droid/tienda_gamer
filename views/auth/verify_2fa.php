<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA - GamerZone</title>
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
            background: #070711;
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.06) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .auth-card {
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 24px;
            padding: 40px;
        }

        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: #00ff88;
            text-align: center;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }

        .brand span {
            color: #fff;
        }

        .shield-icon {
            width: 72px;
            height: 72px;
            background: rgba(0, 255, 136, 0.08);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .auth-title {
            font-size: 1.3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-sub {
            color: #555;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        .code-box {
            background: linear-gradient(135deg, rgba(0, 255, 136, 0.06), rgba(0, 204, 106, 0.03));
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .code-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ff88, transparent);
        }

        .code-label {
            font-size: 0.72rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .code-digits {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .code-digit {
            width: 44px;
            height: 52px;
            background: #111120;
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: #00ff88;
            font-family: 'Inter', sans-serif;
        }

        .code-hint {
            font-size: 0.75rem;
            color: #444;
            margin-top: 8px;
        }

        .input-section {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .code-input {
            width: 100%;
            background: #111120;
            border: 1px solid #1a1a2e;
            color: #fff;
            border-radius: 12px;
            padding: 14px;
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            letter-spacing: 12px;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .code-input:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.08);
        }

        .code-input::placeholder {
            letter-spacing: 4px;
            font-size: 1.2rem;
            font-weight: 400;
            color: #333;
        }

        .alert-err {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
        }

        .btn-submit {
            width: 100%;
            background: #00ff88;
            color: #000;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .btn-submit:hover {
            background: #00cc6a;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 255, 136, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #555;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: #00ff88;
        }

        .timer-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
        }

        .timer-dot {
            width: 8px;
            height: 8px;
            background: #00ff88;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .timer-text {
            font-size: 0.78rem;
            color: #555;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #111120;
            border: 1px solid #1a1a2e;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 20px;
        }

        .info-row i {
            color: #f59e0b;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .info-row span {
            font-size: 0.78rem;
            color: #555;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand">Gamer<span>Zone</span></div>
            <div class="shield-icon">🔐</div>
            <h2 class="auth-title">Verificación en dos pasos</h2>
            <p class="auth-sub">Hola <strong
                    style="color:#fff"><?= htmlspecialchars($_SESSION['temp_user']['nombre']) ?></strong>, ingresa el
                código de seguridad generado para tu cuenta.</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-err"><i
                        class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['usar_totp'] ?? false): ?>
                <!-- MODO GOOGLE AUTHENTICATOR -->
                <div class="code-box" style="background:rgba(59,130,246,0.06);border-color:rgba(59,130,246,0.2);">
                    <div class="code-label" style="color:#3b82f6;">Google Authenticator</div>
                    <div style="font-size:3rem;margin:12px 0;">📱</div>
                    <div style="font-size:0.875rem;color:#aaa;">Abre tu app y busca <strong style="color:#fff">GamerZone
                            Bolivia</strong></div>
                    <div class="code-hint">El código cambia cada 30 segundos</div>
                </div>

            <?php elseif (!($_SESSION['correo_enviado'] ?? false)): ?>
                <!-- MODO DEMO: código visible en pantalla -->
                <div class="code-box">
                    <div class="code-label">Tu código de acceso</div>
                    <div class="code-digits">
                        <?php
                        $codigo = $_SESSION['codigo_2fa'] ?? '000000';
                        foreach (str_split($codigo) as $digit):
                            ?>
                            <div class="code-digit"><?= $digit ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="code-hint">Modo demo — en producción se envía al correo</div>
                    <div class="timer-row">
                        <div class="timer-dot"></div>
                        <span class="timer-text">Código activo</span>
                    </div>
                </div>

            <?php else: ?>
                <!-- MODO CORREO ENVIADO -->
                <div class="code-box" style="background:rgba(59,130,246,0.06);border-color:rgba(59,130,246,0.2);">
                    <div class="code-label" style="color:#3b82f6;">Código enviado al correo</div>
                    <div style="font-size:2rem;margin:12px 0;">📧</div>
                    <div style="font-size:0.875rem;color:#aaa;"><?= $_SESSION['msg_2fa'] ?? '' ?></div>
                    <div class="code-hint">Revisa tu bandeja de entrada y spam</div>
                </div>
            <?php endif; ?>

            <form action="../../controllers/auth_controller.php" method="POST">
                <input type="hidden" name="action" value="verify_2fa">
                <div class="input-section">
                    <label class="form-label">Ingresa el código de 6 dígitos</label>
                    <input type="text" name="codigo" class="code-input" placeholder="••••••" maxlength="6" required
                        autofocus autocomplete="one-time-code" pattern="[0-9]{6}" inputmode="numeric">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-shield-check me-2"></i>Verificar y Entrar
                </button>
            </form>

            <div class="info-row">
                <i class="bi bi-info-circle"></i>
                <span>
                    <?php if ($_SESSION['usar_totp'] ?? false): ?>
                        Usa el código de Google Authenticator que aparece en tu celular para esta cuenta.
                    <?php else: ?>
                        El código se muestra en pantalla como método de verificación. En producción se enviaría a tu correo.
                    <?php endif; ?>
                </span>
            </div>

            <div class="back-link">
                <a href="login.php"><i class="bi bi-arrow-left me-1"></i>Volver al login</a>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.code-input').addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
            if (this.value.length === 6) {
                this.style.borderColor = '#00ff88';
                this.style.boxShadow = '0 0 0 3px rgba(0,255,136,0.08)';
            } else {
                this.style.borderColor = '#1a1a2e';
                this.style.boxShadow = 'none';
            }
        });
    </script>
</body>

</html>