<?php
require_once "../model/Conexion.php";
class Beneficiario
{
  private PDO $pdo;

  public function __construct(PDO $db)
  {
    $this->pdo = $db;
  }

  public function getAll(): array
  {
    $sql = "SELECT idbeneficiario, apellidos, nombres, dni, telefono, direccion
                FROM beneficiarios
                ORDER BY apellidos, nombres";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  public function getById(int $id): array
  {
    $stmt = $this->pdo->prepare(
      "SELECT idbeneficiario, apellidos, nombres, dni, telefono, direccion
             FROM beneficiarios WHERE idbeneficiario = ?"
    );
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
  }

  public function create(array $d): int
  {
    $sql = "INSERT INTO beneficiarios (apellidos, nombres, dni, telefono, direccion)
                VALUES (:apellidos, :nombres, :dni, :telefono, :direccion)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
      ':apellidos' => $d['apellidos'],
      ':nombres' => $d['nombres'],
      ':dni' => $d['dni'],
      ':telefono' => $d['telefono'],
      ':direccion' => $d['direccion']
    ]);
    return (int) $this->pdo->lastInsertId();
  }

  public function update(array $d): bool
  {
    $sql = "UPDATE beneficiarios
                SET apellidos = :apellidos, nombres = :nombres, dni = :dni,
                    telefono = :telefono, direccion = :direccion
                WHERE idbeneficiario = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
      ':id' => $d['id'],
      ':apellidos' => $d['apellidos'],
      ':nombres' => $d['nombres'],
      ':dni' => $d['dni'],
      ':telefono' => $d['telefono'],
      ':direccion' => $d['direccion']
    ]);
  }

  /* public function create(array $data): int
  {
      $sql = "INSERT INTO beneficiarios (apellidos, nombres, dni, telefono, direccion)
              VALUES (:apellidos, :nombres, :dni, :telefono, :direccion)";
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindValue(':apellidos', $data['apellidos'], PDO::PARAM_STR);
      $stmt->bindValue(':nombres',   $data['nombres'],   PDO::PARAM_STR);
      $stmt->bindValue(':dni',       $data['dni'],       PDO::PARAM_STR);
      $stmt->bindValue(':telefono',  $data['telefono'] ?? null, PDO::PARAM_STR);
      $stmt->bindValue(':direccion', $data['direccion'] ?? null, PDO::PARAM_STR);
      $stmt->execute();
      return (int)$this->pdo->lastInsertId();
  } */
}
