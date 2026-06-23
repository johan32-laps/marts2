<?php
/**
 * Modelo Log - Gestiona el historial de auditoría del sistema.
 * Registra acciones importantes como creación, edición y eliminación de datos.
 */
require_once __DIR__ . '/../config/database.php';

class Log {
    private $conn; // Conexión a la base de datos

    /**
     * Constructor: Inicializa la conexión y asegura que la tabla de logs exista.
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->crearTablaSiNoExiste();
    }

    /**
     * Crea la tabla 'log' en la base de datos si aún no ha sido creada.
     * Define la estructura para almacenar usuario, acción, entidad y fecha.
     */
    private function crearTablaSiNoExiste() {
        $query = "CREATE TABLE IF NOT EXISTS log (
            id_log INT AUTO_INCREMENT PRIMARY KEY, -- Identificador único del log
            id_usuario INT,                         -- ID del usuario que realizó la acción
            accion VARCHAR(255) NOT NULL,           -- Descripción de la acción (ej. 'Creó producto')
            entidad VARCHAR(50) NOT NULL,            -- Tipo de dato afectado (producto, usuario, etc.)
            detalles TEXT,                          -- Información adicional o cambios realizados
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora automática
            FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE SET NULL -- Mantiene integridad
        )";
        $this->conn->exec($query);
    }

    /**
     * Registra una nueva entrada en el historial.
     * @param int $id_usuario ID del autor de la acción.
     * @param string $accion Descripción breve.
     * @param string $entidad Área afectada.
     * @param string $detalles (Opcional) Datos extras.
     */
    public function registrar($id_usuario, $accion, $entidad, $detalles = '') {
        $query = "INSERT INTO log (id_usuario, accion, entidad, detalles) VALUES (:id_u, :acc, :ent, :det)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id_u' => $id_usuario,
            ':acc' => $accion,
            ':ent' => $entidad,
            ':det' => $detalles
        ]);
    }

    /**
     * Obtiene todos los registros del historial, uniendo con la tabla de usuarios para mostrar nombres.
     * Antes de listar, ejecuta la limpieza de registros antiguos.
     */
    public function listar() {
        // Ejecuta la política de retención de datos (7 días)
        $this->limpiarAntiguos();

        $query = "SELECT l.*, u.nombre as usuario_nombre 
                  FROM log l 
                  LEFT JOIN usuario u ON l.id_usuario = u.id_usuario 
                  ORDER BY l.fecha DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Elimina automáticamente los registros que tengan más de una semana de antigüedad.
     * Optimiza el espacio en disco y la memoria del servidor.
     */
    public function limpiarAntiguos() {
        $query = "DELETE FROM log WHERE fecha < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        return $this->conn->exec($query);
    }
}
