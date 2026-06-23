<?php
/**
 * Modelo Devolucion - MARTS
 */
require_once __DIR__ . '/../config/database.php';

class Devolucion {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Registra una devolución.
     * $items = [['id_producto'=>X,'cantidad'=>Y,'precio_unitario'=>Z], ...]
     */
    public function registrar(int $id_venta, int $id_usuario,
                               string $motivo, array $items): int|false {
        try {
            $this->conn->beginTransaction();

            // Validar que la venta existe
            $venta = $this->conn->prepare("SELECT * FROM venta WHERE id_venta=:id AND estado='completada'");
            $venta->execute([':id' => $id_venta]);
            $ventaData = $venta->fetch();
            if (!$ventaData) throw new Exception("Venta no válida para devolución.");

            // Validar cantidades contra detalle original
            foreach ($items as $item) {
                $det = $this->conn->prepare(
                    "SELECT cantidad FROM detalle_venta WHERE id_venta=:v AND id_producto=:p"
                );
                $det->execute([':v'=>$id_venta,':p'=>$item['id_producto']]);
                $orig = $det->fetch();
                if (!$orig || $item['cantidad'] > $orig['cantidad']) {
                    throw new Exception("Cantidad inválida para producto ID: {$item['id_producto']}");
                }
            }

            // Calcular total devolución
            $total = array_reduce($items, fn($c,$i) => $c + ($i['cantidad']*$i['precio_unitario']), 0);

            // Insertar devolución
            $stmt = $this->conn->prepare(
                "INSERT INTO devolucion (id_venta,id_usuario,motivo,total_devolucion,fecha)
                 VALUES (:v,:u,:m,:t,NOW())"
            );
            $stmt->execute([':v'=>$id_venta,':u'=>$id_usuario,':m'=>$motivo,':t'=>$total]);
            $id_dev = (int)$this->conn->lastInsertId();

            // Insertar detalle + reintegrar stock + movimiento
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];

                $this->conn->prepare(
                    "INSERT INTO detalle_devolucion (id_devolucion,id_producto,cantidad,precio_unitario,subtotal)
                     VALUES (:d,:p,:c,:pu,:s)"
                )->execute([':d'=>$id_dev,':p'=>$item['id_producto'],
                            ':c'=>$item['cantidad'],':pu'=>$item['precio_unitario'],':s'=>$subtotal]);

                // Reintegrar stock
                $this->conn->prepare(
                    "UPDATE producto SET stock=stock+:c WHERE id_producto=:p"
                )->execute([':c'=>$item['cantidad'],':p'=>$item['id_producto']]);

                // Movimiento inventario (tipo 2 = Devolución de Cliente)
                $this->conn->prepare(
                    "INSERT INTO movimiento (id_producto,id_tipo_movimiento,id_usuario,tipo,cantidad,motivo,fecha)
                     VALUES (:p,2,:u,'entrada',:c,:mot,NOW())"
                )->execute([':p'=>$item['id_producto'],':u'=>$id_usuario,
                            ':c'=>$item['cantidad'],':mot'=>"Devolución #$id_dev"]);
            }

            // Egreso en caja si la venta fue en efectivo
            if ($ventaData['metodo_pago'] === 'efectivo' && $ventaData['id_caja']) {
                $this->conn->prepare(
                    "INSERT INTO movimiento_caja (id_caja,tipo,monto,concepto,id_devolucion,fecha)
                     VALUES (:cj,'egreso',:t,:con,:d,NOW())"
                )->execute([':cj'=>$ventaData['id_caja'],':t'=>$total,
                            ':con'=>"Devolución #$id_dev",':d'=>$id_dev]);
                $this->conn->prepare(
                    "UPDATE caja SET saldo_teorico=saldo_teorico-:t WHERE id_caja=:id"
                )->execute([':t'=>$total,':id'=>$ventaData['id_caja']]);
            }

            $this->conn->commit();
            return $id_dev;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Devolucion::registrar — '.$e->getMessage());
            return false;
        }
    }

    public function listar(int $limite = 50): array {
        $stmt = $this->conn->prepare(
            "SELECT d.*, u.nombre AS usuario_nombre, v.total AS venta_total
             FROM devolucion d
             LEFT JOIN usuario u ON d.id_usuario=u.id_usuario
             LEFT JOIN venta v ON d.id_venta=v.id_venta
             ORDER BY d.fecha DESC LIMIT :l"
        );
        $stmt->bindValue(':l', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->conn->prepare(
            "SELECT d.*, u.nombre AS usuario_nombre
             FROM devolucion d
             LEFT JOIN usuario u ON d.id_usuario=u.id_usuario
             WHERE d.id_devolucion=:id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getDetalle(int $id_dev): array {
        $stmt = $this->conn->prepare(
            "SELECT dd.*, p.nombre AS producto_nombre
             FROM detalle_devolucion dd
             JOIN producto p ON dd.id_producto=p.id_producto
             WHERE dd.id_devolucion=:id"
        );
        $stmt->execute([':id' => $id_dev]);
        return $stmt->fetchAll();
    }
}
