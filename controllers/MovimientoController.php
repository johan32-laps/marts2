<?php
/**
 * MovimientoController - MARTS
 * Registra entradas y salidas de inventario con validación de stock
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Movimiento.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php");
    exit;
}

class MovimientoController
{
    private $modelo;
    private $productoModel;
    private $log;

    public function __construct()
    {
        $this->modelo        = new Movimiento();
        $this->productoModel = new Producto();
        $this->log           = new Log();
    }

    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('movimientos');
            return;
        }

        $id_producto        = intval($_POST['id_producto']        ?? 0);
        $id_tipo_movimiento = intval($_POST['id_tipo_movimiento'] ?? 0);
        $cantidad           = intval($_POST['cantidad']           ?? 0);
        $motivo             = trim($_POST['motivo']               ?? '');
        $id_usuario         = intval($_SESSION['id_usuario']);
        $redirect           = $_POST['redirect'] ?? 'movimientos';

        // Validación básica
        if ($id_producto <= 0 || $id_tipo_movimiento <= 0 || $cantidad <= 0) {
            $_SESSION['error_movimiento'] = "Producto, tipo y cantidad son obligatorios.";
            $this->redirigir($redirect);
            return;
        }

        // Obtener operación del tipo seleccionado
        $db   = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare("SELECT operacion, nombre FROM tipo_movimiento WHERE id_tipo_movimiento = :id");
        $stmt->execute([':id' => $id_tipo_movimiento]);
        $tipoData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipoData) {
            $_SESSION['error_movimiento'] = "Tipo de movimiento no válido.";
            $this->redirigir($redirect);
            return;
        }

        $operacion  = $tipoData['operacion'];
        $nombreTipo = $tipoData['nombre'];

        // Verificar stock suficiente para salidas
        if ($operacion === 'salida') {
            $producto = $this->productoModel->obtenerPorId($id_producto);
            if (!$producto) {
                $_SESSION['error_movimiento'] = "Producto no encontrado.";
                $this->redirigir($redirect);
                return;
            }
            if ($producto['stock'] < $cantidad) {
                $_SESSION['error_movimiento'] = "Stock insuficiente. Disponible: {$producto['stock']} unidades.";
                $this->redirigir($redirect);
                return;
            }
        }

        // Detalles adicionales opcionales
        $detalles = [
            'comentarios' => trim($_POST['comentarios'] ?? ''),
            'ubicacion'   => trim($_POST['ubicacion']   ?? ''),
            'referencia'  => trim($_POST['referencia']  ?? ''),
        ];

        if ($this->modelo->registrar($id_producto, $id_tipo_movimiento, $cantidad, $motivo, $id_usuario, $detalles)) {
            $this->log->registrar(
                $id_usuario,
                "Registró movimiento: $nombreTipo",
                "movimiento",
                "Producto ID: $id_producto, Cantidad: $cantidad"
            );
            $_SESSION['exito_movimiento'] = "Movimiento \"$nombreTipo\" registrado exitosamente.";
        } else {
            $_SESSION['error_movimiento'] = "Error al registrar el movimiento.";
        }

        $this->redirigir($redirect);
    }

    private function redirigir(string $destino): void
    {
        $isAdmin = in_array($_SESSION['rol'] ?? '', ['admin','administrador']);

        $rutas = [
            'dashboard'   => $isAdmin
                ? '../views/dashboard/admin.php'
                : '../views/dashboard/empleado.php',
            'movimientos' => '../views/movimientos/index.php',
        ];

        $url = $rutas[$destino] ?? $rutas['movimientos'];
        header("Location: $url");
        exit;
    }
}

// ── Dispatcher ───────────────────────────────────────────────
$action     = $_GET['action'] ?? '';
$controller = new MovimientoController();

if ($action === 'registrar') {
    $controller->registrar();
} else {
    header("Location: ../views/movimientos/index.php");
    exit;
}

