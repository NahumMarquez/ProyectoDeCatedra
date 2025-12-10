<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$usuario_nombre = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

// Filtros
$filtro_cliente = $_GET['cliente'] ?? '';
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';

$sql = "SELECT v.*, c.nombre AS cliente, u.nombre AS vendedor 
        FROM ventas v 
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
        WHERE 1=1";

// ✔ Búsqueda insensible a mayúsculas/minúsculas
if ($filtro_cliente) {
    $sql .= " AND c.nombre LIKE '%" . $conn->real_escape_string($filtro_cliente) . "%' COLLATE utf8mb4_general_ci";
}

if ($filtro_fecha_desde) {
    $sql .= " AND v.fecha_venta >= '" . $conn->real_escape_string($filtro_fecha_desde) . "'";
}

if ($filtro_fecha_hasta) {
    $sql .= " AND v.fecha_venta <= '" . $conn->real_escape_string($filtro_fecha_hasta) . "'";
}

$sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";
$res = $conn->query($sql);
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>

<div class="card-table">
  <h3>Gestión de Ventas</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>
  
  <!-- Filtros -->
  <form method="get" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px;">
    <div>
      <label style="font-size:12px;color:#666;">Cliente</label>
      <input type="text" name="cliente" value="<?= htmlspecialchars($filtro_cliente) ?>" placeholder="Buscar cliente...">
    </div>
    <div>
      <label style="font-size:12px;color:#666;">Desde</label>
      <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filtro_fecha_desde) ?>">
    </div>
    <div>
      <label style="font-size:12px;color:#666;">Hasta</label>
      <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filtro_fecha_hasta) ?>">
    </div>
    <div style="display:flex;align-items:flex-end;">
      <button type="submit" class="btn-outline" style="margin-right:5px;">🔍 Filtrar</button>
      <a href="ventas_listar.php" class="btn-outline">✖️</a>
    </div>
  </form>

  <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
    <div>
      <a class="btn-outline" href="../clientes/clientes_listar.php">👥 Clientes</a>
    </div>
    <a class="btn-new" href="ventas_agregar.php">+ Nueva Venta</a>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Cliente</th>
        <th>Vendedor</th>
        <th>Total</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td>#<?= $r['id_venta'] ?></td>
        <td><?= date('d/m/Y', strtotime($r['fecha_venta'])) ?></td>
        <td><?= htmlspecialchars($r['cliente']) ?></td>
        <td><?= htmlspecialchars($r['vendedor']) ?></td>
        <td>$<?= number_format($r['total'], 2) ?></td>
        <td><span class="badge <?= strtolower($r['estado']) ?>"><?= ucfirst($r['estado']) ?></span></td>
        <td class="actions">
          <a href="ventas_detalle.php?id=<?= $r['id_venta'] ?>" title="Ver detalle">👁️</a>
          <a href="ventas_editar.php?id=<?= $r['id_venta'] ?>" title="Editar">✏️</a>
          <?php if ($rol === 'administrador'): ?>
            <a href="ventas_eliminar.php?id=<?= $r['id_venta'] ?>" onclick="return confirm('¿Eliminar venta?')" title="Eliminar">🗑️</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../../inc/footer.php'; ?>
