<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone - Tu Tienda Gamer de Confianza</title>
    <meta name="description"
        content="GamerZone - Laptops, monitores, periféricos y consolas gaming al mejor precio en Bolivia.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --verde: #9333ea;
            --verde-dark: #7c3aed;
            --bg: #080810;
            --bg2: #0d0d18;
            --bg3: #12121f;
            --border: #1e1e35;
            --text: #ffffff;
            --text-muted: #8888aa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--verde);
            border-radius: 3px;
        }

        /* NAVBAR */
        .navbar {
            background: rgba(13, 13, 13, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--verde) !important;
            letter-spacing: 1px;
        }

        .navbar-brand span {
            color: #fff;
        }

        .nav-link {
            color: #aaa !important;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
            padding: 6px 14px !important;
            border-radius: 6px;
        }

        .nav-link:hover {
            color: var(--verde) !important;
            background: rgba(0, 255, 136, 0.05);
        }

        .btn-login {
            border: 1.5px solid var(--verde);
            color: var(--verde) !important;
            border-radius: 8px;
            padding: 8px 20px !important;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: var(--verde);
            color: #000 !important;
        }

        .btn-register {
            background: var(--verde);
            color: #000 !important;
            border-radius: 8px;
            padding: 8px 20px !important;
            font-weight: 700;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background: var(--verde-dark);
            transform: translateY(-1px);
        }

        /* HERO */
        .hero {
            min-height: 92vh;
            display: flex;
            align-items: center;
            background: var(--bg);
            position: relative;
            overflow: hidden;
            padding: 80px 0;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at 70% 50%, rgba(0, 255, 136, 0.06) 0%, transparent 60%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.04) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 255, 136, 0.08);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.8rem;
            color: var(--verde);
            margin-bottom: 24px;
        }

        .hero h1 {
            font-family: 'Rajdhani', sans-serif;
            font-size: clamp(2.5rem, 6vw, 5rem);
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .hero h1 .highlight {
            color: var(--verde);
            position: relative;
        }

        .hero p {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.7;
            max-width: 500px;
            margin-bottom: 36px;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 48px;
        }

        .btn-primary-gamer {
            background: var(--verde);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 14px 32px;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary-gamer:hover {
            background: var(--verde-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 255, 136, 0.25);
            color: #000;
        }

        .btn-secondary-gamer {
            background: transparent;
            color: #fff;
            font-weight: 600;
            border: 1.5px solid #333;
            border-radius: 10px;
            padding: 14px 32px;
            font-size: 1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary-gamer:hover {
            border-color: var(--verde);
            color: var(--verde);
            transform: translateY(-2px);
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }

        .stat {
            border-left: 2px solid var(--verde);
            padding-left: 16px;
        }

        .stat-num {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--verde);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .hero-visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-img-container {
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .hero-glow {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 3s ease-in-out infinite;
        }

        .hero-emoji {
            font-size: 12rem;
            text-align: center;
            position: relative;
            z-index: 1;
            filter: drop-shadow(0 0 40px rgba(0, 255, 136, 0.3));
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.5;
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        /* SECCIONES */
        section {
            padding: 90px 0;
        }

        .section-tag {
            display: inline-block;
            background: rgba(0, 255, 136, 0.08);
            border: 1px solid rgba(0, 255, 136, 0.2);
            border-radius: 50px;
            padding: 4px 16px;
            font-size: 0.75rem;
            color: var(--verde);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 16px;
        }

        .section-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 700;
            margin-bottom: 12px;
        }

        .section-title span {
            color: var(--verde);
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            max-width: 500px;
        }

        /* CATEGORÍAS */
        .bg-section2 {
            background: var(--bg2);
        }

        .cat-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .cat-card:hover {
            border-color: var(--verde);
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0, 255, 136, 0.08);
        }

        .cat-icon {
            font-size: 3rem;
            margin-bottom: 16px;
            display: block;
        }

        .cat-name {
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
        }

        .cat-count {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* PRODUCTOS */
        .prod-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
        }

        .prod-card:hover {
            border-color: var(--verde);
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0, 255, 136, 0.08);
        }

        .prod-img {
            background: linear-gradient(135deg, #151515, #0d1f0d);
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            position: relative;
            overflow: hidden;
        }

        .prod-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prod-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--verde);
            color: #000;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .prod-body {
            padding: 20px;
        }

        .prod-marca {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .prod-nombre {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 6px;
            color: #fff;
        }

        .prod-desc {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 14px;
        }

        .prod-precio {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--verde);
        }

        .prod-stock {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .btn-carrito {
            background: var(--verde);
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            width: 100%;
            font-size: 0.9rem;
            transition: all 0.3s;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-carrito:hover {
            background: var(--verde-dark);
            transform: translateY(-1px);
            color: #000;
        }

        /* BENEFICIOS */
        .ben-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s;
        }

        .ben-card:hover {
            border-color: var(--verde);
        }

        .ben-icon {
            width: 56px;
            height: 56px;
            background: rgba(0, 255, 136, 0.1);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .ben-title {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .ben-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* NEWSLETTER */
        .newsletter {
            background: linear-gradient(135deg, #0d1f0d, #0a0a0a);
            border: 1px solid rgba(0, 255, 136, 0.15);
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
        }

        .newsletter h2 {
            font-family: 'Rajdhani', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .newsletter-form {
            display: flex;
            gap: 12px;
            max-width: 500px;
            margin: 24px auto 0;
        }

        .newsletter-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #333;
            color: #fff;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 0.95rem;
        }

        .newsletter-input:focus {
            outline: none;
            border-color: var(--verde);
        }

        .newsletter-input::placeholder {
            color: #555;
        }

        /* FOOTER */
        footer {
            background: var(--bg2);
            border-top: 1px solid var(--border);
            padding: 60px 0 30px;
        }

        .footer-brand {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--verde);
        }

        .footer-brand span {
            color: #fff;
        }

        .footer-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-top: 12px;
            max-width: 280px;
        }

        .footer-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #fff;
            margin-bottom: 16px;
        }

        .footer-link {
            display: block;
            color: var(--text-muted);
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 10px;
            transition: color 0.3s;
        }

        .footer-link:hover {
            color: var(--verde);
        }

        .footer-divider {
            border-color: var(--border);
            margin: 30px 0;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: gap;
        }

        .footer-copy {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .social-link {
            width: 38px;
            height: 38px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            transition: all 0.3s;
            text-decoration: none;
        }

        .social-link:hover {
            border-color: var(--verde);
            color: var(--verde);
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Gamer<span>Zone</span></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="bi bi-list text-white fs-4"></i>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto gap-1">
                    <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                    <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                </ul>
                <div class="d-flex gap-2 mt-2 mt-lg-0">
                    <a href="views/auth/login.php" class="nav-link btn-login">Iniciar Sesión</a>
                    <a href="views/auth/register.php" class="nav-link btn-register">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="inicio">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="hero-tag">
                        <i class="bi bi-lightning-fill"></i>
                        Envío gratis en pedidos mayores a Bs. 500
                    </div>
                    <h1>El equipo que necesitas para <span class="highlight">dominar</span> el juego</h1>
                    <p>Encuentra laptops gamer, monitores de alta frecuencia, periféricos profesionales y consolas de
                        última generación. Todo en un solo lugar.</p>
                    <div class="hero-btns">
                        <a href="views/auth/register.php" class="btn-primary-gamer">
                            <i class="bi bi-controller"></i> Explorar Tienda
                        </a>
                        <a href="#categorias" class="btn-secondary-gamer">
                            <i class="bi bi-grid"></i> Ver Categorías
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-num">500+</div>
                            <div class="stat-label">Productos</div>
                        </div>
                        <div class="stat">
                            <div class="stat-num">1,200+</div>
                            <div class="stat-label">Clientes</div>
                        </div>
                        <div class="stat">
                            <div class="stat-num">4.9★</div>
                            <div class="stat-label">Valoración</div>
                        </div>
                        <div class="stat">
                            <div class="stat-num">24/7</div>
                            <div class="stat-label">Soporte</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <div class="hero-img-container">
                            <div class="hero-glow"></div>
                            <div class="hero-emoji">🎮</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CATEGORÍAS -->
    <section id="categorias" class="bg-section2">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-tag">Explora</span>
                <h2 class="section-title">Nuestras <span>Categorías</span></h2>
                <p class="section-subtitle mx-auto">Encuentra exactamente lo que buscas entre nuestra amplia selección
                </p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">💻</span>
                        <div class="cat-name">Laptops</div>
                        <div class="cat-count">Gaming & Pro</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">🖥️</span>
                        <div class="cat-name">Monitores</div>
                        <div class="cat-count">144Hz - 360Hz</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">🖱️</span>
                        <div class="cat-name">Mouse</div>
                        <div class="cat-count">Alta precisión</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">⌨️</span>
                        <div class="cat-name">Teclados</div>
                        <div class="cat-count">Mecánicos</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">🎮</span>
                        <div class="cat-name">Consolas</div>
                        <div class="cat-count">PS5 & Xbox</div>
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <a href="views/auth/login.php" class="cat-card">
                        <span class="cat-icon">🎧</span>
                        <div class="cat-name">Accesorios</div>
                        <div class="cat-count">Headsets & más</div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section id="productos">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
                <div>
                    <span class="section-tag">Destacados</span>
                    <h2 class="section-title mb-0">Productos <span>Populares</span></h2>
                </div>
                <a href="views/auth/login.php" class="btn-secondary-gamer" style="padding:10px 24px;font-size:0.9rem;">
                    Ver todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="prod-card">
                        <div class="prod-img">
                            <span class="prod-badge">Nuevo</span>
                            💻
                        </div>
                        <div class="prod-body">
                            <p class="prod-marca">ASUS ROG</p>
                            <p class="prod-nombre">ROG Strix G15 Gaming</p>
                            <p class="prod-desc">RTX 3070, Intel i7, 16GB RAM, 144Hz</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="prod-precio">Bs. 8,500</span>
                                <span class="prod-stock"><i class="bi bi-check-circle text-success"></i> En stock</span>
                            </div>
                            <a href="views/auth/login.php" class="btn-carrito">
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="prod-card">
                        <div class="prod-img">
                            <span class="prod-badge" style="background:#ff6b35;">Oferta</span>
                            🖥️
                        </div>
                        <div class="prod-body">
                            <p class="prod-marca">LG</p>
                            <p class="prod-nombre">Monitor 27" 144Hz IPS</p>
                            <p class="prod-desc">Full HD, 1ms, FreeSync, Panel IPS</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="prod-precio">Bs. 2,800</span>
                                <span class="prod-stock"><i class="bi bi-check-circle text-success"></i> En stock</span>
                            </div>
                            <a href="views/auth/login.php" class="btn-carrito">
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="prod-card">
                        <div class="prod-img">
                            <span class="prod-badge" style="background:#7c3aed;">Popular</span>
                            🖱️
                        </div>
                        <div class="prod-body">
                            <p class="prod-marca">Logitech</p>
                            <p class="prod-nombre">G502 Hero Gaming Mouse</p>
                            <p class="prod-desc">Sensor HERO 25K, 11 botones, peso ajustable</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="prod-precio">Bs. 450</span>
                                <span class="prod-stock"><i class="bi bi-check-circle text-success"></i> En stock</span>
                            </div>
                            <a href="views/auth/login.php" class="btn-carrito">
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="prod-card">
                        <div class="prod-img">
                            <span class="prod-badge">Nuevo</span>
                            🎮
                        </div>
                        <div class="prod-body">
                            <p class="prod-marca">Sony</p>
                            <p class="prod-nombre">PlayStation 5 Slim</p>
                            <p class="prod-desc">4K gaming, SSD ultrarrápido, DualSense incluido</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="prod-precio">Bs. 4,200</span>
                                <span class="prod-stock"><i class="bi bi-check-circle text-success"></i> En stock</span>
                            </div>
                            <a href="views/auth/login.php" class="btn-carrito">
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- BENEFICIOS -->
    <section class="bg-section2" id="nosotros">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-tag">¿Por qué nosotros?</span>
                <h2 class="section-title">Ventajas de comprar en <span>GamerZone</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="ben-card">
                        <div class="ben-icon"><i class="bi bi-truck" style="color:var(--verde)"></i></div>
                        <div class="ben-title">Envío Rápido</div>
                        <div class="ben-desc">Entrega en 24-48 horas a todo Bolivia. Seguimiento en tiempo real de tu
                            pedido.</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ben-card">
                        <div class="ben-icon"><i class="bi bi-shield-check" style="color:var(--verde)"></i></div>
                        <div class="ben-title">Garantía Total</div>
                        <div class="ben-desc">12 meses de garantía en todos nuestros productos. Soporte técnico
                            incluido.</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ben-card">
                        <div class="ben-icon"><i class="bi bi-headset" style="color:var(--verde)"></i></div>
                        <div class="ben-title">Soporte 24/7</div>
                        <div class="ben-desc">Nuestro equipo está disponible las 24 horas para resolver tus dudas.</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ben-card">
                        <div class="ben-icon"><i class="bi bi-lock" style="color:var(--verde)"></i></div>
                        <div class="ben-title">Pago Seguro</div>
                        <div class="ben-desc">Transacciones 100% seguras con encriptación SSL y múltiples métodos de
                            pago.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section>
        <div class="container">
            <div class="newsletter">
                <span class="section-tag">Newsletter</span>
                <h2 class="section-title">¿No quieres perderte las <span>mejores ofertas</span>?</h2>
                <p style="color:var(--text-muted)">Suscríbete y recibe descuentos exclusivos, novedades y preventas
                    antes que nadie.</p>
                <div class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="tu@correo.com">
                    <a href="views/auth/register.php" class="btn-primary-gamer" style="white-space:nowrap;">
                        Suscribirme
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="contacto">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="footer-brand">Gamer<span>Zone</span></div>
                    <p class="footer-desc">Tu tienda gamer de confianza en Bolivia. Equipos de alta calidad para
                        jugadores exigentes.</p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="footer-title">Navegación</div>
                    <a href="#inicio" class="footer-link">Inicio</a>
                    <a href="#categorias" class="footer-link">Categorías</a>
                    <a href="#productos" class="footer-link">Productos</a>
                    <a href="#nosotros" class="footer-link">Nosotros</a>
                </div>
                <div class="col-6 col-lg-2">
                    <div class="footer-title">Mi Cuenta</div>
                    <a href="views/auth/login.php" class="footer-link">Iniciar Sesión</a>
                    <a href="views/auth/register.php" class="footer-link">Registrarse</a>
                    <a href="views/auth/login.php" class="footer-link">Mis Pedidos</a>
                    <a href="views/auth/login.php" class="footer-link">Favoritos</a>
                </div>
                <div class="col-lg-4">
                    <div class="footer-title">Contacto</div>
                    <p class="footer-link"><i class="bi bi-envelope me-2"
                            style="color:var(--verde)"></i>info@gamerzone.bo</p>
                    <p class="footer-link"><i class="bi bi-telephone me-2" style="color:var(--verde)"></i>+591 70 000
                        000</p>
                    <p class="footer-link"><i class="bi bi-geo-alt me-2" style="color:var(--verde)"></i>Cochabamba,
                        Bolivia</p>
                    <p class="footer-link"><i class="bi bi-clock me-2" style="color:var(--verde)"></i>Lun - Sáb: 9:00 -
                        20:00</p>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <p class="footer-copy mb-0">© 2025 GamerZone. Todos los derechos reservados.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="footer-link mb-0" style="font-size:0.8rem;">Política de Privacidad</a>
                    <a href="#" class="footer-link mb-0" style="font-size:0.8rem;">Términos y Condiciones</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>