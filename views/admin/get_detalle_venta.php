<?php
session_start();
if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') exit;
require_once '../../config/db.php';

$id = intval($_GET['id']);
$detalles = $conn->prepare("SELECT dv.*, p.nombre, p.marca, p.imagen FROM detalle_venta dv JOIN producto p ON dv.id_producto=p.id_producto WHERE dv.id_venta=?");
$detalles->bind_param("i", $id);
$detalles->execute();
$detalles = $detalles->get_result();

$venta = $conn->prepare("SELECT v.*, u.nombre as cliente FROM venta v JOIN usuario u ON v.id_usuario=u.id_usuario WHERE v.id_venta=?");
$venta->bind_param("i", $id);
$venta->execute();
$venta = $venta->get_result()->fetch_assoc();
?>
<div class="mb-3">
    <p class="text-muted mb-1">Pedido <strong class="text-white">#<?= $venta['id_venta'] ?></strong> — Cliente: <strong class="text-white"><?= htmlspecialchars($venta['cliente']) ?></strong></p>
    <p class="text-muted mb-0">Fecha: <?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
</div>
<?php while($d = $detalles->fetch_assoc()): ?>
<div class="detalle-item d-flex align-items-center gap-3">
    <div style="width:45px;height:45px;background:#111;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;overflow:hidden;">
        <?php if($d['imagen']): ?>
            <img src="../../assets/<?= $d['imagen'] ?>" style="width:100%;height:100%;object-fit:cover;">
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
<div class="mt-3 text-end">
    <strong style="color:#00ff88;font-size:1.2rem;">Total: Bs. <?= number_format($venta['total'], 2) ?></strong>
</div>