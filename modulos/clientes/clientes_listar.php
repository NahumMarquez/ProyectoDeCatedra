<?php 
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

// ==========================
// BÚSQUEDA
// ==========================
$q = $conn->real_escape_string($_GET['q'] ?? '');

$sql = "SELECT c.*, 
        COUNT(v.id_venta) AS total_ventas,
        COALESCE(SUM(v.total), 0) AS monto_total
        FROM clientes c
        LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
        WHERE 1=1";

if ($q !== '') {
    // Búsqueda insensible a mayúsculas/minúsculas
    $sql .= " AND c.nombre LIKE '%$q%' COLLATE utf8mb4_general_ci";
}

$sql .= " GROUP BY c.id_cliente
          ORDER BY c.nombre ASC";

$res = $conn->query($sql);
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>

<div class="card-table">
  <h3>Gestión de Clientes</h3>
  <small class="text-muted">Usuario: <?= htmlspecialchars($usuario_nombre) ?> · Rol: <?= htmlspecialchars($rol) ?></small>

  <!-- FORMULARIO DE BÚSQUEDA -->
  <form method="get" style="margin:20px 0; display:flex; gap:10px;">
      <input 
        type="text" 
        name="q" 
        placeholder="Buscar cliente..." 
        value="<?= htmlspecialchars($q) ?>" 
        style="flex:1;"
      >
      <button class="btn-outline" type="submit">🔍 Buscar</button>
      <a href="clientes_listar.php" class="btn-outline">✖ Limpiar</a>
  </form>

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
