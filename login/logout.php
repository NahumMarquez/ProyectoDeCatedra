<?php
session_start();
session_destroy();
header("Location: index.php");
header("Location: crear_producto.php");
exit();
?>
