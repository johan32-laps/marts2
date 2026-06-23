<?php
/**
 * Modelo Usuario - MARTS
 * BD: bdinventario — tabla usuario
 * Columna contraseña: password
 */
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /** Login: verifica correo + password */
    public function login(string $correo, string $password)
    {
        $sql  = "SELECT u.*, r.nombre AS rol_nombre
                 FROM usuario u
                 LEFT JOIN rol r ON u.id_rol = r.id_rol
                 WHERE u.correo = :correo AND u.estado = 1
                 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $user['rol'] = $user['rol_nombre'] ?? 'empleado';
            return $user;
        }
        return false;
    }

    /** Lista todos los usuarios con su rol */
    public function getUsuarios(): array
    {
        $sql  = "SELECT u.*, r.nombre AS rol_nombre
                 FROM usuario u
                 LEFT JOIN rol r ON u.id_rol = r.id_rol
                 ORDER BY u.estado DESC, u.nombre ASC";
        return $this->conn->query($sql)->fetchAll();
    }

    /** Obtiene todos los roles */
    public function getRoles(): array
    {
        return $this->conn->query("SELECT * FROM rol ORDER BY nombre ASC")->fetchAll();
    }

    /** Busca usuario por ID */
    public function getById(int $id)
    {
        $sql  = "SELECT u.*, r.nombre AS rol_nombre
                 FROM usuario u
                 LEFT JOIN rol r ON u.id_rol = r.id_rol
                 WHERE u.id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Busca usuario por correo */
    public function getByCorreo(string $correo)
    {
        $sql  = "SELECT * FROM usuario WHERE correo = :correo";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        return $stmt->fetch();
    }

    /** Crea un nuevo usuario */
    public function crear(string $nombre, string $correo, string $password, int $id_rol): bool
    {
        $sql  = "INSERT INTO usuario (nombre, correo, password, id_rol, estado, created_at)
                 VALUES (:nombre, :correo, :password, :id_rol, 1, NOW())";
        $stmt = $this->conn->prepare($sql);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':nombre',   $nombre);
        $stmt->bindParam(':correo',   $correo);
        $stmt->bindParam(':password', $hash);
        $stmt->bindParam(':id_rol',   $id_rol, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Actualiza datos básicos */
    public function actualizar(int $id, string $nombre, string $correo, int $id_rol): bool
    {
        $sql  = "UPDATE usuario SET nombre=:nombre, correo=:correo, id_rol=:id_rol WHERE id_usuario=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id',     $id,     PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':id_rol', $id_rol, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Cambia contraseña */
    public function cambiarPassword(int $id, string $nuevaPassword): bool
    {
        $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
        $sql  = "UPDATE usuario SET password=:hash WHERE id_usuario=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':id',   $id,   PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Desactiva usuario (borrado lógico) */
    public function eliminar(int $id): bool
    {
        $sql  = "UPDATE usuario SET estado=0 WHERE id_usuario=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Reactiva usuario */
    public function reactivar(int $id): bool
    {
        $sql  = "UPDATE usuario SET estado=1 WHERE id_usuario=:id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /** Cuenta usuarios activos */
    public function contarActivos(): int
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM usuario WHERE estado=1")->fetchColumn();
    }
}
