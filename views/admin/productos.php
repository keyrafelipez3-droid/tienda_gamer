<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];

    if ($accion === 'crear' || $accion === 'editar') {
        $nombre = trim($_POST['nombre']);
        $marca = trim($_POST['marca']);
        $desc = trim($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $stock = intval($_POST['stock']);
        $id_cat = intval($_POST['id_categoria']);
        $estado = isset($_POST['estado']) ? 1 : 0;
        $imagen = '';

        if (!empty($_FILES['imagen']['name'])) {
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $imagen = 'img/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], '../../assets/' . $imagen);
        }

        if ($accion === 'crear') {
            $stmt = $conn->prepare("INSERT INTO producto (id_categoria, nombre, marca, descripcion, precio, stock, imagen, estado) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssdisi", $id_cat, $nombre, $marca, $desc, $precio, $stock, $imagen, $estado);
            $stmt->execute();
        } else {
            $id = intval($_POST['id_producto']);
            if ($imagen) {
                $stmt = $conn->prepare("UPDATE producto SET id_categoria=?, nombre=?, marca=?, descripcion=?, precio=?, stock=?, imagen=?, estado=? WHERE id_producto=?");
                $stmt->bind_param("isssdisii", $id_cat, $nombre, $marca, $desc, $precio, $stock, $imagen, $estado, $id);
            } else {
                $stmt = $conn->prepare("UPDATE producto SET id_categoria=?, nombre=?, marca=?, descripcion=?, precio=?, stock=?, estado=? WHERE id_producto=?");
                $stmt->bind_param("isssdiii", $id_cat, $nombre, $marca, $desc, $precio, $stock, $estado, $id);
            }
            $stmt->execute();
        }
    }

    if ($accion === 'eliminar') {
        $id = intval($_POST['id_producto']);
        $stmt = $conn->prepare("DELETE FROM producto WHERE id_producto=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header('Location: productos.php');
    exit;
}

$productos = $conn->query("SELECT p.*, c.nombre_categoria FROM producto p JOIN categoria c ON p.id_categoria = c.id_categoria ORDER BY p.id_producto DESC");
$categorias = $conn->query("SELECT * FROM categoria ORDER BY nombre_categoria");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Admin GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .nav-link { color: #ccc !important; }
        .nav-link:hover, .nav-link.active { color: #00ff88 !important; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .card-admin { background: #111; border: 1px solid #222; border-radius: 16px; }
        .table { color: #fff; }
        .table thead th { color: #00ff88; border-color: #222; background: #0d0d0d; }
        .table td { border-color: #1a1a1a; vertical-align: middle; }
        .table tbody tr:hover { background: #161616; }
        .modal-content { background: #111; border: 1px solid #333; color: #fff; }
        .modal-header { border-bottom: 1px solid #222; }
        .modal-footer { border-top: 1px solid #222; }
        .form-control, .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: none; }
        .form-select option { background: #1a1a1a; }
        .producto-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #1a1a1a; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .badge-stock-ok { background: #0d2a0d; color: #00ff88; border: 1px solid #00ff88; border-radius: 6px; padding: 2px 8px; font-size: 0.75rem; }
        .badge-stock-low { background: #2a1a0d; color: #ffa500; border: 1px solid #ffa500; border-radius: 6px; padding: 2px 8px; font-size: 0.75rem; }
        .badge-stock-out { background: #2a0d0d; color: #ff4444; border: 1px solid #ff4444; border-radius: 6px; padding: 2px 8px; font-size: 0.75rem; }
        .page-title { color: #00ff88; font-weight: 800; }
        .alert-success-custom { background: #0d2a0d; border: 1px solid #00ff88; color: #00ff88; border-radius: 10px; padding: 12px 16px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link active"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link"><i class="bi bi-cart"></i> Ventas</a>
            <a href="usuarios.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <?php if(isset($_GET['ok'])): ?>
    <div class="alert-success-custom mb-3"><i class="bi bi-check-circle me-2"></i>
        <?= $_GET['ok'] === 'crear' ? 'Producto creado' : ($_GET['ok'] === 'editar' ? 'Producto actualizado' : 'Producto eliminado') ?> correctamente.
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title mb-0"><i class="bi bi-box"></i> Gestión de Productos</h3>
        <button class="btn btn-gamer" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </button>
    </div>

    <div class="card-admin p-0 overflow-hidden">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Img</th><th>Nombre</th><th>Marca</th>
                    <th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $p['id_producto'] ?></td>
                    <td>
                        <?php if ($p['imagen']): ?>
                            <img src="../../assets/<?= $p['imagen'] ?>" class="producto-img">
                        <?php else: ?>
                            <div class="producto-img">📦</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                    <td class="text-muted"><?= htmlspecialchars($p['marca']) ?></td>
                    <td><span style="color:#00ff88;font-size:0.85rem;"><?= htmlspecialchars($p['nombre_categoria']) ?></span></td>
                    <td><strong class="text-success">Bs. <?= number_format($p['precio'], 2) ?></strong></td>
                    <td>
                        <?php if ($p['stock'] > 10): ?>
                            <span class="badge-stock-ok"><?= $p['stock'] ?> und.</span>
                        <?php elseif ($p['stock'] > 0): ?>
                            <span class="badge-stock-low"><?= $p['stock'] ?> und.</span>
                        <?php else: ?>
                            <span class="badge-stock-out">Agotado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['estado']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                            data-id="<?= $p['id_producto'] ?>"
                            data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                            data-marca="<?= htmlspecialchars($p['marca']) ?>"
                            data-desc="<?= htmlspecialchars($p['descripcion']) ?>"
                            data-precio="<?= $p['precio'] ?>"
                            data-stock="<?= $p['stock'] ?>"
                            data-cat="<?= $p['id_categoria'] ?>"
                            data-estado="<?= $p['estado'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                            data-id="<?= $p['id_producto'] ?>"
                            data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Nuevo Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre del producto</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <select name="id_categoria" class="form-select" required>
                                <?php $categorias->data_seek(0); while ($c = $categorias->fetch_assoc()): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (Bs.)</label>
                            <input type="number" name="precio" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="estado" id="estadoCrear" checked>
                                <label class="form-check-label" for="estadoCrear">Producto activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gamer">Guardar producto</button>
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
                <h5 class="modal-title text-warning">Editar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_producto" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="editNombre" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Marca</label>
                            <input type="text" name="marca" id="editMarca" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoría</label>
                            <select name="id_categoria" id="editCat" class="form-select">
                                <?php $categorias->data_seek(0); while ($c = $categorias->fetch_assoc()): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= $c['nombre_categoria'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Precio (Bs.)</label>
                            <input type="number" name="precio" id="editPrecio" class="form-control" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="editStock" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" id="editDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nueva imagen (opcional)</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="estado" id="editEstado">
                                <label class="form-check-label" for="editEstado">Producto activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
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
                <h5 class="modal-title text-danger">Eliminar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar el producto <strong id="elimNombre" class="text-danger"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_producto" id="elimId">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
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
    document.getElementById('editMarca').value = btn.dataset.marca;
    document.getElementById('editDesc').value = btn.dataset.desc;
    document.getElementById('editPrecio').value = btn.dataset.precio;
    document.getElementById('editStock').value = btn.dataset.stock;
    document.getElementById('editCat').value = btn.dataset.cat;
    document.getElementById('editEstado').checked = btn.dataset.estado == 1;
});
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('elimId').value = btn.dataset.id;
    document.getElementById('elimNombre').textContent = btn.dataset.nombre;
});
</script>
</body>
</html>