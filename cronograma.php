<?php
// cronograma.php
require_once __DIR__ . '/app/model/Conexion.php';

$conexion = new Conexion();
$pdo = $conexion->conectar();
if (!$pdo)
  die('No hay conexión a BD');
if (!isset($_GET['id']) || !ctype_digit($_GET['id']))
  die('Contrato inválido');
$idContrato = (int) $_GET['id'];

// Ruta al controlador de pagos
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$pagosUrl = $basePath . '/app/controller/pagos.php';

// Datos del contrato
$stmt = $pdo->prepare(
  "SELECT c.*, b.apellidos, b.nombres FROM contratos c JOIN beneficiarios b USING(idbeneficiario) WHERE idcontrato = ?"
);
$stmt->execute([$idContrato]);
$contrato = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contrato)
  die('Contrato no encontrado');

// Pagos
$stmt = $pdo->prepare(
  "SELECT p.numcuota, DATE_ADD(c.fechainicio, INTERVAL (p.numcuota-1) MONTH) AS fecha_programada,
            p.monto, p.penalidad, p.fechapago, p.medio
              FROM pagos p JOIN contratos c USING(idcontrato)
              WHERE p.idcontrato = ? ORDER BY p.numcuota"
);
$stmt->execute([$idContrato]);
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Cronograma #<?= $idContrato ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container py-4">
    <h2>Cronograma de Pagos — Contrato #<?= $idContrato ?> <small
        class="text-muted"><?= htmlspecialchars($contrato['apellidos'] . ' ' . $contrato['nombres']) ?></small></h2>

    <?php if (count($pagos) === 0): ?>
      <div class="alert alert-info mt-3">No hay pagos realizados para este contrato.</div>
    <?php else: ?>
      <table class="table table-striped mt-3">
        <thead>
          <tr>
            <th>Cuota</th>
            <th>Fecha Prog.</th>
            <th>Monto</th>
            <th>Penalidad</th>
            <th>Medio</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pagos as $p):
            $pagada = !empty($p['fechapago']);
            $medio = $p['medio'] === 'EFC' ? 'Efectivo' : ($p['medio'] === 'DEP' ? 'Depósito' : '-');
            ?>
            <tr class="<?= $pagada ? 'table-success' : '' ?>">
              <td><?= $p['numcuota'] ?></td>
              <td><?= date('d/m/Y', strtotime($p['fecha_programada'])) ?></td>
              <td><?= number_format($p['monto'], 2) ?></td>
              <td><?= number_format($p['penalidad'], 2) ?></td>
              <td><?= $medio ?></td>
              <td><?= $pagada ? date('d/m/Y', strtotime($p['fechapago'])) : '-' ?></td>
              <td>
                <?= $pagada ? '<span class="badge bg-success">PAGADA</span>' : '<span class="badge bg-warning text-dark">PENDIENTE</span>' ?>
              </td>
              <td>
                <?php if (!$pagada): ?>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalPago"
                    data-num="<?= $p['numcuota'] ?>" data-fecha="<?= date('Y-m-d', strtotime($p['fecha_programada'])) ?>"
                    data-monto="<?= $p['monto'] ?>">Pagar</button>
                <?php else: ?>
                  <button class="btn btn-secondary btn-sm" disabled>Pagada</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- Modal -->
    <div class="modal fade" id="modalPago" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5>Registrar Pago</h5><button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="numCuota">
            <div class="mb-3"><label>Fecha Prog.</label><input readonly id="fechaProg" class="form-control"></div>
            <div class="mb-3"><label>Monto</label><input readonly id="montoCuota" class="form-control"></div>
            <div class="mb-3"><label>Penalidad</label><input readonly id="penalidad" class="form-control"></div>
            <div class="mb-3"><label>Fecha Pago</label><input type="date" id="fechaPago" class="form-control"></div>
            <div class="mb-3">
              <label for="medioPago" class="form-label">Medio de Pago</label>
              <select id="medioPago" class="form-select">
                <option value="EFC">Efectivo</option>
                <option value="DEP">Depósito</option>
              </select>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button
              id="btnSave" class="btn btn-success">Guardar</button></div>
        </div>
      </div>
    </div>

  </div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const baseUrl = window.location.origin + '<?= $pagosUrl ?>';
    const modal = document.getElementById('modalPago');

    modal.addEventListener('show.bs.modal', e => {
      const btn = e.relatedTarget;
      const num = btn.dataset.num;
      const fechaStr = btn.dataset.fecha;
      const monto = parseFloat(btn.dataset.monto);
      const [y, m, d] = fechaStr.split('-').map(Number);
      const prog = new Date(y, m - 1, d);
      document.getElementById('numCuota').value = num;
      document.getElementById('fechaProg').value = prog.toLocaleDateString('es-PE');
      document.getElementById('montoCuota').value = monto.toFixed(2);
      const hoy = new Date();
      document.getElementById('penalidad').value = (hoy > prog ? monto * 0.10 : 0).toFixed(2);
      document.getElementById('fechaPago').value = hoy.toISOString().slice(0, 10);
      document.getElementById('medioPago').value = 'EFC';
    });

    document.getElementById('btnSave').addEventListener('click', () => {
      const id = <?= $idContrato ?>;
      const num = document.getElementById('numCuota').value;
      const monto = document.getElementById('montoCuota').value;
      const pen = document.getElementById('penalidad').value;
      const medio = document.getElementById('medioPago').value;
      const fechaP = document.getElementById('fechaPago').value;

      Swal.fire({
        title: `¿Seguro de registrar pago?`,
        html: `
      Cuota <strong>${num}</strong><br>
      Monto: <strong>S/ ${parseFloat(monto).toFixed(2)}</strong><br>
      ${pen > 0 ? `Penalidad: <strong>S/ ${parseFloat(pen).toFixed(2)}</strong><br>` : ''}
      Medio: <strong>${medio === 'EFC' ? 'Efectivo' : 'Depósito'}</strong>
    `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, pagar',
        cancelButtonText: 'No, cancelar'
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
          const res = await fetch(baseUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-HTTP-Method-Override': 'PUT'
            },
            body: JSON.stringify({ idcontrato: id, numcuota: num, medio: medio, penalidad: parseFloat(pen), fechapago: fechaP })
          });

          if (!res.ok) {
            return Swal.fire('Error HTTP', `Código ${res.status}`, 'error');
          }
          const data = await res.json();
          if (!data.success) {
            return Swal.fire('Error al guardar', data.error, 'error');
          }

          await Swal.fire('¡Listo!', 'Pago registrado correctamente.', 'success');
          bootstrap.Modal.getInstance(modal).hide();
          location.reload();

        } catch (e) {
          console.error(e);
          Swal.fire('Error', 'Ocurrió un problema al guardar.', 'error');
        }
      });
    });
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>

</html>