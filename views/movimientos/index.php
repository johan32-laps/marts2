<?php
/**
 * Historial de Movimientos - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }

require_once __DIR__ . '/../../models/Movimiento.php';
require_once __DIR__ . '/../../models/Producto.php';

$movimientoModel = new Movimiento();
$productoModel   = new Producto();

$movimientos     = $movimientoModel->listarMovimientos(100);
$productos       = $productoModel->listarProductos();
$tiposMovimiento = $movimientoModel->obtenerTipos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Movimientos | MARTS</title>
  <link rel="icon" type="image/png" href="../../public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow-1"></div>

<div class="app-layout">
  <?php include '../layouts/sidebar.php'; ?>

  <div class="main-content" id="mainContent">
    <?php include '../layouts/header.php'; ?>

    <div class="page-content animate-fade-in">

      <div class="page-header">
        <div>
          <h1 class="page-title">Historial de Movimientos</h1>
          <p class="page-subtitle">Registro detallado de todas las transacciones</p>
        </div>
        <button class="btn btn-primary" onclick="openPanel()">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Transacción
        </button>
      </div>

      <!-- Alertas -->
      <?php if(isset($_SESSION['error_movimiento'])): ?>
      <div class="alert alert-error">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['error_movimiento']); unset($_SESSION['error_movimiento']); ?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_movimiento'])): ?>
      <div class="alert alert-success">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['exito_movimiento']); unset($_SESSION['exito_movimiento']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Filtro rápido -->
      <div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;flex-wrap:wrap">
        <button class="btn btn-secondary btn-sm filter-btn active" data-filter="all" onclick="filterTable('all',this)">Todos</button>
        <button class="btn btn-secondary btn-sm filter-btn" data-filter="entrada" onclick="filterTable('entrada',this)"
                style="color:#2e7d32;border-color:rgba(16,185,129,0.3)">↑ Entradas</button>
        <button class="btn btn-secondary btn-sm filter-btn" data-filter="salida" onclick="filterTable('salida',this)"
                style="color:#c62828;border-color:rgba(239,68,68,0.3)">↓ Salidas</button>
      </div>

      <!-- Tabla -->
      <div class="dark-table-wrap animate-fade-in-up">
        <div style="overflow-x:auto">
          <table class="dark-table" id="movTable">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th class="hide-mobile">Motivo</th>
                <th class="hide-mobile">Operador</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($movimientos) > 0): ?>
                <?php foreach($movimientos as $mov): ?>
                <tr data-tipo="<?php echo $mov['operacion'] ?? $mov['tipo']; ?>">
                  <td style="white-space:nowrap;font-size:0.8rem"><?php echo date('d/m/Y H:i', strtotime($mov['fecha'])); ?></td>
                  <td style="color:var(--text-dark);font-weight:500"><?php echo htmlspecialchars($mov['producto_nombre']); ?></td>
                  <td>
                    <span class="pill <?php echo ($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? 'pill-green' : 'pill-red'; ?>">
                      <span class="pill-dot"></span>
                      <?php echo htmlspecialchars($mov['tipo_nombre'] ?? ucfirst($mov['tipo'])); ?>
                    </span>
                  </td>
                  <td style="font-weight:700;color:<?php echo ($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? '#2e7d32' : '#c62828'; ?>">
                    <?php echo (($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? '+' : '-') . $mov['cantidad']; ?>
                  </td>
                  <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" class="hide-mobile">
                    <?php echo htmlspecialchars($mov['motivo'] ?: '—'); ?>
                  </td>
                  <td class="hide-mobile"><?php echo htmlspecialchars($mov['usuario_nombre'] ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6">
                    <div class="empty-state">
                      <div class="empty-state-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                      </div>
                      <p class="empty-state-title">Sin movimientos</p>
                      <p class="empty-state-text">Registra el primer movimiento de inventario.</p>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<!-- Panel Nuevo Movimiento -->
<div class="panel-overlay" id="panelOverlay" onclick="closePanel()"></div>
<div class="slide-panel" id="movPanel">
  <div class="slide-panel-header">
    <div>
      <p class="slide-panel-title">Registrar Movimiento</p>
      <p class="slide-panel-subtitle">Entrada o salida de inventario</p>
    </div>
    <button class="slide-panel-close" onclick="closePanel()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/MovimientoController.php?action=registrar" method="POST">
      <div class="form-group">
        <label class="form-label">Producto *</label>
        <select name="id_producto" required class="form-select">
          <option value="">Seleccionar...</option>
          <?php foreach($productos as $p): ?>
            <option value="<?php echo $p['id_producto']; ?>">
              <?php echo htmlspecialchars($p['nombre']); ?> (Stock: <?php echo $p['stock']; ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Tipo *</label>
          <select name="id_tipo_movimiento" required class="form-select">
            <?php foreach($tiposMovimiento as $tm): ?>
              <option value="<?php echo $tm['id_tipo_movimiento']; ?>">
                <?php echo ($tm['operacion'] === 'entrada' ? '↑ ' : '↓ ') . htmlspecialchars($tm['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Cantidad *</label>
          <input type="number" name="cantidad" required min="1" class="form-input" placeholder="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Motivo</label>
        <textarea name="motivo" class="form-textarea" placeholder="Descripción del movimiento..."></textarea>
      </div>
      <div style="border-top:1px solid var(--border);padding-top:1rem;margin-top:0.5rem">
        <p style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--text-muted);margin-bottom:0.875rem">Logística (Opcional)</p>
        <div class="form-group">
          <input type="text" name="ubicacion" class="form-input" placeholder="Ubicación en almacén">
        </div>
        <div class="form-group">
          <input type="text" name="referencia" class="form-input" placeholder="Referencia / N° Guía">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">
        Guardar Movimiento
      </button>
    </form>
  </div>
</div>

<script>
function openPanel() {
  document.getElementById('panelOverlay').classList.add('active');
  document.getElementById('movPanel').classList.add('active');
}
function closePanel() {
  document.getElementById('panelOverlay').classList.remove('active');
  document.getElementById('movPanel').classList.remove('active');
}

function filterTable(tipo, btn) {
  // Actualizar botones activos
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  // Filtrar filas
  document.querySelectorAll('#movTable tbody tr[data-tipo]').forEach(row => {
    if (tipo === 'all' || row.dataset.tipo === tipo) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>
</body>
</html>
