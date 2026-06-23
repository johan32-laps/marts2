<?php
/**
 * Database - MARTS
 * Conexión PDO — Laragon MySQL puerto 3320
 * BD: bdinventario
 */
class Database
{
    private string $host     = '127.0.0.1';
    private int    $port     = 3320;
    private string $db_name  = 'bdinventario';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    public function connect(): PDO
    {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";

        try {
            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
            return $pdo;
        } catch (PDOException $e) {
            error_log('DB Connection error: ' . $e->getMessage());
            http_response_code(500);
            die('<div style="font-family:monospace;background:#0a0e1a;color:#ef4444;padding:2rem;border-radius:8px;margin:2rem">
                <strong>Error de conexión</strong><br><br>' . htmlspecialchars($e->getMessage()) . '
            </div>');
        }
    }
}
