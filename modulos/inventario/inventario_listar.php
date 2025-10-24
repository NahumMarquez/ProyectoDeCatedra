<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$res = $conn->query("SELECT * FROM inventario ORDER BY fecha_entrada DESC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Inventario (Lotes)</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <div></div>
    <div>
      <a class="btn-new" href="inventario_agregar.php">+ Nuevo Lote</a>
      <a class="btn-outline" href="aplicar_peps.php">Aplicar PEPS</a>
    </div>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th><th>Lote</th><th>Entrada</th><th>Vencimiento</th>
        <th>Stock Inicial</th><th>Stock Actual</th><th>Clasificación</th> <th>Estado</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id_inventario'] ?></td>
        <td><?= htmlspecialchars($r['lote']) ?></td>
        <td><?= $r['fecha_entrada'] ?></td>
        <td><?= $r['fecha_vencimiento'] ?></td>
        <td><?= number_format($r['stock_inicial']) ?></td>
        <td><?= number_format($r['stock_actual']) ?></td>
        <td><?= htmlspecialchars($r['clasificacion']) ?></td>
        <td><span class="badge <?= strtolower(str_replace(' ', '', $r['estado'])) ?>"><?= htmlspecialchars($r['estado']) ?></span></td>
        <td class="actions">
          <a href="inventario_editar.php?id=<?= $r['id_inventario'] ?>" title="Editar">✏️</a>
          <a href="inventario_eliminar.php?id=<?= $r['id_inventario'] ?>" onclick="return confirm('¿Eliminar lote?')" title="Eliminar">🗑️</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
