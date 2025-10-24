<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$res = $conn->query("SELECT p.*, i.lote, u.nombre AS usuario FROM produccion p LEFT JOIN inventario i ON p.id_inventario=i.id_inventario LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario ORDER BY p.fecha DESC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Producción</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>
  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <a class="btn-new" href="produccion_agregar.php">+ Registrar Producción</a>
  </div>

  <table class="table">
    <thead>
      <tr><th>Lote</th><th>Fecha</th><th>Cantidad</th><th>Clasif.</th><th>Observaciones</th><th>Usuario</th><th>Acciones</th></tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($r['lote'] ?? 'Sin lote') ?></td>
        <td><?= $r['fecha'] ?></td>
        <td><?= number_format($r['cantidad_recolectada']) ?></td>
        <td><?= htmlspecialchars($r['clasificacion'] ?? '-') ?></td>
        <td><?= htmlspecialchars($r['observaciones']) ?></td>
        <td><?= htmlspecialchars($r['usuario']) ?></td>
        <td class="actions">
          <a href="produccion_editar.php?id=<?= $r['id_produccion'] ?>">✏️</a>
          <a href="produccion_eliminar.php?id=<?= $r['id_produccion'] ?>" onclick="return confirm('¿Eliminar registro?')">🗑️</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
