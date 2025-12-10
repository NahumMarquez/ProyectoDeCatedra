<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $r = $conn->query("SELECT * FROM reportes WHERE id_report = $id")->fetch_assoc();
    if (!$r) { die("Reporte no encontrado."); }
    $type = $r['type'];
    $params = json_decode($r['params'], true);

    // Para simplicidad: si existe archivo PDF lo entregamos, si no generamos CSV simple según tipo
    $exportsDir = __DIR__ . '/exports';
    if ($r['filename'] && file_exists($exportsDir . '/' . $r['filename'])) {
        // descargar PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.basename($r['filename']).'"');
        readfile($exportsDir . '/' . $r['filename']);
        exit;
    } else {
        // Generar CSV en memoria según tipo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export_'.$type.'_'.date('Ymd_His').'.csv');
        $out = fopen('php://output','w');

        if ($type == 'produccion') {
            fputcsv($out, ['Lote','Fecha','Cantidad','Usuario','Observaciones']);
            $sql = "SELECT p.*, i.lote, u.nombre as usuario FROM produccion p LEFT JOIN inventario i ON p.id_inventario=i.id_inventario LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario WHERE 1=1";
            if (!empty($params['fecha_desde'])) $sql .= " AND p.fecha >= '".$conn->real_escape_string($params['fecha_desde'])."'";
            if (!empty($params['fecha_hasta'])) $sql .= " AND p.fecha <= '".$conn->real_escape_string($params['fecha_hasta'])."'";
            $res = $conn->query($sql);
            while($row = $res->fetch_assoc()) {
                fputcsv($out, [$row['lote'] ?? 'Sin lote', $row['fecha'], $row['cantidad_recolectada'], $row['usuario'], $row['observaciones']]);
            }
        }
        elseif ($type == 'inventario') {
            fputcsv($out, ['Lote','Entrada','Vencimiento','Stock Inicial','Stock Actual','Clasificación','Estado']);
            $sql = "SELECT * FROM inventario WHERE 1=1";
            if (!empty($params['fecha_desde'])) $sql .= " AND fecha_entrada >= '".$conn->real_escape_string($params['fecha_desde'])."'";
            if (!empty($params['fecha_hasta'])) $sql .= " AND fecha_entrada <= '".$conn->real_escape_string($params['fecha_hasta'])."'";
            $res = $conn->query($sql);
            while($row = $res->fetch_assoc()) {
                fputcsv($out, [$row['lote'],$row['fecha_entrada'],$row['fecha_vencimiento'],$row['stock_inicial'],$row['stock_actual'],$row['clasificacion'],$row['estado']]);
            }
        } else { // ventas
            fputcsv($out, ['ID','Fecha','Cliente','Vendedor','Total','Estado']);
            $sql = "SELECT v.*, c.nombre as cliente, u.nombre as vendedor FROM ventas v LEFT JOIN clientes c ON v.id_cliente=c.id_cliente LEFT JOIN usuarios u ON v.id_usuario=u.id_usuario WHERE 1=1";
            if (!empty($params['fecha_desde'])) $sql .= " AND v.fecha_venta >= '".$conn->real_escape_string($params['fecha_desde'])."'";
            if (!empty($params['fecha_hasta'])) $sql .= " AND v.fecha_venta <= '".$conn->real_escape_string($params['fecha_hasta'])."'";
            $res = $conn->query($sql);
            while($row = $res->fetch_assoc()) {
                fputcsv($out, [$row['id_venta'],$row['fecha_venta'],$row['cliente'],$row['vendedor'],$row['total'],$row['estado']]);
            }
        }
        fclose($out);
        exit;
    }
}
header("Location: reportes_listar.php");
exit;
