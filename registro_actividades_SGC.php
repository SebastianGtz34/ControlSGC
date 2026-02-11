<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Control SGC</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                            <div class="card shadow mb-4">
                                <div class="card-header text-center">
                                    <h4 id="tituloRegistroSGC">REGISTRO SGC</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <form id="formActividad">
                                                <input type="hidden" name="id" id="actividad_id">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Categoria</label>
                                                        <select class="form-select" name="id_categoria" id="id_categoria" required>
                                                            <option value="">Selecciona...</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Recurrencia</label>
                                                        <select class="form-select" name="id_recurrencia" id="id_recurrencia" required>
                                                            <option value="">Selecciona...</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">No. Actividad</label>
                                                        <input type="text" class="form-control" name="num_actividad" id="num_actividad" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Actividad</label>
                                                        <textarea class="form-control" name="actividad" id="actividad" rows="2" required onkeyup="convertirTexto(this)"></textarea>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Periodo de registro</label>
                                                        <input type="date" class="form-control" name="periodo_registro" id="periodo_registro">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Meses</label>
                                                        <div class="d-flex flex-wrap gap-3">
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="1" id="mes_1"><label class="form-check-label" for="mes_1">E</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="2" id="mes_2"><label class="form-check-label" for="mes_2">F</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="3" id="mes_3"><label class="form-check-label" for="mes_3">M</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="4" id="mes_4"><label class="form-check-label" for="mes_4">A</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="5" id="mes_5"><label class="form-check-label" for="mes_5">M</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="6" id="mes_6"><label class="form-check-label" for="mes_6">J</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="7" id="mes_7"><label class="form-check-label" for="mes_7">J</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="8" id="mes_8"><label class="form-check-label" for="mes_8">A</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="9" id="mes_9"><label class="form-check-label" for="mes_9">S</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="10" id="mes_10"><label class="form-check-label" for="mes_10">O</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="11" id="mes_11"><label class="form-check-label" for="mes_11">N</label></div>
                                                            <div class="form-check"><input class="form-check-input" type="checkbox" name="meses[]" value="12" id="mes_12"><label class="form-check-label" for="mes_12">D</label></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Responsable</label>
                                                        <select class="form-select" name="responsable" id="responsable" required>
                                                            <option value="">Selecciona...</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Participantes</label>
                                                        <select class="form-select" name="participantes[]" id="participantes" multiple>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label class="form-label">Observaciones</label>
                                                        <textarea class="form-control" name="observaciones" id="observaciones" rows="2" onkeyup="convertirTexto(this)"></textarea>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12 d-flex gap-2">
                                                        <button type="submit" class="btn btn-success">Guardar</button>
                                                    </div>
                                                </div>
                                            </form>
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
    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" defer="defer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/actividades_sgc.js"></script>
</body>
</html>
