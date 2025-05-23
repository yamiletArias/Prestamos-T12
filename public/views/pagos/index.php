<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Cronograma de Pagos</title>
</head>
<body>

  <div class="container mt-4">
    <h3>Cronograma de pagos</h3>
    <hr>
    <div>
      <table class="table table-bordered" id="tabla-pagos">
        <thead>
          <tr>
            <th>Item</th>
            <th>Fecha pago</th>
            <th>Interés</th>
            <th>Abono capital</th>
            <th>Valor cuota</th>
            <th>Saldo capital</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", async () => {
      async function obtenerCronograma() {
        const params = new URLSearchParams();
        params.append("operation", "creaCronograma");
        params.append("fechaRecibida", "2025-10-10");
        params.append("monto", 3000);
        params.append("tasa", 5);
        params.append("numeroCuotas", 12);

        const response = await fetch(`../../../app/controller/pago.controller.php?${params}`, { method: 'GET' });
        return await response.text();
      }

      async function renderCronograma() {
        const tabla = document.querySelector("#tabla-pagos tbody");
        tabla.innerHTML = await obtenerCronograma();
      }

      renderCronograma();
    });
  </script>

</body>
</html>
