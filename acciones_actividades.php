<?php
include_once 'conn.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Capturar noEmpleado de las cookies
$noEmpleado = isset($_COOKIE['noEmpleadoL']) ? trim($_COOKIE['noEmpleadoL']) : '';

// Variables para datos_formulario
$usuarios = [];
$puestos = [];
$areas = [];
$recurrencias = [];
$categorias = [];

// Variables para crear/actualizar
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$idPlanAnual = isset($_POST['id_plan_anual']) ? (int)$_POST['id_plan_anual'] : 0;
$periodoRegistro = date('Y-m-d H:i:s');
$objetivosCalidad = isset($_POST['objetivos_calidad']) ? trim($_POST['objetivos_calidad']) : '';
$idArea = isset($_POST['id_area']) ? (int)$_POST['id_area'] : 0;
$puestoPresenta = isset($_POST['puesto_presenta']) ? (int)$_POST['puesto_presenta'] : 0;
$usuarioPresenta = isset($_POST['usuario_presenta']) ? (int)$_POST['usuario_presenta'] : 0;
$puestoAprueba = isset($_POST['puesto_aprueba']) ? (int)$_POST['puesto_aprueba'] : 0;
$usuarioAprueba = isset($_POST['usuario_aprueba']) ? (int)$_POST['usuario_aprueba'] : 0;
$numActividadArr = isset($_POST['num_actividad']) ? $_POST['num_actividad'] : [];
$actividadArr = isset($_POST['actividad']) ? $_POST['actividad'] : [];
$observacionesArr = isset($_POST['observaciones']) ? $_POST['observaciones'] : [];
$responsableArr = isset($_POST['responsable']) ? $_POST['responsable'] : [];
$participantesArr = isset($_POST['participantes']) ? $_POST['participantes'] : [];
$mesesArr = isset($_POST['meses']) ? $_POST['meses'] : [];

