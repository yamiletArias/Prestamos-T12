<?php
require_once __DIR__ . '/../model/Conexion.php';

class CronogramaController {
    private $pdo;
    private $idContrato;

    public function __construct($id) {
        $this->idContrato = $id;
        $conexion = new Conexion();
        $this->pdo = $conexion->conectar();

        if (!$this->pdo) {
            throw new \Exception('No hay conexión a BD');
        }
        $this->validarContrato();
    }

    private function validarContrato() {
        if (empty($this->idContrato) || !ctype_digit((string)$this->idContrato)) {
            throw new \InvalidArgumentException('Contrato inválido');
        }
    }

    public function ejecutar() {
        $contrato = $this->obtenerDatosContrato();
        $pagos = $this->obtenerPagos();
        include __DIR__ . '/../views/cronograma.view.php';
    }

    private function obtenerDatosContrato() {
        $sql = "SELECT c.*, b.apellidos, b.nombres
                  FROM contratos c
                  JOIN beneficiarios b USING(idbeneficiario)
                 WHERE idcontrato = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idContrato]);
        $contrato = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$contrato) {
            throw new \RuntimeException('Contrato no encontrado');
        }
        return $contrato;
    }

    private function obtenerPagos() {
        $sql = "SELECT p.numcuota,
                       DATE_ADD(c.fechainicio, INTERVAL (p.numcuota-1) MONTH) AS fecha_programada,
                       p.monto, p.penalidad, p.fechapago, p.medio
                  FROM pagos p
                  JOIN contratos c USING(idcontrato)
                 WHERE p.idcontrato = ?
              ORDER BY p.numcuota";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idContrato]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Ejecución
try {
    $id = $_GET['id'] ?? null;
    $controller = new CronogramaController($id);
    $controller->ejecutar();
} catch (Exception $e) {
    die($e->getMessage());
}
?>