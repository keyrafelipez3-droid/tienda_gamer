<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header('Location: login.php');
    exit;
}
$usar_totp      = $_SESSION['usar_totp']      ?? false;
$correo_enviado = $_SESSION['correo_enviado'] ?? false;
$codigo_demo    = $_SESSION['codigo_2fa']     ?? '';
$nombre         = $_SESSION['temp_user']['nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación — GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:     #080808;
            --card:   #111111;
            --raised: #181818;
            --border: #252525;
            --green:  #d4a843;
            --blue:   #3b82f6;
            --red:    #ef4444;
            --text:   #f0f0f8;
            --sub:    #8888a8;
            --muted:  #44445a;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -20%; right: -5%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(212,168,67,.05) 0%, transparent 65%);
            pointer-events: none;
        }
        .wrap { width: 100%; max-width: 400px; position: relative; z-index: 1; }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 36px 32px;
        }
        .brand {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--green);
            text-align: center;
            margin-bottom: 28px;
            letter-spacing: -.5px;
        }
        .brand span { color: var(--text); }

        /* shield visual — CSS only */
        .shield-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .shield {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(212,168,67,.1), rgba(212,168,67,.05));
            border: 1.5px solid rgba(212,168,67,.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--green);
        }
        .shield.blue {
            background: linear-gradient(135deg, rgba(59,130,246,.1), rgba(59,130,246,.05));
            border-color: rgba(59,130,246,.25);
            color: var(--blue);
        }

        .v-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }
        .v-sub {
            font-size: 0.82rem;
            color: var(--sub);
            text-align: center;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .v-sub strong { color: var(--text); }

        .alert-err {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 18px;
            background: rgba(239,68,68,.07);
            border: 1px solid rgba(239,68,68,.2);
            color: #f87171;
        }

        /* code display box */
        .code-panel {
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .code-panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .code-panel.green {
            background: rgba(212,168,67,.05);
            border: 1px solid rgba(212,168,67,.15);
        }
        .code-panel.green::before { background: linear-gradient(90deg, transparent, var(--green), transparent); }
        .code-panel.blue {
            background: rgba(59,130,246,.05);
            border: 1px solid rgba(59,130,246,.15);
        }
        .code-panel.blue::before { background: linear-gradient(90deg, transparent, var(--blue), transparent); }

        .panel-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 14px;
            text-align: center;
        }
        .panel-label.green { color: var(--green); }
        .panel-label.blue  { color: var(--blue); }

        .code-digits {
            display: flex;
            justify-content: center;
            gap: 7px;
            margin-bottom: 10px;
        }
        .code-digit {
            width: 42px;
            height: 50px;
            background: var(--raised);
            border: 1px solid rgba(212,168,67,.2);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk', monospace;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--green);
        }
        .panel-note {
            font-size: 0.72rem;
            color: var(--muted);
            text-align: center;
        }
        .pulse {
            display: inline-block;
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            animation: pulse 1.4s infinite;
            vertical-align: middle;
            margin-right: 6px;
        }
        @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.3; } }

        /* panel for totp/email */
        .panel-body {
            text-align: center;
            padding: 6px 0;
        }
        .panel-app-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 4px;
        }
        .panel-app-sub {
            font-size: 0.75rem;
            color: var(--sub);
        }

        /* input */
        .fg { margin-bottom: 16px; }
        label {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--sub);
            text-transform: uppercase;
            letter-spacing: .7px;
            margin-bottom: 7px;
        }
        .code-input {
            width: 100%;
            background: var(--raised);
            border: 1.5px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 13px 20px;
            font-size: 1.6rem;
            font-family: 'Space Grotesk', monospace;
            font-weight: 800;
            text-align: center;
            letter-spacing: 10px;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .code-input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(212,168,67,.07); }
        .code-input::placeholder { letter-spacing: 4px; font-size: 1.2rem; color: var(--muted); }

        .btn-submit {
            width: 100%;
            background: var(--green);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all .18s;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #c89a30; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(212,168,67,.2); }

        .note-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: var(--raised);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-top: 18px;
            font-size: 0.75rem;
            color: var(--sub);
            line-height: 1.5;
        }
        .note-row i { color: #f59e0b; flex-shrink: 0; margin-top: 1px; }

        .back-link {
            text-align: center;
            margin-top: 16px;
        }
        .back-link a {
            font-size: 0.8rem;
            color: var(--muted);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color .15s;
        }
        .back-link a:hover { color: var(--green); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="brand">Gamer<span>Zone</span></div>

        <div class="shield-wrap">
            <div class="shield <?= $usar_totp ? 'blue' : 'green' ?>">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
        </div>

        <div class="v-title">Verificación en dos pasos</div>
        <div class="v-sub">Hola <strong><?= htmlspecialchars($nombre) ?></strong>, confirma tu identidad para continuar.</div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if ($usar_totp): ?>
            <div class="code-panel blue">
                <div class="panel-label blue">Google Authenticator</div>
                <div class="panel-body">
                    <div class="panel-app-name">GamerZone Bolivia</div>
                    <div class="panel-app-sub">Abre la app y copia el código que aparece para esta cuenta</div>
                    <div class="panel-note" style="margin-top:12px;"><span class="pulse"></span>El código cambia cada 30 segundos</div>
                </div>
            </div>

        <?php elseif (!$correo_enviado): ?>
            <div class="code-panel green">
                <div class="panel-label green">Código de acceso</div>
                <div class="code-digits">
                    <?php foreach (str_split($codigo_demo) as $d): ?>
                        <div class="code-digit"><?= $d ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="panel-note"><span class="pulse"></span>Modo demo — en producción se envía al correo</div>
            </div>

        <?php else: ?>
            <div class="code-panel blue">
                <div class="panel-label blue">Código enviado al correo</div>
                <div class="panel-body">
                    <div class="panel-app-name"><?= $_SESSION['msg_2fa'] ?? '' ?></div>
                    <div class="panel-app-sub">Revisa tu bandeja de entrada y spam</div>
                </div>
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth_controller.php" method="POST">
            <input type="hidden" name="action" value="verify_2fa">
            <div class="fg">
                <label>Ingresa el código de 6 dígitos</label>
                <input type="text" name="codigo" class="code-input" placeholder="——————"
                    maxlength="6" required autofocus autocomplete="one-time-code"
                    pattern="[0-9]{6}" inputmode="numeric">
            </div>
            <button type="submit" class="btn-submit">
                <i class="bi bi-shield-check"></i> Verificar y entrar
            </button>
        </form>

        <div class="note-row">
            <i class="bi bi-info-circle-fill"></i>
            <span>
                <?php if ($usar_totp): ?>
                    Usa el código actual de Google Authenticator para la cuenta <strong>GamerZone Bolivia</strong>.
                <?php else: ?>
                    Ingresa el código de 6 dígitos mostrado arriba. En producción se enviaría a tu correo.
                <?php endif; ?>
            </span>
        </div>

        <div class="back-link">
            <a href="login.php"><i class="bi bi-arrow-left"></i> Volver al login</a>
        </div>
    </div>
</div>
<script>
    document.querySelector('.code-input').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        const full = this.value.length === 6;
        this.style.borderColor = full ? 'var(--green)' : '';
        this.style.boxShadow   = full ? '0 0 0 3px rgba(212,168,67,.07)' : '';
    });
</script>
</body>
</html>
