<?php
// No mostrar errores como HTML:
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../model/Conexion.php';
require_once __DIR__ . '/../model/Beneficiario.php';

$db = (new Conexion())->conectar();
if (!$db)
  exit; // Conexion envía ya JSON y 500

$model = new Beneficiario($db);

$method = $_SERVER['REQUEST_METHOD'];

try {
  if ($method === 'GET') {
    if (!empty($_GET['id'])) {
      $b = $model->getById((int) $_GET['id']);
      echo json_encode($b);
    } else {
      $all = $model->getAll();
      echo json_encode($all);
    }

  } elseif ($method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    // Validaciones básicas
    if (empty($payload['apellidos']) || empty($payload['nombres']) || !preg_match('/^\d{8}$/', $payload['dni'])) {
      throw new Exception('Datos inválidos');
    }

    if (!empty($payload['id'])) {
      // Actualizar
      $ok = $model->update($payload);
      echo json_encode(['success' => (bool) $ok]);
    } else {
      // Crear
      $newId = $model->create($payload);
      echo json_encode(['success' => true, 'id' => $newId]);
    }

  } else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
  }

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
