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
    e.value = e.value
        .toUpperCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "");
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
                        data: 'categoria',
                        render: function(data, type, row) {
                            return '<span class="badge bg-secondary"><i class="fas fa-tag me-1"></i>' + (data || 'Sin categoría') + '</span>';
                        }
                    },
                    { 
                        data: 'recurrencia',
                        render: function(data) {
                            return '<span class="badge bg-primary">' + data + '</span>';
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
                            return '<i class="far fa-calendar-alt me-1 text-dark"></i><span class="text-dark fw-bold">' + data + '</span>';
                        }
                    },
                    { 
                        data: 'observaciones',
                        render: function(data) {
                            return '<span class="text-muted small">' + (data || '-') + '</span>';
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
            $('#actividad_id').val(a.id);
            $('#num_actividad').val(a.num_actividad);
            $('#actividad').val(a.actividad);
            $('#id_categoria').val(a.id_categoria);
            $('#id_recurrencia').val(a.id_recurrencia);
            $('#periodo_registro').val(a.periodo_registro);
            $('#observaciones').val(a.observaciones);

            $('input[name="meses[]"]').prop('checked', false);
            (resp.data.meses || []).forEach(function(m) {
                $('#mes_' + m).prop('checked', true);
            });

            $('#responsable').val(resp.data.responsable).trigger('change');
            $('#participantes').val(resp.data.participantes).trigger('change');
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

            // Cargar categorias
            $('#id_categoria').empty().append('<option value="">Selecciona...</option>');
            data.categorias.forEach(function(cat) {
                $('#id_categoria').append('<option value="' + cat.id + '">' + cat.nombre + '</option>');
            });

            // Cargar recurrencias
            $('#id_recurrencia').empty().append('<option value="">Selecciona...</option>');
            data.recurrencias.forEach(function(rec) {
                $('#id_recurrencia').append('<option value="' + rec.id + '">' + rec.tipo_tiempo + '</option>');
            });

            // Cargar responsable
            $('#responsable').empty().append('<option value="">Selecciona...</option>');
            data.usuarios.forEach(function(usr) {
                $('#responsable').append('<option value="' + usr.noEmpleado + '">' + usr.nombre + '</option>');
            });

            // Cargar participantes
            $('#participantes').empty();
            data.usuarios.forEach(function(usr) {
                $('#participantes').append('<option value="' + usr.noEmpleado + '">' + usr.nombre + '</option>');
            });

            // Re-inicializar Select2 si ya estaba inicializado
            if ($('#responsable').hasClass('select2-hidden-accessible')) {
                $('#responsable').select2('destroy');
            }
            if ($('#participantes').hasClass('select2-hidden-accessible')) {
                $('#participantes').select2('destroy');
            }
            
            $('#responsable').select2({
                placeholder: 'Selecciona responsable',
                width: '100%'
            });
            $('#participantes').select2({
                placeholder: 'Selecciona participantes',
                width: '100%'
            });
        },
        error: function() {
            console.error('Error al cargar datos del formulario');
        }
    });
}
