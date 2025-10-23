<?php
session_start();
// 1. Protección de sesión: verifica si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel principal</title>
<link rel="stylesheet" href="../css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="page">
  <div class="header-panel">
    <h2>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> 👋</h2>
    <a href="logout.php" class="btn-outline">Cerrar sesión</a>
  </div>

  <div class="acciones-contenedor">
    
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
    <a href="usuarios_listar.php" class="btn-new">👥 Gestión de Usuarios</a>
    <?php else: ?>
    <p>Solo los administradores pueden gestionar usuarios.</p>
    <?php endif; ?>

<a href="../produccion/crear_producto.php" class="btn-new">📦 Registrar Producción</a>

    <a href="cambiar_contrasena.php" class="btn-new">🔑 Cambiar contraseña</a>
  </div>

</div>
</body>
</html>