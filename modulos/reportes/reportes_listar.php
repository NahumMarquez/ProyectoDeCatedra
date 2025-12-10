<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

// filtros recibidos
$tipo = $_GET['tipo'] ?? ''; // produccion|inventario|ventas
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// obtener lista de reportes guardados
$resReportes = $conn->query("SELECT r.*, u.nombre AS creador FROM reportes r LEFT JOIN usuarios u ON r.created_by = u.id_usuario ORDER BY r.created_at DESC");

?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Reportes</h3>

  <form method="get" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;margin-bottom:12px;">
    <select name="tipo">
      <option value="">Todos los tipos</option>
      <option value="produccion" <?= $tipo=='produccion'?'selected':'' ?>>Producción</option>
      <option value="inventario" <?= $tipo=='inventario'?'selected':'' ?>>Inventario</option>
      <option value="ventas" <?= $tipo=='ventas'?'selected':'' ?>>Ventas</option>
    </select>
    <input type="date" name="fecha_desde" value="<?= htmlspecialchars($fecha_desde) ?>" placeholder="Desde">
    <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($fecha_hasta) ?>" placeholder="Hasta">
    <div style="display:flex;gap:8px;">
      <button class="btn-outline" type="submit">Filtrar</button>
      <a class="btn-outline" href="reportes_listar.php">Limpiar</a>
    </div>
  </form>

  <div style="margin-bottom:12px;">
    <a class="btn-new" href="reportes_generar.php?tipo=produccion">Generar Reporte Producción (PDF)</a>
    <a class="btn-new" href="reportes_generar.php?tipo=inventario">Generar Reporte Inventario (PDF)</a>
    <a class="btn-new" href="reportes_generar.php?tipo=ventas">Generar Reporte Ventas (PDF)</a>
  </div>

  <h4>Reportes guardados</h4>
  <table class="table">
    <thead>
      <tr><th>ID</th><th>Tipo</th><th>Parametros</th><th>Archivo</th><th>Usuario</th><th>Fecha</th><th>Acciones</th></tr>
    </thead>
    <tbody>
      <?php while($r = $resReportes->fetch_assoc()): ?>
        <tr>
          <td><?= $r['id_report'] ?></td>
          <td><?= htmlspecialchars($r['type']) ?></td>
          <td><?= htmlspecialchars($r['params']) ?></td>
          <td>
            <?php if ($r['filename']): ?>
              <a href="/modulos/reportes/exports/<?= htmlspecialchars($r['filename']) ?>" target="_blank">Ver</a>
              <a href="/modulos/reportes/<?= 'reportes_exportar_excel.php?id=' . $r['id_report'] ?>">Exportar</a>
            <?php else: ?> - <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($r['creador']) ?></td>
          <td><?= $r['created_at'] ?></td>
          <td class="actions">
            <?php if ($_SESSION['rol'] === 'administrador'): ?>
              <a href="reportes_eliminar.php?id=<?= $r['id_report'] ?>" onclick="return confirm('Eliminar reporte?')">🗑️</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
