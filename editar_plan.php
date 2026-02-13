<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Control SGC - Editar Plan</title>

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
                <?php //include 'encabezado.php'; ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <form id="formActividad">
                                        <input type="hidden" name="id" id="actividad_id">
                                        <input type="hidden" name="id_plan_anual" id="id_plan_anual" value="1">
                                        <input type="hidden" name="plan_editar_id" id="plan_editar_id">

                                        <div class="text-center py-2 mb-3">
                                            <div class="fw-bold text-uppercase" id="tituloFormulario">Editar Plan Anual de Actividades del Año: 2026</div>
                                        </div>

                                        <div class="fw-bold text-uppercase mb-2">I. Objetivos de calidad</div>
                                        <textarea class="form-control border-dark mb-3" name="objetivos_calidad" rows="4" placeholder="Escribe los objetivos de calidad..."></textarea>

                                        <div class="fw-bold text-uppercase mb-2">II. Plan de actividades</div>

                                        <div class="accordion mb-3" id="accordionPlan">
                                            <!-- SECCIÓN 6.2 Personal -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button bg-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6-2" aria-expanded="true" aria-controls="collapse6-2">
                                                        <span class="fw-bold text-black">6.2 Personal</span>
                                                    </button>
                                                </h2>
                                                <div id="collapse6-2" class="accordion-collapse collapse show" data-bs-parent="#accordionPlan">
                                                    <div class="accordion-body p-0">
                                                        <div class="table-responsive border-bottom border-dark mb-3">
                                                            <table class="table table-bordered align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="text-center" rowspan="2">No</th>
                                                                        <th rowspan="2">Actividades</th>
                                                                        <th colspan="12">Meses</th>
                                                                        <th rowspan="2">Responsable</th>
                                                                        <th rowspan="2">Participantes</th>
                                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="text-center">E</th>
                                                                        <th class="text-center">F</th>
                                                                        <th class="text-center">M</th>
                                                                        <th class="text-center">A</th>
                                                                        <th class="text-center">M</th>
                                                                        <th class="text-center">J</th>
                                                                        <th class="text-center">J</th>
                                                                        <th class="text-center">A</th>
                                                                        <th class="text-center">S</th>
                                                                        <th class="text-center">O</th>
                                                                        <th class="text-center">N</th>
                                                                        <th class="text-center">D</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="plan-rows-6.2">
                                                                    <tr class="plan-row" data-row="0" data-seccion="6.2">
                                                                        <td class="text-center">
                                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[6.2][0]" value="6.2.1" readonly>
                                                                        </td>
                                                                        <td>
                                                                            <textarea class="form-control form-control-sm" name="actividad[6.2][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                                        </td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="1"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="2"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="3"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="4"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="5"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="6"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="7"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="8"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="9"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="10"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="11"></td>
                                                                        <td class="text-center"><input type="checkbox" name="meses[6.2][0][]" value="12"></td>
                                                                        <td>
                                                                            <select class="form-select form-select-sm responsable-select" name="responsable[6.2][0]">
                                                                                <option value="">Selecciona...</option>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-select form-select-sm participantes-select" name="participantes[6.2][0][]" multiple></select>
                                                                        </td>
                                                                        <td>
                                                                            <textarea class="form-control form-control-sm" name="observaciones[6.2][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>            
                                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('6.2')">Agregar fila</button>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('6.2')">Quitar fila</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- SECCIÓN 6.3 Instalaciones y condiciones ambientales -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6-3" aria-expanded="false" aria-controls="collapse6-3">
                                                        <span class="fw-bold text-black">6.3 Instalaciones y condiciones ambientales</span>
                                                    </button>
                                                </h2>
                                                <div id="collapse6-3" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                                    <div class="accordion-body p-0">
                                                        <div class="table-responsive border-bottom border-dark mb-3">
                                                            <table class="table table-bordered align-middle mb-0">
                                                                <thead>
                                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-6.3">
                                                    <tr class="plan-row" data-row="0" data-seccion="6.3">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[6.3][0]" value="6.3.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[6.3][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.3][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[6.3][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[6.3][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[6.3][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('6.3')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('6.3')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6-4" aria-expanded="false" aria-controls="collapse6-4">
                                        <span class="fw-bold text-black">6.4 Equipamiento</span>
                                    </button>
                                </h2>
                                <div id="collapse6-4" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-6.4">
                                                    <tr class="plan-row" data-row="0" data-seccion="6.4">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[6.4][0]" value="6.4.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[6.4][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[6.4][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[6.4][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[6.4][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[6.4][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('6.4')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('6.4')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 7.2 Selección, verificación y validación de métodos -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7-2" aria-expanded="false" aria-controls="collapse7-2">
                                        <span class="fw-bold text-black">7.2 Selección, verificación y validación de métodos</span>
                                    </button>
                                </h2>
                                <div id="collapse7-2" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-7.2">
                                                    <tr class="plan-row" data-row="0" data-seccion="7.2">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[7.2][0]" value="7.2.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[7.2][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.2][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[7.2][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[7.2][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[7.2][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('7.2')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('7.2')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 7.6 Evaluación de la incertidumbre de la medición -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7-6" aria-expanded="false" aria-controls="collapse7-6">
                                        <span class="fw-bold text-black">7.6 Evaluación de la incertidumbre de la medición</span>
                                    </button>
                                </h2>
                                <div id="collapse7-6" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-7.6">
                                                    <tr class="plan-row" data-row="0" data-seccion="7.6">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[7.6][0]" value="7.6.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[7.6][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.6][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[7.6][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[7.6][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[7.6][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('7.6')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('7.6')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 7.7 Aseguramiento de la validez de los resultados -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7-7" aria-expanded="false" aria-controls="collapse7-7">
                                        <span class="fw-bold text-black">7.7 Aseguramiento de la validez de los resultados</span>
                                    </button>
                                </h2>
                                <div id="collapse7-7" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-7.7">
                                                    <tr class="plan-row" data-row="0" data-seccion="7.7">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[7.7][0]" value="7.7.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[7.7][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.7][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[7.7][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[7.7][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[7.7][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('7.7')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('7.7')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 7.11 Control de los datos y gestión de la información -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7-11" aria-expanded="false" aria-controls="collapse7-11">
                                        <span class="fw-bold text-black">7.11 Control de los datos y gestión de la información</span>
                                    </button>
                                </h2>
                                <div id="collapse7-11" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-7.11">
                                                    <tr class="plan-row" data-row="0" data-seccion="7.11">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[7.11][0]" value="7.11.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[7.11][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[7.11][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[7.11][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[7.11][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[7.11][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('7.11')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('7.11')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 8.8 Auditorías internas Opción A -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8-8" aria-expanded="false" aria-controls="collapse8-8">
                                        <span class="fw-bold text-black">8.8 Auditorías internas Opción A</span>
                                    </button>
                                </h2>
                                <div id="collapse8-8" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-8.8">
                                                    <tr class="plan-row" data-row="0" data-seccion="8.8">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[8.8][0]" value="8.8.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[8.8][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[8.8][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[8.8][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[8.8][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[8.8][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('8.8')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('8.8')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN IV. Otras -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button bg-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseIV" aria-expanded="false" aria-controls="collapseIV">
                                        <span class="fw-bold text-black">IV. Otras</span>
                                    </button>
                                </h2>
                                <div id="collapseIV" class="accordion-collapse collapse" data-bs-parent="#accordionPlan">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive border-bottom border-dark mb-3">
                                            <table class="table table-bordered align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" rowspan="2">No</th>
                                                        <th rowspan="2">Actividades</th>
                                                        <th colspan="12">Meses</th>
                                                        <th rowspan="2">Responsable</th>
                                                        <th rowspan="2">Participantes</th>
                                                        <th rowspan="2">Observaciones sobre el cumplimiento</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-center">E</th>
                                                        <th class="text-center">F</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">M</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">J</th>
                                                        <th class="text-center">A</th>
                                                        <th class="text-center">S</th>
                                                        <th class="text-center">O</th>
                                                        <th class="text-center">N</th>
                                                        <th class="text-center">D</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="plan-rows-iv">
                                                    <tr class="plan-row" data-row="0" data-seccion="iv">
                                                        <td class="text-center">
                                                            <input type="text" class="form-control form-control-sm" name="num_actividad[iv][0]" value="IV.1" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="actividad[iv][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="1"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="2"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="3"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="4"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="5"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="6"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="7"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="8"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="9"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="10"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="11"></td>
                                                        <td class="text-center"><input type="checkbox" name="meses[iv][0][]" value="12"></td>
                                                        <td>
                                                            <select class="form-select form-select-sm responsable-select" name="responsable[iv][0]">
                                                                <option value="">Selecciona...</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm participantes-select" name="participantes[iv][0][]" multiple></select>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" name="observaciones[iv][0]" rows="3" onkeyup="convertirTexto(this)"></textarea>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-end gap-2 mb-3 p-3 pt-0">
                                            <button type="button" class="btn btn-outline-success btn-sm" onclick="agregarFilaPlan('iv')">Agregar fila</button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="quitarFilaPlan('iv')">Quitar fila</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN Firmas y aprobación -->
                        <div class="fw-bold text-uppercase bg-light py-2 px-2 mb-3 mt-4 border border-dark">Firmas y aprobación del plan</div>
                        <div class="row g-3 mb-3">
                            <div class="col-lg-6">
                                <div class="border border-dark p-3">
                                    <input type="hidden" name="puesto_presenta" id="puestoPresenta">
                                    <input type="hidden" name="id_area" id="areaPlan">
                                    <div class="fw-bold mb-2">Elaboro:</div>
                                    <select class="form-control form-control-sm mb-3" name="usuario_presenta" id="usuarioPresenta" style="width: 100%;" required>
                                        <option value="">Selecciona un usuario...</option>
                                    </select>
                                    <div class="small">
                                        <div><span class="fw-semibold">Area:</span> <span id="usuarioPresentaArea">S/R</span></div>
                                        <div><span class="fw-semibold">Puesto:</span> <span id="usuarioPresentaPuesto">S/R</span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="border border-dark p-3">
                                    <input type="hidden" name="puesto_aprueba" id="puestoAprueba">
                                    <div class="fw-bold mb-2">Aprobado:</div>
                                    <select class="form-control form-control-sm mb-3" name="usuario_aprueba" id="usuarioAprueba" style="width: 100%;" required>
                                        <option value="">Selecciona un usuario...</option>
                                    </select>
                                    <div class="small">
                                        <div><span class="fw-semibold">Area:</span> <span id="usuarioApruebaArea">S/R</span></div>
                                        <div><span class="fw-semibold">Puesto:</span> <span id="usuarioApruebaPuesto">S/R</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="submit" class="btn btn-outline-success">Actualizar Plan</button>
                        </div>
                    </form>
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
