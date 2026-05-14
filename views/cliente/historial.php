<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$id_usuario = $_SESSION['usuario_id'];

$compra_exitosa = null;
if(isset($_SESSION['compra_exitosa'])) {
    $compra_exitosa = $_SESSION['compra_exitosa'];
    unset($_SESSION['compra_exitosa']);
}

$ventas = $conn->prepare("SELECT * FROM venta WHERE id_usuario=? ORDER BY fecha DESC");
$ventas->bind_param("i", $id_usuario);
$ventas->execute();
$ventas = $ventas->get_result();
$total_ventas = $ventas->num_rows;

$total_gastado = $conn->prepare("SELECT SUM(total) as suma FROM venta WHERE id_usuario=?");
$total_gastado->bind_param("i", $id_usuario);
$total_gastado->execute();
$total_gastado = $total_gastado->get_result()->fetch_assoc()['suma'] ?? 0;

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#070711;color:#fff;font-family:'Inter',sans-serif;min-height:100vh;}
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
        .page-title{font-size:1.8rem;font-weight:800;margin-bottom:4px;}
        .page-title span{color:#00ff88;}

        /* STATS */
        .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px;}
        .stat-card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:14px;padding:20px;display:flex;align-items:center;gap:16px;}
        .stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
        .stat-num{font-size:1.5rem;font-weight:800;}
        .stat-label{font-size:0.78rem;color:#555;}

        /* ALERTA EXITO */
        .alert-success-custom{background:linear-gradient(135deg,rgba(0,255,136,0.08),rgba(0,204,106,0.04));border:1px solid rgba(0,255,136,0.25);border-radius:16px;padding:20px 24px;margin-bottom:28px;display:flex;align-items:center;gap:16px;}
        .alert-icon{width:48px;height:48px;border-radius:12px;background:rgba(0,255,136,0.1);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
        .alert-text h5{color:#00ff88;font-weight:700;margin-bottom:2px;}
        .alert-text p{color:#555;font-size:0.82rem;margin:0;}

        /* PEDIDO CARD */
        .pedido-card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:16px;overflow:hidden;margin-bottom:16px;transition:all 0.2s;}
        .pedido-card:hover{border-color:#2a2a3e;}
        .pedido-header{padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;cursor:pointer;user-select:none;}
        .pedido-header:hover{background:rgba(255,255,255,0.01);}
        .pedido-num{font-size:0.78rem;color:#555;margin-bottom:2px;}
        .pedido-fecha{font-size:0.875rem;font-weight:600;}
        .pedido-total{font-size:1.3rem;font-weight:800;color:#00ff88;}
        .status-badge{font-size:0.72rem;font-weight:700;padding:5px 12px;border-radius:8px;}
        .status-Pendiente{background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);}
        .status-Pagado{background:rgba(59,130,246,0.1);color:#3b82f6;border:1px solid rgba(59,130,246,0.25);}
        .status-Entregado{background:rgba(0,255,136,0.1);color:#00ff88;border:1px solid rgba(0,255,136,0.25);}
        .toggle-icon{color:#444;transition:transform 0.3s;font-size:1rem;}
        .pedido-items{border-top:1px solid #1a1a2e;padding:16px 24px;display:none;}
        .pedido-items.show{display:block;}
        .item-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #111;}
        .item-row:last-child{border-bottom:none;}
        .item-img{width:44px;height:44px;border-radius:8px;background:#111120;display:flex;align-items:center;justify-content:center;font-size:1.2rem;overflow:hidden;border:1px solid #1a1a2e;flex-shrink:0;}
        .item-img img{width:100%;height:100%;object-fit:cover;}
        .item-name{font-size:0.875rem;font-weight:600;}
        .item-detail{font-size:0.75rem;color:#555;margin-top:2px;}
        .item-price{margin-left:auto;font-weight:700;color:#00ff88;font-size:0.875rem;white-space:nowrap;}

        /* TIMELINE ESTADO */
        .status-timeline{display:flex;align-items:center;gap:0;margin-top:12px;}
        .timeline-step{display:flex;flex-direction:column;align-items:center;flex:1;}
        .timeline-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;border:2px solid #1a1a2e;background:#0a0a14;color:#444;transition:all 0.2s;}
        .timeline-dot.done{background:rgba(0,255,136,0.1);border-color:#00ff88;color:#00ff88;}
        .timeline-dot.active{background:rgba(245,158,11,0.1);border-color:#f59e0b;color:#f59e0b;}
        .timeline-label{font-size:0.65rem;color:#444;margin-top:4px;text-align:center;}
        .timeline-label.done{color:#00ff88;}
        .timeline-label.active{color:#f59e0b;}
        .timeline-line{flex:1;height:2px;background:#1a1a2e;margin-top:-18px;}
        .timeline-line.done{background:#00ff88;}

        /* EMPTY */
        .empty-state{text-align:center;padding:80px 20px;}
        .empty-state i{font-size:4rem;display:block;margin-bottom:20px;opacity:0.3;}
        .empty-state h3{font-size:1.3rem;font-weight:700;margin-bottom:8px;color:#666;}
        .btn-shop{background:#00ff88;color:#000;font-weight:700;border-radius:12px;padding:12px 28px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;}
        .btn-shop:hover{background:#00cc6a;color:#000;}

        @media(max-width:768px){.stats-row{grid-template-columns:1fr;}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="#" class="nav-brand">Gamer<span>Zone</span></a>
            <div class="d-flex align-items-center gap-2">
                <a href="productos.php" class="btn-back"><i class="bi bi-grid"></i> <span class="d-none d-md-inline">Tienda</span></a>
                <a href="carrito.php" class="nav-icon-btn">
                    <i class="bi bi-cart3"></i>
                    <?php if($cant_carrito > 0): ?>
                    <span class="nav-badge"><?= $cant_carrito ?></span>
                    <?php endif; ?>
                </a>
                <form action="../../controllers/auth_controller.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn-logout-sm"><i class="bi bi-box-arrow-right me-1"></i>Salir</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="content">
    <div class="container">
        <div class="mb-4">
            <h1 class="page-title">Mis <span>Pedidos</span></h1>
            <p style="color:#555;font-size:0.875rem;">Historial completo de tus compras en GamerZone</p>
        </div>

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(0,255,136,0.1);">🛒</div>
                <div>
                    <div class="stat-num" style="color:#00ff88;"><?= $total_ventas ?></div>
                    <div class="stat-label">Pedidos realizados</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(59,130,246,0.1);">💰</div>
                <div>
                    <div class="stat-num" style="color:#3b82f6;font-size:1.2rem;">Bs. <?= number_format($total_gastado,0) ?></div>
                    <div class="stat-label">Total invertido</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(168,85,247,0.1);">⭐</div>
                <div>
                    <div class="stat-num" style="color:#a855f7;"><?= $total_ventas > 0 ? 'Gold' : 'New' ?></div>
                    <div class="stat-label">Nivel de cliente</div>
                </div>
            </div>
        </div>

        <!-- ALERTA ÉXITO -->
        <?php if($compra_exitosa): ?>
        <div class="alert-success-custom">
            <div class="alert-icon">✅</div>
            <div class="alert-text">
                <h5>¡Compra realizada exitosamente!</h5>
                <p>Tu pedido #<?= $compra_exitosa ?> ha sido registrado y está siendo procesado. Te notificaremos cuando esté listo.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- PEDIDOS -->
        <?php if($total_ventas === 0): ?>
        <div class="empty-state">
            <i class="bi bi-bag-x"></i>
            <h3>Aún no tienes pedidos</h3>
            <p style="color:#444;margin-bottom:24px;">Cuando realices tu primera compra aparecerá aquí</p>
            <a href="productos.php" class="btn-shop"><i class="bi bi-grid"></i> Ir a la Tienda</a>
        </div>
        <?php else: ?>
        <?php $ventas->data_seek(0); while($venta = $ventas->fetch_assoc()): ?>
        <?php
            $estados = ['Pendiente'=>1,'Pagado'=>2,'Entregado'=>3];
            $paso_actual = $estados[$venta['estado_venta']] ?? 1;
        ?>
        <div class="pedido-card">
            <div class="pedido-header" onclick="togglePedido(<?= $venta['id_venta'] ?>)">
                <div>
                    <div class="pedido-num">Pedido #<?= $venta['id_venta'] ?></div>
                    <div class="pedido-fecha"><?= date('d \d\e F, Y — H:i', strtotime($venta['fecha'])) ?></div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="status-badge status-<?= $venta['estado_venta'] ?>">
                        <?= $venta['estado_venta'] === 'Pendiente' ? '⏳' : ($venta['estado_venta'] === 'Pagado' ? '💳' : '✅') ?>
                        <?= $venta['estado_venta'] ?>
                    </span>
                    <span class="pedido-total">Bs. <?= number_format($venta['total'],2) ?></span>
                    <i class="bi bi-chevron-down toggle-icon" id="icon-<?= $venta['id_venta'] ?>"></i>
                </div>
            </div>

            <div class="pedido-items" id="items-<?= $venta['id_venta'] ?>">
                <!-- Timeline -->
                <div class="status-timeline mb-4">
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso_actual >= 1 ? 'done' : '' ?>"><i class="bi bi-check-lg"></i></div>
                        <div class="timeline-label <?= $paso_actual >= 1 ? 'done' : '' ?>">Pendiente</div>
                    </div>
                    <div class="timeline-line <?= $paso_actual >= 2 ? 'done' : '' ?>"></div>
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso_actual >= 2 ? 'done' : ($paso_actual == 2 ? 'active' : '') ?>">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="timeline-label <?= $paso_actual >= 2 ? 'done' : '' ?>">Pagado</div>
                    </div>
                    <div class="timeline-line <?= $paso_actual >= 3 ? 'done' : '' ?>"></div>
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso_actual >= 3 ? 'done' : '' ?>"><i class="bi bi-truck"></i></div>
                        <div class="timeline-label <?= $paso_actual >= 3 ? 'done' : '' ?>">Entregado</div>
                    </div>
                </div>

                <!-- Productos -->
                <?php
                $detalles = $conn->prepare("SELECT dv.*,p.nombre,p.marca,p.imagen FROM detalle_venta dv JOIN producto p ON dv.id_producto=p.id_producto WHERE dv.id_venta=?");
                $detalles->bind_param("i", $venta['id_venta']);
                $detalles->execute();
                $detalles = $detalles->get_result();
                while($d = $detalles->fetch_assoc()):
                ?>
                <div class="item-row">
                    <div class="item-img">
                        <?php if($d['imagen']): ?>
                            <img src="../../assets/<?= $d['imagen'] ?>" alt="">
                        <?php else: ?>📦<?php endif; ?>
                    </div>
                    <div>
                        <div class="item-name"><?= htmlspecialchars($d['nombre']) ?></div>
                        <div class="item-detail"><?= htmlspecialchars($d['marca']) ?> · <?= $d['cantidad'] ?> unidad<?= $d['cantidad']>1?'es':'' ?></div>
                    </div>
                    <div class="item-price">Bs. <?= number_format($d['subtotal'],2) ?></div>
                </div>
                <?php endwhile; ?>

                <div style="display:flex;justify-content:flex-end;padding-top:12px;border-top:1px solid #111;margin-top:4px;">
                    <div style="text-align:right;">
                        <div style="font-size:0.78rem;color:#555;">Total del pedido</div>
                        <div style="font-size:1.3rem;font-weight:800;color:#00ff88;">Bs. <?= number_format($venta['total'],2) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePedido(id) {
    const items = document.getElementById('items-' + id);
    const icon  = document.getElementById('icon-' + id);
    const open  = items.classList.contains('show');
    items.classList.toggle('show', !open);
    icon.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
}
// Abrir el último pedido automáticamente
<?php if($compra_exitosa): ?>
togglePedido(<?= $compra_exitosa ?>);
<?php endif; ?>
</script>
</body>
</html>