<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    $id_inventario = intval($_POST['id_inventario']) ?: NULL;
    $fecha = $_POST['fecha'];
    $cantidad = intval($_POST['cantidad']);
    $obs = $conn->real_escape_string($_POST['observaciones']);

    $stmt = $conn->prepare("INSERT INTO produccion (id_usuario, id_inventario, fecha, cantidad_recolectada, observaciones) VALUES (?,?,?,?,?)");
    $stmt->bind_param("iisss", $id_usuario, $id_inventario, $fecha, $cantidad, $obs);
    if ($stmt->execute()) {
        // actualizar inventario si vinculó lote (sumar o restar según tu lógica)
        if (!empty($id_inventario)) {
            $u = $conn->prepare("UPDATE inventario SET stock_actual = stock_actual + ? WHERE id_inventario = ?");
            $u->bind_param("ii", $cantidad, $id_inventario);
            $u->execute();

            // registrar movimiento entrada por producción (opcional)
            $m = $conn->prepare("INSERT INTO movimientos_inventario (id_inventario, tipo, cantidad, descripcion, registrado_por) VALUES (?, 'entrada', ?, 'Recolección a inventario', ?)");
            $m->bind_param("iii", $id_inventario, $cantidad, $_SESSION['id_usuario']);
            $m->execute();
        }
        header("Location: produccion_listar.php");
        exit;
    } else $error = $stmt->error;
}

// obtener lotes y usuarios para selects
$inventarios = $conn->query("SELECT id_inventario, lote, clasificacion FROM inventario ORDER BY fecha_entrada DESC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Registrar Producción</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  <form method="post" style="display:grid;gap:8px;max-width:680px;">
    <label>Lote (opcional)</label>
    <select name="id_inventario">
      <option value="">-- Sin lote --</option>
      <?php while($l = $inventarios->fetch_assoc()): ?>
        <option value="<?= $l['id_inventario'] ?>"><?= htmlspecialchars($l['lote'].' - '.$l['clasificacion']) ?></option>
      <?php endwhile; ?>
    </select>
    <label>Fecha</label><input type="date" name="fecha" required>
    <label>Cantidad recolectada</label><input type="number" name="cantidad" min="0" required>
    <label>Observaciones</label><textarea name="observaciones"></textarea>
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Cancelar</button>
      <a class="btn-outline" href="produccion_listar.php">Guardar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
