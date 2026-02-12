let usuariosCache = [];

// Inicializa selectores y eventos si existen en la pagina.
$(document).ready(function() {
    // Si estamos en la pagina de detalle, cargar datos
    if ($('#numActividad').length && window.location.pathname.includes('detalle_actividad_SGC.php')) {
        let idDetalle = getQueryParam('id');
        if (idDetalle) {
            cargarDetalleCompleto(idDetalle);
        } else {
            window.location.href = 'detalles_actividades_SGC.php';
        }
    }

    // Si estamos en la pagina de registro, cargar datos del formulario
    if ($('#formActividad').length) {
        cargarDatosFormulario();
    }

    if ($('#tablaActividades').length) {
        cargarActividades();
    }

    if ($('#formActividad').length) {
        // Carga datos en modo edicion si viene id en la URL.
        let idEditar = getQueryParam('id');
        let enEdicion = false;
        if (idEditar) {
            $('#tituloRegistroSGC').text('Editar Registro SGC');
            document.title = 'Editar Registro SGC';
            enEdicion = true;
            editarActividad(idEditar);
        }

        // Envia alta o actualizacion de actividad.
        $('#formActividad').on('submit', function(e) {
            e.preventDefault();
            
            // Validar firmas
            let idArea = $('#areaPlan').val();
            let puestoPresenta = $('#puestoPresenta').val();
            let usuarioPresenta = $('#usuarioPresenta').val();
            let puestoAprueba = $('#puestoAprueba').val();
            let usuarioAprueba = $('#usuarioAprueba').val();
            
            if (!idArea || !puestoPresenta || !usuarioPresenta || !puestoAprueba || !usuarioAprueba) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Firmas incompletas',
                    text: 'Debes seleccionar el area, puesto y usuario que elabora y quien aprueba.'
                });
                return;
            }
            
            // Validar que haya al menos una actividad completa por sección
            let secciones = ['6-2', '6-3', '6-4', '7-2', '7-6', '7-7', '7-11', '8-8', 'iv'];
            let seccionesNombres = {
                '6-2': '6.2 Personal',
                '6-3': '6.3 Infraestructura',
                '6-4': '6.4 Ambiente de Trabajo',
                '7-2': '7.2 Determinación de los Requisitos',
                '7-6': '7.6 Control de Cambios',
                '7-7': '7.7 Control de Salidas no Conformes',
                '7-11': '7.11 Control de Dispositivos de Seguimiento y Medición',
                '8-8': '8.8 Revisión por la Dirección',
                'iv': 'IV. Comunicación con el Cliente'
            };
            
            for (let i = 0; i < secciones.length; i++) {
                let seccion = secciones[i];
                let seccionId = seccion === 'iv' ? 'iv' : seccion;
                let filas = $('#plan-rows-' + seccionId + ' .plan-row');
                
                if (filas.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sección incompleta',
                        text: 'La sección "' + seccionesNombres[seccion] + '" no tiene filas.'
                    });
                    return;
                }
                
                let tieneActividad = false;
                
                filas.each(function() {
                    // Buscar los elementos dentro de esta fila
                    let textareaActividad = $(this).find('textarea[name*="actividad[' + seccionId + ']"]');
                    let selectResponsable = $(this).find('select[name*="responsable[' + seccionId + ']"]');
                    let checkboxesMeses = $(this).find('input[name*="meses[' + seccionId + ']"]:checked');
                    
                    let actividad = textareaActividad.val() ? textareaActividad.val().trim() : '';
                    let responsable = selectResponsable.val();
                    let meses = checkboxesMeses.length;
                    
                    if (actividad && responsable && meses > 0) {
                        tieneActividad = true;
                        return false; // break
                    }
                });
                
                if (!tieneActividad) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sección incompleta',
                        text: 'La sección "' + seccionesNombres[seccion] + '" debe tener al menos una actividad completa (actividad, responsable y al menos un mes seleccionado).'
                    });
                    return;
                }
            }
            
            let accion = $('#actividad_id').val() ? 'actualizar' : 'crear';
            let datos = $(this).serialize() + '&accion=' + accion;

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
                            text: 'No se pudo guardar.'
                        });
                        return;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        text: 'Registro guardado.'
                    }).then(function() {
                        if (enEdicion || $('#actividad_id').val()) {
                            window.location.href = 'detalles_actividades_SGC.php';
                            return;
                        }
                        if ($('#tablaActividades').length) {
                            cargarActividades();
                        }
                        $('#formActividad')[0].reset();
                        resetPlanRows();

                        if ($('#areaPlan').length) {
                            $('#areaPlan').val('').trigger('change');
                        }
                        if ($('#puestoPresenta').length) {
                            $('#puestoPresenta').val('').trigger('change');
                        }
                        if ($('#usuarioPresenta').length) {
                            $('#usuarioPresenta').val('').trigger('change');
                        }
                        if ($('#puestoAprueba').length) {
                            $('#puestoAprueba').val('').trigger('change');
                        }
                        if ($('#usuarioAprueba').length) {
                            $('#usuarioAprueba').val('').trigger('change');
                        }
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
        });
    }

    if ($('#tablaActividades').length) {
        // Carga datos de una actividad para editar.
        $('#tablaActividades tbody').on('click', '.btn-editar', function() {
            let id = $(this).data('id');
            if ($('#formActividad').length) {
                editarActividad(id);
            } else {
                window.location.href = 'registro_actividades_SGC.php?id=' + id;
            }
        });

        // Solicita eliminacion de una actividad.
        $('#tablaActividades tbody').on('click', '.btn-eliminar', function() {
            let id = $(this).data('id');
            eliminarActividad(id);
        });

        // Redirige a la pagina de detalle.
        $('#tablaActividades tbody').on('click', '.btn-ver-detalle', function() {
            let id = $(this).data('id');
            window.location.href = 'detalle_actividad_SGC.php?id=' + id;
        });
    }
});

