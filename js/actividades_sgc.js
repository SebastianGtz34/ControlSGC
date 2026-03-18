let usuariosCache = [];
let puestosCache = [];
let areasCache = [];
let puedeVerOpcionesPlan = false;

function obtenerCookieValor(nombreCookie) {
    let cookies = document.cookie ? document.cookie.split(';') : [];
    let nombreBuscado = String(nombreCookie || '').trim() + '=';
    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();
        if (cookie.indexOf(nombreBuscado) === 0) {
            return decodeURIComponent(cookie.substring(nombreBuscado.length));
        }
    }
    return '';
}

// Inicializa selectores y eventos si existen en la pagina.
$(document).ready(function() {
    // Si estamos en la pagina de detalle, cargar datos
    if ($('#numActividad').length && window.location.pathname.includes('detalle_actividad_SGC.php')) {
        let idDetalle = obtenerParametroUrl('id');
        if (idDetalle) {
            cargarDetalleCompleto(idDetalle);
        } else {
            window.location.href = 'detalles_actividades_SGC.php';
        }
    }

    if ($('#tablaActividades').length) {
        cargarListadoPlanes();
    }

    if ($('#formActividad').length) {
        // Verificar si hay un plan para editar desde localStorage
        let planEditarId = localStorage.getItem('planEditarId');
        let enEdicion = false;

        const iniciarEdicion = function() {
            if (planEditarId && window.location.pathname.includes('editar_plan.php')) {
                // Editar plan completo
                $('#tituloFormulario').text('Editar Plan Anual');
                document.title = 'Editar Plan Anual';
                enEdicion = true;
                cargarPlanParaEditar(planEditarId);
                // Mantener planEditarId para permitir recargas en edicion
                return;
            }

            if (planEditarId && window.location.pathname.includes('registro_actividades_SGC.php')) {
                // Evitar que el formulario de registro cargue datos de edicion
                localStorage.removeItem('planEditarId');
                planEditarId = null;
            }

            // Carga datos en modo edicion si viene id en la URL (para actividades individuales)
            let idEditar = obtenerParametroUrl('id');
            if (idEditar) {
                $('#tituloRegistroSGC').text('Editar Registro SGC');
                document.title = 'Editar Registro SGC';
                enEdicion = true;
                cargarActividadParaEditar(idEditar);
            }
        };

        // Cargar datos del formulario y luego iniciar edicion si aplica
        cargarDatosFormulario(iniciarEdicion);

        // Envia alta o actualizacion de actividad.
        $('#formActividad').on('submit', function(e) {
            e.preventDefault();

            const guardarAvanceDesdeValidacion = function() {
                let planEditarIdAvance = String($('#plan_editar_id').val() || '').trim();
                let accionAvance = planEditarIdAvance ? 'actualizar' : 'crear';
                let datos = $('#formActividad').serialize() + '&accion=' + accionAvance + '&guardar_avance=1';

                $.ajax({
                    url: 'acciones_actividades.php',
                    method: 'POST',
                    dataType: 'json',
                    data: datos,
                    success: function(resp) {
                        if (!resp || !resp.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo guardar avance',
                                text: (resp && resp.error) ? resp.error : 'Verifica que exista al menos un renglon de actividad completo.'
                            });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Avance guardado',
                            text: 'El avance se guardo.'
                        })
                        
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo guardar el avance.'
                        });
                    }
                });
            };

            const preguntarGuardarAvance = function(titulo, mensaje) {
                Swal.fire({
                    icon: 'warning',
                    title: titulo,
                    text: mensaje,
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: 'Corregir',
                    denyButtonText: 'Guardar avance',
                    denyButtonColor: '#0c7c15',
                }).then(function(result) {
                    if (result.isDenied) {
                        guardarAvanceDesdeValidacion();
                    }
                });
            };
            
            // Validar firmas
            let idArea = $('#areaPlan').val();
            let puestoPresenta = $('#puestoPresenta').val();
            let usuarioPresenta = $('#usuarioPresenta').val();
            let puestoAprueba = $('#puestoAprueba').val();
            let usuarioAprueba = $('#usuarioAprueba').val();
            
            if (!idArea || !puestoPresenta || !usuarioPresenta || !puestoAprueba || !usuarioAprueba) {
                preguntarGuardarAvance(
                    'Firmas incompletas',
                    'Debes seleccionar el area, puesto y usuario que elabora y quien aprueba.'
                );
                return;
            }
            
            // Validar que haya al menos una actividad completa por sección
            let secciones = ['6.2', '6.3', '6.4', '7.2', '7.6', '7.7', '8.3', '8.8', '8.9'];
            let seccionesNombres = {
                '6.2': '6.2 Personal',
                '6.3': '6.3 Instalaciones y condiciones ambientales',
                '6.4': '6.4 Equipamiento',
                '7.2': '7.2 Selección, verificación y validación de métodos',
                '7.6': '7.6 Evaluación de la incertidumbre de la medición',
                '7.7': '7.7 Aseguramiento de la validez de los resultados',
                '8.3': '8.3 Control de documentos del sistema de gestión',
                '8.8': '8.8 Auditorías internas y externas',
                '8.9': '8.9 Revisión por la dirección',
            };
            
            for (let i = 0; i < secciones.length; i++) {
                let seccion = secciones[i];
                let seccionId = seccion === 'iv' ? 'iv' : seccion;
                let $tbody = obtenerTbodySeccionPlan(seccionId);
                let filas = $tbody.find('.plan-row');
                
                if (filas.length === 0) {
                    preguntarGuardarAvance(
                        'Sección incompleta',
                        'La sección "' + seccionesNombres[seccion] + '" no tiene filas.'
                    );
                    return;
                }
                
                let tieneActividad = false;
                let hayFilaIncompleta = false;
                let detalleFilaIncompleta = '';
                
                filas.each(function(indexFila) {
                    // Buscar los elementos dentro de esta fila
                    let inputNumeroActividad = $(this).find('input[name*="num_actividad[' + seccionId + ']"]');
                    let textareaActividad = $(this).find('textarea[name*="actividad[' + seccionId + ']"]');
                    let selectResponsable = $(this).find('select[name*="responsable[' + seccionId + ']"]');
                    let checkboxesMeses = $(this).find('input[name*="meses[' + seccionId + ']"]:checked');
                    let selectParticipantes = $(this).find('select[name*="participantes[' + seccionId + ']"]');
                    
                    let numeroActividad = inputNumeroActividad.val() ? inputNumeroActividad.val().trim() : '';
                    let actividad = textareaActividad.val() ? textareaActividad.val().trim() : '';
                    let responsable = selectResponsable.val();
                    let meses = checkboxesMeses.length;
                    let participantes = selectParticipantes.val() || [];

                    let tieneContenido = (actividad !== '' || responsable || meses > 0 || participantes.length > 0);
                    let filaCompleta = (actividad !== '' && responsable && meses > 0);

                    if (tieneContenido && !filaCompleta) {
                        let camposFaltantes = [];
                        if (actividad === '') {
                            camposFaltantes.push('Actividad');
                        }
                        if (!responsable) {
                            camposFaltantes.push('Responsable');
                        }
                        if (meses === 0) {
                            camposFaltantes.push('Mes');
                        }

                        let etiquetaFila = numeroActividad || ('fila ' + String(indexFila + 1));
                        detalleFilaIncompleta = 'La actividad "' + etiquetaFila + '" en la sección "' + seccionesNombres[seccion] + '" está incompleta. Falta: ' + camposFaltantes.join(', ') + '.';
                        hayFilaIncompleta = true;
                        return false; // break
                    }
                    
                    if (filaCompleta) {
                        tieneActividad = true;
                    }
                });

                if (hayFilaIncompleta) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Actividad incompleta',
                        text: detalleFilaIncompleta || 'Hay un renglon incompleto. Completa actividad, responsable y al menos un mes.'
                    });
                    return;
                }
                
                if (!tieneActividad) {
                    preguntarGuardarAvance(
                        'Sección incompleta',
                        'La sección "' + seccionesNombres[seccion] + '" debe tener al menos una actividad completa.'
                    );
                    return;
                }
            }
            
            let planEditarId = String($('#plan_editar_id').val() || '').trim();
            let accion = planEditarId ? 'actualizar' : ($('#actividad_id').val() ? 'actualizar' : 'crear');
            
            // Función para guardar los datos
            const guardarDatos = function() {
                let datos = $('#formActividad').serialize() + '&accion=' + accion;

                // Guarda actividad en backend.
                $.ajax({
                    url: 'acciones_actividades.php',
                    method: 'POST',
                    dataType: 'json',
                    data: datos,
                    success: function(resp) {
                        if (!resp || !resp.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (resp && resp.error) ? resp.error : 'No se pudo guardar.'
                            });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Listo',
                            text: 'Registro guardado.'
                        }).then(function() {
                            if (planEditarId) {
                                localStorage.removeItem('planEditarId');
                            }
                            //window.location.href = 'detalles_actividades_SGC.php';
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo guardar el registro.'
                        });
                    }
                });
            };
            
            // Si estamos editando un plan, preguntar confirmación
            if (planEditarId) {
                Swal.fire({
                    title: '¿Actualizar plan?',
                    text: '¿Estás seguro de que deseas guardar los cambios en este plan?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        guardarDatos();
                    }
                });
            } else {
                // Crear nuevo plan o actividad sin confirmación
                guardarDatos();
            }
        });
    }
});

