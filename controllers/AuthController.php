<?php
/**
 * AuthController - MARTS
 * BD: bdinventario — columna password (no contraseña)
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
    private PDO $conn;

    public function __construct()
    {
        $db         = new Database();
        $this->conn = $db->connect();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $correo   = trim($_POST['correo']   ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($correo) || empty($password)) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "El formato del correo no es válido.";
            header("Location: ../views/usuarios/login.php");
            exit;
        }

        // Buscar usuario activo con su rol
        $stmt = $this->conn->prepare(
            "SELECT u.*, r.nombre AS rol_nombre
             FROM usuario u
             LEFT JOIN rol r ON u.id_rol = r.id_rol
             WHERE u.correo = :correo AND u.estado = 1
             LIMIT 1"
        );
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['usuario']    = $user['nombre'];
            $_SESSION['rol']        = $user['rol_nombre'] ?? 'empleado';

            $rol = $_SESSION['rol'];
            if (in_array($rol, ['admin', 'administrador'])) {
                header("Location: ../views/dashboard/admin.php");
            } else {
                header("Location: ../views/dashboard/empleado.php");
            }
            exit;

        } else {
            $_SESSION['error'] = "Correo o contraseña incorrectos.";
            header("Location: ../views/usuarios/login.php");
            exit;
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
        header("Location: ../views/usuarios/login.php");
        exit;
    }
}

// Dispatcher
$auth   = new AuthController();
$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    $auth->logout();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
} else {
    header("Location: ../views/usuarios/login.php");
    exit;
}
