<?php
session_start();
if(!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php'); exit;
}
require_once '../../config/db.php';

$id_usuario = $_SESSION['usuario_id'];

// Mensaje de compra exitosa
$compra_exitosa = null;
if(isset($_SESSION['compra_exitosa'])) {
    $compra_exitosa = $_SESSION['compra_exitosa'];
    unset($_SESSION['compra_exitosa']);
}

// Obtener ventas del usuario
$ventas = $conn->prepare("SELECT * FROM venta WHERE id_usuario=? ORDER BY fecha DESC");
$ventas->bind_param("i", $id_usuario);
$ventas->execute();
$ventas = $ventas->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - GamerZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #0a0a0a; color: #fff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #0d0d0d; border-bottom: 2px solid #00ff88; }
        .navbar-brand { color: #00ff88 !important; font-weight: 800; }
        .navbar-brand span { color: #fff; }
        .btn-gamer { background: #00ff88; color: #000; font-weight: 700; border: none; border-radius: 8px; }
        .btn-gamer:hover { background: #00cc6a; color: #000; }
        .venta-card { background: #111; border: 1px solid #222; border-radius: 16px; padding: 20px; margin-bottom: 16px; transition: all 0.3s; }
        .venta-card:hover { border-color: #00ff88; }
        .venta-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .total { color: #00ff88; font-size: 1.3rem; font-weight: 800; }
        .detalle-item { background: #161616; border-radius: 10px; padding: 12px 16px; margin-top: 10px; }
        .detalle-img { width: 50px; height: 50px; background: #1a1a1a; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; overflow: hidden; }
        .detalle-img img { width: 100%; height: 100%; object-fit: cover; }
        .alert-gamer { background: #0d2a0d; border: 1px solid #00ff88; color: #00ff88; border-radius: 12px; padding: 16px 20px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="productos.php">Gamer<span>Zone</span></a>
        <div class="d-flex gap-3 ms-auto">
            <a href="productos.php" class="btn btn-outline-secondary btn-sm">← Productos</a>
            <form action="../../controllers/auth_controller.php" method="POST" class="d-inline">
                <input type="hidden" name="action" value="logout">
                <button class="btn btn-gamer btn-sm">Salir</button>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3 class="fw-bold mb-4">📦 Historial de <span style="color:#00ff88">Compras</span></h3>

    <?php if($compra_exitosa): ?>
    <div class="alert-gamer mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Compra realizada exitosamente!</strong> Tu pedido #<?= $compra_exitosa ?> está en proceso.
    </div>
    <?php endif; ?>

    <?php if($ventas->num_rows === 0): ?>
    <div class="text-center py-5">
        <div style="font-size:5rem;">📭</div>
        <h4 class="text-muted mt-3">Aún no has realizado compras</h4>
        <a href="productos.php" class="btn btn-gamer mt-3 px-4">Explorar productos</a>
    </div>
    <?php else: ?>
    <?php while($venta = $ventas->fetch_assoc()): ?>
    <div class="venta-card">
        <div class="venta-header">
            <div>
                <span class="text-muted small">Pedido #<?= $venta['id_venta'] ?></span>
                <p class="mb-0 fw-bold"><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
            </div>
            <div class="text-center">
                <?php
                $estados = ['Pendiente' => 'warning', 'Pagado' => 'info', 'Entregado' => 'success'];
                $color = $estados[$venta['estado_venta']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $color ?>"><?= $venta['estado_venta'] ?></span>
            </div>
            <div class="text-end">
                <span class="total">Bs. <?= number_format($venta['total'], 2) ?></span>
            </div>
        </div>

        <!-- Detalles de la venta -->
        <?php
        $detalles = $conn->prepare("SELECT dv.*, p.nombre, p.marca, p.imagen FROM detalle_venta dv 
            JOIN producto p ON dv.id_producto = p.id_producto 
            WHERE dv.id_venta = ?");
        $detalles->bind_param("i", $venta['id_venta']);
        $detalles->execute();
        $detalles = $detalles->get_result();
        while($d = $detalles->fetch_assoc()):
        ?>
        <div class="detalle-item d-flex align-items-center gap-3">
            <div class="detalle-img">
                <?php if($d['imagen']): ?>
                    <img src="../../assets/<?= $d['imagen'] ?>" alt="">
                <?php else: ?>
                    📦
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <p class="fw-bold mb-0"><?= htmlspecialchars($d['nombre']) ?></p>
                <small class="text-muted"><?= htmlspecialchars($d['marca']) ?> — <?= $d['cantidad'] ?> und.</small>
            </div>
            <span style="color:#00ff88">Bs. <?= number_format($d['subtotal'], 2) ?></span>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>