// Event listener delegado para botones de acciones del plan
$(document).on('click', '.btn-editar-plan, .btn-eliminar-plan, .btn-ver-plan', function() {
    const $boton = $(this);
    const planId = $boton.data('id');

    if ($boton.hasClass('btn-editar-plan')) {
        localStorage.setItem('planEditarId', planId);
        window.location.href = 'editar_plan.php';
        return;
    }

    if ($boton.hasClass('btn-eliminar-plan')) {
        eliminarPlan(planId);
        return;
    }

    if ($boton.hasClass('btn-ver-plan')) {
        abrirDetallePlan(planId);
    }
});

// Construye el HTML de una fila del plan de actividades para una seccion dada.
function crearFilaPlanHtml(index, seccion, id_actividad) {
    let meses = [1,2,3,4,5,6,7,8,9,10,11,12];
    let mesCeldas = meses.map(function(m) {
        return '<td class="text-center">' +
            '<input type="checkbox" name="meses[' + seccion + '][' + index + '][]" value="' + m + '">' +
            '</td>';
    }).join('');

    let seccionKey = String(seccion).toLowerCase();
    let baseNumero = seccionKey === 'iv' ? 'IV' : seccion;
    let numeroActividad = baseNumero + '.' + (index + 1);
    let actividadId = Number(id_actividad) > 0 ? Number(id_actividad) : '';

    return '<tr class="plan-row" data-row="' + index + '" data-seccion="' + seccion + '">' +
        '<td class="text-center">' +
            '<input type="hidden" name="actividad_id[' + seccion + '][' + index + ']" value="' + actividadId + '">' +
            '<input type="text" class="form-control form-control-sm" name="num_actividad[' + seccion + '][' + index + ']" value="' + numeroActividad + '" readonly>' +
        '</td>' +
        '<td>' +
            '<textarea class="form-control form-control-sm" name="actividad[' + seccion + '][' + index + ']" rows="3" required></textarea>' +
        '</td>' +
        mesCeldas +
        '<td>' +
            '<select class="form-select form-select-sm responsable-select" name="responsable[' + seccion + '][' + index + ']">' +
                '<option value="">Selecciona...</option>' +
            '</select>' +
        '</td>' +
        '<td>' +
            '<select class="form-select form-select-sm participantes-select" name="participantes[' + seccion + '][' + index + '][]" multiple></select>' +
        '</td>' +
        '<td>' +
            '<textarea class="form-control form-control-sm" name="observaciones[' + seccion + '][' + index + ']" rows="3"></textarea>' +
        '</td>' +
        '<td class="text-center">' +
            '<button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarFilaPlan(this)" title="Quitar fila">' +
                '<i class="fas fa-trash-alt"></i>' +
            '</button>' +
        '</td>' +
    '</tr>';
}

