<?php
session_start();
if (isset($_SESSION["id_usuario"])) {
    header("Location: ../dashboard/admin.php"); exit;
}
$error  = $_SESSION["error_reg"]  ?? null;
$exito  = $_SESSION["exito_reg"]  ?? null;
unset($_SESSION["error_reg"], $_SESSION["exito_reg"]);
$base = "../../";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MARTS — Crear Cuenta</title>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
  <style>
    :root{--green-dark:#2d5a3d;--gold:#c8832a;--gold-hover:#b5721f;--beige:#f5f0e8;--white:#fff;--text-dark:#1a2e1f;--text-mid:#4a5e50;--text-muted:#8a9e90;--border:#d8e4db;--radius-sm:.5rem;--radius:.875rem;--radius-xl:1.75rem}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:var(--beige);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
    h1,h2{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800}
    .login-page{width:100%;display:flex;align-items:center;justify-content:center}
    .login-card{width:100%;max-width:900px;background:var(--white);border-radius:var(--radius-xl);box-shadow:0 8px 40px rgba(45,90,61,0.16);overflow:hidden;display:flex}
    .login-left{width:320px;flex-shrink:0;background:var(--green-dark);padding:2.5rem;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
    .login-left::before{content:'';position:absolute;top:-80px;right:-80px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%}
    .login-left::after{content:'';position:absolute;bottom:-60px;left:-60px;width:160px;height:160px;background:rgba(255,255,255,0.04);border-radius:50%}
    .login-left-content{position:relative;z-index:1}
    .login-left h2{font-size:1.6rem;color:white;line-height:1.2;margin-bottom:1rem}
    .login-left p{font-size:.875rem;color:rgba(255,255,255,.7);line-height:1.6}
    .login-left-steps{margin-top:1.5rem;display:flex;flex-direction:column;gap:.75rem;position:relative;z-index:1}
    .step-item{display:flex;align-items:center;gap:.75rem}
    .step-num{width:28px;height:28px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:white;flex-shrink:0}
    .step-text{font-size:.8rem;color:rgba(255,255,255,.8)}
    .login-left-footer{position:relative;z-index:1;background:rgba(255,255,255,.08);border-radius:.875rem;padding:.875rem 1rem;display:flex;align-items:center;gap:.75rem}
    .login-left-footer-icon{width:32px;height:32px;flex-shrink:0;background:var(--gold);border-radius:.5rem;display:flex;align-items:center;justify-content:center;color:white}
    .login-left-footer p{font-size:.75rem;color:rgba(255,255,255,.7);margin:0}
    .login-right{flex:1;padding:2.5rem;display:flex;flex-direction:column;justify-content:center;overflow-y:auto}
    .login-right-header{margin-bottom:1.5rem}
    .login-icon{width:44px;height:44px;background:#e8f0eb;border-radius:.875rem;display:flex;align-items:center;justify-content:center;color:var(--green-dark);margin-bottom:.875rem}
    .login-right-header h1{font-size:1.5rem;color:var(--text-dark);margin-bottom:.3rem}
    .login-right-header p{font-size:.875rem;color:var(--text-muted)}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .form-group{margin-bottom:1rem}
    .form-label{display:block;font-size:.75rem;font-weight:600;color:var(--text-mid);margin-bottom:.4rem}
    .input-wrap{position:relative}
    .input-wrap .i-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none}
    .form-input,.form-select{width:100%;background:#f5f0e8;border:1.5px solid var(--border);border-radius:.5rem;padding:.65rem .95rem .65rem 2.6rem;font-size:.875rem;color:var(--text-dark);font-family:inherit;outline:none;transition:all .25s;-webkit-appearance:none}
    .form-select{padding-left:.95rem;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234a5e50'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .75rem center;background-size:14px;padding-right:2.25rem}
    .form-input:focus,.form-select:focus{background:var(--white);border-color:#3a7a52;box-shadow:0 0 0 3px rgba(58,122,82,.12)}
    .form-input.is-invalid{border-color:#c62828!important;box-shadow:0 0 0 3px rgba(198,40,40,.1)!important}
    .form-input.is-valid{border-color:#2e7d32!important}
    .field-error{font-size:.68rem;color:#c62828;margin-top:.25rem;display:none}
    .field-error.show{display:block}
    .toggle-pwd{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0}
    .alert-error{background:#fce4ec;border:1px solid #f8bbd0;color:#c62828;padding:.875rem 1.125rem;border-radius:.875rem;display:flex;gap:.75rem;margin-bottom:1.25rem;font-size:.875rem;align-items:flex-start}
    .alert-success{background:#e8f5e9;border:1px solid #c8e6c9;color:#2e7d32;padding:.875rem 1.125rem;border-radius:.875rem;display:flex;gap:.75rem;margin-bottom:1.25rem;font-size:.875rem;align-items:flex-start}
    .btn-login{width:100%;padding:.875rem;background:var(--gold);color:white;border:none;border-radius:.5rem;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .25s;box-shadow:0 4px 14px rgba(200,131,42,.35);margin-top:.5rem}
    .btn-login:hover{background:var(--gold-hover);transform:translateY(-2px)}
    .login-footer-links{text-align:center;margin-top:1.25rem;font-size:.82rem;color:var(--text-muted)}
    .login-footer-links a{color:var(--gold);font-weight:600}
    .password-strength{height:4px;border-radius:9999px;background:#e0e0e0;margin-top:.375rem;overflow:hidden}
    .password-strength-bar{height:100%;border-radius:9999px;transition:all .3s;width:0}
    .strength-text{font-size:.65rem;margin-top:.25rem}
    @media(max-width:768px){.login-card{flex-direction:column;max-width:480px}.login-left{width:100%;padding:1.75rem}.login-left-steps{display:none}.form-row{grid-template-columns:1fr}}
  </style>
</head>
<body>
<div class="login-page">
  <div class="login-card">

    <!-- Panel izquierdo -->
    <div class="login-left">
      <div class="login-left-content">
        <h2>Crea tu cuenta</h2>
        <p>Únete al sistema de gestión de inventario MARTS y administra tu tienda de forma eficiente.</p>
        <div class="login-left-steps">
          <div class="step-item">
            <div class="step-num">1</div>
            <span class="step-text">Completa tus datos personales</span>
          </div>
          <div class="step-item">
            <div class="step-num">2</div>
            <span class="step-text">Crea una contraseña segura</span>
          </div>
          <div class="step-item">
            <div class="step-num">3</div>
            <span class="step-text">Accede al sistema de inventario</span>
          </div>
        </div>
      </div>
      <div class="login-left-footer">
        <div class="login-left-footer-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <p>Tu información está protegida con encriptación segura.</p>
      </div>
    </div>

    <!-- Panel derecho -->
    <div class="login-right">
      <div class="login-right-header">
        <div class="login-icon">
          <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
          </svg>
        </div>
        <h1>Crear Cuenta</h1>
        <p>Completa el formulario para registrarte en MARTS</p>
      </div>

      <?php if ($error): ?>
      <div class="alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
      <?php endif; ?>

      <?php if ($exito): ?>
      <div class="alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span><?php echo htmlspecialchars($exito); ?></span>
      </div>
      <?php endif; ?>

      <form id="regForm" action="<?php echo $base; ?>controllers/RegistroController.php" method="POST" novalidate>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="nombre">Nombre *</label>
            <div class="input-wrap">
              <span class="i-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </span>
              <input type="text" id="nombre" name="nombre" class="form-input"
                     placeholder="Tu nombre" required oninput="validateReq(this)">
            </div>
            <p class="field-error" id="nombre-error">El nombre es obligatorio</p>
          </div>
          <div class="form-group">
            <label class="form-label" for="apellido">Apellido</label>
            <div class="input-wrap">
              <span class="i-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </span>
              <input type="text" id="apellido" name="apellido" class="form-input" placeholder="Tu apellido">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="correo">Correo electrónico *</label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
              </svg>
            </span>
            <input type="email" id="correo" name="correo" class="form-input"
                   placeholder="correo@ejemplo.com" required oninput="validateEmail(this)">
          </div>
          <p class="field-error" id="correo-error">Ingresa un correo válido</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="telefono">Teléfono <span style="color:var(--text-muted);font-weight:400">(opcional)</span></label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
            </span>
            <input type="tel" id="telefono" name="telefono" class="form-input" placeholder="+57 300 000 0000">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Contraseña *</label>
            <div class="input-wrap">
              <span class="i-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <input type="password" id="password" name="password" class="form-input"
                     placeholder="Mínimo 6 caracteres" required
                     style="padding-right:2.8rem" oninput="checkStrength(this)">
              <button type="button" class="toggle-pwd" onclick="togglePwd('password','eye1')" tabindex="-1">
                <svg id="eye1" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
            <div class="password-strength"><div class="password-strength-bar" id="strengthBar"></div></div>
            <p class="strength-text" id="strengthText" style="color:var(--text-muted)"></p>
            <p class="field-error" id="password-error">Mínimo 6 caracteres</p>
          </div>
          <div class="form-group">
            <label class="form-label" for="confirm_password">Confirmar contraseña *</label>
            <div class="input-wrap">
              <span class="i-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </span>
              <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                     placeholder="Repite tu contraseña" required
                     style="padding-right:2.8rem" oninput="checkMatch()">
              <button type="button" class="toggle-pwd" onclick="togglePwd('confirm_password','eye2')" tabindex="-1">
                <svg id="eye2" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
            <p class="field-error" id="confirm-error">Las contraseñas no coinciden</p>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="rol">Tipo de cuenta *</label>
          <select id="rol" name="id_rol" class="form-select" required>
            <option value="">Selecciona un rol...</option>
            <option value="2">Empleado — Gestión de inventario</option>
            <option value="1">Administrador — Acceso completo</option>
          </select>
          <p class="field-error" id="rol-error">Selecciona un tipo de cuenta</p>
        </div>

        <button type="submit" class="btn-login" id="submitBtn">
          <span id="btnContent">Crear Cuenta</span>
        </button>

      </form>

      <div class="login-footer-links">
        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
      </div>
    </div>

  </div>
</div>

<script>
function togglePwd(inputId, iconId) {
  var input = document.getElementById(inputId);
  input.type = input.type === 'password' ? 'text' : 'password';
}
function validateReq(input) {
  var err = document.getElementById(input.id + '-error');
  var ok  = input.value.trim().length > 0;
  input.classList.toggle('is-invalid', !ok && input.value.length > 0);
  input.classList.toggle('is-valid',    ok);
  if (err) err.classList.toggle('show', !ok && input.value.length > 0);
}
function validateEmail(input) {
  var err   = document.getElementById('correo-error');
  var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
  input.classList.toggle('is-invalid', input.value.length > 0 && !valid);
  input.classList.toggle('is-valid',   input.value.length > 0 &&  valid);
  if (err) err.classList.toggle('show', input.value.length > 0 && !valid);
}
function checkStrength(input) {
  var val  = input.value;
  var bar  = document.getElementById('strengthBar');
  var txt  = document.getElementById('strengthText');
  var err  = document.getElementById('password-error');
  var score = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  var colors = ['','#ef5350','#ff9800','#ffc107','#66bb6a','#2e7d32'];
  var labels = ['','Muy débil','Débil','Regular','Buena','Muy segura'];
  bar.style.width  = (score * 20) + '%';
  bar.style.background = colors[score] || '#e0e0e0';
  txt.textContent  = val.length > 0 ? labels[score] : '';
  txt.style.color  = colors[score] || 'var(--text-muted)';
  input.classList.toggle('is-invalid', val.length > 0 && val.length < 6);
  input.classList.toggle('is-valid',   val.length >= 6);
  if (err) err.classList.toggle('show', val.length > 0 && val.length < 6);
}
function checkMatch() {
  var p1  = document.getElementById('password').value;
  var p2  = document.getElementById('confirm_password');
  var err = document.getElementById('confirm-error');
  var ok  = p2.value.length > 0 && p1 === p2.value;
  p2.classList.toggle('is-invalid', p2.value.length > 0 && !ok);
  p2.classList.toggle('is-valid',   ok);
  if (err) err.classList.toggle('show', p2.value.length > 0 && !ok);
}
document.getElementById('regForm').addEventListener('submit', function(e) {
  var nombre  = document.getElementById('nombre');
  var correo  = document.getElementById('correo');
  var pass    = document.getElementById('password');
  var confirm = document.getElementById('confirm_password');
  var rol     = document.getElementById('rol');
  var valid   = true;
  if (!nombre.value.trim()) { nombre.classList.add('is-invalid'); document.getElementById('nombre-error').classList.add('show'); valid = false; }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) { correo.classList.add('is-invalid'); document.getElementById('correo-error').classList.add('show'); valid = false; }
  if (pass.value.length < 6) { pass.classList.add('is-invalid'); document.getElementById('password-error').classList.add('show'); valid = false; }
  if (pass.value !== confirm.value) { confirm.classList.add('is-invalid'); document.getElementById('confirm-error').classList.add('show'); valid = false; }
  if (!rol.value) { document.getElementById('rol-error').classList.add('show'); valid = false; }
  if (!valid) { e.preventDefault(); return; }
  document.getElementById('btnContent').textContent = 'Creando cuenta...';
  document.getElementById('submitBtn').disabled = true;
});
</script>
</body>
</html>
