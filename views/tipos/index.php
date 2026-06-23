<?php
/**
 * Tipos de Movimiento - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'administrador') {
    header("Location: ../dashboard/empleado.php"); exit;
}

require_once __DIR__ . '/../../models/TipoMovimiento.php';
$modelo = new TipoMovimiento();
$tipos  = $modelo->getTipos();

$entradas = array_filter($tipos, fn($t) => $t['operacion'] === 'entrada');
$salidas  = array_filter($tipos, fn($t) => $t['operacion'] === 'salida');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tipos de Movimiento | MARTS</title>
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
          <h1 class="page-title">Tipos de Movimiento</h1>
          <p class="page-subtitle">Define las clasificaciones para entradas y salidas</p>
        </div>
        <button class="btn btn-primary" onclick="openPanel('crear')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Clasificación
        </button>
      </div>

      <!-- Alertas -->
      <?php if(isset($_SESSION['error_tipo'])): ?>
      <div class="alert alert-error">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['error_tipo']); unset($_SESSION['error_tipo']); ?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_tipo'])): ?>
      <div class="alert alert-success">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['exito_tipo']); unset($_SESSION['exito_tipo']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Dos columnas: Entradas / Salidas -->
      <div class="two-col-grid">

        <!-- ENTRADAS -->
        <div class="glass-card animate-fade-in-up" style="overflow:hidden">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:rgba(16,185,129,0.05)">
            <div style="display:flex;align-items:center;gap:0.625rem">
              <div style="width:8px;height:8px;border-radius:50%;background:#2e7d32;box-shadow:0 0 8px #2e7d32"></div>
              <p style="font-size:0.8rem;font-weight:700;color:#2e7d32;text-transform:uppercase;letter-spacing:0.08em">Entradas de Stock</p>
            </div>
            <span class="pill pill-green"><?php echo count($entradas); ?></span>
          </div>
          <div style="padding:0.5rem">
            <?php foreach($entradas as $t): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem;border-radius:var(--radius-sm);transition:background var(--t-fast)"
                 onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
              <div>
                <p style="font-size:0.875rem;font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($t['nombre']); ?></p>
                <p style="font-size:0.7rem;color:var(--text-muted);margin-top:0.2rem"><?php echo htmlspecialchars($t['contexto'] ?? ''); ?></p>
              </div>
              <div style="display:flex;gap:0.375rem">
                <button onclick="openEditPanel(<?php echo htmlspecialchars(json_encode($t)); ?>)"
                        class="btn btn-secondary btn-icon" data-tooltip="Editar">
                  <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
                <a href="/controllers/TipoMovimientoController.php?action=eliminar&id=<?php echo $t['id_tipo_movimiento']; ?>"
                   onclick="return confirm('¿Eliminar esta clasificación?')"
                   class="btn btn-danger btn-icon" data-tooltip="Eliminar">
                  <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($entradas)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:0.8rem">Sin tipos de entrada</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- SALIDAS -->
        <div class="glass-card animate-fade-in-up delay-100" style="overflow:hidden">
          <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:rgba(239,68,68,0.05)">
            <div style="display:flex;align-items:center;gap:0.625rem">
              <div style="width:8px;height:8px;border-radius:50%;background:#c62828;box-shadow:0 0 8px #c62828"></div>
              <p style="font-size:0.8rem;font-weight:700;color:#c62828;text-transform:uppercase;letter-spacing:0.08em">Salidas de Stock</p>
            </div>
            <span class="pill pill-red"><?php echo count($salidas); ?></span>
          </div>
          <div style="padding:0.5rem">
            <?php foreach($salidas as $t): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem;border-radius:var(--radius-sm);transition:background var(--t-fast)"
                 onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='transparent'">
              <div>
                <p style="font-size:0.875rem;font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($t['nombre']); ?></p>
                <p style="font-size:0.7rem;color:var(--text-muted);margin-top:0.2rem"><?php echo htmlspecialchars($t['contexto'] ?? ''); ?></p>
              </div>
              <div style="display:flex;gap:0.375rem">
                <button onclick="openEditPanel(<?php echo htmlspecialchars(json_encode($t)); ?>)"
                        class="btn btn-secondary btn-icon" data-tooltip="Editar">
                  <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                  </svg>
                </button>
                <a href="/controllers/TipoMovimientoController.php?action=eliminar&id=<?php echo $t['id_tipo_movimiento']; ?>"
                   onclick="return confirm('¿Eliminar esta clasificación?')"
                   class="btn btn-danger btn-icon" data-tooltip="Eliminar">
                  <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </a>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($salidas)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text-muted);font-size:0.8rem">Sin tipos de salida</div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

<!-- Overlay -->
<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>

<!-- Panel Crear -->
<div class="slide-panel" id="panel-crear">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Nueva Clasificación</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/TipoMovimientoController.php?action=crear" method="POST">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" required class="form-input" placeholder="Ej. Donación, Merma...">
      </div>
      <div class="form-group">
        <label class="form-label">Operación *</label>
        <select name="operacion" required class="form-select">
          <option value="entrada">↑ Entrada (Aumenta stock)</option>
          <option value="salida">↓ Salida (Disminuye stock)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción / Contexto</label>
        <textarea name="contexto" class="form-textarea" placeholder="¿Cuándo se usa esta clasificación?"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Crear Clasificación</button>
    </form>
  </div>
</div>

<!-- Panel Editar -->
<div class="slide-panel" id="panel-editar">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Editar Clasificación</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/TipoMovimientoController.php?action=editar" method="POST">
      <input type="hidden" name="id_tipo_movimiento" id="edit-id">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" id="edit-nombre" required class="form-input">
      </div>
      <div class="form-group">
        <label class="form-label">Operación *</label>
        <select name="operacion" id="edit-operacion" required class="form-select">
          <option value="entrada">↑ Entrada</option>
          <option value="salida">↓ Salida</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción / Contexto</label>
        <textarea name="contexto" id="edit-contexto" class="form-textarea"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Guardar Cambios</button>
    </form>
  </div>
</div>

<script>
function openPanel(name) {
  document.getElementById('panelOverlay').classList.add('active');
  document.getElementById('panel-' + name).classList.add('active');
}
function closeAll() {
  document.getElementById('panelOverlay').classList.remove('active');
  ['crear','editar'].forEach(n => document.getElementById('panel-'+n).classList.remove('active'));
}
function openEditPanel(t) {
  document.getElementById('edit-id').value        = t.id_tipo_movimiento;
  document.getElementById('edit-nombre').value    = t.nombre;
  document.getElementById('edit-operacion').value = t.operacion;
  document.getElementById('edit-contexto').value  = t.contexto || '';
  openPanel('editar');
}
</script>
</body>
</html>
