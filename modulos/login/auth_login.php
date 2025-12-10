<?php 
session_start();
require_once(__DIR__ . '/../../conexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

$sql = "SELECT * FROM usuarios WHERE usuario = ? AND estado = 'activo' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    if (password_verify($contrasena, $row['contrasena'])) {

        // Guardar sesión
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol'] = $row['rol'];
        $_SESSION['nombre'] = $row['nombre'];

        // Ruta correcta
        header("Location: ../../modulos/dashboard.php");
        exit;

    } else {
        $_SESSION['error'] = "Contraseña incorrecta.";
    }

} else {
    $_SESSION['error'] = "Usuario no encontrado o inactivo.";
}

header("Location: index.php");
exit;
?>
