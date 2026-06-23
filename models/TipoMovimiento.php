<?php
require_once __DIR__ . '/../config/database.php';

class TipoMovimiento {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getTipos() {
        $query = "SELECT * FROM tipo_movimiento ORDER BY operacion ASC, nombre ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $operacion, $contexto) {
        $query = "INSERT INTO tipo_movimiento (nombre, operacion, contexto) VALUES (:nombre, :operacion, :contexto)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':nombre' => $nombre,
            ':operacion' => $operacion,
            ':contexto' => $contexto
        ]);
    }

    public function editar($id, $nombre, $operacion, $contexto) {
        $query = "UPDATE tipo_movimiento SET nombre = :nombre, operacion = :operacion, contexto = :contexto WHERE id_tipo_movimiento = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':operacion' => $operacion,
            ':contexto' => $contexto
        ]);
    }

    public function eliminar($id) {
        // Verificar si está en uso antes de eliminar
        $check = $this->db->prepare("SELECT COUNT(*) FROM movimiento WHERE id_tipo_movimiento = :id");
        $check->execute([':id' => $id]);
        if ($check->fetchColumn() > 0) {
            return false; // Está en uso
        }

        $query = "DELETE FROM tipo_movimiento WHERE id_tipo_movimiento = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
