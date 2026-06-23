<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
require_once __DIR__ . '/../../models/Venta.php';
require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Caja.php';

$ventaModel   = new Venta();
$productoModel= new Producto();
$cajaModel    = new Caja();

$ventas       = $ventaModel->listar(50);
$productos    = $productoModel->listarProductos();
$cajaAbierta  = $cajaModel->getCajaAbierta();
$totalHoy     = $ventaModel->totalHoy();
$contarHoy    = $ventaModel->contarHoy();
$base         = '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Ventas | MARTS</title>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
</head>
<body>
<div class="app-layout">
  <?php include '../layouts/sidebar.php'; ?>
  <div class="main-content" id="mainContent">
    <?php include '../layouts/header.php'; ?>
    <div class="page-content animate-fade-in">

      <div class="page-header">
        <div>
          <h1 class="page-title">Ventas</h1>
          <p class="page-subtitle">Registra y consulta las ventas realizadas</p>
        </div>
        <button class="btn btn-primary" onclick="openPanel('nueva')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Venta
        </button>
      </div>

      <?php if(isset($_SESSION['error_venta'])): ?>
      <div class="alert alert-error">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['error_venta']); unset($_SESSION['error_venta']); ?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_venta'])): ?>
      <div class="alert alert-success">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['exito_venta']); unset($_SESSION['exito_venta']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Estado de caja -->
      <?php if(!$cajaAbierta): ?>
      <div class="alert alert-warning" style="margin-bottom:1.25rem">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span>No hay caja abierta. Las ventas en efectivo no estarán disponibles. <a href="../caja/index.php" style="color:var(--gold);font-weight:600">Abrir caja →</a></span>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stats-grid-3" style="margin-bottom:1.75rem">
        <div class="stat-card animate-fade-in-up">
          <div><p class="stat-card-label">Ventas Hoy</p><p class="stat-card-value"><?php echo $contarHoy; ?></p><p class="stat-card-sub">transacciones</p></div>
          <div class="stat-card-icon green"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
        </div>
        <div class="stat-card animate-fade-in-up delay-100">
          <div><p class="stat-card-label">Total Hoy</p><p class="stat-card-value">$<?php echo number_format($totalHoy,2); ?></p><p class="stat-card-sub">en ventas</p></div>
          <div class="stat-card-icon amber"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        </div>
        <div class="stat-card animate-fade-in-up delay-200">
          <div><p class="stat-card-label">Caja</p><p class="stat-card-value" style="color:<?php echo $cajaAbierta ? 'var(--green-dark)' : '#c62828'; ?>"><?php echo $cajaAbierta ? 'Abierta' : 'Cerrada'; ?></p><p class="stat-card-sub"><?php echo $cajaAbierta ? 'Saldo: $'.number_format($cajaAbierta['saldo_teorico'],2) : 'Sin caja activa'; ?></p></div>
          <div class="stat-card-icon <?php echo $cajaAbierta ? 'green' : 'red'; ?>"><svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
        </div>
      </div>

      <!-- Tabla ventas -->
      <div class="dark-table-wrap animate-fade-in-up">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
          <p style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Historial de Ventas</p>
          <span class="pill pill-green"><?php echo count($ventas); ?> registros</span>
        </div>
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead><tr>
              <th>#</th><th>Fecha</th><th>Método Pago</th><th>Total</th><th>Estado</th><th>Operador</th>
            </tr></thead>
            <tbody>
              <?php if(count($ventas) > 0): ?>
                <?php foreach($ventas as $v): ?>
                <tr>
                  <td style="font-weight:600;color:var(--text-dark)">#<?php echo $v['id_venta']; ?></td>
                  <td style="font-size:.8rem"><?php echo date('d/m/Y H:i', strtotime($v['fecha'])); ?></td>
                  <td>
                    <span class="pill <?php echo $v['metodo_pago']==='efectivo' ? 'pill-green' : 'pill-blue'; ?>">
                      <?php echo ucfirst($v['metodo_pago']); ?>
                    </span>
                  </td>
                  <td style="font-weight:700;color:var(--green-dark)">$<?php echo number_format($v['total'],2); ?></td>
                  <td><span class="pill <?php echo $v['estado']==='completada' ? 'pill-green' : 'pill-red'; ?>"><span class="pill-dot"></span><?php echo ucfirst($v['estado']); ?></span></td>
                  <td><?php echo htmlspecialchars($v['usuario_nombre'] ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon"><svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div><p class="empty-state-title">Sin ventas</p><p class="empty-state-text">Registra la primera venta del día.</p></div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<!-- Panel Nueva Venta -->
<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>
<div class="slide-panel" id="panel-nueva">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Nueva Venta</p><p class="slide-panel-subtitle">Agrega productos y confirma</p></div>
    <button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/VentaController.php?action=registrar" method="POST" id="ventaForm">

      <div class="form-group">
        <label class="form-label">Método de Pago *</label>
        <select name="metodo_pago" class="form-select" id="metodoPago" onchange="checkCaja()">
          <option value="efectivo">Efectivo</option>
          <option value="transferencia">Transferencia Bancaria</option>
        </select>
        <?php if(!$cajaAbierta): ?>
        <p style="font-size:.7rem;color:#c62828;margin-top:.3rem" id="cajaMsgWarn">Sin caja abierta — solo transferencia disponible</p>
        <?php endif; ?>
      </div>

      <!-- Productos -->
      <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem">
          <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)">Productos</p>
          <button type="button" class="btn btn-green btn-sm" onclick="addProductRow()">+ Agregar</button>
        </div>
        <div id="productRows"></div>
        <div style="border-top:1px solid var(--border);padding-top:.875rem;margin-top:.875rem;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:.85rem;font-weight:600;color:var(--text-mid)">Total:</span>
          <span style="font-size:1.25rem;font-weight:800;color:var(--green-dark)" id="totalVenta">$0.00</span>
        </div>
      </div>

      <div class="form-group" style="margin-top:1rem">
        <label class="form-label">Observaciones</label>
        <textarea name="observaciones" class="form-textarea" placeholder="Notas adicionales..."></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Confirmar Venta</button>
    </form>
  </div>
</div>

<!-- Template fila producto -->
<template id="productRowTpl">
  <div class="product-row" style="margin-bottom:.625rem">
    <div style="display:grid;grid-template-columns:1fr 80px 100px auto;gap:.5rem;align-items:center">
      <select name="productos[]" class="form-select" onchange="updatePrice(this)" required>
        <option value="">Seleccionar producto...</option>
        <?php foreach($productos as $p): ?>
          <option value="<?php echo $p['id_producto']; ?>"
                  data-precio="<?php echo $p['precio_venta'] ?: ($p['precio'] ?? 0); ?>"
                  data-stock="<?php echo $p['stock']; ?>"
                  data-min="<?php echo $p['stock_minimo'] ?? 5; ?>">
            <?php echo htmlspecialchars($p['nombre']); ?> — $<?php echo number_format($p['precio_venta'] ?: ($p['precio'] ?? 0), 2); ?> (<?php echo $p['stock']; ?> uds.)
          </option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="cantidades[]" class="form-input" placeholder="Cant." min="1" value="1" onchange="calcTotal()" required>
      <input type="number" name="precios[]" class="form-input" placeholder="Precio" step="0.01" onchange="calcTotal()" required>
      <button type="button" onclick="removeRow(this)" style="background:none;border:none;cursor:pointer;color:#c62828;padding:.25rem">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <p class="stock-msg" style="font-size:.68rem;margin-top:.2rem;margin-left:.25rem"></p>
  </div>
</template>

<script>
var cajaAbierta = <?php echo $cajaAbierta ? 'true' : 'false'; ?>;

function openPanel(name) {
  document.getElementById('panelOverlay').classList.add('active');
  document.getElementById('panel-' + name).classList.add('active');
  if (document.querySelectorAll('.product-row').length === 0) addProductRow();
}
function closeAll() {
  document.getElementById('panelOverlay').classList.remove('active');
  document.querySelectorAll('.slide-panel').forEach(p => p.classList.remove('active'));
}

function addProductRow() {
  var tpl = document.getElementById('productRowTpl');
  var clone = tpl.content.cloneNode(true);
  document.getElementById('productRows').appendChild(clone);
}
function removeRow(btn) {
  btn.closest('.product-row').remove();
  calcTotal();
}
function updatePrice(sel) {
  var opt = sel.options[sel.selectedIndex];
  var row = sel.closest('.product-row');
  var priceInput = row.querySelector('input[name="precios[]"]');
  var cantInput  = row.querySelector('input[name="cantidades[]"]');
  var stockMsg   = row.querySelector('.stock-msg');

  if (opt.dataset.precio) priceInput.value = opt.dataset.precio;

  var stock = parseInt(opt.dataset.stock) || 0;
  cantInput.max = stock;

  // Indicador de stock
  if (stockMsg) {
    if (stock === 0) {
      stockMsg.textContent = 'Sin stock';
      stockMsg.style.color = '#c62828';
    } else if (stock < (parseInt(opt.dataset.min) || 5)) {
      stockMsg.textContent = 'Stock bajo: ' + stock;
      stockMsg.style.color = 'var(--gold)';
    } else {
      stockMsg.textContent = 'Stock: ' + stock;
      stockMsg.style.color = '#2e7d32';
    }
  }
  calcTotal();
}
function calcTotal() {
  var rows = document.querySelectorAll('.product-row');
  var total = 0;
  rows.forEach(function(row) {
    var cant  = parseFloat(row.querySelector('input[name="cantidades[]"]').value) || 0;
    var price = parseFloat(row.querySelector('input[name="precios[]"]').value) || 0;
    total += cant * price;
  });
  document.getElementById('totalVenta').textContent = '$' + total.toFixed(2);
}
function checkCaja() {
  var metodo = document.getElementById('metodoPago').value;
  var warn   = document.getElementById('cajaMsgWarn');
  if (!cajaAbierta && metodo === 'efectivo') {
    if (warn) warn.style.display = 'block';
    document.getElementById('metodoPago').value = 'transferencia';
    alert('No hay caja abierta. Solo se permite transferencia bancaria.');
  } else {
    if (warn) warn.style.display = 'none';
  }
}
// Bloquear efectivo si no hay caja
if (!cajaAbierta) {
  document.addEventListener('DOMContentLoaded', function() {
    var opt = document.querySelector('#metodoPago option[value="efectivo"]');
    if (opt) opt.disabled = true;
    document.getElementById('metodoPago').value = 'transferencia';
  });
}
</script>
</body>
</html>
