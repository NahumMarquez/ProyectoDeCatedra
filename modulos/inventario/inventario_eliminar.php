<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    // eliminar movimientos relacionados primero (opcional)
    $stmt = $conn->prepare("DELETE FROM movimientos_inventario WHERE id_inventario = ?");
    $stmt->bind_param("i",$id);
    $stmt->execute();

    $d = $conn->prepare("DELETE FROM inventario WHERE id_inventario = ?");
    $d->bind_param("i",$id);
    $d->execute();
}
header("Location: inventario_listar.php");
exit;
