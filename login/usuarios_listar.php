<?php
session_start();
if ($_SESSION['rol'] != 'administrador') { header("Location: dashboard.php"); exit(); }
require_once __DIR__ . '/../conexion.php';
$result = $conn->query("SELECT * FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Usuarios - Sistema Huevos</title>
<link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="page">
  <div class="header-panel">
    <h2>Gestión de Usuarios</h2>
    <a href="usuarios_agregar.php" class="btn-new">+ Nuevo Usuario</a>
  </div>
  <div class="card-table">
    <table class="table">
      <thead>
        <tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php while($u = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id_usuario'] ?></td>
          <td><?= $u['nombre'] ?></td>
          <td><?= $u['usuario'] ?></td>
          <td><span class="badge <?= $u['rol'] ?>"><?= $u['rol'] ?></span></td>
          <td><span class="badge <?= $u['estado'] ?>"><?= $u['estado'] ?></span></td>
          <td class="actions">
            <a href="usuarios_editar.php?id=<?= $u['id_usuario'] ?>">✏️</a>
            <a href="usuarios_baja.php?id=<?= $u['id_usuario'] ?>">🚫</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
