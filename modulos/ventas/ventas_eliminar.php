<?php
session_start();
require_once __DIR__ . '/../../conexion.php';

// Solo administradores pueden eliminar
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ventas_listar.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $conn->begin_transaction();
    
    try {
        // Obtener detalles para devolver stock
        $detalles = $conn->query("SELECT id_inventario, cantidad FROM detalle_ventas WHERE id_venta = $id");
        
        while($det = $detalles->fetch_assoc()) {
            // Devolver stock al inventario
            $conn->query("UPDATE inventario SET stock_actual = stock_actual + {$det['cantidad']} WHERE id_inventario = {$det['id_inventario']}");
            
            // Registrar movimiento de devolución
            $mov = $conn->prepare("INSERT INTO movimientos_inventario (id_inventario, tipo, cantidad, descripcion, registrado_por) VALUES (?, 'entrada', ?, 'Devolución por eliminación de venta', ?)");
            $mov->bind_param("iii", $det['id_inventario'], $det['cantidad'], $_SESSION['id_usuario']);
            $mov->execute();
        }
        
        // Eliminar detalles (cascade debería hacerlo automáticamente)
        $conn->query("DELETE FROM detalle_ventas WHERE id_venta = $id");
        
        // Eliminar venta
        $conn->query("DELETE FROM ventas WHERE id_venta = $id");
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}

header("Location: ventas_listar.php");
exit;