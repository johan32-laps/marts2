<?php
/**
 * Header / Topbar - MARTS
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

$pageTitles = [
    'admin.php'          => 'Dashboard',
    'empleado.php'       => 'Panel Operativo',
    'adminproductos.php' => 'Productos',
    'historial.php'      => 'Historial',
];
$dirTitles = [
    'movimientos' => 'Movimientos',
    'categorias'  => 'Categorías',
    'tipos'       => 'Tipos de Movimiento',
    'usuarios'    => 'Usuarios',
    'reportes'    => 'Reportes',
];
$pageTitle = $pageTitles[$currentPage] ?? $dirTitles[$currentDir] ?? 'MARTS';
$base      = '../../';
?>
<header class="topbar">

  <div class="topbar-left">
    <!-- Hamburguesa móvil -->
    <button class="topbar-hamburger" onclick="toggleMobileSidebar()" aria-label="Menú"
            style="display:none">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>

    <!-- Breadcrumb -->
    <div class="topbar-breadcrumb">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"
           style="color:var(--text-muted);flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
      </svg>
      <span style="color:var(--text-muted)">MARTS</span>
      <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"
           style="color:var(--text-muted);flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
      <span><?php echo htmlspecialchars($pageTitle); ?></span>
    </div>
  </div>

  <div class="topbar-right">

    <!-- Fecha -->
    <div style="font-size:0.72rem;color:var(--text-muted);display:flex;align-items:center;gap:0.35rem"
         class="topbar-date-wrap">
      <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span id="topbar-date"></span>
    </div>

    <!-- Notificaciones -->
    <button class="topbar-btn" id="notifBtn" data-tooltip="Notificaciones">
      <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      <span class="topbar-notif-dot" id="notif-dot" style="display:none"></span>
    </button>

    <!-- Perfil -->
    <div class="topbar-profile">
      <div class="topbar-avatar">
        <?php echo strtoupper(mb_substr($_SESSION['usuario'] ?? 'U', 0, 1)); ?>
      </div>
      <div class="topbar-user-info">
        <span class="topbar-user-name"><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Usuario'); ?></span>
        <span class="topbar-user-role"><?php echo ucfirst($_SESSION['rol'] ?? 'empleado'); ?></span>
      </div>
    </div>

    <!-- Logout rápido -->
    <a href="<?php echo $base; ?>controllers/AuthController.php?action=logout"
       class="topbar-btn" data-tooltip="Cerrar sesión"
       onclick="return confirm('¿Cerrar sesión?')"
       style="color:var(--text-mid)">
      <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
      </svg>
    </a>

  </div>
</header>

<script>
(function () {
  var el = document.getElementById('topbar-date');
  if (el) {
    el.textContent = new Date().toLocaleDateString('es-ES', {
      weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
    });
  }
  // Mostrar hamburguesa en móvil
  var ham = document.querySelector('.topbar-hamburger');
  if (ham && window.innerWidth <= 900) ham.style.display = 'flex';
  window.addEventListener('resize', function() {
    if (ham) ham.style.display = window.innerWidth <= 900 ? 'flex' : 'none';
  });
})();
</script>
