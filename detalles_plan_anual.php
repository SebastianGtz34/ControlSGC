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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal-num-actividad" value="">
                    <input type="hidden" id="modal-estatus-actividad" value="">
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
        const modalEl = document.getElementById('modalAprobacionActividad');
        const modalAprobacion = modalEl ? new bootstrap.Modal(modalEl) : null;
        const modalNumActividad = document.getElementById('modal-num-actividad');
        const modalEstatusActividad = document.getElementById('modal-estatus-actividad');
        const modalObservacion = document.getElementById('modal-observacion-actividad');
        const modalEtiquetaActividad = document.getElementById('modal-etiqueta-actividad');
        const btnGuardarAprobacion = document.getElementById('btn-guardar-aprobacion');
        let ultimoBotonAccion = null;

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
            const estatus = String(estatusRaw || 'EN REVISION').trim().toUpperCase();
            const etiqueta = estatus.replace(/_/g, ' ');

            if (estatus === 'APROBADO') {
                return '<span class="badge bg bg-success">' + escapeHtml(etiqueta) + '</span>';
            }

            if (estatus === 'NO APROBADO') {
                return '<span class="badge bg bg-danger">' + escapeHtml(etiqueta) + '</span>';
            }

            return '<span class="badge bg bg-warning">' + escapeHtml(etiqueta) + '</span>';
        }

        // Renderiza errores de carga/validacion en el contenedor principal.
        function renderError(message) {
            root.innerHTML = '<div class="alert alert-danger">' + escapeHtml(message || 'Ocurrio un error al cargar el detalle.') + '</div>';
        }

        function abrirModalAprobacion(numActividad, estatusActividad, botonOrigen) {
            if (!modalAprobacion) {
                alert('No se pudo abrir el modal.');
                return;
            }

            const estatus = String(estatusActividad || '').toUpperCase();
            const etiqueta = estatus === 'APROBADO' ? 'Aprobar' : 'No aprobar';

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
            const numActividad = (modalNumActividad.value || '').trim();
            const estatusActividad = (modalEstatusActividad.value || '').trim();
            const observacion = (modalObservacion.value || '').trim();

            if (!numActividad) {
                alert('No se encontro el numero de actividad.');
                return;
            }
            if (estatusActividad !== 'APROBADO' && estatusActividad !== 'NO APROBADO') {
                alert('Estatus invalido.');
                return;
            }
            if (!observacion) {
                alert('La observacion es obligatoria.');
                return;
            }
            if (!idUsrAprobo) {
                alert('No existe id_usuarioL en cookies para registrar aprobacion.');
                return;
            }

            btnGuardarAprobacion.disabled = true;

            $.ajax({
                url: 'acciones_detalles_plan.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'guardar_estatus_actividad',
                    id_plan_anual: idPlan,
                    num_actividad: numActividad,
                    estatus_actividad: estatusActividad,
                    observacion: observacion,
                    id_usr_aprobo: idUsrAprobo
                },
                success: function(resp) {
                    btnGuardarAprobacion.disabled = false;
                    if (!resp || !resp.success) {
                        alert(resp && resp.error ? resp.error : 'No se pudo guardar el estatus.');
                        return;
                    }

                    const selector = 'button[data-num-actividad="' + numActividad.replace(/"/g, '\\"') + '"]';
                    const botonesActividad = root.querySelectorAll(selector);
                    botonesActividad.forEach(function(btn) {
                        if (btn.getAttribute('data-estatus-actividad') === estatusActividad) {
                            btn.classList.remove('btn-outline-success', 'btn-outline-danger');
                            btn.classList.add(estatusActividad === 'APROBADO' ? 'btn-success' : 'btn-danger');
                        } else {
                            btn.classList.remove('btn-success', 'btn-danger');
                            btn.classList.add(btn.getAttribute('data-estatus-actividad') === 'APROBADO' ? 'btn-outline-success' : 'btn-outline-danger');
                        }
                    });

                    const badgesActividad = root.querySelectorAll('span.js-estatus-actividad[data-num-actividad="' + numActividad.replace(/"/g, '\\"') + '"]');
                    badgesActividad.forEach(function(badge) {
                        badge.innerHTML = renderEstatusActividadBadge(estatusActividad);
                    });

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
            html += '  <div class="col-md-6"><span class="fw-semibold">Estatus:</span> ' + escapeHtml(plan.estatus || 'S/R') + '</div>';
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
                html += '        <th rowspan="2">Estatus</th>';
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
                    html += '  <td class="text-center"><span class="js-estatus-actividad" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '">' + renderEstatusActividadBadge(item.estatus_actividad || 'EN REVISION') + '</span></td>';
                    html += '  <td class="text-center text-nowrap">';
                    html += '    <button type="button" class="btn btn-outline-success btn-sm me-1 js-accion-actividad" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '" data-estatus-actividad="APROBADO" title="Aprobar"><i class="fas fa-check"></i></button>';
                    html += '    <button type="button" class="btn btn-outline-danger btn-sm js-accion-actividad" data-num-actividad="' + escapeHtml(item.num_actividad || '') + '" data-estatus-actividad="NO APROBADO" title="No aprobar"><i class="fas fa-times"></i></button>';
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

        root.addEventListener('click', function(event) {
            const button = event.target.closest('.js-accion-actividad');
            if (!button) {
                return;
            }

            const numActividad = button.getAttribute('data-num-actividad') || '';
            const estatusActividad = button.getAttribute('data-estatus-actividad') || '';
            abrirModalAprobacion(numActividad, estatusActividad, button);
        });

        if (btnGuardarAprobacion) {
            btnGuardarAprobacion.addEventListener('click', guardarAprobacionActividad);
        }

        // Valida parametro minimo antes de consultar backend.
        if (!idPlan || idPlan <= 0) {
            renderError('ID de plan invalido.');
            return;
        }

        // Carga el detalle desde el endpoint desacoplado.
        $.ajax({
            url: 'acciones_detalles_plan.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'detalle_plan',
                id: idPlan
            },
            success: function(resp) {
                if (!resp || !resp.success) {
                    renderError(resp && resp.error ? resp.error : 'No se pudo cargar el plan.');
                    return;
                }
                renderDetalle(resp.data || {});
            },
            error: function() {
                renderError('Error de comunicacion con el servidor.');
            }
        });
    })();
    </script>
</body>
</html>
