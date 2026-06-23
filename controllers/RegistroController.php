<?php
/**
 * RegistroController - MARTS
 * Maneja el registro de nuevos usuarios
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/usuarios/registro.php");
    exit;
}

$nombre           = trim($_POST['nombre']           ?? '');
$apellido         = trim($_POST['apellido']         ?? '');
$correo           = trim($_POST['correo']           ?? '');
$telefono         = trim($_POST['telefono']         ?? '');
$password         = trim($_POST['password']         ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$id_rol           = intval($_POST['id_rol']         ?? 0);

// Validaciones
if (empty($nombre)) {
    $_SESSION['error_reg'] = "El nombre es obligatorio.";
    header("Location: ../views/usuarios/registro.php"); exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_reg'] = "El correo no tiene un formato válido.";
    header("Location: ../views/usuarios/registro.php"); exit;
}
if (strlen($password) < 6) {
    $_SESSION['error_reg'] = "La contraseña debe tener al menos 6 caracteres.";
    header("Location: ../views/usuarios/registro.php"); exit;
}
if ($password !== $confirm_password) {
    $_SESSION['error_reg'] = "Las contraseñas no coinciden.";
    header("Location: ../views/usuarios/registro.php"); exit;
}
if ($id_rol <= 0) {
    $_SESSION['error_reg'] = "Selecciona un tipo de cuenta.";
    header("Location: ../views/usuarios/registro.php"); exit;
}

$modelo = new Usuario();

// Verificar correo único
if ($modelo->getByCorreo($correo)) {
    $_SESSION['error_reg'] = "Ya existe una cuenta con ese correo electrónico.";
    header("Location: ../views/usuarios/registro.php"); exit;
}

// Crear usuario — incluir apellido y teléfono si existen en la BD
try {
    $db   = new Database();
    $conn = $db->connect();

    // Verificar si la tabla tiene columnas apellido y telefono
    $cols = $conn->query("SHOW COLUMNS FROM usuario")->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('apellido', $cols) && in_array('telefono', $cols)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO usuario (nombre, apellido, correo, telefono, password, id_rol, estado, created_at)
             VALUES (:nombre, :apellido, :correo, :telefono, :password, :id_rol, 1, NOW())"
        );
        $stmt->execute([
            ':nombre'   => $nombre,
            ':apellido' => $apellido,
            ':correo'   => $correo,
            ':telefono' => $telefono,
            ':password' => $hash,
            ':id_rol'   => $id_rol,
        ]);
    } else {
        // Fallback sin apellido/telefono
        $modelo->crear($nombre, $correo, $password, $id_rol);
    }

    // Login automático después del registro
    session_regenerate_id(true);
    $user = $modelo->getByCorreo($correo);
    if ($user) {
        // Obtener nombre del rol
        $stmtRol = $conn->prepare("SELECT nombre FROM rol WHERE id_rol = :id");
        $stmtRol->execute([':id' => $user['id_rol']]);
        $rolNombre = $stmtRol->fetchColumn() ?: 'empleado';

        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['usuario']    = $user['nombre'];
        $_SESSION['rol']        = $rolNombre;

        $_SESSION['exito_movimiento'] = "Bienvenido, $nombre. Tu cuenta fue creada exitosamente.";

        if (in_array($rolNombre, ['admin', 'administrador'])) {
            header("Location: ../views/dashboard/admin.php");
        } else {
            header("Location: ../views/dashboard/empleado.php");
        }
        exit;
    }

} catch (PDOException $e) {
    error_log('RegistroController: ' . $e->getMessage());
    $_SESSION['error_reg'] = "Error al crear la cuenta. Intenta de nuevo.";
    header("Location: ../views/usuarios/registro.php"); exit;
}

$_SESSION['exito_reg'] = "Cuenta creada. Ahora puedes iniciar sesión.";
header("Location: ../views/usuarios/login.php");
exit;
