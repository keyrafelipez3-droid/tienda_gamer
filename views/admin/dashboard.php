<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

$total_productos = $conn->query("SELECT COUNT(*) as total FROM producto")->fetch_assoc()['total'];
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuario WHERE rol='cliente'")->fetch_assoc()['total'];
$total_ventas = $conn->query("SELECT COUNT(*) as total FROM venta")->fetch_assoc()['total'];
$total_categorias = $conn->query("SELECT COUNT(*) as total FROM categoria")->fetch_assoc()['total'];
$ingresos = $conn->query("SELECT SUM(total) as suma FROM venta")->fetch_assoc()['suma'] ?? 0;
$ventas_hoy = $conn->query("SELECT COUNT(*) as total FROM venta WHERE DATE(fecha)=CURDATE()")->fetch_assoc()['total'];
$stock_bajo = $conn->query("SELECT COUNT(*) as total FROM producto WHERE stock <= 5 AND estado=1")->fetch_assoc()['total'];

// Productos más vendidos
$top_productos = $conn->query("SELECT p.nombre, p.imagen, p.marca, SUM(dv.cantidad) as vendidos, SUM(dv.subtotal) as ingresos FROM detalle_venta dv JOIN producto p ON dv.id_producto=p.id_producto GROUP BY dv.id_producto ORDER BY vendidos DESC LIMIT 5");

// Ventas recientes
$ventas_recientes = $conn->query("SELECT v.*, u.nombre as cliente, u.correo FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario ORDER BY v.fecha DESC LIMIT 8");

