<?php
session_start();
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Caja.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) { header("Location: ../views/usuarios/login.php"); exit; }

$modelo = new Venta();
$caja   = new Caja();
$log    = new Log();
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'registrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/ventas/index.php"); exit; }

        $metodo_pago  = $_POST['metodo_pago']  ?? 'efectivo';
        $observaciones= trim($_POST['observaciones'] ?? '');
        $productos    = $_POST['productos']    ?? [];
        $cantidades   = $_POST['cantidades']   ?? [];
        $precios      = $_POST['precios']      ?? [];

        if (empty($productos)) {
            $_SESSION['error_venta'] = "Debes agregar al menos un producto.";
            header("Location: ../views/ventas/index.php"); exit;
        }

        // Verificar caja abierta si es efectivo
        $cajaAbierta = $caja->getCajaAbierta();
        if ($metodo_pago === 'efectivo' && !$cajaAbierta) {
            $_SESSION['error_venta'] = "Debe haber una caja abierta para ventas en efectivo.";
            header("Location: ../views/ventas/index.php"); exit;
        }

        $items = [];
        foreach ($productos as $i => $id_prod) {
            if (empty($id_prod) || empty($cantidades[$i]) || $cantidades[$i] <= 0) continue;
            $items[] = [
                'id_producto'  => (int)$id_prod,
                'cantidad'     => (int)$cantidades[$i],
                'precio_venta' => (float)($precios[$i] ?? 0),
            ];
        }

        $id_caja = $cajaAbierta ? $cajaAbierta['id_caja'] : null;
        $result  = $modelo->registrar($_SESSION['id_usuario'], $id_caja, $metodo_pago, $items, $observaciones);

        if ($result) {
            $log->registrar($_SESSION['id_usuario'], "Registró venta #$result", "venta", "Total: $".array_reduce($items,fn($c,$i)=>$c+$i['cantidad']*$i['precio_venta'],0));
            $_SESSION['exito_venta'] = "Venta #$result registrada exitosamente.";
        } else {
            $_SESSION['error_venta'] = "Error al registrar la venta. Verifica el stock disponible.";
        }
        header("Location: ../views/ventas/index.php"); exit;

    default:
        header("Location: ../views/ventas/index.php"); exit;
}
