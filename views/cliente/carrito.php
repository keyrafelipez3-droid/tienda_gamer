<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

// Actualizar cantidad
if (isset($_POST['actualizar'])) {
    $id = intval($_POST['id_producto']);
    $cant = intval($_POST['cantidad']);
    if ($cant <= 0) {
        unset($_SESSION['carrito'][$id]);
        $_SESSION['msg_carrito'] = 'Producto eliminado del carrito.';
        $_SESSION['msg_tipo'] = 'err';
    } else {
        $_SESSION['carrito'][$id] = $cant;
        $_SESSION['msg_carrito'] = 'Cantidad actualizada correctamente.';
        $_SESSION['msg_tipo'] = 'ok';
    }
    header('Location: carrito.php');
    exit;
}

// Eliminar producto
if (isset($_POST['eliminar'])) {
    unset($_SESSION['carrito'][intval($_POST['id_producto'])]);
    $_SESSION['msg_carrito'] = 'Producto eliminado del carrito.';
    $_SESSION['msg_tipo'] = 'err';
    header('Location: carrito.php');
    exit;
}

// Vaciar carrito
if (isset($_POST['vaciar'])) {
    $_SESSION['carrito'] = [];
    $_SESSION['msg_carrito'] = 'Carrito vaciado correctamente.';
    $_SESSION['msg_tipo'] = 'err';
    header('Location: carrito.php');
    exit;
}

// Confirmar compra
if (isset($_POST['confirmar_compra']) && !empty($_SESSION['carrito'])) {
    $id_usuario = $_SESSION['usuario_id'];
    $total = 0;
    $items = [];

    foreach ($_SESSION['carrito'] as $id_prod => $cant) {
        $res = $conn->prepare("SELECT precio, stock, nombre FROM producto WHERE id_producto=?");
        $res->bind_param("i", $id_prod);
        $res->execute();
        $prod = $res->get_result()->fetch_assoc();
        if ($prod && $prod['stock'] >= $cant) {
            $subtotal = $prod['precio'] * $cant;
            $total += $subtotal;
            $items[] = ['id' => $id_prod, 'cant' => $cant, 'subtotal' => $subtotal];
        }
    }

    if ($total > 0) {
        $v = $conn->prepare("INSERT INTO venta (id_usuario, total) VALUES (?,?)");
        $v->bind_param("id", $id_usuario, $total);
        $v->execute();
        $id_venta = $conn->insert_id;

        foreach ($items as $item) {
            $d = $conn->prepare("INSERT INTO detalle_venta (id_venta,id_producto,cantidad,subtotal) VALUES (?,?,?,?)");
            $d->bind_param("iiid", $id_venta, $item['id'], $item['cant'], $item['subtotal']);
            $d->execute();
            $u = $conn->prepare("UPDATE producto SET stock=stock-? WHERE id_producto=?");
            $u->bind_param("ii", $item['cant'], $item['id']);
            $u->execute();
        }

        $_SESSION['carrito'] = [];
        $_SESSION['compra_exitosa'] = $id_venta;
        header('Location: historial.php');
        exit;
    }
}

