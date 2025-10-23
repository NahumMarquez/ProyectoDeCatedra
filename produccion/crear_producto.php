<?php
include('conexion.php'); 

$mensaje = ''; 
$id_empleado = 1; // ID de empleado estática

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_lote = $_POST['id_lote'] ?? null;
    $cantidad_huevos = $_POST['cantidad_huevos'] ?? null;

    if (empty($id_lote) || !is_numeric($cantidad_huevos) || $cantidad_huevos < 0) {
        $mensaje = "Error: La cantidad de huevos debe ser un número positivo y el lote es obligatorio.";
    } else {
        $fecha_registro = date("Y-m-d H:i:s");
        
        // Usando Sentencias Preparadas (¡Manteniendo la seguridad contra Inyección SQL!)
        $sql = "INSERT INTO produccion (id_lote, cantidad_huevos, fecha_registro, id_empleado) 
                VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexion, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iisi", $id_lote, $cantidad_huevos, $fecha_registro, $id_empleado);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "✅ Registro de producción creado exitosamente.";
            } else {
                $mensaje = "❌ Error al crear el registro: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "❌ Error al preparar la consulta: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registrar Producción</title>
<link rel="alternate" href="./producto.css" type="application/atom+xml" title="Atom">
</head>
<body>
    <h1>Registro de Recolección de Huevos</h1>
    <p><a href="produccion_listar.php">Ver Historial</a></p>
    <?php if (!empty($mensaje)) { echo "<p>$mensaje</p>"; } ?>
    
    <form action="produccion_crear.php" method="POST">
        <label for="id_lote">Lote:</label>
        <input type="number" id="id_lote" name="id_lote" required> 
        
        <label for="cantidad_huevos">Cantidad de Huevos:</label>
        <input type="number" id="cantidad_huevos" name="cantidad_huevos" min="0" required>
        
        <button type="submit">Registrar Producción</button>
    </form>
</body>
</html>