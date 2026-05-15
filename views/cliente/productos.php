<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

if (isset($_POST['agregar_carrito'])) {
    $id_producto = intval($_POST['id_producto']);
    // Verificar stock real
    $chk = $conn->prepare("SELECT stock, nombre FROM producto WHERE id_producto=? AND estado=1");
    $chk->bind_param("i", $id_producto);
    $chk->execute();
    $prod_chk = $chk->get_result()->fetch_assoc();

    if ($prod_chk) {
        if (!isset($_SESSION['carrito']))
            $_SESSION['carrito'] = [];
        $en_carrito = $_SESSION['carrito'][$id_producto] ?? 0;

        if ($en_carrito >= $prod_chk['stock']) {
            $_SESSION['msg_carrito'] = "Solo hay {$prod_chk['stock']} unidades disponibles de {$prod_chk['nombre']}.";
            $_SESSION['msg_tipo'] = 'err';
        } else {
            $_SESSION['carrito'][$id_producto] = $en_carrito + 1;
            $_SESSION['msg_carrito'] = "{$prod_chk['nombre']} agregado al carrito.";
            $_SESSION['msg_tipo'] = 'ok';
        }
    }
}

if (isset($_POST['toggle_favorito'])) {
    $id_producto = intval($_POST['id_producto']);
    $id_usuario = $_SESSION['usuario_id'];
    $check = $conn->prepare("SELECT id_favorito FROM favorito WHERE id_usuario=? AND id_producto=?");
    $check->bind_param("ii", $id_usuario, $id_producto);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM favorito WHERE id_usuario=? AND id_producto=?");
        $del->bind_param("ii", $id_usuario, $id_producto);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO favorito (id_usuario, id_producto) VALUES (?,?)");
        $ins->bind_param("ii", $id_usuario, $id_producto);
        $ins->execute();
    }
}

$buscar = trim($_GET['buscar'] ?? '');
$id_cat = intval($_GET['categoria'] ?? 0);
$marca_fil = trim($_GET['marca'] ?? '');
$orden = $_GET['orden'] ?? 'reciente';

$where = "WHERE p.estado = 1";
$params = [];
$types = '';

if ($buscar) {
    $where .= " AND (p.nombre LIKE ? OR p.marca LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
    $types .= 'sss';
}
if ($id_cat) {
    $where .= " AND p.id_categoria = ?";
    $params[] = $id_cat;
    $types .= 'i';
}
if ($marca_fil) {
    $where .= " AND p.marca = ?";
    $params[] = $marca_fil;
    $types .= 's';
}

$order_sql = match ($orden) {
    'precio_asc' => 'p.precio ASC',
    'precio_desc' => 'p.precio DESC',
    'nombre' => 'p.nombre ASC',
    default => 'p.id_producto DESC'
};

$sql = "SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria $where ORDER BY $order_sql";
$stmt = $conn->prepare($sql);
if ($params)
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$productos = $stmt->get_result();

$categorias = $conn->query("SELECT * FROM categoria ORDER BY nombre_categoria");
$marcas = $conn->query("SELECT DISTINCT marca FROM producto WHERE estado=1 AND marca != '' ORDER BY marca");

$favs = [];
$fav_res = $conn->prepare("SELECT id_producto FROM favorito WHERE id_usuario=?");
$fav_res->bind_param("i", $_SESSION['usuario_id']);
$fav_res->execute();
$fav_data = $fav_res->get_result();
while ($f = $fav_data->fetch_assoc())
    $favs[] = $f['id_producto'];

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
$cant_favoritos = count($favs);
$total_prods = $productos->num_rows;

