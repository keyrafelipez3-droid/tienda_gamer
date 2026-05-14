<?php
session_start();
if(!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$es_super = $_SESSION['usuario_rol'] === 'super_admin';

// Acciones
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Solo super_admin puede crear admins
    if($accion === 'crear_admin' && $es_super) {
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $pass   = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $rol    = $_POST['rol'];
        if(in_array($rol, ['admin','cliente'])) {
            $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, contrasena, rol) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $nombre, $correo, $pass, $rol);
            $stmt->execute();
            $_SESSION['success'] = 'Usuario creado correctamente.';
        }
    }

    // Cambiar rol — solo super_admin
    if($accion === 'cambiar_rol' && $es_super) {
        $id  = intval($_POST['id_usuario']);
        $rol = $_POST['rol'];
        if(in_array($rol, ['admin','cliente']) && $id !== $_SESSION['usuario_id']) {
            $stmt = $conn->prepare("UPDATE usuario SET rol=? WHERE id_usuario=?");
            $stmt->bind_param("si", $rol, $id);
            $stmt->execute();
            $_SESSION['success'] = 'Rol actualizado correctamente.';
        }
    }

    // Eliminar usuario — solo super_admin
    if($accion === 'eliminar' && $es_super) {
        $id = intval($_POST['id_usuario']);
        if($id !== $_SESSION['usuario_id']) {
            $conn->prepare("DELETE FROM favorito WHERE id_usuario=?")->bind_param("i",$id) && $conn->prepare("DELETE FROM favorito WHERE id_usuario=?")->execute();
            $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario=? AND rol != 'super_admin'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $_SESSION['success'] = 'Usuario eliminado correctamente.';
        }
    }

    header('Location: usuarios.php'); exit;
}

$usuarios = $conn->query("SELECT * FROM usuario ORDER BY fecha_registro DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Admin GamerZone</title>
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
        .form-control, .form-select { background: #1a1a1a; border: 1px solid #333; color: #fff; border-radius: 8px; }
        .form-control:focus, .form-select:focus { background: #1a1a1a; border-color: #00ff88; color: #fff; box-shadow: none; }
        .form-select option { background: #1a1a1a; }
        .avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; }
        .alert-success-custom { background: #0d2a0d; border: 1px solid #00ff88; color: #00ff88; border-radius: 10px; padding: 12px 16px; }
        .alert-danger-custom { background: #2a0d0d; border: 1px solid #ff4444; color: #ff4444; border-radius: 10px; padding: 12px 16px; }
        .super-badge { background: linear-gradient(135deg, #f59e0b, #ef4444); color: #fff; font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="dashboard.php">Gamer<span>Zone</span> <small class="text-muted fs-6"><?= $es_super ? 'Super Admin' : 'Admin' ?></small></a>
        <div class="d-flex gap-3 ms-auto align-items-center">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="productos.php" class="nav-link"><i class="bi bi-box"></i> Productos</a>
            <a href="categorias.php" class="nav-link"><i class="bi bi-tags"></i> Categorías</a>
            <a href="ventas.php" class="nav-link"><i class="bi bi-cart"></i> Ventas</a>
            <a href="usuarios.php" class="nav-link active"><i class="bi bi-people"></i> Usuarios</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert-success-custom mb-3"><i class="bi bi-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert-danger-custom mb-3"><i class="bi bi-exclamation-circle me-2"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0" style="color:#00ff88"><i class="bi bi-people"></i> Gestión de Usuarios</h3>
        <?php if($es_super): ?>
        <button class="btn btn-gamer" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-plus-lg"></i> Crear Usuario
        </button>
        <?php endif; ?>
    </div>

    <div class="card-dark">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <?php if($es_super): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $usuarios->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted"><?= $u['id_usuario'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php
                            $colors = ['super_admin'=>'#f59e0b','admin'=>'#00ff88','cliente'=>'#6366f1'];
                            $color = $colors[$u['rol']] ?? '#888';
                            ?>
                            <div class="avatar" style="background:<?= $color ?>22; border:1px solid <?= $color ?>; color:<?= $color ?>">
                                <?= strtoupper(substr($u['nombre'],0,1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($u['nombre']) ?></strong>
                                <?php if($u['id_usuario'] === $_SESSION['usuario_id']): ?>
                                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Tú</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($u['correo']) ?></td>
                    <td>
                        <?php if($u['rol'] === 'super_admin'): ?>
                            <span class="super-badge">⭐ Super Admin</span>
                        <?php elseif($u['rol'] === 'admin'): ?>
                            <span class="badge bg-danger">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-success">Cliente</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
                    <?php if($es_super): ?>
                    <td>
                        <?php if($u['rol'] !== 'super_admin' && $u['id_usuario'] !== $_SESSION['usuario_id']): ?>
                        <button class="btn btn-sm btn-outline-warning me-1"
                            data-bs-toggle="modal" data-bs-target="#modalRol"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-nombre="<?= htmlspecialchars($u['nombre']) ?>"
                            data-rol="<?= $u['rol'] ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-nombre="<?= htmlspecialchars($u['nombre']) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Crear Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="crear_admin">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" class="form-select">
                            <option value="admin">Administrador</option>
                            <option value="cliente">Cliente</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-gamer">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Rol -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning">Cambiar Rol</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="cambiar_rol">
                <input type="hidden" name="id_usuario" id="rolId">
                <div class="modal-body">
                    <p>Cambiar rol de <strong id="rolNombre" class="text-warning"></strong></p>
                    <select name="rol" id="rolSelect" class="form-select">
                        <option value="admin">Administrador</option>
                        <option value="cliente">Cliente</option>
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

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Eliminar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Eliminar al usuario <strong id="elimNombre" class="text-danger"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_usuario" id="elimId">
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
document.getElementById('modalRol').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('rolId').value = btn.dataset.id;
    document.getElementById('rolNombre').textContent = btn.dataset.nombre;
    document.getElementById('rolSelect').value = btn.dataset.rol;
});
document.getElementById('modalEliminar').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('elimId').value = btn.dataset.id;
    document.getElementById('elimNombre').textContent = btn.dataset.nombre;
});
</script>
</body>
</html>