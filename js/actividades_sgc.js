let usuariosCache = [];
let puestosCache = [];
let areasCache = [];

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

    if ($('#tablaActividades').length) {
        cargarPlanesAnuales();
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
            let idEditar = getQueryParam('id');
            if (idEditar) {
                $('#tituloRegistroSGC').text('Editar Registro SGC');
                document.title = 'Editar Registro SGC';
                enEdicion = true;
                editarActividad(idEditar);
            }
        };

        // Cargar datos del formulario y luego iniciar edicion si aplica
        cargarDatosFormulario(iniciarEdicion);

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
            let secciones = ['6.2', '6.3', '6.4', '7.2', '7.6', '7.7', '7.11', '8.8'];
            let seccionesNombres = {
                '6.2': '6.2 Personal',
                '6.3': '6.3 Infraestructura',
                '6.4': '6.4 Ambiente de Trabajo',
                '7.2': '7.2 Determinación de los Requisitos',
                '7.6': '7.6 Control de Cambios',
                '7.7': '7.7 Control de Salidas no Conformes',
                '7.11': '7.11 Control de Dispositivos de Seguimiento y Medición',
                '8.8': '8.8 Revisión por la Dirección'
            };
            
            for (let i = 0; i < secciones.length; i++) {
                let seccion = secciones[i];
                let seccionId = seccion === 'iv' ? 'iv' : seccion;
                let $tbody = getPlanRowsTbody(seccionId);
                let filas = $tbody.find('.plan-row');
                
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
            let planEditarId = $('#plan_editar_id').val();
            
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
                                text: 'No se pudo guardar.'
                            });
                            return;
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Listo',
                            text: 'Registro guardado.'
                        }).then(function() {
                            if (enEdicion || $('#actividad_id').val() || planEditarId) {
                                if (planEditarId) {
                                    localStorage.removeItem('planEditarId');
                                }
                                window.location.href = 'detalles_actividades_SGC.php';
                                return;
                            }
                            $('#formActividad')[0].reset();
                            resetPlanRows();

                            if ($('#areaPlan').length) {
                                $('#areaPlan').val('');
                            }
                            if ($('#puestoPresenta').length) {
                                $('#puestoPresenta').val('');
                            }
                            if ($('#usuarioPresenta').length) {
                                $('#usuarioPresenta').val('').trigger('change');
                            }
                            if ($('#puestoAprueba').length) {
                                $('#puestoAprueba').val('');
                            }
                            if ($('#usuarioAprueba').length) {
                                $('#usuarioAprueba').val('').trigger('change');
                            }
                            $('#usuarioPresentaArea').text('S/R');
                            $('#usuarioPresentaPuesto').text('S/R');
                            $('#usuarioApruebaArea').text('S/R');
                            $('#usuarioApruebaPuesto').text('S/R');
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

// Event listener para el botón de editar plan
$(document).on('click', '.btn-editar-plan', function() {
    const planId = $(this).data('id');
    // Guardar el ID del plan en localStorage
    localStorage.setItem('planEditarId', planId);
    // Redirigir a la página de registro
    window.location.href = 'editar_plan.php';
});

// Convierte texto a mayusculas y sin acentos.
function convertirTexto(e) {
    // Funcion desactivada: permite escribir en minusculas y con acentos
    return;
}

// Construye el HTML de una fila del plan de actividades para una seccion dada.
function buildPlanRow(index, seccion) {
    let meses = [1,2,3,4,5,6,7,8,9,10,11,12];
    let mesCeldas = meses.map(function(m) {
        return '<td class="text-center">' +
            '<input type="checkbox" name="meses[' + seccion + '][' + index + '][]" value="' + m + '">' +
            '</td>';
    }).join('');

    let seccionKey = String(seccion).toLowerCase();
    let baseNumero = seccionKey === 'iv' ? 'IV' : seccion;
    let numeroActividad = baseNumero + '.' + (index + 1);

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

function getPlanRowsTbody(seccion) {
    let key = String(seccion).toLowerCase();
    return $('#' + $.escapeSelector('plan-rows-' + key));
}

function agregarFilaPlan(seccion) {
    let $tbody = getPlanRowsTbody(seccion);
    let $rows = $tbody.find('.plan-row');
    let nextIndex = $rows.length;
    $tbody.append(buildPlanRow(nextIndex, seccion));
    let $newRow = $tbody.find('.plan-row').last();
    poblarUsuariosEnFila($newRow);
    actualizarNumerosActividades(seccion);
}

function quitarFilaPlan(seccion) {
    let $tbody = getPlanRowsTbody(seccion);
    let $rows = $tbody.find('.plan-row');
    if ($rows.length <= 1) {
        return;
    }
    $rows.last().remove();
    actualizarNumerosActividades(seccion);
}

function actualizarNumerosActividades(seccion) {
    let $tbody = getPlanRowsTbody(seccion);
    let $rows = $tbody.find('.plan-row');
    let seccionKey = String(seccion).toLowerCase();
    let baseNumero = seccionKey === 'iv' ? 'IV' : seccion;
    
    $rows.each(function(index) {
        let $input = $(this).find('input[name^="num_actividad["]');
        let numeroActividad = baseNumero + '.' + (index + 1);
        $input.val(numeroActividad);
    });
}

function resetPlanRows() {
    let secciones = ['6.2', '6.3', '6.4', '7.2', '7.6', '7.7', '7.11', '8.8', 'iv'];
    secciones.forEach(function(seccion) {
        let $tbody = getPlanRowsTbody(seccion);
        if ($tbody.length === 0) return; // Skip if tbody doesn't exist
        $tbody.find('.plan-row').remove();
        $tbody.append(buildPlanRow(0, seccion));
        poblarUsuariosEnFila($tbody.find('.plan-row').first());
        actualizarNumerosActividades(seccion);
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
    if ($areaInput && usuario.departamento) {
        $areaInput.val(usuario.departamento);
    }

    if ($puestoText) {
        $puestoText.text(obtenerPuestoNombre(usuario.puesto) || 'S/R');
    }
    if ($areaText) {
        $areaText.text(obtenerAreaNombre(usuario.departamento) || 'S/R');
    }
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

// Carga planes anuales y llena la tabla.
function cargarPlanesAnuales() {
    if (!$('#tablaActividades').length) {
        return;
    }

    $.ajax({
        url: 'acciones_actividades.php',
        method: 'POST',
        dataType: 'json',
        data: { accion: 'listar_planes' },
        success: function(resp) {
            if (!resp.success) {
                return;
            }

            let rows = resp.data || [];
            if ($.fn.DataTable.isDataTable('#tablaActividades')) {
                let tabla = $('#tablaActividades').DataTable();
                tabla.destroy();
                $('#tablaActividades tbody').empty();
            }

            $('#tablaActividades').DataTable({
                paging: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                searching: true,
                ordering: true,
                order: [[6, 'desc']],
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
                        data: 'estatus',
                        render: function(data) {
                            return '<span class="badge bg-primary">' + (data || 'S/R') + '</span>';
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
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la tabla de planes.'
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
                
                console.log('Plan cargado:', plan);
                console.log('Actividades:', actividades);
                
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
                    
                    console.log('Actividades por sección:', actividadesPorSeccion);
                    
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
                            '7.11 Control de Dispositivos de Seguimiento y Medición': '7.11',
                            '8.8 Revisión por la Dirección': '8.8',
                            'IV. Otras': 'iv',
                            '6.2': '6.2',
                            '6.3': '6.3',
                            '6.4': '6.4',
                            '7.2': '7.2',
                            '7.6': '7.6',
                            '7.7': '7.7',
                            '7.11': '7.11',
                            '8.8': '8.8',
                            'IV': 'iv'
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
                        console.log('Procesando sección:', nombreSeccion, '-> ID:', seccionId);
                        
                        if (!seccionId) {
                            console.warn('Sección no mapeada:', nombreSeccion);
                            return;
                        }
                        
                        const $tbody = getPlanRowsTbody(seccionId);
                        if (!$tbody.length) {
                            console.warn('No se encontró tbody para:', seccionId);
                            return;
                        }
                        
                        // Limpiar filas existentes
                        $tbody.empty();
                        
                        console.log('Agregando', acts.length, 'actividades a sección', nombreSeccion);
                        
                        // Agregar una fila por cada actividad
                        acts.forEach((act, index) => {
                            $tbody.append(buildPlanRow(index, seccionId));
                            const $row = $tbody.find('.plan-row').eq(index);
                            
                            console.log('Fila #' + index, 'Actividad:', act.actividad);
                            
                            // Llenar datos de la actividad
                            $row.find('input[name^="num_actividad"]').val(act.num_actividad || '');
                            $row.find('textarea[name^="actividad"]').val(act.actividad || '');
                            $row.find('textarea[name^="observaciones"]').val(act.observaciones || '');
                            
                            // Marcar meses ANTES de inicializar Select2
                            if (act.meses && act.meses.length > 0) {
                                console.log('Marcando meses:', act.meses);
                                act.meses.forEach(mes => {
                                    $row.find('input[type="checkbox"][value="' + mes + '"]').prop('checked', true);
                                });
                            }
                            
                            // Asignar valores a los selects ANTES de inicializar Select2
                            const $responsable = $row.find('select.responsable-select');
                            const $participantes = $row.find('select.participantes-select');
                            
                            // Llenar opciones del select
                            $responsable.empty().append('<option value="">Selecciona...</option>');
                            $participantes.empty();
                            
                            usuariosCache.forEach(function(usr) {
                                $responsable.append('<option value="' + usr.id_usuario + '">' + usr.nombre + '</option>');
                                $participantes.append('<option value="' + usr.id_usuario + '">' + usr.nombre + '</option>');
                            });
                            
                            // Asignar valores ANTES de Select2
                            if (act.responsable) {
                                console.log('Asignando responsable PRE-Select2:', act.responsable);
                                $responsable.val(act.responsable);
                            }
                            
                            if (act.participantes && act.participantes.length > 0) {
                                console.log('Asignando participantes PRE-Select2:', act.participantes);
                                $participantes.val(act.participantes);
                            }
                            
                            // AHORA SÍ inicializar Select2 con los valores ya asignados
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
                            
                            console.log('Select2 inicializado con valores:', {
                                responsable: $responsable.val(),
                                participantes: $participantes.val()
                            });
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
