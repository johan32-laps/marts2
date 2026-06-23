<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$rol         = $_SESSION['rol'] ?? 'empleado';
$isAdmin     = in_array($rol, ['admin','administrador']);
$userName    = $_SESSION['usuario'] ?? 'Usuario';
$userInitial = strtoupper(mb_substr($userName, 0, 1));
$base        = '../../';

function sidebarActive(string $page = '', string $dir = ''): string {
    global $currentPage, $currentDir;
    if ($page && $currentPage === $page) return 'active';
    if ($dir  && $currentDir  === $dir)  return 'active';
    return '';
}
?>
<aside class="sidebar" id="sidebar">

  <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Colapsar"
          style="display:none">
    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
    </svg>
  </button>

  <!-- Anti-flash: aplicar clase antes del render -->
<script>
(function(){
  if (localStorage.getItem('sidebarCollapsed') === 'true') {
    document.documentElement.classList.add('sidebar-is-collapsed');
  }
}());
</script>
  <div class="sidebar-brand" onclick="toggleSidebar()" style="cursor:pointer"
       title="Colapsar / Expandir menú">
    <div class="sidebar-brand-icon">
      <svg width="19" height="19" fill="none" stroke="white" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
    </div>
    <div class="sidebar-brand-text">
      <h2>MARTS</h2>
      <span>Inventory System</span>
    </div>
  </div>

  <nav class="sidebar-section">

    <!-- Principal -->
    <p class="sidebar-section-label">Principal</p>

    <?php if($isAdmin): ?>
    <a href="<?php echo $base; ?>views/dashboard/admin.php"
       class="sidebar-link <?php echo sidebarActive('admin.php'); ?>" data-label="Dashboard">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></span>
      <span class="sidebar-link-text">Dashboard</span>
    </a>
    <?php else: ?>
    <a href="<?php echo $base; ?>views/dashboard/empleado.php"
       class="sidebar-link <?php echo sidebarActive('empleado.php'); ?>" data-label="Dashboard">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></span>
      <span class="sidebar-link-text">Dashboard</span>
    </a>
    <?php endif; ?>

    <!-- Operaciones -->
    <p class="sidebar-section-label" style="margin-top:1.125rem">Operaciones</p>

    <a href="<?php echo $base; ?>views/ventas/index.php"
       class="sidebar-link <?php echo sidebarActive('','ventas'); ?>" data-label="Ventas">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></span>
      <span class="sidebar-link-text">Ventas</span>
    </a>

    <a href="<?php echo $base; ?>views/caja/index.php"
       class="sidebar-link <?php echo sidebarActive('','caja'); ?>" data-label="Caja">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
      <span class="sidebar-link-text">Caja</span>
    </a>

    <!-- Inventario -->
    <p class="sidebar-section-label" style="margin-top:1.125rem">Inventario</p>

    <a href="<?php echo $base; ?>views/dashboard/adminproductos.php"
       class="sidebar-link <?php echo sidebarActive('adminproductos.php'); ?>" data-label="Productos">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
      <span class="sidebar-link-text">Productos</span>
    </a>

    <a href="<?php echo $base; ?>views/movimientos/index.php"
       class="sidebar-link <?php echo sidebarActive('','movimientos'); ?>" data-label="Movimientos">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></span>
      <span class="sidebar-link-text">Movimientos</span>
    </a>

    <a href="<?php echo $base; ?>views/categorias/index.php"
       class="sidebar-link <?php echo sidebarActive('','categorias'); ?>" data-label="Categorías">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5l5.707 5.707a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414L7 3z"/></svg></span>
      <span class="sidebar-link-text">Categorías</span>
    </a>

    <?php if($isAdmin): ?>

    <!-- Administración -->
    <p class="sidebar-section-label" style="margin-top:1.125rem">Administración</p>

    <a href="<?php echo $base; ?>views/compras/index.php"
       class="sidebar-link <?php echo sidebarActive('','compras'); ?>" data-label="Compras">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
      <span class="sidebar-link-text">Compras</span>
    </a>

    <a href="<?php echo $base; ?>views/devoluciones/index.php"
       class="sidebar-link <?php echo sidebarActive('','devoluciones'); ?>" data-label="Devoluciones">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg></span>
      <span class="sidebar-link-text">Devoluciones</span>
    </a>

    <a href="<?php echo $base; ?>views/tipos/index.php"
       class="sidebar-link <?php echo sidebarActive('','tipos'); ?>" data-label="Tipos Mov.">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></span>
      <span class="sidebar-link-text">Tipos de Mov.</span>
    </a>

    <a href="<?php echo $base; ?>views/usuarios/index.php"
       class="sidebar-link <?php echo ($currentDir==='usuarios'&&$currentPage!=='login.php'&&$currentPage!=='registro.php') ? 'active' : ''; ?>" data-label="Usuarios">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
      <span class="sidebar-link-text">Usuarios</span>
    </a>

    <a href="<?php echo $base; ?>views/reportes/index.php"
       class="sidebar-link <?php echo sidebarActive('','reportes'); ?>" data-label="Reportes">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
      <span class="sidebar-link-text">Reportes</span>
    </a>

    <a href="<?php echo $base; ?>views/dashboard/historial.php"
       class="sidebar-link <?php echo sidebarActive('historial.php'); ?>" data-label="Historial">
      <span class="sidebar-link-icon"><svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
      <span class="sidebar-link-text">Historial</span>
    </a>

    <?php endif; ?>

  </nav>

  <div class="sidebar-footer">
    <a href="<?php echo $base; ?>controllers/AuthController.php?action=logout"
       class="sidebar-user" style="text-decoration:none"
       onclick="return confirm('¿Cerrar sesión?')">
      <div class="sidebar-avatar"><?php echo $userInitial; ?></div>
      <div class="sidebar-user-info">
        <p><?php echo htmlspecialchars($userName); ?></p>
        <span><?php echo $isAdmin ? 'Administrador' : 'Empleado'; ?> &bull; Salir</span>
      </div>
    </a>
  </div>

</aside>

<div class="sidebar-mobile-overlay" id="sidebarMobileOverlay" onclick="closeMobileSidebar()"></div>

<script>
// Ejecutar inmediatamente — el sidebar ya está en el DOM
(function(){
  var s = document.getElementById('sidebar');

  // Aplicar estado guardado SIN transición al cargar
  var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  if (collapsed) {
    s.classList.add('collapsed');
    var applyToMain = function() {
      var c = document.getElementById('mainContent');
      if (c) {
        c.style.transition = 'none';
        c.classList.add('sidebar-collapsed');
        document.documentElement.classList.add('sidebar-is-collapsed');
        setTimeout(function(){ c.style.transition = ''; }, 50);
      }
    };
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', applyToMain);
    } else {
      applyToMain();
    }
  }

  window.toggleSidebar = function() {
    var isCollapsed = s.classList.toggle('collapsed');
    var c = document.getElementById('mainContent');
    if (c) {
      if (isCollapsed) {
        c.classList.add('sidebar-collapsed');
        document.documentElement.classList.add('sidebar-is-collapsed');
      } else {
        c.classList.remove('sidebar-collapsed');
        document.documentElement.classList.remove('sidebar-is-collapsed');
      }
    }
    localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
  };

  window.toggleMobileSidebar = function() {
    var o = document.getElementById('sidebarMobileOverlay');
    s.classList.toggle('mobile-open');
    if (o) o.classList.toggle('active');
  };

  window.closeMobileSidebar = function() {
    var o = document.getElementById('sidebarMobileOverlay');
    s.classList.remove('mobile-open');
    if (o) o.classList.remove('active');
  };
}());
</script>
</script>
