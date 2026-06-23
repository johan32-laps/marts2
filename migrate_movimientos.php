<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    // Check if column exists first
    $query = "SHOW COLUMNS FROM movimiento LIKE 'motivo'";
    $stmt = $conn->query($query);
    
    if ($stmt->rowCount() == 0) {
        $alter = "ALTER TABLE movimiento ADD COLUMN motivo TEXT NULL AFTER cantidad";
        $conn->exec($alter);
        echo "Columna 'motivo' añadida con éxito.\n";
    } else {
        echo "La columna 'motivo' ya existe.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
