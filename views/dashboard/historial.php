<?php
/**
 * Historial de Actividad - MARTS
 */
session_start();
if (!isset($_SESSION['id_usuario'])) { header("Location: ../usuarios/login.php"); exit; }
if ($_SESSION['rol'] !== 'admin' && $_SESSION['rol'] !== 'administrador') {
    header("Location: ../dashboard/empleado.php"); exit;
}

require_once __DIR__ . '/../../models/Log.php';
$logModel = new Log();
$logs     = $logModel->listar();

// Colores por entidad
$entityColors = [
    'producto'   => 'pill-blue',
    'usuario'    => 'pill-purple',
    'categoria'  => 'pill-cyan',
    'movimiento' => 'pill-green',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial | MARTS</title>
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
          <h1 class="page-title">Historial de Actividad</h1>
          <p class="page-subtitle">Registro de auditoría — últimos 7 días</p>
        </div>
        <span class="pill pill-blue"><?php echo count($logs); ?> registros</span>
      </div>

      <div class="dark-table-wrap animate-fade-in-up">
        <div style="overflow-x:auto">
          <table class="dark-table">
            <thead>
              <tr>
                <th>Fecha y Hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Entidad</th>
                <th class="hide-mobile">Detalles</th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($logs) > 0): ?>
                <?php foreach($logs as $l): ?>
                <tr>
                  <td style="white-space:nowrap;font-size:0.8rem">
                    <?php echo date('d/m/Y H:i:s', strtotime($l['fecha'])); ?>
                  </td>
                  <td>
                    <div style="display:flex;align-items:center;gap:0.625rem">
                      <div style="width:28px;height:28px;border-radius:6px;background:linear-gradient(135deg,var(--green-dark),#7b1fa2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;color:white;flex-shrink:0">
                        <?php echo strtoupper(mb_substr($l['usuario_nombre'] ?? 'S', 0, 1)); ?>
                      </div>
                      <span style="font-weight:500;color:var(--text-dark)">
                        <?php echo htmlspecialchars($l['usuario_nombre'] ?? 'Sistema'); ?>
                      </span>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($l['accion']); ?></td>
                  <td>
                    <?php
                    $entidad = strtolower($l['entidad']);
                    $pillClass = $entityColors[$entidad] ?? 'pill-gray';
                    ?>
                    <span class="pill <?php echo $pillClass; ?>" style="font-size:0.65rem">
                      <?php echo htmlspecialchars($l['entidad']); ?>
                    </span>
                  </td>
                  <td style="font-size:0.8rem;color:var(--text-muted)" class="hide-mobile">
                    <?php echo htmlspecialchars($l['detalles'] ?: '—'); ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5">
                    <div class="empty-state">
                      <div class="empty-state-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </div>
                      <p class="empty-state-title">Sin actividad reciente</p>
                      <p class="empty-state-text">No hay registros en los últimos 7 días.</p>
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

</body>
</html>
