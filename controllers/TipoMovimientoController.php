<?php
/**
 * TipoMovimientoController - MARTS
 */
session_start();
require_once __DIR__ . '/../models/TipoMovimiento.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
    header("Location: ../views/dashboard/empleado.php"); exit;
}

$modelo = new TipoMovimiento();
$action = $_GET['action'] ?? '';
$BACK   = '../views/tipos/index.php';

switch ($action) {

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre    = trim($_POST['nombre']    ?? '');
            $operacion = trim($_POST['operacion'] ?? '');
            $contexto  = trim($_POST['contexto']  ?? '');

            if (empty($nombre) || !in_array($operacion, ['entrada','salida'])) {
                $_SESSION['error_tipo'] = "Nombre y operación son obligatorios.";
            } elseif ($modelo->crear($nombre, $operacion, $contexto)) {
                $_SESSION['exito_tipo'] = "Clasificación \"$nombre\" creada correctamente.";
            } else {
                $_SESSION['error_tipo'] = "Error al crear la clasificación.";
            }
        }
        header("Location: $BACK"); exit;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id        = intval($_POST['id_tipo_movimiento'] ?? 0);
            $nombre    = trim($_POST['nombre']               ?? '');
            $operacion = trim($_POST['operacion']            ?? '');
            $contexto  = trim($_POST['contexto']             ?? '');

            if ($id <= 0 || empty($nombre) || !in_array($operacion, ['entrada','salida'])) {
                $_SESSION['error_tipo'] = "Datos inválidos.";
            } elseif ($modelo->editar($id, $nombre, $operacion, $contexto)) {
                $_SESSION['exito_tipo'] = "Clasificación actualizada correctamente.";
            } else {
                $_SESSION['error_tipo'] = "Error al actualizar la clasificación.";
            }
        }
        header("Location: $BACK"); exit;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            if ($modelo->eliminar($id)) {
                $_SESSION['exito_tipo'] = "Clasificación eliminada correctamente.";
            } else {
                $_SESSION['error_tipo'] = "No se puede eliminar: ya fue utilizada en transacciones.";
            }
        }
        header("Location: $BACK"); exit;

    default:
        header("Location: $BACK"); exit;
}