function obtenerTbodySeccionPlan(seccion) {
    let key = String(seccion).toLowerCase();
    return $('#' + $.escapeSelector('plan-rows-' + key));
}

function agregarFilaSeccionPlan(seccion) {
    let $tbody = obtenerTbodySeccionPlan(seccion);
    let $rows = $tbody.find('.plan-row');
    let nextIndex = $rows.length;
    $tbody.append(crearFilaPlanHtml(nextIndex, seccion));
    let $newRow = $tbody.find('.plan-row').last();
    poblarUsuariosEnFila($newRow);
    actualizarNumerosActividades(seccion);
}

function eliminarFilaPlan(btn) {
    let $fila = $(btn).closest('.plan-row');
    let seccion = $fila.data('seccion');
    let $tbody = $fila.closest('tbody');
    if ($tbody.find('.plan-row').length <= 1) {
        return;
    }
    $fila.remove();
    actualizarNumerosActividades(seccion);
}

function actualizarNumerosActividades(seccion) {
    let $tbody = obtenerTbodySeccionPlan(seccion);
    let $rows = $tbody.find('.plan-row');
    let seccionKey = String(seccion).toLowerCase();
    let baseNumero = seccionKey === 'iv' ? 'IV' : seccion;
    
    $rows.each(function(index) {
        let $row = $(this);
        $row.attr('data-row', index);

        // Reindexar names para que backend reciba indices continuos tras eliminar filas.
        $row.find('input[name^="actividad_id["]').attr('name', 'actividad_id[' + seccion + '][' + index + ']');
        $row.find('input[name^="num_actividad["]').attr('name', 'num_actividad[' + seccion + '][' + index + ']');
        $row.find('textarea[name^="actividad["]').attr('name', 'actividad[' + seccion + '][' + index + ']');
        $row.find('textarea[name^="observaciones["]').attr('name', 'observaciones[' + seccion + '][' + index + ']');
        $row.find('select[name^="responsable["]').attr('name', 'responsable[' + seccion + '][' + index + ']');
        $row.find('select[name^="participantes["]').attr('name', 'participantes[' + seccion + '][' + index + '][]');
        $row.find('input[type="checkbox"][name^="meses["]').attr('name', 'meses[' + seccion + '][' + index + '][]');

        let $input = $row.find('input[name^="num_actividad["]');
        let numeroActividad = baseNumero + '.' + (index + 1);
        $input.val(numeroActividad);
    });
}

function reiniciarFilasPlan() {
    let secciones = ['6.2', '6.3', '6.4', '7.2', '7.6', '7.7', '8.3', '8.8', '8.9', 'iv'];
    secciones.forEach(function(seccion) {
        let $tbody = obtenerTbodySeccionPlan(seccion);
        if ($tbody.length === 0) return; // Skip if tbody doesn't exist
        $tbody.find('.plan-row').remove();
        $tbody.append(crearFilaPlanHtml(0, seccion));
        poblarUsuariosEnFila($tbody.find('.plan-row').first());
        actualizarNumerosActividades(seccion);
    });
}

function inicializarSelect2Estandar($elemento, placeholder) {
    if ($elemento.hasClass('select2-hidden-accessible')) {
        $elemento.select2('destroy');
    }
    $elemento.select2({
        placeholder: placeholder,
        width: '100%'
    });
}

