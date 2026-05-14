<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

// Cambiar estado de venta
if(isset($_POST['cambiar_estado'])) {
    $id_venta = $_POST['id_venta'];
    $estado = $_POST['estado_venta'];
    $stmt = $conn->prepare("UPDATE venta SET estado_venta=? WHERE id_venta=?");
    $stmt->bind_param("si", $estado, $id_venta);
    $stmt->execute();
    header('Location: ventas.php'); exit;
}

$ventas = $conn->query("SELECT v.*, u.nombre, u.correo FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario ORDER BY v.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Admin GamerZone</title>
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
        .card-dark { background: #111; border: 1px solid #222; border-radius: 16px; overflow: hidden; }
        .table { color: #fff; }
        .table thead th { color: #00ff88; border-color: #222; background: #0d0d0d; }
        .table td { border-color: #1a1a1a; vertical-align: middle; }
        .table tbody tr:hover { background: #161616; }
        .modal-content { background: #111; border: 1px solid #333; color: #fff; }
        .modal-header { border-bottom: 1px solid #222; }
        .modal-footer { border-top: 1px solid #222; }
        .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 8px; }
        .form-select:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: none; }
        .form-select option { background: #1a1a1a; }
        .detalle-item { background: #1a1a1a; border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6">Admin</small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link active"><i class="bi bi-cart"></i> Ventas</a>
            <a href="usuarios.php" class="nav-link"><i class="bi bi-people"></i> Usuarios</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <h3 class="fw-bold mb-4" style="color:#00ff88"><i class="bi bi-cart"></i> Gestión de Ventas</h3>

    <div class="card-dark">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Correo</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if($ventas->num_rows === 0): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay ventas registradas</td></tr>
                <?php else: ?>
                <?php while($v = $ventas->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted">#<?= $v['id_venta'] ?></td>
                    <td><strong><?= htmlspecialchars($v['nombre']) ?></strong></td>
                    <td class="text-muted small"><?= htmlspecialchars($v['correo']) ?></td>
                    <td class="text-muted"><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <td><strong class="text-success">Bs. <?= number_format($v['total'], 2) ?></strong></td>
                    <td>
                        <?php $colores = ['Pendiente'=>'warning','Pagado'=>'info','Entregado'=>'success']; ?>
                        <span class="badge bg-<?= $colores[$v['estado_venta']] ?>"><?= $v['estado_venta'] ?></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info me-1"
                            data-bs-toggle="modal" data-bs-target="#modalDetalle"
                            data-id="<?= $v['id_venta'] ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning"
                            data-bs-toggle="modal" data-bs-target="#modalEstado"
                            data-id="<?= $v['id_venta'] ?>"
                            data-estado="<?= $v['estado_venta'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-info">Detalle de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <div class="text-center py-3 text-muted">Cargando...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">Cambiar Estado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="cambiar_estado" value="1">
                <input type="hidden" name="id_venta" id="estadoId">
                <div class="modal-body">
                    <label class="form-label">Estado de la venta</label>
                    <select name="estado_venta" id="estadoSelect" class="form-select">
                        <option value="Pendiente">Pendiente</option>
                        <option value="Pagado">Pagado</option>
                        <option value="Entregado">Entregado</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modal estado
document.getElementById('modalEstado').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('estadoId').value = btn.dataset.id;
    document.getElementById('estadoSelect').value = btn.dataset.estado;
});

// Modal detalle — cargar via fetch
document.getElementById('modalDetalle').addEventListener('show.bs.modal', function(e) {
    const id = e.relatedTarget.dataset.id;
    document.getElementById('detalleContenido').innerHTML = '<div class="text-center py-3 text-muted">Cargando...</div>';
    fetch('get_detalle_venta.php?id=' + id)
        .then(r => r.text())
        .then(html => document.getElementById('detalleContenido').innerHTML = html);
});
</script>
</body>
</html>