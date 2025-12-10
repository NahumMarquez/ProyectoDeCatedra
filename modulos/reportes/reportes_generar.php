<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$tipo = $_GET['tipo'] ?? 'produccion'; // produccion|inventario|ventas
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$export_csv = isset($_GET['export']) && $_GET['export'] === 'csv';

$html = "<h2>Reporte: " . htmlspecialchars(ucfirst($tipo)) . "</h2>";
$html .= "<p>Generado por: " . htmlspecialchars($_SESSION['usuario']) . " - " . date('d/m/Y H:i') . "</p>";
$html .= "<hr>";

// construir contenido según tipo
if ($tipo === 'produccion') {
    $sql = "SELECT p.*, i.lote, u.nombre as usuario FROM produccion p 
            LEFT JOIN inventario i ON p.id_inventario=i.id_inventario 
            LEFT JOIN usuarios u ON p.id_usuario=u.id_usuario
            WHERE 1=1";
    if ($fecha_desde) $sql .= " AND p.fecha >= '" . $conn->real_escape_string($fecha_desde) . "'";
    if ($fecha_hasta) $sql .= " AND p.fecha <= '" . $conn->real_escape_string($fecha_hasta) . "'";
    $sql .= " ORDER BY p.fecha DESC";
    $res = $conn->query($sql);

    // HTML table
    $html .= "<table style='width:100%;border-collapse:collapse;'><thead><tr>
              <th>Lote</th><th>Fecha</th><th>Cantidad</th><th>Usuario</th><th>Observaciones</th></tr></thead><tbody>";
    $rows_for_csv = [];
    while($r = $res->fetch_assoc()) {
        $html .= "<tr>
                    <td>" . htmlspecialchars($r['lote'] ?? 'Sin lote') . "</td>
                    <td>" . $r['fecha'] . "</td>
                    <td>" . number_format($r['cantidad_recolectada']) . "</td>
                    <td>" . htmlspecialchars($r['usuario']) . "</td>
                    <td>" . htmlspecialchars($r['observaciones']) . "</td>
                  </tr>";
        $rows_for_csv[] = [
            'lote' => $r['lote'] ?? 'Sin lote',
            'fecha' => $r['fecha'],
            'cantidad' => $r['cantidad_recolectada'],
            'usuario' => $r['usuario'],
            'observaciones' => $r['observaciones']
        ];
    }
    $html .= "</tbody></table>";
}
elseif ($tipo === 'inventario') {
    $sql = "SELECT * FROM inventario WHERE 1=1";
    if ($fecha_desde) $sql .= " AND fecha_entrada >= '" . $conn->real_escape_string($fecha_desde) . "'";
    if ($fecha_hasta) $sql .= " AND fecha_entrada <= '" . $conn->real_escape_string($fecha_hasta) . "'";
    $sql .= " ORDER BY fecha_entrada DESC";
    $res = $conn->query($sql);

    $html .= "<table style='width:100%;border-collapse:collapse;'><thead><tr>
              <th>Lote</th><th>Entrada</th><th>Vencimiento</th><th>Stock Actual</th><th>Estado</th></tr></thead><tbody>";
    $rows_for_csv = [];
    while($r = $res->fetch_assoc()) {
        $html .= "<tr>
                    <td>" . htmlspecialchars($r['lote']) . "</td>
                    <td>" . $r['fecha_entrada'] . "</td>
                    <td>" . $r['fecha_vencimiento'] . "</td>
                    <td>" . number_format($r['stock_actual']) . "</td>
                    <td>" . htmlspecialchars($r['estado']) . "</td>
                  </tr>";
        $rows_for_csv[] = [
            'lote' => $r['lote'],
            'fecha_entrada' => $r['fecha_entrada'],
            'fecha_vencimiento' => $r['fecha_vencimiento'],
            'stock_actual' => $r['stock_actual'],
            'estado' => $r['estado']
        ];
    }
    $html .= "</tbody></table>";
}
else { // ventas
    $sql = "SELECT v.*, c.nombre as cliente, u.nombre as vendedor FROM ventas v 
            LEFT JOIN clientes c ON v.id_cliente=c.id_cliente 
            LEFT JOIN usuarios u ON v.id_usuario=u.id_usuario
            WHERE 1=1";
    if ($fecha_desde) $sql .= " AND v.fecha_venta >= '" . $conn->real_escape_string($fecha_desde) . "'";
    if ($fecha_hasta) $sql .= " AND v.fecha_venta <= '" . $conn->real_escape_string($fecha_hasta) . "'";
    $sql .= " ORDER BY v.fecha_venta DESC";
    $res = $conn->query($sql);

    $html .= "<table style='width:100%;border-collapse:collapse;'><thead><tr>
              <th>ID</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead><tbody>";
    $rows_for_csv = [];
    while($r = $res->fetch_assoc()) {
        $html .= "<tr>
                    <td>" . $r['id_venta'] . "</td>
                    <td>" . $r['fecha_venta'] . "</td>
                    <td>" . htmlspecialchars($r['cliente']) . "</td>
                    <td>$" . number_format($r['total'],2) . "</td>
                    <td>" . htmlspecialchars($r['estado']) . "</td>
                  </tr>";
        $rows_for_csv[] = [
            'id_venta' => $r['id_venta'],
            'fecha_venta' => $r['fecha_venta'],
            'cliente' => $r['cliente'],
            'total' => $r['total'],
            'estado' => $r['estado']
        ];
    }
    $html .= "</tbody></table>";
}