function poblarUsuariosEnFila($row) {
    let $responsable = $row.find('.responsable-select');
    let $participantes = $row.find('.participantes-select');

    $responsable.empty().append('<option value="">Selecciona...</option>');
    $participantes.empty();

    usuariosCache.forEach(function(usr) {
        $responsable.append('<option value="' + usr.id_usuario + '">' + usr.nombre + '</option>');
        $participantes.append('<option value="' + usr.id_usuario + '">' + usr.nombre + '</option>');
    });

    inicializarSelect2Estandar($responsable, 'Selecciona responsable');
    inicializarSelect2Estandar($participantes, 'Selecciona participantes');
}

function obtenerUsuarioPorId(idUsuario) {
    if (!idUsuario) {
        return null;
    }
    return usuariosCache.find(function(usr) {
        return String(usr.id_usuario) === String(idUsuario);
    }) || null;
}

function obtenerPuestoNombre(idPuesto) {
    if (!idPuesto) {
        return '';
    }
    let puesto = puestosCache.find(function(item) {
        return String(item.id_puesto) === String(idPuesto);
    });
    return puesto ? puesto.nombre_puesto : '';
}

function obtenerAreaNombre(idArea) {
    if (!idArea) {
        return '';
    }
    let area = areasCache.find(function(item) {
        return String(item.id_area) === String(idArea);
    });
    return area ? area.nombre_area : '';
}

function aplicarUsuarioASelects(idUsuario, $puestoInput, $areaInput, $puestoText, $areaText) {
    let usuario = obtenerUsuarioPorId(idUsuario);
    if (!usuario) {
        return;
    }

    if ($puestoInput && usuario.puesto) {
        $puestoInput.val(usuario.puesto);
    }
    if ($areaInput && usuario.area) {
        $areaInput.val(usuario.area);
    }

    if ($puestoText) {
        $puestoText.text(obtenerPuestoNombre(usuario.puesto) || 'S/R');
    }
    if ($areaText) {
        $areaText.text(obtenerAreaNombre(usuario.area) || 'S/R');
    }
}

// Obtiene el valor de un parametro en la URL.
function obtenerParametroUrl(key) {
    let params = new URLSearchParams(window.location.search);
    return params.get(key);
}

// Formatea fecha a dd/mm/yyyy para mostrar en vistas.
function formatearFechaDdMmYyyy(fecha) {
    let fechaNormalizada = String(fecha).replace(' ', 'T');
    let d = new Date(fechaNormalizada);
    if (isNaN(d.getTime())) {
        return String(fecha);
    }

    let dd = String(d.getDate()).padStart(2, '0');
    let mm = String(d.getMonth() + 1).padStart(2, '0');
    let yyyy = d.getFullYear();
    return dd + '/' + mm + '/' + yyyy;
}

// Aplica estilos y configuraciones a una tabla dada, con opciones para ordenamiento, paginación y lenguaje.
function inicializarDataTableEstandar(tablaId, ordenColumna, opcionesExtra) {
    let configuracion = {
        responsive: true,
        language: {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Ningún dato disponible en esta tabla",
            sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
            sInfoPostFix: "",
            sSearch: "Buscar:",
            sUrl: "",
            sInfoThousands: ",",
            sLoadingRecords: "Cargando...",
            oPaginate: {
                sFirst: "Primero",
                sLast: "Último",
                sNext: "Siguiente",
                sPrevious: "Anterior"
            },
            oAria: {
                sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                sSortDescending: ": Activar para ordenar la columna de manera descendente"
            }
        },
        ordering: false,
        order: [],
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        pageLength: 10,
        searching: false
    };

    if (ordenColumna !== undefined && ordenColumna !== null && ordenColumna !== false) {
        configuracion.ordering = true;
        configuracion.order = [[ordenColumna, 'desc']];
    }

    if (opcionesExtra) {
        configuracion = $.extend(true, {}, configuracion, opcionesExtra);
    }

    return $(tablaId).DataTable(configuracion);
}

