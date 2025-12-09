<?php
session_start();
require_once __DIR__ . '/../../conexion.php';
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php'); exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente']);
    $fecha_venta = $_POST['fecha_venta'];
    $observaciones = $conn->real_escape_string($_POST['observaciones']);
    
    // Validar que hay al menos un producto
    if (empty($_POST['lotes']) || empty($_POST['cantidades']) || empty($_POST['precios'])) {
        $error = "Debe agregar al menos un lote a la venta.";
    } else {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // Insertar venta
            $stmt = $conn->prepare("INSERT INTO ventas (id_cliente, id_usuario, fecha_venta, total, observaciones) VALUES (?, ?, ?, 0, ?)");
            $stmt->bind_param("iiss", $id_cliente, $_SESSION['id_usuario'], $fecha_venta, $observaciones);
            $stmt->execute();
            $id_venta = $conn->insert_id;
            
            $total = 0;
            
            // Insertar detalles y actualizar inventario
            foreach ($_POST['lotes'] as $index => $id_inventario) {
                $cantidad = intval($_POST['cantidades'][$index]);
                $precio = floatval($_POST['precios'][$index]);
                $subtotal = $cantidad * $precio;
                $total += $subtotal;
                
                // Verificar stock disponible
                $check = $conn->query("SELECT stock_actual FROM inventario WHERE id_inventario = $id_inventario");
                $stock_data = $check->fetch_assoc();
                
                if ($stock_data['stock_actual'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el lote seleccionado.");
                }
                
                // Insertar detalle
                $det = $conn->prepare("INSERT INTO detalle_ventas (id_venta, id_inventario, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $det->bind_param("iiidd", $id_venta, $id_inventario, $cantidad, $precio, $subtotal);
                $det->execute();
                
                // Actualizar inventario (restar stock)
                $nuevo_stock = $stock_data['stock_actual'] - $cantidad;
                $estado = $nuevo_stock == 0 ? 'Agotado' : 'Disponible';
                $upd = $conn->prepare("UPDATE inventario SET stock_actual = ?, estado = ? WHERE id_inventario = ?");
                $upd->bind_param("isi", $nuevo_stock, $estado, $id_inventario);
                $upd->execute();
                
                // Registrar movimiento
                $mov = $conn->prepare("INSERT INTO movimientos_inventario (id_inventario, tipo, cantidad, descripcion, registrado_por) VALUES (?, 'salida', ?, 'Venta', ?)");
                $mov->bind_param("iii", $id_inventario, $cantidad, $_SESSION['id_usuario']);
                $mov->execute();
            }
            
            // Actualizar total de la venta
            $conn->query("UPDATE ventas SET total = $total WHERE id_venta = $id_venta");
            
            $conn->commit();
            header("Location: ventas_listar.php");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Obtener clientes y lotes disponibles
$clientes = $conn->query("SELECT * FROM clientes WHERE estado = 'activo' ORDER BY nombre");
$lotes = $conn->query("SELECT id_inventario, lote, clasificacion, stock_actual FROM inventario WHERE stock_actual > 0 AND estado != 'Vencido' ORDER BY fecha_entrada ASC");
?>
<?php include __DIR__ . '/../../inc/header.php'; ?>
<div class="card-table">
  <h3>Nueva Venta</h3>
  <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>
  
  <form method="post" id="formVenta" style="display:grid;gap:15px;max-width:900px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
      <div>
        <label>Cliente *</label>
        <select name="id_cliente" required>
          <option value="">Seleccionar cliente...</option>
          <?php while($c = $clientes->fetch_assoc()): ?>
            <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div>
        <label>Fecha de venta *</label>
        <input type="date" name="fecha_venta" value="<?= date('Y-m-d') ?>" required>
      </div>
    </div>
    
    <div>
      <label>Observaciones</label>
      <textarea name="observaciones" rows="2"></textarea>
    </div>
    
    <hr>
    
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h4>Productos</h4>
      <button type="button" class="btn-outline" onclick="agregarLinea()">+ Agregar Lote</button>
    </div>
    
    <div id="lineas-container">
      <!-- Las líneas de productos se agregarán aquí -->
    </div>
    
    <div style="text-align:right;font-size:20px;font-weight:bold;padding:15px;background:#f8f9fa;border-radius:8px;">
      Total: $<span id="total-venta">0.00</span>
    </div>
    
    <div style="text-align:right;">
      <button class="btn btn-primary" type="submit">💾 Guardar Venta</button>
      <a class="btn-outline" href="ventas_listar.php">Cancelar</a>
    </div>
  </form>
</div>

<script>
const lotes = <?= json_encode($conn->query("SELECT id_inventario, lote, clasificacion, stock_actual FROM inventario WHERE stock_actual > 0 AND estado != 'Vencido' ORDER BY fecha_entrada ASC")->fetch_all(MYSQLI_ASSOC)) ?>;

let lineaCounter = 0;

function agregarLinea() {
  const container = document.getElementById('lineas-container');
  const linea = document.createElement('div');
  linea.className = 'linea-producto';
  linea.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:10px;margin-bottom:10px;padding:10px;background:#f8f9fa;border-radius:8px;';
  linea.innerHTML = `
    <select name="lotes[]" onchange="calcularSubtotal(this)" required>
      <option value="">Seleccionar lote...</option>
      ${lotes.map(l => `<option value="${l.id_inventario}" data-stock="${l.stock_actual}">${l.lote} - ${l.clasificacion} (Stock: ${l.stock_actual})</option>`).join('')}
    </select>
    <input type="number" name="cantidades[]" min="1" placeholder="Cantidad" onchange="calcularSubtotal(this)" required>
    <input type="number" name="precios[]" step="0.01" min="0.01" placeholder="Precio" onchange="calcularSubtotal(this)" required>
    <input type="text" class="subtotal" readonly placeholder="Subtotal" style="background:#fff;font-weight:bold;">
    <button type="button" class="btn-outline" onclick="eliminarLinea(this)" style="padding:10px;">🗑️</button>
  `;
  container.appendChild(linea);
  lineaCounter++;
}

function eliminarLinea(btn) {
  btn.closest('.linea-producto').remove();
  calcularTotal();
}

function calcularSubtotal(elem) {
  const linea = elem.closest('.linea-producto');
  const cantidad = parseFloat(linea.querySelector('input[name="cantidades[]"]').value) || 0;
  const precio = parseFloat(linea.querySelector('input[name="precios[]"]').value) || 0;
  const subtotal = cantidad * precio;
  linea.querySelector('.subtotal').value = '$' + subtotal.toFixed(2);
  calcularTotal();
}

function calcularTotal() {
  let total = 0;
  document.querySelectorAll('.linea-producto').forEach(linea => {
    const cantidad = parseFloat(linea.querySelector('input[name="cantidades[]"]').value) || 0;
    const precio = parseFloat(linea.querySelector('input[name="precios[]"]').value) || 0;
    total += cantidad * precio;
  });
  document.getElementById('total-venta').textContent = total.toFixed(2);
}

// Agregar primera línea automáticamente
agregarLinea();
</script>

<?php include __DIR__ . '/../../inc/footer.php'; ?>