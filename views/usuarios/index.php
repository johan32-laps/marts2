<?php
/**
 * Gestión de Usuarios - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'administrador') {
    header("Location: ../dashboard/empleado.php"); exit;
}

require_once __DIR__ . '/../../models/Usuario.php';
$modelo   = new Usuario();
$usuarios = $modelo->getUsuarios();
$roles    = $modelo->getRoles();

$total   = count($usuarios);
$activos = count(array_filter($usuarios, fn($u) => $u['estado'] == 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios | MARTS</title>
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
          <h1 class="page-title">Usuarios y Roles</h1>
          <p class="page-subtitle">Gestión centralizada de accesos</p>
        </div>
        <button class="btn btn-primary" onclick="openPanel('crear')">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Nuevo Usuario
        </button>
      </div>

      <!-- Alertas -->
      <?php if(isset($_SESSION['error_usr'])): ?>
      <div class="alert alert-error">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['error_usr']); unset($_SESSION['error_usr']); ?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_usr'])): ?>
      <div class="alert alert-success">
        <span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
        <span><?php echo htmlspecialchars($_SESSION['exito_usr']); unset($_SESSION['exito_usr']); ?></span>
      </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stats-grid-3">
        <div class="stat-card animate-fade-in-up">
          <div>
            <p class="stat-card-label">Total</p>
            <p class="stat-card-value"><?php echo $total; ?></p>
          </div>
          <div class="stat-card-icon blue">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
        </div>
        <div class="stat-card animate-fade-in-up delay-100">
          <div>
            <p class="stat-card-label">Activos</p>
            <p class="stat-card-value" style="color:#2e7d32"><?php echo $activos; ?></p>
          </div>
          <div class="stat-card-icon green">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div class="stat-card animate-fade-in-up delay-200">
          <div>
            <p class="stat-card-label">Inactivos</p>
            <p class="stat-card-value" style="color:var(--text-muted)"><?php echo $total - $activos; ?></p>
          </div>
          <div class="stat-card-icon red">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
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
                <th>Usuario</th>
                <th>Correo</th>
                <th>Rol</th>
                <th style="text-align:center">Estado</th>
                <th style="text-align:right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($usuarios as $usr): ?>
              <tr style="<?php echo $usr['estado'] == 0 ? 'opacity:0.5' : ''; ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:0.75rem">
                    <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:linear-gradient(135deg,var(--green-dark),#7b1fa2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.875rem;color:white;flex-shrink:0">
                      <?php echo strtoupper(mb_substr($usr['nombre'],0,1)); ?>
                    </div>
                    <p style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($usr['nombre']); ?></p>
                  </div>
                </td>
                <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                <td>
                  <span class="pill <?php echo ($usr['rol_nombre'] ?? '') === 'admin' ? 'pill-blue' : 'pill-purple'; ?>">
                    <?php echo ucfirst(htmlspecialchars($usr['rol_nombre'] ?? 'Sin rol')); ?>
                  </span>
                </td>
                <td style="text-align:center">
                  <span class="pill <?php echo $usr['estado'] == 1 ? 'pill-green' : 'pill-gray'; ?>">
                    <span class="pill-dot"></span>
                    <?php echo $usr['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                  </span>
                </td>
                <td style="text-align:right">
                  <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.375rem">
                    <button onclick="openEditPanel(<?php echo htmlspecialchars(json_encode(['id_usuario'=>$usr['id_usuario'],'nombre'=>$usr['nombre'],'correo'=>$usr['correo'],'id_rol'=>$usr['id_rol']])); ?>)"
                            class="btn btn-secondary btn-icon" data-tooltip="Editar">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                      </svg>
                    </button>
                    <button onclick="openPwdPanel(<?php echo $usr['id_usuario']; ?>, '<?php echo addslashes($usr['nombre']); ?>')"
                            class="btn btn-secondary btn-icon" data-tooltip="Cambiar contraseña" style="color:var(--gold)">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                      </svg>
                    </button>
                    <?php if($usr['id_usuario'] != $_SESSION['id_usuario']): ?>
                    <a href="/controllers/UsuarioController.php?action=<?php echo $usr['estado'] == 1 ? 'desactivar' : 'reactivar'; ?>&id=<?php echo $usr['id_usuario']; ?>"
                       class="btn btn-icon <?php echo $usr['estado'] == 1 ? 'btn-danger' : 'btn-success'; ?>"
                       data-tooltip="<?php echo $usr['estado'] == 1 ? 'Desactivar' : 'Reactivar'; ?>">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php if($usr['estado'] == 1): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <?php endif; ?>
                      </svg>
                    </a>
                    <?php endif; ?>
                  </div>
                </td>
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

<!-- Overlay -->
<div class="panel-overlay" id="panelOverlay" onclick="closeAll()"></div>

<!-- Panel Crear -->
<div class="slide-panel" id="panel-crear">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Nuevo Usuario</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/UsuarioController.php?action=crear" method="POST">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" required class="form-input" placeholder="Nombre completo">
      </div>
      <div class="form-group">
        <label class="form-label">Correo *</label>
        <input type="email" name="correo" required class="form-input" placeholder="correo@ejemplo.com">
      </div>
      <div class="form-group">
        <label class="form-label">Contraseña *</label>
        <input type="password" name="password" required class="form-input" placeholder="Mínimo 6 caracteres">
      </div>
      <div class="form-group">
        <label class="form-label">Rol *</label>
        <select name="id_rol" required class="form-select">
          <?php foreach($roles as $r): ?>
            <option value="<?php echo $r['id_rol']; ?>"><?php echo ucfirst(htmlspecialchars($r['nombre'])); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Crear Usuario</button>
    </form>
  </div>
</div>

<!-- Panel Editar -->
<div class="slide-panel" id="panel-editar">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Editar Usuario</p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/UsuarioController.php?action=editar" method="POST">
      <input type="hidden" name="id_usuario" id="edit-id">
      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" id="edit-nombre" required class="form-input">
      </div>
      <div class="form-group">
        <label class="form-label">Correo *</label>
        <input type="email" name="correo" id="edit-correo" required class="form-input">
      </div>
      <div class="form-group">
        <label class="form-label">Rol *</label>
        <select name="id_rol" id="edit-rol" required class="form-select">
          <?php foreach($roles as $r): ?>
            <option value="<?php echo $r['id_rol']; ?>"><?php echo ucfirst(htmlspecialchars($r['nombre'])); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem">Guardar Cambios</button>
    </form>
  </div>
</div>

<!-- Panel Contraseña -->
<div class="slide-panel" id="panel-pwd">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Cambiar Contraseña</p><p class="slide-panel-subtitle" id="pwd-subtitle"></p></div>
    <button class="slide-panel-close" onclick="closeAll()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>
  <div class="slide-panel-body">
    <form action="../../controllers/UsuarioController.php?action=cambiar_password" method="POST">
      <input type="hidden" name="id_usuario" id="pwd-id">
      <div class="form-group">
        <label class="form-label">Nueva Contraseña *</label>
        <input type="password" name="password" required class="form-input" placeholder="Mínimo 6 caracteres">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;padding:0.875rem;background:linear-gradient(135deg,var(--gold),#d97706)">
        Actualizar Contraseña
      </button>
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
  ['crear','editar','pwd'].forEach(n => document.getElementById('panel-'+n).classList.remove('active'));
}
function openEditPanel(u) {
  document.getElementById('edit-id').value    = u.id_usuario;
  document.getElementById('edit-nombre').value = u.nombre;
  document.getElementById('edit-correo').value = u.correo;
  document.getElementById('edit-rol').value    = u.id_rol;
  openPanel('editar');
}
function openPwdPanel(id, nombre) {
  document.getElementById('pwd-id').value       = id;
  document.getElementById('pwd-subtitle').textContent = 'Usuario: ' + nombre;
  openPanel('pwd');
}
</script>
</body>
</html>
