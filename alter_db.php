<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    // Check if column exists first
    $query = "SHOW COLUMNS FROM producto LIKE 'imagen'";
    $stmt = $conn->query($query);
    
    if ($stmt->rowCount() == 0) {
        $alter = "ALTER TABLE producto ADD COLUMN imagen VARCHAR(255) NULL DEFAULT NULL";
        $conn->exec($alter);
        echo "Columna 'imagen' añadida con éxito.\n";
    } else {
        echo "La columna 'imagen' ya existe.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
