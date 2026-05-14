<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        .form-input:invalid:not(:placeholder-shown) {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.08);
        }

        .form-input:valid:not(:placeholder-shown) {
            border-color: #00ff88;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.08);
        }

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
            padding: 20px 0;
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

        body::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #555;
            text-decoration: none;
            font-size: 0.82rem;
            margin-bottom: 24px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #00ff88;
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
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .brand span {
            color: #fff;
        }

        .auth-title {
            font-size: 1.4rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 6px;
        }

        .auth-sub {
            color: #555;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 18px;
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

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
            font-size: 1rem;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            background: #111120;
            border: 1px solid #1a1a2e;
            color: #fff;
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #00ff88;
            background: #111120;
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.08);
        }

        .form-input::placeholder {
            color: #333;
        }

        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .form-input.is-valid {
            border-color: #00ff88;
        }

        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #444;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.2s;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-pass:hover {
            color: #00ff88;
        }

        .field-hint {
            font-size: 0.72rem;
            color: #444;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-hint.ok {
            color: #00ff88;
        }

        .field-hint.err {
            color: #ef4444;
        }

        .pass-strength {
            height: 3px;
            border-radius: 2px;
            margin-top: 8px;
            background: #1a1a2e;
            overflow: hidden;
        }

        .pass-strength-bar {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s;
            width: 0;
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
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: #00cc6a;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 255, 136, 0.3);
        }

        .btn-submit:disabled {
            background: #1a1a2e;
            color: #444;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #1a1a2e;
        }

        .divider span {
            color: #333;
            font-size: 0.78rem;
        }

        .auth-link {
            text-align: center;
            font-size: 0.875rem;
            color: #555;
        }

        .auth-link a {
            color: #00ff88;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link a:hover {
            color: #00cc6a;
        }

        .benefits {
            background: #111120;
            border: 1px solid #1a1a2e;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }

        .benefits-title {
            font-size: 0.72rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            color: #777;
            margin-bottom: 6px;
        }

        .benefit-item:last-child {
            margin-bottom: 0;
        }

        .benefit-item i {
            color: #00ff88;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <a href="../../index.php" class="back-link"><i class="bi bi-arrow-left"></i> Volver al inicio</a>

        <div class="auth-card">
            <div class="brand">Gamer<span>Zone</span></div>
            <h2 class="auth-title">Crear cuenta gratis</h2>
            <p class="auth-sub">Únete a la comunidad gamer más grande de Bolivia</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error'];
                unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../../controllers/auth_controller.php" method="POST" id="registerForm">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <div class="input-wrap">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="nombre" id="nombre" class="form-input" placeholder="Tu nombre completo"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{2,50}"
                            title="Solo letras y espacios, entre 2 y 50 caracteres" required autocomplete="name">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="correo" id="correo" class="form-input" placeholder="tu@correo.com"
                            pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}"
                            title="Ingresa un correo válido. Ej: usuario@gmail.com" required autocomplete="email">
                    </div>
                    <div class="field-hint" id="correoHint"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" name="contrasena" id="pass1" class="form-input"
                            placeholder="Mínimo 6 caracteres" pattern="(?=.*[0-9])(?=.*[a-zA-Z]).{6,}"
                            title="Mínimo 6 caracteres, debe incluir letras y números" required
                            autocomplete="new-password">
                        <button type="button" class="toggle-pass" onclick="togglePass('pass1', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="pass-strength">
                        <div class="pass-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="field-hint" id="passHint"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar contraseña</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock-fill input-icon"></i>
                        <input type="password" name="confirmar" id="pass2" class="form-input"
                            placeholder="Repite tu contraseña" pattern=".{6,}" title="Repite la contraseña" required
                            autocomplete="new-password">
                        <button type="button" class="toggle-pass" onclick="togglePass('pass2', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="field-hint" id="confirmHint"></div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="bi bi-person-plus me-2"></i>Crear mi cuenta
                </button>
            </form>

            <div class="divider"><span>¿Ya tienes cuenta?</span></div>
            <div class="auth-link"><a href="login.php">Iniciar sesión</a></div>

            <div class="benefits">
                <div class="benefits-title">Al registrarte obtienes</div>
                <div class="benefit-item"><i class="bi bi-check-circle-fill"></i>Acceso a todos los productos</div>
                <div class="benefit-item"><i class="bi bi-check-circle-fill"></i>Carrito y lista de favoritos</div>
                <div class="benefit-item"><i class="bi bi-check-circle-fill"></i>Historial de compras</div>
                <div class="benefit-item"><i class="bi bi-check-circle-fill"></i>Autenticación 2FA segura</div>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
                btn.style.color = '#00ff88';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
                btn.style.color = '#444';
            }
        }

        // Fortaleza de contraseña
        document.getElementById('pass1').addEventListener('input', function () {
            const val = this.value;
            const bar = document.getElementById('strengthBar');
            const hint = document.getElementById('passHint');
            let strength = 0;
            let color = '#ef4444';
            let msg = '';

            if (val.length >= 6) strength++;
            if (val.length >= 10) strength++;
            if (/[A-Z]/.test(val)) strength++;
            if (/[0-9]/.test(val)) strength++;
            if (/[^A-Za-z0-9]/.test(val)) strength++;

            const levels = [
                { w: '20%', c: '#ef4444', m: 'Muy débil' },
                { w: '40%', c: '#f59e0b', m: 'Débil' },
                { w: '60%', c: '#f59e0b', m: 'Regular' },
                { w: '80%', c: '#00ff88', m: 'Fuerte' },
                { w: '100%', c: '#00ff88', m: 'Muy fuerte ✓' }
            ];

            const level = levels[Math.min(strength - 1, 4)] || { w: '0%', c: '#ef4444', m: '' };
            bar.style.width = strength > 0 ? level.w : '0%';
            bar.style.background = level.c;
            hint.textContent = val.length > 0 ? level.m : '';
            hint.className = 'field-hint ' + (strength >= 3 ? 'ok' : 'err');
            checkForm();
        });

        // Confirmar contraseña
        document.getElementById('pass2').addEventListener('input', function () {
            const p1 = document.getElementById('pass1').value;
            const hint = document.getElementById('confirmHint');
            if (this.value === '') { hint.textContent = ''; return; }
            if (this.value === p1) {
                hint.textContent = '✓ Las contraseñas coinciden';
                hint.className = 'field-hint ok';
                this.classList.remove('is-invalid'); this.classList.add('is-valid');
            } else {
                hint.textContent = '✗ Las contraseñas no coinciden';
                hint.className = 'field-hint err';
                this.classList.remove('is-valid'); this.classList.add('is-invalid');
            }
            checkForm();
        });

        // Validar email
        document.getElementById('correo').addEventListener('input', function () {
            const hint = document.getElementById('correoHint');
            const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
            if (this.value === '') { hint.textContent = ''; return; }
            if (valid) {
                hint.textContent = '✓ Correo válido';
                hint.className = 'field-hint ok';
            } else {
                hint.textContent = '✗ Ingresa un correo válido';
                hint.className = 'field-hint err';
            }
            checkForm();
        });

        function checkForm() {
            const nombre = document.getElementById('nombre').value.length > 1;
            const correo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('correo').value);
            const pass1 = document.getElementById('pass1').value.length >= 6;
            const pass2 = document.getElementById('pass2').value === document.getElementById('pass1').value && pass1;
            document.getElementById('btnSubmit').disabled = !(nombre && correo && pass1 && pass2);
        }

        // Validar al enviar
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const p1 = document.getElementById('pass1').value;
            const p2 = document.getElementById('pass2').value;
            if (p1 !== p2) { e.preventDefault(); return; }
            if (p1.length < 6) { e.preventDefault(); return; }
        });
    </script>
</body>

</html>