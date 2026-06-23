<?php
/**
 * CategoriaController - MARTS
 */
session_start();
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
    header("Location: ../views/dashboard/empleado.php"); exit;
}

$modelo  = new Categoria();
$log     = new Log();
$action  = $_GET['action'] ?? '';
$BACK    = '../views/categorias/index.php';

switch ($action) {

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if (empty($nombre)) {
                $_SESSION['error_cat'] = "El nombre es obligatorio.";
            } elseif ($modelo->existeNombre($nombre)) {
                $_SESSION['error_cat'] = "Ya existe una categoría con ese nombre.";
            } elseif ($modelo->crear($nombre, $descripcion)) {
                $log->registrar($_SESSION['id_usuario'], "Creó la categoría: $nombre", "categoria");
                $_SESSION['exito_cat'] = "Categoría \"$nombre\" creada exitosamente.";
            } else {
                $_SESSION['error_cat'] = "Error al crear la categoría.";
            }
        }
        header("Location: $BACK"); exit;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id          = intval($_POST['id_categoria'] ?? 0);
            $nombre      = trim($_POST['nombre']         ?? '');
            $descripcion = trim($_POST['descripcion']    ?? '');

            if ($id <= 0 || empty($nombre)) {
                $_SESSION['error_cat'] = "Datos inválidos.";
            } elseif ($modelo->existeNombre($nombre, $id)) {
                $_SESSION['error_cat'] = "Ya existe otra categoría con ese nombre.";
            } elseif ($modelo->actualizar($id, $nombre, $descripcion)) {
                $log->registrar($_SESSION['id_usuario'], "Editó la categoría: $nombre", "categoria", "ID: $id");
                $_SESSION['exito_cat'] = "Categoría actualizada correctamente.";
            } else {
                $_SESSION['error_cat'] = "Error al actualizar la categoría.";
            }
        }
        header("Location: $BACK"); exit;

    case 'eliminar':
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $resultado = $modelo->eliminar($id);
            if (isset($resultado['error'])) {
                $_SESSION['error_cat'] = $resultado['error'];
            } else {
                $log->registrar($_SESSION['id_usuario'], "Eliminó categoría ID: $id", "categoria");
                $_SESSION['exito_cat'] = "Categoría eliminada correctamente.";
            }
        }
        header("Location: $BACK"); exit;

    default:
        header("Location: $BACK"); exit;
}