// Escapa caracteres especiales para usarlos en búsquedas de DataTables con regex.
function escaparRegexDataTable(valor) {
    return String(valor || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Obtiene los valores únicos de una columna específica de la tabla, formateados y ordenados alfabéticamente.
function obtenerValoresUnicosDeColumna(tabla, indiceColumna) {
    return tabla.column(indiceColumna).data().toArray()
        .map(function(valor) {
            return String(valor || 'S/R').trim() || 'S/R';
        })
        .filter(function(valor, indice, valores) {
            return valores.indexOf(valor) === indice;
        })
        .sort(function(a, b) {
            return a.localeCompare(b, 'es', { sensitivity: 'base' });
        });
}

// Llena un select con opciones dadas, manteniendo el valor seleccionado si sigue disponible.
function poblarSelectFiltro($select, opciones) {
    const valorActual = $select.val() || '';
    $select.find('option:not(:first)').remove();

    opciones.forEach(function(opcion) {
        $select.append('<option value="' + opcion + '">' + opcion + '</option>');
    });

    $select.val(opciones.indexOf(valorActual) >= 0 ? valorActual : '');
}

// Aplica un filtro de búsqueda exacta a una columna específica de la tabla.
function aplicarFiltroExactoColumna(tabla, indiceColumna, valor) {
    if (!valor) {
        tabla.column(indiceColumna).search('');
        return;
    }

    tabla.column(indiceColumna).search('^' + escaparRegexDataTable(valor) + '$', true, false);
}

// Inicializa los filtros de la tabla de actividades y sus eventos asociados.
function inicializarFiltrosPlanes(tablaActividades) {
    const $filtroPresenta = $('#filtroPresenta');
    const $filtroArea = $('#filtroArea');
    const $filtroEstatus = $('#filtroEstatus');
    const $btnLimpiar = $('#limpiarFiltrosTablaActividades');

    poblarSelectFiltro($filtroPresenta, obtenerValoresUnicosDeColumna(tablaActividades, 1));
    poblarSelectFiltro($filtroArea, obtenerValoresUnicosDeColumna(tablaActividades, 4));
    poblarSelectFiltro($filtroEstatus, obtenerValoresUnicosDeColumna(tablaActividades, 6));

    $filtroPresenta.off('change.filtroActividades').on('change.filtroActividades', function() {
        aplicarFiltroExactoColumna(tablaActividades, 1, $(this).val());
        tablaActividades.draw();
    });

    $filtroArea.off('change.filtroActividades').on('change.filtroActividades', function() {
        aplicarFiltroExactoColumna(tablaActividades, 4, $(this).val());
        tablaActividades.draw();
    });

    $filtroEstatus.off('change.filtroActividades').on('change.filtroActividades', function() {
        aplicarFiltroExactoColumna(tablaActividades, 6, $(this).val());
        tablaActividades.draw();
    });

    $btnLimpiar.off('click.filtroActividades').on('click.filtroActividades', function() {
        $filtroPresenta.val('');
        $filtroArea.val('');
        $filtroEstatus.val('');
        tablaActividades.columns().search('');
        tablaActividades.draw();
    });
}

// Carga planes anuales y llena la tabla.
function cargarListadoPlanes() {
    if (!$('#tablaActividades').length) {
        return;
    }

    const noEmpleado = obtenerCookieValor('noEmpleado');

    const peticionPlanes = $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'listar_planes' }
    });

    const peticionPermiso = $.ajax({
        url: '../loginMaster/acciones_globales.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'ValidarPermisos',
            noEmpleado: noEmpleado,
            sistema: 'controlSGC',
            opcion: 'verOpcionesPlan'
        }
    });

    $.when(peticionPlanes, peticionPermiso)
        .done(function(resPlanes, resPermiso) {
            let resp = resPlanes[0] || {};
            let respPermiso = resPermiso[0] || {};

            if (respPermiso && respPermiso.status === 'success' && respPermiso.data && respPermiso.data[0]) {
                puedeVerOpcionesPlan = Number(respPermiso.data[0].cuantos || 0) > 0;
            } else {
                puedeVerOpcionesPlan = false;
            }

            if (!resp.success) {
                return;
            }

            let rows = resp.data || [];
            if ($.fn.DataTable.isDataTable('#tablaActividades')) {
                let tabla = $('#tablaActividades').DataTable();
                tabla.destroy();
                $('#tablaActividades tbody').empty();
            }

            let tablaActividades = inicializarDataTableEstandar('#tablaActividades', false, {
                paging: true,
                searching: true,
                dom: 'lrtip',
                orderCellsTop: true,
                data: rows,
                columns: [
                    {
                        data: 'titulo_objetivos',
                        render: function(data) {
                            return '<span class="text-dark small">' + (data || 'S/R') + '</span>';
                        }
                    },
                    {
                        data: 'presenta_text',
                        render: function(data) {
                            if (!data || data === 'S/R') return '<span class="text-dark">S/R</span>';
                            var parts = data.split(' - ');
                            if (parts.length === 2) {
                                return '<span class="text-dark">' + parts[0] + '</span> <span class="text-black-50">- ' + parts[1] + '</span>';
                            }
                            return '<span class="text-dark">' + data + '</span>';
                        }
                    },
                    {
                        data: 'aprueba_text',
                        render: function(data) {
                            if (!data || data === 'S/R') return '<span class="text-dark">S/R</span>';
                            var parts = data.split(' - ');
                            if (parts.length === 2) {
                                return '<span class="text-dark">' + parts[0] + '</span> <span class="text-black-50">- ' + parts[1] + '</span>';
                            }
                            return '<span class="text-dark">' + data + '</span>';
                        }
                    },
                    {
                        data: 'registra_text',
                        render: function(data, type, row) {
                            return '<span class="text-dark">' + (data || row.id_registra || 'S/R') + '</span>';
                        }
                    },
                    {
                        data: 'area_text',
                        render: function(data, type, row) {
                            return '<span class="text-dark">' + (data || row.id_area || 'S/R') + '</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            var total = parseInt(row.total_actividades, 10);
                            var aprobadas = parseInt(row.actividades_aprobadas, 10);
                            var enRevision = parseInt(row.actividades_en_revision, 10);
                            var noAprobadas = parseInt(row.actividades_no_aprobadas, 10);

                            if (isNaN(total)) total = 0;
                            if (isNaN(aprobadas)) aprobadas = 0;
                            if (isNaN(enRevision)) enRevision = 0;
                            if (isNaN(noAprobadas)) noAprobadas = 0;

                            return '<div class="small">' +
                                        '<div class="fw-semibold text-dark mb-1">Total: ' + total + '</div>' +
                                        '<span class="badge bg-warning text-dark me-1" title="En revisión">' + enRevision + '</span>' +
                                        '<span class="badge bg-success me-1" title="Aprobadas">' + aprobadas + '</span>' +
                                        '<span class="badge bg-danger" title="No aprobadas">' + noAprobadas + '</span>' +
                                    '</div>';
                        }
                    },
                    {
                        data: 'estatus',
                        render: function(data) {
                                let estatus = String(data || '').trim().toUpperCase();
                                let estatusNormalizado = estatus.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                                let etiqueta = estatus.replace(/_/g, ' ') || 'EN REVISION';
                            if (estatusNormalizado === 'CUMPLIO') {
                                    return '<span class="badge bg bg-success">' + etiqueta + '</span>';
                            } else if (estatusNormalizado === 'NO CUMPLIO') {
                                    return '<span class="badge bg bg-danger text-white">' + etiqueta + '</span>';
                                } else if (estatusNormalizado === 'CANCELADO') {
                                    return '<span class="badge bg bg-dark text-white">' + etiqueta + '</span>';
                                } else if (estatusNormalizado === 'EN REVISION' || estatus === '') {
                                    return '<span class="badge bg bg-info text-dark">' + etiqueta + '</span>';
                                } else {
                                    // INCOMPLETO u otro.
                                    return '<span class="badge bg bg-warning text-dark">' + etiqueta + '</span>';
                            }
                        }
                    },
                    {
                        data: 'fecha_creacion',
                        render: function(data) {
                            return '<span class="text-dark small">' + (data || 'S/R') + '</span>';
                        }
                    },
                    {
                        data: 'fecha_actualizacion',
                        render: function(data) {
                            return '<span class="text-dark small">' + (data || 'S/R') + '</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-center',
                        render: function(data) {
                            if (!puedeVerOpcionesPlan) {
                                return '<div class="btn-group" role="group" aria-label="Acciones plan">' +
                                        '<button class="btn btn-sm btn-outline-warning btn-ver-plan" data-id="' + data.id + '" title="Ver detalle">' +
                                        '<i class="fas fa-eye"></i></button>' +
                                    '</div>';
                            }
                            return '<div class="btn-group" role="group" aria-label="Acciones plan">' +
                                        '<button class="btn btn-sm btn-outline-info btn-editar-plan" data-id="' + data.id + '" title="Editar">' +
                                        '<i class="fas fa-pen"></i></button>' +
                                        '<button class="btn btn-sm btn-outline-warning btn-ver-plan" data-id="' + data.id + '" title="Ver detalle">' +
                                        '<i class="fas fa-eye"></i></button>' +
                                        '<button class="btn btn-sm btn-outline-danger btn-eliminar-plan" data-id="' + data.id + '" title="Eliminar">' +
                                        '<i class="fas fa-trash"></i></button>' +
                                    '</div>';
                        }
                    }
                ]
            });

            inicializarFiltrosPlanes(tablaActividades);

            $(window).off('resize.tablaActividades').on('resize.tablaActividades', function() {
                if ($.fn.DataTable.isDataTable('#tablaActividades')) {
                    $('#tablaActividades').DataTable().columns.adjust().responsive.recalc();
                }
            });

            tablaActividades.columns.adjust().responsive.recalc();
        })
        .fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la tabla de planes.'
            });
        });
}

