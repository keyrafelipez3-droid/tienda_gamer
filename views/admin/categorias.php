<?php
session_start();
if(!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    if($accion === 'crear') {
        $nombre = trim($_POST['nombre_categoria']);
        $desc = trim($_POST['descripcion']);
        $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?,?)");
        $stmt->bind_param("ss", $nombre, $desc);
        $stmt->execute();
        $_SESSION['success'] = "Categoría <strong>$nombre</strong> creada correctamente.";
    }
    if($accion === 'editar') {
        $id = $_POST['id_categoria'];
        $nombre = trim($_POST['nombre_categoria']);
        $desc = trim($_POST['descripcion']);
        $stmt = $conn->prepare("UPDATE categoria SET nombre_categoria=?, descripcion=? WHERE id_categoria=?");
        $stmt->bind_param("ssi", $nombre, $desc, $id);
        $stmt->execute();
        $_SESSION['success'] = "Categoría actualizada correctamente.";
    }
    if($accion === 'eliminar') {
        $id = $_POST['id_categoria'];
        $check = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE id_categoria=?");
        $check->bind_param("i", $id);
        $check->execute();
        $total = $check->get_result()->fetch_assoc()['total'];
        if($total > 0) {
            $_SESSION['error'] = "No puedes eliminar esta categoría porque tiene <strong>$total producto(s)</strong> asociado(s).";
        } else {
            $stmt = $conn->prepare("DELETE FROM categoria WHERE id_categoria=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['success'] = "Categoría eliminada correctamente.";
        }
    }
    header('Location: categorias.php'); exit;
}

