<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

// Eliminar favorito
if(isset($_POST['eliminar_favorito'])) {
    $id_producto = $_POST['id_producto'];
    $id_usuario = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("DELETE FROM favorito WHERE id_usuario=? AND id_producto=?");
    $stmt->bind_param("ii", $id_usuario, $id_producto);
    $stmt->execute();
    header('Location: favoritos.php'); exit;
}

// Agregar al carrito desde favoritos
if(isset($_POST['agregar_carrito'])) {
    $id_producto = $_POST['id_producto'];
    if(!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
    if(isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]++;
    } else {
        $_SESSION['carrito'][$id_producto] = 1;
    }
}

$id_usuario = $_SESSION['usuario_id'];
$favoritos = $conn->prepare("SELECT p.*, c.nombre_categoria FROM favorito f 
    JOIN producto p ON f.id_producto = p.id_producto 
    JOIN categoria c ON p.id_categoria = c.id_categoria
    WHERE f.id_usuario = ? ORDER BY f.fecha DESC");
$favoritos->bind_param("i", $id_usuario);
$favoritos->execute();
$favoritos = $favoritos->get_result();

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favoritos - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .producto-card { background: #111; border: 1px solid #222; border-radius: 16px; overflow: hidden; transition: all 0.3s; }
        .producto-card:hover { border-color: #ff4444; transform: translateY(-5px); box-shadow: 0 10px 30px rgba(255,68,68,0.1); }
        .producto-img { background: linear-gradient(135deg, #1a1a1a, #1a0d0d); height: 180px; display: flex; align-items: center; justify-content: center; font-size: 4rem; overflow: hidden; }
        .producto-img img { width: 100%; height: 100%; object-fit: cover; }
        .precio { color: #00ff88; font-size: 1.2rem; font-weight: 800; }
        .marca { color: #777; font-size: 0.8rem; text-transform: uppercase; }
        .carrito-badge { background: #00ff88; color: #000; border-radius: 50%; width: 20px; height: 20px; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; position: absolute; top: -5px; right: -5px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="productos.php">Gamer<span>Zone</span></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="productos.php" class="btn btn-outline-secondary btn-sm">← Productos</a>
            <a href="carrito.php" class="btn btn-outline-secondary btn-sm position-relative">
                <i class="bi bi-cart3"></i> Carrito
                <?php if($cant_carrito > 0): ?>
                <span class="carrito-badge"><?= $cant_carrito ?></span>
                <?php endif; ?>
            </a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="fw-bold mb-4">❤️ Mis <span style="color:#ff4444">Favoritos</span></h3>

    <?php if($favoritos->num_rows === 0): ?>
    <div class="text-center py-5">
        <div style="font-size:5rem;">💔</div>
        <h4 class="text-muted mt-3">No tienes productos favoritos aún</h4>
        <a href="productos.php" class="btn btn-gamer mt-3 px-4">Explorar productos</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php while($p = $favoritos->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-3">
            <div class="producto-card">
                <div class="producto-img">
                    <?php if($p['imagen']): ?>
                        <img src="../../assets/<?= $p['imagen'] ?>" alt="">
                    <?php else: ?>
                        📦
                    <?php endif; ?>
                </div>
                <div class="p-3">
                    <p class="marca mb-1"><?= htmlspecialchars($p['marca']) ?></p>
                    <p class="fw-bold mb-1"><?= htmlspecialchars($p['nombre']) ?></p>
                    <p class="precio mb-3">Bs. <?= number_format($p['precio'], 2) ?></p>
                    <div class="d-flex gap-2">
                        <?php if($p['stock'] > 0): ?>
                        <form method="POST" class="flex-grow-1">
                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                            <button name="agregar_carrito" class="btn btn-gamer w-100 btn-sm">
                                <i class="bi bi-cart-plus"></i> Al carrito
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-secondary w-100 btn-sm" disabled>Agotado</button>
                        <?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                            <button name="eliminar_favorito" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-heart-break"></i>
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