// Obtiene detalle de actividad y llena el formulario.
function cargarActividadParaEditar(id) {
    // Consulta detalle de actividad.
    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'detalle', id: id },
        success: function(resp) {
            if (!resp.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar.' });
                return;
            }
            let a = resp.data.actividad;
            reiniciarFilasPlan();
            let $row = $('.plan-row').first();
            $('#actividad_id').val(a.id);
            $row.find('input[name^="num_actividad"]').val(a.num_actividad);
            $row.find('textarea[name^="actividad"]').val(a.actividad);
            $('#id_categoria').val(a.id_categoria);
            $('#id_recurrencia').val(a.id_recurrencia);
            $('#periodo_registro').val(a.periodo_registro);
            $row.find('textarea[name^="observaciones"]').val(a.observaciones);

            $row.find('input[type="checkbox"][name^="meses"]').prop('checked', false);
            (resp.data.meses || []).forEach(function(m) {
                $row.find('input[type="checkbox"][value="' + m + '"]').prop('checked', true);
            });

            $row.find('.responsable-select').val(resp.data.responsable).trigger('change');
            $row.find('.participantes-select').val(resp.data.participantes).trigger('change');
        }
    });
}

// Confirma y elimina la actividad.
function eliminarPlan(id) {
    Swal.fire({
        title: 'Eliminar',
        text: '¿Seguro que deseas cancelar la actividad?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, cancelar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        // Solicita cancelación al backend.
        $.ajax({
            url: 'acciones_actividades.php',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'eliminar', id: id },
            success: function(resp) {
                if (!resp || !resp.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo eliminar.' });
                    return;
                }
                Swal.fire({ icon: 'success', title: 'Cancelado', text: 'Cambios realizados.' }).then(function() {
                    cargarListadoPlanes();
                });
            }
        });
    });
}

