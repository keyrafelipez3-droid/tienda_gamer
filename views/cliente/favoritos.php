<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

if (isset($_POST['eliminar_favorito'])) {
    $id_producto = intval($_POST['id_producto']);
    $id_usuario = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("DELETE FROM favorito WHERE id_usuario=? AND id_producto=?");
    $stmt->bind_param("ii", $id_usuario, $id_producto);
    $stmt->execute();
    $_SESSION['msg_fav'] = 'Producto eliminado de favoritos.';
    $_SESSION['msg_tipo'] = 'err';
    header('Location: favoritos.php');
    exit;
}

if (isset($_POST['agregar_carrito'])) {
    $id_producto = intval($_POST['id_producto']);
    if (!isset($_SESSION['carrito']))
        $_SESSION['carrito'] = [];
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]++;
    } else {
        $_SESSION['carrito'][$id_producto] = 1;
    }
    $_SESSION['msg_fav'] = 'Producto agregado al carrito.';
    $_SESSION['msg_tipo'] = 'ok';
    header('Location: favoritos.php');
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT p.*, c.nombre_categoria FROM favorito f JOIN producto p ON f.id_producto=p.id_producto JOIN categoria c ON p.id_categoria=c.id_categoria WHERE f.id_usuario=? ORDER BY f.fecha DESC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$favoritos = $stmt->get_result();
$cant_favs = $favoritos->num_rows;
$cant_carrito = array_sum($_SESSION['carrito'] ?? []);

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
    <title>Favoritos - GamerZone</title>
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
            min-height: 100vh;
        }

        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #252525;
            border-radius: 2px;
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
            border: 1px solid #252525;
            transition: all 0.2s;
        }

        .btn-back:hover {
            color: #fff;
            border-color: #333;
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
            position: relative;
        }

        .nav-icon-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
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

        .page-sub {
            color: #555;
            font-size: 0.875rem;
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

        .fav-card {
            background: #111111;
            border: 1px solid #252525;
            border-radius: 18px;
            overflow: hidden;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .fav-card:hover {
            border-color: rgba(255, 68, 102, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .fav-img {
            position: relative;
            height: 200px;
            background: linear-gradient(135deg, #181818, #0a0a14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4.5rem;
            overflow: hidden;
        }

        .fav-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }

        .fav-card:hover .fav-img img {
            transform: scale(1.05);
        }

        .fav-remove {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 68, 102, 0.15);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 68, 102, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #ff4466;
            font-size: 0.9rem;
        }

        .fav-remove:hover {
            background: rgba(255, 68, 102, 0.3);
        }

        .fav-body {
            padding: 18px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .fav-marca {
            font-size: 0.72rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .fav-nombre {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }

        .fav-cat {
            display: inline-block;
            background: rgba(255, 68, 102, 0.06);
            border: 1px solid rgba(255, 68, 102, 0.15);
            color: #ff4466;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .fav-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .fav-precio {
            font-size: 1.2rem;
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
        }

        .btn-add:disabled {
            background: #252525;
            color: #444;
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #666;
        }

        .btn-shop {
            background: #d4a843;
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
            background: #c89a30;
            color: #000;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="productos.php" class="nav-brand">Gamer<span>Zone</span></a>
                <div class="d-flex align-items-center gap-2">
                    <a href="favoritos.php" class="nav-icon-btn" style="color:#d4a843;">
                        <i class="bi bi-heart-fill"></i>
                        <span class="d-none d-md-inline">Favoritos</span>
                        <?php if ($cant_favs > 0): ?><span class="nav-badge fav"><?= $cant_favs ?></span><?php endif; ?>
                    </a>
                    <a href="carrito.php" class="nav-icon-btn">
                        <i class="bi bi-cart3"></i>
                        <span class="d-none d-md-inline">Carrito</span>
                        <?php if ($cant_carrito > 0): ?><span class="nav-badge"><?= $cant_carrito ?></span><?php endif; ?>
                    </a>
                    <a href="historial.php" class="nav-icon-btn">
                        <i class="bi bi-bag-check"></i>
                        <span class="d-none d-md-inline">Pedidos</span>
                    </a>
                    <a href="perfil.php" class="d-none d-lg-flex" style="display:flex;align-items:center;gap:8px;background:rgba(212,168,67,0.06);border:1px solid rgba(212,168,67,0.15);border-radius:20px;padding:6px 14px;font-size:0.82rem;text-decoration:none;color:#fff;">
                        <div style="width:8px;height:8px;background:#d4a843;border-radius:50%;"></div>
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

    <?php if (isset($_SESSION['msg_fav'])): ?>
        <div class="toast-container">
            <div class="toast-msg" id="toastMsg"
                style="<?= ($_SESSION['msg_tipo'] ?? 'ok') === 'err' ? 'background:#2a0d0d;border-color:rgba(239,68,68,0.3);' : '' ?>">
                <i class="bi <?= ($_SESSION['msg_tipo'] ?? 'ok') === 'ok' ? 'bi-check-circle-fill' : 'bi-heart-break-fill' ?>"
                    style="color:<?= ($_SESSION['msg_tipo'] ?? 'ok') === 'ok' ? '#d4a843' : '#ef4444' ?>"></i>
                <span><?= $_SESSION['msg_fav'] ?></span>
            </div>
        </div>
        <?php unset($_SESSION['msg_fav'], $_SESSION['msg_tipo']); ?>
    <?php endif; ?>

    <div class="content">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h1 class="page-title">Mis <span style="color:#ff4466">Favoritos</span></h1>
                    <p class="page-sub"><?= $cant_favs ?> producto<?= $cant_favs != 1 ? 's' : '' ?>
                        guardado<?= $cant_favs != 1 ? 's' : '' ?></p>
                </div>
                <?php if ($cant_favs > 0): ?>
                    <a href="productos.php"
                        style="display:flex;align-items:center;gap:6px;color:#d4a843;text-decoration:none;font-size:0.875rem;border:1px solid rgba(212,168,67,0.2);padding:8px 16px;border-radius:8px;">
                        <i class="bi bi-plus-lg"></i> Agregar más
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($cant_favs === 0): ?>
                <div class="empty-state">
                    <i class="bi bi-heart" style="font-size:4rem;margin-bottom:20px;opacity:0.2;display:block;"></i>
                    <h3>No tienes favoritos aún</h3>
                    <p style="color:#444;font-size:0.875rem;margin-bottom:24px;">Guarda los productos que te gusten para
                        encontrarlos fácilmente</p>
                    <a href="productos.php" class="btn-shop"><i class="bi bi-grid"></i> Explorar Tienda</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php while ($p = $favoritos->fetch_assoc()):
                        $img = imgSrc($p['imagen']);
                        ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="fav-card">
                                <div class="fav-img">
                                    <?php if ($img): ?>
                                        <img src="<?= $img ?>" alt="">
                                    <?php else: ?><i class="bi bi-box" style="font-size:2rem;opacity:0.3;"></i><?php endif; ?>
                                    <form method="POST" style="position:absolute;top:12px;right:12px;">
                                        <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                                        <button type="submit" name="eliminar_favorito" class="fav-remove"
                                            title="Quitar de favoritos">
                                            <i class="bi bi-heart-break-fill"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="fav-body">
                                    <div class="fav-marca"><?= htmlspecialchars($p['marca']) ?></div>
                                    <div class="fav-nombre"><?= htmlspecialchars($p['nombre']) ?></div>
                                    <span class="fav-cat"><?= htmlspecialchars($p['nombre_categoria']) ?></span>
                                    <div class="fav-footer">
                                        <span class="fav-precio">Bs. <?= number_format($p['precio'], 2) ?></span>
                                        <?php if ($p['stock'] > 10): ?>
                                            <span class="stock-badge stock-ok"><?= $p['stock'] ?> und.</span>
                                        <?php elseif ($p['stock'] > 0): ?>
                                            <span class="stock-badge stock-low">Últimas <?= $p['stock'] ?></span>
                                        <?php else: ?>
                                            <span class="stock-badge stock-out">Agotado</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($p['stock'] > 0): ?>
                                        <form method="POST">
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
                    <?php endwhile; ?>
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