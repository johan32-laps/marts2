<?php
require_once __DIR__ . '/../config/database.php';

class Caja {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /** Obtiene la caja actualmente abierta, o false si no hay */
    public function getCajaAbierta() {
        return $this->conn->query(
            "SELECT c.*, u.nombre AS usuario_nombre
             FROM caja c LEFT JOIN usuario u ON c.id_usuario=u.id_usuario
             WHERE c.estado='abierta' LIMIT 1"
        )->fetch();
    }

    /** Abre una nueva caja */
    public function abrir(int $id_usuario, float $saldo_inicial): bool {
        // Verificar que no haya caja abierta
        if ($this->getCajaAbierta()) return false;
        $stmt = $this->conn->prepare(
            "INSERT INTO caja (id_usuario, saldo_inicial, saldo_teorico, estado, fecha_apertura)
             VALUES (:u, :si, :st, 'abierta', NOW())"
        );
        return $stmt->execute([
            ':u'  => $id_usuario,
            ':si' => $saldo_inicial,
            ':st' => $saldo_inicial,
        ]);
    }

    /** Cierra la caja activa */
    public function cerrar(int $id_caja, float $saldo_final, string $justificacion = ''): bool {
        $caja = $this->conn->prepare("SELECT saldo_teorico FROM caja WHERE id_caja=:id");
        $caja->execute([':id' => $id_caja]);
        $row = $caja->fetch();
        if (!$row) return false;

        $diferencia = $saldo_final - $row['saldo_teorico'];
        $stmt = $this->conn->prepare(
            "UPDATE caja SET saldo_final=:sf, diferencia=:dif,
             justificacion=:just, estado='cerrada', fecha_cierre=NOW()
             WHERE id_caja=:id"
        );
        return $stmt->execute([
            ':sf'   => $saldo_final,
            ':dif'  => $diferencia,
            ':just' => $justificacion,
            ':id'   => $id_caja,
        ]);
    }

    /** Registra un movimiento manual de caja */
    public function registrarMovimiento(int $id_caja, string $tipo,
                                        float $monto, string $concepto,
                                        ?int $id_venta = null): bool {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare(
                "INSERT INTO movimiento_caja (id_caja,tipo,monto,concepto,id_venta,fecha)
                 VALUES (:c,:t,:m,:con,:v,NOW())"
            );
            $stmt->execute([':c'=>$id_caja,':t'=>$tipo,':m'=>$monto,
                            ':con'=>$concepto,':v'=>$id_venta]);

            $op = $tipo === 'ingreso' ? '+' : '-';
            $this->conn->prepare(
                "UPDATE caja SET saldo_teorico=saldo_teorico $op :m WHERE id_caja=:id"
            )->execute([':m'=>$monto, ':id'=>$id_caja]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /** Historial de movimientos de una caja */
    public function movimientos(int $id_caja): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM movimiento_caja WHERE id_caja=:id ORDER BY fecha DESC"
        );
        $stmt->execute([':id' => $id_caja]);
        return $stmt->fetchAll();
    }

    /** Historial de cajas */
    public function historial(int $limite = 20): array {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nombre AS usuario_nombre
             FROM caja c LEFT JOIN usuario u ON c.id_usuario=u.id_usuario
             ORDER BY c.fecha_apertura DESC LIMIT :l"
        );
        $stmt->bindValue(':l', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
