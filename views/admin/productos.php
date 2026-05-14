<?php
session_start();
if(!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

function imgSrc($img, $prefix='../../assets/') {
    if(!$img) return null;
    return (strpos($img,'http')===0) ? $img : $prefix.$img;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];

    if($accion === 'crear' || $accion === 'editar') {
        $nombre = trim($_POST['nombre']);
        $marca  = trim($_POST['marca']);
        $desc   = trim($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $stock  = intval($_POST['stock']);
        $id_cat = intval($_POST['id_categoria']);
        $estado = isset($_POST['estado']) ? 1 : 0;
        $imagen = '';

        if(!empty($_FILES['imagen']['name'])) {
            $ext    = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $imagen = 'img/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], '../../assets/' . $imagen);
        }

        if($accion === 'crear') {
            $stmt = $conn->prepare("INSERT INTO producto (id_categoria,nombre,marca,descripcion,precio,stock,imagen,estado) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssdisi", $id_cat,$nombre,$marca,$desc,$precio,$stock,$imagen,$estado);
            $stmt->execute();
            $_SESSION['success'] = "Producto <strong>$nombre</strong> creado correctamente.";
        } else {
            $id = intval($_POST['id_producto']);
            if($imagen) {
                $stmt = $conn->prepare("UPDATE producto SET id_categoria=?,nombre=?,marca=?,descripcion=?,precio=?,stock=?,imagen=?,estado=? WHERE id_producto=?");
                $stmt->bind_param("isssdisii",$id_cat,$nombre,$marca,$desc,$precio,$stock,$imagen,$estado,$id);
            } else {
                $stmt = $conn->prepare("UPDATE producto SET id_categoria=?,nombre=?,marca=?,descripcion=?,precio=?,stock=?,estado=? WHERE id_producto=?");
                $stmt->bind_param("isssdiii",$id_cat,$nombre,$marca,$desc,$precio,$stock,$estado,$id);
            }
            $stmt->execute();
            $_SESSION['success'] = "Producto actualizado correctamente.";
        }
    }

    if($accion === 'eliminar') {
        $id   = intval($_POST['id_producto']);
        $stmt = $conn->prepare("DELETE FROM producto WHERE id_producto=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['success'] = "Producto eliminado correctamente.";
    }

    header('Location: productos.php'); exit;
}

$productos     = $conn->query("SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria=c.id_categoria ORDER BY p.id_producto DESC");
$categorias    = $conn->query("SELECT * FROM categoria ORDER BY nombre_categoria");
$total_prods   = $conn->query("SELECT COUNT(*) as t FROM producto")->fetch_assoc()['t'];
$total_activos = $conn->query("SELECT COUNT(*) as t FROM producto WHERE estado=1")->fetch_assoc()['t'];
$stock_bajo    = $conn->query("SELECT COUNT(*) as t FROM producto WHERE stock<=5 AND estado=1")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - GamerZone Admin</title>
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
        .btn-gamer{background:#00ff88;color:#000;font-weight:700;border:none;border-radius:10px;padding:10px 20px;font-size:0.875rem;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
        .btn-gamer:hover{background:#00cc6a;transform:translateY(-1px);box-shadow:0 4px 15px rgba(0,255,136,0.2);}
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
        .prod-img-table{width:46px;height:46px;border-radius:10px;background:#151520;display:flex;align-items:center;justify-content:center;font-size:1.4rem;overflow:hidden;border:1px solid #1a1a2e;flex-shrink:0;}
        .prod-img-table img{width:100%;height:100%;object-fit:cover;}
        .badge-cat{background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.15);color:#00ff88;border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .stock-ok{background:rgba(0,255,136,0.08);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .stock-low{background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#f59e0b;border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .stock-out{background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#ef4444;border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .estado-on{background:rgba(0,255,136,0.08);color:#00ff88;border:1px solid rgba(0,255,136,0.2);border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .estado-off{background:rgba(100,100,100,0.08);color:#555;border:1px solid #1a1a2e;border-radius:6px;padding:3px 10px;font-size:0.72rem;font-weight:600;}
        .icon-btns{display:flex;gap:6px;}
        .icon-btn{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid;cursor:pointer;transition:all 0.2s;font-size:0.82rem;background:transparent;}
        .icon-btn-edit{border-color:rgba(245,158,11,0.3);color:#f59e0b;}
        .icon-btn-edit:hover{background:rgba(245,158,11,0.1);}
        .icon-btn-del{border-color:rgba(239,68,68,0.3);color:#ef4444;}
        .icon-btn-del:hover{background:rgba(239,68,68,0.1);}
        .alert-ok{background:rgba(0,255,136,0.06);border:1px solid rgba(0,255,136,0.2);color:#00ff88;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
        .alert-err{background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);color:#ef4444;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
        .modal-content{background:#0d0d1a;border:1px solid #1a1a2e;border-radius:16px;color:#fff;}
        .modal-header{border-bottom:1px solid #1a1a2e;padding:20px 24px;}
        .modal-footer{border-top:1px solid #1a1a2e;padding:16px 24px;}
        .modal-body{padding:24px;}
        .form-label{font-size:0.82rem;font-weight:600;color:#aaa;margin-bottom:6px;}
        .form-control,.form-select{background:#111120;border:1px solid #1a1a2e;color:#fff;border-radius:10px;padding:10px 14px;font-size:0.875rem;transition:border-color 0.2s;width:100%;}
        .form-control:focus,.form-select:focus{background:#111120;border-color:#00ff88;color:#fff;box-shadow:0 0 0 3px rgba(0,255,136,0.08);outline:none;}
        .form-control::placeholder{color:#333;}
        .form-select option{background:#111120;}
        .form-check-input:checked{background-color:#00ff88;border-color:#00ff88;}
        .btn-cancel{background:rgba(255,255,255,0.05);border:1px solid #1a1a2e;color:#aaa;border-radius:10px;padding:10px 20px;font-size:0.875rem;cursor:pointer;transition:all 0.2s;}
        .btn-cancel:hover{background:rgba(255,255,255,0.08);color:#fff;}
        .btn-warning-c{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);color:#f59e0b;border-radius:10px;padding:10px 20px;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .btn-warning-c:hover{background:rgba(245,158,11,0.2);}
        .btn-danger-c{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);color:#ef4444;border-radius:10px;padding:10px 20px;font-size:0.875rem;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .btn-danger-c:hover{background:rgba(239,68,68,0.2);}
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
        <a href="productos.php" class="sidebar-link active"><i class="bi bi-box-seam"></i> Productos</a>
        <a href="categorias.php" class="sidebar-link"><i class="bi bi-tags"></i> Categorías</a>
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

<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title"><i class="bi bi-box-seam" style="color:#00ff88"></i> <span>Productos</span></div>
            <div class="breadcrumb-nav"><a href="dashboard.php">Dashboard</a> / Productos</div>
        </div>
        <button class="btn-gamer" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </button>
    </div>

    <div class="content">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert-ok"><i class="bi bi-check-circle-fill"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert-err"><i class="bi bi-exclamation-circle-fill"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,255,136,0.1);">📦</div>
                <div><div class="mini-stat-num"><?= $total_prods ?></div><div class="mini-stat-label">Total productos</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(59,130,246,0.1);">✅</div>
                <div><div class="mini-stat-num" style="color:#3b82f6"><?= $total_activos ?></div><div class="mini-stat-label">Activos</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(245,158,11,0.1);">⚠️</div>
                <div><div class="mini-stat-num" style="color:#f59e0b"><?= $stock_bajo ?></div><div class="mini-stat-label">Stock bajo (≤5)</div></div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(168,85,247,0.1);">🏷️</div>
                <div><div class="mini-stat-num" style="color:#a855f7"><?= $conn->query("SELECT COUNT(*) as t FROM categoria")->fetch_assoc()['t'] ?></div><div class="mini-stat-label">Categorías</div></div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <div class="table-title"><i class="bi bi-box-seam" style="color:#00ff88;margin-right:8px;"></i>Catálogo de Productos</div>
                <div class="d-flex align-items-center gap-3">
                    <input type="text" class="search-box" id="searchProd" placeholder="🔍  Buscar producto...">
                    <span style="font-size:0.78rem;color:#555;"><?= $total_prods ?> producto<?= $total_prods!=1?'s':'' ?></span>
                </div>
            </div>
            <table id="tablaProductos">
                <thead>
                    <tr>
                        <th>#</th><th>Imagen</th><th>Producto</th><th>Categoría</th>
                        <th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($p = $productos->fetch_assoc()):
                    $img = imgSrc($p['imagen']);
                ?>
                <tr>
                    <td style="color:#444;font-size:0.78rem;"><?= $p['id_producto'] ?></td>
                    <td>
                        <div class="prod-img-table">
                            <?php if($img): ?>
                                <img src="<?= $img ?>" alt="">
                            <?php else: ?>📦<?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($p['nombre']) ?></div>
                        <div style="font-size:0.72rem;color:#555;margin-top:2px;"><?= htmlspecialchars($p['marca']) ?></div>
                    </td>
                    <td><span class="badge-cat"><?= htmlspecialchars($p['nombre_categoria']) ?></span></td>
                    <td><strong style="color:#00ff88;">Bs. <?= number_format($p['precio'],2) ?></strong></td>
                    <td>
                        <?php if($p['stock']>10): ?><span class="stock-ok"><?= $p['stock'] ?> und.</span>
                        <?php elseif($p['stock']>0): ?><span class="stock-low"><?= $p['stock'] ?> und.</span>
                        <?php else: ?><span class="stock-out">Agotado</span><?php endif; ?>
                    </td>
                    <td><?= $p['estado'] ? '<span class="estado-on">Activo</span>' : '<span class="estado-off">Inactivo</span>' ?></td>
                    <td>
                        <div class="icon-btns">
                            <button class="icon-btn icon-btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditar"
                                data-id="<?= $p['id_producto'] ?>"
                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                data-marca="<?= htmlspecialchars($p['marca']) ?>"
                                data-desc="<?= htmlspecialchars($p['descripcion']) ?>"
                                data-precio="<?= $p['precio'] ?>"
                                data-stock="<?= $p['stock'] ?>"
                                data-cat="<?= $p['id_categoria'] ?>"
                                data-estado="<?= $p['estado'] ?>" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="icon-btn icon-btn-del" data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                data-id="<?= $p['id_producto'] ?>"
                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>" title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#00ff88;font-weight:700;"><i class="bi bi-plus-circle me-2"></i>Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre del producto *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej: Laptop Gamer ROG" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-control" placeholder="Ej: ASUS, Sony...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría *</label>
                            <select name="id_categoria" class="form-select" required>
                                <?php $categorias->data_seek(0); while($c=$categorias->fetch_assoc()): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (Bs.) *</label>
                            <input type="number" name="precio" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe el producto..."></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="estado" id="estadoCrear" checked>
                                <label class="form-check-label" for="estadoCrear" style="font-size:0.875rem;">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-gamer"><i class="bi bi-check-lg me-1"></i>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color:#f59e0b;font-weight:700;"><i class="bi bi-pencil me-2"></i>Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_producto" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" id="editMarca" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <select name="id_categoria" id="editCat" class="form-select">
                                <?php $categorias->data_seek(0); while($c=$categorias->fetch_assoc()): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (Bs.)</label>
                            <input type="number" name="precio" id="editPrecio" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="editStock" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nueva imagen <span style="color:#555;font-weight:400;">(opcional)</span></label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="estado" id="editEstado">
                                <label class="form-check-label" for="editEstado" style="font-size:0.875rem;">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-warning-c"><i class="bi bi-check-lg me-1"></i>Guardar Cambios</button>
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
                <h5 class="modal-title" style="color:#ef4444;font-weight:700;"><i class="bi bi-trash me-2"></i>Eliminar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="text-align:center;padding:32px 24px;">
                <div style="font-size:3.5rem;margin-bottom:16px;">🗑️</div>
                <p style="font-size:0.95rem;margin-bottom:8px;">¿Eliminar el producto</p>
                <p><strong id="elimNombre" style="color:#ef4444;font-size:1.05rem;"></strong>?</p>
                <p style="color:#555;font-size:0.82rem;margin-top:12px;">Esta acción no se puede deshacer.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_producto" id="elimId">
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-danger-c"><i class="bi bi-trash me-1"></i>Sí, Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEditar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value    = btn.dataset.id;
    document.getElementById('editNombre').value = btn.dataset.nombre;
    document.getElementById('editMarca').value  = btn.dataset.marca;
    document.getElementById('editDesc').value   = btn.dataset.desc;
    document.getElementById('editPrecio').value = btn.dataset.precio;
    document.getElementById('editStock').value  = btn.dataset.stock;
    document.getElementById('editCat').value    = btn.dataset.cat;
    document.getElementById('editEstado').checked = btn.dataset.estado == 1;
});
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('elimId').value = btn.dataset.id;
    document.getElementById('elimNombre').textContent = btn.dataset.nombre;
});
document.getElementById('searchProd').addEventListener('input', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#tablaProductos tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>
</body>
</html>