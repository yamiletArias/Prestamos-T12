<?php
// app/controller/pagos.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../model/Conexion.php';

$db = (new Conexion())->conectar();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'No hay conexión a BD']);
    exit;
}

// Detectar método real (soporta override)
$method = $_SERVER['REQUEST_METHOD'];
if ($method==='POST'
    && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])
    && strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])==='PUT') {
    $method = 'PUT';
}

// 1) LISTAR PAGOS: GET ?contrato=ID
if ($method === 'GET' && isset($_GET['contrato']) && !isset($_GET['estadisticas'])) {
    $id = (int)$_GET['contrato'];
    $stmt = $db->prepare(
        "SELECT 
            p.idpago,
            p.numcuota,
            DATE_ADD(c.fechainicio, INTERVAL (p.numcuota-1) MONTH) AS fecha_programada,
            p.fechapago,
            p.monto,
            p.penalidad,
            p.medio
         FROM pagos p
         JOIN contratos c USING(idcontrato)
         WHERE p.idcontrato = ?
         ORDER BY p.numcuota"
    );
    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// 2) ESTADÍSTICAS: GET ?estadisticas=1&contrato=ID
if ($method === 'GET' && isset($_GET['estadisticas'], $_GET['contrato'])) {
    $id = (int)$_GET['contrato'];

    // Cuotas pagadas
    $c = $db->prepare("SELECT COUNT(*) AS cnt FROM pagos WHERE idcontrato=? AND fechapago IS NOT NULL");
    $c->execute([$id]); $pagadas = (int)$c->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Cuotas pendientes
    $c = $db->prepare("SELECT COUNT(*) AS cnt FROM pagos WHERE idcontrato=? AND fechapago IS NULL");
    $c->execute([$id]); $pendientes = (int)$c->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Total pagado (monto + penalidad)
    $c = $db->prepare("SELECT SUM(monto+penalidad) AS total FROM pagos WHERE idcontrato=? AND fechapago IS NOT NULL");
    $c->execute([$id]); $total = (float)$c->fetch(PDO::FETCH_ASSOC)['total'];

    // Total penalidades
    $c = $db->prepare("SELECT SUM(penalidad) AS pen FROM pagos WHERE idcontrato=? AND fechapago IS NOT NULL");
    $c->execute([$id]); $pen = (float)$c->fetch(PDO::FETCH_ASSOC)['pen'];

    echo json_encode([
        'cuotas_pagadas'    => $pagadas,
        'cuotas_pendientes' => $pendientes,
        'total_pagado'      => round($total,2),
        'total_penalidades' => round($pen,2),
    ]);
    exit;
}

// 3) REGISTRAR PAGO: PUT
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validar
    foreach (['idcontrato','numcuota','fechapago','penalidad','medio'] as $f) {
        if (!isset($data[$f])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'error'=>"Falta campo $f"]);
            exit;
        }
    }

    $id   = (int)$data['idcontrato'];
    $num  = (int)$data['numcuota'];
    $fch  = DateTime::createFromFormat('Y-m-d', $data['fechapago']);
    if (!$fch) {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Formato de fecha inválido']);
        exit;
    }
    $fecha     = $fch->format('Y-m-d H:i:s');
    $penalidad = (float)$data['penalidad'];
    $medio     = strtoupper(substr($data['medio'],0,3)); // EFC o DEP

    try {
        $stmt = $db->prepare(
            "UPDATE pagos SET
                 fechapago = :fechapago,
                 medio     = :medio,
                 penalidad = :penalidad
             WHERE idcontrato = :idc AND numcuota = :num"
        );
        $stmt->execute([
            ':fechapago'=> $fecha,
            ':medio'    => $medio,
            ':penalidad'=> $penalidad,
            ':idc'      => $id,
            ':num'      => $num,
        ]);

        if ($stmt->rowCount() === 0) {
            echo json_encode(['success'=>false,'error'=>'No se actualizó ningún registro']);
        } else {
            echo json_encode(['success'=>true]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Error BD: '.$e->getMessage()]);
    }
    exit;
}

// Método no permitido
http_response_code(405);
echo json_encode(['success'=>false,'error'=>'Método no permitido']);
