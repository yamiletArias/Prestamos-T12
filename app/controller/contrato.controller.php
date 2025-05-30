<?php
// Archivo: app/controller/contrato.controller.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../model/Conexion.php';
require_once __DIR__ . '/../model/Contrato.php';

$db = (new Conexion())->conectar();
if (!$db) {
  http_response_code(500);
  echo json_encode(['error' => 'No hay conexión a BD']);
  exit;
}

$model = new Contrato($db);
$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    // 1) Chequeo rápido de contratos activos
    if (!empty($_GET['check']) && !empty($_GET['beneficiario'])) {
      $sql = "SELECT COUNT(*) AS cnt
                    FROM contratos
                    WHERE idbeneficiario = :ben AND estado = 'ACT'";
      $stmt = $db->prepare($sql);
      $stmt->execute([':ben' => (int) $_GET['beneficiario']]);
      $cnt = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
      echo json_encode(['count' => $cnt]);
      exit;
    }

    // 2) GET con id específico
    if (!empty($_GET['id'])) {
      $c = $model->getById((int) $_GET['id']);
      echo json_encode($c);

      // 3) Listado filtrado por beneficiario
    } elseif (!empty($_GET['beneficiario'])) {
      $arr = $model->getAll((int) $_GET['beneficiario']);
      echo json_encode($arr);

      // 4) Listado general
    } else {
      $arr = $model->getAll();
      echo json_encode($arr);
    }

  } elseif ($method === 'POST') {
    // Crear nuevo contrato
    $data = json_decode(file_get_contents('php://input'), true);

    // Validaciones básicas
    if (
      empty($data['idbeneficiario']) ||
      empty($data['monto']) ||
      empty($data['interes']) ||
      empty($data['fechainicio']) ||
      empty($data['numcuotas'])
    ) {
      throw new Exception('Faltan datos obligatorios');
    }

    // 1) Insertar el contrato
    $newId = $model->create($data);

    // 2) Generar el cronograma de pagos
    $db->beginTransaction();
    try {
      $fechaInicio = new DateTime($data['fechainicio']);
      $principal = (float) $data['monto'];
      $interesAnual = (float) $data['interes'] / 100;
      $cuotas = (int) $data['numcuotas'];

      // Cálculo de cuota fija (amortización francesa)
      $tasaMensual = $interesAnual / 12;
      if ($tasaMensual > 0) {
        $factor = ($tasaMensual * pow(1 + $tasaMensual, $cuotas))
          / (pow(1 + $tasaMensual, $cuotas) - 1);
        $cuotaFija = $principal * $factor;
      } else {
        $cuotaFija = $principal / $cuotas;
      }

      // Inserción en pagos sin fecha_programada (se calcula luego con DATE_ADD)
      $stmtPago = $db->prepare(
        "INSERT INTO pagos
                (idcontrato, numcuota, monto, penalidad, fechapago, medio)
                VALUES
                (?, ?, ?, 0, NULL, NULL)"
      );

      for ($i = 1; $i <= $cuotas; $i++) {
        $stmtPago->execute([
          $newId,
          $i,
          number_format($cuotaFija, 2, '.', '')
        ]);
      }

      $db->commit();
    } catch (Throwable $e) {
      $db->rollBack();
      throw new Exception('No se pudo generar el cronograma');
    }

    // 3) Responder al frontend
    echo json_encode(['success' => true, 'id' => $newId]);

  } elseif ($method === 'PUT') {
    // Actualizar contrato
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['idcontrato'])) {
      throw new Exception('ID de contrato requerido');
    }
    $ok = $model->update($data);
    echo json_encode(['success' => (bool) $ok]);

  } else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
  }

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
