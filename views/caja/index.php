<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
require_once __DIR__ . '/../../models/Caja.php';
$cajaModel   = new Caja();
$cajaAbierta = $cajaModel->getCajaAbierta();
$historial   = $cajaModel->historial(10);
$movimientos = $cajaAbierta ? $cajaModel->movimientos($cajaAbierta['id_caja']) : [];
$base        = '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Caja | MARTS</title>
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
          <h1 class="page-title">Gestión de Caja</h1>
          <p class="page-subtitle">Control del flujo de efectivo de la jornada</p>
        </div>
        <?php if(!$cajaAbierta): ?>
        <button class="btn btn-primary" onclick="openPanel('abrir')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Abrir Caja
        </button>
        <?php else: ?>
        <div style="display:flex;gap:.75rem">
          <button class="btn btn-secondary" onclick="openPanel('movimiento')">Movimiento Manual</button>
          <button class="btn btn-danger" onclick="openPanel('cerrar')">Cerrar Caja</button>
        </div>
        <?php endif; ?>
      </div>

      <?php if(isset($_SESSION['error_caja'])): ?>
      <div class="alert alert-error"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span><span><?php echo htmlspecialchars($_SESSION['error_caja']); unset($_SESSION['error_caja']); ?></span></div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_caja'])): ?>
      <div class="alert alert-success"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><?php echo htmlspecialchars($_SESSION['exito_caja']); unset($_SESSION['exito_caja']); ?></span></div>
      <?php endif; ?>

      <?php if($cajaAbierta): ?>
      <!-- Caja activa -->
      <div class="glass-card" style="padding:1.5rem;margin-bottom:1.75rem;border-left:4px solid var(--green-dark)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
          <div>
            <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.375rem">Caja Activa</p>
            <p style="font-size:1.5rem;font-weight:800;color:var(--green-dark)">$<?php echo number_format($cajaAbierta['saldo_teorico'],2); ?></p>
            <p style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem">
              Saldo inicial: $<?php echo number_format($cajaAbierta['saldo_inicial'],2); ?> &bull;
              Abierta: <?php echo date('d/m/Y H:i', strtotime($cajaAbierta['fecha_apertura'])); ?> &bull;
              Operador: <?php echo htmlspecialchars($cajaAbierta['usuario_nombre']); ?>
            </p>
          </div>
          <span class="pill pill-green" style="font-size:.8rem;padding:.4rem 1rem"><span class="pill-dot"></span>Abierta</span>
        </div>
      </div>

      <!-- Movimientos de la caja activa -->
      <div class="dark-table-wrap animate-fade-in-up" style="margin-bottom:1.75rem">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
          <p style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Movimientos de la Jornada</p>
        </div>
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Concepto</th><th style="text-align:right">Monto</th></tr></thead>
            <tbody>
              <?php if(count($movimientos) > 0): ?>
                <?php foreach($movimientos as $m): ?>
                <tr>
                  <td style="font-size:.8rem"><?php echo date('d/m H:i', strtotime($m['fecha'])); ?></td>
                  <td><span class="pill <?php echo $m['tipo']==='ingreso' ? 'pill-green' : 'pill-red'; ?>"><span class="pill-dot"></span><?php echo ucfirst($m['tipo']); ?></span></td>
                  <td><?php echo htmlspecialchars($m['concepto']); ?></td>
                  <td style="text-align:right;font-weight:700;color:<?php echo $m['tipo']==='ingreso' ? 'var(--green-dark)' : '#c62828'; ?>">
                    <?php echo ($m['tipo']==='ingreso' ? '+' : '-').'$'.number_format($m['monto'],2); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted)">Sin movimientos en esta jornada.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Historial de cajas -->
      <div class="dark-table-wrap animate-fade-in-up">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)">
          <p style="font-size:.9rem;font-weight:700;color:var(--text-dark)">Historial de Cajas</p>
        </div>
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead><tr><th>#</th><th>Apertura</th><th>Cierre</th><th>Saldo Inicial</th><th>Saldo Final</th><th>Diferencia</th><th>Estado</th></tr></thead>
            <tbody>
              <?php foreach($historial as $c): ?>
              <tr>
                <td style="font-weight:600">#<?php echo $c['id_caja']; ?></td>
                <td style="font-size:.8rem"><?php echo date('d/m/Y H:i', strtotime($c['fecha_apertura'])); ?></td>
                <td style="font-size:.8rem"><?php echo $c['fecha_cierre'] ? date('d/m/Y H:i', strtotime($c['fecha_cierre'])) : '—'; ?></td>
                <td>$<?php echo number_format($c['saldo_inicial'],2); ?></td>
                <td><?php echo $c['saldo_final'] !== null ? '$'.number_format($c['saldo_final'],2) : '—'; ?></td>
                <td style="color:<?php echo ($c['diferencia'] ?? 0) == 0 ? 'var(--green-dark)' : '#c62828'; ?>">
                  <?php echo $c['diferencia'] !== null ? '$'.number_format($c['diferencia'],2) : '—'; ?>
                </td>
                <td><span class="pill <?php echo $c['estado']==='abierta' ? 'pill-green' : 'pill-gray'; ?>"><span class="pill-dot"></span><?php echo ucfirst($c['estado']); ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>

