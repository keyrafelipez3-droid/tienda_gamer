<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

// Modificar cantidad
if(isset($_POST['actualizar'])) {
    $id = $_POST['id_producto'];
    $cant = intval($_POST['cantidad']);
    if($cant <= 0) unset($_SESSION['carrito'][$id]);
    else $_SESSION['carrito'][$id] = $cant;
}

// Eliminar producto
if(isset($_POST['eliminar'])) {
    unset($_SESSION['carrito'][$_POST['id_producto']]);
}

// Confirmar compra
if(isset($_POST['confirmar_compra']) && !empty($_SESSION['carrito'])) {
    $id_usuario = $_SESSION['usuario_id'];
    $total = 0;
    $items = [];

    foreach($_SESSION['carrito'] as $id_prod => $cant) {
        $res = $conn->prepare("SELECT precio, stock FROM producto WHERE id_producto=?");
        $res->bind_param("i", $id_prod);
        $res->execute();
        $prod = $res->get_result()->fetch_assoc();
        if($prod && $prod['stock'] >= $cant) {
            $subtotal = $prod['precio'] * $cant;
            $total += $subtotal;
            $items[] = ['id' => $id_prod, 'cant' => $cant, 'subtotal' => $subtotal];
        }
    }

    if($total > 0) {
        // Crear venta
        $v = $conn->prepare("INSERT INTO venta (id_usuario, total) VALUES (?,?)");
        $v->bind_param("id", $id_usuario, $total);
        $v->execute();
        $id_venta = $conn->insert_id;

        // Insertar detalles y actualizar stock
        foreach($items as $item) {
            $d = $conn->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, subtotal) VALUES (?,?,?,?)");
            $d->bind_param("iiid", $id_venta, $item['id'], $item['cant'], $item['subtotal']);
            $d->execute();
            $u = $conn->prepare("UPDATE producto SET stock = stock - ? WHERE id_producto=?");
            $u->bind_param("ii", $item['cant'], $item['id']);
            $u->execute();
        }

        $_SESSION['carrito'] = [];
        $_SESSION['compra_exitosa'] = $id_venta;
        header('Location: historial.php'); exit;
    }
}

// Obtener productos del carrito
$carrito_items = [];
$total = 0;
if(!empty($_SESSION['carrito'])) {
    foreach($_SESSION['carrito'] as $id_prod => $cant) {
        $res = $conn->prepare("SELECT * FROM producto WHERE id_producto=?");
        $res->bind_param("i", $id_prod);
        $res->execute();
        $prod = $res->get_result()->fetch_assoc();
        if($prod) {
            $prod['cantidad'] = $cant;
            $prod['subtotal'] = $prod['precio'] * $cant;
            $total += $prod['subtotal'];
            $carrito_items[] = $prod;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .card-dark { background: #111; border: 1px solid #222; border-radius: 16px; padding: 24px; }
        .item-row { background: #161616; border: 1px solid #1a1a1a; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        .item-img { width: 70px; height: 70px; background: #1a1a1a; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 2rem; overflow: hidden; }
        .item-img img { width: 100%; height: 100%; object-fit: cover; }
        .precio { color: #00ff88; font-weight: 800; }
        .form-control { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 8px; text-align: center; width: 70px; }
        .form-control:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: none; }
        .total-box { background: #0d1f0d; border: 1px solid #00ff88; border-radius: 16px; padding: 24px; }
        .total-box h3 { color: #00ff88; font-weight: 900; font-size: 2rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="productos.php">Gamer<span>Zone</span></a>
        <div class="d-flex gap-3 ms-auto">
            <a href="productos.php" class="btn btn-outline-secondary btn-sm">← Seguir comprando</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="fw-bold mb-4">🛒 Mi <span style="color:#00ff88">Carrito</span></h3>

    <?php if(empty($carrito_items)): ?>
    <div class="text-center py-5">
        <div style="font-size:5rem;">🛒</div>
        <h4 class="text-muted mt-3">Tu carrito está vacío</h4>
        <a href="productos.php" class="btn btn-gamer mt-3 px-4">Ver productos</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <?php foreach($carrito_items as $item): ?>
            <div class="item-row">
                <div class="d-flex align-items-center gap-3">
                    <div class="item-img">
                        <?php if($item['imagen']): ?>
                            <img src="../../assets/<?= $item['imagen'] ?>" alt="">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <p class="fw-bold mb-0"><?= htmlspecialchars($item['nombre']) ?></p>
                        <small class="text-muted"><?= htmlspecialchars($item['marca']) ?></small>
                        <p class="precio mb-0 mt-1">Bs. <?= number_format($item['precio'], 2) ?> c/u</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <form method="POST" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                            <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>" min="0" max="<?= $item['stock'] ?>" class="form-control">
                            <button name="actualizar" class="btn btn-outline-secondary btn-sm">↺</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                            <button name="eliminar" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    <div class="text-end" style="min-width:100px">
                        <p class="precio mb-0">Bs. <?= number_format($item['subtotal'], 2) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="col-lg-4">
            <div class="total-box">
                <h5 class="text-muted mb-3">Resumen del pedido</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>Bs. <?= number_format($total, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Envío</span>
                    <span class="text-success">Gratis</span>
                </div>
                <hr style="border-color:#1a3a1a">
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold">Total</span>
                    <h3 class="mb-0">Bs. <?= number_format($total, 2) ?></h3>
                </div>
                <form method="POST">
                    <button name="confirmar_compra" class="btn btn-gamer w-100 py-3" style="font-size:1.1rem;">
                        <i class="bi bi-bag-check"></i> Confirmar compra
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>