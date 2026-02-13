<?php
header('Location: registro_actividades_SGC.php');
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Control SGC</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php
            include 'menu.php';
        ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php
                    include 'encabezado.php';
                ?>
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-xl-12">
                            <?php include 'registro_actividades_SGC.php'; ?>
                            <?php include 'detalles_actividades_SGC.php'; ?>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MESS 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cerrar sesión</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">¿Estas seguro?</div>
                <div class="modal-footer">
                    <button class="btn btn-info" type="button" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-danger" href="logout">Salir</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript
    <script src = "vendor/jquery/jquery.min.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" defer="defer"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script type="text/javascript">
        $(document).ready(function() {
            $('#participantes').select2({
                placeholder: 'Selecciona participantes',
                width: '100%'
            });
            $('#responsable').select2({
                placeholder: 'Selecciona responsable',
                width: '100%'
            });

            // Envia alta/actualizacion de actividad.
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
                    });
                    $('#formActividad')[0].reset();
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

        });
        
        // Convierte texto a mayusculas y sin acentos.
        function convertirTexto(e) {    
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }

        // Obtiene el valor de una cookie por su nombre.
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
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
                        Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo cargar.' });
                        return;
                    }
                    let a = resp.data.actividad;
                    $('#actividad_id').val(a.id);
                    $('#num_actividad').val(a.num_actividad);
                    $('#actividad').val(a.actividad);
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
                    }
                });
            });
        }
    </script>
</body>

</html>
