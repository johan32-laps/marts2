<?php
session_start();
if (!isset($_SESSION["id_usuario"])) { header("Location: ../usuarios/login.php"); exit; }
require_once __DIR__ . "/../../models/Producto.php";
$productoModel = new Producto();
$searchTerm = $_GET["search"] ?? "";
$productos  = !empty($searchTerm) ? $productoModel->buscar($searchTerm) : $productoModel->listarProductos();
$categorias = $productoModel->obtenerCategorias();
$isAdmin    = in_array($_SESSION["rol"] ?? "", ["admin","administrador"]);
$base       = "../../";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Productos | MARTS</title>
  <link rel="icon" type="image/png" href="<?php echo $base; ?>public/img/icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base; ?>public/css/style.css">
</head>
<body>
<div class="app-layout">
  <?php include '../layouts/sidebar.php'; ?>
  <div class="main-content" id="mainContent">
    <?php include '../layouts/header.php'; ?>
    <div class="page-content animate-fade-in">

      <!-- Header -->
      <div class="page-header">
        <div>
          <h1 class="page-title" id="pageTitle">
            <?php echo !empty($searchTerm) ? 'Búsqueda: "'.htmlspecialchars($searchTerm).'"' : 'Catálogo de Productos'; ?>
          </h1>
          <p class="page-subtitle" id="pageSubtitle">
            <?php echo !empty($searchTerm) ? count($productos).' resultados' : count($productos).' productos en catálogo'; ?>
          </p>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center">
          <!-- Búsqueda en tiempo real -->
          <div style="display:flex;align-items:center;gap:.5rem;background:var(--beige);border:1.5px solid var(--border);border-radius:var(--radius-sm);padding:.5rem .875rem;transition:all var(--t-base)"
               onfocusin="this.style.borderColor='var(--green-mid)'" onfocusout="this.style.borderColor='var(--border)'">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--text-muted);flex-shrink:0">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" value="<?php echo htmlspecialchars($searchTerm); ?>"
                   placeholder="Buscar producto..." autocomplete="off" oninput="searchProducts(this.value)"
                   style="background:transparent;border:none;outline:none;color:var(--text-dark);font-size:.875rem;width:200px">
            <button id="clearSearch" onclick="clearSearch()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0;display:<?php echo !empty($searchTerm)?'flex':'none'; ?>;align-items:center">
              <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
          <button class="btn btn-primary" onclick="openPanel('crear')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Producto
          </button>
        </div>
      </div>

      <!-- Alertas -->
      <?php if(isset($_SESSION['error_producto'])): ?>
      <div class="alert alert-error"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span><span><?php echo htmlspecialchars($_SESSION['error_producto']); unset($_SESSION['error_producto']); ?></span></div>
      <?php endif; ?>
      <?php if(isset($_SESSION['exito_producto'])): ?>
      <div class="alert alert-success"><span class="alert-icon"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span><span><?php echo htmlspecialchars($_SESSION['exito_producto']); unset($_SESSION['exito_producto']); ?></span></div>
      <?php endif; ?>

      <!-- Tabla de productos -->
      <div class="dark-table-wrap animate-fade-in-up">
        <div style="overflow-x:auto">
          <table class="dark-table" id="productTable">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>P. Compra</th>
                <th>P. Venta</th>
                <th>Stock</th>
                <th>Mín.</th>
                <th style="text-align:right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($productos) > 0): ?>
                <?php foreach($productos as $prod): ?>
                <tr data-nombre="<?php echo htmlspecialchars(strtolower($prod['nombre'])); ?>"
                    data-categoria="<?php echo htmlspecialchars(strtolower($prod['categoria'] ?? '')); ?>">
                  <td>
                    <div style="display:flex;align-items:center;gap:.875rem">
                      <?php if(!empty($prod['imagen'])): ?>
                        <img src="<?php echo $base; ?>public/img/productos/<?php echo htmlspecialchars($prod['imagen']); ?>" class="product-img" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                      <?php else: ?>
                        <div class="product-img-placeholder">
                          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                      <?php endif; ?>
                      <div>
                        <p style="font-weight:600;color:var(--text-dark)"><?php echo htmlspecialchars($prod['nombre']); ?></p>
                        <p style="font-size:.7rem;color:var(--text-muted)">#<?php echo str_pad($prod['id_producto'],5,'0',STR_PAD_LEFT); ?><?php echo !empty($prod['tamano']) ? ' · '.$prod['tamano'] : ''; ?></p>
                      </div>
                    </div>
                  </td>
                  <td><span class="pill pill-purple"><?php echo htmlspecialchars($prod['categoria'] ?? 'Sin cat.'); ?></span></td>
                  <td style="color:var(--text-mid);font-size:.82rem">$<?php echo number_format($prod['precio_compra'] ?? $prod['precio'] ?? 0, 2); ?></td>
                  <td style="color:var(--green-dark);font-weight:700">$<?php echo number_format($prod['precio_venta'] ?? $prod['precio'] ?? 0, 2); ?></td>
                  <td>
                    <?php
                    $stockMin = (int)($prod['stock_minimo'] ?? 5);
                    if ($prod['stock'] > $stockMin * 2) {
                        echo '<span class="pill pill-green"><span class="pill-dot"></span>'.$prod['stock'].' uds.</span>';
                    } elseif ($prod['stock'] > 0) {
                        echo '<span class="pill pill-amber"><span class="pill-dot"></span>'.$prod['stock'].' uds.</span>';
                    } else {
                        echo '<span class="pill pill-red"><span class="pill-dot"></span>Agotado</span>';
                    }
                    ?>
                  </td>
                  <td style="font-size:.8rem;color:var(--text-muted)"><?php echo $prod['stock_minimo'] ?? 5; ?></td>
                  <td style="text-align:right">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:.375rem">
                      <button onclick="openEditPanel(<?php echo htmlspecialchars(json_encode($prod)); ?>)" class="btn btn-secondary btn-icon" data-tooltip="Editar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                      </button>
                      <?php if($isAdmin): ?>
                      <button onclick="confirmDelete(<?php echo $prod['id_producto']; ?>, '<?php echo addslashes($prod['nombre']); ?>')" class="btn btn-danger btn-icon" data-tooltip="Eliminar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr id="emptyRow"><td colspan="7"><div class="empty-state"><div class="empty-state-icon"><svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div><p class="empty-state-title">Sin productos</p><p class="empty-state-text">Agrega el primer producto al catálogo.</p><button class="btn btn-primary" onclick="openPanel('crear')">Agregar Producto</button></div></td></tr>
              <?php endif; ?>
              <tr id="noResultsRow" style="display:none"><td colspan="7"><div class="empty-state"><div class="empty-state-icon"><svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></div><p class="empty-state-title">Sin resultados</p><p class="empty-state-text">No hay productos que coincidan.</p><button class="btn btn-secondary btn-sm" onclick="clearSearch()">Limpiar</button></div></td></tr>
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
    <div><p class="slide-panel-title">Nuevo Producto</p><p class="slide-panel-subtitle">Completa todos los datos del producto</p></div>
    <button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="slide-panel-body">
    <form action="<?php echo $base; ?>controllers/ProductoController.php?action=registrar" method="POST" enctype="multipart/form-data">

      <!-- Imagen -->
      <div class="form-group">
        <label class="form-label">Imagen</label>
        <div class="upload-zone" onclick="document.getElementById('imgInput').click()">
          <input type="file" id="imgInput" name="imagen" accept="image/*" style="display:none" onchange="previewImg(this,'imgPreview','uploadIcon')">
          <div id="uploadIcon" style="text-align:center">
            <div style="width:40px;height:40px;background:var(--green-pale);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;margin:0 auto .625rem;color:var(--green-mid)"><svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <p style="font-size:.78rem;color:var(--text-mid)">Click para subir imagen</p>
            <p style="font-size:.68rem;color:var(--text-muted);margin-top:.2rem">PNG, JPG, WEBP (máx. 5MB)</p>
          </div>
          <img id="imgPreview" style="display:none;max-height:110px;margin:0 auto;border-radius:var(--radius-sm);object-fit:contain">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" required class="form-input" placeholder="Ej. Camiseta Polo Azul">
      </div>

      <div class="form-group">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-textarea" placeholder="Descripción del producto..."></textarea>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Código de Barras</label>
          <input type="text" name="codigo_barras" class="form-input" placeholder="EAN/SKU">
        </div>
        <div class="form-group">
          <label class="form-label">Tamaño / Talla</label>
          <input type="text" name="tamano" class="form-input" placeholder="S, M, L, XL...">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Precio Compra *</label>
          <div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" step="0.01" name="precio_compra" required class="form-input" placeholder="0.00" style="padding-left:1.75rem" oninput="validateRentabilidad()"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Precio Venta *</label>
          <div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" step="0.01" name="precio_venta" required class="form-input" placeholder="0.00" style="padding-left:1.75rem" oninput="validateRentabilidad()"></div>
        </div>
      </div>
      <p id="rentabilidadMsg" style="font-size:.7rem;margin-top:-.75rem;margin-bottom:.75rem;display:none"></p>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group">
          <label class="form-label">Stock Inicial</label>
          <input type="number" name="stock" value="0" min="0" class="form-input">
        </div>
        <div class="form-group">
          <label class="form-label">Stock Mínimo</label>
          <input type="number" name="stock_minimo" value="5" min="0" class="form-input">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Categoría *</label>
        <select name="id_categoria" required class="form-select">
          <option value="">Seleccionar categoría...</option>
          <?php foreach($categorias as $cat): ?>
            <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
        <?php if(empty($categorias)): ?><p style="font-size:.7rem;color:#c62828;margin-top:.35rem">Crea una categoría primero.</p><?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Guardar Producto</button>
    </form>
  </div>
