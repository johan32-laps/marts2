<?php
/**
 * UsuarioController - MARTS
 * CRUD de usuarios con gestión de roles
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Log.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../views/usuarios/login.php"); exit;
}
if (!in_array($_SESSION['rol'] ?? '', ['admin','administrador'])) {
    header("Location: ../views/dashboard/empleado.php"); exit;
}

$modelo = new Usuario();
$log    = new Log();
$action = $_GET['action'] ?? '';
$BACK   = '../views/usuarios/index.php';

switch ($action) {

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $correo   = trim($_POST['correo']   ?? '');
            $password = trim($_POST['password'] ?? '');
            $id_rol   = intval($_POST['id_rol'] ?? 0);

            if (empty($nombre) || empty($correo) || empty($password) || $id_rol <= 0) {
                $_SESSION['error_usr'] = "Todos los campos son obligatorios.";
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_usr'] = "El correo no tiene un formato válido.";
            } elseif (strlen($password) < 6) {
                $_SESSION['error_usr'] = "La contraseña debe tener al menos 6 caracteres.";
            } elseif ($modelo->getByCorreo($correo)) {
                $_SESSION['error_usr'] = "Ya existe un usuario con ese correo.";
            } elseif ($modelo->crear($nombre, $correo, $password, $id_rol)) {
                $log->registrar($_SESSION['id_usuario'], "Creó el usuario: $nombre", "usuario");
                $_SESSION['exito_usr'] = "Usuario \"$nombre\" creado exitosamente.";
            } else {
                $_SESSION['error_usr'] = "Error al crear el usuario.";
            }
        }
        header("Location: $BACK"); exit;

    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id     = intval($_POST['id_usuario'] ?? 0);
            $nombre = trim($_POST['nombre']       ?? '');
            $correo = trim($_POST['correo']       ?? '');
            $id_rol = intval($_POST['id_rol']     ?? 0);

            if ($id <= 0 || empty($nombre) || empty($correo) || $id_rol <= 0) {
                $_SESSION['error_usr'] = "Datos inválidos.";
                header("Location: $BACK"); exit;
            }

            // Verificar que el admin no se quite su propio rol
            $db   = new Database();
            $conn = $db->connect();
            $stmt = $conn->prepare("SELECT nombre FROM rol WHERE id_rol = :id");
            $stmt->execute([':id' => $id_rol]);
            $nombreRol = $stmt->fetchColumn();

            if ($id === intval($_SESSION['id_usuario']) && $nombreRol !== 'admin') {
                $_SESSION['error_usr'] = "No puedes cambiar tu propio rol de administrador.";
                header("Location: $BACK"); exit;
            }

            if ($modelo->actualizar($id, $nombre, $correo, $id_rol)) {
                $log->registrar($_SESSION['id_usuario'], "Editó el usuario ID: $id", "usuario");
                $_SESSION['exito_usr'] = "Usuario actualizado correctamente.";
            } else {
                $_SESSION['error_usr'] = "Error al actualizar el usuario.";
            }
        }
        header("Location: $BACK"); exit;

    case 'desactivar':
        $id = intval($_GET['id'] ?? 0);
        if ($id === intval($_SESSION['id_usuario'])) {
            $_SESSION['error_usr'] = "No puedes desactivar tu propia cuenta.";
        } elseif ($id > 0 && $modelo->eliminar($id)) {
            $log->registrar($_SESSION['id_usuario'], "Desactivó al usuario ID: $id", "usuario");
            $_SESSION['exito_usr'] = "Usuario desactivado.";
        }
        header("Location: $BACK"); exit;

    case 'reactivar':
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0 && $modelo->reactivar($id)) {
            $log->registrar($_SESSION['id_usuario'], "Reactivó al usuario ID: $id", "usuario");
            $_SESSION['exito_usr'] = "Usuario reactivado correctamente.";
        }
        header("Location: $BACK"); exit;

    case 'cambiar_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id       = intval($_POST['id_usuario'] ?? 0);
            $password = trim($_POST['password']     ?? '');

            if ($id <= 0 || strlen($password) < 6) {
                $_SESSION['error_usr'] = "La contraseña debe tener al menos 6 caracteres.";
            } elseif ($modelo->cambiarPassword($id, $password)) {
                $log->registrar($_SESSION['id_usuario'], "Cambió contraseña del usuario ID: $id", "usuario");
                $_SESSION['exito_usr'] = "Contraseña actualizada correctamente.";
            } else {
                $_SESSION['error_usr'] = "Error al actualizar la contraseña.";
            }
        }
        header("Location: $BACK"); exit;

    default:
        header("Location: $BACK"); exit;
}

