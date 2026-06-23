<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if (!in_array($_SESSION['rol'] ?? '', ['empleado','admin','administrador'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../models/Producto.php';
require_once __DIR__ . '/../../models/Movimiento.php';
require_once __DIR__ . '/../../models/Venta.php';
require_once __DIR__ . '/../../models/Caja.php';

$productoModel   = new Producto();
$movimientoModel = new Movimiento();
$ventaModel      = new Venta();
$cajaModel       = new Caja();

$productos       = $productoModel->listarProductos();
$totalProductos  = count($productos);
$totalStock      = array_reduce($productos, fn($c,$p) => $c + $p['stock'], 0);
$stockCritico    = array_filter($productos, fn($p) => $p['stock'] < ($p['stock_minimo'] ?? 5));
$tiposMovimiento = $movimientoModel->obtenerTipos();
$movRecientes    = $movimientoModel->listarMovimientos(6);
$resumenSemanal  = $movimientoModel->obtenerResumenSemanal();
$totalVentasHoy  = $ventaModel->totalHoy();
$contarVentasHoy = $ventaModel->contarHoy();
$cajaAbierta     = $cajaModel->getCajaAbierta();

// Datos gráfica
$labels = []; $dataEntradas = []; $dataSalidas = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($fecha));
    $e = 0; $s = 0;
    foreach ($resumenSemanal as $r) {
        if ($r['dia'] === $fecha) { $e = (int)$r['entradas']; $s = (int)$r['salidas']; break; }
    }
    $dataEntradas[] = $e; $dataSalidas[] = $s;
}

$base = '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Operativo | MARTS</title>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="app-layout">
  <?php include '../layouts/sidebar.php'; ?>

  <div class="main-content" id="mainContent">
    <?php include '../layouts/header.php'; ?>

    <div class="page-content animate-fade-in">

      <!-- Page Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Panel Operativo</h1>
          <p class="page-subtitle">Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?> &mdash; Gestión de inventario</p>
        </div>
        <div style="display:flex;gap:.75rem">
          <a href="../ventas/index.php" class="btn btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Nueva Venta
          </a>
          <a href="../caja/index.php" class="btn btn-secondary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Caja
          </a>
        </div>
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

      <!-- Stats: 6 cards en un solo grid -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem">

        <!-- Ventas hoy -->
        <div class="stat-card animate-fade-in-up">
          <div>
            <p class="stat-card-label">Ventas Hoy</p>
            <p class="stat-card-value" style="color:var(--green-dark)">$<?php echo number_format($totalVentasHoy,0); ?></p>
            <p class="stat-card-sub"><?php echo $contarVentasHoy; ?> transacciones</p>
          </div>
          <div class="stat-card-icon green">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
          </div>
        </div>

        <!-- Caja -->
        <div class="stat-card animate-fade-in-up delay-100" style="border-color:<?php echo $cajaAbierta ? 'rgba(45,90,61,0.3)' : 'rgba(198,40,40,0.2)'; ?>">
          <div>
            <p class="stat-card-label">Caja</p>
            <p class="stat-card-value" style="color:<?php echo $cajaAbierta ? 'var(--green-dark)' : '#c62828'; ?>"><?php echo $cajaAbierta ? 'Abierta' : 'Cerrada'; ?></p>
            <p class="stat-card-sub"><?php echo $cajaAbierta ? '$'.number_format($cajaAbierta['saldo_teorico'],2) : 'Sin caja activa'; ?></p>
          </div>
          <div class="stat-card-icon <?php echo $cajaAbierta ? 'green' : 'red'; ?>">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
        </div>

        <!-- Catálogo -->
        <div class="stat-card animate-fade-in-up delay-200">
          <div>
            <p class="stat-card-label">Catálogo</p>
            <p class="stat-card-value"><?php echo $totalProductos; ?></p>
            <p class="stat-card-sub">productos activos</p>
          </div>
          <div class="stat-card-icon blue">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
        </div>

        <!-- Stock global -->
        <div class="stat-card animate-fade-in-up delay-100">
          <div>
            <p class="stat-card-label">Stock Global</p>
            <p class="stat-card-value"><?php echo number_format($totalStock); ?></p>
            <p class="stat-card-sub">unidades totales</p>
          </div>
          <div class="stat-card-icon green">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
        </div>

        <!-- Stock crítico -->
        <div class="stat-card animate-fade-in-up delay-200" style="border-color:<?php echo count($stockCritico) > 0 ? 'rgba(245,158,11,0.35)' : 'var(--border)'; ?>">
          <div>
            <p class="stat-card-label">Stock Crítico</p>
            <p class="stat-card-value" style="color:<?php echo count($stockCritico) > 0 ? 'var(--gold)' : 'var(--text-dark)'; ?>"><?php echo count($stockCritico); ?></p>
            <p class="stat-card-sub">productos &lt; 5 uds.</p>
          </div>
          <div class="stat-card-icon amber">
            <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
        </div>

        <!-- Acceso rápido -->
        <div class="stat-card animate-fade-in-up delay-300">
          <div style="width:100%">
            <p class="stat-card-label">Acceso Rápido</p>
            <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.625rem">
              <a href="../ventas/index.php" class="btn btn-primary btn-sm" style="justify-content:center">Registrar Venta</a>
              <a href="../caja/index.php" class="btn btn-secondary btn-sm" style="justify-content:center">Gestionar Caja</a>
            </div>
          </div>
        </div>

      </div>

      <!-- Gráfica + Movimientos recientes -->
      <div class="two-col-grid">

        <div class="chart-container animate-fade-in-up">
          <div class="chart-header">
            <div>
              <p class="chart-title">Tendencia Semanal</p>
              <p class="chart-subtitle">Entradas y salidas — últimos 7 días</p>
            </div>
            <div style="display:flex;gap:1rem;font-size:.72rem">
              <span style="display:flex;align-items:center;gap:.35rem;color:#2e7d32"><span style="width:8px;height:8px;border-radius:50%;background:#2e7d32;display:inline-block"></span>Entradas</span>
              <span style="display:flex;align-items:center;gap:.35rem;color:#c62828"><span style="width:8px;height:8px;border-radius:50%;background:#c62828;display:inline-block"></span>Salidas</span>
            </div>
          </div>
          <div style="height:240px;position:relative">
            <canvas id="movChart"></canvas>
          </div>
        </div>

        <div class="glass-card" style="padding:1.5rem;display:flex;flex-direction:column">
          <p style="font-size:.9rem;font-weight:700;color:var(--text-dark);margin-bottom:1rem">Últimos Movimientos</p>
          <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:.625rem">
            <?php if(count($movRecientes) > 0): ?>
              <?php foreach($movRecientes as $mov): ?>
              <div class="mov-card">
                <div class="mov-card-icon <?php echo ($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? 'entrada' : 'salida'; ?>">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if(($mov['operacion'] ?? $mov['tipo']) === 'entrada'): ?>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    <?php else: ?>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    <?php endif; ?>
                  </svg>
                </div>
                <div class="mov-card-info">
                  <p class="mov-card-name"><?php echo htmlspecialchars($mov['producto_nombre']); ?></p>
                  <p class="mov-card-meta"><?php echo date('d/m H:i', strtotime($mov['fecha'])); ?></p>
                </div>
                <span class="mov-card-qty <?php echo ($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? 'entrada' : 'salida'; ?>">
                  <?php echo (($mov['operacion'] ?? $mov['tipo']) === 'entrada' ? '+' : '-') . $mov['cantidad']; ?>
                </span>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem">Sin movimientos recientes</div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<!-- Panel Movimiento de inventario -->
<div class="panel-overlay" id="movOverlay" onclick="closeMovPanel()"></div>
<div class="slide-panel" id="movPanel">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Registrar Movimiento</p><p class="slide-panel-subtitle">Entrada o salida de inventario</p></div>
    <button class="slide-panel-close" onclick="closeMovPanel()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/MovimientoController.php?action=registrar" method="POST">
      <input type="hidden" name="redirect" value="dashboard">
      <div class="form-group">
        <label class="form-label">Producto *</label>
        <select name="id_producto" required class="form-select">
          <option value="">Seleccionar...</option>
          <?php foreach($productos as $p): ?>
            <option value="<?php echo $p['id_producto']; ?>"><?php echo htmlspecialchars($p['nombre']); ?> (<?php echo $p['stock']; ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Tipo *</label>
          <select name="id_tipo_movimiento" required class="form-select">
            <?php foreach($tiposMovimiento as $tm): ?>
              <option value="<?php echo $tm['id_tipo_movimiento']; ?>"><?php echo ($tm['operacion']==='entrada'?'↑ ':'↓ ').htmlspecialchars($tm['nombre']); ?></option>
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
        <textarea name="motivo" class="form-textarea" placeholder="Descripción..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Registrar Movimiento</button>
    </form>
  </div>
</div>

<script>
function openMovPanel() { document.getElementById('movOverlay').classList.add('active'); document.getElementById('movPanel').classList.add('active'); }
function closeMovPanel() { document.getElementById('movOverlay').classList.remove('active'); document.getElementById('movPanel').classList.remove('active'); }

document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('movChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($labels); ?>,
      datasets: [
        { label:'Entradas', data:<?php echo json_encode($dataEntradas); ?>, backgroundColor:'rgba(46,125,50,0.7)', borderColor:'#2e7d32', borderWidth:1, borderRadius:4 },
        { label:'Salidas',  data:<?php echo json_encode($dataSalidas); ?>,  backgroundColor:'rgba(198,40,40,0.7)', borderColor:'#c62828', borderWidth:1, borderRadius:4 }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins: {
        legend: { display:false },
        tooltip: { backgroundColor:'#fff', titleColor:'#1a2e1f', bodyColor:'#4a5e50', borderColor:'#d8e4db', borderWidth:1, padding:10, cornerRadius:8 }
      },
      scales: {
        x: { grid:{ color:'rgba(0,0,0,0.04)' }, ticks:{ font:{size:10}, color:'#8a9e90' } },
        y: { beginAtZero:true, grid:{ color:'rgba(0,0,0,0.04)' }, ticks:{ font:{size:10}, color:'#8a9e90', stepSize:1 } }
      }
    }
  });
});
</script>
</body>
</html>
