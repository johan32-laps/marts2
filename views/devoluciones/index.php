<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) { header("Location: ../dashboard/empleado.php"); exit; }
require_once __DIR__ . '/../../models/Devolucion.php';
require_once __DIR__ . '/../../models/Venta.php';
$devModel   = new Devolucion();
$ventaModel = new Venta();
$devoluciones = $devModel->listar(50);
$ventas       = $ventaModel->listar(100);
$base         = '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Devoluciones | MARTS</title>
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
        <div><h1 class="page-title">Devoluciones</h1><p class="page-subtitle">Gestiona devoluciones de clientes sobre ventas realizadas</p></div>
        <button class="btn btn-primary" onclick="openPanel('nueva')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
          Nueva Devolución
        </button>
      </div>

      <?php if(isset($_SESSION['error_dev'])): ?>
      <div class="alert alert-error"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span><span><?php echo htmlspecialchars($_SESSION['error_dev']); unset($_SESSION['error_dev']); ?></span></div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_dev'])): ?>
      <div class="alert alert-success"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><?php echo htmlspecialchars($_SESSION['exito_dev']); unset($_SESSION['exito_dev']); ?></span></div>
      <?php endif; ?>

      <div class="dark-table-wrap animate-fade-in-up">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
          <p style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Historial de Devoluciones</p>
          <span class="pill pill-amber"><?php echo count($devoluciones); ?> registros</span>
        </div>
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead><tr><th>#</th><th>Fecha</th><th>Venta</th><th>Motivo</th><th>Total Devuelto</th><th>Operador</th></tr></thead>
            <tbody>
              <?php if(count($devoluciones) > 0): ?>
                <?php foreach($devoluciones as $d): ?>
                <tr>
                  <td style="font-weight:600">#<?php echo $d['id_devolucion']; ?></td>
                  <td style="font-size:.8rem"><?php echo date('d/m/Y H:i', strtotime($d['fecha'])); ?></td>
                  <td><span class="pill pill-blue">Venta #<?php echo $d['id_venta']; ?></span></td>
                  <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($d['motivo']); ?></td>
                  <td style="font-weight:700;color:#c62828">$<?php echo number_format($d['total_devolucion'],2); ?></td>
                  <td><?php echo htmlspecialchars($d['usuario_nombre'] ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon"><svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></div><p class="empty-state-title">Sin devoluciones</p><p class="empty-state-text">No hay devoluciones registradas.</p></div></td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>
<div class="slide-panel" id="panel-nueva">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Nueva Devolución</p><p class="slide-panel-subtitle">Selecciona la venta y los productos a devolver</p></div>
    <button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/DevolucionController.php?action=registrar" method="POST" id="devForm">

      <div class="form-group">
        <label class="form-label">Venta a Devolver *</label>
        <select name="id_venta" required class="form-select" id="ventaSelect" onchange="loadVentaDetalle(this.value)">
          <option value="">Seleccionar venta...</option>
          <?php foreach($ventas as $v): ?>
            <option value="<?php echo $v['id_venta']; ?>"
                    data-total="<?php echo $v['total']; ?>">
              Venta #<?php echo $v['id_venta']; ?> — $<?php echo number_format($v['total'],2); ?> — <?php echo date('d/m/Y', strtotime($v['fecha'])); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Motivo de la Devolución *</label>
        <textarea name="motivo" required class="form-textarea" placeholder="Describe el motivo de la devolución..."></textarea>
      </div>

      <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.875rem">
          <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted)">Productos a Devolver</p>
          <button type="button" class="btn btn-secondary btn-sm" onclick="addDevRow()">+ Agregar</button>
        </div>
        <div id="devRows"></div>
        <div style="border-top:1px solid var(--border);padding-top:.875rem;margin-top:.875rem;display:flex;justify-content:space-between">
          <span style="font-size:.85rem;font-weight:600;color:var(--text-mid)">Total a Devolver:</span>
          <span style="font-size:1.25rem;font-weight:800;color:#c62828" id="totalDev">$0.00</span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem;margin-top:1rem">Registrar Devolución</button>
    </form>
  </div>
</div>

<!-- Datos de ventas para JS -->
<script>
var ventasData = <?php
$ventasJS = [];
foreach ($ventas as $v) {
    $detalle = $ventaModel->getDetalle($v['id_venta']);
    $ventasJS[$v['id_venta']] = $detalle;
}
echo json_encode($ventasJS);
?>;

function openPanel(name) {
  document.getElementById('panelOverlay').classList.add('active');
  document.getElementById('panel-'+name).classList.add('active');
}
function closeAll() {
  document.getElementById('panelOverlay').classList.remove('active');
  document.querySelectorAll('.slide-panel').forEach(p=>p.classList.remove('active'));
}

function loadVentaDetalle(id_venta) {
  var rows = document.getElementById('devRows');
  rows.innerHTML = '';
  if (!id_venta || !ventasData[id_venta]) return;
  ventasData[id_venta].forEach(function(item) {
    addDevRow(item.id_producto, item.producto_nombre, item.cantidad, item.precio_venta);
  });
}

function addDevRow(id_prod, nombre, maxCant, precio) {
  var div = document.createElement('div');
  div.className = 'product-row';
  div.style.cssText = 'display:grid;grid-template-columns:1fr 80px 100px auto;gap:.5rem;margin-bottom:.5rem;align-items:center';
  div.innerHTML =
    '<input type="text" class="form-input" value="'+(nombre||'')+'" readonly style="font-size:.8rem;background:var(--beige-dark)">' +
    '<input type="hidden" name="productos[]" value="'+(id_prod||'')+'">' +
    '<input type="number" name="cantidades[]" class="form-input" placeholder="Cant." min="1" max="'+(maxCant||99)+'" value="1" onchange="calcDevTotal()" required>' +
    '<input type="number" name="precios[]" class="form-input" placeholder="Precio" step="0.01" value="'+(precio||0)+'" onchange="calcDevTotal()" required>' +
    '<button type="button" onclick="this.closest(\'.product-row\').remove();calcDevTotal()" style="background:none;border:none;cursor:pointer;color:#c62828;padding:.25rem"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>';
  document.getElementById('devRows').appendChild(div);
  calcDevTotal();
}

function calcDevTotal() {
  var rows = document.querySelectorAll('#devRows .product-row');
  var t = 0;
  rows.forEach(function(r) {
    var c = parseFloat(r.querySelector('input[name="cantidades[]"]').value)||0;
    var p = parseFloat(r.querySelector('input[name="precios[]"]').value)||0;
    t += c*p;
  });
  document.getElementById('totalDev').textContent = '$'+t.toFixed(2);
}
</script>
</body>
</html>
