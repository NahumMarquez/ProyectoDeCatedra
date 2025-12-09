<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: ventas_listar.php"); exit; }

$error = '';

// Obtener venta
$stmt = $conn->prepare("SELECT * FROM ventas WHERE id_venta = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) { header("Location: ventas_listar.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_venta = $_POST['fecha_venta'];
    $estado = $_POST['estado'];
    $observaciones = $conn->real_escape_string($_POST['observaciones']);
    
    $upd = $conn->prepare("UPDATE ventas SET fecha_venta = ?, estado = ?, observaciones = ? WHERE id_venta = ?");
    $upd->bind_param("sssi", $fecha_venta, $estado, $observaciones, $id);
    
    if ($upd->execute()) {
        header("Location: ventas_listar.php");
        exit;
    } else {
        $error = "Error al actualizar: " . $upd->error;
    }
}

$clientes = $conn->query("SELECT * FROM clientes WHERE estado = 'activo' ORDER BY nombre");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Editar Venta #<?= $venta['id_venta'] ?></h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  
  <div class="alert" style="background:#fff3cd;border-left:4px solid #ffc107;color:#856404;margin-bottom:20px;">
    ⚠️ Solo se pueden editar fecha, estado y observaciones. Para cambiar productos, elimine y cree una nueva venta.
  </div>
  
  <form method="post" style="display:grid;gap:15px;max-width:720px;">
    <div>
      <label>Fecha de venta</label>
      <input type="date" name="fecha_venta" value="<?= $venta['fecha_venta'] ?>" required>
    </div>
    
    <div>
      <label>Estado</label>
      <select name="estado">
        <option value="completada" <?= $venta['estado']=='completada'?'selected':'' ?>>Completada</option>
        <option value="pendiente" <?= $venta['estado']=='pendiente'?'selected':'' ?>>Pendiente</option>
        <option value="cancelada" <?= $venta['estado']=='cancelada'?'selected':'' ?>>Cancelada</option>
      </select>
    </div>
    
    <div>
      <label>Observaciones</label>
      <textarea name="observaciones" rows="3"><?= htmlspecialchars($venta['observaciones']) ?></textarea>
    </div>
    
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn-outline" href="ventas_listar.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>