<?php
require_once __DIR__ . '/../config/database.php';

class Compra {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Registra una compra y actualiza el stock.
     * $items = [['id_producto'=>X,'cantidad'=>Y,'precio_compra'=>Z], ...]
     */
    public function registrar(int $id_usuario, string $proveedor,
                               array $items, string $observaciones = ''): int|false {
        try {
            $this->conn->beginTransaction();

            $total = array_reduce($items, fn($c,$i) => $c + ($i['cantidad']*$i['precio_compra']), 0);

            $stmt = $this->conn->prepare(
                "INSERT INTO compra (id_usuario,proveedor,total,observaciones,fecha)
                 VALUES (:u,:p,:t,:obs,NOW())"
            );
            $stmt->execute([':u'=>$id_usuario,':p'=>$proveedor,':t'=>$total,':obs'=>$observaciones]);
            $id_compra = (int)$this->conn->lastInsertId();

            $stmtDet = $this->conn->prepare(
                "INSERT INTO detalle_compra (id_compra,id_producto,cantidad,precio_compra,subtotal)
                 VALUES (:c,:p,:cant,:pc,:s)"
            );

            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_compra'];
                $stmtDet->execute([':c'=>$id_compra,':p'=>$item['id_producto'],
                                   ':cant'=>$item['cantidad'],':pc'=>$item['precio_compra'],
                                   ':s'=>$subtotal]);

                // Actualizar stock
                $this->conn->prepare(
                    "UPDATE producto SET stock=stock+:c WHERE id_producto=:p"
                )->execute([':c'=>$item['cantidad'],':p'=>$item['id_producto']]);

                // Movimiento inventario (tipo 1 = Compra de Mercancía)
                $this->conn->prepare(
                    "INSERT INTO movimiento (id_producto,id_tipo_movimiento,id_usuario,tipo,cantidad,motivo,fecha)
                     VALUES (:p,1,:u,'entrada',:c,:mot,NOW())"
                )->execute([':p'=>$item['id_producto'],':u'=>$id_usuario,
                            ':c'=>$item['cantidad'],':mot'=>"Compra #$id_compra"]);
            }

            $this->conn->commit();
            return $id_compra;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Compra::registrar — '.$e->getMessage());
            return false;
        }
    }

    public function listar(int $limite = 50): array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre AS usuario_nombre
             FROM compra c LEFT JOIN usuario u ON c.id_usuario=u.id_usuario
             ORDER BY c.fecha DESC LIMIT :l"
        );
        $stmt->bindValue(':l', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre AS usuario_nombre
             FROM compra c LEFT JOIN usuario u ON c.id_usuario=u.id_usuario
             WHERE c.id_compra=:id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getDetalle(int $id_compra): array {
        $stmt = $this->conn->prepare(
            "SELECT dc.*, p.nombre AS producto_nombre
             FROM detalle_compra dc JOIN producto p ON dc.id_producto=p.id_producto
             WHERE dc.id_compra=:id"
        );
        $stmt->execute([':id' => $id_compra]);
        return $stmt->fetchAll();
    }

    public function reportePorPeriodo(string $desde, string $hasta): array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre AS usuario_nombre
             FROM compra c LEFT JOIN usuario u ON c.id_usuario=u.id_usuario
             WHERE DATE(c.fecha) BETWEEN :d AND :h ORDER BY c.fecha DESC"
        );
        $stmt->execute([':d'=>$desde,':h'=>$hasta]);
        return $stmt->fetchAll();
    }
}
