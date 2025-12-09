<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $correo = $conn->real_escape_string($_POST['correo']);
    
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, telefono, direccion, correo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $telefono, $direccion, $correo);
    
    if ($stmt->execute()) {
        header("Location: clientes_listar.php");
        exit;
    } else {
        $error = $stmt->error;
    }
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Nuevo Cliente</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  
  <form method="post" style="display:grid;gap:15px;max-width:720px;">
    <div>
      <label>Nombre *</label>
      <input type="text" name="nombre" required>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
      <div>
        <label>Teléfono</label>
        <input type="text" name="telefono">
      </div>
      <div>
        <label>Correo</label>
        <input type="email" name="correo">
      </div>
    </div>
    
    <div>
      <label>Dirección</label>
      <textarea name="direccion" rows="2"></textarea>
    </div>
    
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn-outline" href="clientes_listar.php">Cancelar</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/../../inc/footer.php'; ?>