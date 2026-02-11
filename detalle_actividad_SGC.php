<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Detalle Actividad - Control SGC</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4 border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-primary border-4">
                                        <div>
                                            <h5 class="text-uppercase text-secondary mb-1">Actividad SGC</h5>
                                            <h3 class="text-primary fw-bold mb-0" id="numActividad">#</h3>
                                        </div>
                                        <div>
                                            <button class="btn btn-outline-warning" onclick="window.location.href='detalles_actividades_SGC.php'">
                                                <i class="fas fa-arrow-left me-2"></i>Volver
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <h6 class="text-uppercase text-secondary fw-bold mb-3">Información General</h6>
                                                <div class="mb-3">
                                                    <label class="text-muted small mb-1 fw-bold">Actividad:</label>
                                                    <p class="mb-0" id="actividad">-</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small mb-1 fw-bold">Categoría:</label>
                                                    <p class="mb-0" id="categoria">
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-tag me-1"></i>-
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small mb-1 fw-bold">Recurrencia:</label>
                                                    <p class="mb-0" id="recurrencia">
                                                        <span class="badge bg-primary">-</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <h6 class="text-uppercase text-secondary fw-bold mb-3">Personal Involucrado</h6>
                                                <div class="mb-3">
                                                    <label class="text-muted small mb-1 fw-bold">Responsable:</label>
                                                    <p class="mb-0" id="responsable">-</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="text-muted small mb-1 fw-bold">Participantes:</label>
                                                    <p class="mb-0 text-muted" id="participantes">-</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <h6 class="text-uppercase text-secondary fw-bold mb-3">Periodo y Programación</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="text-muted small mb-1 fw-bold">Fecha de Registro:</label>
                                                            <p class="mb-0" id="fechaRegistro">
                                                                <i class="far fa-calendar-alt me-2 text-dark"></i>-
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="text-muted small mb-1 fw-bold">Meses Aplicables:</label>
                                                            <p class="mb-0 text-muted" id="meses">-</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-secondary fw-bold mb-3">Observaciones</h6>
                                                <div class="bg-light p-3 rounded">
                                                    <p class="mb-0 text-muted" id="observaciones">Sin observaciones registradas</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MESS 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/actividades_sgc.js"></script>
</body>
</html>
