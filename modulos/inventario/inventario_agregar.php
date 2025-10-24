<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lote = $conn->real_escape_string($_POST['lote']);
    $fecha_entrada = $_POST['fecha_entrada'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $stock_inicial = intval($_POST['stock_inicial']);
    $stock_actual = intval($_POST['stock_actual']);
    $clasificacion = $conn->real_escape_string($_POST['clasificacion']);
    $estado = $conn->real_escape_string($_POST['estado']);

    $stmt = $conn->prepare("INSERT INTO inventario (lote, fecha_entrada, fecha_vencimiento, stock_inicial, stock_actual, clasificacion, estado) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("ssiiiss", $lote, $fecha_entrada, $fecha_vencimiento, $stock_inicial, $stock_actual, $clasificacion, $estado);
    if ($stmt->execute()) {
        // registrar movimiento de entrada
        $mov = $conn->prepare("INSERT INTO movimientos_inventario (id_inventario, tipo, cantidad, descripcion, registrado_por) VALUES (?, 'entrada', ?, 'Entrada inicial', ?)");
        $newId = $conn->insert_id;
        $mov->bind_param("iii", $newId, $stock_inicial, $_SESSION['id_usuario']);
        $mov->execute();
        header("Location: inventario_listar.php");
        exit;
    } else $error = $stmt->error;
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Registrar Lote</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  <form method="post" style="display:grid;gap:10px;max-width:720px;">
    <label>Lote</label><input name="lote" required>
    <label>Fecha entrada</label><input type="date" name="fecha_entrada" required>
    <label>Fecha vencimiento</label><input type="date" name="fecha_vencimiento" required>
    <div style="display:flex;gap:10px;">
      <div style="flex:1"><label>Stock inicial</label><input type="number" name="stock_inicial" min="0" value="0" required></div>
      <div style="flex:1"><label>Stock actual</label><input type="number" name="stock_actual" min="0" value="0" required></div>
    </div>
    <label>Clasificación</label><input name="clasificacion" placeholder="AA / A / AAA">
    <label>Estado</label>
    <select name="estado">
      <option value="Disponible">Disponible</option>
      <option value="Próximo a Vencer">Próximo a Vencer</option>
      <option value="Vencido">Vencido</option>
    </select>
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn-outline" href="inventario_listar.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
