<?php
session_start();
require_once(__DIR__ . '/../../conexion.php');
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') { die("Acceso denegado."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $rol = $_POST['rol'] ?? 'empleado';
    $password_plain = $_POST['contrasena'];

    if (empty($nombre) || empty($usuario) || empty($password_plain)) {
        $error = "Complete los campos obligatorios.";
    } else {
        $v = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
        $v->bind_param("s", $usuario);
        $v->execute();
        $v->store_result();
        if ($v->num_rows > 0) {
            $error = "Usuario ya existe.";
        } else {
            $hash = password_hash($password_plain, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO usuarios (nombre, usuario, correo, contrasena, rol, estado) VALUES (?, ?, ?, ?, ?, 'activo')");
            $ins->bind_param("sssss", $nombre, $usuario, $correo, $hash, $rol);
            if ($ins->execute()) {
                header('Location: usuarios_listar.php');
                exit;
            } else {
                $error = "Error al guardar: " . $ins->error;
            }
        }
        $v->close();
    }
}
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Agregar Usuario</title></head>
<body>
<h2>Nuevo Usuario</h2>
<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" style="display:grid;gap:10px;max-width:720px;">
  <label>Nombre completo</label><br><input name="nombre" required><br>
  <label>Usuario</label><br><input name="usuario" required><br>
  <label>Correo</label><br><input name="correo" type="email"><br>
  <label>Rol</label><br>
  <select name="rol">
    <option value="empleado">Empleado</option>
    <option value="produccion">Producción</option>
    <option value="encargado">Encargado</option>
    <option value="administrador">Administrador</option>
  </select><br>
  <label>Contraseña</label><br><input name="contrasena" type="password" required><br><br>
  <button type="submit">Guardar</button>
</form>
<a href="usuarios_listar.php">Volver</a>
</body>
</html>
