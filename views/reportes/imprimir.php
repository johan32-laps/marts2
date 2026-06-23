<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reporte de Inventario — MARTS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --blue: #2563eb;
      --green: #059669;
      --red: #dc2626;
      --gray: #64748b;
      --dark: #0f172a;
      --border: #e2e8f0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
      color: #1e293b;
      padding: 2rem;
    }
    .container {
      max-width: 1100px;
      margin: 0 auto;
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    /* Header del reporte */
    .report-header {
      background: var(--dark);
      color: white;
      padding: 2.5rem 3rem;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
    }
    .report-logo {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .report-logo-icon {
      width: 52px; height: 52px;
      background: linear-gradient(135deg, #3b82f6, #8b5cf6);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
    }
    .report-logo-icon svg { width: 26px; height: 26px; }
    .report-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.75rem; font-weight: 800; }
    .report-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 0.375rem; }
    .report-brand { text-align: right; }
    .report-brand h2 { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.25rem; font-weight: 800; color: #60a5fa; }
    .report-brand p { font-size: 0.7rem; color: #64748b; margin-top: 0.25rem; text-transform: uppercase; letter-spacing: 0.1em; }
    /* Stats */
    .report-stats {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0;
      border-bottom: 1px solid var(--border);
    }
    .stat-box {
      padding: 1.5rem 2rem;
      border-right: 1px solid var(--border);
    }
    .stat-box:last-child { border-right: none; }
    .stat-box-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--gray); margin-bottom: 0.5rem; }
    .stat-box-value { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 2rem; font-weight: 800; }
    .stat-box-sub { font-size: 0.7rem; color: var(--gray); margin-top: 0.25rem; }
    /* Tabla */
    .report-table-wrap { padding: 0 2rem 2rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
    thead tr { background: var(--dark); }
    th {
      padding: 0.875rem 1rem;
      text-align: left;
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94a3b8;
    }
    td {
      padding: 0.875rem 1rem;
      font-size: 0.8rem;
      border-bottom: 1px solid var(--border);
      color: #334155;
    }
    tbody tr:hover td { background: #f8fafc; }
    tbody tr:last-child td { border-bottom: none; }
    .pill {
      display: inline-flex; align-items: center; gap: 0.3rem;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      font-size: 0.65rem; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.05em;
    }
    .pill-green { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
    .pill-red   { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
    /* Footer */
    .report-footer {
      padding: 1.5rem 2rem;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f8fafc;
    }
    .report-footer p { font-size: 0.7rem; color: var(--gray); }
    /* Firmas */
    .signatures {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      padding: 3rem 4rem 2rem;
    }
    .signature-line {
      border-top: 2px solid var(--border);
      padding-top: 0.75rem;
      text-align: center;
    }
    .signature-line p { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--dark); }
    .signature-line span { font-size: 0.65rem; color: var(--gray); }
    /* Controles de impresión */
    .print-controls {
      position: fixed;
      bottom: 2rem; right: 2rem;
      display: flex; flex-direction: column; gap: 0.75rem;
    }
    .print-btn {
      display: flex; align-items: center; gap: 0.75rem;
      background: var(--dark); color: white;
      padding: 0.875rem 1.5rem;
      border-radius: 12px;
      border: none; cursor: pointer;
      font-size: 0.875rem; font-weight: 700;
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
      transition: all 0.2s;
      font-family: inherit;
    }
    .print-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }
    .back-btn {
      background: #1e293b;
      font-size: 0.8rem;
      padding: 0.625rem 1.25rem;
    }
    @media print {
      body { background: white; padding: 0; }
      .container { box-shadow: none; border-radius: 0; }
      .print-controls { display: none; }
    }
  </style>
</head>
<body>

<div class="container">

  <!-- Header -->
  <div class="report-header">
    <div class="report-logo">
      <div class="report-logo-icon">
        <svg fill="none" stroke="white" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
      </div>
      <div>
        <h1 class="report-title">Reporte Analítico</h1>
        <p class="report-meta">
          ID: MARTS-<?php echo date('Ymd-His'); ?> &nbsp;&bull;&nbsp;
          Generado: <?php echo date('d/m/Y H:i'); ?>
        </p>
      </div>
    </div>
    <div class="report-brand">
      <h2>MARTS</h2>
      <p>Inventory Management System</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="report-stats">
    <div class="stat-box">
      <p class="stat-box-label">Total Movimientos</p>
      <p class="stat-box-value"><?php echo count($datos); ?></p>
      <p class="stat-box-sub">registros procesados</p>
    </div>
    <div class="stat-box">
      <p class="stat-box-label">Entradas Totales</p>
      <p class="stat-box-value" style="color:var(--green)">
        +<?php
        $te = 0;
        foreach($datos as $d) if($d['tipo'] === 'entrada') $te += $d['cantidad'];
        echo number_format($te);
        ?>
      </p>
      <p class="stat-box-sub">unidades ingresadas</p>
    </div>
    <div class="stat-box">
      <p class="stat-box-label">Salidas Totales</p>
      <p class="stat-box-value" style="color:var(--red)">
        -<?php
        $ts = 0;
        foreach($datos as $d) if($d['tipo'] === 'salida') $ts += $d['cantidad'];
        echo number_format($ts);
        ?>
      </p>
      <p class="stat-box-sub">unidades egresadas</p>
    </div>
  </div>

  <!-- Tabla -->
  <div class="report-table-wrap">
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Producto</th>
          <th>Clasificación</th>
          <th style="text-align:center">Cantidad</th>
          <th>Motivo</th>
          <th>Operador</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($datos as $fila): ?>
        <tr>
          <td style="white-space:nowrap"><?php echo date('d/m/Y', strtotime($fila['fecha'])); ?></td>
          <td>
            <strong><?php echo htmlspecialchars($fila['producto_nombre']); ?></strong>
            <br><span style="font-size:0.65rem;color:#94a3b8">ID: <?php echo str_pad($fila['id_producto'],6,'0',STR_PAD_LEFT); ?></span>
          </td>
          <td>
            <span class="pill <?php echo $fila['tipo'] === 'entrada' ? 'pill-green' : 'pill-red'; ?>">
              <span class="dot"></span>
              <?php echo htmlspecialchars($fila['tipo_nombre'] ?? ucfirst($fila['tipo'])); ?>
            </span>
          </td>
          <td style="text-align:center;font-weight:700;color:<?php echo $fila['tipo'] === 'entrada' ? 'var(--green)' : 'var(--red)'; ?>">
            <?php echo ($fila['tipo'] === 'entrada' ? '+' : '-') . number_format($fila['cantidad']); ?>
          </td>
          <td style="color:#64748b;font-style:italic"><?php echo htmlspecialchars($fila['motivo'] ?: 'N/A'); ?></td>
          <td style="font-weight:500"><?php echo htmlspecialchars($fila['usuario_nombre'] ?? '—'); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Firmas -->
  <div class="signatures">
    <div class="signature-line">
      <p>Firma del Responsable</p>
      <span>Control de Almacén</span>
    </div>
    <div class="signature-line">
      <p>Firma de Auditoría</p>
      <span>Gerencia Administrativa</span>
    </div>
  </div>

  <!-- Footer -->
  <div class="report-footer">
    <p>Documento oficial generado por MARTS Inventory Management System</p>
    <p>&copy; <?php echo date('Y'); ?> MARTS v2.1 &mdash; <?php echo md5(date('Y-m-d H:i:s')); ?></p>
  </div>

</div>

<!-- Controles -->
<div class="print-controls no-print">
  <button class="print-btn" onclick="window.print()">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Imprimir / PDF
  </button>
  <button class="print-btn back-btn" onclick="window.close()">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
    </svg>
    Cerrar
  </button>
</div>

</body>
</html>
