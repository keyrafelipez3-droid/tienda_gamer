<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:     #080808;
            --card:   #111111;
            --raised: #181818;
            --border: #252525;
            --green:  #d4a843;
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
            padding: 28px 16px;
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
        body::after {
            content: '';
            position: absolute;
            bottom: -20%; left: -5%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(212,168,67,.04) 0%, transparent 65%);
            pointer-events: none;
        }
        .wrap {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        .back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 20px;
            transition: color .15s;
        }
        .back:hover { color: var(--green); }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-top: 3px solid var(--green);
            border-radius: 20px;
            padding: 36px 32px;
        }
        .brand {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--green);
            text-align: center;
            margin-bottom: 24px;
            letter-spacing: -.5px;
        }
        .brand span { color: var(--text); }
        .card-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }
        .card-sub {
            font-size: 0.82rem;
            color: var(--sub);
            text-align: center;
            margin-bottom: 28px;
        }
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 18px;
        }
        .alert-err { background: rgba(239,68,68,.07); border: 1px solid rgba(239,68,68,.2); color: #f87171; }
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
        .iw { position: relative; }
        .iw i.ico {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: .9rem;
            pointer-events: none;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            background: var(--raised);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 11px 42px;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .15s, box-shadow .15s;
        }
        input:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(212,168,67,.07); }
        input::placeholder { color: var(--muted); }
        input:invalid:not(:placeholder-shown) { border-color: rgba(239,68,68,.5); }
        input:valid:not(:placeholder-shown)   { border-color: rgba(212,168,67,.3); }
        .tog {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: .9rem;
            padding: 0;
            transition: color .15s;
        }
        .tog:hover { color: var(--green); }
        .hint {
            font-size: 0.7rem;
            margin-top: 5px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 4px;
            min-height: 18px;
        }
        .hint.ok  { color: var(--green); }
        .hint.err { color: #f87171; }
        .strength-track {
            height: 3px;
            background: var(--border);
            border-radius: 3px;
            margin-top: 7px;
            overflow: hidden;
        }
        .strength-fill { height: 100%; border-radius: 3px; transition: all .3s; width: 0; }
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
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover { background: #c89a30; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(212,168,67,.2); }
        .btn-submit:disabled { background: var(--border); color: var(--muted); cursor: not-allowed; transform: none; box-shadow: none; }
        .sep {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
        }
        .sep::before, .sep::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .sep span { font-size: 0.72rem; color: var(--muted); white-space: nowrap; }
        .foot { text-align: center; font-size: 0.82rem; color: var(--sub); }
        .foot a { color: var(--green); text-decoration: none; font-weight: 600; }
        .foot a:hover { color: #c89a30; }
        .btn-oauth { display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:12px;border-radius:10px;font-size:0.875rem;font-weight:600;text-decoration:none;transition:all .18s;border:1.5px solid var(--border);color:var(--text);background:var(--raised); }
        .btn-oauth:hover { border-color:var(--green);color:var(--green);background:rgba(212,168,67,0.04); }
    </style>
</head>
<body>
<div class="wrap">
    <a href="../../index.php" class="back"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
    <div class="card">
        <div class="brand">Gamer<span>Zone</span></div>
        <div class="card-title">Crear cuenta gratis</div>
        <div class="card-sub">Únete a la comunidad gamer de Bolivia</div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="../../controllers/auth_controller.php" method="POST" id="rf">
            <input type="hidden" name="action" value="register">

            <div class="fg">
                <label>Nombre completo</label>
                <div class="iw">
                    <i class="bi bi-person ico"></i>
                    <input type="text" name="nombre" id="nombre" placeholder="Tu nombre completo"
                        pattern="[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]{2,50}" required autocomplete="name">
                </div>
            </div>

            <div class="fg">
                <label>Correo electrónico</label>
                <div class="iw">
                    <i class="bi bi-envelope ico"></i>
                    <input type="email" name="correo" id="correo" placeholder="tu@correo.com" required autocomplete="email">
                </div>
                <div class="hint" id="hCorreo"></div>
            </div>

            <div class="fg">
                <label>Contraseña</label>
                <div class="iw">
                    <i class="bi bi-lock ico"></i>
                    <input type="password" name="contrasena" id="p1" placeholder="Mínimo 6 caracteres"
                        pattern="(?=.*[0-9])(?=.*[a-zA-Z]).{6,}" required autocomplete="new-password">
                    <button type="button" class="tog" onclick="tp('p1',this)"><i class="bi bi-eye"></i></button>
                </div>
                <div class="strength-track"><div class="strength-fill" id="sf"></div></div>
                <div class="hint" id="hPass"></div>
            </div>

            <div class="fg">
                <label>Confirmar contraseña</label>
                <div class="iw">
                    <i class="bi bi-lock-fill ico"></i>
                    <input type="password" name="confirmar" id="p2" placeholder="Repite la contraseña"
                        pattern=".{6,}" required autocomplete="new-password">
                    <button type="button" class="tog" onclick="tp('p2',this)"><i class="bi bi-eye"></i></button>
                </div>
                <div class="hint" id="hConfirm"></div>
            </div>

            <button type="submit" class="btn-submit" id="btnReg" disabled>
                <i class="bi bi-person-plus"></i> Crear mi cuenta
            </button>
        </form>

        <div class="sep"><span>O regístrate con</span></div>
        <a href="../../controllers/auth_controller.php?action=oauth_github" class="btn-oauth">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/></svg>
            Continuar con GitHub
        </a>
        <div class="sep" style="margin-top:18px;"><span>¿Ya tienes cuenta?</span></div>
        <div class="foot"><a href="login.php">Iniciar sesión</a></div>
    </div>
</div>
<script>
    function tp(id, btn) {
        const i = document.getElementById(id);
        const ic = btn.querySelector('i');
        if (i.type === 'password') { i.type = 'text'; ic.className = 'bi bi-eye-slash'; btn.style.color = 'var(--green)'; }
        else { i.type = 'password'; ic.className = 'bi bi-eye'; btn.style.color = ''; }
    }

    function check() {
        const ok = document.getElementById('nombre').value.length > 1
            && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(document.getElementById('correo').value)
            && document.getElementById('p1').value.length >= 6
            && document.getElementById('p2').value === document.getElementById('p1').value;
        document.getElementById('btnReg').disabled = !ok;
    }

    document.getElementById('p1').addEventListener('input', function () {
        const v = this.value, sf = document.getElementById('sf'), h = document.getElementById('hPass');
        let s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        const lvl = [['20%','#ef4444','Muy débil'],['40%','#f59e0b','Débil'],['60%','#f59e0b','Regular'],['80%','#d4a843','Fuerte'],['100%','#d4a843','Muy fuerte']][Math.min(s-1,4)] || ['0%','',''];
        sf.style.width = s > 0 ? lvl[0] : '0%';
        sf.style.background = lvl[1];
        h.textContent = v.length > 0 ? lvl[2] : '';
        h.className = 'hint ' + (s >= 3 ? 'ok' : 'err');
        check();
    });

    document.getElementById('p2').addEventListener('input', function () {
        const h = document.getElementById('hConfirm');
        if (!this.value) { h.textContent = ''; return; }
        const match = this.value === document.getElementById('p1').value;
        h.textContent = match ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden';
        h.className = 'hint ' + (match ? 'ok' : 'err');
        check();
    });

    document.getElementById('correo').addEventListener('input', function () {
        const h = document.getElementById('hCorreo'), valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value);
        if (!this.value) { h.textContent = ''; return; }
        h.textContent = valid ? 'Correo válido' : 'Correo inválido';
        h.className = 'hint ' + (valid ? 'ok' : 'err');
        check();
    });

    document.getElementById('nombre').addEventListener('input', check);

    document.getElementById('rf').addEventListener('submit', function(e) {
        if (document.getElementById('p1').value !== document.getElementById('p2').value) e.preventDefault();
    });
</script>
</body>
</html>
