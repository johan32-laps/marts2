<?php
session_start();
require_once __DIR__ . '/../models/Caja.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) { header("Location: ../views/usuarios/login.php"); exit; }

$modelo = new Caja();
$log    = new Log();
$action = $_GET['action'] ?? '';

switch ($action) {

    case 'abrir':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/caja/index.php"); exit; }
        $saldo_inicial = (float)($_POST['saldo_inicial'] ?? 0);
        if ($saldo_inicial < 0) {
            $_SESSION['error_caja'] = "El saldo inicial no puede ser negativo.";
            header("Location: ../views/caja/index.php"); exit;
        }
        if ($modelo->getCajaAbierta()) {
            $_SESSION['error_caja'] = "Ya existe una caja abierta. Ciérrala antes de abrir una nueva.";
            header("Location: ../views/caja/index.php"); exit;
        }
        if ($modelo->abrir($_SESSION['id_usuario'], $saldo_inicial)) {
            $log->registrar($_SESSION['id_usuario'], "Abrió caja con saldo inicial: $$saldo_inicial", "caja");
            $_SESSION['exito_caja'] = "Caja abierta correctamente con saldo inicial de $$saldo_inicial.";
        } else {
            $_SESSION['error_caja'] = "Error al abrir la caja.";
        }
        header("Location: ../views/caja/index.php"); exit;

    case 'cerrar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/caja/index.php"); exit; }
        $id_caja      = (int)($_POST['id_caja']      ?? 0);
        $saldo_final  = (float)($_POST['saldo_final'] ?? 0);
        $justificacion= trim($_POST['justificacion']  ?? '');

        $cajaAbierta = $modelo->getCajaAbierta();
        if (!$cajaAbierta || $cajaAbierta['id_caja'] != $id_caja) {
            $_SESSION['error_caja'] = "No se encontró la caja activa.";
            header("Location: ../views/caja/index.php"); exit;
        }

        // Calcular diferencia para exigir justificación
        $diferencia = $saldo_final - $cajaAbierta['saldo_teorico'];
        if (abs($diferencia) > 0.01 && empty($justificacion)) {
            $_SESSION['error_caja'] = "Existe una diferencia de $$diferencia. Debes ingresar una justificación.";
            header("Location: ../views/caja/index.php"); exit;
        }

        if ($modelo->cerrar($id_caja, $saldo_final, $justificacion)) {
            $log->registrar($_SESSION['id_usuario'], "Cerró caja #$id_caja. Diferencia: $$diferencia", "caja");
            $_SESSION['exito_caja'] = "Caja cerrada. Diferencia: $$diferencia.";
        } else {
            $_SESSION['error_caja'] = "Error al cerrar la caja.";
        }
        header("Location: ../views/caja/index.php"); exit;

    case 'movimiento':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../views/caja/index.php"); exit; }
        $id_caja  = (int)($_POST['id_caja']  ?? 0);
        $tipo     = $_POST['tipo']     ?? '';
        $monto    = (float)($_POST['monto']   ?? 0);
        $concepto = trim($_POST['concepto']   ?? '');

        if (!in_array($tipo, ['ingreso','egreso']) || $monto <= 0 || empty($concepto)) {
            $_SESSION['error_caja'] = "Datos inválidos para el movimiento.";
            header("Location: ../views/caja/index.php"); exit;
        }

        if ($modelo->registrarMovimiento($id_caja, $tipo, $monto, $concepto)) {
            $log->registrar($_SESSION['id_usuario'], "Movimiento de caja: $tipo $$monto", "caja", $concepto);
            $_SESSION['exito_caja'] = "Movimiento registrado: $tipo de $$monto.";
        } else {
            $_SESSION['error_caja'] = "Error al registrar el movimiento.";
        }
        header("Location: ../views/caja/index.php"); exit;

    default:
        header("Location: ../views/caja/index.php"); exit;
}
