<?php
require_once(__DIR__ . '/../../conexion.php');

echo "Conexión correcta.<br>";

$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);

if ($result) {
    echo "Usuarios encontrados: " . $result->num_rows;
} else {
    echo "Error en la consulta.";
}
?>
