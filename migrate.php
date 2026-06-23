<?php
/**
 * Script de migración automática – StockControl
 * Ejecuta las modificaciones necesarias a la BD para el nuevo sistema.
 * Acceder UNA sola vez: http://localhost/stockcontrol/migrate.php
 */

require_once __DIR__ . '/config/database.php';
$db   = new Database();
$conn = $db->connect();

$migraciones = [];
$errores     = [];

// ─── 1. categoria.descripcion ─────────────────────────────────────────────
try {
    $check = $conn->query("SHOW COLUMNS FROM categoria LIKE 'descripcion'");
    if ($check->rowCount() === 0) {
        $conn->exec("ALTER TABLE categoria ADD COLUMN descripcion TEXT NULL DEFAULT NULL AFTER nombre");
        $migraciones[] = "✅ columna <code>categoria.descripcion</code> agregada.";
    } else {
        $migraciones[] = "⏭️  columna <code>categoria.descripcion</code> ya existe.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ categoria.descripcion: " . $e->getMessage();
}

// ─── 2. producto.imagen ───────────────────────────────────────────────────
try {
    $check = $conn->query("SHOW COLUMNS FROM producto LIKE 'imagen'");
    if ($check->rowCount() === 0) {
        $conn->exec("ALTER TABLE producto ADD COLUMN imagen VARCHAR(255) NULL DEFAULT NULL");
        $migraciones[] = "✅ columna <code>producto.imagen</code> agregada.";
    } else {
        $migraciones[] = "⏭️  columna <code>producto.imagen</code> ya existe.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ producto.imagen: " . $e->getMessage();
}

// ─── 3. usuario.rol — cambiar ENUM a minúsculas ───────────────────────────
try {
    $check = $conn->query("SHOW COLUMNS FROM usuario LIKE 'rol'");
    $col   = $check->fetch(PDO::FETCH_ASSOC);
    // Si el ENUM tiene mayúsculas, lo modificamos
    if ($col && strpos($col['Type'], "'Administrador'") !== false) {
        $conn->exec("ALTER TABLE usuario MODIFY COLUMN rol ENUM('administrador','empleado') NOT NULL DEFAULT 'empleado'");
        // Normalizar datos existentes
        $conn->exec("UPDATE usuario SET rol = LOWER(rol)");
        $migraciones[] = "✅ <code>usuario.rol</code> ENUM normalizado a minúsculas.";
    } else {
        $migraciones[] = "⏭️  <code>usuario.rol</code> ya está en formato correcto.";
    }
} catch (PDOException $e) {
    $errores[] = "❌ usuario.rol: " . $e->getMessage();
}

// ─── 4. Crear admin por defecto si no existe ningún usuario ───────────────
try {
    $count = $conn->query("SELECT COUNT(*) FROM usuario")->fetchColumn();
    if ($count == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO usuario (nombre, correo, contraseña, rol, estado) VALUES ('Administrador', 'admin@stockcontrol.com', '$hash', 'administrador', 1)");
        $migraciones[] = "✅ Usuario admin creado: <strong>admin@stockcontrol.com</strong> / contraseña: <strong>admin123</strong>";
    } else {
        $migraciones[] = "⏭️  Ya existen usuarios en el sistema (no se creó admin por defecto).";
    }
} catch (PDOException $e) {
    $errores[] = "❌ Usuario admin: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración | StockControl</title>
    <link rel="icon" type="image/png" href="img/icon.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4s8 1.79 8 4"/></svg>
            </div>
            <div>
                <h1 style="font-family:'Plus Jakarta Sans',sans-serif" class="text-xl font-bold text-slate-900">Migración de Base de Datos</h1>
                <p class="text-sm text-slate-500">StockControl – Script de actualización</p>
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
            <p class="text-green-700 font-semibold text-sm">✅ Migración completada sin errores.</p>
        </div>
        <?php else: ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center mb-6">
            <p class="text-red-700 font-semibold text-sm">⚠️ Se encontraron errores. Revisa los mensajes anteriores.</p>
        </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <a href="views/usuarios/login.php" class="flex-1 text-center bg-blue-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                Ir al Login →
            </a>
            <a href="views/dashboard/admin.php" class="flex-1 text-center border border-slate-200 text-slate-600 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
                Ir al Dashboard
            </a>
        </div>
    </div>
</body>
</html>
