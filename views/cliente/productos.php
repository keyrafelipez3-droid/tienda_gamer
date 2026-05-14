<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

// Agregar al carrito
if(isset($_POST['agregar_carrito'])) {
    $id_producto = $_POST['id_producto'];
    if(!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
    if(isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]++;
    } else {
        $_SESSION['carrito'][$id_producto] = 1;
    }
}

// Agregar/quitar favorito
if(isset($_POST['toggle_favorito'])) {
    $id_producto = $_POST['id_producto'];
    $id_usuario = $_SESSION['usuario_id'];
    $check = $conn->prepare("SELECT id_favorito FROM favorito WHERE id_usuario=? AND id_producto=?");
    $check->bind_param("ii", $id_usuario, $id_producto);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM favorito WHERE id_usuario=? AND id_producto=?");
        $del->bind_param("ii", $id_usuario, $id_producto);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO favorito (id_usuario, id_producto) VALUES (?,?)");
        $ins->bind_param("ii", $id_usuario, $id_producto);
        $ins->execute();
    }
}

// Filtros
$buscar = $_GET['buscar'] ?? '';
$id_cat = $_GET['categoria'] ?? '';
$where = "WHERE p.estado = 1";
$params = [];
$types = '';
if($buscar) { $where .= " AND (p.nombre LIKE ? OR p.marca LIKE ?)"; $params[] = "%$buscar%"; $params[] = "%$buscar%"; $types .= 'ss'; }
if($id_cat) { $where .= " AND p.id_categoria = ?"; $params[] = $id_cat; $types .= 'i'; }

$sql = "SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria $where ORDER BY p.id_producto DESC";
$stmt = $conn->prepare($sql);
if($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$productos = $stmt->get_result();

$categorias = $conn->query("SELECT * FROM categoria");

// Favoritos del usuario
$favs = [];
$fav_res = $conn->prepare("SELECT id_producto FROM favorito WHERE id_usuario=?");
$fav_res->bind_param("i", $_SESSION['usuario_id']);
$fav_res->execute();
$fav_data = $fav_res->get_result();
while($f = $fav_data->fetch_assoc()) $favs[] = $f['id_producto'];

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; font-size: 1.5rem; }
        .navbar-brand span { color: #fff; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover { color: #00ff88 !important; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .form-control, .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: none; }
        .form-select option { background: #1a1a1a; }
        .producto-card { background: #111; border: 1px solid #222; border-radius: 16px; overflow: hidden; transition: all 0.3s; height: 100%; }
        .producto-card:hover { border-color: #00ff88; transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,255,136,0.1); }
        .producto-img { background: linear-gradient(135deg, #1a1a1a, #0d1f0d); height: 200px; display: flex; align-items: center; justify-content: center; font-size: 4rem; position: relative; overflow: hidden; }
        .producto-img img { width: 100%; height: 100%; object-fit: cover; }
        .card-body { padding: 16px; }
        .precio { color: #00ff88; font-size: 1.3rem; font-weight: 800; }
        .marca { color: #777; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .nombre { color: #fff; font-weight: 700; font-size: 0.95rem; margin: 4px 0; }
        .descripcion { color: #888; font-size: 0.82rem; line-height: 1.4; }
        .stock-badge { font-size: 0.75rem; padding: 2px 8px; border-radius: 6px; }
        .stock-ok { background: #0d2a0d; color: #00ff88; border: 1px solid #00ff88; }
        .stock-low { background: #2a1a0d; color: #ffa500; border: 1px solid #ffa500; }
        .stock-out { background: #2a0d0d; color: #ff4444; border: 1px solid #ff4444; }
        .btn-fav { background: transparent; border: 1px solid #333; color: #777; border-radius: 8px; padding: 6px 10px; transition: all 0.3s; }
        .btn-fav.active { border-color: #ff4444; color: #ff4444; background: #2a0d0d; }
        .btn-fav:hover { border-color: #ff4444; color: #ff4444; }
        .carrito-badge { background: #00ff88; color: #000; border-radius: 50%; width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; position: absolute; top: -5px; right: -5px; }
        .cat-btn { background: #111; border: 1px solid #333; color: #ccc; border-radius: 8px; padding: 6px 14px; font-size: 0.85rem; transition: all 0.3s; text-decoration: none; }
        .cat-btn:hover, .cat-btn.active { background: #0d1f0d; border-color: #00ff88; color: #00ff88; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="inicio.php">Gamer<span>Zone</span></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="favoritos.php" class="nav-link"><i class="bi bi-heart"></i> Favoritos</a>
            <a href="carrito.php" class="nav-link position-relative">
                <i class="bi bi-cart3"></i> Carrito
                <?php if($cant_carrito > 0): ?>
                <span class="carrito-badge"><?= $cant_carrito ?></span>
                <?php endif; ?>
            </a>
            <span class="text-muted">Hola, <strong class="text-white"><?= $_SESSION['usuario_nombre'] ?></strong></span>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <!-- Buscador y filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar producto o marca..." value="<?= htmlspecialchars($buscar) ?>">
                <button class="btn btn-gamer" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
        <div class="col-md-4">
            <select name="categoria" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                <?php while($c = $categorias->fetch_assoc()): ?>
                <option value="<?= $c['id_categoria'] ?>" <?= $id_cat == $c['id_categoria'] ? 'selected' : '' ?>>
                    <?= $c['nombre_categoria'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <a href="productos.php" class="btn btn-outline-secondary w-100">Limpiar</a>
        </div>
    </form>

    <!-- Grid de productos -->
    <?php if($productos->num_rows === 0): ?>
    <div class="text-center py-5">
        <div style="font-size:4rem;">🔍</div>
        <h4 class="text-muted mt-3">No se encontraron productos</h4>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php while($p = $productos->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-3">
            <div class="producto-card">
                <div class="producto-img">
                    <?php if($p['imagen']): ?>
                        <img src="../../assets/<?= $p['imagen'] ?>" alt="<?= $p['nombre'] ?>">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="marca mb-1"><?= htmlspecialchars($p['marca']) ?></p>
                    <p class="nombre"><?= htmlspecialchars($p['nombre']) ?></p>
                    <p class="descripcion mb-2"><?= htmlspecialchars(substr($p['descripcion'], 0, 80)) ?>...</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="precio">Bs. <?= number_format($p['precio'], 2) ?></span>
                        <?php if($p['stock'] > 10): ?>
                            <span class="stock-badge stock-ok"><?= $p['stock'] ?> und.</span>
                        <?php elseif($p['stock'] > 0): ?>
                            <span class="stock-badge stock-low">Últimas <?= $p['stock'] ?></span>
                        <?php else: ?>
                            <span class="stock-badge stock-out">Agotado</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <?php if($p['stock'] > 0): ?>
                        <form method="POST" class="flex-grow-1">
                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                            <button type="submit" name="agregar_carrito" class="btn btn-gamer w-100 btn-sm">
                                <i class="bi bi-cart-plus"></i> Agregar
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-secondary w-100 btn-sm" disabled>Agotado</button>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                            <button type="submit" name="toggle_favorito" class="btn-fav <?= in_array($p['id_producto'], $favs) ? 'active' : '' ?>">
                                <i class="bi bi-heart<?= in_array($p['id_producto'], $favs) ? '-fill' : '' ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>