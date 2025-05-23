<?php
// Archivo: app/controller/contrato.controller.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../model/Conexion.php';
require_once __DIR__ . '/../model/Contrato.php';

$db = (new Conexion())->conectar();
if (!$db)
  exit;

$model = new Contrato($db);
$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    // GET con id / ?id=123
    if (!empty($_GET['id'])) {
      $c = $model->getById((int) $_GET['id']);
      echo json_encode($c);
    } elseif (!empty($_GET['beneficiario'])) {
      $arr = $model->getAll((int) $_GET['beneficiario']);
      echo json_encode($arr);
    } else {
      $arr = $model->getAll();
      echo json_encode($arr);
    }

  } elseif ($method === 'POST') {
    // Crear nuevo contrato
    $data = json_decode(file_get_contents('php://input'), true);
    // Validaciones básicas
    if (
      empty($data['idbeneficiario']) || empty($data['monto'])
      || empty($data['interes']) || empty($data['fechainicio'])
      || empty($data['numcuotas'])
    ) {
      throw new Exception('Faltan datos obligatorios');
    }

    $newId = $model->create($data);
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
