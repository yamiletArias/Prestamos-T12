<?php
// app/controller/pagos.php
header('Content-Type: application/json');
require_once __DIR__ . '/../model/Conexion.php';

$conexion = new Conexion();
$pdo = $conexion->conectar();
if (!$pdo) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'No hay conexión a BD']);
  exit;
}

// Detectar override de método
$method = $_SERVER['REQUEST_METHOD'];
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
  $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
}

if ($method !== 'PUT') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Método no permitido']);
  exit;
}

// Leer JSON del body
$input = json_decode(file_get_contents('php://input'), true);
if (
  !isset($input['idcontrato'], $input['numcuota'], $input['fechapago'], $input['penalidad'], $input['medio'])
  || !ctype_digit(strval($input['idcontrato']))
  || !ctype_digit(strval($input['numcuota']))
) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
  exit;
}

$id = (int) $input['idcontrato'];
$num = (int) $input['numcuota'];
$fecha = $input['fechapago'];    // debería venir en YYYY-MM-DD
$penalidad = floatval($input['penalidad']);
$medio = substr($input['medio'], 0, 3); // EFC o DEP

try {
  $sql = "UPDATE pagos
               SET fechapago  = :fechapago,
                   penalidad  = :penalidad,
                   medio      = :medio
             WHERE idcontrato = :idcontrato
               AND numcuota   = :numcuota";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':fechapago' => $fecha,
    ':penalidad' => $penalidad,
    ':medio' => $medio,
    ':idcontrato' => $id,
    ':numcuota' => $num,
  ]);

  if ($stmt->rowCount() === 0) {
    // Tal vez ya estaba pagada o datos incorrectos
    echo json_encode(['success' => false, 'error' => 'No se actualizó ningún registro']);
  } else {
    echo json_encode(['success' => true]);
  }
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Error BD: ' . $e->getMessage()]);
}