<!-- Panel Abrir Caja -->
<div class="slide-panel" id="panel-abrir">
  <div class="slide-panel-header"><div><p class="slide-panel-title">Abrir Caja</p><p class="slide-panel-subtitle">Inicia la jornada con el saldo inicial</p></div><button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
  <div class="slide-panel-body">
    <form action="../../controllers/CajaController.php?action=abrir" method="POST">
      <div class="form-group"><label class="form-label">Saldo Inicial *</label><div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" name="saldo_inicial" step="0.01" min="0" value="0" required class="form-input" style="padding-left:1.75rem"></div></div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Abrir Caja</button>
    </form>
  </div>
</div>

<!-- Panel Cerrar Caja -->
<?php if($cajaAbierta): ?>
<div class="slide-panel" id="panel-cerrar">
  <div class="slide-panel-header"><div><p class="slide-panel-title">Cerrar Caja</p><p class="slide-panel-subtitle">Saldo teórico: $<?php echo number_format($cajaAbierta['saldo_teorico'],2); ?></p></div><button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
  <div class="slide-panel-body">
    <form action="../../controllers/CajaController.php?action=cerrar" method="POST">
      <input type="hidden" name="id_caja" value="<?php echo $cajaAbierta['id_caja']; ?>">
      <div class="form-group"><label class="form-label">Saldo Final Contado *</label><div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" name="saldo_final" step="0.01" min="0" required class="form-input" style="padding-left:1.75rem" oninput="calcDiff(this)"></div></div>
      <div id="diffMsg" style="font-size:.8rem;margin-bottom:1rem;display:none"></div>
      <div class="form-group"><label class="form-label">Justificación (si hay diferencia)</label><textarea name="justificacion" class="form-textarea" placeholder="Explica la diferencia si existe..."></textarea></div>
      <button type="submit" class="btn btn-danger" style="width:100%;padding:.875rem">Cerrar Caja</button>
    </form>
  </div>
</div>

<!-- Panel Movimiento Manual -->
<div class="slide-panel" id="panel-movimiento">
  <div class="slide-panel-header"><div><p class="slide-panel-title">Movimiento Manual</p></div><button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
  <div class="slide-panel-body">
    <form action="../../controllers/CajaController.php?action=movimiento" method="POST">
      <input type="hidden" name="id_caja" value="<?php echo $cajaAbierta['id_caja']; ?>">
      <div class="form-group"><label class="form-label">Tipo *</label><select name="tipo" required class="form-select"><option value="ingreso">Ingreso</option><option value="egreso">Egreso</option></select></div>
      <div class="form-group"><label class="form-label">Monto *</label><div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" name="monto" step="0.01" min="0.01" required class="form-input" style="padding-left:1.75rem"></div></div>
      <div class="form-group"><label class="form-label">Concepto *</label><input type="text" name="concepto" required class="form-input" placeholder="Descripción del movimiento..."></div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Registrar Movimiento</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function openPanel(name) {
  document.getElementById('panelOverlay').classList.add('active');
  var p = document.getElementById('panel-' + name);
  if (p) p.classList.add('active');
}
function closeAll() {
  document.getElementById('panelOverlay').classList.remove('active');
  document.querySelectorAll('.slide-panel').forEach(p => p.classList.remove('active'));
}
function calcDiff(input) {
  var teorico = <?php echo $cajaAbierta ? $cajaAbierta['saldo_teorico'] : 0; ?>;
  var final   = parseFloat(input.value) || 0;
  var diff    = final - teorico;
  var msg     = document.getElementById('diffMsg');
  msg.style.display = 'block';
  msg.style.color   = Math.abs(diff) < 0.01 ? 'var(--green-dark)' : '#c62828';
  msg.textContent   = Math.abs(diff) < 0.01
    ? 'Sin diferencia. Cuadre perfecto.'
    : 'Diferencia: $' + diff.toFixed(2) + '. Se requiere justificación.';
}
</script>
</body>
</html>
