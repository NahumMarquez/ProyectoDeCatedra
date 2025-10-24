<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $d = $conn->prepare("DELETE FROM produccion WHERE id_produccion = ?");
    $d->bind_param("i",$id);
    $d->execute();
}
header("Location: produccion_listar.php");
exit;
