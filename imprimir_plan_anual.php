<?php
    // Vista dedicada para impresion limpia del plan anual.
    $idPlan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $autoPrint = isset($_GET['auto_print']) && $_GET['auto_print'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control SGC - Impresion Plan Anual SGC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #ffffff; }
        .print-wrapper { max-width: 1280px; margin: 0 auto; }
        .table td, .table th { font-size: 12px; padding: .25rem; }
        .titulo-plan { font-size: 2rem; }
        @media print {
            .d-print-none { display: none !important; }
            .print-wrapper { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3 print-wrapper">
        <div id="detalle-plan-root"></div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="js/actividades_sgc.js?v=20260306"></script>
    <script>
    (function() {
        const root = document.getElementById('detalle-plan-root');
        const idPlan = <?php echo (int)$idPlan; ?>;
        const autoPrint = <?php echo $autoPrint ? 'true' : 'false'; ?>;

        function escapeHtml(text) {
            const value = String(text == null ? '' : text);
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function nl2brSafe(text) {
            return escapeHtml(text).replace(/\n/g, '<br>');
        }

        function renderError(message) {
            root.innerHTML = '<div class="alert alert-danger">' + escapeHtml(message || 'Ocurrio un error al cargar el detalle.') + '</div>';
        }

        function renderDetalle(data) {
            const plan = data.plan || {};
            const actividadesPorSeccion = data.actividades_por_seccion || {};
            const ordenSecciones = data.orden_secciones || [];
            const seccionesTitulos = data.secciones_titulos || {};
            const folio = data.folio || 'S/R';
            const fechaRegistro = formatearFechaRegistro(plan.fecha_creacion);

            let html = '';
            html += '<div class="border-bottom border-2 border-dark pb-2 mb-3">';
            html += '  <div class="row g-2">';
            html += '    <div class="col-12 text-center"><div class="fw-bold titulo-plan">Plan Anual SGC</div></div>';
            html += '    <div class="col-12 d-flex justify-content-between align-items-end flex-nowrap">';
            html += '      <div class="fw-bold fs-2 me-1">Folio: <span class="text-primary text-decoration-underline">' + escapeHtml(folio) + '</span></div>';
            html += '      <div class="fw-bold fs-6 text-end text-nowrap">Fecha: <span class="text-dark">' + escapeHtml(fechaRegistro) + '</span></div>';
            html += '    </div>';
            html += '      <div class="col-6"><span class="fw-semibold">Area:</span> ' + escapeHtml(plan.area_text || 'S/R') + '</div>';
            html += '  </div>';
            html += '</div>';

            html += '<div class="row g-2 mb-3">';
            html += '  <div class="col-4"><span class="fw-semibold">Registra:</span> ' + escapeHtml(plan.registra_text || plan.id_registra || 'S/R') + '</div>';
            html += '  <div class="col-4"><span class="fw-semibold">Presenta:</span> ' + escapeHtml(plan.presenta_text || 'S/R') + '</div>';
            html += '  <div class="col-4"><span class="fw-semibold">Aprueba:</span> ' + escapeHtml(plan.aprueba_text || 'S/R') + '</div>';
            html += '  <div class="col-6"><span class="fw-semibold">Estatus:</span> ' + escapeHtml(plan.estatus || 'S/R') + '</div>';
            html += '</div>';

            html += '<h6 class="bg-primary border border-dark text-dark py-2 px-3 mt-3 mb-0">Objetivo de calidad</h6>';
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

                html += '<h6 class="bg-primary border border-dark text-dark py-2 px-3 mt-3 mb-0">' + escapeHtml(tituloSeccion) + '</h6>';
                html += '<div class="table-responsive mb-3">';
                html += '  <table class="table table-bordered table-sm mb-0">';
                html += '    <thead class="table-light text-center align-middle small">';
                html += '      <tr>';
                html += '        <th rowspan="2">No</th>';
                html += '        <th rowspan="2">Actividades</th>';
                html += '        <th colspan="12">Meses</th>';
                html += '        <th rowspan="2">Responsable</th>';
                html += '        <th rowspan="2">Participantes</th>';
                html += '        <th rowspan="2">Observaciones sobre el cumplimiento</th>';
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

            // Si viene desde boton Imprimir del detalle, abrir dialogo automaticamente.
            if (autoPrint) {
                setTimeout(function() {
                    window.print();
                }, 150);
            }
        }

        if (!idPlan || idPlan <= 0) {
            renderError('ID de plan invalido.');
            return;
        }

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
