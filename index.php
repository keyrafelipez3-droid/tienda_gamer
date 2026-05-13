 
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone - Equipos Gamer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }

        /* NAVBAR */
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-size: 1.8rem; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .nav-link { color: #ccc !important; transition: color 0.3s; }
        .nav-link:hover { color: #00ff88 !important; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .btn-outline-gamer { border: 2px solid #00ff88; color: #00ff88; font-weight: 700; border-radius: 8px; background: transparent; }
        .btn-outline-gamer:hover { background: #00ff88; color: #000; }

        /* HERO */
        .hero {
            background: linear-gradient(135deg, #0a0a0a 0%, #0d1f0d 50%, #0a0a0a 100%);
            padding: 100px 0;
            border-bottom: 1px solid #1a1a1a;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0,255,136,0.05) 0%, transparent 60%);
        }
        .hero h1 { font-size: 3.5rem; font-weight: 900; line-height: 1.2; }
        .hero h1 span { color: #00ff88; }
        .hero p { color: #aaa; font-size: 1.2rem; }
        .hero-img { font-size: 12rem; text-align: center; filter: drop-shadow(0 0 30px #00ff88); }

        /* CATEGORÍAS */
        .section-title { font-size: 2rem; font-weight: 800; }
        .section-title span { color: #00ff88; }
        .categoria-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .categoria-card:hover { border-color: #00ff88; transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,255,136,0.1); }
        .categoria-card .icon { font-size: 3rem; margin-bottom: 15px; }
        .categoria-card h5 { color: #fff; font-weight: 700; }

        /* PRODUCTOS */
        .producto-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s;
        }
        .producto-card:hover { border-color: #00ff88; transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,255,136,0.1); }
        .producto-card .img-placeholder {
            background: linear-gradient(135deg, #1a1a1a, #0d1f0d);
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
        }
        .producto-card .card-body { padding: 20px; }
        .producto-card .precio { color: #00ff88; font-size: 1.4rem; font-weight: 800; }
        .producto-card .nombre { color: #fff; font-weight: 700; font-size: 1rem; }
        .producto-card .marca { color: #777; font-size: 0.85rem; }
        .badge-nuevo { background: #00ff88; color: #000; font-size: 0.75rem; font-weight: 700; }

        /* BENEFICIOS */
        .beneficio-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
        }
        .beneficio-card .icon { font-size: 2.5rem; color: #00ff88; margin-bottom: 15px; }

        /* FOOTER */
        footer { background: #0d0d0d; border-top: 2px solid #00ff88; padding: 50px 0 20px; }
        footer h5 { color: #00ff88; font-weight: 700; }
        footer p, footer a { color: #888; font-size: 0.9rem; text-decoration: none; }
        footer a:hover { color: #00ff88; }

        /* SECCIÓN */
        section { padding: 80px 0; }
        .bg-dark-2 { background: #0d0d0d; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">Gamer<span>Zone</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon" style="filter: invert(1)"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto gap-2">
                <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
            </ul>
            <div class="d-flex gap-2">
                <a href="views/auth/login.php" class="btn btn-outline-gamer">Iniciar Sesión</a>
                <a href="views/auth/register.php" class="btn btn-gamer">Registrarse</a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="inicio">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <p class="text-success mb-2">⚡ Los mejores equipos al mejor precio</p>
                <h1>El equipo que necesitas para <span>dominar</span> el juego</h1>
                <p class="mt-4 mb-4">Encuentra laptops, monitores, periféricos y consolas de las mejores marcas. Envío rápido y garantía asegurada.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="views/auth/register.php" class="btn btn-gamer btn-lg px-4">Ver Productos</a>
                    <a href="#categorias" class="btn btn-outline-gamer btn-lg px-4">Explorar</a>
                </div>
                <div class="d-flex gap-4 mt-5">
                    <div><h3 class="text-success mb-0">500+</h3><small class="text-muted">Productos</small></div>
                    <div><h3 class="text-success mb-0">1K+</h3><small class="text-muted">Clientes</small></div>
                    <div><h3 class="text-success mb-0">5★</h3><small class="text-muted">Valoración</small></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-img">🎮</div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORÍAS -->
<section id="categorias" class="bg-dark-2">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Nuestras <span>Categorías</span></h2>
            <p class="text-muted">Encuentra exactamente lo que buscas</p>
        </div>
        <div class="row g-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">💻</div>
                    <h5>Laptops</h5>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">🖥️</div>
                    <h5>Monitores</h5>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">🖱️</div>
                    <h5>Mouse</h5>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">⌨️</div>
                    <h5>Teclados</h5>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">🎮</div>
                    <h5>Consolas</h5>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="categoria-card">
                    <div class="icon">🎧</div>
                    <h5>Accesorios</h5>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section id="productos">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Productos <span>Destacados</span></h2>
            <p class="text-muted">Los más vendidos de la semana</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="producto-card">
                    <div class="img-placeholder">💻</div>
                    <div class="card-body">
                        <span class="badge badge-nuevo mb-2">NUEVO</span>
                        <p class="marca">ASUS ROG</p>
                        <p class="nombre">Laptop Gamer ROG Strix G15</p>
                        <p class="precio mt-2">Bs. 8,500</p>
                        <a href="views/auth/login.php" class="btn btn-gamer w-100 mt-3">Agregar al carrito</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="producto-card">
                    <div class="img-placeholder">🖥️</div>
                    <div class="card-body">
                        <span class="badge badge-nuevo mb-2">OFERTA</span>
                        <p class="marca">LG</p>
                        <p class="nombre">Monitor 27" 144Hz IPS</p>
                        <p class="precio mt-2">Bs. 2,800</p>
                        <a href="views/auth/login.php" class="btn btn-gamer w-100 mt-3">Agregar al carrito</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="producto-card">
                    <div class="img-placeholder">🖱️</div>
                    <div class="card-body">
                        <span class="badge badge-nuevo mb-2">POPULAR</span>
                        <p class="marca">Logitech</p>
                        <p class="nombre">Mouse G502 Hero Gaming</p>
                        <p class="precio mt-2">Bs. 450</p>
                        <a href="views/auth/login.php" class="btn btn-gamer w-100 mt-3">Agregar al carrito</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="producto-card">
                    <div class="img-placeholder">🎮</div>
                    <div class="card-body">
                        <span class="badge badge-nuevo mb-2">NUEVO</span>
                        <p class="marca">Sony</p>
                        <p class="nombre">PlayStation 5 Slim</p>
                        <p class="precio mt-2">Bs. 4,200</p>
                        <a href="views/auth/login.php" class="btn btn-gamer w-100 mt-3">Agregar al carrito</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BENEFICIOS -->
<section class="bg-dark-2" id="nosotros">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">¿Por qué elegirnos?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="beneficio-card">
                    <div class="icon"><i class="bi bi-truck"></i></div>
                    <h5>Envío Rápido</h5>
                    <p class="text-muted mt-2">Entrega en 24-48 horas a todo el país</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="beneficio-card">
                    <div class="icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Garantía</h5>
                    <p class="text-muted mt-2">1 año de garantía en todos los productos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="beneficio-card">
                    <div class="icon"><i class="bi bi-headset"></i></div>
                    <h5>Soporte 24/7</h5>
                    <p class="text-muted mt-2">Atención al cliente todos los días</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="beneficio-card">
                    <div class="icon"><i class="bi bi-credit-card"></i></div>
                    <h5>Pago Seguro</h5>
                    <p class="text-muted mt-2">Múltiples métodos de pago seguros</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <h5>GamerZone</h5>
                <p class="mt-3">Tu tienda gamer de confianza. Los mejores equipos para los mejores jugadores.</p>
            </div>
            <div class="col-md-2">
                <h5>Links</h5>
                <ul class="list-unstyled mt-3">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#categorias">Categorías</a></li>
                    <li><a href="#productos">Productos</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Cuenta</h5>
                <ul class="list-unstyled mt-3">
                    <li><a href="views/auth/login.php">Iniciar Sesión</a></li>
                    <li><a href="views/auth/register.php">Registrarse</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contacto</h5>
                <ul class="list-unstyled mt-3">
                    <li><p>📧 info@gamerzone.com</p></li>
                    <li><p>📱 +591 70000000</p></li>
                    <li><p>📍 Cochabamba, Bolivia</p></li>
                </ul>
            </div>
        </div>
        <hr style="border-color: #222;">
        <p class="text-center text-muted">© 2025 GamerZone. Todos los derechos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>