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

// Filtros
$buscar     = trim($_GET['buscar'] ?? '');
$estado_f   = $_GET['estado'] ?? '';
$fecha_desde= $_GET['fecha_desde'] ?? '';
$fecha_hasta= $_GET['fecha_hasta'] ?? '';

$where  = "WHERE v.id_usuario=?";
$params = [$id_usuario];
$types  = "i";

if($estado_f) {
    $where   .= " AND v.estado_venta=?";
    $params[] = $estado_f;
    $types   .= "s";
}
if($fecha_desde) {
    $where   .= " AND DATE(v.fecha)>=?";
    $params[] = $fecha_desde;
    $types   .= "s";
}
if($fecha_hasta) {
    $where   .= " AND DATE(v.fecha)<=?";
    $params[] = $fecha_hasta;
    $types   .= "s";
}

$stmt = $conn->prepare("SELECT v.* FROM venta v $where ORDER BY v.fecha DESC");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$ventas = $stmt->get_result();
$total_ventas = $ventas->num_rows;

// Si hay búsqueda por producto lo manejamos diferente
if($buscar) {
    $stmt2 = $conn->prepare("SELECT DISTINCT v.* FROM venta v JOIN detalle_venta dv ON v.id_venta=dv.id_venta JOIN producto p ON dv.id_producto=p.id_producto WHERE v.id_usuario=? AND (p.nombre LIKE ? OR p.marca LIKE ?) ORDER BY v.fecha DESC");
    $like = "%$buscar%";
    $stmt2->bind_param("iss", $id_usuario, $like, $like);
    $stmt2->execute();
    $ventas = $stmt2->get_result();
    $total_ventas = $ventas->num_rows;
}

$total_gastado = $conn->prepare("SELECT SUM(total) as s FROM venta WHERE id_usuario=?");
$total_gastado->bind_param("i", $id_usuario);
$total_gastado->execute();
$total_gastado = $total_gastado->get_result()->fetch_assoc()['s'] ?? 0;

$total_todas = $conn->prepare("SELECT COUNT(*) as t FROM venta WHERE id_usuario=?");
$total_todas->bind_param("i", $id_usuario);
$total_todas->execute();
$total_todas = $total_todas->get_result()->fetch_assoc()['t'];

