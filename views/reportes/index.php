<?php
/**
 * Reportes de Inventario - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }

require_once __DIR__ . '/../../models/Movimiento.php';
require_once __DIR__ . '/../../models/Producto.php';

$movimientoModel = new Movimiento();
$productoModel   = new Producto();

$productos       = $productoModel->listarProductos();
$tiposMovimiento = $movimientoModel->obtenerTipos();

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin    = $_GET['fecha_fin']    ?? '';
$id_tipo      = $_GET['id_tipo']      ?? '';
$id_producto  = $_GET['id_producto']  ?? '';

$datos            = [];
$mostrar          = false;
$totalEntradas    = 0;
$totalSalidas     = 0;

if (isset($_GET['filtrar'])) {
    $datos   = $movimientoModel->obtenerReporte($fecha_inicio, $fecha_fin, $id_tipo, $id_producto);
    $mostrar = true;
    foreach ($datos as $d) {
        if ($d['tipo'] === 'entrada') $totalEntradas += $d['cantidad'];
        else $totalSalidas += $d['cantidad'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reportes | MARTS</title>
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
          <h1 class="page-title">Reportes de Inventario</h1>
          <p class="page-subtitle">Genera reportes detallados con filtros avanzados</p>
        </div>
        <?php if($mostrar && count($datos) > 0): ?>
        <div style="display:flex;gap:0.75rem">
          <a href="/controllers/ReporteController.php?action=excel&<?php echo http_build_query(['fecha_inicio'=>$fecha_inicio,'fecha_fin'=>$fecha_fin,'id_tipo'=>$id_tipo,'id_producto'=>$id_producto,'filtrar'=>1]); ?>"
             class="btn btn-success">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exportar CSV
          </a>
          <a href="/controllers/ReporteController.php?action=pdf&<?php echo http_build_query(['fecha_inicio'=>$fecha_inicio,'fecha_fin'=>$fecha_fin,'id_tipo'=>$id_tipo,'id_producto'=>$id_producto,'filtrar'=>1]); ?>"
             target="_blank" class="btn btn-secondary">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Ver PDF
          </a>
        </div>
        <?php endif; ?>
      </div>

      <!-- Filtros -->
      <div class="glass-card" style="padding:1.5rem;margin-bottom:1.5rem">
        <form method="GET">
          <input type="hidden" name="filtrar" value="1">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;align-items:end">
            <div class="form-group" style="margin:0">
              <label class="form-label">Desde</label>
              <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="form-input">
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Hasta</label>
              <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" class="form-input">
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Tipo de Movimiento</label>
              <select name="id_tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <?php foreach($tiposMovimiento as $tm): ?>
                  <option value="<?php echo $tm['id_tipo_movimiento']; ?>" <?php echo $id_tipo == $tm['id_tipo_movimiento'] ? 'selected' : ''; ?>>
                    <?php echo ($tm['operacion'] === 'entrada' ? '↑ ' : '↓ ') . htmlspecialchars($tm['nombre']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin:0">
              <label class="form-label">Producto</label>
              <select name="id_producto" class="form-select">
                <option value="">Todos los productos</option>
                <?php foreach($productos as $p): ?>
                  <option value="<?php echo $p['id_producto']; ?>" <?php echo $id_producto == $p['id_producto'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p['nombre']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="display:flex;gap:0.5rem">
              <button type="submit" class="btn btn-primary" style="flex:1">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtrar
              </button>
              <?php if($mostrar): ?>
              <a href="index.php" class="btn btn-secondary">Limpiar</a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>

      <?php if($mostrar): ?>

        <!-- Resumen -->
        <?php if(count($datos) > 0): ?>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
          <div class="stat-card animate-fade-in-up">
            <div>
              <p class="stat-card-label">Total Registros</p>
              <p class="stat-card-value"><?php echo count($datos); ?></p>
            </div>
            <div class="stat-card-icon blue">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
          </div>
          <div class="stat-card animate-fade-in-up delay-100">
            <div>
              <p class="stat-card-label">Entradas</p>
              <p class="stat-card-value" style="color:#2e7d32">+<?php echo number_format($totalEntradas); ?></p>
            </div>
            <div class="stat-card-icon green">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
              </svg>
            </div>
          </div>
          <div class="stat-card animate-fade-in-up delay-200">
            <div>
              <p class="stat-card-label">Salidas</p>
              <p class="stat-card-value" style="color:#c62828">-<?php echo number_format($totalSalidas); ?></p>
            </div>
            <div class="stat-card-icon red">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
              </svg>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Tabla resultados -->
        <div class="dark-table-wrap animate-fade-in-up">
          <div style="overflow-x:auto">
            <table class="dark-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Producto</th>
                  <th>Tipo</th>
                  <th style="text-align:center">Cantidad</th>
                  <th>Motivo</th>
                  <th>Operador</th>
                </tr>
              </thead>
              <tbody>
                <?php if(count($datos) > 0): ?>
                  <?php foreach($datos as $fila): ?>
                  <tr>
                    <td style="white-space:nowrap;font-size:0.8rem"><?php echo date('d/m/Y', strtotime($fila['fecha'])); ?></td>
                    <td style="color:var(--text-dark);font-weight:500"><?php echo htmlspecialchars($fila['producto_nombre']); ?></td>
                    <td>
                      <span class="pill <?php echo $fila['tipo'] === 'entrada' ? 'pill-green' : 'pill-red'; ?>">
                        <span class="pill-dot"></span>
                        <?php echo htmlspecialchars($fila['tipo_nombre'] ?? ucfirst($fila['tipo'])); ?>
                      </span>
                    </td>
                    <td style="text-align:center;font-weight:700;color:<?php echo $fila['tipo'] === 'entrada' ? '#2e7d32' : '#c62828'; ?>">
                      <?php echo ($fila['tipo'] === 'entrada' ? '+' : '-') . $fila['cantidad']; ?>
                    </td>
                    <td><?php echo htmlspecialchars($fila['motivo'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($fila['usuario_nombre'] ?? '—'); ?></td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6">
                      <div class="empty-state">
                        <div class="empty-state-icon">
                          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                        </div>
                        <p class="empty-state-title">Sin resultados</p>
                        <p class="empty-state-text">No se encontraron registros con los filtros seleccionados.</p>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <?php else: ?>

        <!-- Estado inicial -->
        <div class="dark-table-wrap" style="border:2px dashed var(--border)">
          <div class="empty-state" style="padding:5rem 2rem">
            <div class="empty-state-icon" style="width:80px;height:80px">
              <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <p class="empty-state-title">Configura tu reporte</p>
            <p class="empty-state-text">Selecciona los filtros en el panel superior y presiona <strong>Filtrar</strong> para visualizar los datos.</p>
          </div>
        </div>

      <?php endif; ?>

    </div>
    <?php include '../layouts/footer.php'; ?>
  </div>
</div>

</body>
</html>