function imgSrc($img, $prefix = '../../assets/')
{
    if (!$img)
        return null;
    return (strpos($img, 'http') === 0) ? $img : $prefix . $img;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - GamerZone</title>
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
            background: #080808;
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: #252525;
            border-radius: 3px;
        }

        .navbar {
            background: rgba(13, 13, 26, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #252525;
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: #d4a843;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .nav-brand span {
            color: #fff;
        }

        .nav-icon-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            border: 1px solid transparent;
            position: relative;
        }

        .nav-icon-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-icon-btn i {
            font-size: 1.1rem;
        }

        .nav-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #d4a843;
            color: #000;
            font-size: 0.6rem;
            font-weight: 800;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-badge.fav {
            background: #ff4466;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(212, 168, 67, 0.06);
            border: 1px solid rgba(212, 168, 67, 0.15);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            text-decoration: none;
            color: #fff;
            transition: all 0.2s;
        }

        .user-chip:hover {
            background: rgba(212, 168, 67, 0.1);
            color: #d4a843;
        }

        .user-chip .dot {
            width: 8px;
            height: 8px;
            background: #d4a843;
            border-radius: 50%;
        }

        .btn-logout-sm {
            background: rgba(255, 68, 68, 0.08);
            border: 1px solid rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout-sm:hover {
            background: rgba(255, 68, 68, 0.15);
        }

        .hero-bar {
            background: linear-gradient(135deg, #0a1225, #080808);
            border-bottom: 1px solid #252525;
            padding: 32px 0;
        }

        .hero-bar h1 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .hero-bar h1 span {
            color: #d4a843;
        }

        .hero-bar p {
            color: #555;
            font-size: 0.9rem;
        }

        .filter-bar {
            background: #111111;
            border-bottom: 1px solid #252525;
            padding: 16px 0;
            position: sticky;
            top: 65px;
            z-index: 100;
        }

        .search-input {
            background: #181818;
            border: 1px solid #252525;
            color: #fff;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.875rem;
            width: 100%;
        }

        .search-input:focus {
            outline: none;
            border-color: #d4a843;
            box-shadow: 0 0 0 3px rgba(212, 168, 67, 0.08);
        }

        .search-input::placeholder {
            color: #333;
        }

        .filter-select {
            background: #181818;
            border: 1px solid #252525;
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #d4a843;
        }

        .filter-select option {
            background: #181818;
        }

        .results-count {
            font-size: 0.82rem;
            color: #555;
        }

        .results-count strong {
            color: #d4a843;
        }

        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }

        .toast-msg {
            background: #0d1f0d;
            border: 1px solid rgba(212, 168, 67, 0.3);
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.3s ease;
        }

        .toast-msg i {
            color: #d4a843;
            font-size: 1.1rem;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .content {
            padding: 32px 0;
        }

        .prod-card {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 18px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .prod-card:hover {
            border-color: rgba(212, 168, 67, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .prod-img {
            position: relative;
            height: 220px;
            background: linear-gradient(135deg, #181818, #0a0a14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            overflow: hidden;
        }

        .prod-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .prod-card:hover .prod-img img {
            transform: scale(1.05);
        }

        .prod-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-nuevo {
            background: #d4a843;
            color: #000;
        }

        .badge-oferta {
            background: #ef4444;
            color: #fff;
        }

        .badge-popular {
            background: #a855f7;
            color: #fff;
        }

        .fav-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
            font-size: 1rem;
        }

        .fav-btn:hover {
            border-color: #ff4466;
            color: #ff4466;
        }

        .fav-btn.active {
            background: rgba(255, 68, 102, 0.15);
            border-color: #ff4466;
            color: #ff4466;
        }

        .prod-body {
            padding: 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .prod-marca {
            font-size: 0.72rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .prod-nombre {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .prod-desc {
            font-size: 0.78rem;
            color: #555;
            line-height: 1.5;
            margin-bottom: 12px;
            flex: 1;
        }

        .prod-cat {
            display: inline-block;
            background: rgba(212, 168, 67, 0.06);
            border: 1px solid rgba(212, 168, 67, 0.12);
            color: #d4a843;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .prod-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .prod-precio {
            font-size: 1.3rem;
            font-weight: 800;
            color: #d4a843;
        }

        .stock-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .stock-ok {
            background: rgba(212, 168, 67, 0.08);
            color: #d4a843;
            border: 1px solid rgba(212, 168, 67, 0.2);
        }

        .stock-low {
            background: rgba(245, 158, 11, 0.08);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .stock-out {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-add {
            background: #d4a843;
            color: #000;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            font-size: 0.875rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #c89a30;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(212, 168, 67, 0.25);
        }

        .btn-add:disabled {
            background: #252525;
            color: #444;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #444;
        }

        .empty-state i {
            font-size: 4rem;
            display: block;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h4 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #666;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
                <div class="d-flex align-items-center gap-2">
                    <a href="favoritos.php" class="nav-icon-btn">
                        <i class="bi bi-heart"></i>
                        <span class="d-none d-md-inline">Favoritos</span>
                        <?php if ($cant_favoritos > 0): ?>
                            <span class="nav-badge fav"><?= $cant_favoritos ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="carrito.php" class="nav-icon-btn">
                        <i class="bi bi-cart3"></i>
                        <span class="d-none d-md-inline">Carrito</span>
                        <?php if ($cant_carrito > 0): ?>
                            <span class="nav-badge"><?= $cant_carrito ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="historial.php" class="nav-icon-btn">
                        <i class="bi bi-bag-check"></i>
                        <span class="d-none d-md-inline">Mis Pedidos</span>
                    </a>
                    <a href="perfil.php" class="user-chip d-none d-lg-flex">
                        <div class="dot"></div>
                        <span><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    </a>
                    <form action="../../controllers/auth_controller.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button class="btn-logout-sm"><i class="bi bi-box-arrow-right me-1"></i>Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-bar">
        <div class="container">
            <h1>Catálogo de <span>Productos</span></h1>
            <p>Encuentra el equipo gamer perfecto para ti</p>
        </div>
    </div>

    <div class="filter-bar">
        <div class="container">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="position-relative">
                        <i class="bi bi-search"
                            style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#444;font-size:0.9rem;"></i>
                        <input type="text" name="buscar" class="search-input" style="padding-left:40px;"
                            placeholder="Buscar producto, marca o descripción..."
                            value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="categoria" class="filter-select w-100" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php while ($c = $categorias->fetch_assoc()): ?>
                            <option value="<?= $c['id_categoria'] ?>" <?= $id_cat == $c['id_categoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre_categoria']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="marca" class="filter-select w-100" onchange="this.form.submit()">
                        <option value="">Todas las marcas</option>
                        <?php while ($m = $marcas->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($m['marca']) ?>" <?= $marca_fil === $m['marca'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($m['marca']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="orden" class="filter-select w-100" onchange="this.form.submit()">
                        <option value="reciente" <?= $orden == 'reciente' ? 'selected' : '' ?>>Más recientes</option>
                        <option value="precio_asc" <?= $orden == 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
                        <option value="precio_desc" <?= $orden == 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                        <option value="nombre" <?= $orden == 'nombre' ? 'selected' : '' ?>>Nombre A-Z</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center gap-2">
                    <button type="submit" class="btn-add" style="padding:10px 16px;width:auto;border-radius:8px;">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($buscar || $id_cat || $marca_fil): ?>
                        <a href="productos.php"
                            style="background:rgba(255,255,255,0.05);border:1px solid #252525;color:#aaa;border-radius:8px;padding:10px 14px;font-size:0.82rem;text-decoration:none;white-space:nowrap;">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            <div class="mt-2 results-count">
                Mostrando <strong><?= $total_prods ?></strong> producto<?= $total_prods != 1 ? 's' : '' ?>
                <?php if ($buscar): ?> para "<strong style="color:#fff"><?= htmlspecialchars($buscar) ?></strong>"<?php endif; ?>
                <?php if ($id_cat): ?> en esta categoría<?php endif; ?>
                <?php if ($marca_fil): ?> · marca <strong style="color:#d4a843"><?= htmlspecialchars($marca_fil) ?></strong><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['msg_carrito'])): ?>
        <div class="toast-container">
            <div class="toast-msg" id="toastMsg">
                <i class="bi bi-cart-check-fill"></i>
                <span><?= $_SESSION['msg_carrito'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['msg_carrito']); ?>
    <?php endif; ?>

    <div class="content">
        <div class="container">
            <?php if ($total_prods === 0): ?>
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h4>No se encontraron productos</h4>
                    <p>Intenta con otros términos de búsqueda o explora todas las categorías</p>
                    <a href="productos.php"
                        style="display:inline-block;margin-top:16px;background:#d4a843;color:#000;font-weight:700;border-radius:10px;padding:10px 24px;text-decoration:none;">Ver
                        todos</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php
                    $badges = ['nuevo', 'oferta', 'popular'];
                    $bi = 0;
                    while ($p = $productos->fetch_assoc()):
                        $is_fav = in_array($p['id_producto'], $favs);
                        $img_src = imgSrc($p['imagen']);
                        ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="prod-card" style="cursor:pointer;"
                                onclick="window.location='producto_detalle.php?id=<?= $p['id_producto'] ?>'">
                                <div class="prod-img">
                                    <?php if ($img_src): ?>
                                        <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                                    <?php else:
                                        $cat_n = strtolower($p['nombre_categoria'] ?? '');
                                        $ph_icon = 'bi-box-seam';
                                        $ph_bg = 'linear-gradient(135deg,#0d1520,#1a2030)';
                                        if (str_contains($cat_n,'laptop')||str_contains($cat_n,'portatil')) { $ph_icon='bi-laptop'; $ph_bg='linear-gradient(135deg,#0a0f1e,#1a1040)'; }
                                        elseif (str_contains($cat_n,'monitor')) { $ph_icon='bi-display'; $ph_bg='linear-gradient(135deg,#0d1520,#1a2a3a)'; }
                                        elseif (str_contains($cat_n,'mouse')||str_contains($cat_n,'raton')) { $ph_icon='bi-mouse'; $ph_bg='linear-gradient(135deg,#1a0d0d,#2a1020)'; }
                                        elseif (str_contains($cat_n,'teclado')) { $ph_icon='bi-keyboard'; $ph_bg='linear-gradient(135deg,#0d1a0d,#1a2a10)'; }
                                        elseif (str_contains($cat_n,'consola')) { $ph_icon='bi-controller'; $ph_bg='linear-gradient(135deg,#10101a,#1a1040)'; }
                                        elseif (str_contains($cat_n,'headset')||str_contains($cat_n,'auricular')||str_contains($cat_n,'accesorio')) { $ph_icon='bi-headset'; $ph_bg='linear-gradient(135deg,#1a1200,#2a2010)'; }
                                    ?>
                                    <div style="position:absolute;inset:0;background:<?= $ph_bg ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;">
                                        <i class="bi <?= $ph_icon ?>" style="font-size:4rem;color:#d4a843;opacity:0.6;"></i>
                                        <span style="font-size:0.65rem;color:rgba(212,168,67,0.4);text-transform:uppercase;letter-spacing:2px;"><?= htmlspecialchars($p['nombre_categoria'] ?? '') ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <span
                                        class="prod-badge badge-<?= $badges[$bi % 3] ?>"><?= ucfirst($badges[$bi % 3]) ?></span>
                                    <form method="POST" style="position:absolute;top:12px;right:12px;"
                                        onclick="event.stopPropagation()">
                                        <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                        <button type="submit" name="toggle_favorito"
                                            class="fav-btn <?= $is_fav ? 'active' : '' ?>"
                                            title="<?= $is_fav ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
                                            <i class="bi bi-heart<?= $is_fav ? '-fill' : '' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="prod-body">
                                    <div class="prod-marca"><?= htmlspecialchars($p['marca']) ?></div>
                                    <div class="prod-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                                    <div class="prod-desc">
                                        <?= htmlspecialchars(substr($p['descripcion'] ?? '', 0, 90)) ?>        <?= strlen($p['descripcion'] ?? '') > 90 ? '...' : '' ?>
                                    </div>
                                    <span class="prod-cat"><?= htmlspecialchars($p['nombre_categoria']) ?></span>
                                    <div class="prod-footer">
                                        <span class="prod-precio">Bs. <?= number_format($p['precio'], 2) ?></span>
                                        <?php if ($p['stock'] > 10): ?>
                                            <span class="stock-badge stock-ok"><i
                                                    class="bi bi-check-circle me-1"></i><?= $p['stock'] ?> und.</span>
                                        <?php elseif ($p['stock'] > 0): ?>
                                            <span class="stock-badge stock-low"><i class="bi bi-exclamation-circle me-1"></i>Últimas
                                                <?= $p['stock'] ?></span>
                                        <?php else: ?>
                                            <span class="stock-badge stock-out">Agotado</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($p['stock'] > 0): ?>
                                        <form method="POST" onclick="event.stopPropagation()">
                                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                            <button type="submit" name="agregar_carrito" class="btn-add">
                                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn-add" disabled><i class="bi bi-x-circle me-1"></i>Sin stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php $bi++; endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toast = document.getElementById('toastMsg');
        if (toast) setTimeout(() => toast.style.opacity = '0', 3000);
    </script>
</body>

</html>