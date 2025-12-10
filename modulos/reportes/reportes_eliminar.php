<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $r = $conn->query("SELECT filename FROM reportes WHERE id_report = $id")->fetch_assoc();
    if ($r && $r['filename']) {
        $file = __DIR__ . '/exports/' . $r['filename'];
        if (file_exists($file)) unlink($file);
    }
    $conn->query("DELETE FROM reportes WHERE id_report = $id");
}
header("Location: reportes_listar.php");
exit;
