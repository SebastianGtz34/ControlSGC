<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PLANEACION</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>        
        .quitarbarra {
            width: 100%;
            clip-path: polygon(0% 0%, 100% 0%, 100% calc(100% - 37px), 0% calc(100% - 37px));
        }
    </style>
<?php
    $usuariosRegistran = array(212, 14, 42, 161, 403, 183, 521, 276, 26, 147, 189, 177, 45, 26, 525, 435, 489, 523, 298, 81, 203, 8, 278, 206, 123, 516);
    if (in_array($_COOKIE['noEmpleado'], $usuariosRegistran)) {
        // El usuario tiene permiso para ver la página
    } else {
        header("Location: seguimiento_actividades.php");
    }
?>
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
                            <div class="card shadow">
                                <!-- Card Body -->
                                <div class="card-body   card-header">
                                    <div class="row">   
                                        <div class="col-xl-12 mb-0" style="text-align: center">
                                            <center>
                                                <h4>REGISTRO DE ACTIVIDADES</h4>
                                            </center>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-sm-12 mb-0">
                                            <embed class="quitarbarra" id="vistaPrevia" src='https://app.powerbi.com/view?r=eyJrIjoiODJkZWY0MjQtODQxNC00YWJmLWIzOWMtMThhYTEyODdmZmMwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=4c96c5accec6d9000806' type="application/pdf" width="100%" height="500px" />
                                        </div>
                                    </div>
                                </div>
                            </div>
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
            
        });
        
        // Convertir a mayúsculas y quitar acentos
        function convertirTexto(e) {    
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }

        // Función para obtener el valor de una cookie por su nombre
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }
    </script>
</body>

</html>
