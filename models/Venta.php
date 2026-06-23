<?php
/**
 * Modelo Venta - MARTS
 * BD: bdinventario
 */
require_once __DIR__ . '/../config/database.php';

class Venta {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Registra una venta completa con su detalle.
     * $items = [['id_producto'=>X, 'cantidad'=>Y, 'precio_venta'=>Z], ...]
     */
    public function registrar(int $id_usuario, ?int $id_caja,
                               string $metodo_pago, array $items,
                               string $observaciones = ''): int|false {
        try {
            $this->conn->beginTransaction();

            // Validar stock antes de proceder
            foreach ($items as $item) {
                $stock = $this->conn->prepare("SELECT stock FROM producto WHERE id_producto=:p AND estado=1");
                $stock->execute([':p' => $item['id_producto']]);
                $row = $stock->fetch();
                if (!$row || $row['stock'] < $item['cantidad']) {
                    throw new Exception("Stock insuficiente para producto ID: {$item['id_producto']}");
                }
            }

            // Calcular total
            $total = array_reduce($items, fn($c,$i) => $c + ($i['cantidad'] * $i['precio_venta']), 0);

            // Insertar venta
            $stmt = $this->conn->prepare(
                "INSERT INTO venta (id_usuario,id_caja,metodo_pago,total,estado,observaciones,fecha)
                 VALUES (:u,:cj,:mp,:t,'completada',:obs,NOW())"
            );
            $stmt->execute([':u'=>$id_usuario,':cj'=>$id_caja,
                            ':mp'=>$metodo_pago,':t'=>$total,':obs'=>$observaciones]);
            $id_venta = (int)$this->conn->lastInsertId();

            // Insertar detalle + actualizar stock + registrar movimiento
            foreach ($items as $item) {
                $subtotal = $item['cantidad'] * $item['precio_venta'];

                // Detalle venta
                $this->conn->prepare(
                    "INSERT INTO detalle_venta (id_venta,id_producto,cantidad,precio_venta,subtotal)
                     VALUES (:v,:p,:c,:pv,:s)"
                )->execute([':v'=>$id_venta,':p'=>$item['id_producto'],
                            ':c'=>$item['cantidad'],':pv'=>$item['precio_venta'],':s'=>$subtotal]);

                // Actualizar stock
                $this->conn->prepare(
                    "UPDATE producto SET stock=stock-:c WHERE id_producto=:p"
                )->execute([':c'=>$item['cantidad'],':p'=>$item['id_producto']]);

                // Movimiento inventario (tipo 4 = Venta Directa)
                $this->conn->prepare(
                    "INSERT INTO movimiento (id_producto,id_tipo_movimiento,id_usuario,tipo,cantidad,motivo,fecha)
                     VALUES (:p,4,:u,'salida',:c,:mot,NOW())"
                )->execute([':p'=>$item['id_producto'],':u'=>$id_usuario,
                            ':c'=>$item['cantidad'],':mot'=>"Venta #$id_venta"]);
            }

            // Registrar ingreso en caja si es efectivo
            if ($metodo_pago === 'efectivo' && $id_caja) {
                $this->conn->prepare(
                    "INSERT INTO movimiento_caja (id_caja,tipo,monto,concepto,id_venta,fecha)
                     VALUES (:cj,'ingreso',:t,:con,:v,NOW())"
                )->execute([':cj'=>$id_caja,':t'=>$total,
                            ':con'=>"Venta #$id_venta",':v'=>$id_venta]);
                $this->conn->prepare(
                    "UPDATE caja SET saldo_teorico=saldo_teorico+:t WHERE id_caja=:id"
                )->execute([':t'=>$total,':id'=>$id_caja]);
            }

            $this->conn->commit();
            return $id_venta;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log('Venta::registrar — '.$e->getMessage());
            return false;
        }
    }

    public function listar(int $limite = 50): array {
        $stmt = $this->conn->prepare(
            "SELECT v.*, u.nombre AS usuario_nombre
             FROM venta v
             LEFT JOIN usuario u ON v.id_usuario=u.id_usuario
             ORDER BY v.fecha DESC LIMIT :l"
        );
        $stmt->bindValue(':l', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id) {
        $stmt = $this->conn->prepare(
            "SELECT v.*, u.nombre AS usuario_nombre
             FROM venta v
             LEFT JOIN usuario u ON v.id_usuario=u.id_usuario
             WHERE v.id_venta=:id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getDetalle(int $id_venta): array {
        $stmt = $this->conn->prepare(
            "SELECT dv.*, p.nombre AS producto_nombre, p.imagen
             FROM detalle_venta dv
             JOIN producto p ON dv.id_producto=p.id_producto
             WHERE dv.id_venta=:id"
        );
        $stmt->execute([':id' => $id_venta]);
        return $stmt->fetchAll();
    }

    public function totalHoy(): float {
        return (float)$this->conn->query(
            "SELECT COALESCE(SUM(total),0) FROM venta WHERE DATE(fecha)=CURDATE() AND estado='completada'"
        )->fetchColumn();
    }

    public function contarHoy(): int {
        return (int)$this->conn->query(
            "SELECT COUNT(*) FROM venta WHERE DATE(fecha)=CURDATE() AND estado='completada'"
        )->fetchColumn();
    }

    public function reportePorPeriodo(string $desde, string $hasta): array {
        $stmt = $this->conn->prepare(
            "SELECT v.*, u.nombre AS usuario_nombre
             FROM venta v
             LEFT JOIN usuario u ON v.id_usuario=u.id_usuario
             WHERE DATE(v.fecha) BETWEEN :d AND :h AND v.estado='completada'
             ORDER BY v.fecha DESC"
        );
        $stmt->execute([':d'=>$desde,':h'=>$hasta]);
        return $stmt->fetchAll();
    }
}
