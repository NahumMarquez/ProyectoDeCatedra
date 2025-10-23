<?php
include('conexion.php'); 

$id_registro = $_GET['id'] ?? null;
$mensaje = '';

if ($id_registro) {
    // Usando Sentencias Preparadas
    $sql = "DELETE FROM produccion WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_registro);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "✅ Registro de producción eliminado exitosamente.";
        } else {
            $mensaje = "❌ Error al eliminar el registro: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $mensaje = "❌ Error al preparar la consulta: " . mysqli_error($conexion);
    }
} else {
    $mensaje = "Error: No se especificó el ID del registro a eliminar.";
}

// Redirige de vuelta a la lista con el mensaje
header("Location: produccion_listar.php?mensaje=" . urlencode($mensaje));
exit;
?>