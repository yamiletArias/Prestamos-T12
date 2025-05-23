
<?php
// app/conexion.php
class Conexion {
    private string $host = 'localhost';
    private string $dbname = 'prestamos';
    private string $user = 'root';
    private string $pass = '';
    private ?PDO $pdo = null;

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