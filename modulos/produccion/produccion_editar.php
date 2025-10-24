<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: produccion_listar.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM produccion WHERE id_produccion = ?");
$stmt->bind_param("i",$id); $stmt->execute();
$reg = $stmt->get_result()->fetch_assoc();
if (!$reg) header("Location: produccion_listar.php");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_inventario = intval($_POST['id_inventario']) ?: NULL;
    $fecha = $_POST['fecha'];
    $cantidad = intval($_POST['cantidad']);
    $obs = $conn->real_escape_string($_POST['observaciones']);

    $u = $conn->prepare("UPDATE produccion SET id_inventario=?, fecha=?, cantidad_recolectada=?, observaciones=? WHERE id_produccion=?");
    $u->bind_param("isisi", $id_inventario, $fecha, $cantidad, $obs, $id);
    if ($u->execute()) {
        header("Location: produccion_listar.php");
        exit;
    } else $error = $u->error;
}

$inventarios = $conn->query("SELECT id_inventario, lote, clasificacion FROM inventario ORDER BY fecha_entrada DESC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Editar Producción</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  <form method="post" style="display:grid;gap:8px;max-width:680px;">
    <label>Lote (opcional)</label>
    <select name="id_inventario">
      <option value="">-- Sin lote --</option>
      <?php while($l = $inventarios->fetch_assoc()): ?>
        <option value="<?= $l['id_inventario'] ?>" <?= $l['id_inventario']==$reg['id_inventario']?'selected':'' ?>><?= htmlspecialchars($l['lote'].' - '.$l['clasificacion']) ?></option>
      <?php endwhile; ?>
    </select>
    <label>Fecha</label><input type="date" name="fecha" value="<?= $reg['fecha'] ?>" required>
    <label>Cantidad</label><input type="number" name="cantidad" value="<?= $reg['cantidad_recolectada'] ?>" min="0" required>
    <label>Observaciones</label><textarea name="observaciones"><?= htmlspecialchars($reg['observaciones']) ?></textarea>
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Cancelar</button>
      <a class="btn-outline" href="produccion_listar.php">Actualizar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
