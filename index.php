<?php
session_start();
require_once 'config/db.php';
$prod_destacados = $conn->query("SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria WHERE p.estado=1 ORDER BY p.id_producto DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamerZone - Tu Tienda Gamer en Bolivia</title>
    <meta name="description" content="GamerZone - Laptops, monitores, periféricos y consolas gaming al mejor precio en Bolivia.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #d4a843;
            --green-dark: #c89a30;
            --bg: #080808;
            --bg2: #111111;
            --bg3: #181818;
            --border: #252525;
            --text: #fff;
            --muted: #555;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        html{scroll-behavior:smooth;}
        body{background:var(--bg);color:var(--text);font-family:'Inter',sans-serif;overflow-x:hidden;}
        ::-webkit-scrollbar{width:5px;}
        ::-webkit-scrollbar-thumb{background:var(--green);border-radius:3px;}

        /* ── NAVBAR ── */
        .navbar{background:rgba(7,7,17,0.9);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);padding:16px 0;position:sticky;top:0;z-index:1000;transition:all 0.3s;}
        .nav-brand{font-family:'Rajdhani',sans-serif;font-size:1.9rem;font-weight:700;color:var(--green);text-decoration:none;letter-spacing:2px;}
        .nav-brand span{color:#fff;}
        .nav-link-item{color:#888;text-decoration:none;font-size:0.875rem;font-weight:500;padding:8px 16px;border-radius:8px;transition:all 0.2s;}
        .nav-link-item:hover{color:#fff;background:rgba(255,255,255,0.05);}
        .btn-nav-login{border:1.5px solid var(--green);color:var(--green);border-radius:10px;padding:8px 20px;font-size:0.875rem;font-weight:600;text-decoration:none;transition:all 0.2s;}
        .btn-nav-login:hover{background:var(--green);color:#000;}
        .btn-nav-register{background:var(--green);color:#000;border-radius:10px;padding:8px 20px;font-size:0.875rem;font-weight:700;text-decoration:none;transition:all 0.2s;border:1.5px solid var(--green);}
        .btn-nav-register:hover{background:var(--green-dark);transform:translateY(-1px);box-shadow:0 4px 15px rgba(212,168,67,0.3);color:#000;}

        /* ── HERO ── */
        .hero{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding:80px 0;}
        .hero-bg{position:absolute;inset:0;background:radial-gradient(ellipse at 70% 50%,rgba(212,168,67,0.07) 0%,transparent 60%),radial-gradient(ellipse at 20% 80%,rgba(99,102,241,0.05) 0%,transparent 50%);pointer-events:none;}
        .hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.02) 1px,transparent 1px);background-size:60px 60px;pointer-events:none;mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);}
        .hero-tag{display:inline-flex;align-items:center;gap:8px;background:rgba(212,168,67,0.08);border:1px solid rgba(212,168,67,0.2);border-radius:50px;padding:6px 16px;font-size:0.78rem;color:var(--green);margin-bottom:28px;animation:fadeInDown 0.6s ease;}
        .hero-tag .pulse{width:8px;height:8px;background:var(--green);border-radius:50%;animation:pulse 2s infinite;}
        @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(212,168,67,0.4);}50%{box-shadow:0 0 0 6px transparent;}}
        .hero h1{font-family:'Rajdhani',sans-serif;font-size:clamp(2.8rem,7vw,5.5rem);font-weight:700;line-height:1.05;margin-bottom:24px;animation:fadeInUp 0.7s ease 0.1s both;}
        .hero h1 .line1{display:block;}
        .hero h1 .highlight{color:var(--green);position:relative;}
        .hero h1 .highlight::after{content:'';position:absolute;bottom:-4px;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--green),transparent);border-radius:2px;}
        .hero-desc{font-size:1.05rem;color:#777;line-height:1.8;max-width:520px;margin-bottom:40px;animation:fadeInUp 0.7s ease 0.2s both;}
        .hero-btns{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:56px;animation:fadeInUp 0.7s ease 0.3s both;}
        .btn-hero-primary{background:var(--green);color:#000;font-weight:800;border:none;border-radius:12px;padding:14px 32px;font-size:1rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;}
        .btn-hero-primary:hover{background:var(--green-dark);transform:translateY(-2px);box-shadow:0 8px 30px rgba(212,168,67,0.3);color:#000;}
        .btn-hero-secondary{background:transparent;color:#fff;border:1.5px solid #2a2a3e;border-radius:12px;padding:14px 32px;font-size:1rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.3s;}
        .btn-hero-secondary:hover{border-color:var(--green);color:var(--green);transform:translateY(-2px);}
        .hero-stats{display:flex;gap:0;animation:fadeInUp 0.7s ease 0.4s both;}
        .stat-item{padding:0 32px 0 0;border-right:1px solid #252525;margin-right:32px;}
        .stat-item:last-child{border-right:none;margin-right:0;}
        .stat-num{font-family:'Rajdhani',sans-serif;font-size:2.2rem;font-weight:700;color:var(--green);line-height:1;}
        .stat-label{font-size:0.75rem;color:var(--muted);margin-top:4px;}

        /* HERO VISUAL */
        .hero-visual{position:relative;display:flex;align-items:center;justify-content:center;animation:fadeInRight 0.8s ease 0.3s both;}
        .hero-circle{width:480px;height:480px;border-radius:50%;border:1px solid rgba(212,168,67,0.08);display:flex;align-items:center;justify-content:center;position:relative;animation:rotate 20s linear infinite;}
        .hero-circle::before{content:'';position:absolute;inset:-20px;border-radius:50%;border:1px solid rgba(212,168,67,0.04);}
        .hero-circle-inner{width:360px;height:360px;border-radius:50%;background:radial-gradient(circle,rgba(212,168,67,0.1) 0%,rgba(212,168,67,0.02) 50%,transparent 70%);display:flex;align-items:center;justify-content:center;font-size:10rem;filter:drop-shadow(0 0 40px rgba(212,168,67,0.4));animation:float 4s ease-in-out infinite,counterRotate 20s linear infinite;}
        .orbit-item{position:absolute;width:48px;height:48px;border-radius:12px;background:var(--bg2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.4rem;animation:counterRotate 20s linear infinite;}
        @keyframes rotate{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
        @keyframes counterRotate{from{transform:rotate(0deg);}to{transform:rotate(-360deg);}}
        @keyframes float{0%,100%{transform:translateY(0) rotate(0deg);}50%{transform:translateY(-20px) rotate(0deg);}}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
        @keyframes fadeInDown{from{opacity:0;transform:translateY(-20px);}to{opacity:1;transform:translateY(0);}}
        @keyframes fadeInRight{from{opacity:0;transform:translateX(40px);}to{opacity:1;transform:translateX(0);}}

        /* ── SECCIÓN GENÉRICA ── */
        section{padding:100px 0;}
        .sec-tag{display:inline-block;background:rgba(212,168,67,0.06);border:1px solid rgba(212,168,67,0.15);border-radius:50px;padding:4px 16px;font-size:0.72rem;color:var(--green);text-transform:uppercase;letter-spacing:2px;margin-bottom:16px;}
        .sec-title{font-family:'Rajdhani',sans-serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:700;margin-bottom:12px;}
        .sec-title span{color:var(--green);}
        .sec-sub{color:var(--muted);font-size:0.95rem;line-height:1.7;max-width:480px;}

        /* ── CATEGORÍAS ── */
        .bg2{background:var(--bg2);}
        .cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
        .cat-card{border-radius:20px;overflow:hidden;position:relative;height:180px;cursor:pointer;transition:all 0.4s;text-decoration:none;display:block;}
        .cat-card:hover{transform:translateY(-6px);box-shadow:0 20px 60px rgba(0,0,0,0.5);}
        .cat-card:hover .cat-overlay{opacity:0.5;}
        .cat-card:hover .cat-content{transform:translateY(-4px);}
        .cat-bg{position:absolute;inset:0;background-size:cover;background-position:center;transition:transform 0.4s;}
        .cat-card:hover .cat-bg{transform:scale(1.08);}
        .cat-overlay{position:absolute;inset:0;transition:opacity 0.3s;}
        .cat-content{position:absolute;inset:0;padding:24px;display:flex;flex-direction:column;justify-content:flex-end;position:relative;z-index:2;transition:transform 0.3s;}
        .cat-icon-big{font-size:2.5rem;margin-bottom:8px;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));}
        .cat-name{font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.5);}
        .cat-sub{font-size:0.75rem;color:rgba(255,255,255,0.6);margin-top:2px;}
        .cat-arrow{position:absolute;top:16px;right:16px;width:32px;height:32px;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--green);font-size:0.9rem;opacity:0;transition:opacity 0.3s;}
        .cat-card:hover .cat-arrow{opacity:1;}

        /* ── PRODUCTOS ── */
        .prod-card{background:var(--bg2);border:1px solid var(--border);border-radius:20px;overflow:hidden;transition:all 0.35s;height:100%;}
        .prod-card:hover{border-color:rgba(212,168,67,0.3);transform:translateY(-6px);box-shadow:0 16px 50px rgba(0,0,0,0.5);}
        .prod-img{height:220px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:5rem;position:relative;overflow:hidden;}
        .prod-img img{width:100%;height:100%;object-fit:cover;transition:transform 0.4s;}
        .prod-card:hover .prod-img img{transform:scale(1.06);}
        .prod-badge{position:absolute;top:12px;left:12px;font-size:0.68rem;font-weight:700;padding:4px 10px;border-radius:6px;text-transform:uppercase;}
        .badge-n{background:var(--green);color:#000;}
        .badge-o{background:#ef4444;color:#fff;}
        .badge-p{background:#a855f7;color:#fff;}
        .prod-body{padding:20px;}
        .prod-marca{font-size:0.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;}
        .prod-nombre{font-weight:700;font-size:0.95rem;margin-bottom:6px;line-height:1.4;}
        .prod-desc{font-size:0.78rem;color:#555;line-height:1.5;margin-bottom:14px;}
        .prod-footer{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;}
        .prod-precio{font-family:'Rajdhani',sans-serif;font-size:1.4rem;font-weight:700;color:var(--green);}
        .prod-stock{font-size:0.7rem;color:var(--muted);}
        .btn-prod{background:var(--green);color:#000;font-weight:700;border:none;border-radius:10px;padding:10px;width:100%;font-size:0.875rem;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;}
        .btn-prod:hover{background:var(--green-dark);transform:translateY(-1px);box-shadow:0 4px 15px rgba(212,168,67,0.25);color:#000;}

        /* ── BENEFICIOS ── */
        .ben-card{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:32px;transition:all 0.3s;position:relative;overflow:hidden;}
        .ben-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--green),transparent);transform:scaleX(0);transition:transform 0.3s;transform-origin:left;}
        .ben-card:hover::before{transform:scaleX(1);}
        .ben-card:hover{border-color:rgba(212,168,67,0.2);transform:translateY(-4px);}
        .ben-icon{width:56px;height:56px;background:rgba(212,168,67,0.08);border:1px solid rgba(212,168,67,0.15);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:20px;}
        .ben-title{font-weight:700;font-size:1.1rem;margin-bottom:10px;}
        .ben-desc{color:var(--muted);font-size:0.875rem;line-height:1.7;}

        /* ── COUNTER SECTION ── */
        .counter-section{background:linear-gradient(135deg,rgba(212,168,67,0.05) 0%,transparent 50%),var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:60px 0;}
        .counter-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0;}
        .counter-item{text-align:center;padding:20px;border-right:1px solid var(--border);}
        .counter-item:last-child{border-right:none;}
        .counter-num{font-family:'Rajdhani',sans-serif;font-size:3rem;font-weight:700;color:var(--green);line-height:1;}
        .counter-label{font-size:0.82rem;color:var(--muted);margin-top:6px;}

        /* ── NEWSLETTER ── */
        .newsletter-section{background:linear-gradient(135deg,rgba(212,168,67,0.06),rgba(99,102,241,0.04));border:1px solid rgba(212,168,67,0.12);border-radius:28px;padding:60px 48px;text-align:center;margin:0 auto;max-width:700px;}
        .newsletter-section h2{font-family:'Rajdhani',sans-serif;font-size:2.2rem;font-weight:700;margin-bottom:12px;}
        .newsletter-input-wrap{display:flex;gap:12px;max-width:480px;margin:24px auto 0;}
        .newsletter-input{flex:1;background:rgba(255,255,255,0.04);border:1px solid var(--border);color:#fff;border-radius:12px;padding:14px 20px;font-size:0.95rem;}
        .newsletter-input:focus{outline:none;border-color:var(--green);}
        .newsletter-input::placeholder{color:#333;}
        .btn-newsletter{background:var(--green);color:#000;font-weight:700;border:none;border-radius:12px;padding:14px 24px;font-size:0.95rem;cursor:pointer;transition:all 0.2s;white-space:nowrap;}
        .btn-newsletter:hover{background:var(--green-dark);}

        /* ── FOOTER ── */
        footer{background:var(--bg2);border-top:1px solid var(--border);padding:70px 0 30px;}
        .footer-brand{font-family:'Rajdhani',sans-serif;font-size:1.8rem;font-weight:700;color:var(--green);}
        .footer-brand span{color:#fff;}
        .footer-desc{color:var(--muted);font-size:0.875rem;line-height:1.7;margin-top:12px;max-width:280px;}
        .social-btn{width:38px;height:38px;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:var(--muted);transition:all 0.2s;text-decoration:none;}
        .social-btn:hover{border-color:var(--green);color:var(--green);background:rgba(212,168,67,0.06);}
        .footer-title{font-weight:700;font-size:0.875rem;color:#fff;margin-bottom:18px;}
        .footer-link{display:block;color:var(--muted);font-size:0.875rem;text-decoration:none;margin-bottom:10px;transition:color 0.2s;}
        .footer-link:hover{color:var(--green);}
        .footer-divider{border-color:var(--border);margin:30px 0;}
        .footer-copy{color:#333;font-size:0.82rem;}

        /* SCROLL REVEAL */
        .reveal{opacity:0;transform:translateY(30px);transition:opacity 0.6s ease,transform 0.6s ease;}
        .reveal.visible{opacity:1;transform:translateY(0);}

        @media(max-width:992px){
            .cat-grid{grid-template-columns:repeat(2,1fr);}
            .counter-grid{grid-template-columns:repeat(2,1fr);}
            .hero-visual{display:none;}
        }
        @media(max-width:576px){
            .cat-grid{grid-template-columns:1fr;}
            .newsletter-input-wrap{flex-direction:column;}
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="nav-brand" href="#">Gamer<span>Zone</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <div class="d-flex gap-1 mx-auto">
                <a href="#inicio" class="nav-link-item">Inicio</a>
                <a href="#categorias" class="nav-link-item">Categorías</a>
                <a href="#productos" class="nav-link-item">Productos</a>
                <a href="#nosotros" class="nav-link-item">Nosotros</a>
                <a href="#contacto" class="nav-link-item">Contacto</a>
            </div>
            <div class="d-flex gap-2 mt-2 mt-lg-0">
                <a href="views/auth/login.php" class="btn-nav-login">Iniciar Sesión</a>
                <a href="views/auth/register.php" class="btn-nav-register">Registrarse</a>
            </div>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero" id="inicio">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-tag">
                    <div class="pulse"></div>
                    Envío gratis en pedidos mayores a Bs. 500
                </div>
                <h1>
                    <span class="line1">El equipo para</span>
                    <span class="highlight">dominar</span> el juego
                </h1>
                <p class="hero-desc">Encuentra laptops gamer, monitores de alta frecuencia, periféricos profesionales y consolas de última generación. Todo en un solo lugar, al mejor precio de Bolivia.</p>
                <div class="hero-btns">
                    <a href="views/auth/register.php" class="btn-hero-primary">
                        <i class="bi bi-controller"></i> Explorar Tienda
                    </a>
                    <a href="#categorias" class="btn-hero-secondary">
                        <i class="bi bi-grid"></i> Ver Categorías
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-num">500+</div>
                        <div class="stat-label">Productos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">1.2K+</div>
                        <div class="stat-label">Clientes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">4.9★</div>
                        <div class="stat-label">Valoración</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-num">24/7</div>
                        <div class="stat-label">Soporte</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-visual">
                    <div class="hero-circle">
                        <div class="hero-circle-inner"><i class="bi bi-controller" style="font-size:10rem;color:#d4a843;filter:drop-shadow(0 0 40px rgba(212,168,67,0.4));"></i></div>
                        <div class="orbit-item" style="top:10%;left:50%;transform:translateX(-50%);"><i class="bi bi-laptop" style="color:#d4a843;"></i></div>
                        <div class="orbit-item" style="top:50%;right:-24px;transform:translateY(-50%);"><i class="bi bi-display" style="color:#d4a843;"></i></div>
                        <div class="orbit-item" style="bottom:10%;left:50%;transform:translateX(-50%);"><i class="bi bi-headset" style="color:#d4a843;"></i></div>
                        <div class="orbit-item" style="top:50%;left:-24px;transform:translateY(-50%);"><i class="bi bi-mouse" style="color:#d4a843;"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTADOR -->
<div class="counter-section reveal">
    <div class="container">
        <div class="counter-grid">
            <div class="counter-item">
                <div class="counter-num" data-target="500">0</div>
                <div class="counter-label">Productos disponibles</div>
            </div>
            <div class="counter-item">
                <div class="counter-num" data-target="1200">0</div>
                <div class="counter-label">Clientes satisfechos</div>
            </div>
            <div class="counter-item">
                <div class="counter-num" data-target="98">0</div>
                <div class="counter-label">% de satisfacción</div>
            </div>
            <div class="counter-item">
                <div class="counter-num" data-target="5">0</div>
                <div class="counter-label">Categorías de productos</div>
            </div>
        </div>
    </div>
</div>

<!-- CATEGORÍAS -->
<section id="categorias" class="bg2">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="sec-tag">Explora</span>
            <h2 class="sec-title">Nuestras <span>Categorías</span></h2>
            <p class="sec-sub mx-auto">Encuentra exactamente lo que buscas entre nuestra amplia selección de equipos gaming</p>
        </div>
        <div class="cat-grid reveal">
            <!-- Laptops -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'" style="grid-row:span 2;">
                <div class="cat-bg" style="background:linear-gradient(135deg,#0a0f1e,#1a1040,#0d1f0d);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(212,168,67,0.3),rgba(99,102,241,0.4));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:flex-end;padding:28px;z-index:2;">
                    <div style="font-size:4rem;margin-bottom:12px;filter:drop-shadow(0 4px 12px rgba(0,0,0,0.5));"><i class="bi bi-laptop" style="color:#d4a843;"></i></div>
                    <div style="font-family:'Rajdhani',sans-serif;font-size:1.8rem;font-weight:700;color:#fff;text-shadow:0 2px 8px rgba(0,0,0,0.5);">Laptops Gamer</div>
                    <div style="font-size:0.82rem;color:rgba(255,255,255,0.6);margin-top:4px;">RTX 4090 · i9 · 32GB RAM</div>
                    <div style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;background:rgba(212,168,67,0.15);border:1px solid rgba(212,168,67,0.3);border-radius:8px;padding:6px 14px;width:fit-content;font-size:0.78rem;color:var(--green);">Ver laptops <i class="bi bi-arrow-right"></i></div>
                </div>
            </div>
            <!-- Monitores -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'">
                <div class="cat-bg" style="background:linear-gradient(135deg,#0d1520,#1a2a3a,#0a1520);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(59,130,246,0.4),rgba(99,102,241,0.3));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:20px;z-index:2;gap:12px;">
                    <div style="font-size:2.5rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));"><i class="bi bi-display" style="color:#d4a843;"></i></div>
                    <div>
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;">Monitores</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.5);">144Hz · 4K · IPS</div>
                    </div>
                    <i class="bi bi-arrow-right-circle" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:1.2rem;"></i>
                </div>
            </div>
            <!-- Mouse -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'">
                <div class="cat-bg" style="background:linear-gradient(135deg,#1a0d0d,#2a1020,#111111);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(239,68,68,0.3),rgba(168,85,247,0.3));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:20px;z-index:2;gap:12px;">
                    <div style="font-size:2.5rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));"><i class="bi bi-mouse" style="color:#d4a843;"></i></div>
                    <div>
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;">Mouse Gaming</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.5);">25K DPI · RGB · Wireless</div>
                    </div>
                    <i class="bi bi-arrow-right-circle" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:1.2rem;"></i>
                </div>
            </div>
            <!-- Teclados -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'">
                <div class="cat-bg" style="background:linear-gradient(135deg,#0d1a0d,#1a2a10,#0a1a1a);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(212,168,67,0.3),rgba(245,158,11,0.2));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:20px;z-index:2;gap:12px;">
                    <div style="font-size:2.5rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));"><i class="bi bi-keyboard" style="color:#d4a843;"></i></div>
                    <div>
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;">Teclados</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.5);">Mecánicos · RGB · TKL</div>
                    </div>
                    <i class="bi bi-arrow-right-circle" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:1.2rem;"></i>
                </div>
            </div>
            <!-- Consolas -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'">
                <div class="cat-bg" style="background:linear-gradient(135deg,#10101a,#1a1040,#0d0d20);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(168,85,247,0.4),rgba(59,130,246,0.3));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:20px;z-index:2;gap:12px;">
                    <div style="font-size:2.5rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));"><i class="bi bi-controller" style="color:#d4a843;"></i></div>
                    <div>
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;">Consolas</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.5);">PS5 · Xbox · Switch</div>
                    </div>
                    <i class="bi bi-arrow-right-circle" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:1.2rem;"></i>
                </div>
            </div>
            <!-- Accesorios -->
            <div class="cat-card" onclick="window.location='views/auth/login.php'">
                <div class="cat-bg" style="background:linear-gradient(135deg,#1a1200,#2a2010,#0a0a0a);"></div>
                <div class="cat-overlay" style="background:linear-gradient(135deg,rgba(245,158,11,0.3),rgba(239,68,68,0.2));opacity:0.7;"></div>
                <div style="position:absolute;inset:0;display:flex;align-items:flex-end;padding:20px;z-index:2;gap:12px;">
                    <div style="font-size:2.5rem;filter:drop-shadow(0 2px 8px rgba(0,0,0,0.5));"><i class="bi bi-headset" style="color:#d4a843;"></i></div>
                    <div>
                        <div style="font-family:'Rajdhani',sans-serif;font-size:1.3rem;font-weight:700;color:#fff;">Accesorios</div>
                        <div style="font-size:0.72rem;color:rgba(255,255,255,0.5);">Headsets · Sillas · Pads</div>
                    </div>
                    <i class="bi bi-arrow-right-circle" style="margin-left:auto;color:rgba(255,255,255,0.4);font-size:1.2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section id="productos">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3 reveal">
            <div>
                <span class="sec-tag">Destacados</span>
                <h2 class="sec-title mb-0">Productos <span>Populares</span></h2>
            </div>
            <a href="views/auth/login.php" style="display:flex;align-items:center;gap:6px;color:var(--green);text-decoration:none;font-size:0.875rem;border:1px solid rgba(212,168,67,0.2);padding:10px 20px;border-radius:10px;transition:all 0.2s;" onmouseover="this.style.background='rgba(212,168,67,0.06)'" onmouseout="this.style.background='transparent'">
                Ver todos <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="row g-4 reveal">
            <?php
            $cat_icons = ['Laptops'=>'bi-laptop','Monitores'=>'bi-display','Mouse'=>'bi-mouse','Teclados'=>'bi-keyboard','Consolas'=>'bi-controller','Auriculares'=>'bi-headset','Accesorios'=>'bi-headset'];
            $badges = ['badge-n','badge-o','badge-p','badge-n'];
            $badge_labels = ['Nuevo','Oferta','Popular','Nuevo'];
            $i = 0;
            if ($prod_destacados->num_rows === 0):
            ?>
            <div class="col-12 text-center py-5" style="color:#555;">
                <i class="bi bi-box-seam" style="font-size:3rem;"></i>
                <p class="mt-3">Aún no hay productos en el catálogo. <a href="views/auth/login.php" style="color:#d4a843;">Inicia sesión</a> para explorar.</p>
            </div>
            <?php else: while ($p = $prod_destacados->fetch_assoc()):
                $cat = $p['nombre_categoria'] ?? '';
                $ico = 'bi-controller';
                foreach ($cat_icons as $k => $v) { if (stripos($cat, $k) !== false) { $ico = $v; break; } }
                $img_src = !empty($p['imagen']) ? ((strpos($p['imagen'],'http')===0) ? $p['imagen'] : 'assets/'.$p['imagen']) : null;
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="prod-card">
                    <div class="prod-img" style="background:linear-gradient(135deg,#0d1520,#1a2030);">
                        <span class="prod-badge <?= $badges[$i % 4] ?>"><?= $badge_labels[$i % 4] ?></span>
                        <?php if ($img_src): ?>
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
                        <?php else: ?>
                            <i class="bi <?= $ico ?>" style="font-size:5rem;color:#d4a843;opacity:0.7;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="prod-body">
                        <div class="prod-marca"><?= htmlspecialchars($p['marca']) ?></div>
                        <div class="prod-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                        <div class="prod-desc"><?= htmlspecialchars(mb_strimwidth($p['descripcion'] ?? '', 0, 60, '…')) ?></div>
                        <div class="prod-footer">
                            <span class="prod-precio">Bs. <?= number_format($p['precio'], 2) ?></span>
                            <?php if ($p['stock'] > 0): ?>
                                <span class="prod-stock"><i class="bi bi-check-circle text-success me-1"></i>En stock</span>
                            <?php else: ?>
                                <span class="prod-stock" style="color:#ef4444;"><i class="bi bi-x-circle me-1"></i>Agotado</span>
                            <?php endif; ?>
                        </div>
                        <a href="views/auth/login.php" class="btn-prod"><i class="bi bi-cart-plus"></i>Agregar</a>
                    </div>
                </div>
            </div>
            <?php $i++; endwhile; endif; ?>
        </div>
    </div>
</section>

<!-- BENEFICIOS -->
<section class="bg2" id="nosotros">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <span class="sec-tag">¿Por qué nosotros?</span>
            <h2 class="sec-title">Ventajas de comprar en <span>GamerZone</span></h2>
        </div>
        <div class="row g-4 reveal">
            <div class="col-md-6 col-lg-3">
                <div class="ben-card">
                    <div class="ben-icon"><i class="bi bi-truck" style="color:var(--green)"></i></div>
                    <div class="ben-title">Envío Rápido</div>
                    <div class="ben-desc">Entrega en 24-48h a todo Bolivia. Seguimiento en tiempo real de tu pedido hasta tu puerta.</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ben-card">
                    <div class="ben-icon"><i class="bi bi-shield-check" style="color:var(--green)"></i></div>
                    <div class="ben-title">Garantía Total</div>
                    <div class="ben-desc">12 meses de garantía en todos nuestros productos. Soporte técnico especializado incluido.</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ben-card">
                    <div class="ben-icon"><i class="bi bi-headset" style="color:var(--green)"></i></div>
                    <div class="ben-title">Soporte 24/7</div>
                    <div class="ben-desc">Nuestro equipo de expertos gamer está disponible las 24 horas para resolver tus dudas.</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="ben-card">
                    <div class="ben-icon"><i class="bi bi-lock" style="color:var(--green)"></i></div>
                    <div class="ben-title">Pago Seguro</div>
                    <div class="ben-desc">Transacciones 100% seguras con encriptación SSL. Múltiples métodos de pago disponibles.</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER -->
<section>
    <div class="container">
        <div class="newsletter-section reveal">
            <span class="sec-tag">Newsletter</span>
            <h2 class="sec-title">¿No quieres perderte las <span>mejores ofertas</span>?</h2>
            <p style="color:var(--muted);font-size:0.95rem;margin-top:8px;">Suscríbete y recibe descuentos exclusivos, lanzamientos y preventas antes que nadie.</p>
            <div class="newsletter-input-wrap">
                <input type="email" class="newsletter-input" placeholder="tu@correo.com">
                <a href="views/auth/register.php" class="btn-newsletter">Suscribirme</a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer id="contacto">
    <div class="container">
        <div class="row g-5 reveal">
            <div class="col-lg-4">
                <div class="footer-brand">Gamer<span>Zone</span></div>
                <p class="footer-desc">Tu tienda gamer de confianza en Bolivia. Los mejores equipos para los jugadores más exigentes.</p>
                <div class="d-flex gap-2 mt-4">
                    <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-whatsapp"></i></a>
                    <a href="#" class="social-btn"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-title">Navegación</div>
                <a href="#inicio" class="footer-link">Inicio</a>
                <a href="#categorias" class="footer-link">Categorías</a>
                <a href="#productos" class="footer-link">Productos</a>
                <a href="#nosotros" class="footer-link">Nosotros</a>
                <a href="#contacto" class="footer-link">Contacto</a>
            </div>
            <div class="col-6 col-lg-2">
                <div class="footer-title">Mi Cuenta</div>
                <a href="views/auth/login.php" class="footer-link">Iniciar Sesión</a>
                <a href="views/auth/register.php" class="footer-link">Registrarse</a>
                <a href="views/auth/login.php" class="footer-link">Mis Pedidos</a>
                <a href="views/auth/login.php" class="footer-link">Favoritos</a>
                <a href="views/auth/login.php" class="footer-link">Carrito</a>
            </div>
            <div class="col-lg-4">
                <div class="footer-title">Contacto</div>
                <p class="footer-link"><i class="bi bi-envelope me-2" style="color:var(--green)"></i>info@gamerzone.bo</p>
                <p class="footer-link"><i class="bi bi-telephone me-2" style="color:var(--green)"></i>+591 70 000 000</p>
                <p class="footer-link"><i class="bi bi-geo-alt me-2" style="color:var(--green)"></i>Cochabamba, Bolivia</p>
                <p class="footer-link"><i class="bi bi-clock me-2" style="color:var(--green)"></i>Lun - Sáb: 9:00 - 20:00</p>
                <p class="footer-link"><i class="bi bi-chat-dots me-2" style="color:var(--green)"></i>Soporte 24/7 online</p>
            </div>
        </div>
        <hr class="footer-divider">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <p class="footer-copy mb-0">© 2025 GamerZone Bolivia. Todos los derechos reservados.</p>
            <div class="d-flex gap-3">
                <a href="#" class="footer-link mb-0" style="font-size:0.78rem;">Política de Privacidad</a>
                <a href="#" class="footer-link mb-0" style="font-size:0.78rem;">Términos y Condiciones</a>
                <a href="#" class="footer-link mb-0" style="font-size:0.78rem;">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
        if(entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), i * 100);
        }
    });
}, {threshold: 0.1});
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// Counter animation
function animateCounter(el) {
    const target = parseInt(el.dataset.target);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
        current += step;
        if(current >= target) {
            current = target;
            clearInterval(timer);
        }
        el.textContent = Math.floor(current) + (target >= 100 ? '+' : target < 10 ? '' : '%');
    }, 16);
}

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if(entry.isIntersecting) {
            entry.target.querySelectorAll('[data-target]').forEach(animateCounter);
            counterObserver.unobserve(entry.target);
        }
    });
}, {threshold: 0.5});
document.querySelectorAll('.counter-section').forEach(el => counterObserver.observe(el));

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if(window.scrollY > 50) {
        navbar.style.borderBottomColor = 'rgba(212,168,67,0.15)';
    } else {
        navbar.style.borderBottomColor = 'var(--border)';
    }
});
</script>
</body>
</html>