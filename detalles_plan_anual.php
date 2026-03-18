<?php
    // El ID del plan se obtiene de la URL, por ejemplo: detalles_plan_anual.php?id=123
    $idPlan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control SGC - Detalle Plan Anual</title>

    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <div class="container my-auto">
                        <!-- Contenedor donde se renderiza el detalle del plan -->
                        <input type="hidden" id="id-usr-aprobo-hidden" value="<?php echo htmlspecialchars($_COOKIE['id_usuarioL'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" id="no-empleado-hidden" value="<?php echo htmlspecialchars($_COOKIE['noEmpleado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div id="detalle-plan-root"></div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white d-print-none">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MESS <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button -->
    <a class="scroll-to-top rounded d-print-none" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="modalAprobacionActividad" tabindex="-1" aria-labelledby="modalAprobacionActividadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAprobacionActividadLabel">Actualizar estatus de actividad</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-id-actividad" value="">
                    <input type="hidden" id="modal-num-actividad" value="">
                    <input type="hidden" id="modal-estatus-actividad" value="">
                    <input type="hidden" id="modal-realizado" value="">
                    <div class="mb-2">
                        <small class="text-muted" id="modal-etiqueta-actividad"></small>
                    </div>
                    <label for="modal-observacion-actividad" class="form-label">Observacion</label>
                    <textarea id="modal-observacion-actividad" class="form-control" rows="4" maxlength="999" placeholder="Escribe la observacion"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-guardar-aprobacion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmarRealizado" tabindex="-1" aria-labelledby="modalConfirmarRealizadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarRealizadoLabel">Confirmar realizado</h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-confirmar-id-actividad" value="">
                    <p class="mb-0" id="modal-confirmar-texto">¿Deseas marcar esta actividad como REALIZADO?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-confirmar-realizado">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>

    <script>
    (function() {
        // Elemento donde se inyecta todo el contenido de detalle.
        const root = document.getElementById('detalle-plan-root');
        const idPlan = <?php echo (int)$idPlan; ?>;
        const idUsrAprobo = (document.getElementById('id-usr-aprobo-hidden') || {}).value || '';
        const noEmpleado = (document.getElementById('no-empleado-hidden') || {}).value || '';
        let puedeVerCumplio = false;
        const modalEl = document.getElementById('modalAprobacionActividad');
        const modalAprobacion = modalEl ? new bootstrap.Modal(modalEl) : null;
        const modalConfirmarEl = document.getElementById('modalConfirmarRealizado');
        const modalConfirmarRealizado = modalConfirmarEl ? new bootstrap.Modal(modalConfirmarEl) : null;
        const modalIdActividad = document.getElementById('modal-id-actividad');
        const modalNumActividad = document.getElementById('modal-num-actividad');
        const modalEstatusActividad = document.getElementById('modal-estatus-actividad');
        const modalObservacion = document.getElementById('modal-observacion-actividad');
        const modalEtiquetaActividad = document.getElementById('modal-etiqueta-actividad');
        const modalConfirmarIdActividad = document.getElementById('modal-confirmar-id-actividad');
        const modalConfirmarTexto = document.getElementById('modal-confirmar-texto');
        const btnGuardarAprobacion = document.getElementById('btn-guardar-aprobacion');
        const btnConfirmarRealizado = document.getElementById('btn-confirmar-realizado');
        let ultimoBotonAccion = null;
        let ultimoBotonRealizado = null;

        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                if (ultimoBotonAccion && typeof ultimoBotonAccion.focus === 'function' && document.contains(ultimoBotonAccion)) {
                    ultimoBotonAccion.focus();
                }
            });
        }

        // Escapa texto para evitar inyeccion de HTML.
        function escapeHtml(text) {
            const value = String(text == null ? '' : text);
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Convierte saltos de linea en <br> manteniendo el escape seguro.
        function nl2brSafe(text) {
            return escapeHtml(text).replace(/\n/g, '<br>');
        }

        function renderEstatusActividadBadge(estatusRaw) {
            const estatus = String(estatusRaw || '').trim().toUpperCase();
            const estatusNormalizado = estatus.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const etiqueta = (estatus === '' ? 'EN REVISION' : estatus.replace(/_/g, ' '));

            if (estatusNormalizado === 'CUMPLIO') {
                return '<span class="badge bg bg-success">' + escapeHtml(etiqueta) + '</span>';
            }

            if (estatusNormalizado === 'NO CUMPLIO') {
                return '<span class="badge bg bg-danger text-white">' + escapeHtml(etiqueta) + '</span>';
            }

            if (estatusNormalizado === 'CANCELADO') {
                return '<span class="badge bg bg-dark text-white">' + escapeHtml(etiqueta) + '</span>';
            }

            if (estatusNormalizado === 'EN REVISION' || estatus === '') {
                return '<span class="badge bg bg-info text-dark">' + escapeHtml(etiqueta) + '</span>';
            }

            // INCOMPLETO u otro valor desconocido.
            return '<span class="badge bg bg-warning text-dark">' + escapeHtml(etiqueta) + '</span>';
        }

        function renderRealizadoBadge(realizadoRaw) {
            const realizado = String(realizadoRaw || '').trim().toUpperCase();
            if (realizado === 'REALIZADO') {
                return '<span class="badge bg bg-success">REALIZADO</span>';
            }

            if (realizado === 'NO REALIZADO') {
                return '<span class="badge bg bg-danger text-white">NO REALIZADO</span>';
            }

            if (realizado === 'PENDIENTE') {
                return '<span class="badge bg bg-secondary">PENDIENTE</span>';
            }

            return '<span class="badge bg bg-warning">' + escapeHtml(realizado.replace(/_/g, ' ')) + '</span>';
        }

        // Renderiza errores de carga/validacion en el contenedor principal.
        function renderError(message) {
            root.innerHTML = '<div class="alert alert-danger">' + escapeHtml(message || 'Ocurrio un error al cargar el detalle.') + '</div>';
        }

        function abrirModalEstatusActividad(idActividad, numActividad, estatusActividad, botonOrigen) {
            if (!modalAprobacion) {
                alert('No se pudo abrir el modal.');
                return;
            }

            const estatus = String(estatusActividad || '').toUpperCase();
            const etiqueta = estatus === 'CUMPLIO' ? 'CUMPLIO' : 'NO CUMPLIO';

            modalIdActividad.value = String(idActividad || '');
            modalNumActividad.value = String(numActividad || '');
            modalEstatusActividad.value = estatus;
            modalObservacion.value = '';
            ultimoBotonAccion = botonOrigen || null;
            modalEtiquetaActividad.textContent = 'Actividad ' + String(numActividad || 'S/R') + ' - ' + etiqueta;
            modalAprobacion.show();
            setTimeout(function() {
                modalObservacion.focus();
            }, 120);
        }

        function guardarAprobacionActividad() {
            const idActividad = (modalIdActividad.value || '').trim();
            const estatusActividad = (modalEstatusActividad.value || '').trim();
            const observacion = (modalObservacion.value || '').trim();

            if (!idActividad) {
                alert('No se encontro la actividad.');
                return;
            }
            if (!observacion) {
                alert('La observacion es obligatoria.');
                return;
            }

            btnGuardarAprobacion.disabled = true;

            $.ajax({
                url: 'acciones_detalles_plan.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'guardar_realizado',
                    id_actividad: idActividad,
                    realizado: estatusActividad,
                    observacion: observacion
                },
                success: function(resp) {
                    btnGuardarAprobacion.disabled = false;
                    if (!resp || !resp.success) {
                        alert(resp && resp.error ? resp.error : 'No se pudo guardar el estatus.');
                        return;
                    }

                    const badge = root.querySelector('span.js-estatus-actividad[data-id-actividad="' + String(idActividad).replace(/"/g, '\\"') + '"]');
                    if (badge) {
                        badge.innerHTML = renderEstatusActividadBadge(resp.estatus_actividad || estatusActividad);
                    }

                    if (modalEl && modalEl.contains(document.activeElement) && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }
                    modalAprobacion.hide();
                },
                error: function() {
                    btnGuardarAprobacion.disabled = false;
                    alert('Error de comunicacion al guardar el estatus de la actividad.');
                }
            });
        }

        function abrirModalConfirmarRealizado(idActividad, numActividad, botonOrigen) {
            if (!modalConfirmarRealizado) {
                alert('No se pudo abrir el modal de confirmacion.');
                return;
            }

            modalConfirmarIdActividad.value = String(idActividad || '');
            modalConfirmarTexto.textContent = '¿Deseas marcar la actividad ' + String(numActividad || 'S/R') + ' como REALIZADO?';
            ultimoBotonRealizado = botonOrigen || null;
            modalConfirmarRealizado.show();
        }

        function guardarRealizadoActividad(idActividad, botonOrigen) {
            if (!idActividad) {
                alert('No se encontro la actividad.');
                return;
            }

            botonOrigen.disabled = true;

            $.ajax({
                url: 'acciones_detalles_plan.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'guardar_estatus_cumplio',
                    id_actividad: idActividad
                },
                success: function(resp) {
                    botonOrigen.disabled = false;
                    if (!resp || !resp.success) {
                        alert(resp && resp.error ? resp.error : 'No se pudo guardar el realizado.');
                        return;
                    }

                    const badge = root.querySelector('span.js-realizado-actividad[data-id-actividad="' + String(idActividad).replace(/"/g, '\\"') + '"]');
                    if (badge) {
                        badge.innerHTML = renderRealizadoBadge(resp.realizado || 'REALIZADO');
                    }

                    if (modalConfirmarRealizado) {
                        modalConfirmarRealizado.hide();
                    }
                },
                error: function() {
                    botonOrigen.disabled = false;
                    alert('Error de comunicacion al guardar el realizado de la actividad.');
                }
            });
        }

        // Imprime la vista dedicada sin cambiar de URL ni abrir nueva pestana.
        window.imprimirPlanAnualActual = function(id) {
            const planId = Number(id || 0);
            if (!planId) {
                alert('ID de plan invalido para impresion.');
                return;
            }

            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.src = 'imprimir_plan_anual.php?id=' + encodeURIComponent(planId) + '&auto_print=1';

            // La vista de impresion decide cuando llamar a print(), ya con datos renderizados.
            iframe.onload = function() {
                setTimeout(function() {
                    if (iframe && iframe.parentNode) {
                        iframe.parentNode.removeChild(iframe);
                    }
                }, 30000);
            };

            document.body.appendChild(iframe);
        };

        // Construye y pinta el HTML completo del detalle del plan.
        function renderDetalle(data) {
            const plan = data.plan || {};
            const actividadesPorSeccion = data.actividades_por_seccion || {};
            const ordenSecciones = data.orden_secciones || [];
            const seccionesTitulos = data.secciones_titulos || {};
            const folio = data.folio || 'S/R';

            let html = '';
            html += '<div class="border-bottom border-2 border-dark pb-2 mb-3">';
            html += '  <div class="row g-2">';
            html += '    <div class="col-12 text-center"><div class="fw-bold fs-1">Plan Anual SGC</div></div>';
            html += '    <div class="col-6 text-start"><div class="fw-bold fs-2">Folio: <span class="text-primary text-decoration-underline">' + escapeHtml(folio) + '</span></div></div>';
            html += '    <div class="col-6 d-flex justify-content-end gap-2 d-print-none">';
            html += '      <button class="btn btn-outline-primary btn-sm" onclick="imprimirPlanAnualActual(' + encodeURIComponent(idPlan) + ')">Generar PDF</button>';
            html += '      <button class="btn btn-outline-warning btn-sm" onclick="window.location.href=\'detalles_actividades_SGC\'">Volver</button>';
            html += '    </div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="row g-2 mb-3">';
            html += '  <div class="col-md-6"><span class="fw-semibold">Area:</span> ' + escapeHtml(plan.area_text || 'S/R') + '</div>';
            html += '  <div class="col-md-6"><span class="fw-semibold">Presenta:</span> ' + escapeHtml(plan.presenta_text || 'S/R') + '</div>';
            html += '  <div class="col-md-6"><span class="fw-semibold">Aprueba:</span> ' + escapeHtml(plan.aprueba_text || 'S/R') + '</div>';
            html += '  <div class="col-md-6"><span class="fw-semibold">Registra:</span> ' + escapeHtml(plan.registra_text || plan.id_registra || 'S/R') + '</div>';
            html += '  <div class="col-md-6"><span class="fw-semibold">Estatus Rev:</span> ' + escapeHtml(plan.estatus || 'S/R') + '</div>';
            html += '</div>';

            html += '<h6 class="bg-primary border border-dark text-white py-2 px-3 mt-3 mb-0">Objetivo de calidad</h6>';
            html += '<div class="table-responsive mb-3">';
            html += '  <table class="table table-bordered table-sm mb-0"><tbody><tr><td>' + nl2brSafe(plan.titulo_objetivos || 'S/R') + '</td></tr></tbody></table>';
            html += '</div>';

            let hayActividades = false;
            ordenSecciones.forEach(function(clave) {
                const lista = actividadesPorSeccion[clave] || [];
                if (!lista.length) {
                    return;
                }
                hayActividades = true;
                const tituloSeccion = seccionesTitulos[clave] || String(clave || '').toUpperCase();

                html += '<h6 class="bg-primary border border-dark text-white py-2 px-3 mt-3 mb-0">' + escapeHtml(tituloSeccion) + '</h6>';
                html += '<div class="table-responsive mb-3">';
                html += '  <table class="table table-bordered table-sm mb-0">';
                html += '    <thead class="table-light text-center align-middle small">';
                html += '      <tr>';
                html += '        <th rowspan="2">No</th>';
                html += '        <th rowspan="2">Actividad</th>';
                html += '        <th colspan="12">Meses</th>';
                html += '        <th rowspan="2">Responsable</th>';
                html += '        <th rowspan="2">Participantes</th>';
                html += '        <th rowspan="2">Observaciones sobre el cumplimiento</th>';
                html += '        <th rowspan="2">Realizado</th>';
                html += '        <th rowspan="2">Estatus Rev</th>';
                html += '        <th rowspan="2">Acciones</th>';
                html += '      </tr>';
                html += '      <tr>';
                html += '        <th class="text-center">E</th><th class="text-center">F</th><th class="text-center">M</th><th class="text-center">A</th><th class="text-center">M</th><th class="text-center">J</th><th class="text-center">J</th><th class="text-center">A</th><th class="text-center">S</th><th class="text-center">O</th><th class="text-center">N</th><th class="text-center">D</th>';
                html += '      </tr>';
                html += '    </thead>';
                html += '    <tbody>';

                lista.forEach(function(item) {
                    const mesesMarcados = item.meses_marcados || {};
                    html += '<tr>';
                    html += '  <td>' + escapeHtml(item.num_actividad || '') + '</td>';
                    html += '  <td>' + nl2brSafe(item.actividad || '') + '</td>';
                    for (let mes = 1; mes <= 12; mes++) {
                        html += '  <td class="text-center fw-bold">' + (mesesMarcados[String(mes)] || mesesMarcados[mes] ? 'X' : '') + '</td>';
                    }
                    html += '  <td>' + escapeHtml(item.responsable || 'S/R') + '</td>';
                    html += '  <td>' + escapeHtml(item.participantes || 'S/R') + '</td>';
                    html += '  <td>' + nl2brSafe(item.observaciones || '') + '</td>';
                    html += '  <td class="text-center"><span class="js-realizado-actividad" data-id-actividad="' + escapeHtml(String(item.id || '')) + '">' + renderRealizadoBadge(item.realizado || '') + '</span></td>';
                    html += '  <td class="text-center"><span class="js-estatus-actividad" data-id-actividad="' + escapeHtml(String(item.id || '')) + '" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '">' + renderEstatusActividadBadge(item.estatus_actividad) + '</span></td>';
                    html += '  <td class="text-center text-nowrap">';
                    if (puedeVerCumplio) {
                        html += '    <button type="button" class="btn btn-outline-success btn-sm me-1 js-accion-estatus-modal" data-id-actividad="' + escapeHtml(String(item.id || '')) + '" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '" data-estatus-actividad="CUMPLIO" title="Cumplio"><i class="fas fa-check"></i></button>';
                        html += '    <button type="button" class="btn btn-outline-danger btn-sm me-1 js-accion-estatus-modal" data-id-actividad="' + escapeHtml(String(item.id || '')) + '" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '" data-estatus-actividad="NO CUMPLIO" title="No cumplio"><i class="fas fa-times"></i></button>';
                    }
                    if ($("#no-empleado-hidden").val() == item.id_responsable) {
                        html += '<button type="button" class="btn btn-outline-primary btn-sm js-accion-realizado-confirm" data-id-actividad="' + escapeHtml(String(item.id || '')) + '" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '" title="Realizado"><i class="fas fa-check-double"></i></button>';

                    }
                    html += '  </td>';
                    html += '</tr>';
                });

                html += '    </tbody>';
                html += '  </table>';
                html += '</div>';
            });

            if (!hayActividades) {
                html += '<div class="alert alert-warning">Este plan no tiene actividades registradas.</div>';
            }

            root.innerHTML = html;
        }

        root.onclick = function(event) {
            const botonEstatus = event.target.closest('.js-accion-estatus-modal');
            if (botonEstatus) {
                const idActividad = botonEstatus.getAttribute('data-id-actividad') || '';
                const numActividad = botonEstatus.getAttribute('data-num-actividad') || '';
                const estatusActividad = botonEstatus.getAttribute('data-estatus-actividad') || '';
                abrirModalEstatusActividad(idActividad, numActividad, estatusActividad, botonEstatus);
                return;
            }

            const botonRealizado = event.target.closest('.js-accion-realizado-confirm');
            if (botonRealizado) {
                const idActividad = botonRealizado.getAttribute('data-id-actividad') || '';
                const numActividad = botonRealizado.getAttribute('data-num-actividad') || '';
                abrirModalConfirmarRealizado(idActividad, numActividad, botonRealizado);
                return;
            }
        };

        if (btnGuardarAprobacion) {
            btnGuardarAprobacion.onclick = guardarAprobacionActividad;
        }

        if (btnConfirmarRealizado) {
            btnConfirmarRealizado.onclick = function() {
                const idActividad = (modalConfirmarIdActividad.value || '').trim();
                guardarRealizadoActividad(idActividad, ultimoBotonRealizado || btnConfirmarRealizado);
            };
        }

        // Valida parametro minimo antes de consultar backend.
        if (!idPlan || idPlan <= 0) {
            renderError('ID de plan invalido.');
            return;
        }

        // Carga el detalle y verifica permisos en paralelo.
        const peticionPlan = $.ajax({
            url: 'acciones_detalles_plan.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'detalle_plan', id: idPlan }
        });

        const peticionPermiso = $.ajax({
            url: '../loginMaster/acciones_globales.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'ValidarPermisos',
                noEmpleado: noEmpleado,
                sistema: 'controlSGC',
                opcion: 'verCumplioActividad' // Permiso necesario para mostrar botones de marcar cumplio/no cumplio.
            }
        });

        $.when(peticionPlan, peticionPermiso)
            .done(function(resPlan, resPermiso) {
                const respPlan = resPlan[0];
                const respPermiso = resPermiso[0];

                if (respPermiso && respPermiso.status === 'success' && respPermiso.data && respPermiso.data[0]) {
                    puedeVerCumplio = Number(respPermiso.data[0].cuantos || 0) > 0;
                }

                if (!respPlan || !respPlan.success) {
                    renderError(respPlan && respPlan.error ? respPlan.error : 'No se pudo cargar el plan.');
                    return;
                }
                renderDetalle(respPlan.data || {});
            })
            .fail(function() {
                renderError('Error de comunicacion con el servidor.');
            });
    })();
    </script>
</body>
</html>