$cant_carrito = array_sum($_SESSION['carrito'] ?? []);
$fav_c = $conn->prepare("SELECT COUNT(*) as c FROM favorito WHERE id_usuario=?");
$fav_c->bind_param("i", $id_usuario);
$fav_c->execute();
$cant_favoritos = $fav_c->get_result()->fetch_assoc()['c'];

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
    <title>Mis Pedidos - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#080808;color:#fff;font-family:'Inter',sans-serif;min-height:100vh;}
        ::-webkit-scrollbar{width:4px;}
        ::-webkit-scrollbar-thumb{background:#252525;border-radius:2px;}
        .navbar{background:rgba(13,13,26,0.95);backdrop-filter:blur(10px);border-bottom:1px solid #252525;padding:14px 0;position:sticky;top:0;z-index:1000;}
        .nav-brand{font-size:1.5rem;font-weight:800;color:#d4a843;text-decoration:none;}
        .nav-brand span{color:#fff;}
        .btn-back{display:flex;align-items:center;gap:6px;color:#aaa;text-decoration:none;font-size:0.875rem;padding:8px 14px;border-radius:8px;border:1px solid #252525;transition:all 0.2s;}
        .btn-back:hover{color:#fff;border-color:#333;}
        .nav-icon-btn{display:flex;align-items:center;gap:6px;color:#aaa;text-decoration:none;font-size:0.85rem;padding:8px 14px;border-radius:8px;transition:all 0.2s;position:relative;}
        .nav-icon-btn:hover{color:#fff;background:rgba(255,255,255,0.05);}
        .nav-badge{position:absolute;top:-4px;right:-4px;background:#d4a843;color:#000;font-size:0.6rem;font-weight:800;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
        .btn-logout-sm{background:rgba(255,68,68,0.08);border:1px solid rgba(255,68,68,0.2);color:#ff6b6b;border-radius:8px;padding:8px 14px;font-size:0.8rem;cursor:pointer;transition:all 0.2s;}
        .btn-logout-sm:hover{background:rgba(255,68,68,0.15);}
        .content{padding:40px 0;}
        .page-title{font-size:1.8rem;font-weight:800;margin-bottom:4px;}
        .page-title span{color:#d4a843;}

        /* STATS */
        .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
        .stat-card{background:#111111;border:1px solid #252525;border-radius:14px;padding:20px;display:flex;align-items:center;gap:16px;transition:all 0.2s;}
        .stat-card:hover{border-color:rgba(212,168,67,0.2);}
        .stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
        .stat-num{font-size:1.5rem;font-weight:800;}
        .stat-label{font-size:0.78rem;color:#555;}

        /* FILTROS */
        .filter-card{background:#111111;border:1px solid #252525;border-radius:16px;padding:20px;margin-bottom:24px;}
        .filter-title{font-size:0.82rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
        .filter-input{background:#181818;border:1px solid #252525;color:#fff;border-radius:10px;padding:10px 14px;font-size:0.875rem;width:100%;transition:all 0.2s;}
        .filter-input:focus{outline:none;border-color:#d4a843;box-shadow:0 0 0 3px rgba(212,168,67,0.08);}
        .filter-input::placeholder{color:#333;}
        .filter-select{background:#181818;border:1px solid #252525;color:#fff;border-radius:10px;padding:10px 14px;font-size:0.875rem;width:100%;cursor:pointer;}
        .filter-select:focus{outline:none;border-color:#d4a843;}
        .filter-select option{background:#181818;}
        .btn-filter{background:#d4a843;color:#000;font-weight:700;border:none;border-radius:10px;padding:10px 20px;font-size:0.875rem;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:6px;}
        .btn-filter:hover{background:#c89a30;}
        .btn-clear-filter{background:rgba(255,255,255,0.05);border:1px solid #252525;color:#aaa;border-radius:10px;padding:10px 16px;font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s;}
        .btn-clear-filter:hover{color:#fff;border-color:#333;}
        .results-info{font-size:0.82rem;color:#555;margin-bottom:16px;}
        .results-info strong{color:#d4a843;}

        /* ALERTA ÉXITO */
        .alert-success-custom{background:linear-gradient(135deg,rgba(212,168,67,0.08),rgba(0,204,106,0.04));border:1px solid rgba(212,168,67,0.25);border-radius:16px;padding:20px 24px;margin-bottom:28px;display:flex;align-items:center;gap:16px;}
        .alert-icon{width:48px;height:48px;border-radius:12px;background:rgba(212,168,67,0.1);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}

        /* PEDIDO CARD */
        .pedido-card{background:#111111;border:1px solid #252525;border-radius:16px;overflow:hidden;margin-bottom:16px;transition:all 0.2s;}
        .pedido-card:hover{border-color:#2a2a3e;}
        .pedido-header{padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;cursor:pointer;}
        .pedido-header:hover{background:rgba(255,255,255,0.01);}
        .pedido-num{font-size:0.78rem;color:#555;margin-bottom:2px;}
        .pedido-fecha{font-size:0.875rem;font-weight:600;}
        .pedido-total{font-size:1.3rem;font-weight:800;color:#d4a843;}
        .status-badge{font-size:0.72rem;font-weight:700;padding:5px 12px;border-radius:8px;}
        .status-Pendiente{background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);}
        .status-Pagado{background:rgba(59,130,246,0.1);color:#3b82f6;border:1px solid rgba(59,130,246,0.25);}
        .status-Entregado{background:rgba(212,168,67,0.1);color:#d4a843;border:1px solid rgba(212,168,67,0.25);}
        .toggle-icon{color:#444;transition:transform 0.3s;font-size:1rem;}
        .pedido-items{border-top:1px solid #252525;padding:20px 24px;display:none;}
        .pedido-items.show{display:block;}

        /* TIMELINE */
        .status-timeline{display:flex;align-items:center;margin-bottom:20px;}
        .timeline-step{display:flex;flex-direction:column;align-items:center;flex:1;}
        .timeline-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;border:2px solid #252525;background:#0a0a14;color:#444;}
        .timeline-dot.done{background:rgba(212,168,67,0.1);border-color:#d4a843;color:#d4a843;}
        .timeline-dot.active{background:rgba(245,158,11,0.1);border-color:#f59e0b;color:#f59e0b;}
        .timeline-label{font-size:0.65rem;color:#444;margin-top:6px;text-align:center;}
        .timeline-label.done{color:#d4a843;}
        .timeline-label.active{color:#f59e0b;}
        .timeline-line{flex:1;height:2px;background:#252525;margin-top:-20px;}
        .timeline-line.done{background:#d4a843;}

        /* ITEMS */
        .item-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #111;}
        .item-row:last-child{border-bottom:none;}
        .item-img{width:52px;height:52px;border-radius:10px;background:#181818;display:flex;align-items:center;justify-content:center;font-size:1.3rem;overflow:hidden;border:1px solid #252525;flex-shrink:0;}
        .item-img img{width:100%;height:100%;object-fit:cover;}
        .item-name{font-size:0.875rem;font-weight:700;}
        .item-detail{font-size:0.75rem;color:#555;margin-top:2px;}
        .item-price{margin-left:auto;font-weight:700;color:#d4a843;font-size:0.9rem;white-space:nowrap;}

        /* RESUMEN PEDIDO */
        .pedido-summary{background:#181818;border-radius:12px;padding:16px;margin-top:16px;}
        .summary-row{display:flex;justify-content:space-between;font-size:0.82rem;margin-bottom:8px;color:#555;}
        .summary-row.total{font-size:1rem;font-weight:800;color:#fff;border-top:1px solid #252525;padding-top:10px;margin-top:4px;}
        .summary-row.total .val{color:#d4a843;}

        /* EMPTY */
        .empty-state{text-align:center;padding:80px 20px;}
        .empty-state h3{font-size:1.3rem;font-weight:700;margin-bottom:8px;color:#666;}
        .btn-shop{background:#d4a843;color:#000;font-weight:700;border-radius:12px;padding:12px 28px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;}
        .btn-shop:hover{background:#c89a30;color:#000;}

        @media(max-width:768px){.stats-row{grid-template-columns:1fr;}}
    </style>
</head>
<body>

<nav class="navbar">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="productos.php" class="nav-brand">Gamer<span>Zone</span></a>
            <div class="d-flex align-items-center gap-2">
                <a href="favoritos.php" class="nav-icon-btn">
                    <i class="bi bi-heart"></i>
                    <span class="d-none d-md-inline">Favoritos</span>
                    <?php if($cant_favoritos > 0): ?><span class="nav-badge fav"><?= $cant_favoritos ?></span><?php endif; ?>
                </a>
                <a href="carrito.php" class="nav-icon-btn">
                    <i class="bi bi-cart3"></i>
                    <span class="d-none d-md-inline">Carrito</span>
                    <?php if($cant_carrito > 0): ?><span class="nav-badge"><?= $cant_carrito ?></span><?php endif; ?>
                </a>
                <a href="historial.php" class="nav-icon-btn" style="color:#d4a843;">
                    <i class="bi bi-bag-check"></i>
                    <span class="d-none d-md-inline">Pedidos</span>
                </a>
                <a href="perfil.php" class="user-chip d-none d-lg-flex" style="display:flex;align-items:center;gap:8px;background:rgba(212,168,67,0.06);border:1px solid rgba(212,168,67,0.15);border-radius:20px;padding:6px 14px;font-size:0.82rem;text-decoration:none;color:#fff;">
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

<div class="content">
    <div class="container">
        <div class="mb-4">
            <h1 class="page-title">Mis <span>Pedidos</span></h1>
            <p style="color:#555;font-size:0.875rem;">Historial completo de tus compras en GamerZone</p>
        </div>

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(212,168,67,0.1);"><i class="bi bi-bag-check" style="color:#d4a843;font-size:1.2rem;"></i></div>
                <div>
                    <div class="stat-num" style="color:#d4a843;"><?= $total_todas ?></div>
                    <div class="stat-label">Pedidos totales</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(59,130,246,0.1);"><i class="bi bi-cash-coin" style="color:#3b82f6;font-size:1.2rem;"></i></div>
                <div>
                    <div class="stat-num" style="color:#3b82f6;font-size:1.2rem;">Bs. <?= number_format($total_gastado,0) ?></div>
                    <div class="stat-label">Total invertido</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:rgba(168,85,247,0.1);"><i class="bi bi-award" style="color:#a855f7;font-size:1.2rem;"></i></div>
                <div>
                    <div class="stat-num" style="color:#a855f7;"><?= $total_todas >= 10 ? 'Gold' : ($total_todas >= 5 ? 'Silver' : ($total_todas >= 1 ? 'Bronze' : 'New')) ?></div>
                    <div class="stat-label">Nivel de cliente</div>
                </div>
            </div>
        </div>

        <!-- ALERTA ÉXITO -->
        <?php if($compra_exitosa): ?>
        <div class="alert-success-custom">
            <div class="alert-icon"><i class="bi bi-check-circle-fill" style="color:#d4a843;font-size:1.4rem;"></i></div>
            <div>
                <h5 style="color:#d4a843;font-weight:700;margin-bottom:2px;">¡Compra realizada exitosamente!</h5>
                <p style="color:#555;font-size:0.82rem;margin:0;">Tu pedido #<?= $compra_exitosa ?> ha sido registrado y está siendo procesado.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- FILTROS -->
        <div class="filter-card">
            <div class="filter-title"><i class="bi bi-funnel" style="color:#d4a843"></i> Filtrar pedidos</div>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Buscar producto</label>
                    <div class="position-relative">
                        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#444;font-size:0.85rem;"></i>
                        <input type="text" name="buscar" class="filter-input" style="padding-left:36px;" placeholder="Nombre o marca..." value="<?= htmlspecialchars($buscar) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Estado</label>
                    <select name="estado" class="filter-select">
                        <option value="">Todos</option>
                        <option value="Pendiente" <?= $estado_f=='Pendiente'?'selected':'' ?>>Pendiente</option>
                        <option value="Pagado" <?= $estado_f=='Pagado'?'selected':'' ?>>Pagado</option>
                        <option value="Entregado" <?= $estado_f=='Entregado'?'selected':'' ?>>Entregado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Desde</label>
                    <input type="date" name="fecha_desde" class="filter-input" value="<?= $fecha_desde ?>">
                </div>
                <div class="col-md-2">
                    <label style="font-size:0.75rem;color:#555;margin-bottom:6px;display:block;">Hasta</label>
                    <input type="date" name="fecha_hasta" class="filter-input" value="<?= $fecha_hasta ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Buscar</button>
                    <?php if($buscar || $estado_f || $fecha_desde || $fecha_hasta): ?>
                    <a href="historial.php" class="btn-clear-filter"><i class="bi bi-x-lg"></i> Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="results-info">
            Mostrando <strong><?= $total_ventas ?></strong> pedido<?= $total_ventas!=1?'s':'' ?>
            <?php if($buscar): ?> con "<strong style="color:#fff"><?= htmlspecialchars($buscar) ?></strong>"<?php endif; ?>
            <?php if($estado_f): ?> · Estado: <strong style="color:#fff"><?= $estado_f ?></strong><?php endif; ?>
            <?php if($fecha_desde || $fecha_hasta): ?> · Período: <strong style="color:#fff"><?= $fecha_desde ?: '...' ?> — <?= $fecha_hasta ?: 'hoy' ?></strong><?php endif; ?>
        </div>

        <!-- PEDIDOS -->
        <?php if($total_ventas === 0): ?>
        <div class="empty-state">
            <i class="bi bi-inbox" style="font-size:4rem;margin-bottom:20px;opacity:0.2;display:block;"></i>
            <h3><?= ($buscar || $estado_f || $fecha_desde || $fecha_hasta) ? 'No se encontraron pedidos' : 'Aún no tienes pedidos' ?></h3>
            <p style="color:#444;font-size:0.875rem;margin-bottom:24px;">
                <?= ($buscar || $estado_f || $fecha_desde || $fecha_hasta) ? 'Intenta con otros filtros' : 'Cuando realices tu primera compra aparecerá aquí' ?>
            </p>
            <?php if($buscar || $estado_f || $fecha_desde || $fecha_hasta): ?>
            <a href="historial.php" class="btn-shop"><i class="bi bi-arrow-left"></i> Ver todos</a>
            <?php else: ?>
            <a href="productos.php" class="btn-shop"><i class="bi bi-grid"></i> Ir a la Tienda</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <?php while($venta = $ventas->fetch_assoc()):
            $estados = ['Pendiente'=>1,'Pagado'=>2,'Entregado'=>3];
            $paso = $estados[$venta['estado_venta']] ?? 1;

            // Detalles
            $det = $conn->prepare("SELECT dv.*,p.nombre,p.marca,p.imagen,p.precio as precio_unit FROM detalle_venta dv JOIN producto p ON dv.id_producto=p.id_producto WHERE dv.id_venta=?");
            $det->bind_param("i", $venta['id_venta']);
            $det->execute();
            $detalles = $det->get_result();
            $det_items = [];
            while($d = $detalles->fetch_assoc()) $det_items[] = $d;
        ?>
        <div class="pedido-card">
            <div class="pedido-header" onclick="togglePedido(<?= $venta['id_venta'] ?>)">
                <div>
                    <div class="pedido-num">Pedido #<?= $venta['id_venta'] ?></div>
                    <div class="pedido-fecha"><?= date('d \d\e F, Y — H:i', strtotime($venta['fecha'])) ?></div>
                    <div style="font-size:0.75rem;color:#444;margin-top:4px;">
                        <i class="bi bi-box me-1"></i><?= count($det_items) ?> producto<?= count($det_items)!=1?'s':'' ?>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <?php
                    $pay_labels = ['qr'=>['QR Bolivia','bi-qr-code'],'tarjeta'=>['Tarjeta','bi-credit-card'],'transferencia'=>['Transferencia','bi-bank'],'tigo_money'=>['Tigo Money','bi-phone'],'paypal'=>['PayPal','bi-paypal'],'mercadopago'=>['MercadoPago','bi-wallet2'],'stripe'=>['Stripe','bi-lightning-charge'],'payu'=>['PayU','bi-globe'],'payoneer'=>['Payoneer','bi-send'],'wise'=>['Wise','bi-arrow-left-right']];
                    $mp = $venta['metodo_pago'] ?? 'qr';
                    $pl = $pay_labels[$mp] ?? ['Otro','bi-cash-coin'];
                    ?>
                    <span style="display:inline-flex;align-items:center;gap:5px;background:rgba(212,168,67,.07);border:1px solid rgba(212,168,67,.15);border-radius:8px;padding:4px 10px;font-size:0.72rem;color:#d4a843;">
                        <i class="bi <?= $pl[1] ?>"></i><?= $pl[0] ?>
                    </span>
                    <span class="status-badge status-<?= $venta['estado_venta'] ?>">
                        <?= $venta['estado_venta'] ?>
                    </span>
                    <span class="pedido-total">Bs. <?= number_format($venta['total'],2) ?></span>
                    <i class="bi bi-chevron-down toggle-icon" id="icon-<?= $venta['id_venta'] ?>"></i>
                </div>
            </div>

            <div class="pedido-items" id="items-<?= $venta['id_venta'] ?>">
                <!-- TIMELINE -->
                <div class="status-timeline mb-4">
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso>=1?'done':'' ?>"><i class="bi bi-receipt"></i></div>
                        <div class="timeline-label <?= $paso>=1?'done':'' ?>">Registrado</div>
                    </div>
                    <div class="timeline-line <?= $paso>=2?'done':'' ?>"></div>
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso>=2?'done':($paso==2?'active':'') ?>"><i class="bi bi-credit-card"></i></div>
                        <div class="timeline-label <?= $paso>=2?'done':'' ?>">Pagado</div>
                    </div>
                    <div class="timeline-line <?= $paso>=3?'done':'' ?>"></div>
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso>=3?'done':'' ?>"><i class="bi bi-truck"></i></div>
                        <div class="timeline-label <?= $paso>=3?'done':'' ?>">En camino</div>
                    </div>
                    <div class="timeline-line <?= $paso>=3?'done':'' ?>"></div>
                    <div class="timeline-step">
                        <div class="timeline-dot <?= $paso>=3?'done':'' ?>"><i class="bi bi-house-check"></i></div>
                        <div class="timeline-label <?= $paso>=3?'done':'' ?>">Entregado</div>
                    </div>
                </div>

                <!-- PRODUCTOS -->
                <?php foreach($det_items as $d):
                    $img = imgSrc($d['imagen']);
                ?>
                <div class="item-row">
                    <div class="item-img">
                        <?php if($img): ?>
                            <img src="<?= $img ?>" alt="">
                        <?php else: ?><i class="bi bi-box" style="font-size:2rem;opacity:0.3;"></i><?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="item-name"><?= htmlspecialchars($d['nombre']) ?></div>
                        <div class="item-detail">
                            <?= htmlspecialchars($d['marca']) ?> ·
                            <?= $d['cantidad'] ?> unidad<?= $d['cantidad']>1?'es':'' ?> ×
                            Bs. <?= number_format($d['precio_unit'],2) ?>
                        </div>
                    </div>
                    <div class="item-price">Bs. <?= number_format($d['subtotal'],2) ?></div>
                </div>
                <?php endforeach; ?>

                <!-- RESUMEN -->
                <div class="pedido-summary">
                    <div class="summary-row"><span>Subtotal</span><span class="val">Bs. <?= number_format($venta['total'],2) ?></span></div>
                    <div class="summary-row"><span>Envío</span><span style="color:#d4a843;">Gratis</span></div>
                    <div class="summary-row total"><span>Total pagado</span><span class="val">Bs. <?= number_format($venta['total'],2) ?></span></div>
                </div>

                <!-- INFO PEDIDO -->
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px;">
                    <div style="background:#181818;border-radius:10px;padding:12px;">
                        <div style="font-size:0.7rem;color:#444;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Número de pedido</div>
                        <div style="font-weight:700;font-size:0.875rem;">#<?= $venta['id_venta'] ?></div>
                    </div>
                    <div style="background:#181818;border-radius:10px;padding:12px;">
                        <div style="font-size:0.7rem;color:#444;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Fecha</div>
                        <div style="font-weight:700;font-size:0.875rem;"><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></div>
                    </div>
                    <div style="background:#181818;border-radius:10px;padding:12px;">
                        <div style="font-size:0.7rem;color:#444;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Estado</div>
                        <div style="font-weight:700;font-size:0.875rem;color:<?= $venta['estado_venta']==='Entregado'?'#d4a843':($venta['estado_venta']==='Pagado'?'#3b82f6':'#f59e0b') ?>"><?= $venta['estado_venta'] ?></div>
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
<?php if($compra_exitosa): ?>
togglePedido(<?= $compra_exitosa ?>);
<?php endif; ?>
</script>
</body>
</html>