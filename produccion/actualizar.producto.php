<?php
include('conexion.php'); 

$mensaje = '';
$id_registro = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_registro = $_POST['id_registro'] ?? null;
    $id_lote = $_POST['id_lote'] ?? null;
    $cantidad_huevos = $_POST['cantidad_huevos'] ?? null;

    if (empty($id_registro) || empty($id_lote) || !is_numeric($cantidad_huevos) || $cantidad_huevos < 0) {
        $mensaje = "Error: Faltan datos o la cantidad es inválida.";
    } else {
        // Usando Sentencias Preparadas
        $sql = "UPDATE produccion SET id_lote = ?, cantidad_huevos = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iii", $id_lote, $cantidad_huevos, $id_registro);
            
            if (mysqli_stmt_execute($stmt)) {
                // Redirige después de la actualización exitosa
                header("Location: produccion_listar.php?mensaje=" . urlencode("Registro #$id_registro actualizado exitosamente."));
                exit;
            } else {
                $mensaje = "❌ Error al actualizar: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Obtener los datos actuales del registro
if ($id_registro) {
    $sql_fetch = "SELECT id_lote, cantidad_huevos FROM produccion WHERE id = ?";
    $stmt_fetch = mysqli_prepare($conexion, $sql_fetch);

    if ($stmt_fetch) {
        mysqli_stmt_bind_param($stmt_fetch, "i", $id_registro);
        mysqli_stmt_execute($stmt_fetch);
        $resultado = mysqli_stmt_get_result($stmt_fetch);
        $datos = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt_fetch);

        if (!$datos) {
            $mensaje = "Error: Registro no encontrado.";
            $id_registro = null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Actualizar Producción</title>
</head>
<body>
    <h1>Actualizar Registro de Producción #<?= htmlspecialchars($id_registro) ?></h1>
    <p><a href="produccion_listar.php">Volver al Historial</a></p>
    <?php if (!empty($mensaje)) { echo "<p style='color: red;'>$mensaje</p>"; } ?>
    
    <?php if ($id_registro && isset($datos)): ?>
    <form action="produccion_actualizar.php" method="POST">
        <input type="hidden" name="id_registro" value="<?= htmlspecialchars($id_registro) ?>">
        
        <label for="id_lote">Lote:</label>
        <input type="number" id="id_lote" name="id_lote" required value="<?= htmlspecialchars($datos['id_lote']) ?>"> 
        
        <label for="cantidad_huevos">Cantidad de Huevos:</label>
        <input type="number" id="cantidad_huevos" name="cantidad_huevos" min="0" required value="<?= htmlspecialchars($datos['cantidad_huevos']) ?>">
        
        <button type="submit">Guardar Cambios</button>
    </form>
    <?php else: ?>
        <p>Seleccione un registro válido para editar.</p>
    <?php endif; ?>
</body>
</html>