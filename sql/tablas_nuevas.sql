-- ============================================================
-- MARTS - Tablas adicionales según historias de usuario
-- BD: bdinventario | Puerto: 3320
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Tabla: cliente ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente`  INT AUTO_INCREMENT PRIMARY KEY,
  `nombre`      VARCHAR(100) NOT NULL,
  `apellido`    VARCHAR(100) DEFAULT NULL,
  `documento`   VARCHAR(20)  DEFAULT NULL,
  `telefono`    VARCHAR(20)  DEFAULT NULL,
  `email`       VARCHAR(100) DEFAULT NULL,
  `direccion`   TEXT         DEFAULT NULL,
  `estado`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: caja ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `caja` (
  `id_caja`        INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario`     INT          NOT NULL,
  `saldo_inicial`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `saldo_teorico`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `saldo_final`    DECIMAL(12,2) DEFAULT NULL,
  `diferencia`     DECIMAL(12,2) DEFAULT NULL,
  `justificacion`  TEXT          DEFAULT NULL,
  `estado`         ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `fecha_apertura` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre`   TIMESTAMP    NULL DEFAULT NULL,
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `caja_usr_fk` FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: movimiento_caja ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `movimiento_caja` (
  `id_mov_caja`  INT AUTO_INCREMENT PRIMARY KEY,
  `id_caja`      INT           NOT NULL,
  `tipo`         ENUM('ingreso','egreso') NOT NULL,
  `monto`        DECIMAL(12,2) NOT NULL,
  `concepto`     VARCHAR(255)  NOT NULL,
  `id_venta`     INT           DEFAULT NULL,
  `id_devolucion`INT           DEFAULT NULL,
  `fecha`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `id_caja` (`id_caja`),
  CONSTRAINT `movcaja_caja_fk` FOREIGN KEY (`id_caja`) REFERENCES `caja`(`id_caja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: venta ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `venta` (
  `id_venta`       INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario`     INT           NOT NULL,
  `id_cliente`     INT           DEFAULT NULL,
  `id_caja`        INT           DEFAULT NULL,
  `metodo_pago`    ENUM('efectivo','transferencia') NOT NULL DEFAULT 'efectivo',
  `total`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `estado`         ENUM('completada','anulada') NOT NULL DEFAULT 'completada',
  `observaciones`  TEXT          DEFAULT NULL,
  `fecha`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `id_usuario` (`id_usuario`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_caja`    (`id_caja`),
  CONSTRAINT `venta_usr_fk`    FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`),
  CONSTRAINT `venta_cli_fk`    FOREIGN KEY (`id_cliente`) REFERENCES `cliente`(`id_cliente`),
  CONSTRAINT `venta_caja_fk`   FOREIGN KEY (`id_caja`)    REFERENCES `caja`(`id_caja`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: detalle_venta ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `detalle_venta` (
  `id_detalle`    INT AUTO_INCREMENT PRIMARY KEY,
  `id_venta`      INT           NOT NULL,
  `id_producto`   INT           NOT NULL,
  `cantidad`      INT           NOT NULL,
  `precio_venta`  DECIMAL(12,2) NOT NULL,
  `subtotal`      DECIMAL(12,2) NOT NULL,
  KEY `id_venta`    (`id_venta`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `dv_venta_fk`   FOREIGN KEY (`id_venta`)    REFERENCES `venta`(`id_venta`)    ON DELETE CASCADE,
  CONSTRAINT `dv_prod_fk`    FOREIGN KEY (`id_producto`) REFERENCES `producto`(`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: compra ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `compra` (
  `id_compra`    INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario`   INT           NOT NULL,
  `proveedor`    VARCHAR(150)  DEFAULT NULL,
  `total`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `observaciones`TEXT          DEFAULT NULL,
  `fecha`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `compra_usr_fk` FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: detalle_compra ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `detalle_compra` (
  `id_detalle`    INT AUTO_INCREMENT PRIMARY KEY,
  `id_compra`     INT           NOT NULL,
  `id_producto`   INT           NOT NULL,
  `cantidad`      INT           NOT NULL,
  `precio_compra` DECIMAL(12,2) NOT NULL,
  `subtotal`      DECIMAL(12,2) NOT NULL,
  KEY `id_compra`   (`id_compra`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `dc_compra_fk` FOREIGN KEY (`id_compra`)   REFERENCES `compra`(`id_compra`)   ON DELETE CASCADE,
  CONSTRAINT `dc_prod_fk`   FOREIGN KEY (`id_producto`) REFERENCES `producto`(`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: devolucion ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `devolucion` (
  `id_devolucion`  INT AUTO_INCREMENT PRIMARY KEY,
  `id_venta`       INT           NOT NULL,
  `id_usuario`     INT           NOT NULL,
  `motivo`         TEXT          NOT NULL,
  `total_devolucion` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fecha`          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `id_venta`   (`id_venta`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `dev_venta_fk` FOREIGN KEY (`id_venta`)   REFERENCES `venta`(`id_venta`),
  CONSTRAINT `dev_usr_fk`   FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabla: detalle_devolucion ────────────────────────────────
CREATE TABLE IF NOT EXISTS `detalle_devolucion` (
  `id_detalle`     INT AUTO_INCREMENT PRIMARY KEY,
  `id_devolucion`  INT           NOT NULL,
  `id_producto`    INT           NOT NULL,
  `cantidad`       INT           NOT NULL,
  `precio_unitario`DECIMAL(12,2) NOT NULL,
  `subtotal`       DECIMAL(12,2) NOT NULL,
  KEY `id_devolucion` (`id_devolucion`),
  KEY `id_producto`   (`id_producto`),
  CONSTRAINT `dd_dev_fk`  FOREIGN KEY (`id_devolucion`) REFERENCES `devolucion`(`id_devolucion`) ON DELETE CASCADE,
  CONSTRAINT `dd_prod_fk` FOREIGN KEY (`id_producto`)   REFERENCES `producto`(`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Actualizar producto: agregar precio_compra y precio_venta ─
ALTER TABLE `producto`
  ADD COLUMN IF NOT EXISTS `precio_compra` DECIMAL(12,2) DEFAULT 0.00 AFTER `precio`,
  ADD COLUMN IF NOT EXISTS `precio_venta`  DECIMAL(12,2) DEFAULT 0.00 AFTER `precio_compra`,
  ADD COLUMN IF NOT EXISTS `tamano`        VARCHAR(50)   DEFAULT NULL  AFTER `descripcion`;

SET FOREIGN_KEY_CHECKS = 1;
