<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: inventario_listar.php"); exit; }

$error = '';
$stmt = $conn->prepare("SELECT * FROM inventario WHERE id_inventario = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$reg = $res->fetch_assoc();

if (!$reg) { header("Location: inventario_listar.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clasificacion = $conn->real_escape_string($_POST['clasificacion']);
    $stock_actual = intval($_POST['stock_actual']);
    $estado = $conn->real_escape_string($_POST['estado']);

    $u = $conn->prepare("UPDATE inventario SET clasificacion=?, stock_actual=?, estado=? WHERE id_inventario=?");
    $u->bind_param("sisi", $clasificacion, $stock_actual, $estado, $id);
    if ($u->execute()) {
        header("Location: inventario_listar.php");
        exit;
    } else $error = $u->error;
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Editar Lote <?= htmlspecialchars($reg['lote']) ?></h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  <form method="post" style="display:grid;gap:10px;max-width:720px;">
    <label>Clasificación</label><input name="clasificacion" value="<?= htmlspecialchars($reg['clasificacion']) ?>">
    <label>Stock actual</label><input type="number" name="stock_actual" value="<?= $reg['stock_actual'] ?>" min="0">
    <label>Estado</label>
    <select name="estado">
      <option value="Disponible" <?= $reg['estado']=='Disponible'?'selected':'' ?>>Disponible</option>
      <option value="Próximo a Vencer" <?= $reg['estado']=='Próximo a Vencer'?'selected':'' ?>>Próximo a Vencer</option>
      <option value="Vencido" <?= $reg['estado']=='Vencido'?'selected':'' ?>>Vencido</option>
    </select>
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Cancelar</button>
      <a class="btn-outline" href="inventario_listar.php">Actualizar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
