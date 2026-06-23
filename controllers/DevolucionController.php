<?php
session_start();
require_once __DIR__ . '/../models/Devolucion.php';
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) { header("Location: ../views/usuarios/login.php"); exit; }
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
    header("Location: ../views/dashboard/empleado.php"); exit;
}

$modelo = new Devolucion();
$venta  = new Venta();
$log    = new Log();
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../views/devoluciones/index.php"); exit;
        }

        $id_venta  = (int)($_POST['id_venta']  ?? 0);
        $motivo    = trim($_POST['motivo']      ?? '');
        $productos = $_POST['productos']        ?? [];
        $cantidades= $_POST['cantidades']       ?? [];
        $precios   = $_POST['precios']          ?? [];

        if ($id_venta <= 0) {
            $_SESSION['error_dev'] = "Selecciona una venta válida.";
            header("Location: ../views/devoluciones/index.php"); exit;
        }
        if (empty($motivo)) {
            $_SESSION['error_dev'] = "El motivo de la devolución es obligatorio.";
            header("Location: ../views/devoluciones/index.php"); exit;
        }
        if (empty($productos)) {
            $_SESSION['error_dev'] = "Debes seleccionar al menos un producto.";
            header("Location: ../views/devoluciones/index.php"); exit;
        }

        $items = [];
        foreach ($productos as $i => $id_prod) {
            if (empty($id_prod) || empty($cantidades[$i]) || $cantidades[$i] <= 0) continue;
            $items[] = [
                'id_producto'    => (int)$id_prod,
                'cantidad'       => (int)$cantidades[$i],
                'precio_unitario'=> (float)($precios[$i] ?? 0),
            ];
        }

        $result = $modelo->registrar($id_venta, $_SESSION['id_usuario'], $motivo, $items);

        if ($result) {
            $log->registrar($_SESSION['id_usuario'], "Registró devolución #$result", "devolucion", "Venta #$id_venta");
            $_SESSION['exito_dev'] = "Devolución #$result registrada. Inventario actualizado.";
        } else {
            $_SESSION['error_dev'] = "Error al registrar la devolución. Verifica las cantidades.";
        }
        header("Location: ../views/devoluciones/index.php"); exit;

    default:
        header("Location: ../views/devoluciones/index.php"); exit;
}
