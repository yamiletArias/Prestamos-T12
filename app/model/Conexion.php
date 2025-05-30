<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// app/conexion.php
class Conexion {
    private string $host;
    private string $dbname;
    private string $user;
    private string $pass;
    private ?PDO $pdo = null;

    public function __construct() {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->host = $_ENV['DB_HOST'];
        $this->dbname = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];
    }

    public function conectar(): ?PDO {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->user,
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return $this->pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Conexión fallida: ' . $e->getMessage()]);
            return null;
        }
    }
}