// Cargar items del carrito
$carrito_items = [];
$total = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id_prod => $cant) {
        $res = $conn->prepare("SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria WHERE p.id_producto=?");
        $res->bind_param("i", $id_prod);
        $res->execute();
        $prod = $res->get_result()->fetch_assoc();
        if ($prod) {
            $prod['cantidad'] = $cant;
            $prod['subtotal'] = $prod['precio'] * $cant;
            $total += $prod['subtotal'];
            $carrito_items[] = $prod;
        }
    }
}
$cant_items = array_sum($_SESSION['carrito'] ?? []);

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
    <title>Carrito - GamerZone</title>
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
            background: #070711;
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #1a1a2e;
            border-radius: 2px;
        }

        .navbar {
            background: rgba(13, 13, 26, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #1a1a2e;
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: #00ff88;
            text-decoration: none;
        }

        .nav-brand span {
            color: #fff;
        }

        .btn-back {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #aaa;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #1a1a2e;
            transition: all 0.2s;
        }

        .btn-back:hover {
            color: #fff;
            border-color: #333;
        }

        .btn-logout-sm {
            background: rgba(255, 68, 68, 0.08);
            border: 1px solid rgba(255, 68, 68, 0.2);
            color: #ff6b6b;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-logout-sm:hover {
            background: rgba(255, 68, 68, 0.15);
        }

        .content {
            padding: 40px 0;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .page-title span {
            color: #00ff88;
        }

        .page-sub {
            color: #555;
            font-size: 0.875rem;
        }

        .cart-item {
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .cart-item:hover {
            border-color: #2a2a3e;
        }

        .item-img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            background: #111120;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            overflow: hidden;
            border: 1px solid #1a1a2e;
            flex-shrink: 0;
        }

        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-name {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .item-brand {
            font-size: 0.78rem;
            color: #555;
        }

        .item-cat {
            display: inline-block;
            background: rgba(0, 255, 136, 0.06);
            border: 1px solid rgba(0, 255, 136, 0.12);
            color: #00ff88;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 4px;
        }

        .item-price {
            font-size: 0.875rem;
            color: #555;
            margin-top: 6px;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #111120;
            border: 1px solid #1a1a2e;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        .qty-btn:hover {
            border-color: #00ff88;
            color: #00ff88;
        }

        .qty-input {
            width: 50px;
            background: #111120;
            border: 1px solid #1a1a2e;
            color: #fff;
            border-radius: 8px;
            padding: 4px;
            text-align: center;
            font-size: 0.875rem;
        }

        .qty-input:focus {
            outline: none;
            border-color: #00ff88;
        }

        .item-subtotal {
            font-size: 1.1rem;
            font-weight: 800;
            color: #00ff88;
            text-align: right;
        }

        .item-remove {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border-radius: 8px;
            padding: 6px 10px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.82rem;
        }

        .item-remove:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        .summary-card {
            background: #0d0d1a;
            border: 1px solid #1a1a2e;
            border-radius: 16px;
            padding: 24px;
            position: sticky;
            top: 80px;
        }

        .summary-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #1a1a2e;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.875rem;
        }

        .summary-row .label {
            color: #555;
        }

        .summary-row .value {
            font-weight: 600;
        }

        .summary-divider {
            border: none;
            border-top: 1px solid #1a1a2e;
            margin: 16px 0;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .summary-total .label {
            font-size: 1rem;
            font-weight: 700;
        }

        .summary-total .value {
            font-size: 1.8rem;
            font-weight: 800;
            color: #00ff88;
        }

        .btn-checkout {
            background: #00ff88;
            color: #000;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            padding: 14px;
            width: 100%;
            font-size: 1rem;
            transition: all 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-checkout:hover {
            background: #00cc6a;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 255, 136, 0.3);
        }

        .btn-clear {
            background: transparent;
            border: 1px solid #1a1a2e;
            color: #555;
            border-radius: 10px;
            padding: 10px;
            width: 100%;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        .btn-clear:hover {
            border-color: #ef4444;
            color: #ef4444;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-cart h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #666;
        }

        .btn-shop {
            background: #00ff88;
            color: #000;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 28px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-shop:hover {
            background: #00cc6a;
            color: #000;
        }

        .secure-badges {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: #444;
        }

        .secure-badge i {
            color: #00ff88;
        }

        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
        }

        .toast-msg {
            background: #0d1f0d;
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.875rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.3s ease;
            min-width: 280px;
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
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
                <div class="d-flex align-items-center gap-2">
                    <a href="productos.php" class="btn-back"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
                    <form action="../../controllers/auth_controller.php" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button class="btn-logout-sm"><i class="bi bi-box-arrow-right me-1"></i>Salir</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['msg_carrito'])): ?>
        <div class="toast-container">
            <div class="toast-msg" id="toastMsg"
                style="<?= ($_SESSION['msg_tipo'] ?? 'ok') === 'err' ? 'background:#2a0d0d;border-color:rgba(239,68,68,0.3);' : '' ?>">
                <i class="bi <?= ($_SESSION['msg_tipo'] ?? 'ok') === 'ok' ? 'bi-check-circle-fill' : 'bi-trash-fill' ?>"
                    style="color:<?= ($_SESSION['msg_tipo'] ?? 'ok') === 'ok' ? '#00ff88' : '#ef4444' ?>"></i>
                <span><?= $_SESSION['msg_carrito'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['msg_carrito'], $_SESSION['msg_tipo']); ?>
    <?php endif; ?>

    <div class="content">
        <div class="container">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div>
                    <h1 class="page-title">Mi <span>Carrito</span></h1>
                    <p class="page-sub"><?= $cant_items ?> producto<?= $cant_items != 1 ? 's' : '' ?> en tu carrito</p>
                </div>
            </div>

            <?php if (empty($carrito_items)): ?>
                <div class="empty-cart">
                    <div style="font-size:5rem;margin-bottom:20px;opacity:0.3;">🛒</div>
                    <h3>Tu carrito está vacío</h3>
                    <p style="color:#444;font-size:0.875rem;margin-bottom:24px;">Agrega productos desde la tienda para
                        comenzar</p>
                    <a href="productos.php" class="btn-shop"><i class="bi bi-grid"></i> Explorar Tienda</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <?php foreach ($carrito_items as $item):
                            $img = imgSrc($item['imagen']);
                            ?>
                            <div class="cart-item">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="item-img">
                                        <?php if ($img): ?>
                                            <img src="<?= $img ?>" alt="">
                                        <?php else: ?>📦<?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="item-name"><?= htmlspecialchars($item['nombre']) ?></div>
                                        <div class="item-brand"><?= htmlspecialchars($item['marca']) ?></div>
                                        <span class="item-cat"><?= htmlspecialchars($item['nombre_categoria']) ?></span>
                                        <div class="item-price">Bs. <?= number_format($item['precio'], 2) ?> por unidad</div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-3">
                                        <div class="item-subtotal">Bs. <?= number_format($item['subtotal'], 2) ?></div>
                                        <form method="POST" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                                            <div class="qty-control">
                                                <button type="submit" name="actualizar" class="qty-btn"
                                                    onclick="this.form.cantidad.value=Math.max(1,parseInt(this.form.cantidad.value)-1)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="cantidad" class="qty-input"
                                                    value="<?= $item['cantidad'] ?>" min="1" max="<?= $item['stock'] ?>">
                                                <button type="submit" name="actualizar" class="qty-btn"
                                                    onclick="this.form.cantidad.value=Math.min(<?= $item['stock'] ?>,parseInt(this.form.cantidad.value)+1)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="id_producto" value="<?= $item['id_producto'] ?>">
                                            <button type="submit" name="eliminar" class="item-remove">
                                                <i class="bi bi-trash me-1"></i>Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-lg-4">
                        <div class="summary-card">
                            <div class="summary-title">📋 Resumen del pedido</div>
                            <div class="summary-row">
                                <span class="label">Productos (<?= $cant_items ?>)</span>
                                <span class="value">Bs. <?= number_format($total, 2) ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="label">Envío</span>
                                <span class="value" style="color:#00ff88;">Gratis</span>
                            </div>
                            <div class="summary-row">
                                <span class="label">Descuento</span>
                                <span class="value" style="color:#555;">Bs. 0.00</span>
                            </div>
                            <hr class="summary-divider">
                            <div class="summary-total">
                                <span class="label">Total</span>
                                <span class="value">Bs. <?= number_format($total, 2) ?></span>
                            </div>
                            <form method="POST">
                                <button type="submit" name="confirmar_compra" class="btn-checkout">
                                    <i class="bi bi-bag-check-fill"></i> Confirmar Compra
                                </button>
                            </form>
                            <form method="POST">
                                <button type="submit" name="vaciar" class="btn-clear">
                                    <i class="bi bi-trash me-1"></i>Vaciar carrito
                                </button>
                            </form>
                            <div class="secure-badges">
                                <div class="secure-badge"><i class="bi bi-shield-lock"></i>Pago seguro</div>
                                <div class="secure-badge"><i class="bi bi-truck"></i>Envío gratis</div>
                                <div class="secure-badge"><i class="bi bi-arrow-counterclockwise"></i>Garantía</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toast = document.getElementById('toastMsg');
        if (toast) setTimeout(() => { toast.style.transition = 'opacity 0.5s'; toast.style.opacity = '0'; }, 3000);
    </script>
</body>

</html>