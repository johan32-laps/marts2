<?php
/**
 * Modelo Producto - MARTS
 * BD: bdinventario
 * Campos: nombre, descripcion, codigo_barras, precio, precio_compra,
 *         precio_venta, stock, stock_minimo, tamano, imagen, id_categoria, estado
 */
require_once __DIR__ . '/../config/database.php';

class Producto {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /** Lista todos los productos activos con su categoría */
    public function listarProductos(): array {
        $sql = "SELECT p.*, c.nombre AS categoria
                FROM producto p
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                WHERE p.estado = 1 OR p.estado IS NULL
                ORDER BY p.id_producto DESC";
        return $this->conn->query($sql)->fetchAll();
    }

    /** Obtiene categorías para selectores */
    public function obtenerCategorias(): array {
        return $this->conn->query(
            "SELECT * FROM categoria ORDER BY nombre ASC"
        )->fetchAll();
    }

    /** Registra un nuevo producto con todos los campos */
    public function registrar(
        string  $nombre,
        float   $precio_compra,
        float   $precio_venta,
        int     $stock,
        int     $stock_minimo,
        int     $id_categoria,
        ?string $imagen,
        string  $descripcion   = '',
        string  $codigo_barras = '',
        string  $tamano        = ''
    ): bool {
        // Compatibilidad: precio = precio_venta para consultas legacy
        $stmt = $this->conn->prepare(
            "INSERT INTO producto
               (nombre, descripcion, codigo_barras, precio, precio_compra,
                precio_venta, stock, stock_minimo, tamano, imagen,
                id_categoria, estado, created_at)
             VALUES
               (:nombre, :desc, :cod, :pv, :pc,
                :pv2, :stock, :stock_min, :tamano, :imagen,
                :id_cat, 1, NOW())"
        );
        return $stmt->execute([
            ':nombre'    => $nombre,
            ':desc'      => $descripcion,
            ':cod'       => $codigo_barras,
            ':pv'        => $precio_venta,
            ':pc'        => $precio_compra,
            ':pv2'       => $precio_venta,
            ':stock'     => $stock,
            ':stock_min' => $stock_minimo,
            ':tamano'    => $tamano,
            ':imagen'    => $imagen,
            ':id_cat'    => $id_categoria,
        ]);
    }

    /** Obtiene un producto por ID */
    public function obtenerPorId(int $id): array|false {
        $stmt = $this->conn->prepare(
            "SELECT * FROM producto WHERE id_producto = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /** Actualiza un producto */
    public function actualizar(
        int     $id,
        string  $nombre,
        float   $precio_compra,
        float   $precio_venta,
        int     $id_categoria,
        int     $stock_minimo,
        ?string $imagen        = null,
        string  $descripcion   = '',
        string  $codigo_barras = '',
        string  $tamano        = ''
    ): bool {
        if ($imagen) {
            $sql = "UPDATE producto SET
                      nombre=:nombre, descripcion=:desc, codigo_barras=:cod,
                      precio=:pv, precio_compra=:pc, precio_venta=:pv2,
                      stock_minimo=:sm, tamano=:tamano, imagen=:imagen,
                      id_categoria=:id_cat
                    WHERE id_producto=:id";
        } else {
            $sql = "UPDATE producto SET
                      nombre=:nombre, descripcion=:desc, codigo_barras=:cod,
                      precio=:pv, precio_compra=:pc, precio_venta=:pv2,
                      stock_minimo=:sm, tamano=:tamano,
                      id_categoria=:id_cat
                    WHERE id_producto=:id";
        }
        $params = [
            ':nombre' => $nombre,
            ':desc'   => $descripcion,
            ':cod'    => $codigo_barras,
            ':pv'     => $precio_venta,
            ':pc'     => $precio_compra,
            ':pv2'    => $precio_venta,
            ':sm'     => $stock_minimo,
            ':tamano' => $tamano,
            ':id_cat' => $id_categoria,
            ':id'     => $id,
        ];
        if ($imagen) $params[':imagen'] = $imagen;

        return $this->conn->prepare($sql)->execute($params);
    }

    /** Elimina producto (borrado lógico) en transacción */
    public function eliminar(int $id): bool {
        try {
            $this->conn->beginTransaction();
            // Eliminar detalles de movimientos relacionados
            $this->conn->prepare(
                "DELETE dm FROM detalle_movimiento dm
                 JOIN movimiento m ON dm.id_movimiento = m.id_movimiento
                 WHERE m.id_producto = :id"
            )->execute([':id' => $id]);
            // Eliminar movimientos
            $this->conn->prepare(
                "DELETE FROM movimiento WHERE id_producto = :id"
            )->execute([':id' => $id]);
            // Borrado lógico
            $this->conn->prepare(
                "UPDATE producto SET estado = 0 WHERE id_producto = :id"
            )->execute([':id' => $id]);
            $this->conn->commit();
            return true;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            error_log('Producto::eliminar — ' . $e->getMessage());
            return false;
        }
    }

    /** Busca por nombre, categoría o código de barras */
    public function buscar(string $termino): array {
        $stmt = $this->conn->prepare(
            "SELECT p.*, c.nombre AS categoria
             FROM producto p
             LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
             WHERE (p.nombre LIKE :t OR c.nombre LIKE :t2
                    OR p.codigo_barras LIKE :t3 OR p.descripcion LIKE :t4)
               AND (p.estado = 1 OR p.estado IS NULL)
             ORDER BY p.id_producto DESC"
        );
        $t = "%$termino%";
        $stmt->execute([':t'=>$t, ':t2'=>$t, ':t3'=>$t, ':t4'=>$t]);
        return $stmt->fetchAll();
    }

    /** Productos con stock crítico (stock < stock_minimo) */
    public function obtenerStockCritico(int $limite = 5): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM producto
             WHERE stock < COALESCE(stock_minimo, :limite)
               AND (estado = 1 OR estado IS NULL)
             ORDER BY stock ASC"
        );
        $stmt->execute([':limite' => $limite]);
        return $stmt->fetchAll();
    }
}