function abrirDetallePlan(id) {
    if (!id) {
        Swal.fire({ icon: 'warning', title: 'Aviso', text: 'No se encontró el plan a consultar.' });
        return;
    }
    window.location.href = 'detalles_plan_anual.php?id=' + encodeURIComponent(id);
}

// Carga detalle completo de una actividad para la pagina de detalle.
function cargarDetalleCompleto(id) {
    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'detalle_completo', id: id },
        success: function(resp) {
            if (!resp || !resp.success) {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: 'No se pudo cargar el detalle.' 
                }).then(function() {
                    window.location.href = 'detalles_actividades_SGC.php';
                });
                return;
            }

            let data = resp.data;
            let act = data.actividad;

            // Llenar campos con los datos
            $('#numActividad').text('#' + act.num_actividad);
            $('#actividad').text(act.actividad);
            
            // Categoria
            $('#categoria').html('<span class="badge bg-secondary"><i class="fas fa-tag me-1"></i>' + 
                (act.categoria || 'Sin categoría') + '</span>');
            
            // Recurrencia
            $('#recurrencia').html('<span class="badge bg-primary">' + act.recurrencia + '</span>');
            
            // Responsable
            $('#responsable').text(data.responsable || 'Sin asignar');
            
            // Participantes
            let participantesTexto = data.participantes.length > 0 ? 
                data.participantes.join(', ') : 'No especificados';
            $('#participantes').text(participantesTexto);
            
            // Fecha registro
            $('#fechaRegistro').html('<i class="far fa-calendar-alt me-2 text-dark"></i>' + act.periodo_registro);
            
            // Meses
            let mesesTexto = data.meses.length > 0 ? 
                data.meses.join(', ') : 'No especificados';
            $('#meses').text(mesesTexto);
            
            // Observaciones
            $('#observaciones').text(act.observaciones || 'Sin observaciones registradas');
        },
        error: function() {
            Swal.fire({ 
                icon: 'error', 
                title: 'Error', 
                text: 'No se pudo cargar el detalle.' 
            }).then(function() {
                window.location.href = 'detalles_actividades_SGC.php';
            });
        }
    });
}

// Carga datos iniciales para el formulario (usuarios, recurrencias, categorias).
function cargarDatosFormulario(onReady) {
    // Establecer fecha actual por defecto
    if ($('#periodo_registro').val() === '') {
        let hoy = new Date();
        let mes = ('0' + (hoy.getMonth() + 1)).slice(-2);
        let dia = ('0' + hoy.getDate()).slice(-2);
        $('#periodo_registro').val(hoy.getFullYear() + '-' + mes + '-' + dia);
    }

    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'datos_formulario' },
        success: function(resp) {
            if (!resp || !resp.success) {
                console.error('No se pudieron cargar los datos del formulario');
                return;
            }

            let data = resp.data;
            usuariosCache = data.usuarios || [];
            puestosCache = data.puestos || [];
            areasCache = data.areas || [];

            if ($('#puestoPresenta').length) {
                $('#usuarioPresenta').empty().append('<option value="">Selecciona un usuario...</option>');
                $('#usuarioAprueba').empty().append('<option value="">Selecciona un usuario...</option>');

                usuariosCache.forEach(function(usuario) {
                    let option = $('<option></option>')
                        .attr('value', usuario.id_usuario)
                        .text(usuario.nombre);
                    $('#usuarioPresenta').append(option.clone());
                    $('#usuarioAprueba').append(option);
                });

                if (!$('#usuarioPresenta').hasClass('select2-hidden-accessible')) {
                    $('#usuarioPresenta').select2({ width: '100%' });
                }
                if (!$('#usuarioAprueba').hasClass('select2-hidden-accessible')) {
                    $('#usuarioAprueba').select2({ width: '100%' });
                }

                $('#usuarioPresenta').off('change.autofill select2:select.autofill').
                    on('change.autofill select2:select.autofill', function() {
                        aplicarUsuarioASelects(
                            $(this).val(),
                            $('#puestoPresenta'),
                            $('#areaPlan'),
                            $('#usuarioPresentaPuesto'),
                            $('#usuarioPresentaArea')
                        );
                    });

                $('#usuarioAprueba').off('change.autofill select2:select.autofill').
                    on('change.autofill select2:select.autofill', function() {
                        aplicarUsuarioASelects(
                            $(this).val(),
                            $('#puestoAprueba'),
                            $('#areaPlan'),
                            $('#usuarioApruebaPuesto'),
                            $('#usuarioApruebaArea')
                        );
                    });

                aplicarUsuarioASelects(
                    $('#usuarioPresenta').val(),
                    $('#puestoPresenta'),
                    $('#areaPlan'),
                    $('#usuarioPresentaPuesto'),
                    $('#usuarioPresentaArea')
                );
                aplicarUsuarioASelects(
                    $('#usuarioAprueba').val(),
                    $('#puestoAprueba'),
                    $('#areaPlan'),
                    $('#usuarioApruebaPuesto'),
                    $('#usuarioApruebaArea')
                );
            }

            $('.plan-row').each(function() {
                poblarUsuariosEnFila($(this));
            });

            if (typeof onReady === 'function') {
                onReady();
            }
        },
        error: function() {
            console.error('Error al cargar datos del formulario');
        }
    });
}

