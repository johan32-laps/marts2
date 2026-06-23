-- ============================================================
-- MARTS - Datos Iniciales
-- Base de datos: stockcontrol  |  Puerto Laragon: 3320
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Roles ────────────────────────────────────────────────────
INSERT IGNORE INTO `rol` (`id_rol`, `nombre`) VALUES
(1, 'admin'),
(2, 'empleado');

-- ── Tipos de movimiento ───────────────────────────────────────
INSERT IGNORE INTO `tipo_movimiento`
  (`id_tipo_movimiento`, `nombre`, `operacion`, `contexto`) VALUES
(1, 'Compra de Mercancía',      'entrada', 'Ingreso por adquisición a proveedores'),
(2, 'Devolución de Cliente',    'entrada', 'Reingreso por garantía o cambio'),
(3, 'Ajuste de Inventario (+)', 'entrada', 'Corrección manual de stock positiva'),
(4, 'Venta Directa',            'salida',  'Salida por comercialización al cliente'),
(5, 'Producto Dañado',          'salida',  'Baja por pérdida, daño o vencimiento'),
(6, 'Ajuste de Inventario (-)', 'salida',  'Corrección manual de stock negativa');

-- ── Usuarios ─────────────────────────────────────────────────
-- admin@marts.com     / admin123
-- empleado@marts.com  / empleado123
INSERT IGNORE INTO `usuario`
  (`id_usuario`, `nombre`, `correo`, `contraseña`, `id_rol`, `estado`) VALUES
(1, 'Administrador', 'admin@marts.com',
 '$2y$10$YH/zAXZufw6Tiy/vsd0reu3p6d/k79Z8OU20UF7coCeNrdVLYZAJ2', 1, 1),
(2, 'Juan Empleado', 'empleado@marts.com',
 '$2y$10$gdlbtrMxteuWeD.JX4AfK.ijbT12JNUCkgenAg2Bccec05TSwWA6.', 2, 1);

-- ── Categorías ───────────────────────────────────────────────
INSERT IGNORE INTO `categoria` (`id_categoria`, `nombre`, `descripcion`) VALUES
(1, 'Electrónica',  'Dispositivos electrónicos y accesorios'),
(2, 'Ropa',         'Prendas de vestir y accesorios de moda'),
(3, 'Alimentos',    'Productos alimenticios y bebidas'),
(4, 'Herramientas', 'Herramientas manuales y eléctricas'),
(5, 'Papelería',    'Artículos de oficina y escritorio');

-- ── Productos ────────────────────────────────────────────────
INSERT IGNORE INTO `producto`
  (`id_producto`, `nombre`, `precio`, `stock`, `id_categoria`) VALUES
(1,  'Laptop Dell Inspiron 15',    12500.00,  8, 1),
(2,  'Mouse Inalámbrico Logitech',   350.00, 25, 1),
(3,  'Teclado Mecánico RGB',         850.00, 15, 1),
(4,  'Monitor 24" Full HD',         3200.00,  6, 1),
(5,  'Audífonos Bluetooth',          650.00, 20, 1),
(6,  'Camiseta Polo Slim Fit',       280.00, 40, 2),
(7,  'Pantalón Cargo',               520.00, 30, 2),
(8,  'Arroz 5kg',                     85.00, 50, 3),
(9,  'Aceite de Oliva 1L',           120.00, 35, 3),
(10, 'Taladro Inalámbrico 18V',     1800.00,  4, 4),
(11, 'Juego de Desarmadores',        320.00, 12, 4),
(12, 'Resma de Papel A4',             95.00, 60, 5),
(13, 'Bolígrafos x12',                45.00, 80, 5),
(14, 'Cuaderno Profesional',          65.00, 45, 5),
(15, 'Disco Duro Externo 1TB',      1200.00,  2, 1);

SET FOREIGN_KEY_CHECKS = 1;
