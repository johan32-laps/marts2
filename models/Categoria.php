<?php
require_once __DIR__ . '/../config/database.php';

class Categoria
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
        $this->verificarEstructura();
    }

    private function verificarEstructura()
    {
        try {
            $check = $this->conn->query("SHOW COLUMNS FROM categoria LIKE 'descripcion'");
            if ($check->rowCount() === 0) {
                $this->conn->exec("ALTER TABLE categoria ADD COLUMN descripcion TEXT NULL DEFAULT NULL AFTER nombre");
            }
        } catch (PDOException $e) {
            // Ignorar silenciosamente
        }
    }

    // 📋 LISTAR TODAS CON CONTEO DE PRODUCTOS
    public function listar()
    {
        $query = "SELECT c.*, COUNT(p.id_producto) as total_productos
                  FROM categoria c
                  LEFT JOIN producto p ON c.id_categoria = p.id_categoria
                  GROUP BY c.id_categoria
                  ORDER BY c.nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔍 OBTENER POR ID
    public function getById($id)
    {
        $query = "SELECT * FROM categoria WHERE id_categoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ➕ CREAR
    public function crear($nombre, $descripcion = '')
    {
        $query = "INSERT INTO categoria (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    // ✏️ ACTUALIZAR
    public function actualizar($id, $nombre, $descripcion = '')
    {
        $query = "UPDATE categoria SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    // ❌ ELIMINAR (solo si no tiene productos)
    public function eliminar($id)
    {
        // Verificar si tiene productos asociados
        $check = $this->conn->prepare("SELECT COUNT(*) as total FROM producto WHERE id_categoria = :id");
        $check->bindParam(':id', $id, PDO::PARAM_INT);
        $check->execute();
        $result = $check->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            return ['error' => 'No se puede eliminar: la categoría tiene ' . $result['total'] . ' producto(s) asociado(s).'];
        }

        $query = "DELETE FROM categoria WHERE id_categoria = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return ['success' => true];
    }

    // 📊 CONTEO TOTAL
    public function contar()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM categoria");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // 🔍 VERIFICAR SI NOMBRE YA EXISTE
    public function existeNombre($nombre, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as total FROM categoria WHERE nombre = :nombre";
        if ($excludeId) {
            $query .= " AND id_categoria != :id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        if ($excludeId) {
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    }
}
