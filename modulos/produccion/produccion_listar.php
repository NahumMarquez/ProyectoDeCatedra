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

$sql = "SELECT p.*, i.lote, u.nombre AS usuario 
        FROM produccion p 
        LEFT JOIN inventario i ON p.id_inventario = i.id_inventario
        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE 1=1";

if ($q !== '') {
    $sql .= " AND (i.lote LIKE '%$q%' COLLATE utf8mb4_general_ci 
              OR p.observaciones LIKE '%$q%' COLLATE utf8mb4_general_ci)";
}

if ($fecha_desde) {
    $sql .= " AND p.fecha >= '$fecha_desde'";
}

if ($fecha_hasta) {
    $sql .= " AND p.fecha <= '$fecha_hasta'";
}

$sql .= " ORDER BY p.fecha DESC";

$res = $conn->query($sql);
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>

<div class="card-table">
  <h3>Producción</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>

  <!-- BUSQUEDA Y FILTRO -->
  <form method="get" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px;">
      <div>
          <label style="font-size:12px;color:#666;">Buscar por lote u observación</label>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar...">
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
          <a href="produccion_listar.php" class="btn-outline">✖️</a>
      </div>
  </form>

  <div style="display:flex;justify-content:flex-end;margin-bottom:12px;">
    <a class="btn-new" href="produccion_agregar.php">+ Registrar Producción</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>Lote</th><th>Fecha</th><th>Cantidad</th>
        <th>Clasif.</th><th>Observaciones</th><th>Usuario</th><th>Acciones</th>
      </tr>
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
