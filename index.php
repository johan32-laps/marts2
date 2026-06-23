<?php
/**
 * MARTS - Punto de entrada raíz
 * http://localhost/marts2/
 */
session_start();

if (isset($_SESSION['id_usuario'])) {
    $rol = $_SESSION['rol'] ?? '';
    if (in_array($rol, ['admin', 'administrador'])) {
        header("Location: views/dashboard/admin.php");
    } else {
        header("Location: views/dashboard/empleado.php");
    }
    exit;
}

header("Location: views/usuarios/login.php");
exit;