// ====================================================
// ACCIÓN: datos_formulario
// ====================================================
if ($accion === 'datos_formulario') {
    $resultUsuarios = $conn->query("SELECT id_usuario, nombre FROM mess_rrhh.usuarios WHERE estatus = 1 ORDER BY nombre");
    if ($resultUsuarios) {
        while ($row = $resultUsuarios->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }

    $resultPuestos = $conn->query("SELECT id AS id_puesto, puesto AS nombre_puesto FROM mess_rrhh.puesto ORDER BY puesto");
    if ($resultPuestos) {
        while ($row = $resultPuestos->fetch_assoc()) {
            $puestos[] = $row;
        }
    }

    $resultAreas = $conn->query("SELECT id AS id_area, AREA FROM mess_rrhh.areas ORDER BY AREA");
    if ($resultAreas) {
        while ($row = $resultAreas->fetch_assoc()) {
            $areas[] = [
                'id_area' => $row['id_area'],
                'nombre_area' => $row['AREA']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'usuarios' => $usuarios,
            'puestos' => $puestos,
            'areas' => $areas
        ]
    ]);
    exit;
}

// ====================================================
// ACCIÓN: listado
// ====================================================
if ($accion === 'listar') {
        $sql = "SELECT a.id, a.id_plan_anual, p.id AS plan_id, p.anio, a.seccion, a.num_actividad, a.actividad, a.periodo_registro, a.observaciones
            FROM actividades a
            INNER JOIN planes_anuales p ON p.id = a.id_plan_anual
            ORDER BY p.anio DESC, a.seccion, a.num_actividad";
    $result = $conn->query($sql);

    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $idActividad = (int)$row['id'];

            $meses = [];
            $stmtMeses = $conn->prepare("SELECT mes FROM actividad_meses WHERE id_actividad = ? AND activo = 1");
            $stmtMeses->bind_param('i', $idActividad);
            $stmtMeses->execute();
            $resMeses = $stmtMeses->get_result();
            
            $nombresMeses = [
                'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
            ];
            
            while ($m = $resMeses->fetch_assoc()) {
                $nombreMes = trim($m['mes']);
                if (isset($nombresMeses[$nombreMes])) {
                    $meses[] = $nombresMeses[$nombreMes];
                }
            }
            $stmtMeses->close();
            
            sort($meses);

            $responsable = '';
            $stmtResp = $conn->prepare("SELECT u.nombre
                FROM involucrados i
                INNER JOIN mess_rrhh.usuarios u ON u.id_usuario = i.id_usuario
                WHERE i.id_actividad = ? AND i.tipo_involucrado = 'Responsable'
                LIMIT 1");
            $stmtResp->bind_param('i', $idActividad);
            $stmtResp->execute();
            $resResp = $stmtResp->get_result();
            if ($resResp && $resResp->num_rows > 0) {
                $responsable = $resResp->fetch_assoc()['nombre'];
            }
            $stmtResp->close();

            $participantes = [];
            $stmtPart = $conn->prepare("SELECT u.nombre
                FROM involucrados i
                INNER JOIN mess_rrhh.usuarios u ON u.id_usuario = i.id_usuario
                WHERE i.id_actividad = ? AND i.tipo_involucrado = 'Participante'
                ORDER BY u.nombre");
            $stmtPart->bind_param('i', $idActividad);
            $stmtPart->execute();
            $resPart = $stmtPart->get_result();
            while ($p = $resPart->fetch_assoc()) {
                $participantes[] = $p['nombre'];
            }
            $stmtPart->close();

            $rows[] = [
                'id' => $idActividad,
                'plan_id' => $row['plan_id'],
                'anio' => $row['anio'],
                'seccion' => $row['seccion'],
                'num_actividad' => $row['num_actividad'],
                'actividad' => $row['actividad'],
                'periodo_registro' => $row['periodo_registro'],
                'observaciones' => $row['observaciones'],
                'meses' => $meses,
                'meses_texto' => implode(', ', $meses),
                'responsable' => $responsable,
                'participantes' => $participantes
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

// ====================================================
// ACCIÓN: detalle
// ====================================================
if ($accion === 'detalle') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, id_plan_anual, seccion, num_actividad, actividad, periodo_registro, observaciones
                            FROM actividades WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $actividad = $res->fetch_assoc();
    $stmt->close();

    if (!$actividad) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $meses = [];
    $stmtMeses = $conn->prepare("SELECT mes FROM actividad_meses WHERE id_actividad = ? AND activo = 1 ORDER BY mes");
    $stmtMeses->bind_param('i', $id);
    $stmtMeses->execute();
    $resMeses = $stmtMeses->get_result();
    while ($m = $resMeses->fetch_assoc()) {
        $meses[] = (int)$m['mes'];
    }
    $stmtMeses->close();

    $responsable = '';
    $stmtResp = $conn->prepare("SELECT id_usuario FROM involucrados WHERE id_actividad = ? AND tipo_involucrado = 'Responsable' LIMIT 1");
    $stmtResp->bind_param('i', $id);
    $stmtResp->execute();
    $resResp = $stmtResp->get_result();
    if ($resResp && $resResp->num_rows > 0) {
        $responsable = $resResp->fetch_assoc()['id_usuario'];
    }
    $stmtResp->close();

    $participantes = [];
    $stmtPart = $conn->prepare("SELECT id_usuario FROM involucrados WHERE id_actividad = ? AND tipo_involucrado = 'Participante'");
    $stmtPart->bind_param('i', $id);
    $stmtPart->execute();
    $resPart = $stmtPart->get_result();
    while ($p = $resPart->fetch_assoc()) {
        $participantes[] = $p['id_usuario'];
    }
    $stmtPart->close();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'actividad' => $actividad,
            'meses' => $meses,
            'responsable' => $responsable,
            'participantes' => $participantes
        ]
    ]);
    exit;
}

// ====================================================
// ACCIÓN: crear / actualizar
// ====================================================
if ($accion === 'crear' || $accion === 'actualizar') {
    // Capturar fecha actual siempre
    $periodoRegistro = date('Y-m-d H:i:s');

    // Obtener el año actual
    $anioActual = date('Y');
    
    $nombrePresenta = $usuarioPresenta;
    $nombreAprueba = $usuarioAprueba;

    // INSERTAR un nuevo registro en planes_anuales con los datos del formulario
    $stmtInsertPlan = $conn->prepare("INSERT INTO planes_anuales (anio, titulo_objetivos, cargo_presenta, nombre_presenta, cargo_aprueba, nombre_aprueba, id_registra, id_area)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtInsertPlan->bind_param('isiiiisi', $anioActual, $objetivosCalidad, $puestoPresenta, $nombrePresenta, $puestoAprueba, $nombreAprueba, $noEmpleado, $idArea);
    $okInsert = $stmtInsertPlan->execute();
    $idPlanAnual = $stmtInsertPlan->insert_id;
    $stmtInsertPlan->close();

    if (!$okInsert) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $conn->error
        ]);
        exit;
    }
    
    if ($idPlanAnual <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $conn->begin_transaction();

    if ($accion === 'crear') {
        $totalInserted = 0;
        $secciones = array_keys($numActividadArr);
        $seccionesNombres = [
            '6-2' => '6.2 Personal',
            '6-3' => '6.3 Infraestructura',
            '6-4' => '6.4 Ambiente de Trabajo',
            '7-2' => '7.2 Determinación de los Requisitos',
            '7-6' => '7.6 Control de Cambios',
            '7-7' => '7.7 Control de Salidas no Conformes',
            '7-11' => '7.11 Control de Dispositivos de Seguimiento y Medición',
            '8-8' => '8.8 Revisión por la Dirección',
            'iv' => 'IV. Comunicación con el Cliente'
        ];

        foreach ($secciones as $seccion) {
            // Convertir guion a punto para la numeracion y resolver nombre de seccion
            $seccionConPunto = str_replace('-', '.', $seccion);
            $seccionKey = strtolower($seccion);
            $seccionNombre = $seccionesNombres[$seccionKey] ?? $seccionesNombres[$seccion] ?? $seccionConPunto;
            
            $numArrSeccion = $numActividadArr[$seccion] ?? [];
            $actArrSeccion = $actividadArr[$seccion] ?? [];
            $obsArrSeccion = $observacionesArr[$seccion] ?? [];
            $respArrSeccion = $responsableArr[$seccion] ?? [];
            $partArrSeccion = $participantesArr[$seccion] ?? [];
            $mesArrSeccion = $mesesArr[$seccion] ?? [];

            if (!is_array($numArrSeccion)) $numArrSeccion = [$numArrSeccion];
            if (!is_array($actArrSeccion)) $actArrSeccion = [$actArrSeccion];
            if (!is_array($obsArrSeccion)) $obsArrSeccion = [$obsArrSeccion];
            if (!is_array($respArrSeccion)) $respArrSeccion = [$respArrSeccion];
            if (!is_array($partArrSeccion)) $partArrSeccion = [$partArrSeccion];
            if (!is_array($mesArrSeccion)) $mesArrSeccion = [$mesArrSeccion];

            $totalRows = count($numArrSeccion);

            for ($i = 0; $i < $totalRows; $i++) {
                $numActividad = trim($numArrSeccion[$i] ?? '');
                $actividad = trim($actArrSeccion[$i] ?? '');
                $observaciones = trim($obsArrSeccion[$i] ?? '');
                $responsable = trim($respArrSeccion[$i] ?? '');
                $participantes = is_array($partArrSeccion[$i] ?? null) ? $partArrSeccion[$i] : [];
                $meses = is_array($mesArrSeccion[$i] ?? null) ? $mesArrSeccion[$i] : [];

                $filaVacia = ($numActividad === '' && $actividad === '' && $observaciones === '' && $responsable === ''
                    && empty($participantes) && empty($meses));

                if ($filaVacia) {
                    continue;
                }

                if ($numActividad === '' || $actividad === '' || $responsable === '') {
                    $conn->rollback();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false]);
                    exit;
                }

                $stmt = $conn->prepare("INSERT INTO actividades (id_plan_anual, seccion, num_actividad, actividad, periodo_registro, observaciones)
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssss', $idPlanAnual, $seccionNombre, $numActividad, $actividad, $periodoRegistro, $observaciones);
                $ok = $stmt->execute();
                if ($ok) {
                    $idActividad = $stmt->insert_id;
                }
                $stmt->close();

                if (!$ok) {
                    $conn->rollback();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false]);
                    exit;
                }

                $stmtResp = $conn->prepare("INSERT INTO involucrados (id_actividad, tipo_involucrado, id_usuario)
                                            VALUES (?, 'Responsable', ?)");
                $stmtResp->bind_param('is', $idActividad, $responsable);
                $stmtResp->execute();
                $stmtResp->close();

                if (is_array($participantes)) {
                    $stmtPart = $conn->prepare("INSERT INTO involucrados (id_actividad, tipo_involucrado, id_usuario)
                                                VALUES (?, 'Participante', ?)");
                    foreach ($participantes as $p) {
                        $p = trim($p);
                        if ($p === '') {
                            continue;
                        }
                        $stmtPart->bind_param('is', $idActividad, $p);
                        $stmtPart->execute();
                    }
                    $stmtPart->close();
                }

                if (is_array($meses)) {
                    $nombresMeses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    
                    $stmtMes = $conn->prepare("INSERT INTO actividad_meses (id_actividad, mes, num_actividad, activo)
                                               VALUES (?, ?, ?, 1)");
                    foreach ($meses as $m) {
                        $m = (int)$m;
                        if ($m < 1 || $m > 12) {
                            continue;
                        }
                        $nombreMes = $nombresMeses[$m];
                        $stmtMes->bind_param('iss', $idActividad, $nombreMes, $numActividad);
                        $stmtMes->execute();
                    }
                    $stmtMes->close();
                }

                $totalInserted++;
            }
        }

        if ($totalInserted === 0) {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }
    } else {
        if ($id <= 0) {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }

        $primeraSeccion = array_key_first($numActividadArr);
        $primeraSeccionConPunto = str_replace('-', '.', $primeraSeccion);

        $numActividad = trim($numActividadArr[$primeraSeccion][0] ?? '');
        $actividad = trim($actividadArr[$primeraSeccion][0] ?? '');
        $observaciones = trim($observacionesArr[$primeraSeccion][0] ?? '');
        $responsable = trim($responsableArr[$primeraSeccion][0] ?? '');
        $participantes = is_array($participantesArr[$primeraSeccion][0] ?? null) ? $participantesArr[$primeraSeccion][0] : [];
        $meses = is_array($mesesArr[$primeraSeccion][0] ?? null) ? $mesesArr[$primeraSeccion][0] : [];

        if ($numActividad === '' || $actividad === '' || $responsable === '') {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }

        $stmt = $conn->prepare("UPDATE actividades
                SET seccion = ?, num_actividad = ?, actividad = ?, periodo_registro = ?, observaciones = ?
                WHERE id = ?");
        $stmt->bind_param('sssssi', $primeraSeccionConPunto, $numActividad, $actividad, $periodoRegistro, $observaciones, $id);
        $ok = $stmt->execute();
        $stmt->close();

        if (!$ok) {
            $conn->rollback();
            header('Content-Type: application/json');
            echo json_encode(['success' => false]);
            exit;
        }

        $stmtDel = $conn->prepare("DELETE FROM involucrados WHERE id_actividad = ?");
        $stmtDel->bind_param('i', $id);
        $stmtDel->execute();
        $stmtDel->close();

        $stmtDelMes = $conn->prepare("DELETE FROM actividad_meses WHERE id_actividad = ?");
        $stmtDelMes->bind_param('i', $id);
        $stmtDelMes->execute();
        $stmtDelMes->close();
    }

// ====================================================
// ACCIÓN: Actualizar involucrados y meses (solo para actualizar, no para crear)
// ====================================================
    if ($accion === 'actualizar') {
        $stmtResp = $conn->prepare("INSERT INTO involucrados (id_actividad, tipo_involucrado, id_usuario)
                                    VALUES (?, 'Responsable', ?)");
        $stmtResp->bind_param('is', $id, $responsable);
        $stmtResp->execute();
        $stmtResp->close();

        if (is_array($participantes)) {
            $stmtPart = $conn->prepare("INSERT INTO involucrados (id_actividad, tipo_involucrado, id_usuario)
                                        VALUES (?, 'Participante', ?)");
            foreach ($participantes as $p) {
                $p = trim($p);
                if ($p === '') {
                    continue;
                }
                $stmtPart->bind_param('is', $id, $p);
                $stmtPart->execute();
            }
            $stmtPart->close();
        }

        if (is_array($meses)) {
            $nombresMeses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            
            $stmtMes = $conn->prepare("INSERT INTO actividad_meses (id_actividad, mes, num_actividad, activo)
                                       VALUES (?, ?, ?, 1)");
            foreach ($meses as $m) {
                $m = (int)$m;
                if ($m < 1 || $m > 12) {
                    continue;
                }
                $nombreMes = $nombresMeses[$m];
                $stmtMes->bind_param('iss', $id, $nombreMes, $numActividad);
                $stmtMes->execute();
            }
            $stmtMes->close();
        }
    }

    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// ====================================================
// ACCIÓN: eliminar
// ====================================================
if ($accion === 'eliminar') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $conn->begin_transaction();
    $stmtDelInv = $conn->prepare("DELETE FROM involucrados WHERE id_actividad = ?");
    $stmtDelInv->bind_param('i', $id);
    $stmtDelInv->execute();
    $stmtDelInv->close();

    $stmtDelMes = $conn->prepare("DELETE FROM actividad_meses WHERE id_actividad = ?");
    $stmtDelMes->bind_param('i', $id);
    $stmtDelMes->execute();
    $stmtDelMes->close();

    $stmtDelAct = $conn->prepare("DELETE FROM actividades WHERE id = ?");
    $stmtDelAct->bind_param('i', $id);
    $stmtDelAct->execute();
    $stmtDelAct->close();

    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// ====================================================
// ACCIÓN: detalle_completo
// ====================================================
if ($accion === 'detalle_completo') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    // Obtener datos de la actividad
    $stmt = $conn->prepare("SELECT a.*, pa.anio
            FROM actividades a
            LEFT JOIN planes_anuales pa ON a.id_plan_anual = pa.id
            WHERE a.id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $actividad = $res->fetch_assoc();
    $stmt->close();

    if (!$actividad) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    // Obtener meses
    $stmtMeses = $conn->prepare("SELECT mes FROM actividad_meses WHERE id_actividad = ? ORDER BY mes");
    $stmtMeses->bind_param('i', $id);
    $stmtMeses->execute();
    $resMeses = $stmtMeses->get_result();
    $meses = [];
    $nombresMeses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    while ($m = $resMeses->fetch_assoc()) {
        $meses[] = $nombresMeses[$m['mes']];
    }
    $stmtMeses->close();

    // Obtener involucrados
    $stmtInv = $conn->prepare("SELECT i.*, u.nombre FROM involucrados i 
               LEFT JOIN mess_rrhh.usuarios u ON i.id_usuario = u.id_usuario 
               WHERE i.id_actividad = ?");
    $stmtInv->bind_param('i', $id);
    $stmtInv->execute();
    $resInv = $stmtInv->get_result();
    $responsable = '';
    $participantes = [];
    while ($inv = $resInv->fetch_assoc()) {
        if ($inv['tipo_involucrado'] == 'Responsable') {
            $responsable = $inv['nombre'];
        } else {
            $participantes[] = $inv['nombre'];
        }
    }
    $stmtInv->close();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'actividad' => $actividad,
            'meses' => $meses,
            'responsable' => $responsable,
            'participantes' => $participantes
        ]
    ]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false]);