</div>

<!-- Panel Editar -->
<div class="slide-panel" id="panel-editar">
  <div class="slide-panel-header">
    <div><p class="slide-panel-title">Editar Producto</p><p class="slide-panel-subtitle">Modifica los datos del producto</p></div>
    <button class="slide-panel-close" onclick="closeAll()"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
  </div>
  <div class="slide-panel-body">
    <form action="<?php echo $base; ?>controllers/ProductoController.php?action=editar" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_producto" id="edit-id">

      <div class="form-group">
        <label class="form-label">Imagen</label>
        <div class="upload-zone" onclick="document.getElementById('editImgInput').click()">
          <input type="file" id="editImgInput" name="imagen" accept="image/*" style="display:none" onchange="previewImg(this,'editImgPreview',null)">
          <img id="editImgPreview" style="max-height:100px;margin:0 auto;border-radius:var(--radius-sm);object-fit:contain">
          <p style="font-size:.68rem;color:var(--text-muted);margin-top:.5rem;text-align:center">Click para cambiar imagen</p>
        </div>
      </div>

      <div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="edit-nombre" required class="form-input"></div>
      <div class="form-group"><label class="form-label">Descripción</label><textarea name="descripcion" id="edit-descripcion" class="form-textarea"></textarea></div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group"><label class="form-label">Código Barras</label><input type="text" name="codigo_barras" id="edit-codigo" class="form-input"></div>
        <div class="form-group"><label class="form-label">Tamaño / Talla</label><input type="text" name="tamano" id="edit-tamano" class="form-input"></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group"><label class="form-label">Precio Compra *</label><div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" step="0.01" name="precio_compra" id="edit-precio-compra" required class="form-input" style="padding-left:1.75rem"></div></div>
        <div class="form-group"><label class="form-label">Precio Venta *</label><div style="position:relative"><span style="position:absolute;left:.875rem;top:50%;transform:translateY(-50%);color:var(--text-muted)">$</span><input type="number" step="0.01" name="precio_venta" id="edit-precio-venta" required class="form-input" style="padding-left:1.75rem"></div></div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-group"><label class="form-label">Stock Mínimo</label><input type="number" name="stock_minimo" id="edit-stock-min" min="0" class="form-input"></div>
        <div class="form-group"><label class="form-label">Categoría *</label>
          <select name="id_categoria" id="edit-categoria" required class="form-select">
            <?php foreach($categorias as $cat): ?><option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">Actualizar Producto</button>
    </form>
  </div>