// Convierte texto a mayusculas y sin acentos.
function convertirTexto(e) {
    // Función desactivada: permite escribir en minúsculas y con acentos
    return;
}

// Construye el HTML de una fila del plan de actividades para una sección dada.
function buildPlanRow(index, seccion) {
    let meses = [1,2,3,4,5,6,7,8,9,10,11,12];
    let mesCeldas = meses.map(function(m) {
        return '<td class="text-center">' +
            '<input type="checkbox" name="meses[' + seccion + '][' + index + '][]" value="' + m + '">' +
            '</td>';
    }).join('');

    // Convertir guión a punto para el número de actividad (7-11 -> 7.11)
    let seccionConPunto = seccion.replace('-', '.');
    let numeroActividad = seccionConPunto + '.' + (index + 1);

    return '<tr class="plan-row" data-row="' + index + '" data-seccion="' + seccion + '">' +
        '<td class="text-center">' +
            '<input type="text" class="form-control form-control-sm" name="num_actividad[' + seccion + '][' + index + ']" value="' + numeroActividad + '" readonly>' +
        '</td>' +
        '<td>' +
            '<textarea class="form-control form-control-sm" name="actividad[' + seccion + '][' + index + ']" rows="3" required onkeyup="convertirTexto(this)"></textarea>' +
        '</td>' +
        mesCeldas +
        '<td>' +
            '<select class="form-select form-select-sm responsable-select" name="responsable[' + seccion + '][' + index + ']" required>' +
                '<option value="">Selecciona...</option>' +
            '</select>' +
        '</td>' +
        '<td>' +
            '<select class="form-select form-select-sm participantes-select" name="participantes[' + seccion + '][' + index + '][]" multiple></select>' +
        '</td>' +
        '<td>' +
            '<textarea class="form-control form-control-sm" name="observaciones[' + seccion + '][' + index + ']" rows="3" onkeyup="convertirTexto(this)"></textarea>' +
        '</td>' +
    '</tr>';
}

function agregarFilaPlan(seccion) {
    let $tbody = $('#plan-rows-' + seccion.replace('.', '-'));
    let $rows = $tbody.find('.plan-row');
    let nextIndex = $rows.length;
    $tbody.append(buildPlanRow(nextIndex, seccion));
    let $newRow = $tbody.find('.plan-row').last();
    poblarUsuariosEnFila($newRow);
    actualizarNumerosActividades(seccion);
}

