<?php
/**
 * ProductoController - MARTS
 * CRUD de productos con todos los campos de bdinventario
 */
session_start();
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}

class ProductoController {
    private Producto $modelo;
    private Log      $log;
    private const REDIRECT = '../views/dashboard/adminproductos.php';
    private const IMG_DIR  = __DIR__ . '/../public/img/productos/';
    private const MAX_SIZE = 5_000_000;
    private const ALLOWED  = ['jpg','jpeg','png','gif','webp'];

    public function __construct() {
        $this->modelo = new Producto();
        $this->log    = new Log();
    }

    public function registrar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . self::REDIRECT); exit;
        }

        $nombre        = trim($_POST['nombre']        ?? '');
        $descripcion   = trim($_POST['descripcion']   ?? '');
        $codigo_barras = trim($_POST['codigo_barras'] ?? '');
        $tamano        = trim($_POST['tamano']        ?? '');
        $precio_compra = (float)($_POST['precio_compra'] ?? 0);
        $precio_venta  = (float)($_POST['precio_venta']  ?? 0);
        $stock         = (int)($_POST['stock']         ?? 0);
        $stock_minimo  = (int)($_POST['stock_minimo']  ?? 5);
        $id_categoria  = (int)($_POST['id_categoria']  ?? 0);

        if (empty($nombre)) {
            $_SESSION['error_producto'] = "El nombre del producto es obligatorio.";
            header('Location: ' . self::REDIRECT); exit;
        }
        if ($precio_venta <= 0) {
            $_SESSION['error_producto'] = "El precio de venta debe ser mayor a cero.";
            header('Location: ' . self::REDIRECT); exit;
        }
        if ($precio_venta <= $precio_compra && $precio_compra > 0) {
            $_SESSION['error_producto'] = "El precio de venta debe ser mayor al precio de compra.";
            header('Location: ' . self::REDIRECT); exit;
        }
        if ($id_categoria <= 0) {
            $_SESSION['error_producto'] = "Selecciona una categoría válida.";
            header('Location: ' . self::REDIRECT); exit;
        }

        $imagen = $this->procesarImagen();
        if ($imagen === false) { header('Location: ' . self::REDIRECT); exit; }

        if ($this->modelo->registrar($nombre, $precio_compra, $precio_venta,
            $stock, $stock_minimo, $id_categoria, $imagen,
            $descripcion, $codigo_barras, $tamano)) {
            $this->log->registrar($_SESSION['id_usuario'],
                "Registró producto: $nombre", "producto",
                "Compra: $precio_compra | Venta: $precio_venta | Stock: $stock");
            $_SESSION['exito_producto'] = "Producto \"$nombre\" registrado exitosamente.";
        } else {
            $_SESSION['error_producto'] = "Error al registrar el producto.";
        }
        header('Location: ' . self::REDIRECT); exit;
    }

    public function editar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . self::REDIRECT); exit;
        }

        $id            = (int)($_POST['id_producto']    ?? 0);
        $nombre        = trim($_POST['nombre']           ?? '');
        $descripcion   = trim($_POST['descripcion']      ?? '');
        $codigo_barras = trim($_POST['codigo_barras']    ?? '');
        $tamano        = trim($_POST['tamano']           ?? '');
        $precio_compra = (float)($_POST['precio_compra'] ?? 0);
        $precio_venta  = (float)($_POST['precio_venta']  ?? 0);
        $stock_minimo  = (int)($_POST['stock_minimo']    ?? 5);
        $id_categoria  = (int)($_POST['id_categoria']    ?? 0);

        if ($id <= 0 || empty($nombre) || $precio_venta <= 0 || $id_categoria <= 0) {
            $_SESSION['error_producto'] = "Datos inválidos. Verifica todos los campos.";
            header('Location: ' . self::REDIRECT); exit;
        }

        $imagen = $this->procesarImagen();
        if ($imagen === false) { header('Location: ' . self::REDIRECT); exit; }

        if ($this->modelo->actualizar($id, $nombre, $precio_compra, $precio_venta,
            $id_categoria, $stock_minimo, $imagen,
            $descripcion, $codigo_barras, $tamano)) {
            $this->log->registrar($_SESSION['id_usuario'],
                "Editó producto: $nombre", "producto", "ID: $id");
            $_SESSION['exito_producto'] = "Producto actualizado correctamente.";
        } else {
            $_SESSION['error_producto'] = "Error al actualizar el producto.";
        }
        header('Location: ' . self::REDIRECT); exit;
    }

    public function eliminar(): void {
        if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
            $_SESSION['error_producto'] = "Sin permisos para eliminar.";
            header('Location: ' . self::REDIRECT); exit;
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { header('Location: ' . self::REDIRECT); exit; }

        if ($this->modelo->eliminar($id)) {
            $this->log->registrar($_SESSION['id_usuario'],
                "Eliminó producto ID: $id", "producto");
            $_SESSION['exito_producto'] = "Producto eliminado correctamente.";
        } else {
            $_SESSION['error_producto'] = "Error al eliminar el producto.";
        }
        header('Location: ' . self::REDIRECT); exit;
    }

    private function procesarImagen(): ?string {
        if (!isset($_FILES['imagen']) ||
            $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['imagen'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_producto'] = "Error al subir la imagen.";
            return false;
        }
        if ($file['size'] > self::MAX_SIZE) {
            $_SESSION['error_producto'] = "La imagen supera los 5 MB.";
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED)) {
            $_SESSION['error_producto'] = "Formato no permitido (jpg, png, webp).";
            return false;
        }

        if (!@getimagesize($file['tmp_name'])) {
            $_SESSION['error_producto'] = "El archivo no es una imagen válida.";
            return false;
        }

        if (!is_dir(self::IMG_DIR)) mkdir(self::IMG_DIR, 0755, true);

        $nombre  = md5(uniqid('', true)) . '.' . $ext;
        $destino = self::IMG_DIR . $nombre;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            $_SESSION['error_producto'] = "No se pudo guardar la imagen.";
            return false;
        }

        return $nombre;
    }
}

// Dispatcher
$action     = $_GET['action'] ?? '';
$controller = new ProductoController();

match ($action) {
    'registrar' => $controller->registrar(),
    'editar'    => $controller->editar(),
    'eliminar'  => $controller->eliminar(),
    default     => header('Location: ../views/dashboard/adminproductos.php')
};
exit;
