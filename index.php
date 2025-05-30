<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Préstamos - API REST</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <style>
    .navbar-brand {
      font-weight: bold;
    }

    .section-title {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 15px;
      border-radius: 8px 8px 0 0;
      margin-bottom: 0;
    }

    .table-container {
      background: white;
      border-radius: 0 0 8px 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
    }

    .main-content {
      background-color: #f8f9fa;
      min-height: calc(100vh - 76px);
      padding: 20px 0;
    }

    .loading {
      text-align: center;
      padding: 50px;
    }

    .modal-header {
      border-bottom: 1px solid #dee2e6;
    }

    .form-floating>label {
      padding: 1rem 0.75rem;
      pointer-events: none;
    }

    .alert-info {
      background-color: #d1ecf1;
      border-color: #bee5eb;
      color: #0c5460;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-coins me-2"></i>
        Sistema de Préstamos
      </a>
      <div class="navbar-nav ms-auto">
        <a class="nav-link active" href="#beneficiarios" onclick="mostrarSeccion('beneficiarios')">
          <i class="fas fa-users me-1"></i> Beneficiarios
        </a>
        <a class="nav-link" href="#contratos" onclick="mostrarSeccion('contratos')">
          <i class="fas fa-file-contract me-1"></i> Contratos
        </a>
        <a class="nav-link" href="#pagos" onclick="mostrarSeccion('pagos')">
          <i class="fas fa-credit-card me-1"></i> Pagos
        </a>
      </div>
    </div>
  </nav>

  <div class="main-content">
    <div class="container">
      <!-- Sección Beneficiarios -->
      <section id="beneficiarios" class="seccion">
        <div class="table-container">
          <h3 class="section-title">
            <i class="fas fa-users me-2"></i>
            Beneficiarios
            <button class="btn btn-light btn-sm float-end" onclick="mostrarModalBeneficiario()">
              <i class="fas fa-plus me-1"></i> Nuevo Beneficiario
            </button>
          </h3>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Apellidos</th>
                  <th>Nombres</th>
                  <th>DNI</th>
                  <th>Teléfono</th>
                  <th>Dirección</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tablaBeneficiarios">
                <tr>
                  <td colspan="7" class="loading">Cargando beneficiarios...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Sección Contratos -->
      <section id="contratos" class="seccion" style="display:none;">
        <div class="table-container">
          <h3 class="section-title">
            <i class="fas fa-file-contract me-2"></i>
            Contratos
            <button class="btn btn-light btn-sm float-end" onclick="mostrarModalContrato()">
              <i class="fas fa-plus me-1"></i> Nuevo Contrato
            </button>
          </h3>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>ID</th>
                  <th>Beneficiario</th>
                  <th>Monto</th>
                  <th>Interés</th>
                  <th>Fecha Inicio</th>
                  <th>Cuotas</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tablaContratos">
                <tr>
                  <td colspan="8" class="loading">Cargando contratos...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Sección Pagos -->
      <section id="pagos" class="seccion" style="display:none;">
        <div class="table-container">
          <h3 class="section-title">
            <i class="fas fa-credit-card me-2"></i>
            Pagos del Contrato
            <select id="selectContrato" class="form-select"
              style="width: auto; display: inline-block; margin-left: 15px;" onchange="cargarPagos()">
              <option value="">Seleccionar contrato...</option>
            </select>
          </h3>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <!-- <th>Cuota</th>
                  <th>Fecha Programada</th>
                  <th>Fecha Pago</th>
                  <th>Monto</th>
                  <th>Penalidad</th>
                  <th>Estado</th>
                  <th>Acciones</th> -->
                </tr>
              </thead>
              <tbody id="tablaPagos">
                <tr>
                  <td colspan="8" class="text-center">Seleccione un contrato</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Estadísticas -->
          <div id="estadisticasPagos" class="row mt-3 p-3 bg-light" style="display:none;">
            <!-- Se llenará dinámicamente -->
          </div>
          <div id="cronogramaContrato" class="mt-4"></div>
        </div>
      </section>
    </div>
  </div>

  <!-- Modal Beneficiario -->
  <div class="modal fade" id="modalBeneficiario" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-user me-2"></i>
            <span id="tituloModalBeneficiario">Nuevo Beneficiario</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formBeneficiario">
            <input type="hidden" id="idBeneficiario" name="id">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                  <label>Apellidos *</label>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" class="form-control" id="nombres" name="nombres" required>
                  <label>Nombres *</label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="text" class="form-control" id="dni" name="dni" pattern="[0-9]{8}" maxlength="8" required>
                  <label>DNI *</label>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="tel" class="form-control" id="telefono" name="telefono">
                  <label>Teléfono</label>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <div class="form-floating">
                <textarea class="form-control" id="direccion" name="direccion" style="height: 80px"></textarea>
                <label>Dirección</label>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="guardarBeneficiario()">
            <i class="fas fa-save me-1"></i> Guardar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Contrato -->
  <div class="modal fade" id="modalContrato" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-file-contract me-2"></i>
            Nuevo Contrato
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="formContrato">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Beneficiario *</label>
                <select class="form-select" id="selectBeneficiario" required>
                  <option value="">Seleccionar beneficiario...</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <div class="form-floating">
                  <input type="number" class="form-control" id="monto" step="0.01" min="100" required>
                  <label>Monto del Préstamo (S/) *</label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 mb-3">
                <div class="form-floating">
                  <input type="number" class="form-control" id="interes" step="0.01" min="0" max="100" required>
                  <label>Interés (%) *</label>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="form-floating">
                  <input type="number" class="form-control" id="numcuotas" min="1" max="60" required>
                  <label>Número de Cuotas *</label>
                </div>
              </div>
              <div class="col-md-4 mb-3">
                <div class="form-floating">
                  <input type="date" class="form-control" id="fechainicio" required>
                  <label>Fecha de Inicio *</label>
                </div>
              </div>
            </div>
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              <strong>Información:</strong> Se generará automáticamente el cronograma de pagos mensual.
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="guardarContrato()">
            <i class="fas fa-save me-1"></i> Crear Contrato
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    const API_BASE = './app/controller/';
    const pagosUrl = API_BASE + 'pagos.php';
    // Funciones de navegación
    function mostrarSeccion(seccion) {
      document.querySelectorAll('.seccion').forEach(s => s.style.display = 'none');
      document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

      document.getElementById(seccion).style.display = 'block';
      document.querySelector(`[href="#${seccion}"]`).classList.add('active');

      if (seccion === 'beneficiarios') cargarBeneficiarios();
      else if (seccion === 'contratos') cargarContratos();
      else if (seccion === 'pagos') cargarSelectContratos();
    }

    // Cargar datos
    async function cargarBeneficiarios() {
      const tbody = document.getElementById('tablaBeneficiarios');
      tbody.innerHTML = '<tr><td colspan="7" class="loading">Cargando beneficiarios...</td></tr>';
      try {
        const res = await fetch(API_BASE + 'beneficiario.controller.php');
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const datos = await res.json();
        if (datos.error) throw new Error(datos.error);
        if (!Array.isArray(datos)) throw new Error('Respuesta inesperada');

        tbody.innerHTML = datos.length > 0
          ? datos.map(b => `
            <tr>
              <td>${b.idbeneficiario}</td>
              <td>${b.apellidos}</td>
              <td>${b.nombres}</td>
              <td>${b.dni}</td>
              <td>${b.telefono || '-'}</td>
              <td>${b.direccion || 'Sin direccion'}</td>
              <td>
                <button class="btn btn-info btn-sm" onclick="verContratosBeneficiario(${b.idbeneficiario})" title="Ver contratos">
                  <i class="fas fa-eye"></i>
                </button>
              </td>
            </tr>
          `).join('')
          : '<tr><td colspan="7" class="text-center text-muted">No hay beneficiarios registrados</td></tr>';
      } catch (e) {
        console.error('Error al cargar beneficiarios:', e);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar beneficiarios</td></tr>';
      }
    }

    async function cargarContratos() {
      try {
        // Si hay un filtro global, lo pasamos como querystring
        const filtro = window._filtroBeneficiario
          ? `?beneficiario=${window._filtroBeneficiario}`
          : '';
        const res = await fetch(API_BASE + 'contrato.controller.php' + filtro);
        const contratos = await res.json();

        const tbody = document.getElementById('tablaContratos');
        tbody.innerHTML = '';

        if (contratos.length === 0) {
          tbody.innerHTML = `<tr>
        <td colspan="8" class="text-center text-muted">No hay contratos</td>
      </tr>`;
          return;
        }

        contratos.forEach(c => {
          const estado = c.estado === 'ACT'
            ? '<span class="badge bg-success">ACTIVO</span>'
            : '<span class="badge bg-secondary">FINALIZADO</span>';

          tbody.innerHTML += `
        <tr>
          <td>${c.idcontrato}</td>
          <td>${c.beneficiario_nombre}</td>
          <td>S/ ${parseFloat(c.monto).toLocaleString('es-PE', { minimumFractionDigits: 2 })}</td>
          <td>${c.interes}%</td>
          <td>${(c.fechainicio)}</td>
          <td>${c.numcuotas}</td>
          <td>${estado}</td>
          <td>
            <button title="Ver cronograma" class="btn btn-info btn-sm" onclick="verCronograma(${c.idcontrato})">
              <i class="fas fa-calendar-alt"></i>
            </button>

          </td>
        </tr>
      `;
        });

        // Opcional: limpiar filtro tras usarlo
        // window._filtroBeneficiario = null;

      } catch (error) {
        console.error('Error al cargar contratos:', error);
      }
    }
    async function cargarSelectContratos() {
      try {
        const response = await fetch(API_BASE + 'contrato.controller.php');
        const contratos = await response.json();

        const select = document.getElementById('selectContrato');
        select.innerHTML = '<option value="">Seleccionar contrato...</option>';

        contratos.forEach(c => {
          select.innerHTML += `
                        <option value="${c.idcontrato}">
                            Contrato #${c.idcontrato} - ${c.beneficiario_nombre} - S/ ${parseFloat(c.monto).toLocaleString('es-PE')}
                        </option>
                    `;
        });
      } catch (error) {
        console.error('Error al cargar contratos:', error);
      }
    }

    async function cargarPagos() {
      const contratoId = document.getElementById('selectContrato').value;
      if (!contratoId) return;

      try {
        const [pagosResponse, estadisticasResponse] = await Promise.all([
          fetch(API_BASE + `pagos.php?contrato=${contratoId}`),
          fetch(API_BASE + `pagos.php?estadisticas=1&contrato=${contratoId}`)
        ]);

        const pagos = await pagosResponse.json();
        const estadisticas = await estadisticasResponse.json();

        const tbody = document.getElementById('tablaPagos');
        tbody.innerHTML = '';
        pagos.forEach(p => {
          // Datos bien nombrados
          const fechaProg = p.fecha_programada;
          const fechaPago = p.fechapago
            ? new Date(p.fechapago).toLocaleDateString('es-PE')
            : '-';
          const monto = parseFloat(p.monto).toFixed(2);
          const medio = p.medio
            ? (p.medio === 'EFC' ? 'EFECTIVO' : 'DEPÓSITO')
            : '-';
          const estado = p.fechapago
            ? '<span class="badge bg-success">PAGADO</span>'
            : '<span class="badge bg-danger">PENDIENTE</span>';
          const claseFila = p.fechapago ? 'table-success' : '';

          tbody.innerHTML += `
              <tr class="${claseFila}">
                <td>${p.numcuota}</td>
                <td>${new Date(fechaProg).toLocaleDateString('es-PE')}</td>
                <td>${fechaPago}</td>
                <td>S/ ${monto}</td>
                <td>S/ ${parseFloat(p.penalidad).toFixed(2)}</td>
                <td>${medio}</td>
                <td>${estado}</td>
                <td>
                  ${!p.fechapago
                    ? `<button class="btn btn-success btn-sm"
                        onclick="registrarPago(${contratoId}, ${p.numcuota}, '${fechaProg}', ${monto})">
                      Pagar
                    </button>`
                    : `<button class="btn btn-info btn-sm" onclick="verDetallePago(${p.idpago})">Ver</button>`
                  }
                </td>
              </tr>
            `;
        });

        // Mostrar estadísticas
        const divEstadisticas = document.getElementById('estadisticasPagos');
        divEstadisticas.style.display = 'block';
        divEstadisticas.innerHTML = `
          <div class="row row-cols-1 row-cols-md-4 g-3">
            <div class="col">
              <div class="card text-center border-primary">
                <div class="card-body">
                  <h6 class="card-title">Cuotas Pagadas</h6>
                  <h4 class="text-primary">${estadisticas.cuotas_pagadas}</h4>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card text-center border-danger">
                <div class="card-body">
                  <h6 class="card-title">Cuotas Pendientes</h6>
                  <h4 class="text-danger">${estadisticas.cuotas_pendientes}</h4>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card text-center border-success">
                <div class="card-body">
                  <h6 class="card-title">Total Pagado</h6>
                  <h4 class="text-success">S/ ${parseFloat(estadisticas.total_pagado || 0).toFixed(2)}</h4>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card text-center border-warning">
                <div class="card-body">
                  <h6 class="card-title">Penalidades</h6>
                  <h4 class="text-warning">S/ ${parseFloat(estadisticas.total_penalidades || 0).toFixed(2)}</h4>
                </div>
              </div>
            </div>
          </div>
        `;

      } catch (error) {
        console.error('Error al cargar pagos:', error);
      }
      /* try {
        const cronogramaRes = await fetch(`cronograma.php?id=${contratoId}`);
        if (!cronogramaRes.ok) throw new Error(`HTTP ${cronogramaRes.status}`);
        const cronogramaHtml = await cronogramaRes.text();
        document.getElementById('cronogramaContrato').innerHTML = cronogramaHtml;
      } catch (e) {
        console.error('Error al cargar cronograma:', e);
        document.getElementById('cronogramaContrato').innerHTML =
          `<div class="alert alert-warning">No se pudo cargar el cronograma.</div>`;
      } */
    }

    // Funciones de acción (placeholders)
    function mostrarModalBeneficiario() {
      alert('Aquí abriríamos un modal para crear/editar beneficiario');
    }

    function editarBeneficiario(id) {
      alert('Editar beneficiario ID: ' + id);
    }

    function verContratosBeneficiario(benId) {
      // 1) Marco la pestaña de "Contratos"
      mostrarSeccion('contratos');

      // 2) Guardo el id en un global para filtrar
      window._filtroBeneficiario = benId;

      // 3) Llamo a cargarContratos, que usará ese filtro
      cargarContratos();
    }
    let modalContrato;
    let _lastContractBeneficiario = null;
    async function mostrarModalContrato() {
      const form = document.getElementById('formContrato');
      form.reset();
      form.classList.remove('was-validated');

      // 1) Llenar el select de beneficiarios
      const sel = document.getElementById('selectBeneficiario');
      sel.innerHTML = '<option value="">Seleccionar beneficiario...</option>';

      try {
        const res = await fetch(API_BASE + 'beneficiario.controller.php');
        const datos = await res.json();
        datos.forEach(b => {
          sel.innerHTML += `
        <option value="${b.idbeneficiario}">
          ${b.apellidos} ${b.nombres}
        </option>`;
        });
      } catch (e) {
        console.error('No se pudo cargar beneficiarios para el contrato', e);
      }

      // 2) Abrir modal
      modalContrato.show();
    }
    document.addEventListener('DOMContentLoaded', () => {
      // ya tienes el de beneficiario; añadimos éste:
      modalContrato = new bootstrap.Modal(document.getElementById('modalContrato'));
    });
    function verCronograma(id) {
      // Redirige a la página de cronograma, pasando el id del contrato
      window.location.href = `cronograma.php?id=${id}`;
    }

    function verPagosContrato(id) {
      mostrarSeccion('pagos');
      document.getElementById('selectContrato').value = id;
      cargarPagos();
    }

    async function registrarPago(contratoId, numCuota, fechaProg, monto) {
      const hoy = new Date();
      const hoyStr = hoy.toISOString().slice(0, 10);
      // Calcular penalidad automática
      const penalidad = hoy > new Date(fechaProg)
        ? parseFloat((monto * 0.10).toFixed(2))
        : 0;

      const { value: form } = await Swal.fire({
        title: `Pagar cuota ${numCuota}`,
        html:
          `<p>Monto: <strong>S/ ${monto}</strong></p>
       <p>Fecha programada: <strong>${new Date(fechaProg).toLocaleDateString('es-PE')}</strong></p>
       <input id="swal-fecha" type="date" class="swal2-input" value="${hoyStr}" />
       <input id="swal-penalidad" type="number" min="0" step="0.01"
              class="swal2-input" value="${penalidad.toFixed(2)}"
              placeholder="Penalidad (S/)" />`,
        focusConfirm: false,
        showCancelButton: true,
        preConfirm: () => ({
          fecha: document.getElementById('swal-fecha').value,
          penalidad: parseFloat(document.getElementById('swal-penalidad').value) || 0
        })
      });

      if (!form) return; // Canceló

      try {
        const res = await fetch(pagosUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-HTTP-Method-Override': 'PUT'
          },
          body: JSON.stringify({
            idcontrato: contratoId,
            numcuota: numCuota,
            fechapago: form.fecha,
            penalidad: form.penalidad,
            medio: 'EFC'    // o nếu quieres permitir cambiar, añádelo al modal
          })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error);
        await Swal.fire('¡Listo!', 'Pago registrado.', 'success');
        cargarPagos();
      } catch (e) {
        Swal.fire('Error', e.message, 'error');
      }
    }

    function verDetallePago(id) {
      alert('Ver detalle del pago ID: ' + id);
    }

    // Cargar datos iniciales
    document.addEventListener('DOMContentLoaded', function () {
      cargarBeneficiarios();
    });
    let modalBeneficiario;

    document.addEventListener('DOMContentLoaded', () => {
      // Inicializamos la instancia del modal
      const el = document.getElementById('modalBeneficiario');
      modalBeneficiario = new bootstrap.Modal(el);
    });

    // Reemplaza tu función:
    function mostrarModalBeneficiario(id = null) {
      // Reseteamos el formulario
      const form = document.getElementById('formBeneficiario');
      form.reset();
      document.getElementById('idBeneficiario').value = '';
      document.getElementById('tituloModalBeneficiario').textContent = 'Nuevo Beneficiario';

      if (id) {
        // Si viene un ID, carga datos existentes para editar (opcional)
        fetch(`${API_BASE}beneficiario.controller.php?id=${id}`)
          .then(res => res.json())
          .then(data => {
            document.getElementById('idBeneficiario').value = data.idbeneficiario;
            document.getElementById('apellidos').value = data.apellidos;
            document.getElementById('nombres').value = data.nombres;
            document.getElementById('dni').value = data.dni;
            document.getElementById('telefono').value = data.telefono;
            document.getElementById('direccion').value = data.direccion;
            document.getElementById('tituloModalBeneficiario').textContent = 'Editar Beneficiario';
            modalBeneficiario.show();
          })
          .catch(err => alert('Error al cargar beneficiario'));
      } else {
        // Modal en blanco para nuevo
        modalBeneficiario.show();
      }
    }

    function guardarBeneficiario() {
      const form = document.getElementById('formBeneficiario');
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      // Usamos SweetAlert2 en lugar de confirm()
      Swal.fire({
        title: '¿Está segura?',
        text: '¿Desea guardar este beneficiario?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ¡guardar!',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (!result.isConfirmed) return;  // Si cancela, salimos

        // Si confirma, hacemos el fetch
        const datos = {
          id: document.getElementById('idBeneficiario').value,
          apellidos: document.getElementById('apellidos').value,
          nombres: document.getElementById('nombres').value,
          dni: document.getElementById('dni').value,
          telefono: document.getElementById('telefono').value,
          direccion: document.getElementById('direccion').value
        };


        fetch(API_BASE + 'beneficiario.controller.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(datos)
        })
          .then(res => res.json())
          .then(res => {
            if (res.success) {
              modalBeneficiario.hide();
              cargarBeneficiarios();
              // Mensaje de éxito con SweetAlert2
              Swal.fire({
                icon: 'success',
                title: 'Guardado',
                text: 'Beneficiario registrado exitosamente',
                timer: 1500,
                showConfirmButton: false
              });
            } else {
              Swal.fire('Error', res.error, 'error');
            }
          })
          .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un problema en la solicitud', 'error');
          });
      });
    }

    async function guardarContrato() {
      const form = document.getElementById('formContrato');
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }

      // Recogemos datos del formulario
      const datos = {
        idbeneficiario: document.getElementById('selectBeneficiario').value,
        monto: document.getElementById('monto').value,
        interes: document.getElementById('interes').value,
        fechainicio: document.getElementById('fechainicio').value,
        numcuotas: document.getElementById('numcuotas').value
      };

      // Chequeo en el backend si ya hay contratos activos
      const resp = await fetch(
        `${API_BASE}contrato.controller.php?check=1&beneficiario=${datos.idbeneficiario}`
      );
      const { count } = await resp.json();

      // Función interna para enviar el POST
      const doPost = () => {
        fetch(API_BASE + 'contrato.controller.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(datos)
        })
          .then(r => r.json())
          .then(res => {
            if (res.success) {
              modalContrato.hide();
              cargarContratos();
              Swal.fire({
                icon: 'success',
                title: 'Contrato creado',
                showConfirmButton: false,
                timer: 1400
              });
            } else {
              Swal.fire('Error', res.error, 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Problema de conexión', 'error');
          });
      };

      // Si ya existe al menos un contrato activo, pedimos confirmación
      if (count > 0) {
        Swal.fire({
          title: '¡Atención!',
          text: 'Este beneficiario ya tiene un contrato activo. ¿Deseas continuar?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, continuar',
          cancelButtonText: 'Cancelar'
        }).then(({ isConfirmed }) => {
          if (isConfirmed) doPost();
        });
      } else {
        doPost();
      }
    }

  </script>
</body>

</html>