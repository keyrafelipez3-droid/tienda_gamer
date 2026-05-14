<?php
session_start();
if(!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id_venta = intval($_POST['id_venta']);
    $estado   = $_POST['estado_venta'];
    $stmt = $conn->prepare("UPDATE venta SET estado_venta=? WHERE id_venta=?");
    $stmt->bind_param("si", $estado, $id_venta);
    $stmt->execute();
    $_SESSION['success'] = "Estado de la venta #$id_venta actualizado a <strong>$estado</strong>.";
    header('Location: ventas.php'); exit;
}

$ventas = $conn->query("SELECT v.*, u.nombre as cliente, u.correo FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario ORDER BY v.fecha DESC");
$total_ventas    = $conn->query("SELECT COUNT(*) as t FROM venta")->fetch_assoc()['t'];
$ventas_hoy      = $conn->query("SELECT COUNT(*) as t FROM venta WHERE DATE(fecha)=CURDATE()")->fetch_assoc()['t'];
$ingresos_total  = $conn->query("SELECT SUM(total) as s FROM venta")->fetch_assoc()['s'] ?? 0;
$pendientes      = $conn->query("SELECT COUNT(*) as t FROM venta WHERE estado_venta='Pendiente'")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - GamerZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#070711;color:#fff;font-family:'Inter',sans-serif;}
        .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:#0d0d1a;border-right:1px solid #1a1a2e;display:flex;flex-direction:column;z-index:100;}
        .sidebar-brand{padding:24px 20px;border-bottom:1px solid #1a1a2e;}
        .brand-name{font-size:1.5rem;font-weight:800;color:#00ff88;}
        .brand-name span{color:#fff;}
        .brand-role{font-size:0.75rem;color:#555;margin-top:4px;}
        .brand-role.super{color:#a855f7;}
        .sidebar-nav{padding:16px 0;flex:1;overflow-y:auto;}
        .nav-section{padding:8px 20px 4px;font-size:0.65rem;text-transform:uppercase;letter-spacing:2px;color:#444;font-weight:600;}
        .sidebar-link{display:flex;align-items:center;gap:12px;padding:11px 20px;color:#666;font-size:0.9rem;font-weight:500;text-decoration:none;transition:all 0.2s;border-left:3px solid transparent;}
        .sidebar-link:hover{color:#fff;background:rgba(255,255,255,0.04);}
        .sidebar-link.active{color:#00ff88;background:rgba(0,255,136,0.06);border-left-color:#00ff88;}
        .sidebar-link i{font-size:1rem;width:20px;}
        .badge-count{margin-left:auto;background:#f59e0b;color:#000;font-size:0.65rem;padding:2px 6px;border-radius:10px;font-weight:700;}
        .sidebar-footer{padding:16px 20px;border-top:1px solid #1a1a2e;}
        .user-info{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
        .user-av{width:34px;height:34px;border-radius:8px;background:rgba(0,255,136,0.1);border:1px solid rgba(0,255,136,0.2);display:flex;align-items:center;justify-content:center;color:#00ff88;font-weight:700;font-size:0.85rem;}
        .user-name{font-size:0.82rem;font-weight:600;}
        .user-role{font-size:0.7rem;color:#555;}
        .btn-logout{background:rgba(255,68,68,0.1);border:1px solid rgba(255,68,68,0.2);color:#ff6b6b;border-radius:8px;padding:8px 16px;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all 0.2s;width:100%;}
        .btn-logout:hover{background:rgba(255,68,68,0.2);}
        .main{margin-left:260px;min-height:100vh;}
        .topbar{background:#0d0d1a;border-bottom:1px solid #1a1a2e;padding:18px 32px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:50;}
        .topbar-title{font-size:1.1rem;font-weight:700;}
        .topbar-title span{color:#00ff88;}
        .breadcrumb-nav{font-size:0.75rem;color:#444;margin-top:2px;}
        .breadcrumb-nav a{color:#555;text-decoration:none;}
        .breadcrumb-nav a:hover{color:#00ff88;}
        .content{padding:32px;}
        .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
        .mini-stat{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:14px;padding:20px;display:flex;align-items:center;gap:16px;}
        .mini-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
        .mini-stat-num{font-size:1.6rem;font-weight:800;color:#00ff88;}
        .mini-stat-label{font-size:0.78rem;color:#555;}
        .table-card{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:16px;overflow:hidden;}
        .table-header{padding:20px 24px;border-bottom:1px solid #1a1a2e;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;}
        .table-title{font-size:0.95rem;font-weight:700;}
        .search-box{background:#111120;border:1px solid #1a1a2e;border-radius:8px;padding:8px 14px;color:#fff;font-size:0.82rem;width:220px;}
        .search-box:focus{outline:none;border-color:#00ff88;}
        .search-box::placeholder{color:#333;}
        table{width:100%;border-collapse:collapse;}
        thead th{padding:12px 20px;font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;color:#444;font-weight:600;border-bottom:1px solid #1a1a2e;background:#0a0a14;text-align:left;white-space:nowrap;}
        tbody td{padding:14px 20px;border-bottom:1px solid #0f0f1f;font-size:0.875rem;vertical-align:middle;}
        tbody tr:hover{background:rgba(255,255,255,0.02);}
        tbody tr:last-child td{border-bottom:none;}
        .status-Pendiente{background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.2);border-radius:6px;padding:4px 10px;font-size:0.72rem;font-weight:600;}
        .status-Pagado{background:rgba(59,130,246,0.1);color:#3b82f6;border:1px solid rgba(59,130,246,0.2);border-radius:6px;padding:4px 10px;font-size:0.72rem;font-weight:600;}
        .status-Entregado{background:rgba(0,255,136,0.1);color:#00ff88;border:1px solid rgba(0,255,136,0.2);border-radius:6px;padding:4px 10px;font-size:0.72rem;font-weight:600;}
        .icon-btn{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid;cursor:pointer;transition:all 0.2s;font-size:0.82rem;background:transparent;}
        .icon-btn-info{border-color:rgba(59,130,246,0.3);color:#3b82f6;}
        .icon-btn-info:hover{background:rgba(59,130,246,0.1);}
        .icon-btn-edit{border-color:rgba(245,158,11,0.3);color:#f59e0b;}
        .icon-btn-edit:hover{background:rgba(245,158,11,0.1);}
        .alert-ok{background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
        .modal-content{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:16px;color:#fff;}
        .modal-header{border-bottom:1px solid #1a1a2e;padding:20px 24px;}
        .modal-footer{border-top:1px solid #1a1a2e;padding:16px 24px;}
        .modal-body{padding:24px;}
        .form-label{font-size:0.82rem;font-weight:600;color:#aaa;margin-bottom:6px;}
        .form-select{background:#111120;border:1px solid #1a1a2e;color:#fff;border-radius:10px;padding:10px 14px;font-size:0.875rem;width:100%;}
        .form-select:focus{background:#111120;border-color:#00ff88;color:#fff;box-shadow:0 0 0 3px rgba(0,255,136,0.08);outline:none;}
        .form-select option{background:#111120;}
        .btn-cancel{background:rgba(255,255,255,0.05);border:1px solid #1a1a2e;color:#aaa;border-radius:10px;padding:10px 20px;font-size:0.875rem;cursor:pointer;transition:all 0.2s;}
        .btn-cancel:hover{background:rgba(255,255,255,0.08);color:#fff;}
        .btn-warning-c{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#f59e0b;border-radius:10px;padding:10px 20px;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .btn-warning-c:hover{background:rgba(245,158,11,0.2);}
        .detalle-item{background:#111120;border-radius:10px;padding:12px 16px;margin-bottom:8px;display:flex;align-items:center;gap:12px;}
        .detalle-img{width:42px;height:42px;border-radius:8px;background:#0d0d1a;display:flex;align-items:center;justify-content:center;font-size:1.3rem;overflow:hidden;border:1px solid #1a1a2e;flex-shrink:0;}
        .detalle-img img{width:100%;height:100%;object-fit:cover;}
        ::-webkit-scrollbar{width:4px;}
        ::-webkit-scrollbar-thumb{background:#1a1a2e;border-radius:2px;}
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name">Gamer<span>Zone</span></div>
        <div class="brand-role <?= $es_super?'super':'' ?>"><?= $es_super?'⭐ Super Administrador':'👤 Administrador' ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <div class="nav-section">Gestión</div>
        <a href="productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
        <a href="categorias.php" class="sidebar-link"><i class="bi bi-tags"></i> Categorías</a>
        <a href="ventas.php" class="sidebar-link active">
            <i class="bi bi-bag"></i> Ventas
            <?php if($pendientes > 0): ?><span class="badge-count"><?= $pendientes ?></span><?php endif; ?>
        </a>
        <a href="usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
        <div class="nav-section">Sistema</div>
        <a href="../../index.php" class="sidebar-link"><i class="bi bi-globe"></i> Ver Tienda</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-av"><?= strtoupper(substr($_SESSION['usuario_nombre'],0,1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                <div class="user-role"><?= ucfirst(str_replace('_',' ',$_SESSION['usuario_rol'])) ?></div>
            </div>
        </div>
        <form action="../../controllers/auth_controller.php" method="POST">
            <input type="hidden" name="action" value="logout">
            <button class="btn-logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</button>
        </form>
    </div>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title"><i class="bi bi-bag" style="color:#00ff88"></i> <span>Ventas</span></div>
            <div class="breadcrumb-nav"><a href="dashboard.php">Dashboard</a> / Ventas</div>
        </div>
        <?php if($pendientes > 0): ?>
        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;padding:8px 14px;font-size:0.8rem;color:#f59e0b;">
            <i class="bi bi-clock me-1"></i><?= $pendientes ?> venta<?= $pendientes>1?'s':'' ?> pendiente<?= $pendientes>1?'s':'' ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="content">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,255,136,0.1);">🛒</div>
                <div><div class="mini-stat-num"><?= $total_ventas ?></div><div class="mini-stat-label">Ventas totales</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(59,130,246,0.1);">📅</div>
                <div><div class="mini-stat-num" style="color:#3b82f6"><?= $ventas_hoy ?></div><div class="mini-stat-label">Ventas hoy</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(245,158,11,0.1);">⏳</div>
                <div><div class="mini-stat-num" style="color:#f59e0b"><?= $pendientes ?></div><div class="mini-stat-label">Pendientes</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,255,136,0.1);">💰</div>
                <div><div class="mini-stat-num">Bs.<?= number_format($ingresos_total,0) ?></div><div class="mini-stat-label">Ingresos totales</div></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="table-title"><i class="bi bi-bag" style="color:#00ff88;margin-right:8px;"></i>Registro de Ventas</div>
                <div class="d-flex align-items-center gap-3">
                    <input type="text" class="search-box" id="searchVenta" placeholder="🔍  Buscar venta...">
                    <span style="font-size:0.78rem;color:#555;"><?= $total_ventas ?> venta<?= $total_ventas!=1?'s':'' ?></span>
                </div>
            </div>
            <table id="tablaVentas">
                <thead>
                    <tr>
                        <th>Pedido</th><th>Cliente</th><th>Fecha</th>
                        <th>Total</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($ventas->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center;color:#444;padding:48px;">No hay ventas registradas aún</td></tr>
                <?php else: while($v = $ventas->fetch_assoc()): ?>
                <tr>
                    <td>
                        <span style="color:#555;font-size:0.78rem;">#<?= $v['id_venta'] ?></span>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?= htmlspecialchars($v['cliente']) ?></div>
                        <div style="font-size:0.72rem;color:#444;"><?= htmlspecialchars($v['correo']) ?></div>
                    </td>
                    <td style="color:#555;font-size:0.8rem;"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <td><strong style="color:#00ff88;">Bs. <?= number_format($v['total'],2) ?></strong></td>
                    <td><span class="status-<?= $v['estado_venta'] ?>"><?= $v['estado_venta'] ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="icon-btn icon-btn-info"
                                data-bs-toggle="modal" data-bs-target="#modalDetalle"
                                data-id="<?= $v['id_venta'] ?>"
                                data-cliente="<?= htmlspecialchars($v['cliente']) ?>"
                                data-fecha="<?= date('d/m/Y H:i', strtotime($v['fecha'])) ?>"
                                data-total="<?= number_format($v['total'],2) ?>"
                                data-estado="<?= $v['estado_venta'] ?>"
                                title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="icon-btn icon-btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEstado"
                                data-id="<?= $v['id_venta'] ?>"
                                data-estado="<?= $v['estado_venta'] ?>"
                                title="Cambiar estado">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#3b82f6;font-weight:700;"><i class="bi bi-receipt me-2"></i>Detalle del Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="background:#111120;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-size:0.72rem;color:#555;text-transform:uppercase;letter-spacing:1px;">Cliente</div>
                        <div id="detCliente" style="font-weight:700;font-size:1rem;margin-top:2px;"></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:#555;text-transform:uppercase;letter-spacing:1px;">Fecha</div>
                        <div id="detFecha" style="font-weight:600;margin-top:2px;"></div>
                    </div>
                    <div>
                        <div style="font-size:0.72rem;color:#555;text-transform:uppercase;letter-spacing:1px;">Estado</div>
                        <div id="detEstado" style="margin-top:4px;"></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:0.72rem;color:#555;text-transform:uppercase;letter-spacing:1px;">Total</div>
                        <div id="detTotal" style="font-size:1.4rem;font-weight:800;color:#00ff88;margin-top:2px;"></div>
                    </div>
                </div>
                <div style="font-size:0.82rem;color:#555;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;">Productos del pedido</div>
                <div id="detProductos"><div style="text-align:center;color:#444;padding:24px;">Cargando...</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#f59e0b;font-weight:700;"><i class="bi bi-arrow-repeat me-2"></i>Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="cambiar_estado" value="1">
                <input type="hidden" name="id_venta" id="estadoId">
                <div class="modal-body">
                    <label class="form-label">Nuevo estado de la venta</label>
                    <select name="estado_venta" id="estadoSelect" class="form-select" style="margin-top:8px;">
                        <option value="Pendiente">⏳ Pendiente</option>
                        <option value="Pagado">💳 Pagado</option>
                        <option value="Entregado">✅ Entregado</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-warning-c"><i class="bi bi-check-lg me-1"></i>Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEstado').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('estadoId').value = btn.dataset.id;
    document.getElementById('estadoSelect').value = btn.dataset.estado;
});

document.getElementById('modalDetalle').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('detCliente').textContent = btn.dataset.cliente;
    document.getElementById('detFecha').textContent = btn.dataset.fecha;
    document.getElementById('detTotal').textContent = 'Bs. ' + btn.dataset.total;
    const estados = {Pendiente:'#f59e0b', Pagado:'#3b82f6', Entregado:'#00ff88'};
    const col = estados[btn.dataset.estado] || '#888';
    document.getElementById('detEstado').innerHTML = `<span style="background:${col}18;color:${col};border:1px solid ${col}40;border-radius:6px;padding:3px 10px;font-size:0.78rem;font-weight:600;">${btn.dataset.estado}</span>`;
    document.getElementById('detProductos').innerHTML = '<div style="text-align:center;color:#444;padding:24px;">Cargando...</div>';
    fetch('get_detalle_venta.php?id=' + btn.dataset.id)
        .then(r => r.text())
        .then(html => document.getElementById('detProductos').innerHTML = html);
});

document.getElementById('searchVenta').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#tablaVentas tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
</body>
</html>