<?php
session_start();
require_once(__DIR__ . '/../../conexion.php');

$usuario = $_POST['usuario'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';

$sql = "SELECT * FROM usuarios WHERE usuario = ? AND estado = 'activo'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($contrasena, $row['contrasena'])) {
        $_SESSION['id_usuario'] = $row['id_usuario'];
        $_SESSION['usuario'] = $row['usuario'];
        $_SESSION['rol'] = $row['rol'];
        header("Location: ../dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Contraseña incorrecta.";
    }
} else {
    $_SESSION['error'] = "Usuario no encontrado o inactivo.";
}
header("Location: index.php");
header("Location: crear_producto.php");
?>
