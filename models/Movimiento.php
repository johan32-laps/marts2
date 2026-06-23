<?php
/**
 * Modelo Movimiento - MARTS
 * BD: bdinventario — tabla movimiento
 * Campos extra: stock_anterior, stock_nuevo
 */
require_once __DIR__ . '/../config/database.php';

class Movimiento
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /** Obtiene todos los tipos de movimiento */
    public function obtenerTipos(): array
    {
        return $this->conn->query(
            "SELECT * FROM tipo_movimiento ORDER BY operacion ASC, nombre ASC"
        )->fetchAll();
    }

    /** Lista movimientos recientes con detalle */
    public function listarMovimientos(int $limite = 50): array
    {
        $sql  = "SELECT m.*,
                        p.nombre  AS producto_nombre,
                        u.nombre  AS usuario_nombre,
                        tm.nombre AS tipo_nombre,
                        COALESCE(tm.operacion, m.tipo) AS operacion
                 FROM movimiento m
                 JOIN producto p ON m.id_producto = p.id_producto
                 LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
                 LEFT JOIN tipo_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
                 ORDER BY m.fecha DESC
                 LIMIT :limite";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Registra un movimiento y actualiza stock */
    public function registrar(
        int $id_producto,
        int $id_tipo_movimiento,
        int $cantidad,
        string $motivo,
        int $id_usuario,
        array $detalles = []
    ): bool {
        try {
            $this->conn->beginTransaction();

            // Obtener operación del tipo
            $stmtTipo = $this->conn->prepare(
                "SELECT operacion FROM tipo_movimiento WHERE id_tipo_movimiento = :id"
            );
            $stmtTipo->execute([':id' => $id_tipo_movimiento]);
            $tipoData = $stmtTipo->fetch();
            if (!$tipoData) throw new Exception("Tipo de movimiento no válido.");
            $operacion = $tipoData['operacion'];

            // Stock actual
            $stmtStock = $this->conn->prepare("SELECT stock FROM producto WHERE id_producto = :id");
            $stmtStock->execute([':id' => $id_producto]);
            $stockActual = (int)$stmtStock->fetchColumn();
            $stockNuevo  = $operacion === 'entrada'
                ? $stockActual + $cantidad
                : $stockActual - $cantidad;

            // Insertar movimiento con stock_anterior y stock_nuevo
            $sql  = "INSERT INTO movimiento
                       (id_producto, id_tipo_movimiento, id_usuario, tipo, cantidad,
                        stock_anterior, stock_nuevo, motivo, fecha)
                     VALUES
                       (:id_prod, :id_tm, :id_usr, :tipo, :cant,
                        :stock_ant, :stock_nvo, :motivo, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':id_prod'   => $id_producto,
                ':id_tm'     => $id_tipo_movimiento,
                ':id_usr'    => $id_usuario,
                ':tipo'      => $operacion,
                ':cant'      => $cantidad,
                ':stock_ant' => $stockActual,
                ':stock_nvo' => $stockNuevo,
                ':motivo'    => $motivo,
            ]);
            $id_movimiento = $this->conn->lastInsertId();

            // Detalles adicionales
            if (!empty(array_filter($detalles))) {
                $this->conn->prepare(
                    "INSERT INTO detalle_movimiento
                       (id_movimiento, comentarios_tecnicos, ubicacion_almacen, referencia_externa)
                     VALUES (:id_m, :com, :ubi, :ref)"
                )->execute([
                    ':id_m' => $id_movimiento,
                    ':com'  => $detalles['comentarios'] ?? null,
                    ':ubi'  => $detalles['ubicacion']   ?? null,
                    ':ref'  => $detalles['referencia']  ?? null,
                ]);
            }

            // Actualizar stock
            $op  = $operacion === 'entrada' ? '+' : '-';
            $this->conn->prepare(
                "UPDATE producto SET stock = stock $op :cant WHERE id_producto = :id"
            )->execute([':cant' => $cantidad, ':id' => $id_producto]);

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Movimiento::registrar — ' . $e->getMessage());
            return false;
        }
    }

    /** Reporte filtrado */
    public function obtenerReporte(
        ?string $fecha_inicio,
        ?string $fecha_fin,
        ?string $id_tipo,
        ?string $id_producto
    ): array {
        $sql    = "SELECT m.*,
                          p.nombre  AS producto_nombre,
                          u.nombre  AS usuario_nombre,
                          tm.nombre AS tipo_nombre,
                          COALESCE(tm.operacion, m.tipo) AS tipo
                   FROM movimiento m
                   LEFT JOIN producto p ON m.id_producto = p.id_producto
                   LEFT JOIN usuario u ON m.id_usuario = u.id_usuario
                   LEFT JOIN tipo_movimiento tm ON m.id_tipo_movimiento = tm.id_tipo_movimiento
                   WHERE 1=1";
        $params = [];

        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(m.fecha) >= :fi";
            $params[':fi'] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(m.fecha) <= :ff";
            $params[':ff'] = $fecha_fin;
        }
        if (!empty($id_tipo)) {
            $sql .= " AND m.id_tipo_movimiento = :it";
            $params[':it'] = $id_tipo;
        }
        if (!empty($id_producto)) {
            $sql .= " AND m.id_producto = :ip";
            $params[':ip'] = (int)$id_producto;
        }

        $sql .= " ORDER BY m.fecha DESC";
        $stmt  = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Resumen semanal para gráficas */
    public function obtenerResumenSemanal(): array
    {
        $sql  = "SELECT
                   DATE(fecha) AS dia,
                   SUM(CASE WHEN tipo='entrada' THEN cantidad ELSE 0 END) AS entradas,
                   SUM(CASE WHEN tipo='salida'  THEN cantidad ELSE 0 END) AS salidas
                 FROM movimiento
                 WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(fecha)
                 ORDER BY dia ASC";
        return $this->conn->query($sql)->fetchAll();
    }
}
