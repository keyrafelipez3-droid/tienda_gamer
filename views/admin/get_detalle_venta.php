<?php
session_start();
if(!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin','super_admin'])) exit;
require_once '../../config/db.php';

$id = intval($_GET['id']);

$detalles = $conn->prepare("SELECT dv.*, p.nombre, p.marca, p.imagen, p.precio FROM detalle_venta dv JOIN producto p ON dv.id_producto=p.id_producto WHERE dv.id_venta=?");
$detalles->bind_param("i", $id);
$detalles->execute();
$detalles = $detalles->get_result();

if($detalles->num_rows === 0): ?>
<div style="text-align:center;color:#444;padding:32px;">No hay productos en esta venta</div>
<?php else: while($d = $detalles->fetch_assoc()): ?>
<div style="background:#111120;border-radius:10px;padding:14px 16px;margin-bottom:8px;display:flex;align-items:center;gap:12px;">
    <div style="width:46px;height:46px;border-radius:8px;background:#0d0d1a;display:flex;align-items:center;justify-content:center;font-size:1.3rem;overflow:hidden;border:1px solid #1a1a2e;flex-shrink:0;">
        <?php if($d['imagen']): ?>
            <img src="../../assets/<?= $d['imagen'] ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>📦<?php endif; ?>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($d['nombre']) ?></div>
        <div style="font-size:0.75rem;color:#555;margin-top:2px;"><?= htmlspecialchars($d['marca']) ?> — <?= $d['cantidad'] ?> unidad<?= $d['cantidad']>1?'es':'' ?> × Bs. <?= number_format($d['precio'],2) ?></div>
    </div>
    <div style="text-align:right;flex-shrink:0;">
        <div style="color:#00ff88;font-weight:700;">Bs. <?= number_format($d['subtotal'],2) ?></div>
    </div>
</div>
<?php endwhile; endif; ?>