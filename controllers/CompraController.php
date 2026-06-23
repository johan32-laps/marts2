<?php
session_start();
require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) { header("Location: ../views/usuarios/login.php"); exit; }
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
    header("Location: ../views/dashboard/empleado.php"); exit;
}

$modelo = new Compra();
$log    = new Log();
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/compras/index.php"); exit; }

        $proveedor    = trim($_POST['proveedor']    ?? '');
        $observaciones= trim($_POST['observaciones'] ?? '');
        $productos    = $_POST['productos']    ?? [];
        $cantidades   = $_POST['cantidades']   ?? [];
        $precios      = $_POST['precios']      ?? [];

        if (empty($productos)) {
            $_SESSION['error_compra'] = "Debes agregar al menos un producto.";
            header("Location: ../views/compras/index.php"); exit;
        }

        $items = [];
        foreach ($productos as $i => $id_prod) {
            if (empty($id_prod) || empty($cantidades[$i]) || $cantidades[$i] <= 0) continue;
            $items[] = [
                'id_producto'   => (int)$id_prod,
                'cantidad'      => (int)$cantidades[$i],
                'precio_compra' => (float)($precios[$i] ?? 0),
            ];
        }

        $result = $modelo->registrar($_SESSION['id_usuario'], $proveedor, $items, $observaciones);

        if ($result) {
            $log->registrar($_SESSION['id_usuario'], "Registró compra #$result", "compra", "Proveedor: $proveedor");
            $_SESSION['exito_compra'] = "Compra #$result registrada. Stock actualizado.";
        } else {
            $_SESSION['error_compra'] = "Error al registrar la compra.";
        }
        header("Location: ../views/compras/index.php"); exit;

    default:
        header("Location: ../views/compras/index.php"); exit;
}