function quitarFilaPlan(seccion) {
    let $tbody = $('#plan-rows-' + seccion.replace('.', '-'));
    let $rows = $tbody.find('.plan-row');
    if ($rows.length <= 1) {
        return;
    }
    $rows.last().remove();
    actualizarNumerosActividades(seccion);
}

function actualizarNumerosActividades(seccion) {
    let $tbody = $('#plan-rows-' + seccion.replace('.', '-'));
    let $rows = $tbody.find('.plan-row');
    
    // Convertir guión a punto para el número de actividad
    let seccionConPunto = seccion.replace('-', '.');
    
    $rows.each(function(index) {
        let $input = $(this).find('input[name^="num_actividad["]');
        let numeroActividad = seccionConPunto + '.' + (index + 1);
        $input.val(numeroActividad);
    });
}

function resetPlanRows() {
    let secciones = ['6-2', '6-3', '6-4', '7-2', '7-6', '7-7', '7-11', '8-8', 'IV'];
    secciones.forEach(function(seccion) {
        let $tbody = $('#plan-rows-' + seccion);
        if ($tbody.length === 0) return; // Skip if tbody doesn't exist
        $tbody.find('.plan-row').remove();
        let seccionOriginal = seccion.replace('-', '.');
        $tbody.append(buildPlanRow(0, seccionOriginal));
        poblarUsuariosEnFila($tbody.find('.plan-row').first());
        actualizarNumerosActividades(seccionOriginal);
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

    if ($responsable.hasClass('select2-hidden-accessible')) {
        $responsable.select2('destroy');
    }
    if ($participantes.hasClass('select2-hidden-accessible')) {
        $participantes.select2('destroy');
    }

    $responsable.select2({
        placeholder: 'Selecciona responsable',
        width: '100%'
    });
    $participantes.select2({
        placeholder: 'Selecciona participantes',
        width: '100%'
    });
}

// Obtiene el valor de un parametro en la URL.
function getQueryParam(key) {
    let params = new URLSearchParams(window.location.search);
    return params.get(key);
}

// Obtiene el valor de una cookie por su nombre.
function getCookie(name) {
    let value = "; " + document.cookie;
    let parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
}

// Carga actividades y llena la tabla.
function cargarActividades() {
    if (!$('#tablaActividades').length) {
        return;
    }

    // Consulta actividades en backend.
    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'listar' },
        success: function(resp) {
            if (!resp.success) {
                return;
            }

            let rows = resp.data || [];
            if ($.fn.DataTable.isDataTable('#tablaActividades')) {
                let tabla = $('#tablaActividades').DataTable();
                tabla.clear();
                tabla.rows.add(rows).draw();
                return;
            }

            $('#tablaActividades').DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                searching: true,
                ordering: true,
                responsive: true,
                autoWidth: true,
                language: {
                    processing: "Procesando...",
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros en total)",
                    loadingRecords: "Cargando...",
                    zeroRecords: "No se encontraron resultados",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Ultimo"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna de manera ascendente",
                        sortDescending: ": activar para ordenar la columna de manera descendente"
                    }
                },
                data: rows,
                columns: [
                    { 
                        data: 'plan_id',
                        render: function(data) {
                            return '<span class="text-dark fw-bold">' + (data || '-') + '</span>';
                        }
                    },
                    { 
                        data: 'anio',
                        render: function(data) {
                            return '<span class="badge bg-success">' + (data || '-') + '</span>';
                        }
                    },
                    { 
                        data: 'seccion',
                        render: function(data) {
                            return '<span class="badge bg-info text-dark">' + (data || '-') + '</span>';
                        }
                    },
                    { 
                        data: null,
                        render: function(data, type, row) {
                            return '<strong class="text-primary">' + (row.num_actividad || '') + '</strong> ' + 
                                   '<span class="text-dark">' + (row.actividad || '-') + '</span>';
                        }
                    },
                    { 
                        data: 'meses_texto',
                        render: function(data) {
                            return '<span class="badge bg-secondary">' + (data || '-') + '</span>';
                        }
                    },
                    { 
                        data: 'responsable',
                        render: function(data) {
                            return '<span class="text-dark">' + (data || '-') + '</span>';
                        }
                    },
                    { 
                        data: 'participantes', 
                        render: function(data) { 
                            return '<span class="text-muted small">' + ((data || []).join(', ') || '-') + '</span>'; 
                        } 
                    },
                    { 
                        data: 'periodo_registro',
                        render: function(data) {
                            if (!data) return '-';
                            return '<i class="far fa-calendar-alt me-1 text-dark"></i><span class="text-dark small">' + data + '</span>';
                        }
                    },
                    { 
                        data: null, 
                        orderable: false,
                        className: 'text-center',
                        render: function(data) {
                            return '<button class="btn btn-sm btn-outline-info btn-editar me-1" data-id="' + data.id + '" title="Editar">' +
                                   '<i class="fas fa-pen"></i></button>' +
                                   '<button class="btn btn-sm btn-outline-warning btn-ver-detalle" data-id="' + data.id + '" title="Ver detalle">' +
                                   '<i class="fas fa-eye"></i></button>' +
                                   '<button class="btn btn-sm btn-outline-danger btn-eliminar me-1" data-id="' + data.id + '" title="Eliminar">' +
                                   '<i class="fas fa-trash"></i></button>'; 
                        }
                    }
                ]
            });
        }
    });
}

