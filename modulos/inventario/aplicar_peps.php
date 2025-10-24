<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = intval($_POST['cantidad']);
    if ($cantidad <= 0) $msg = "Ingrese una cantidad válida.";
    else {
        $sql = "SELECT * FROM inventario WHERE stock_actual > 0 ORDER BY fecha_entrada ASC";
        $res = $conn->query($sql);
        $restante = $cantidad;
        while($row = $res->fetch_assoc() && $restante > 0) {
            // loop guard: use separate fetch loop below
        }
        // Re-do loop correctly:
        $res = $conn->query("SELECT * FROM inventario WHERE stock_actual > 0 ORDER BY fecha_entrada ASC");
        $restante = $cantidad;
        while($row = $res->fetch_assoc() ) {
            if ($restante <= 0) break;
            $id = $row['id_inventario'];
            $stock = intval($row['stock_actual']);
            $salida = min($stock, $restante);
            $nuevo = $stock - $salida;
            $estado = $nuevo == 0 ? 'Agotado' : $row['estado'];
            $u = $conn->prepare("UPDATE inventario SET stock_actual=?, estado=? WHERE id_inventario=?");
            $u->bind_param("isi", $nuevo, $estado, $id);
            $u->execute();

            $m = $conn->prepare("INSERT INTO movimientos_inventario (id_inventario, tipo, cantidad, descripcion, registrado_por) VALUES (?, 'salida', ?, ?, ?)");
            $desc = "Salida PEPS aplicada";
            $m->bind_param("iisi", $id, $salida, $desc, $_SESSION['id_usuario']);
            $m->execute();

            $restante -= $salida;
        }

        $msg = "PEPS aplicado. Restante no asignada: " . max(0,$restante);
    }
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Aplicar Método PEPS</h3>
  <?php if ($msg) echo "<div class='alert success'>$msg</div>"; ?>
  <form method="post" style="max-width:480px;display:grid;gap:8px;">
    <label>Cantidad a retirar</label>
    <input type="number" name="cantidad" min="1" required>
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Aplicar PEPS</button>
      <a class="btn-outline" href="inventario_listar.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>
