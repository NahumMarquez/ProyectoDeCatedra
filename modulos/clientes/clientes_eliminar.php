<?php
session_start();
require_once __DIR__ . '/../../conexion.php';

// Solo administradores
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: clientes_listar.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    // Verificar si tiene ventas asociadas
    $check = $conn->query("SELECT COUNT(*) as total FROM ventas WHERE id_cliente = $id");
    $result = $check->fetch_assoc();
    
    if ($result['total'] > 0) {
        // No eliminar, solo desactivar
        $conn->query("UPDATE clientes SET estado = 'inactivo' WHERE id_cliente = $id");
    } else {
        // Eliminar si no tiene ventas
        $conn->query("DELETE FROM clientes WHERE id_cliente = $id");
    }
}

header("Location: clientes_listar.php");
exit;