</div>

<!-- Modal Eliminar -->
<div class="modal-backdrop" id="deleteModal">
  <div class="modal-box">
    <div style="text-align:center;margin-bottom:1.5rem">
      <div style="width:56px;height:56px;background:#fce4ec;border:1px solid #f8bbd0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#c62828"><svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
      <h3 style="font-size:1.125rem;font-weight:700;color:var(--text-dark);margin-bottom:.5rem">¿Eliminar producto?</h3>
      <p style="font-size:.875rem;color:var(--text-muted)">Se eliminará <strong id="deleteProductName" style="color:var(--text-dark)"></strong>. Esta acción no se puede deshacer.</p>
    </div>
    <div style="display:flex;gap:.75rem">
      <button onclick="closeDeleteModal()" class="btn btn-secondary" style="flex:1">Cancelar</button>
      <a id="deleteLink" href="#" class="btn btn-danger" style="flex:1;justify-content:center">Eliminar</a>
    </div>
  </div>
</div>

<script>
// Panel management
function openPanel(name) { document.getElementById('panelOverlay').classList.add('active'); document.getElementById('panel-'+name).classList.add('active'); }
function closeAll() { document.getElementById('panelOverlay').classList.remove('active'); ['crear','editar'].forEach(n=>document.getElementById('panel-'+n).classList.remove('active')); }

