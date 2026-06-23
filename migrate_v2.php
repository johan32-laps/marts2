<?php
/**
 * Script de migración v2.0 – StockControl
 * Restructuración: Roles, Tipos de Movimiento y Detalle de Movimientos.
 */

require_once __DIR__ . '/config/database.php';
$db   = new Database();
$conn = $db->connect();

$migraciones = [];
$errores     = [];

// ─── 1. Tabla ROL ─────────────────────────────────────────────────────────
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS rol (
        id_rol INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL UNIQUE
    )");
    
    // Insertar roles base si no existen
    $check = $conn->query("SELECT COUNT(*) FROM rol")->fetchColumn();
    if ($check == 0) {
        $conn->exec("INSERT INTO rol (nombre) VALUES ('admin'), ('empleado')");
        $migraciones[] = "✅ Tabla <code>rol</code> creada e inicializada.";
    } else {
        $migraciones[] = "⏭️  Tabla <code>rol</code> ya existe.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ Error en tabla rol: " . $e->getMessage();
}

// ─── 2. Tabla TIPO_MOVIMIENTO ─────────────────────────────────────────────
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS tipo_movimiento (
        id_tipo_movimiento INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        operacion ENUM('entrada', 'salida') NOT NULL,
        contexto TEXT
    )");
    
    // Insertar tipos base si no existen
    $check = $conn->query("SELECT COUNT(*) FROM tipo_movimiento")->fetchColumn();
    if ($check == 0) {
        $conn->exec("INSERT INTO tipo_movimiento (nombre, operacion, contexto) VALUES 
            ('Compra', 'entrada', 'Entrada de mercancía por compra a proveedores'),
            ('Devolución Cliente', 'entrada', 'Entrada por devolución de un cliente'),
            ('Ajuste Positivo', 'entrada', 'Corrección de inventario por faltante detectado'),
            ('Venta', 'salida', 'Salida de mercancía por venta realizada'),
            ('Devolución Proveedor', 'salida', 'Salida por devolución a proveedor'),
            ('Merma/Ajuste Negativo', 'salida', 'Salida por daño, pérdida o error de conteo')");
        $migraciones[] = "✅ Tabla <code>tipo_movimiento</code> creada e inicializada.";
    } else {
        $migraciones[] = "⏭️  Tabla <code>tipo_movimiento</code> ya existe.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ Error en tabla tipo_movimiento: " . $e->getMessage();
}

// ─── 3. Vincular USUARIOS con ROLES ───────────────────────────────────────
try {
    $check = $conn->query("SHOW COLUMNS FROM usuario LIKE 'id_rol'");
    if ($check->rowCount() === 0) {
        $conn->exec("ALTER TABLE usuario ADD COLUMN id_rol INT AFTER id_usuario");
        $conn->exec("ALTER TABLE usuario ADD FOREIGN KEY (id_rol) REFERENCES rol(id_rol)");
        
        // Migrar datos de la columna 'rol' (string) a 'id_rol' (int)
        $conn->exec("UPDATE usuario SET id_rol = (SELECT id_rol FROM rol WHERE nombre = 'admin') WHERE rol IN ('admin', 'administrador')");
        $conn->exec("UPDATE usuario SET id_rol = (SELECT id_rol FROM rol WHERE nombre = 'empleado') WHERE rol = 'empleado'");
        
        $migraciones[] = "✅ Columna <code>usuario.id_rol</code> vinculada y datos migrados.";
    } else {
        $migraciones[] = "⏭️  Columna <code>usuario.id_rol</code> ya existe.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ Error vinculando roles: " . $e->getMessage();
}

// ─── 4. Tabla DETALLE_MOVIMIENTO ──────────────────────────────────────────
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS detalle_movimiento (
        id_detalle INT AUTO_INCREMENT PRIMARY KEY,
        id_movimiento INT NOT NULL,
        comentarios_tecnicos TEXT,
        ubicacion_almacen VARCHAR(100),
        referencia_externa VARCHAR(100),
        FOREIGN KEY (id_movimiento) REFERENCES movimiento(id_movimiento) ON DELETE CASCADE
    )");
    $migraciones[] = "✅ Tabla <code>detalle_movimiento</code> creada.";
} catch (PDOException $e) {
    $errores[] = "❌ Error en tabla detalle_movimiento: " . $e->getMessage();
}

// ─── 5. Actualizar tabla MOVIMIENTO ───────────────────────────────────────
try {
    $check = $conn->query("SHOW COLUMNS FROM movimiento LIKE 'id_tipo_movimiento'");
    if ($check->rowCount() === 0) {
        $conn->exec("ALTER TABLE movimiento ADD COLUMN id_tipo_movimiento INT AFTER id_producto");
        $conn->exec("ALTER TABLE movimiento ADD FOREIGN KEY (id_tipo_movimiento) REFERENCES tipo_movimiento(id_tipo_movimiento)");
        
        // Asignar un tipo por defecto a los existentes
        $conn->exec("UPDATE movimiento SET id_tipo_movimiento = (SELECT id_tipo_movimiento FROM tipo_movimiento WHERE nombre = 'Compra') WHERE tipo = 'entrada'");
        $conn->exec("UPDATE movimiento SET id_tipo_movimiento = (SELECT id_tipo_movimiento FROM tipo_movimiento WHERE nombre = 'Venta') WHERE tipo = 'salida'");
        
        $migraciones[] = "✅ Tabla <code>movimiento</code> actualizada con tipos.";
    } else {
        $migraciones[] = "⏭️  Tabla <code>movimiento</code> ya está actualizada.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ Error actualizando movimientos: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración v2 | StockControl</title>
    <link rel="icon" type="image/png" href="img/icon.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4s8 1.79 8 4"/></svg>
            </div>
            <div>
                <h1 style="font-family:'Plus Jakarta Sans',sans-serif" class="text-xl font-bold text-slate-900">Migración Arquitectónica v2.0</h1>
                <p class="text-sm text-slate-500">StockControl – Reestructuración de Datos</p>
            </div>
        </div>

        <div class="space-y-2 mb-6">
            <?php foreach ($migraciones as $m): ?>
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700"><?php echo $m; ?></div>
            <?php endforeach; ?>
            <?php foreach ($errores as $e): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700"><?php echo $e; ?></div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($errores)): ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center mb-6">
            <p class="text-green-700 font-semibold text-sm">✅ Sistema actualizado exitosamente.</p>
        </div>
        <?php else: ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center mb-6">
            <p class="text-red-700 font-semibold text-sm">⚠️ Errores detectados durante la migración.</p>
        </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <a href="views/dashboard/admin.php" class="flex-1 text-center bg-indigo-600 text-white py-3 rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all">
                Ir al Dashboard →
            </a>
        </div>
    </div>
</body>
</html>
