<?php
require_once(__DIR__ . '/../../conexion.php');

// Datos del admin
$usuario = 'admin';
$nombre = 'Administrador Principal';
$correo = 'admin@elcampo.com';
$password_plain = 'Admin123!';
$rol = 'administrador';
$estado = 'activo';

// Verificar si ya existe
$stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "El usuario ya existe.";
    exit;
}
$stmt->close();

// Hashear la contraseña
$hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Insertar
$ins = $conn->prepare("INSERT INTO usuarios (nombre, usuario, correo, contrasena, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
$ins->bind_param("ssssss", $nombre, $usuario, $correo, $hash, $rol, $estado);
if ($ins->execute()) {
    echo "Administrador creado correctamente. Usuario: $usuario, Contraseña: $password_plain";
} else {
    echo "Error al crear admin: " . $ins->error;
}
$ins->close();
$conn->close();
?>