// Carga un plan completo para editar
function cargarPlanParaEditar(planId) {
    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        data: {
            accion: 'detalle_plan',
            id: planId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const plan = response.data.plan;
                const actividades = response.data.actividades;
                
                // Establecer ID del plan para edición
                $('#plan_editar_id').val(planId);
                
                // Llenar datos básicos del plan
                $('textarea[name="objetivos_calidad"]').val(plan.titulo_objetivos || '');
                $('#areaPlan').val(plan.id_area || '').trigger('change');
                $('#usuarioPresenta').val(plan.id_presenta || '').trigger('change');
                $('#usuarioAprueba').val(plan.id_aprueba || '').trigger('change');
                
                // Procesar actividades por sección
                if (actividades && actividades.length > 0) {
                    // Agrupar actividades por sección
                    const actividadesPorSeccion = {};
                    actividades.forEach(act => {
                        const seccion = act.seccion;
                        if (!actividadesPorSeccion[seccion]) {
                            actividadesPorSeccion[seccion] = [];
                        }
                        actividadesPorSeccion[seccion].push(act);
                    });
                    
                    // Llenar cada sección
                    Object.keys(actividadesPorSeccion).forEach(nombreSeccion => {
                        const acts = actividadesPorSeccion[nombreSeccion];
                        // Mapear nombre de sección a ID de sección
                        const mapeoSecciones = {
                            '6.2 Personal': '6.2',
                            '6.3 Infraestructura': '6.3',
                            '6.4 Ambiente de Trabajo': '6.4',
                            '7.2 Determinación de los Requisitos': '7.2',
                            '7.6 Control de Cambios': '7.6',
                            '7.7 Control de Salidas no Conformes': '7.7',
                            '8.3 Control de Documentos del Sistema de Gestión': '8.3',
                            '8.8 Auditorías Internas y Externas': '8.8',
                            '8.9 Revisión por la dirección': '8.9',
                            'IV. Otras': 'iv'
                        };

                        const resolverSeccionId = function(valor) {
                            if (mapeoSecciones[valor]) {
                                return mapeoSecciones[valor];
                            }
                            let match = String(valor).match(/^(\d+\.\d+)/);
                            if (match) {
                                return match[1];
                            }
                            if (String(valor).trim().toLowerCase().startsWith('iv')) {
                                return 'iv';
                            }
                            return null;
                        };

                        const seccionId = resolverSeccionId(nombreSeccion);
                        //console.log('Procesando sección:', nombreSeccion, '-> ID:', seccionId);
                        
                        if (!seccionId) {
                            //console.warn('Sección no mapeada:', nombreSeccion);
                            return;
                        }
                        
                        const $tbody = obtenerTbodySeccionPlan(seccionId);
                        if (!$tbody.length) {
                            //console.warn('No se encontró tbody para:', seccionId);
                            return;
                        }
                        
                        // Limpiar filas existentes
                        $tbody.empty();
                        
                        // Agregar una fila por cada actividad
                        acts.forEach((act, index) => {
                            const id_actividad = act.id;
                            $tbody.append(crearFilaPlanHtml(index, seccionId, id_actividad));
                            const $row = $tbody.find('.plan-row').eq(index);
                            
                            // Llenar datos de la actividad
                            $row.find('input[name^="num_actividad"]').val(act.num_actividad || '');
                            $row.find('textarea[name^="actividad"]').val(act.actividad || '');
                            $row.find('textarea[name^="observaciones"]').val(act.observaciones || '');
                            
                            if (act.meses && act.meses.length > 0) {
                                act.meses.forEach(mes => {
                                    $row.find('input[type="checkbox"][value="' + mes + '"]').prop('checked', true);
                                });
                            }

                            poblarUsuariosEnFila($row);

                            if (act.responsable) {
                                $row.find('select.responsable-select').val(String(act.responsable)).trigger('change');
                            }

                            if (act.participantes && act.participantes.length > 0) {
                                let participantes = act.participantes.map(function(valor) {
                                    return String(valor);
                                });
                                $row.find('select.participantes-select').val(participantes).trigger('change');
                            }
                        });
                    });
                }
                
                // Aplicar auto-fill de puestos y áreas
                setTimeout(() => {
                    aplicarUsuarioASelects(
                        $('#usuarioPresenta').val(),
                        $('#puestoPresenta'),
                        $('#areaPlan'),
                        $('#usuarioPresentaPuesto'),
                        $('#usuarioPresentaArea')
                    );
                    aplicarUsuarioASelects(
                        $('#usuarioAprueba').val(),
                        $('#puestoAprueba'),
                        $('#areaPlan'),
                        $('#usuarioApruebaPuesto'),
                        $('#usuarioApruebaArea')
                    );
                }, 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el plan.'
                }).then(() => {
                    window.location.href = 'detalles_actividades_SGC.php';
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar el plan.'
            }).then(() => {
                window.location.href = 'detalles_actividades_SGC.php';
            });
        }
    });
}