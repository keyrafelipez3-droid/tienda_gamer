<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
if(!$id) { header('Location: productos.php'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria WHERE p.id_producto=? AND p.estado=1");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if(!$p) { header('Location: productos.php'); exit; }

if(isset($_POST['agregar_carrito'])) {
    $cant = intval($_POST['cantidad'] ?? 1);
    if(!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + $cant;
    $_SESSION['msg_carrito'] = "¡{$p['nombre']} agregado al carrito!";
    header('Location: producto_detalle.php?id=' . $id); exit;
}

if(isset($_POST['toggle_favorito'])) {
    $id_usuario = $_SESSION['usuario_id'];
    $check = $conn->prepare("SELECT id_favorito FROM favorito WHERE id_usuario=? AND id_producto=?");
    $check->bind_param("ii", $id_usuario, $id);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0) {
        $del = $conn->prepare("DELETE FROM favorito WHERE id_usuario=? AND id_producto=?");
        $del->bind_param("ii", $id_usuario, $id);
        $del->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO favorito (id_usuario, id_producto) VALUES (?,?)");
        $ins->bind_param("ii", $id_usuario, $id);
        $ins->execute();
    }
    header('Location: producto_detalle.php?id=' . $id); exit;
}

$fav_check = $conn->prepare("SELECT id_favorito FROM favorito WHERE id_usuario=? AND id_producto=?");
$fav_check->bind_param("ii", $_SESSION['usuario_id'], $id);
$fav_check->execute();
$fav_check->store_result();
$is_fav = $fav_check->num_rows > 0;

$rel = $conn->prepare("SELECT * FROM producto WHERE id_categoria=? AND id_producto!=? AND estado=1 LIMIT 4");
$rel->bind_param("ii", $p['id_categoria'], $id);
$rel->execute();
$relacionados = $rel->get_result();

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);

// Helper para imagen
function imgSrc($img, $prefix='../../assets/') {
    if(!$img) return null;
    return (strpos($img,'http')===0) ? $img : $prefix.$img;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['nombre']) ?> - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#070711;color:#fff;font-family:'Inter',sans-serif;}
        ::-webkit-scrollbar{width:4px;}
        ::-webkit-scrollbar-thumb{background:#1a1a2e;border-radius:2px;}
        .navbar{background:rgba(13,13,26,0.95);backdrop-filter:blur(10px);border-bottom:1px solid #1a1a2e;padding:14px 0;position:sticky;top:0;z-index:1000;}
        .nav-brand{font-size:1.5rem;font-weight:800;color:#00ff88;text-decoration:none;}
        .nav-brand span{color:#fff;}
        .btn-back{display:flex;align-items:center;gap:6px;color:#aaa;text-decoration:none;font-size:0.875rem;padding:8px 14px;border-radius:8px;border:1px solid #1a1a2e;transition:all 0.2s;}
        .btn-back:hover{color:#fff;border-color:#333;}
        .nav-icon-btn{display:flex;align-items:center;gap:6px;color:#aaa;text-decoration:none;font-size:0.85rem;padding:8px 14px;border-radius:8px;transition:all 0.2s;position:relative;}
        .nav-icon-btn:hover{color:#fff;background:rgba(255,255,255,0.05);}
        .nav-badge{position:absolute;top:-4px;right:-4px;background:#00ff88;color:#000;font-size:0.6rem;font-weight:800;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
        .btn-logout-sm{background:rgba(255,68,68,0.08);border:1px solid rgba(255,68,68,0.2);color:#ff6b6b;border-radius:8px;padding:8px 14px;font-size:0.8rem;cursor:pointer;transition:all 0.2s;}
        .btn-logout-sm:hover{background:rgba(255,68,68,0.15);}
        .content{padding:40px 0;}
        .breadcrumb-nav{font-size:0.78rem;color:#444;margin-bottom:24px;}
        .breadcrumb-nav a{color:#555;text-decoration:none;}
        .breadcrumb-nav a:hover{color:#00ff88;}
        .breadcrumb-nav span{color:#777;}
        .img-main{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:20px;height:420px;display:flex;align-items:center;justify-content:center;font-size:8rem;overflow:hidden;position:relative;}
        .img-main img{width:100%;height:100%;object-fit:contain;padding:20px;}
        .prod-tag{display:inline-block;background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:6px;padding:4px 12px;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;}
        .prod-title{font-size:1.8rem;font-weight:800;line-height:1.2;margin-bottom:8px;}
        .prod-marca{font-size:0.875rem;color:#555;margin-bottom:16px;}
        .prod-marca strong{color:#aaa;}
        .prod-precio{font-size:2.5rem;font-weight:800;color:#00ff88;margin-bottom:8px;}
        .prod-stock-info{display:flex;align-items:center;gap:8px;margin-bottom:24px;}
        .stock-ok{background:rgba(0,255,136,0.08);color:#00ff88;border:1px solid rgba(0,255,136,0.2);border-radius:8px;padding:5px 12px;font-size:0.78rem;font-weight:600;}
        .stock-low{background:rgba(245,158,11,0.08);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);border-radius:8px;padding:5px 12px;font-size:0.78rem;font-weight:600;}
        .stock-out{background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:5px 12px;font-size:0.78rem;font-weight:600;}
        .prod-desc{color:#888;font-size:0.9rem;line-height:1.7;margin-bottom:24px;padding:20px;background:#0d0d1a;border-radius:12px;border:1px solid #1a1a2e;}
        .specs-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;}
        .spec-item{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:10px;padding:14px;}
        .spec-label{font-size:0.72rem;color:#444;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;}
        .spec-value{font-size:0.875rem;font-weight:600;}
        .qty-row{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
        .qty-label{font-size:0.82rem;color:#555;width:80px;}
        .qty-control{display:flex;align-items:center;gap:8px;}
        .qty-btn{width:36px;height:36px;border-radius:10px;background:#111120;border:1px solid #1a1a2e;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;font-size:1rem;}
        .qty-btn:hover{border-color:#00ff88;color:#00ff88;}
        .qty-input{width:60px;background:#111120;border:1px solid #1a1a2e;color:#fff;border-radius:10px;padding:8px;text-align:center;font-size:0.95rem;font-weight:700;}
        .qty-input:focus{outline:none;border-color:#00ff88;}
        .btn-add-main{background:#00ff88;color:#000;font-weight:800;border:none;border-radius:12px;padding:14px 28px;font-size:1rem;transition:all 0.2s;cursor:pointer;display:flex;align-items:center;gap:8px;flex:1;}
        .btn-add-main:hover{background:#00cc6a;transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,255,136,0.3);}
        .btn-fav-main{width:50px;height:50px;border-radius:12px;border:1px solid #1a1a2e;background:#0d0d1a;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;color:#555;font-size:1.2rem;flex-shrink:0;}
        .btn-fav-main:hover,.btn-fav-main.active{border-color:#ff4466;color:#ff4466;background:rgba(255,68,102,0.08);}
        .action-row{display:flex;gap:10px;align-items:center;}
        .toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;}
        .toast-msg{background:#0d1f0d;border:1px solid rgba(0,255,136,0.3);border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:12px;font-size:0.875rem;box-shadow:0 8px 32px rgba(0,0,0,0.4);animation:slideIn 0.3s ease;}
        @keyframes slideIn{from{transform:translateX(100px);opacity:0;}to{transform:translateX(0);opacity:1;}}
        .section-title{font-size:1.2rem;font-weight:800;margin-bottom:20px;}
        .section-title span{color:#00ff88;}
        .rel-card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:14px;overflow:hidden;transition:all 0.3s;text-decoration:none;display:block;color:#fff;}
        .rel-card:hover{border-color:rgba(0,255,136,0.3);transform:translateY(-3px);color:#fff;}
        .rel-img{height:140px;background:#111120;display:flex;align-items:center;justify-content:center;font-size:3rem;overflow:hidden;}
        .rel-img img{width:100%;height:100%;object-fit:cover;}
        .rel-body{padding:14px;}
        .rel-nombre{font-weight:700;font-size:0.875rem;margin-bottom:4px;}
        .rel-precio{color:#00ff88;font-weight:800;font-size:1rem;}
        .divider{border:none;border-top:1px solid #1a1a2e;margin:40px 0;}
        .guarantee-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:20px;}
        .guarantee-item{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:10px;padding:12px;text-align:center;}
        .guarantee-item i{color:#00ff88;font-size:1.2rem;display:block;margin-bottom:4px;}
        .guarantee-item span{font-size:0.72rem;color:#555;}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
            <div class="d-flex align-items-center gap-2">
                <a href="productos.php" class="btn-back"><i class="bi bi-arrow-left"></i> Volver</a>
                <a href="favoritos.php" class="nav-icon-btn"><i class="bi bi-heart"></i></a>
                <a href="carrito.php" class="nav-icon-btn">
                    <i class="bi bi-cart3"></i>
                    <?php if($cant_carrito > 0): ?>
                    <span class="nav-badge"><?= $cant_carrito ?></span>
                    <?php endif; ?>
                </a>
                <a href="historial.php" class="nav-icon-btn"><i class="bi bi-bag-check"></i></a>
                <form action="../../controllers/auth_controller.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn-logout-sm"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['msg_carrito'])): ?>
<div class="toast-container">
    <div class="toast-msg" id="toastMsg">
        <i class="bi bi-cart-check-fill" style="color:#00ff88"></i>
        <span><?= $_SESSION['msg_carrito'] ?></span>
    </div>
</div>
<?php unset($_SESSION['msg_carrito']); ?>
<?php endif; ?>

<div class="content">
    <div class="container">
        <div class="breadcrumb-nav">
            <a href="productos.php">Tienda</a> /
            <a href="productos.php?categoria=<?= $p['id_categoria'] ?>"><?= htmlspecialchars($p['nombre_categoria']) ?></a> /
            <span><?= htmlspecialchars($p['nombre']) ?></span>
        </div>

        <div class="row g-5">
            <!-- IMAGEN -->
            <div class="col-lg-5">
                <div class="img-main">
                    <?php $img = imgSrc($p['imagen']); ?>
                    <?php if($img): ?>
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <?php else: ?>📦<?php endif; ?>
                </div>
            </div>

            <!-- INFO -->
            <div class="col-lg-7">
                <span class="prod-tag"><?= htmlspecialchars($p['nombre_categoria']) ?></span>
                <h1 class="prod-title"><?= htmlspecialchars($p['nombre']) ?></h1>
                <div class="prod-marca">Marca: <strong><?= htmlspecialchars($p['marca']) ?></strong></div>
                <div class="prod-precio">Bs. <?= number_format($p['precio'], 2) ?></div>

                <div class="prod-stock-info">
                    <?php if($p['stock'] > 10): ?>
                        <span class="stock-ok"><i class="bi bi-check-circle me-1"></i><?= $p['stock'] ?> unidades disponibles</span>
                    <?php elseif($p['stock'] > 0): ?>
                        <span class="stock-low"><i class="bi bi-exclamation-circle me-1"></i>¡Solo quedan <?= $p['stock'] ?>!</span>
                    <?php else: ?>
                        <span class="stock-out"><i class="bi bi-x-circle me-1"></i>Sin stock</span>
                    <?php endif; ?>
                </div>

                <?php if($p['descripcion']): ?>
                <div class="prod-desc"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></div>
                <?php endif; ?>

                <div class="specs-grid">
                    <div class="spec-item">
                        <div class="spec-label">Categoría</div>
                        <div class="spec-value"><?= htmlspecialchars($p['nombre_categoria']) ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Marca</div>
                        <div class="spec-value"><?= htmlspecialchars($p['marca']) ?: 'N/A' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Disponibilidad</div>
                        <div class="spec-value" style="color:<?= $p['stock']>0?'#00ff88':'#ef4444' ?>"><?= $p['stock']>0?'En stock':'Agotado' ?></div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Stock</div>
                        <div class="spec-value"><?= $p['stock'] ?> unidades</div>
                    </div>
                </div>

                <?php if($p['stock'] > 0): ?>
                <form method="POST">
                    <div class="qty-row">
                        <span class="qty-label">Cantidad:</span>
                        <div class="qty-control">
                            <button type="button" class="qty-btn" onclick="cambiarCant(-1)"><i class="bi bi-dash"></i></button>
                            <input type="number" name="cantidad" id="cantidad" class="qty-input" value="1" min="1" max="<?= $p['stock'] ?>">
                            <button type="button" class="qty-btn" onclick="cambiarCant(1)"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                    <div class="action-row">
                        <button type="submit" name="agregar_carrito" class="btn-add-main">
                            <i class="bi bi-cart-plus"></i> Agregar al carrito
                        </button>
                </form>
                        <form method="POST" style="margin:0;">
                            <button type="submit" name="toggle_favorito" class="btn-fav-main <?= $is_fav?'active':'' ?>">
                                <i class="bi bi-heart<?= $is_fav?'-fill':'' ?>"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                <div class="action-row">
                    <button class="btn-add-main" disabled style="background:#1a1a2e;color:#444;cursor:not-allowed;">
                        <i class="bi bi-x-circle"></i> Sin stock disponible
                    </button>
                    <form method="POST">
                        <button type="submit" name="toggle_favorito" class="btn-fav-main <?= $is_fav?'active':'' ?>">
                            <i class="bi bi-heart<?= $is_fav?'-fill':'' ?>"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <div class="guarantee-grid">
                    <div class="guarantee-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Garantía 1 año</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="bi bi-truck"></i>
                        <span>Envío gratis</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Devolución 30 días</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RELACIONADOS -->
        <?php if($relacionados->num_rows > 0): ?>
        <hr class="divider">
        <div class="section-title">Productos <span>Relacionados</span></div>
        <div class="row g-4">
            <?php while($r = $relacionados->fetch_assoc()):
                $r_img = imgSrc($r['imagen']);
            ?>
            <div class="col-6 col-md-3">
                <a href="producto_detalle.php?id=<?= $r['id_producto'] ?>" class="rel-card">
                    <div class="rel-img">
                        <?php if($r_img): ?>
                            <img src="<?= $r_img ?>" alt="<?= htmlspecialchars($r['nombre']) ?>">
                        <?php else: ?>📦<?php endif; ?>
                    </div>
                    <div class="rel-body">
                        <div style="font-size:0.7rem;color:#555;margin-bottom:2px;"><?= htmlspecialchars($r['marca']) ?></div>
                        <div class="rel-nombre"><?= htmlspecialchars($r['nombre']) ?></div>
                        <div class="rel-precio">Bs. <?= number_format($r['precio'],2) ?></div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cambiarCant(delta) {
    const input = document.getElementById('cantidad');
    const max   = parseInt(input.max);
    input.value = Math.max(1, Math.min(max, parseInt(input.value) + delta));
}
const toast = document.getElementById('toastMsg');
if(toast) setTimeout(() => { toast.style.transition='opacity 0.5s'; toast.style.opacity='0'; }, 3000);
</script>
</body>
</html>