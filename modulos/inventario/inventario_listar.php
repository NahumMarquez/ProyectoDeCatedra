<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }


// ======================
// BÚSQUEDAS Y FILTROS
// ======================
$q = $conn->real_escape_string($_GET['q'] ?? '');
$fecha_desde = $conn->real_escape_string($_GET['fecha_desde'] ?? '');
$fecha_hasta = $conn->real_escape_string($_GET['fecha_hasta'] ?? '');

$sql = "SELECT * FROM inventario WHERE 1=1";

if ($q !== '') {
    $sql .= " AND lote LIKE '%$q%' COLLATE utf8mb4_general_ci";
}

if ($fecha_desde) {
    $sql .= " AND fecha_entrada >= '$fecha_desde'";
}

if ($fecha_hasta) {
    $sql .= " AND fecha_entrada <= '$fecha_hasta'";
}

$sql .= " ORDER BY fecha_entrada DESC";

$res = $conn->query($sql);
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>

<div class="card-table">
  <h3>Inventario (Lotes)</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>

  <!-- BUSCADOR Y FILTROS -->
  <form method="get" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px;">
      <div>
          <label style="font-size:12px;color:#666;">Buscar Lote</label>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar lote...">
      </div>

      <div>
          <label style="font-size:12px;color:#666;">Desde</label>
          <input type="date" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>">
      </div>

      <div>
          <label style="font-size:12px;color:#666;">Hasta</label>
          <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>">
      </div>

      <div style="display:flex;align-items:flex-end;">
          <button class="btn-outline" style="margin-right:5px;">🔍 Filtrar</button>
          <a href="inventario_listar.php" class="btn-outline">✖️</a>
      </div>
  </form>

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
        <th>Stock Inicial</th><th>Stock Actual</th><th>Clasificación</th>
        <th>Estado</th><th>Acciones</th>
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

