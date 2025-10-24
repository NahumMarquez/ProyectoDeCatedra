<?php
session_start();
if ($_SESSION['rol'] != 'administrador') { header("Location: dashboard.php"); exit(); }
require_once(__DIR__ . '/../../conexion.php');

$id = $_GET['id'];
$u = $conn->query("SELECT * FROM usuarios WHERE id_usuario=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $usuario = $_POST['usuario'];
  $rol = $_POST['rol'];
  $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE id_usuario=?");
  $stmt->bind_param("sssi", $nombre, $usuario, $rol, $id);
  $stmt->execute();
  header("Location: usuarios_listar.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Editar Usuario</title></head>
<body>
<h2>Editar Usuario</h2>
<form method="POST">
  <label>Nombre</label><br><input type="text" name="nombre" value="<?= $u['nombre'] ?>"><br>
  <label>Usuario</label><br><input type="text" name="usuario" value="<?= $u['usuario'] ?>"><br>
  <label>Rol</label><br>
  <select name="rol">
    <option value="administrador" <?= $u['rol']=='administrador'?'selected':'' ?>>Administrador</option>
    <option value="empleado" <?= $u['rol']=='empleado'?'selected':'' ?>>Empleado</option>
    <option value="produccion" <?= $u['rol']=='produccion'?'selected':'' ?>>Producción</option>
    <option value="encargado" <?= $u['rol']=='encargado'?'selected':'' ?>>Encargado</option>
  </select><br><br>
  <button type="submit">Guardar Cambios</button>
</form>
<a href="usuarios_listar.php">Volver</a>
</body>
</html>
