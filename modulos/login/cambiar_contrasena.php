<?php
session_start();
require_once(__DIR__ . '/../../conexion.php');
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}
$id = $_SESSION['id_usuario'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'];
    $nueva = $_POST['nueva'];
    $confirm = $_POST['confirm'];

    $stmt = $conn->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($actual, $hash)) {
        $msg = "La contraseña actual no coincide.";
    } elseif ($nueva !== $confirm) {
        $msg = "La nueva contraseña y su confirmación no coinciden.";
    } else {
        $newhash = password_hash($nueva, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
        $upd->bind_param("si", $newhash, $id);
        if ($upd->execute()) {
            $msg = "Contraseña actualizada.";
        } else {
            $msg = "Error al actualizar.";
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Cambiar contraseña</title></head>
<body>
<h2>Cambiar contraseña</h2>
<?php if ($msg) echo "<p>$msg</p>"; ?>
<form method="post">
  <label>Contraseña actual</label><br>
  <input type="password" name="actual" required><br>
  <label>Nueva contraseña</label><br>
  <input type="password" name="nueva" required><br>
  <label>Confirmar nueva contraseña</label><br>
  <input type="password" name="confirm" required><br><br>
  <button type="submit">Cambiar</button>
</form>
<a href="dashboard.php">Volver</a>
</body>
</html>
