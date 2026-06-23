<?php
/**
 * Setup - MARTS
 * Crea la BD, aplica la estructura y carga datos iniciales.
 * Ejecutar UNA sola vez desde el navegador o CLI.
 */
$host = '127.0.0.1';
$port = 3320;
$user = 'root';
$pass = '';
$db   = 'bdinventario';

$pasos  = [];
$errores = [];

/* ── 1. Conectar sin BD y crearla ─────────────────────────── */
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    $pasos[] = ['ok', "Base de datos <code>$db</code> creada/verificada."];
} catch (PDOException $e) {
    $errores[] = "No se pudo conectar al servidor MySQL (puerto $port): " . $e->getMessage();
    goto render;
}

/* ── 2. Crear tablas ──────────────────────────────────────── */
$tablasSql = [
    'rol' => "CREATE TABLE IF NOT EXISTS `rol` (
        `id_rol` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'categoria' => "CREATE TABLE IF NOT EXISTS `categoria` (
        `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre`       VARCHAR(100) NOT NULL,
        `descripcion`  TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'usuario' => "CREATE TABLE IF NOT EXISTS `usuario` (
        `id_usuario` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre`     VARCHAR(100) NOT NULL,
        `correo`     VARCHAR(100) NOT NULL UNIQUE,
        `contraseña` VARCHAR(255) NOT NULL,
        `id_rol`     INT NOT NULL,
        `estado`     TINYINT(1) NOT NULL DEFAULT 1,
        KEY `id_rol` (`id_rol`),
        CONSTRAINT `usuario_rol_fk` FOREIGN KEY (`id_rol`) REFERENCES `rol`(`id_rol`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'tipo_movimiento' => "CREATE TABLE IF NOT EXISTS `tipo_movimiento` (
        `id_tipo_movimiento` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre`    VARCHAR(100) NOT NULL,
        `operacion` ENUM('entrada','salida') NOT NULL,
        `contexto`  TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'producto' => "CREATE TABLE IF NOT EXISTS `producto` (
        `id_producto`  INT AUTO_INCREMENT PRIMARY KEY,
        `nombre`       VARCHAR(100) NOT NULL,
        `precio`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `stock`        INT NOT NULL DEFAULT 0,
        `id_categoria` INT DEFAULT NULL,
        `imagen`       VARCHAR(255) DEFAULT NULL,
        KEY `id_categoria` (`id_categoria`),
        CONSTRAINT `producto_cat_fk` FOREIGN KEY (`id_categoria`) REFERENCES `categoria`(`id_categoria`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'movimiento' => "CREATE TABLE IF NOT EXISTS `movimiento` (
        `id_movimiento`      INT AUTO_INCREMENT PRIMARY KEY,
        `id_producto`        INT NOT NULL,
        `id_tipo_movimiento` INT DEFAULT NULL,
        `tipo`               ENUM('entrada','salida') NOT NULL,
        `cantidad`           INT NOT NULL,
        `motivo`             TEXT DEFAULT NULL,
        `id_usuario`         INT DEFAULT NULL,
        `fecha`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY `id_producto`        (`id_producto`),
        KEY `id_usuario`         (`id_usuario`),
        KEY `id_tipo_movimiento` (`id_tipo_movimiento`),
        CONSTRAINT `mov_prod_fk` FOREIGN KEY (`id_producto`)        REFERENCES `producto`(`id_producto`),
        CONSTRAINT `mov_usr_fk`  FOREIGN KEY (`id_usuario`)         REFERENCES `usuario`(`id_usuario`),
        CONSTRAINT `mov_tipo_fk` FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento`(`id_tipo_movimiento`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'detalle_movimiento' => "CREATE TABLE IF NOT EXISTS `detalle_movimiento` (
        `id_detalle`           INT AUTO_INCREMENT PRIMARY KEY,
        `id_movimiento`        INT NOT NULL,
        `comentarios_tecnicos` TEXT DEFAULT NULL,
        `ubicacion_almacen`    VARCHAR(100) DEFAULT NULL,
        `referencia_externa`   VARCHAR(100) DEFAULT NULL,
        KEY `id_movimiento` (`id_movimiento`),
        CONSTRAINT `det_mov_fk` FOREIGN KEY (`id_movimiento`) REFERENCES `movimiento`(`id_movimiento`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'log' => "CREATE TABLE IF NOT EXISTS `log` (
        `id_log`     INT AUTO_INCREMENT PRIMARY KEY,
        `id_usuario` INT DEFAULT NULL,
        `accion`     VARCHAR(255) NOT NULL,
        `entidad`    VARCHAR(50)  NOT NULL,
        `detalles`   TEXT DEFAULT NULL,
        `fecha`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY `id_usuario` (`id_usuario`),
        CONSTRAINT `log_usr_fk` FOREIGN KEY (`id_usuario`) REFERENCES `usuario`(`id_usuario`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tablasSql as $nombre => $sql) {
    try {
        $pdo->exec($sql);
        $n = $pdo->query("SELECT COUNT(*) FROM `$nombre`")->fetchColumn();
        $pasos[] = ['ok', "Tabla <code>$nombre</code> lista ($n filas)."];
    } catch (PDOException $e) {
        $errores[] = "Error en tabla <code>$nombre</code>: " . $e->getMessage();
    }
}

/* ── 3. Datos iniciales ───────────────────────────────────── */

// Roles
try {
    $pdo->exec("INSERT IGNORE INTO `rol` (id_rol, nombre) VALUES (1,'admin'),(2,'empleado')");
    $pasos[] = ['ok', 'Roles <code>admin</code> y <code>empleado</code> verificados.'];
} catch (PDOException $e) { $errores[] = 'Roles: ' . $e->getMessage(); }

// Tipos de movimiento
try {
    $pdo->exec("INSERT IGNORE INTO `tipo_movimiento` (id_tipo_movimiento, nombre, operacion, contexto) VALUES
        (1,'Compra de Mercancía','entrada','Ingreso por adquisición a proveedores'),
        (2,'Devolución de Cliente','entrada','Reingreso por garantía o cambio'),
        (3,'Ajuste de Inventario (+)','entrada','Corrección manual positiva'),
        (4,'Venta Directa','salida','Salida por comercialización'),
        (5,'Producto Dañado','salida','Baja por pérdida o daño'),
        (6,'Ajuste de Inventario (-)','salida','Corrección manual negativa')");
    $n = $pdo->query("SELECT COUNT(*) FROM tipo_movimiento")->fetchColumn();
    $pasos[] = ['ok', "Tipos de movimiento: $n registros."];
} catch (PDOException $e) { $errores[] = 'Tipos: ' . $e->getMessage(); }

// Usuario admin
try {
    $hashAdmin    = '$2y$10$YH/zAXZufw6Tiy/vsd0reu3p6d/k79Z8OU20UF7coCeNrdVLYZAJ2';
    $hashEmpleado = '$2y$10$gdlbtrMxteuWeD.JX4AfK.ijbT12JNUCkgenAg2Bccec05TSwWA6.';
    $stmt = $pdo->prepare("INSERT IGNORE INTO `usuario` (id_usuario,nombre,correo,`contraseña`,id_rol,estado) VALUES (?,?,?,?,?,1)");
    $stmt->execute([1, 'Administrador',  'admin@marts.com',    $hashAdmin,    1]);
    $stmt->execute([2, 'Juan Empleado',  'empleado@marts.com', $hashEmpleado, 2]);
    $n = $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
    $pasos[] = ['ok', "Usuarios creados: $n. (<strong>admin@marts.com / admin123</strong>)"];
} catch (PDOException $e) { $errores[] = 'Usuarios: ' . $e->getMessage(); }

// Categorías
try {
    $pdo->exec("INSERT IGNORE INTO `categoria` (id_categoria,nombre,descripcion) VALUES
        (1,'Electrónica','Dispositivos electrónicos y accesorios'),
        (2,'Ropa','Prendas de vestir y accesorios'),
        (3,'Alimentos','Productos alimenticios y bebidas'),
        (4,'Herramientas','Herramientas manuales y eléctricas'),
        (5,'Papelería','Artículos de oficina y escritorio')");
    $n = $pdo->query("SELECT COUNT(*) FROM categoria")->fetchColumn();
    $pasos[] = ['ok', "Categorías: $n registros."];
} catch (PDOException $e) { $errores[] = 'Categorías: ' . $e->getMessage(); }

// Productos de ejemplo
try {
    $pdo->exec("INSERT IGNORE INTO `producto` (id_producto,nombre,precio,stock,id_categoria) VALUES
        (1,'Laptop Dell Inspiron 15',12500.00,8,1),
        (2,'Mouse Inalámbrico Logitech',350.00,25,1),
        (3,'Teclado Mecánico RGB',850.00,15,1),
        (4,'Monitor 24\" Full HD',3200.00,6,1),
        (5,'Audífonos Bluetooth',650.00,20,1),
        (6,'Camiseta Polo Slim Fit',280.00,40,2),
        (7,'Pantalón Cargo',520.00,30,2),
        (8,'Arroz 5kg',85.00,50,3),
        (9,'Aceite de Oliva 1L',120.00,35,3),
        (10,'Taladro Inalámbrico 18V',1800.00,4,4),
        (11,'Juego de Desarmadores',320.00,12,4),
        (12,'Resma de Papel A4',95.00,60,5),
        (13,'Bolígrafos x12',45.00,80,5),
        (14,'Cuaderno Profesional',65.00,45,5),
        (15,'Disco Duro Externo 1TB',1200.00,2,1)");
    $n = $pdo->query("SELECT COUNT(*) FROM producto")->fetchColumn();
    $pasos[] = ['ok', "Productos de ejemplo: $n registros."];
} catch (PDOException $e) { $errores[] = 'Productos: ' . $e->getMessage(); }

// Directorio de imágenes
$imgDir = __DIR__ . '/public/img/productos/';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0755, true);
    $pasos[] = ['ok', 'Directorio <code>public/img/productos/</code> creado.'];
} else {
    $pasos[] = ['skip', 'Directorio de imágenes ya existe.'];
}

render:
$exito = empty($errores);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setup | MARTS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#0a0e1a;color:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
    .card{background:#0d1224;border:1px solid rgba(255,255,255,0.08);border-radius:20px;width:100%;max-width:620px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.5)}
    .card-header{padding:1.75rem 2rem;border-bottom:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;gap:1rem}
    .icon{width:46px;height:46px;background:linear-gradient(135deg,#3b82f6,#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 0 16px rgba(59,130,246,0.35)}
    h1{font-family:'Plus Jakarta Sans',sans-serif;font-size:1.2rem;font-weight:800}
    .sub{font-size:0.72rem;color:#475569;margin-top:0.2rem}
    .card-body{padding:1.5rem 2rem}
    .step{display:flex;align-items:flex-start;gap:0.75rem;padding:0.7rem 0.875rem;border-radius:10px;margin-bottom:0.4rem;font-size:0.82rem}
    .step-ok  {background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#6ee7b7}
    .step-skip{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);color:#475569}
    .step-err {background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:#fca5a5}
    .step-icon{flex-shrink:0;margin-top:1px}
    .result{margin:1.25rem 0;padding:1.125rem;border-radius:12px;text-align:center}
    .result-ok {background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#6ee7b7}
    .result-err{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#fca5a5}
    .result p{font-weight:700;margin-bottom:0.3rem}
    .result small{font-size:0.72rem;opacity:0.85}
    .creds{background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:1rem 1.25rem;margin:1rem 0;font-size:0.82rem}
    .creds p{margin-bottom:0.35rem;color:#94a3b8}
    .creds strong{color:#60a5fa}
    .actions{display:flex;gap:0.75rem;margin-top:1.25rem}
    .btn{flex:1;padding:0.8rem;border-radius:10px;border:none;cursor:pointer;font-size:0.875rem;font-weight:700;font-family:inherit;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.2s}
    .btn-primary{background:linear-gradient(135deg,#3b82f6,#4f46e5);color:white;box-shadow:0 4px 15px rgba(59,130,246,0.3)}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(59,130,246,0.4)}
    .btn-secondary{background:rgba(255,255,255,0.05);color:#94a3b8;border:1px solid rgba(255,255,255,0.08)}
    code{background:rgba(255,255,255,0.08);padding:0.1rem 0.4rem;border-radius:4px;font-size:0.78em}
    strong{color:#f1f5f9}
  </style>
</head>
<body>
<div class="card">
  <div class="card-header">
    <div class="icon">
      <svg width="22" height="22" fill="none" stroke="white" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
    </div>
    <div>
      <h1>MARTS — Setup del Sistema</h1>
      <p class="sub">Configuración inicial &bull; Puerto MySQL: <?php echo $port; ?></p>
    </div>
  </div>

  <div class="card-body">

    <?php foreach ($pasos as [$tipo, $msg]): ?>
    <div class="step step-<?php echo $tipo; ?>">
      <span class="step-icon">
        <?php if ($tipo === 'ok'): ?>
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <?php else: ?>
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?php endif; ?>
      </span>
      <span><?php echo $msg; ?></span>
    </div>
    <?php endforeach; ?>

    <?php foreach ($errores as $err): ?>
    <div class="step step-err">
      <span class="step-icon"><svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>
      <span><?php echo htmlspecialchars($err); ?></span>
    </div>
    <?php endforeach; ?>

    <div class="result <?php echo $exito ? 'result-ok' : 'result-err'; ?>">
      <?php if ($exito): ?>
        <p>✅ Sistema configurado correctamente</p>
        <small>Base de datos lista con <?php echo $pdo->query("SELECT COUNT(*) FROM producto")->fetchColumn(); ?> productos y <?php echo $pdo->query("SELECT COUNT(*) FROM usuario")->fetchColumn(); ?> usuarios.</small>
      <?php else: ?>
        <p>⚠️ Se encontraron errores</p>
        <small>Verifica que Laragon esté corriendo y que el puerto <?php echo $port; ?> sea correcto.</small>
      <?php endif; ?>
    </div>

    <?php if ($exito): ?>
    <div class="creds">
      <p>Credenciales de acceso:</p>
      <p><strong>Admin:</strong> admin@marts.com &nbsp;/&nbsp; admin123</p>
      <p><strong>Empleado:</strong> empleado@marts.com &nbsp;/&nbsp; empleado123</p>
    </div>
    <?php endif; ?>

    <div class="actions">
      <?php if ($exito): ?>
      <a href="views/usuarios/login.php" class="btn btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
        Ir al Login
      </a>
      <a href="views/dashboard/admin.php" class="btn btn-secondary">Dashboard →</a>
      <?php else: ?>
      <a href="setup.php" class="btn btn-primary">Reintentar</a>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>