// Obtiene detalle de actividad y llena el formulario.
function editarActividad(id) {
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
            resetPlanRows();
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
function eliminarActividad(id) {
    Swal.fire({
        title: 'Eliminar',
        text: '¿Seguro que deseas eliminar la actividad?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }
        // Solicita eliminacion al backend.
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
                Swal.fire({ icon: 'success', title: 'Eliminado', text: 'Registro eliminado.' });
                cargarActividades();
            }
        });
    });
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
function cargarDatosFormulario() {
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
            let puestos = data.puestos || [];
            let areas = data.areas || [];

            if ($('#puestoPresenta').length) {
                $('#areaPlan').empty().append('<option value="">Selecciona un area...</option>');
                $('#puestoPresenta').empty().append('<option value="">Selecciona un puesto...</option>');
                $('#puestoAprueba').empty().append('<option value="">Selecciona un puesto...</option>');
                $('#usuarioPresenta').empty().append('<option value="">Selecciona un usuario...</option>');
                $('#usuarioAprueba').empty().append('<option value="">Selecciona un usuario...</option>');

                areas.forEach(function(area) {
                    let option = $('<option></option>')
                        .attr('value', area.id_area)
                        .text(area.nombre_area);
                    $('#areaPlan').append(option);
                });

                puestos.forEach(function(puesto) {
                    let option = $('<option></option>')
                        .attr('value', puesto.id_puesto)
                        .text(puesto.nombre_puesto);
                    $('#puestoPresenta').append(option.clone());
                    $('#puestoAprueba').append(option);
                });

                usuariosCache.forEach(function(usuario) {
                    let option = $('<option></option>')
                        .attr('value', usuario.id_usuario)
                        .text(usuario.nombre);
                    $('#usuarioPresenta').append(option.clone());
                    $('#usuarioAprueba').append(option);
                });

                if (!$('#areaPlan').hasClass('select2-hidden-accessible')) {
                    $('#areaPlan').select2({ width: '100%' });
                }
                if (!$('#puestoPresenta').hasClass('select2-hidden-accessible')) {
                    $('#puestoPresenta').select2({ width: '100%' });
                }
                if (!$('#puestoAprueba').hasClass('select2-hidden-accessible')) {
                    $('#puestoAprueba').select2({ width: '100%' });
                }
                if (!$('#usuarioPresenta').hasClass('select2-hidden-accessible')) {
                    $('#usuarioPresenta').select2({ width: '100%' });
                }
                if (!$('#usuarioAprueba').hasClass('select2-hidden-accessible')) {
                    $('#usuarioAprueba').select2({ width: '100%' });
                }
            }

            $('.plan-row').each(function() {
                poblarUsuariosEnFila($(this));
            });
        },
        error: function() {
            console.error('Error al cargar datos del formulario');
        }
    });
}