// Edit panel
function openEditPanel(prod) {
  document.getElementById('edit-id').value            = prod.id_producto;
  document.getElementById('edit-nombre').value        = prod.nombre;
  document.getElementById('edit-descripcion').value   = prod.descripcion || '';
  document.getElementById('edit-codigo').value        = prod.codigo_barras || '';
  document.getElementById('edit-tamano').value        = prod.tamano || '';
  document.getElementById('edit-precio-compra').value = prod.precio_compra || prod.precio || 0;
  document.getElementById('edit-precio-venta').value  = prod.precio_venta  || prod.precio || 0;
  document.getElementById('edit-stock-min').value     = prod.stock_minimo || 5;
  document.getElementById('edit-categoria').value     = prod.id_categoria;
  var prev = document.getElementById('editImgPreview');
  prev.src = prod.imagen ? '<?php echo $base; ?>public/img/productos/' + prod.imagen : 'https://placehold.co/100x100/e8f0eb/2d5a3d?text=IMG';
  prev.style.display = 'block';
  openPanel('editar');
}

// Preview imagen
function previewImg(input, previewId, iconId) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var prev = document.getElementById(previewId);
      prev.src = e.target.result; prev.style.display = 'block';
      if (iconId) { var ic = document.getElementById(iconId); if(ic) ic.style.display='none'; }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Validación rentabilidad
function validateRentabilidad() {
  var compra = parseFloat(document.querySelector('[name="precio_compra"]').value) || 0;
  var venta  = parseFloat(document.querySelector('[name="precio_venta"]').value) || 0;
  var msg    = document.getElementById('rentabilidadMsg');
  if (compra > 0 && venta > 0) {
    msg.style.display = 'block';
    if (venta <= compra) {
      msg.style.color = '#c62828';
      msg.textContent = '⚠ El precio de venta debe ser mayor al precio de compra.';
    } else {
      var margen = ((venta - compra) / compra * 100).toFixed(1);
      msg.style.color = '#2e7d32';
      msg.textContent = '✓ Margen de ganancia: ' + margen + '%';
    }
  } else { msg.style.display = 'none'; }
}

// Delete modal
function confirmDelete(id, nombre) {
  document.getElementById('deleteProductName').textContent = nombre;
  document.getElementById('deleteLink').href = '<?php echo $base; ?>controllers/ProductoController.php?action=eliminar&id=' + id;
  document.getElementById('deleteModal').classList.add('active');
}
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('active'); }

// Búsqueda en tiempo real
var allRows = [];
document.addEventListener('DOMContentLoaded', function() {
  allRows = Array.from(document.querySelectorAll('#productTable tbody tr[data-nombre]'));
});
function searchProducts(term) {
  var q = term.toLowerCase().trim();
  var visible = 0;
  allRows.forEach(function(row) {
    var match = q === '' || row.dataset.nombre.includes(q) || (row.dataset.categoria||'').includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  document.getElementById('clearSearch').style.display = term ? 'flex' : 'none';
  document.getElementById('pageTitle').textContent   = q ? 'Búsqueda: "'+term+'"' : 'Catálogo de Productos';
  document.getElementById('pageSubtitle').textContent = q ? visible+' resultados' : allRows.length+' productos';
  var nr = document.getElementById('noResultsRow');
  if (nr) nr.style.display = (visible === 0 && q) ? '' : 'none';
}
function clearSearch() {
  document.getElementById('searchInput').value = '';
  searchProducts('');
  document.getElementById('searchInput').focus();
}
</script>
</body>
</html>
