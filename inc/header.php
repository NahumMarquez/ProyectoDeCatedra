<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../conexion.php';

// Variables de sesión usadas en tu login
$usuario_nombre = $_SESSION['usuario'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Venta de Huevos El Campo</title>
  <link rel="stylesheet" href="/css/main.css">
</head>
<body>

<header class="topbar">
  <div class="logo-area">
    <div class="avatar">O</div>
    <div>
      <h1>Venta de Huevos El Campo</h1>
      <p>Sistema de Gestión</p>
    </div>
  </div>
  <div class="user-area">
    <span>Bienvenido <?= htmlspecialchars($usuario_nombre) ?></span>
    <a href="/modulos/login/logout.php" class="btn-logout">⎋ Salir</a>
  </div>
</header>

<nav class="tabs">
  <a href="/modulos/inventario/inventario_listar.php"
     class="tab <?= (strpos($_SERVER['PHP_SELF'], 'inventario') !== false) ? 'active' : '' ?>">Inventario</a>

  <a href="/modulos/produccion/produccion_listar.php"
     class="tab <?= (strpos($_SERVER['PHP_SELF'], 'produccion') !== false) ? 'active' : '' ?>">Producción</a>

  <a href="/modulos/ventas/ventas_listar.php"
     class="tab <?= (strpos($_SERVER['PHP_SELF'], 'ventas') !== false || strpos($_SERVER['PHP_SELF'], 'clientes') !== false) ? 'active' : '' ?>">Ventas</a>

  <a href="/modulos/reportes/reportes_listar.php"
     class="tab <?= (strpos($_SERVER['PHP_SELF'], 'reportes') !== false) ? 'active' : '' ?>">Reportes</a>

  <?php if ($rol === 'administrador'): ?>
    <a href="/modulos/login/usuarios_listar.php"
       class="tab <?= (strpos($_SERVER['PHP_SELF'], 'usuarios') !== false) ? 'active' : '' ?>">Usuarios</a>
  <?php endif; ?>
</nav>


<main class="content">