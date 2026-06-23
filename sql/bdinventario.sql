CREATE TABLE `rol` (
  `id_rol` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` text
);

CREATE TABLE `usuario` (
  `id_usuario` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `apellido` varchar(255),
  `correo` varchar(255) UNIQUE,
  `password` varchar(255),
  `telefono` varchar(255),
  `foto` varchar(255),
  `id_rol` int,
  `estado` boolean,
  `created_at` timestamp
);

CREATE TABLE `categoria` (
  `id_categoria` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` text,
  `created_at` timestamp
);

CREATE TABLE `producto` (
  `id_producto` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `descripcion` text,
  `codigo_barras` varchar(255),
  `precio` decimal,
  `stock` int,
  `stock_minimo` int,
  `imagen` varchar(255),
  `id_categoria` int,
  `estado` boolean,
  `created_at` timestamp
);

CREATE TABLE `tipo_movimiento` (
  `id_tipo_movimiento` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(255),
  `operacion` varchar(255),
  `contexto` text
);

CREATE TABLE `movimiento` (
  `id_movimiento` int PRIMARY KEY AUTO_INCREMENT,
  `id_producto` int,
  `id_tipo_movimiento` int,
  `id_usuario` int,
  `tipo` varchar(255),
  `cantidad` int,
  `stock_anterior` int,
  `stock_nuevo` int,
  `motivo` text,
  `fecha` timestamp
);

CREATE TABLE `detalle_movimiento` (
  `id_detalle` int PRIMARY KEY AUTO_INCREMENT,
  `id_movimiento` int,
  `comentarios_tecnicos` text,
  `ubicacion_almacen` varchar(255),
  `referencia_externa` varchar(255)
);

CREATE TABLE `log` (
  `id_log` int PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` int,
  `accion` varchar(255),
  `entidad` varchar(255),
  `detalles` text,
  `fecha` timestamp
);

ALTER TABLE `usuario` ADD FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);

ALTER TABLE `producto` ADD FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`);

ALTER TABLE `movimiento` ADD FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

ALTER TABLE `movimiento` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

ALTER TABLE `movimiento` ADD FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`);

ALTER TABLE `detalle_movimiento` ADD FOREIGN KEY (`id_movimiento`) REFERENCES `movimiento` (`id_movimiento`);

ALTER TABLE `log` ADD FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
