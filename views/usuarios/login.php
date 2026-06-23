<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    $rol = $_SESSION['rol'] ?? '';
    $dest = in_array($rol, ['admin','administrador']) ? '../dashboard/admin.php' : '../dashboard/empleado.php';
    header("Location: $dest"); exit;
}
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// Ruta base: views/usuarios/ -> ../../ = raiz del proyecto
$base = '../../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MARTS — Iniciar Sesión</title>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
  <style>
    /* CSS crítico inline — garantiza diseño aunque falle el archivo externo */
    :root{--green-dark:#2d5a3d;--gold:#c8832a;--gold-hover:#b5721f;--beige:#f5f0e8;--white:#fff;--text-dark:#1a2e1f;--text-mid:#4a5e50;--text-muted:#8a9e90;--border:#d8e4db;--radius-sm:.5rem;--radius:.875rem;--radius-xl:1.75rem}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:var(--beige);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
    h1,h2{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800}
    .login-page{width:100%;display:flex;align-items:center;justify-content:center}
    .login-card{width:100%;max-width:860px;background:var(--white);border-radius:var(--radius-xl);box-shadow:0 8px 40px rgba(45,90,61,0.16);overflow:hidden;display:flex}
    .login-left{width:340px;flex-shrink:0;background:var(--green-dark);padding:2.5rem;display:flex;flex-direction:column;justify-content:space-between;position:relative;overflow:hidden}
    .login-left::before{content:'';position:absolute;top:-80px;right:-80px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%}
    .login-left::after{content:'';position:absolute;bottom:-60px;left:-60px;width:160px;height:160px;background:rgba(255,255,255,0.04);border-radius:50%}
    .login-left-content{position:relative;z-index:1}
    .login-left h2{font-size:1.75rem;color:white;line-height:1.2;margin-bottom:1rem}
    .login-left p{font-size:.875rem;color:rgba(255,255,255,.7);line-height:1.6}
    .login-left-logo{margin-top:2rem;display:flex;align-items:center;justify-content:center}
    .login-left-logo-placeholder{width:80px;height:80px;background:rgba(255,255,255,.1);border-radius:1.25rem;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.5)}
    .login-left-footer{position:relative;z-index:1;background:rgba(255,255,255,.08);border-radius:.875rem;padding:.875rem 1rem;display:flex;align-items:center;gap:.75rem}
    .login-left-footer-icon{width:32px;height:32px;flex-shrink:0;background:var(--gold);border-radius:.5rem;display:flex;align-items:center;justify-content:center;color:white}
    .login-left-footer p{font-size:.75rem;color:rgba(255,255,255,.7);margin:0}
    .login-right{flex:1;padding:2.5rem;display:flex;flex-direction:column;justify-content:center}
    .login-right-header{margin-bottom:2rem}
    .login-icon{width:48px;height:48px;background:#e8f0eb;border-radius:.875rem;display:flex;align-items:center;justify-content:center;color:var(--green-dark);margin-bottom:1rem}
    .login-right-header h1{font-size:1.75rem;color:var(--text-dark);margin-bottom:.375rem}
    .login-right-header p{font-size:.875rem;color:var(--text-muted)}
    .form-group{margin-bottom:1.125rem}
    .form-label{display:block;font-size:.75rem;font-weight:600;color:var(--text-mid);margin-bottom:.45rem}
    .input-wrap{position:relative}
    .input-wrap .i-icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none}
    .form-input{width:100%;background:#f5f0e8;border:1.5px solid var(--border);border-radius:.5rem;padding:.7rem .95rem .7rem 2.6rem;font-size:.875rem;color:var(--text-dark);font-family:inherit;outline:none;transition:all .25s}
    .form-input:focus{background:var(--white);border-color:#3a7a52;box-shadow:0 0 0 3px rgba(58,122,82,.12)}
    .form-input.is-invalid{border-color:#c62828!important}
    .field-error{font-size:.7rem;color:#c62828;margin-top:.3rem;display:none}
    .field-error.show{display:block}
    .toggle-pwd{position:absolute;right:.9rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0}
    .alert-error{background:#fce4ec;border:1px solid #f8bbd0;color:#c62828;padding:.875rem 1.125rem;border-radius:.875rem;display:flex;gap:.75rem;margin-bottom:1.25rem;font-size:.875rem}
    .btn-login{width:100%;padding:.9rem;background:var(--gold);color:white;border:none;border-radius:.5rem;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .25s;box-shadow:0 4px 14px rgba(200,131,42,.35)}
    .btn-login:hover{background:var(--gold-hover);transform:translateY(-2px)}
    .login-footer-links{text-align:center;margin-top:1.5rem;font-size:.82rem;color:var(--text-muted)}
    .login-footer-links a{color:var(--gold);font-weight:600}
    .login-back{display:flex;align-items:center;justify-content:center;gap:.375rem;margin-top:.75rem;font-size:.82rem;color:var(--text-muted);cursor:pointer}
    .login-back:hover{color:var(--green-dark)}
    .remember-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem}
    .remember-label{display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.82rem;color:var(--text-mid)}
    .forgot-link{font-size:.82rem;color:var(--gold);font-weight:600;cursor:pointer}
    @media(max-width:768px){.login-card{flex-direction:column;max-width:420px}.login-left{width:100%;padding:1.75rem}.login-left-logo{display:none}}
    @media(max-width:480px){.login-card{border-radius:1.25rem}}
  </style>
</head>
<body>

<div class="login-page">
  <div class="login-card">

    <!-- Panel izquierdo verde -->
    <div class="login-left">
      <div class="login-left-content">
        <h2>Bienvenido nuevamente</h2>
        <p>Accede al sistema para administrar productos, categorías, ventas e inventario de tu tienda de manera eficiente.</p>
        <div class="login-left-logo">
          <div class="login-left-logo-placeholder">
            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
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
        <p>Acceso seguro y protegido para la gestión de tu información.</p>
      </div>
    </div>

    <!-- Panel derecho blanco -->
    <div class="login-right">
      <div class="login-right-header">
        <div class="login-icon">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <h1>Iniciar Sesión</h1>
        <p>Ingresa tus credenciales para continuar</p>
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

      <form id="loginForm" action="<?php echo $base; ?>controllers/AuthController.php" method="POST" novalidate>

        <div class="form-group">
          <label class="form-label" for="correo">Usuario o correo</label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </span>
            <input type="email" id="correo" name="correo" class="form-input"
                   placeholder="Ingresa tu usuario" required autocomplete="email"
                   oninput="validateField(this)">
          </div>
          <p class="field-error" id="correo-error">Ingresa un correo válido</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Contraseña</label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </span>
            <input type="password" id="password" name="password" class="form-input"
                   placeholder="••••••••" required autocomplete="current-password"
                   style="padding-right:2.8rem" oninput="validateField(this)">
            <button type="button" class="toggle-pwd" onclick="togglePassword()" tabindex="-1">
              <svg id="eyeIcon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
          <p class="field-error" id="password-error">Mínimo 6 caracteres</p>
        </div>

        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" name="remember" style="width:14px;height:14px;accent-color:var(--gold);cursor:pointer">
            Recordar sesión
          </label>
          <span class="forgot-link">¿Olvidaste tu contraseña?</span>
        </div>

        <button type="submit" class="btn-login" id="submitBtn">
          <span id="btnContent">Entrar al Sistema</span>
        </button>

      </form>

      <div class="login-footer-links">
        <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        <div class="login-back" onclick="window.location.href='<?php echo $base; ?>'">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Volver al inicio
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function togglePassword() {
  var input = document.getElementById('password');
  var icon  = document.getElementById('eyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
  } else {
    input.type = 'password';
    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
  }
}
function validateField(input) {
  var errEl = document.getElementById(input.id + '-error');
  var valid = input.id === 'correo'
    ? /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())
    : input.value.length >= 6;
  input.classList.toggle('is-invalid', input.value.length > 0 && !valid);
  if (errEl) errEl.classList.toggle('show', input.value.length > 0 && !valid);
}
document.getElementById('loginForm').addEventListener('submit', function(e) {
  var correo   = document.getElementById('correo');
  var password = document.getElementById('password');
  var valid    = true;
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo.value.trim())) {
    correo.classList.add('is-invalid');
    document.getElementById('correo-error').classList.add('show');
    valid = false;
  }
  if (password.value.length < 6) {
    password.classList.add('is-invalid');
    document.getElementById('password-error').classList.add('show');
    valid = false;
  }
  if (!valid) { e.preventDefault(); return; }
  document.getElementById('btnContent').textContent = 'Verificando...';
  document.getElementById('submitBtn').disabled = true;
  document.getElementById('submitBtn').style.opacity = '0.8';
});
</script>
</body>
</html>
