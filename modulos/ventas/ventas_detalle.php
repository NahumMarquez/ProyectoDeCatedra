<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: ventas_listar.php"); exit; }

// Obtener venta
$stmt = $conn->prepare("SELECT v.*, c.nombre AS cliente, c.telefono, c.direccion, u.nombre AS vendedor 
                        FROM ventas v 
                        INNER JOIN clientes c ON v.id_cliente = c.id_cliente
                        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                        WHERE v.id_venta = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) { header("Location: ventas_listar.php"); exit; }

// Obtener detalles
$detalles = $conn->query("SELECT d.*, i.lote, i.clasificacion 
                          FROM detalle_ventas d
                          INNER JOIN inventario i ON d.id_inventario = i.id_inventario
                          WHERE d.id_venta = $id");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h3>Detalle de Venta #<?= $venta['id_venta'] ?></h3>
    <a class="btn-outline" href="ventas_listar.php">← Volver</a>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px;">
    <div style="padding:20px;background:#f8f9fa;border-radius:8px;">
      <h4 style="margin-bottom:15px;color:#667eea;">📋 Información de Venta</h4>
      <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?></p>
      <p><strong>Vendedor:</strong> <?= htmlspecialchars($venta['vendedor']) ?></p>
      <p><strong>Estado:</strong> <span class="badge <?= strtolower($venta['estado']) ?>"><?= ucfirst($venta['estado']) ?></span></p>
      <?php if ($venta['observaciones']): ?>
        <p><strong>Observaciones:</strong> <?= htmlspecialchars($venta['observaciones']) ?></p>
      <?php endif; ?>
    </div>

    <div style="padding:20px;background:#f8f9fa;border-radius:8px;">
      <h4 style="margin-bottom:15px;color:#667eea;">👤 Información del Cliente</h4>
      <p><strong>Cliente:</strong> <?= htmlspecialchars($venta['cliente']) ?></p>
      <?php if ($venta['telefono']): ?>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($venta['telefono']) ?></p>
      <?php endif; ?>
      <?php if ($venta['direccion']): ?>
        <p><strong>Dirección:</strong> <?= htmlspecialchars($venta['direccion']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <h4 style="margin-bottom:15px;">Productos Vendidos</h4>
  <table class="table">
    <thead>
      <tr>
        <th>Lote</th>
        <th>Clasificación</th>
        <th>Cantidad</th>
        <th>Precio Unitario</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php while($d = $detalles->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($d['lote']) ?></td>
        <td><?= htmlspecialchars($d['clasificacion']) ?></td>
        <td><?= number_format($d['cantidad']) ?></td>
        <td>$<?= number_format($d['precio_unitario'], 2) ?></td>
        <td>$<?= number_format($d['subtotal'], 2) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;font-size:18px;">
        <td colspan="4" style="text-align:right;">TOTAL:</td>
        <td>$<?= number_format($venta['total'], 2) ?></td>
      </tr>
    </tfoot>
  </table>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>