// directorio de exports
$filename = uniqid("reporte_") . ".pdf";
$exportsDir = __DIR__ . '/exports';
if (!is_dir($exportsDir)) mkdir($exportsDir, 0755, true);

// Si piden export CSV -> generar y forzar descarga CSV (compatible con Excel)
if ($export_csv) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($rows_for_csv)) {
        // cabeceras según el primer elemento
        fputcsv($out, array_keys($rows_for_csv[0]));
        foreach ($rows_for_csv as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

// Intentar generar PDF con Dompdf si está instalado (vendor/autoload.php)
$vendor = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
    try {
        // instanciar usando nombre totalmente calificado (evita "use" en medio del archivo)
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($exportsDir . '/' . $filename, $output);

        // guardar registro en BD (si la tabla reportes existe)
        $params = json_encode(['tipo'=>$tipo,'fecha_desde'=>$fecha_desde,'fecha_hasta'=>$fecha_hasta]);
        if ($ins = $conn->prepare("INSERT INTO reportes (type, params, filename, created_by) VALUES (?, ?, ?, ?)")) {
            $ins->bind_param("sssi", $tipo, $params, $filename, $_SESSION['id_usuario']);
            $ins->execute();
        }

        // Forzar descarga/visualización en navegador
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo file_get_contents($exportsDir . '/' . $filename);
        exit;
    } catch (Exception $e) {
        // fallback a HTML si falla Dompdf
        echo "<h3>Generación PDF falló:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo $html;
        echo "<p><a href=\"?tipo=" . urlencode($tipo) . "&fecha_desde=" . urlencode($fecha_desde) . "&fecha_hasta=" . urlencode($fecha_hasta) . "&export=csv\">⬇️ Descargar CSV (Excel)</a></p>";
        exit;
    }
} else {
    // Dompdf no instalado -> mostrar HTML y ofrecer CSV
    echo "<h3>Generación de PDF no disponible</h3>";
    echo "<p>Para habilitar generación de PDF instale Dompdf con Composer:</p>";
    echo "<pre>composer require dompdf/dompdf</pre>";
    echo $html;
    echo "<p><a href=\"?tipo=" . urlencode($tipo) . "&fecha_desde=" . urlencode($fecha_desde) . "&fecha_hasta=" . urlencode($fecha_hasta) . "&export=csv\">⬇️ Descargar CSV (para abrir con Excel)</a></p>";
    exit;
}