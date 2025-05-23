<?php
require_once __DIR__ . "/Conexion.php";

class Contrato
{
  private PDO $pdo;

  public function __construct(PDO $db)
  {
    $this->pdo = $db;
  }

  /**
   * Obtiene todos los contratos, opcionalmente filtrados por beneficiario
   * @param int|null $idBeneficiario
   * @return array
   */
  public function getAll(?int $idBeneficiario = null): array
  {
    $sql = "
            SELECT
                c.idcontrato,
                c.idbeneficiario,
                CONCAT(b.apellidos, ' ', b.nombres) AS beneficiario_nombre,
                c.monto,
                c.interes,
                c.fechainicio,
                c.numcuotas,
                c.estado
            FROM contratos c
            INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
        ";

    if ($idBeneficiario !== null) {
      $sql .= " WHERE c.idbeneficiario = :ben";
    }
    $sql .= " ORDER BY c.fechainicio DESC";

    $stmt = $this->pdo->prepare($sql);
    if ($idBeneficiario !== null) {
      $stmt->bindValue(':ben', $idBeneficiario, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /**
   * Obtiene un contrato por su ID
   */
  public function getById(int $id): array
  {
    $stmt = $this->pdo->prepare(
      "SELECT idcontrato, idbeneficiario, monto, interes, fechainicio, numcuotas, estado
             FROM contratos WHERE idcontrato = :id"
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  }

  /**
   * Crea un nuevo contrato
   * @param array $data
   * @return int ID insertado
   */
  public function create(array $data): int
  {
    $sql = "
            INSERT INTO contratos
                (idbeneficiario, monto, interes, fechainicio, numcuotas, estado)
            VALUES
                (:idben, :monto, :interes, :fecha, :cuotas, 'ACT')
        ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':idben' => $data['idbeneficiario'],
      ':monto' => $data['monto'],
      ':interes' => $data['interes'],
      ':fecha' => $data['fechainicio'],
      ':cuotas' => $data['numcuotas'],
    ]);
    return (int) $this->pdo->lastInsertId();
  }

  /**
   * Actualiza un contrato existente
   * @param array $data
   * @return bool
   */
  public function update(array $data): bool
  {
    $sql = "
            UPDATE contratos SET
                idbeneficiario = :idben,
                monto          = :monto,
                interes        = :interes,
                fechainicio    = :fecha,
                numcuotas      = :cuotas,
                estado         = :estado
            WHERE idcontrato = :id
        ";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
      ':id' => $data['idcontrato'],
      ':idben' => $data['idbeneficiario'],
      ':monto' => $data['monto'],
      ':interes' => $data['interes'],
      ':fecha' => $data['fechainicio'],
      ':cuotas' => $data['numcuotas'],
      ':estado' => $data['estado'] ?? 'ACT',
    ]);
  }
}
