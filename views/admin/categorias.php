<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

// Procesar acciones
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    
    if($accion === 'crear') {
        $nombre = trim($_POST['nombre_categoria']);
        $desc = trim($_POST['descripcion']);
        $stmt = $conn->prepare("INSERT INTO categoria (nombre_categoria, descripcion) VALUES (?,?)");
        $stmt->bind_param("ss", $nombre, $desc);
        $stmt->execute();
    }
    if($accion === 'editar') {
        $id = $_POST['id_categoria'];
        $nombre = trim($_POST['nombre_categoria']);
        $desc = trim($_POST['descripcion']);
        $stmt = $conn->prepare("UPDATE categoria SET nombre_categoria=?, descripcion=? WHERE id_categoria=?");
        $stmt->bind_param("ssi", $nombre, $desc, $id);
        $stmt->execute();
    }
    if($accion === 'eliminar') {
        $id = $_POST['id_categoria'];
        $stmt = $conn->prepare("DELETE FROM categoria WHERE id_categoria=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    header('Location: categorias.php'); exit;
}

$categorias = $conn->query("SELECT * FROM categoria ORDER BY id_categoria DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Admin GamerZone</title>
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
        .badge-cat { background: #0d1f0d; color: #00ff88; border: 1px solid #00ff88; border-radius: 8px; padding: 4px 12px; font-size: 0.8rem; }
        .page-title { color: #00ff88; font-weight: 800; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link active"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link"><i class="bi bi-cart"></i> Ventas</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title mb-0"><i class="bi bi-tags"></i> Gestión de Categorías</h3>
        <button class="btn btn-gamer" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </button>
    </div>

    <div class="card-admin p-0 overflow-hidden">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($cat = $categorias->fetch_assoc()): ?>
                <tr>
                    <td><span class="text-muted"><?= $cat['id_categoria'] ?></span></td>
                    <td><span class="badge-cat"><?= htmlspecialchars($cat['nombre_categoria']) ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($cat['descripcion']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal" data-bs-target="#modalEditar"
                            data-id="<?= $cat['id_categoria'] ?>"
                            data-nombre="<?= htmlspecialchars($cat['nombre_categoria']) ?>"
                            data-desc="<?= htmlspecialchars($cat['descripcion']) ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                            data-id="<?= $cat['id_categoria'] ?>"
                            data-nombre="<?= htmlspecialchars($cat['nombre_categoria']) ?>">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre_categoria" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gamer">Guardar</button>
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
                <h5 class="modal-title text-warning">Editar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_categoria" id="editId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre_categoria" id="editNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="editDesc" class="form-control" rows="3"></textarea>
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
                <h5 class="modal-title text-danger">Eliminar Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar la categoría <strong id="elimNombre" class="text-danger"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_categoria" id="elimId">
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
// Llenar modal editar
document.getElementById('modalEditar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value = btn.dataset.id;
    document.getElementById('editNombre').value = btn.dataset.nombre;
    document.getElementById('editDesc').value = btn.dataset.desc;
});
// Llenar modal eliminar
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('elimId').value = btn.dataset.id;
    document.getElementById('elimNombre').textContent = btn.dataset.nombre;
});
</script>
</body>
</html>