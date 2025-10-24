<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistema Huevos</title>
  <link rel="stylesheet" href="../../css/login.css">
</head>
<body>
  <div class="card">
    <div class="avatar">🥚</div>
    <h1>Iniciar Sesión</h1>
    <p class="lead">Sistema de Gestión de Usuarios</p>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="auth_login.php" method="POST">
      <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="usuario" required>
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="contrasena" required>
      </div>
      <button class="btn btn-primary" type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
