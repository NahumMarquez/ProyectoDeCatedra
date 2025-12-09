<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$res = $conn->query("SELECT c.*, 
                     COUNT(v.id_venta) AS total_ventas,
                     COALESCE(SUM(v.total), 0) AS monto_total
                     FROM clientes c
                     LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
                     GROUP BY c.id_cliente
                     ORDER BY c.nombre ASC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Gestión de Clientes</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>
  
  <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
    <a class="btn-outline" href="../ventas/ventas_listar.php">← Volver a Ventas</a>
    <a class="btn-new" href="clientes_agregar.php">+ Nuevo Cliente</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Total Ventas</th>
        <th>Monto Total</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id_cliente'] ?></td>
        <td><?= htmlspecialchars($r['nombre']) ?></td>
        <td><?= htmlspecialchars($r['telefono'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['correo'] ?? '-') ?></td>
        <td><?= $r['total_ventas'] ?></td>
        <td>$<?= number_format($r['monto_total'], 2) ?></td>
        <td><span class="badge <?= strtolower($r['estado']) ?>"><?= ucfirst($r['estado']) ?></span></td>
        <td class="actions">
          <a href="clientes_editar.php?id=<?= $r['id_cliente'] ?>" title="Editar">✏️</a>
          <?php if ($rol === 'administrador'): ?>
            <a href="clientes_eliminar.php?id=<?= $r['id_cliente'] ?>" onclick="return confirm('¿Eliminar cliente?')" title="Eliminar">🗑️</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>