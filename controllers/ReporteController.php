<?php
/**
 * ReporteController - MARTS
 * Exportación de reportes en CSV y PDF (impresión)
 */
session_start();
require_once __DIR__ . '/../models/Movimiento.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}

$modelo = new Movimiento();
$action = $_GET['action'] ?? '';

$fecha_inicio = !empty($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin    = !empty($_GET['fecha_fin'])    ? $_GET['fecha_fin']    : null;
$id_tipo      = !empty($_GET['id_tipo'])      ? $_GET['id_tipo']      : null;
$id_producto  = !empty($_GET['id_producto'])  ? $_GET['id_producto']  : null;

$datos = $modelo->obtenerReporte($fecha_inicio, $fecha_fin, $id_tipo, $id_producto);

switch ($action) {

    case 'excel':
        // Exportar como CSV compatible con Excel
        $filename = 'reporte_marts_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $output = fopen('php://output', 'w');
        // BOM para UTF-8 en Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeceras
        fputcsv($output, ['Fecha', 'Producto', 'Tipo de Movimiento', 'Operación', 'Cantidad', 'Motivo', 'Operador']);

        foreach ($datos as $fila) {
            fputcsv($output, [
                date('d/m/Y H:i', strtotime($fila['fecha'])),
                $fila['producto_nombre'],
                $fila['tipo_nombre'] ?? ucfirst($fila['tipo']),
                ucfirst($fila['tipo']),
                $fila['cantidad'],
                $fila['motivo'] ?? '',
                $fila['usuario_nombre'] ?? '',
            ]);
        }

        fclose($output);
        exit;

    case 'pdf':
        // Cargar vista de impresión (abre en nueva pestaña para imprimir/guardar PDF)
        require_once __DIR__ . '/../views/reportes/imprimir.php';
        exit;

    default:
        header("Location: ../views/reportes/index.php");
        exit;
}