// Últimos usuarios registrados
$nuevos_usuarios = $conn->query("SELECT * FROM usuario WHERE rol='cliente' ORDER BY fecha_registro DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GamerZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #080808; color: #fff; font-family: 'Inter', sans-serif; min-height: 100vh; }

        /* SIDEBAR */
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: #111111; border-right: 1px solid #252525; padding: 0; z-index: 100; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid #252525; }
        .sidebar-brand .brand-name { font-size: 1.5rem; font-weight: 800; color: #d4a843; letter-spacing: 1px; }
        .sidebar-brand .brand-name span { color: #fff; }
        .sidebar-brand .brand-role { font-size: 0.75rem; color: #555; margin-top: 4px; }
        .sidebar-brand .brand-role.super { color: #a855f7; }
        .sidebar-nav { padding: 16px 0; flex: 1; }
        .nav-section { padding: 8px 20px 4px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 2px; color: #444; font-weight: 600; }
        .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: #666; font-size: 0.9rem; font-weight: 500; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; margin: 2px 0; }
        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,0.04); }
        .sidebar-link.active { color: #d4a843; background: rgba(212,168,67,0.06); border-left-color: #d4a843; }
        .sidebar-link i { font-size: 1rem; width: 20px; }
        .sidebar-link .badge-count { margin-left: auto; background: #ff4444; color: #fff; font-size: 0.65rem; padding: 2px 6px; border-radius: 10px; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid #252525; }
        .user-info { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 10px; background: rgba(212,168,67,0.15); border: 1px solid rgba(212,168,67,0.3); display: flex; align-items: center; justify-content: center; color: #d4a843; font-weight: 700; font-size: 0.9rem; }
        .user-name { font-size: 0.85rem; font-weight: 600; }
        .user-role { font-size: 0.7rem; color: #555; }

        /* MAIN */
        .main { margin-left: 260px; min-height: 100vh; }
        .topbar { background: #111111; border-bottom: 1px solid #252525; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .topbar-title span { color: #d4a843; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .btn-logout { background: rgba(255,68,68,0.1); border: 1px solid rgba(255,68,68,0.2); color: #ff6b6b; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-logout:hover { background: rgba(255,68,68,0.2); }
        .content { padding: 32px; }

        /* SUPER BANNER */
        .super-banner { background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(124,58,237,0.05)); border: 1px solid rgba(168,85,247,0.2); border-radius: 16px; padding: 16px 24px; margin-bottom: 28px; display: flex; align-items: center; gap: 16px; }
        .super-icon { width: 44px; height: 44px; background: rgba(168,85,247,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }

        /* STAT CARDS */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px; }
        .stat-card { background: #111111; border: 1px solid #252525; border-radius: 16px; padding: 24px; position: relative; overflow: hidden; transition: all 0.3s; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; }
        .stat-card.green::before { background: linear-gradient(90deg, #d4a843, transparent); }
        .stat-card.blue::before { background: linear-gradient(90deg, #3b82f6, transparent); }
        .stat-card.purple::before { background: linear-gradient(90deg, #a855f7, transparent); }
        .stat-card.orange::before { background: linear-gradient(90deg, #f59e0b, transparent); }
        .stat-card.red::before { background: linear-gradient(90deg, #ef4444, transparent); }
        .stat-card:hover { border-color: #2a2a3e; transform: translateY(-2px); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 16px; }
        .stat-icon.green { background: rgba(212,168,67,0.1); }
        .stat-icon.blue { background: rgba(59,130,246,0.1); }
        .stat-icon.purple { background: rgba(168,85,247,0.1); }
        .stat-icon.orange { background: rgba(245,158,11,0.1); }
        .stat-icon.red { background: rgba(239,68,68,0.1); }
        .stat-num { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 6px; }
        .stat-num.green { color: #d4a843; }
        .stat-num.blue { color: #3b82f6; }
        .stat-num.purple { color: #a855f7; }
        .stat-num.orange { color: #f59e0b; }
        .stat-num.red { color: #ef4444; }
        .stat-label { font-size: 0.82rem; color: #555; font-weight: 500; }
        .stat-badge { position: absolute; top: 16px; right: 16px; font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; font-weight: 600; }
        .stat-badge.up { background: rgba(212,168,67,0.1); color: #d4a843; }
        .stat-badge.warn { background: rgba(245,158,11,0.1); color: #f59e0b; }

        /* SECTION CARDS */
        .section-card { background: #111111; border: 1px solid #252525; border-radius: 16px; overflow: hidden; margin-bottom: 24px; }
        .section-header { padding: 20px 24px; border-bottom: 1px solid #252525; display: flex; justify-content: space-between; align-items: center; }
        .section-title { font-size: 0.95rem; font-weight: 700; }
        .section-title i { color: #d4a843; margin-right: 8px; }
        .section-link { font-size: 0.8rem; color: #d4a843; text-decoration: none; font-weight: 500; }
        .section-link:hover { color: #c89a30; }

        /* TABLE */
        .dash-table { width: 100%; }
        .dash-table th { padding: 12px 24px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #444; font-weight: 600; border-bottom: 1px solid #252525; background: #0a0a14; }
        .dash-table td { padding: 14px 24px; border-bottom: 1px solid #111; font-size: 0.875rem; }
        .dash-table tbody tr:hover { background: rgba(255,255,255,0.02); }
        .dash-table tbody tr:last-child td { border-bottom: none; }

        /* BADGES */
        .status-badge { font-size: 0.72rem; padding: 4px 10px; border-radius: 6px; font-weight: 600; }
        .status-Pendiente { background: rgba(245,158,11,0.1); color: #f59e0b; border: 1px solid rgba(245,158,11,0.2); }
        .status-Pagado { background: rgba(59,130,246,0.1); color: #3b82f6; border: 1px solid rgba(59,130,246,0.2); }
        .status-Entregado { background: rgba(212,168,67,0.1); color: #d4a843; border: 1px solid rgba(212,168,67,0.2); }

        /* TOP PRODUCTOS */
        .prod-rank { display: flex; align-items: center; gap: 14px; padding: 16px 24px; border-bottom: 1px solid #111; transition: background 0.2s; }
        .prod-rank:hover { background: rgba(255,255,255,0.02); }
        .prod-rank:last-child { border-bottom: none; }
        .rank-num { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0; }
        .rank-1 { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .rank-2 { background: rgba(156,163,175,0.1); color: #9ca3af; }
        .rank-3 { background: rgba(180,83,9,0.1); color: #b45309; }
        .rank-other { background: rgba(255,255,255,0.05); color: #555; }
        .prod-img-small { width: 40px; height: 40px; border-radius: 8px; background: #151520; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; overflow: hidden; flex-shrink: 0; }
        .prod-img-small img { width: 100%; height: 100%; object-fit: cover; }
        .prod-info { flex: 1; min-width: 0; }
        .prod-info .name { font-size: 0.875rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .prod-info .brand { font-size: 0.75rem; color: #555; }
        .prod-stats { text-align: right; }
        .prod-stats .sold { font-size: 0.875rem; font-weight: 700; color: #d4a843; }
        .prod-stats .revenue { font-size: 0.75rem; color: #555; }

        /* USUARIOS RECIENTES */
        .user-row { display: flex; align-items: center; gap: 12px; padding: 14px 24px; border-bottom: 1px solid #111; }
        .user-row:last-child { border-bottom: none; }
        .user-av { width: 36px; height: 36px; border-radius: 10px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.2); display: flex; align-items: center; justify-content: center; color: #6366f1; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; }
        .user-row .name { font-size: 0.875rem; font-weight: 600; }
        .user-row .email { font-size: 0.75rem; color: #555; }
        .user-row .date { margin-left: auto; font-size: 0.75rem; color: #444; }

        /* INGRESO BOX */
        .ingreso-hero { background: linear-gradient(135deg, #0a1f0a, #080808); border: 1px solid rgba(212,168,67,0.15); border-radius: 16px; padding: 28px; margin-bottom: 24px; }
        .ingreso-label { font-size: 0.8rem; color: #555; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .ingreso-amount { font-size: 3rem; font-weight: 800; color: #d4a843; line-height: 1; }
        .ingreso-sub { font-size: 0.8rem; color: #444; margin-top: 8px; }

        /* QUICK ACTIONS */
        .quick-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
        .quick-card { background: #111111; border: 1px solid #252525; border-radius: 14px; padding: 20px; text-align: center; text-decoration: none; color: #fff; transition: all 0.2s; }
        .quick-card:hover { border-color: #d4a843; color: #d4a843; transform: translateY(-2px); background: rgba(212,168,67,0.03); }
        .quick-card i { font-size: 1.8rem; display: block; margin-bottom: 10px; }
        .quick-card .q-title { font-size: 0.875rem; font-weight: 600; }
        .quick-card .q-sub { font-size: 0.75rem; color: #444; margin-top: 4px; }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #252525; border-radius: 2px; }

        @media (max-width: 1200px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .quick-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name">Gamer<span>Zone</span></div>
        <div class="brand-role <?= $es_super ? 'super' : '' ?>">
            <?= $es_super ? 'Super Administrador' : 'Administrador' ?>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="dashboard.php" class="sidebar-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <div class="nav-section">Gestión</div>
        <a href="productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
        <a href="categorias.php" class="sidebar-link"><i class="bi bi-tags"></i> Categorías</a>
        <a href="ventas.php" class="sidebar-link">
            <i class="bi bi-bag"></i> Ventas
            <?php if($ventas_hoy > 0): ?>
            <span class="badge-count"><?= $ventas_hoy ?></span>
            <?php endif; ?>
        </a>
        <a href="usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
        <div class="nav-section">Sistema</div>
        <a href="../../index.php" class="sidebar-link"><i class="bi bi-globe"></i> Ver Tienda</a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['usuario_nombre'],0,1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                <div class="user-role"><?= ucfirst(str_replace('_',' ',$_SESSION['usuario_rol'])) ?></div>
            </div>
        </div>
        <form action="../../controllers/auth_controller.php" method="POST">
            <input type="hidden" name="action" value="logout">
            <button class="btn-logout w-100"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</button>
        </form>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Panel de <span>Administración</span></div>
            <div style="font-size:0.75rem;color:#444;margin-top:2px;"><?= date('l, d \d\e F Y') ?></div>
        </div>
        <div class="topbar-right">
            <?php if($stock_bajo > 0): ?>
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:8px 14px;font-size:0.8rem;color:#ef4444;">
                <i class="bi bi-exclamation-triangle me-1"></i><?= $stock_bajo ?> producto<?= $stock_bajo>1?'s':'' ?> con stock bajo
            </div>
            <?php endif; ?>
            <div style="font-size:0.8rem;color:#555;">Bienvenido, <strong style="color:#fff"><?= $_SESSION['usuario_nombre'] ?></strong></div>
        </div>
    </div>

    <div class="content">

        <?php if($es_super): ?>
        <div class="super-banner">
            <div class="super-icon"><i class="bi bi-star-fill" style="color:#a855f7;font-size:1.3rem;"></i></div>
            <div>
                <div style="color:#a855f7;font-weight:700;font-size:0.9rem;">Modo Super Administrador Activo</div>
                <div style="color:#555;font-size:0.8rem;margin-top:2px;">Tienes acceso total al sistema — crear admins, gestionar roles y eliminar usuarios.</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stat-grid">
            <div class="stat-card green">
                <span class="stat-badge up">Total</span>
                <div class="stat-icon green"><i class="bi bi-box-seam" style="color:#d4a843"></i></div>
                <div class="stat-num green"><?= $total_productos ?></div>
                <div class="stat-label">Productos en catálogo</div>
            </div>
            <div class="stat-card blue">
                <span class="stat-badge up">Activos</span>
                <div class="stat-icon blue"><i class="bi bi-people" style="color:#3b82f6"></i></div>
                <div class="stat-num blue"><?= $total_usuarios ?></div>
                <div class="stat-label">Clientes registrados</div>
            </div>
            <div class="stat-card purple">
                <span class="stat-badge up">Hoy: <?= $ventas_hoy ?></span>
                <div class="stat-icon purple"><i class="bi bi-bag-check" style="color:#a855f7"></i></div>
                <div class="stat-num purple"><?= $total_ventas ?></div>
                <div class="stat-label">Ventas realizadas</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-icon orange"><i class="bi bi-tags" style="color:#f59e0b"></i></div>
                <div class="stat-num orange"><?= $total_categorias ?></div>
                <div class="stat-label">Categorías activas</div>
            </div>
        </div>

        <!-- INGRESOS -->
        <div class="ingreso-hero">
            <div class="ingreso-label"><i class="bi bi-cash-coin me-1"></i> Ingresos Totales</div>
            <div class="ingreso-amount">Bs. <?= number_format($ingresos, 2) ?></div>
            <div class="ingreso-sub">Suma acumulada de todas las ventas registradas en el sistema</div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-grid">
            <a href="productos.php" class="quick-card">
                <i class="bi bi-plus-circle"></i>
                <div class="q-title">Nuevo Producto</div>
                <div class="q-sub">Agregar al catálogo</div>
            </a>
            <a href="categorias.php" class="quick-card">
                <i class="bi bi-folder-plus"></i>
                <div class="q-title">Nueva Categoría</div>
                <div class="q-sub">Organizar productos</div>
            </a>
            <a href="ventas.php" class="quick-card">
                <i class="bi bi-receipt"></i>
                <div class="q-title">Ver Ventas</div>
                <div class="q-sub"><?= $ventas_hoy ?> hoy</div>
            </a>
            <a href="usuarios.php" class="quick-card">
                <i class="bi bi-person-badge"></i>
                <div class="q-title">Gestionar Usuarios</div>
                <div class="q-sub"><?= $total_usuarios ?> clientes</div>
            </a>
        </div>

        <div class="row g-4">
            <!-- VENTAS RECIENTES -->
            <div class="col-lg-7">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title"><i class="bi bi-bag"></i> Ventas Recientes</div>
                        <a href="ventas.php" class="section-link">Ver todas <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($ventas_recientes->num_rows === 0): ?>
                            <tr><td colspan="5" style="text-align:center;color:#444;padding:32px;">No hay ventas aún</td></tr>
                            <?php else: ?>
                            <?php while($v = $ventas_recientes->fetch_assoc()): ?>
                            <tr>
                                <td><span style="color:#555;font-size:0.8rem;">#<?= $v['id_venta'] ?></span></td>
                                <td>
                                    <div style="font-weight:600;"><?= htmlspecialchars($v['cliente']) ?></div>
                                    <div style="font-size:0.72rem;color:#444;"><?= htmlspecialchars($v['correo']) ?></div>
                                </td>
                                <td style="color:#555;font-size:0.8rem;"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                                <td><strong style="color:#d4a843;">Bs. <?= number_format($v['total'],2) ?></strong></td>
                                <td><span class="status-badge status-<?= $v['estado_venta'] ?>"><?= $v['estado_venta'] ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TOP PRODUCTOS -->
            <div class="col-lg-5">
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title"><i class="bi bi-trophy"></i> Productos Más Vendidos</div>
                    </div>
                    <?php
                    $rank = 1;
                    if($top_productos->num_rows === 0):
                    ?>
                    <div style="padding:32px;text-align:center;color:#444;">Sin datos de ventas aún</div>
                    <?php else: ?>
                    <?php while($p = $top_productos->fetch_assoc()): ?>
                    <div class="prod-rank">
                        <div class="rank-num rank-<?= $rank <= 3 ? $rank : 'other' ?>">#<?= $rank ?></div>
                        <div class="prod-img-small">
                            <?php if($p['imagen']): ?>
                                <img src="../../assets/<?= $p['imagen'] ?>" alt="">
                            <?php else: ?>
                                <i class="bi bi-box" style="font-size:1.5rem;opacity:0.3;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="prod-info">
                            <div class="name"><?= htmlspecialchars($p['nombre']) ?></div>
                            <div class="brand"><?= htmlspecialchars($p['marca']) ?></div>
                        </div>
                        <div class="prod-stats">
                            <div class="sold"><?= $p['vendidos'] ?> und.</div>
                            <div class="revenue">Bs. <?= number_format($p['ingresos'],0) ?></div>
                        </div>
                    </div>
                    <?php $rank++; endwhile; ?>
                    <?php endif; ?>
                </div>

                <!-- ÚLTIMOS USUARIOS -->
                <div class="section-card mt-4">
                    <div class="section-header">
                        <div class="section-title"><i class="bi bi-person-plus"></i> Nuevos Clientes</div>
                        <a href="usuarios.php" class="section-link">Ver todos <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <?php while($u = $nuevos_usuarios->fetch_assoc()): ?>
                    <div class="user-row">
                        <div class="user-av"><?= strtoupper(substr($u['nombre'],0,1)) ?></div>
                        <div>
                            <div class="name"><?= htmlspecialchars($u['nombre']) ?></div>
                            <div class="email"><?= htmlspecialchars($u['correo']) ?></div>
                        </div>
                        <div class="date"><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>