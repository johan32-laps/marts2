<?php
/**
 * Gestión de Categorías - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'administrador') {
    header("Location: ../dashboard/empleado.php"); exit;
}

require_once __DIR__ . '/../../models/Categoria.php';
$catModel   = new Categoria();
$categorias = $catModel->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Categorías | MARTS</title>
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
          <h1 class="page-title">Categorías</h1>
          <p class="page-subtitle">Organiza tu inventario por categorías</p>
        </div>
        <button class="btn btn-primary" onclick="openPanel('crear')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nueva Categoría
        </button>
      </div>

      <!-- Alertas -->
      <?php if(isset($_SESSION['error_cat'])): ?>
      <div class="alert alert-error">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['error_cat']); unset($_SESSION['error_cat']); ?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_cat'])): ?>
      <div class="alert alert-success">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['exito_cat']); unset($_SESSION['exito_cat']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stats-grid-3">
        <div class="stat-card animate-fade-in-up">
          <div>
            <p class="stat-card-label">Total</p>
            <p class="stat-card-value"><?php echo count($categorias); ?></p>
          </div>
          <div class="stat-card-icon purple">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 7h.01M7 3h5l5.707 5.707a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414L7 3z"/>
            </svg>
          </div>
        </div>
        <div class="stat-card animate-fade-in-up delay-100">
          <div>
            <p class="stat-card-label">Con Productos</p>
            <p class="stat-card-value" style="color:#2e7d32">
              <?php echo count(array_filter($categorias, fn($c) => $c['total_productos'] > 0)); ?>
            </p>
          </div>
          <div class="stat-card-icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
        </div>
        <div class="stat-card animate-fade-in-up delay-200">
          <div>
            <p class="stat-card-label">Total Productos</p>
            <p class="stat-card-value"><?php echo array_sum(array_column($categorias,'total_productos')); ?></p>
          </div>
          <div class="stat-card-icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Tabla -->
      <div class="dark-table-wrap animate-fade-in-up">
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Descripción</th>
                <th style="text-align:center">Productos</th>
                <th style="text-align:right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $pillColors = ['pill-purple','pill-blue','pill-green','pill-amber','pill-red','pill-cyan'];
              $idx = 0;
              foreach($categorias as $cat):
                $color = $pillColors[$idx % count($pillColors)];
                $idx++;
              ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:0.75rem">
                    <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:rgba(139,92,246,0.15);border:1px solid rgba(139,92,246,0.25);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.875rem;color:#7b1fa2;flex-shrink:0">
                      <?php echo strtoupper(mb_substr($cat['nombre'],0,1)); ?>
                    </div>
                    <p style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($cat['nombre']); ?></p>
                  </div>
                </td>
                <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?php echo htmlspecialchars($cat['descripcion'] ?? '—'); ?>
                </td>
                <td style="text-align:center">
                  <?php if($cat['total_productos'] > 0): ?>
                    <span class="pill pill-blue"><?php echo $cat['total_productos']; ?> producto<?php echo $cat['total_productos'] != 1 ? 's' : ''; ?></span>
                  <?php else: ?>
                    <span style="font-size:0.75rem;color:var(--text-muted)">Sin productos</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:right">
                  <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.375rem">
                    <button onclick="openEditPanel(<?php echo htmlspecialchars(json_encode($cat)); ?>)"
                            class="btn btn-secondary btn-icon" data-tooltip="Editar">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                      </svg>
                    </button>
                    <?php if($cat['total_productos'] == 0): ?>
                    <button onclick="confirmDelete(<?php echo $cat['id_categoria']; ?>, '<?php echo addslashes($cat['nombre']); ?>')"
                            class="btn btn-danger btn-icon" data-tooltip="Eliminar">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                    <?php else: ?>
                    <button disabled class="btn btn-icon" style="opacity:0.3;cursor:not-allowed" data-tooltip="Tiene productos asociados">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($categorias)): ?>
              <tr>
                <td colspan="4">
                  <div class="empty-state">
                    <div class="empty-state-icon">
                      <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 7h.01M7 3h5l5.707 5.707a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414L7 3z"/>
                      </svg>
                    </div>
                    <p class="empty-state-title">Sin categorías</p>
                    <p class="empty-state-text">Crea la primera categoría para organizar tu inventario.</p>
                    <button class="btn btn-primary" onclick="openPanel('crear')">Nueva Categoría</button>
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

<!-- Overlay -->
<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>

<!-- Panel Crear -->
<div class="slide-panel" id="panel-crear">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Nueva Categoría</p><p class="slide-panel-subtitle">Clasifica tus productos</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/CategoriaController.php?action=crear" method="POST">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" required class="form-input" placeholder="Ej. Electrónica, Ropa...">
      </div>
      <div class="form-group">
        <label class="form-label">Descripción <span style="color:var(--text-muted);font-weight:400;text-transform:none">(opcional)</span></label>
        <textarea name="descripcion" class="form-textarea" placeholder="Breve descripción de esta categoría..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Guardar Categoría</button>
    </form>
  </div>
</div>

<!-- Panel Editar -->
<div class="slide-panel" id="panel-editar">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Editar Categoría</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/CategoriaController.php?action=editar" method="POST">
      <input type="hidden" name="id_categoria" id="edit-id">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" id="edit-nombre" required class="form-input">
      </div>
      <div class="form-group">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" id="edit-descripcion" class="form-textarea"></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Actualizar Categoría</button>
    </form>
  </div>
</div>

<!-- Modal Eliminar -->
<div class="modal-backdrop" id="deleteModal">
  <div class="modal-box">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="width:56px;height:56px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#c62828">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-dark);margin-bottom:0.5rem">¿Eliminar categoría?</h3>
      <p style="font-size:0.875rem;color:var(--text-muted)">
        Se eliminará <strong id="deleteCatName" style="color:var(--text-dark)"></strong>. Esta acción no se puede deshacer.
      </p>
    </div>
    <div style="display:flex;gap:0.75rem">
      <button onclick="closeDeleteModal()" class="btn btn-secondary" style="flex:1">Cancelar</button>
      <a id="deleteLink" href="#" class="btn btn-danger" style="flex:1;justify-content:center">Eliminar</a>
    </div>
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
function openEditPanel(cat) {
  document.getElementById('edit-id').value          = cat.id_categoria;
  document.getElementById('edit-nombre').value      = cat.nombre;
  document.getElementById('edit-descripcion').value = cat.descripcion || '';
  openPanel('editar');
}
function confirmDelete(id, nombre) {
  document.getElementById('deleteCatName').textContent = nombre;
  document.getElementById('deleteLink').href = '../../controllers/CategoriaController.php?action=eliminar&id=' + id;
  document.getElementById('deleteModal').classList.add('active');
}
function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('active');
}
</script>
</body>
</html>
