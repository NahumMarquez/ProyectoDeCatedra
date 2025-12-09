<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: clientes_listar.php"); exit; }

$stmt = $conn->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

if (!$cliente) { header("Location: clientes_listar.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $correo = $conn->real_escape_string($_POST['correo']);
    $estado = $_POST['estado'];
    
    $upd = $conn->prepare("UPDATE clientes SET nombre=?, telefono=?, direccion=?, correo=?, estado=? WHERE id_cliente=?");
    $upd->bind_param("sssssi", $nombre, $telefono, $direccion, $correo, $estado, $id);
    
    if ($upd->execute()) {
        header("Location: clientes_listar.php");
        exit;
    } else {
        $error = $upd->error;
    }
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Editar Cliente</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  
  <form method="post" style="display:grid;gap:15px;max-width:720px;">
    <div>
      <label>Nombre</label>
      <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
      <div>
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>">
      </div>
      <div>
        <label>Correo</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo']) ?>">
      </div>
    </div>
    
    <div>
      <label>Dirección</label>
      <textarea name="direccion" rows="2"><?= htmlspecialchars($cliente['direccion']) ?></textarea>
    </div>
    
    <div>
      <label>Estado</label>
      <select name="estado">
        <option value="activo" <?= $cliente['estado']=='activo'?'selected':'' ?>>Activo</option>
        <option value="inactivo" <?= $cliente['estado']=='inactivo'?'selected':'' ?>>Inactivo</option>
      </select>
    </div>
    
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn-outline" href="clientes_listar.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>