$categorias = $conn->query("SELECT c.*, COUNT(p.id_producto) as total_productos FROM categoria c LEFT JOIN producto p ON c.id_categoria=p.id_categoria GROUP BY c.id_categoria ORDER BY c.id_categoria DESC");
$total_cats = $conn->query("SELECT COUNT(*) as total FROM categoria")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - GamerZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #070711; color: #fff; font-family: 'Inter', sans-serif; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: #0d0d1a; border-right: 1px solid #1a1a2e; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { padding: 24px 20px; border-bottom: 1px solid #1a1a2e; }
        .brand-name { font-size: 1.5rem; font-weight: 800; color: #00ff88; }
        .brand-name span { color: #fff; }
        .brand-role { font-size: 0.75rem; color: #555; margin-top: 4px; }
        .brand-role.super { color: #a855f7; }
        .sidebar-nav { padding: 16px 0; flex: 1; overflow-y: auto; }
        .nav-section { padding: 8px 20px 4px; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 2px; color: #444; font-weight: 600; }
        .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: #666; font-size: 0.9rem; font-weight: 500; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; }
        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,0.04); }
        .sidebar-link.active { color: #00ff88; background: rgba(0,255,136,0.06); border-left-color: #00ff88; }
        .sidebar-link i { font-size: 1rem; width: 20px; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid #1a1a2e; }
        .user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .user-av { width: 34px; height: 34px; border-radius: 8px; background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.2); display: flex; align-items: center; justify-content: center; color: #00ff88; font-weight: 700; font-size: 0.85rem; }
        .user-name { font-size: 0.82rem; font-weight: 600; }
        .user-role { font-size: 0.7rem; color: #555; }
        .btn-logout { background: rgba(255,68,68,0.1); border: 1px solid rgba(255,68,68,0.2); color: #ff6b6b; border-radius: 8px; padding: 8px 16px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; width: 100%; }
        .btn-logout:hover { background: rgba(255,68,68,0.2); }
        .main { margin-left: 260px; min-height: 100vh; }
        .topbar { background: #0d0d1a; border-bottom: 1px solid #1a1a2e; padding: 18px 32px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 1.1rem; font-weight: 700; }
        .topbar-title span { color: #00ff88; }
        .breadcrumb-nav { font-size: 0.75rem; color: #444; margin-top: 2px; }
        .breadcrumb-nav a { color: #555; text-decoration: none; }
        .breadcrumb-nav a:hover { color: #00ff88; }
        .content { padding: 32px; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 10px; padding: 10px 20px; font-size: 0.875rem; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
        .btn-gamer:hover { background: #00cc6a; transform: translateY(-1px); box-shadow: 0 4px 15px rgba(0,255,136,0.2); }

        /* STATS TOP */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
        .mini-stat { background: #0d0d1a; border: 1px solid #1a1a2e; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .mini-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .mini-stat-num { font-size: 1.6rem; font-weight: 800; color: #00ff88; }
        .mini-stat-label { font-size: 0.78rem; color: #555; }

        /* TABLE CARD */
        .table-card { background: #0d0d1a; border: 1px solid #1a1a2e; border-radius: 16px; overflow: hidden; }
        .table-header { padding: 20px 24px; border-bottom: 1px solid #1a1a2e; display: flex; justify-content: space-between; align-items: center; }
        .table-title { font-size: 0.95rem; font-weight: 700; }
        .table-title i { color: #00ff88; margin-right: 8px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { padding: 12px 24px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; color: #444; font-weight: 600; border-bottom: 1px solid #1a1a2e; background: #0a0a14; text-align: left; }
        tbody td { padding: 16px 24px; border-bottom: 1px solid #0f0f1f; font-size: 0.875rem; }
        tbody tr:hover { background: rgba(255,255,255,0.02); }
        tbody tr:last-child td { border-bottom: none; }

        /* CAT BADGE */
        .cat-icon-box { width: 40px; height: 40px; border-radius: 10px; background: rgba(0,255,136,0.08); border: 1px solid rgba(0,255,136,0.15); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .cat-name { font-weight: 600; font-size: 0.9rem; }
        .cat-desc { font-size: 0.78rem; color: #555; margin-top: 2px; }
        .prod-count { background: rgba(0,255,136,0.08); border: 1px solid rgba(0,255,136,0.15); color: #00ff88; border-radius: 8px; padding: 4px 12px; font-size: 0.78rem; font-weight: 600; display: inline-block; }
        .empty-state { text-align: center; padding: 60px 24px; color: #444; }
        .empty-state i { font-size: 3rem; display: block; margin-bottom: 16px; opacity: 0.3; }

        /* ALERTS */
        .alert-ok { background: rgba(0,255,136,0.06); border: 1px solid rgba(0,255,136,0.2); color: #00ff88; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-err { background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; border-radius: 12px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* MODAL */
        .modal-content { background: #0d0d1a; border: 1px solid #1a1a2e; border-radius: 16px; color: #fff; }
        .modal-header { border-bottom: 1px solid #1a1a2e; padding: 20px 24px; }
        .modal-footer { border-top: 1px solid #1a1a2e; padding: 16px 24px; }
        .modal-body { padding: 24px; }
        .form-label { font-size: 0.82rem; font-weight: 600; color: #aaa; margin-bottom: 6px; }
        .form-control, .form-select { background: #111120; border: 1px solid #1a1a2e; color: #fff; border-radius: 10px; padding: 10px 14px; font-size: 0.875rem; transition: border-color 0.2s; }
        .form-control:focus, .form-select:focus { background: #111120; border-color: #00ff88; color: #fff; box-shadow: 0 0 0 3px rgba(0,255,136,0.08); }
        .form-control::placeholder { color: #333; }
        .btn-cancel { background: rgba(255,255,255,0.05); border: 1px solid #1a1a2e; color: #aaa; border-radius: 10px; padding: 10px 20px; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
        .btn-cancel:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .btn-danger-custom { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444; border-radius: 10px; padding: 10px 20px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-danger-custom:hover { background: rgba(239,68,68,0.2); }
        .btn-warning-custom { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); color: #f59e0b; border-radius: 10px; padding: 10px 20px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-warning-custom:hover { background: rgba(245,158,11,0.2); }
        .icon-btns { display: flex; gap: 8px; }
        .icon-btn { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid; cursor: pointer; transition: all 0.2s; font-size: 0.875rem; background: transparent; }
        .icon-btn-edit { border-color: rgba(245,158,11,0.3); color: #f59e0b; }
        .icon-btn-edit:hover { background: rgba(245,158,11,0.1); }
        .icon-btn-del { border-color: rgba(239,68,68,0.3); color: #ef4444; }
        .icon-btn-del:hover { background: rgba(239,68,68,0.1); }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #1a1a2e; border-radius: 2px; }

        /* ICONS MAP */
        .cat-icons { display: grid; grid-template-columns: repeat(6,1fr); gap: 8px; }
        .cat-icon-opt { background: #111120; border: 1px solid #1a1a2e; border-radius: 8px; padding: 10px; text-align: center; font-size: 1.4rem; cursor: pointer; transition: all 0.2s; }
        .cat-icon-opt:hover, .cat-icon-opt.selected { border-color: #00ff88; background: rgba(0,255,136,0.06); }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name">Gamer<span>Zone</span></div>
        <div class="brand-role <?= $es_super ? 'super' : '' ?>">
            <?= $es_super ? '⭐ Super Administrador' : '👤 Administrador' ?>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Principal</div>
        <a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <div class="nav-section">Gestión</div>
        <a href="productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
        <a href="categorias.php" class="sidebar-link active"><i class="bi bi-tags"></i> Categorías</a>
        <a href="ventas.php" class="sidebar-link"><i class="bi bi-bag"></i> Ventas</a>
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

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title"><i class="bi bi-tags" style="color:#00ff88"></i> <span>Categorías</span></div>
            <div class="breadcrumb-nav"><a href="dashboard.php">Dashboard</a> / Categorías</div>
        </div>
        <button class="btn-gamer" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </button>
    </div>

    <div class="content">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert-ok"><i class="bi bi-check-circle-fill"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- MINI STATS -->
        <div class="stats-row">
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,255,136,0.1);">🏷️</div>
                <div>
                    <div class="mini-stat-num"><?= $total_cats ?></div>
                    <div class="mini-stat-label">Categorías totales</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(59,130,246,0.1);">📦</div>
                <div>
                    <div class="mini-stat-num" style="color:#3b82f6"><?= $conn->query("SELECT COUNT(*) as t FROM producto WHERE estado=1")->fetch_assoc()['t'] ?></div>
                    <div class="mini-stat-label">Productos activos</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(168,85,247,0.1);">📊</div>
                <div>
                    <div class="mini-stat-num" style="color:#a855f7"><?= $total_cats > 0 ? round($conn->query("SELECT COUNT(*) as t FROM producto")->fetch_assoc()['t'] / $total_cats, 1) : 0 ?></div>
                    <div class="mini-stat-label">Productos por categoría</div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <div class="table-header">
                <div class="table-title"><i class="bi bi-tags"></i> Listado de Categorías</div>
                <span style="font-size:0.78rem;color:#555;"><?= $total_cats ?> categoría<?= $total_cats!=1?'s':'' ?> registrada<?= $total_cats!=1?'s':'' ?></span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $icons = ['💻','🖥️','🖱️','⌨️','🎮','🎧','📱','🖨️','💾','🔌'];
                $i = 0;
                if($categorias->num_rows === 0): ?>
                <tr><td colspan="4"><div class="empty-state"><i class="bi bi-tags"></i>No hay categorías aún</div></td></tr>
                <?php else: while($cat = $categorias->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="cat-icon-box"><?= $icons[$i % count($icons)] ?></div>
                            <div>
                                <div class="cat-name"><?= htmlspecialchars($cat['nombre_categoria']) ?></div>
                                <div style="font-size:0.7rem;color:#444;">ID #<?= $cat['id_categoria'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="color:#666;max-width:300px;"><?= htmlspecialchars($cat['descripcion']) ?: '<span style="color:#333;font-style:italic;">Sin descripción</span>' ?></td>
                    <td><span class="prod-count"><i class="bi bi-box me-1"></i><?= $cat['total_productos'] ?> producto<?= $cat['total_productos']!=1?'s':'' ?></span></td>
                    <td>
                        <div class="icon-btns">
                            <button class="icon-btn icon-btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEditar"
                                data-id="<?= $cat['id_categoria'] ?>"
                                data-nombre="<?= htmlspecialchars($cat['nombre_categoria']) ?>"
                                data-desc="<?= htmlspecialchars($cat['descripcion']) ?>"
                                title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="icon-btn icon-btn-del"
                                data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                data-id="<?= $cat['id_categoria'] ?>"
                                data-nombre="<?= htmlspecialchars($cat['nombre_categoria']) ?>"
                                data-total="<?= $cat['total_productos'] ?>"
                                title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php $i++; endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#00ff88;font-weight:700;"><i class="bi bi-plus-circle me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Nombre de la categoría *</label>
                        <input type="text" name="nombre_categoria" class="form-control" placeholder="Ej: Laptops Gamer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe brevemente esta categoría..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-gamer"><i class="bi bi-check-lg me-1"></i>Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#f59e0b;font-weight:700;"><i class="bi bi-pencil me-2"></i>Editar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_categoria" id="editId">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Nombre de la categoría *</label>
                        <input type="text" name="nombre_categoria" id="editNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="editDesc" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-warning-custom"><i class="bi bi-check-lg me-1"></i>Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#ef4444;font-weight:700;"><i class="bi bi-trash me-2"></i>Eliminar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="text-align:center;padding:16px 0;">
                    <div style="font-size:3rem;margin-bottom:16px;">🗑️</div>
                    <p style="font-size:0.95rem;">¿Estás seguro de eliminar la categoría</p>
                    <p><strong id="elimNombre" style="color:#ef4444;font-size:1.1rem;"></strong>?</p>
                    <p id="elimWarning" style="color:#f59e0b;font-size:0.82rem;margin-top:12px;display:none;">
                        <i class="bi bi-exclamation-triangle me-1"></i>Esta categoría tiene productos asociados y no se puede eliminar.
                    </p>
                    <p style="color:#555;font-size:0.82rem;margin-top:8px;">Esta acción no se puede deshacer.</p>
                </div>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_categoria" id="elimId">
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-danger-custom"><i class="bi bi-trash me-1"></i>Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEditar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value = btn.dataset.id;
    document.getElementById('editNombre').value = btn.dataset.nombre;
    document.getElementById('editDesc').value = btn.dataset.desc;
});
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('elimId').value = btn.dataset.id;
    document.getElementById('elimNombre').textContent = btn.dataset.nombre;
    const total = parseInt(btn.dataset.total);
    document.getElementById('elimWarning').style.display = total > 0 ? 'block' : 'none';
});
</script>
</body>
</html>