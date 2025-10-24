<?php
session_start();
if ($_SESSION['rol'] != 'administrador') { header("Location: dashboard.php"); exit(); }
require_once(__DIR__ . '/../../conexion.php');

$id = $_GET['id'];
$u = $conn->query("SELECT estado FROM usuarios WHERE id_usuario=$id")->fetch_assoc();
$newState = ($u['estado'] == 'activo') ? 'inactivo' : 'activo';
$conn->query("UPDATE usuarios SET estado='$newState' WHERE id_usuario=$id");
header("Location: usuarios_listar.php");
exit();
?>
