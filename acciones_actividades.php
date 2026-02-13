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
    $resultUsuarios = $conn->query("SELECT id_usuario, nombre, puesto, departamento FROM mess_rrhh.usuarios WHERE estatus = 1 ORDER BY nombre");
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

    $resultAreas = $conn->query("SELECT id AS id_area, departamento AS nombre_area FROM mess_rrhh.departamento ORDER BY departamento");
    if ($resultAreas) {
        while ($row = $resultAreas->fetch_assoc()) {
            $areas[] = [
                'id_area' => $row['id_area'],
                'nombre_area' => $row['nombre_area']
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
if ($accion === 'listar_planes') {
    $sql = "SELECT pa.*, 
            CONCAT_WS(' - ', up.nombre, pp.puesto) AS presenta_text,
            CONCAT_WS(' - ', ua.nombre, pa2.puesto) AS aprueba_text,
            ur.nombre AS registra_text,
            dpto.departamento AS area_text
        FROM planes_anuales pa
        LEFT JOIN mess_rrhh.usuarios up ON up.id_usuario = pa.id_presenta
        LEFT JOIN mess_rrhh.usuarios ua ON ua.id_usuario = pa.id_aprueba
        LEFT JOIN mess_rrhh.puesto pp ON pp.id = up.puesto
        LEFT JOIN mess_rrhh.puesto pa2 ON pa2.id = ua.puesto
        LEFT JOIN mess_rrhh.usuarios ur ON ur.noEmpleado = pa.id_registra
        LEFT JOIN mess_rrhh.departamento dpto ON dpto.id = pa.id_area
        ORDER BY pa.fecha_creacion DESC, pa.id DESC";
    $result = $conn->query($sql);

    if ($result === false) {
        $sqlFallback = "SELECT pa.*, 
                CONCAT_WS(' - ', up.nombre, pp.puesto) AS presenta_text,
                CONCAT_WS(' - ', ua.nombre, pa2.puesto) AS aprueba_text,
                ur.nombre AS registra_text
            FROM planes_anuales pa
            LEFT JOIN mess_rrhh.usuarios up ON up.id_usuario = pa.id_presenta
            LEFT JOIN mess_rrhh.usuarios ua ON ua.id_usuario = pa.id_aprueba
            LEFT JOIN mess_rrhh.puesto pp ON pp.id = up.puesto
            LEFT JOIN mess_rrhh.puesto pa2 ON pa2.id = ua.puesto
            LEFT JOIN mess_rrhh.usuarios ur ON ur.noEmpleado = pa.id_registra
            ORDER BY pa.fecha_creacion DESC, pa.id DESC";
        $result = $conn->query($sqlFallback);
    }

    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    if ($result === false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $conn->error]);
        exit;
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
// ACCIÓN: detalle_plan
// ====================================================
if ($accion === 'detalle_plan') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    // Obtener datos del plan
    $stmt = $conn->prepare("SELECT * FROM planes_anuales WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $plan = $res->fetch_assoc();
    $stmt->close();

    if (!$plan) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    // Obtener todas las actividades del plan agrupadas por sección
    $stmtActividades = $conn->prepare("SELECT * FROM actividades WHERE id_plan_anual = ? ORDER BY seccion, id");
    $stmtActividades->bind_param('i', $id);
    $stmtActividades->execute();
    $resActividades = $stmtActividades->get_result();
    $actividades = [];
    while ($act = $resActividades->fetch_assoc()) {
        $idActividad = $act['id'];
        
        // Obtener meses de cada actividad
        $stmtMeses = $conn->prepare("SELECT mes FROM actividad_meses WHERE id_actividad = ? AND activo = 1 ORDER BY mes");
        $stmtMeses->bind_param('i', $idActividad);
        $stmtMeses->execute();
        $resMeses = $stmtMeses->get_result();
        $meses = [];
        $nombresMeses = ['Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6, 
                        'Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12];
        while ($m = $resMeses->fetch_assoc()) {
            $meses[] = isset($nombresMeses[$m['mes']]) ? $nombresMeses[$m['mes']] : $m['mes'];
        }
        $stmtMeses->close();
        
        // Obtener responsable
        $stmtResp = $conn->prepare("SELECT id_usuario FROM involucrados WHERE id_actividad = ? AND tipo_involucrado = 'Responsable' LIMIT 1");
        $stmtResp->bind_param('i', $idActividad);
        $stmtResp->execute();
        $resResp = $stmtResp->get_result();
        $responsable = '';
        if ($resResp && $resResp->num_rows > 0) {
            $responsable = $resResp->fetch_assoc()['id_usuario'];
        }
        $stmtResp->close();
        
        // Obtener participantes
        $stmtPart = $conn->prepare("SELECT id_usuario FROM involucrados WHERE id_actividad = ? AND tipo_involucrado = 'Participante'");
        $stmtPart->bind_param('i', $idActividad);
        $stmtPart->execute();
        $resPart = $stmtPart->get_result();
        $participantes = [];
        while ($p = $resPart->fetch_assoc()) {
            $participantes[] = $p['id_usuario'];
        }
        $stmtPart->close();
        
        $act['meses'] = $meses;
        $act['responsable'] = $responsable;
        $act['participantes'] = $participantes;
        $actividades[] = $act;
    }
    $stmtActividades->close();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'plan' => $plan,
            'actividades' => $actividades
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
    
    // Verificar si estamos editando un plan existente
    $planEditarId = isset($_POST['plan_editar_id']) ? (int)$_POST['plan_editar_id'] : 0;
    
    if ($planEditarId > 0) {
        // ACTUALIZAR plan existente
        $stmtUpdatePlan = $conn->prepare("UPDATE planes_anuales 
            SET titulo_objetivos = ?, id_presenta = ?, id_aprueba = ?, id_area = ?, fecha_actualizacion = NOW()
            WHERE id = ?");
        $stmtUpdatePlan->bind_param('siiii', $objetivosCalidad, $usuarioPresenta, $usuarioAprueba, $idArea, $planEditarId);
        $okUpdate = $stmtUpdatePlan->execute();
        $stmtUpdatePlan->close();
        
        if (!$okUpdate) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $conn->error
            ]);
            exit;
        }
        
        $idPlanAnual = $planEditarId;
        
        // Obtener actividades existentes del plan para actualizar
        $actividadesExistentes = [];
        $stmtGetAct = $conn->prepare("SELECT id, seccion FROM actividades WHERE id_plan_anual = ? ORDER BY seccion, id");
        $stmtGetAct->bind_param('i', $idPlanAnual);
        $stmtGetAct->execute();
        $resGetAct = $stmtGetAct->get_result();
        while ($rowAct = $resGetAct->fetch_assoc()) {
            $actividadesExistentes[$rowAct['seccion']][] = $rowAct['id'];
        }
        $stmtGetAct->close();
        
    } else {
        // INSERTAR un nuevo registro en planes_anuales con los datos del formulario
        $stmtInsertPlan = $conn->prepare("INSERT INTO planes_anuales (anio, titulo_objetivos, id_presenta, id_aprueba, id_registra, id_area)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmtInsertPlan->bind_param('isiiii', $anioActual, $objetivosCalidad, $usuarioPresenta, $usuarioAprueba, $noEmpleado, $idArea);
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
        
        $actividadesExistentes = [];
    }

    $conn->begin_transaction();

    if ($accion === 'crear' || $planEditarId > 0) {
        $totalInserted = 0;
        $secciones = array_keys($numActividadArr);
        $seccionesNombres = [
            '6.2' => '6.2 Personal',
            '6.3' => '6.3 Infraestructura',
            '6.4' => '6.4 Ambiente de Trabajo',
            '7.2' => '7.2 Determinación de los Requisitos',
            '7.6' => '7.6 Control de Cambios',
            '7.7' => '7.7 Control de Salidas no Conformes',
            '7.11' => '7.11 Control de Dispositivos de Seguimiento y Medición',
            '8.8' => '8.8 Revisión por la Dirección',
            'iv' => 'IV. Otras'
        ];

        foreach ($secciones as $seccionKeyRaw) {
            // PHP convierte los puntos en guiones bajos dentro de los nombres
            $seccionKey = strtolower($seccionKeyRaw);
            $seccionNormalizada = str_replace('_', '.', $seccionKey);
            $seccionConPunto = str_replace('-', '.', $seccionNormalizada);
            $seccionNombre = $seccionesNombres[$seccionNormalizada] ?? $seccionesNombres[$seccionConPunto] ?? $seccionConPunto;
            
            $numArrSeccion = $numActividadArr[$seccionKeyRaw] ?? [];
            $actArrSeccion = $actividadArr[$seccionKeyRaw] ?? [];
            $obsArrSeccion = $observacionesArr[$seccionKeyRaw] ?? [];
            $respArrSeccion = $responsableArr[$seccionKeyRaw] ?? [];
            $partArrSeccion = $participantesArr[$seccionKeyRaw] ?? [];
            $mesArrSeccion = $mesesArr[$seccionKeyRaw] ?? [];

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

                $filaVacia = ($actividad === '' && $responsable === ''
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

                // Verificar si estamos actualizando actividades existentes
                $idActividad = null;
                if ($planEditarId > 0 && isset($actividadesExistentes[$seccionNombre][$i])) {
                    // ACTUALIZAR actividad existente
                    $idActividad = $actividadesExistentes[$seccionNombre][$i];
                    
                    $stmt = $conn->prepare("UPDATE actividades 
                        SET num_actividad = ?, actividad = ?, periodo_registro = ?, observaciones = ?
                        WHERE id = ?");
                    $stmt->bind_param('ssssi', $numActividad, $actividad, $periodoRegistro, $observaciones, $idActividad);
                    $ok = $stmt->execute();
                    $stmt->close();
                    
                    if (!$ok) {
                        $conn->rollback();
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false]);
                        exit;
                    }
                    
                    // Eliminar involucrados y meses antiguos para actualizar
                    $stmtDelInv = $conn->prepare("DELETE FROM involucrados WHERE id_actividad = ?");
                    $stmtDelInv->bind_param('i', $idActividad);
                    $stmtDelInv->execute();
                    $stmtDelInv->close();
                    
                    $stmtDelMes = $conn->prepare("DELETE FROM actividad_meses WHERE id_actividad = ?");
                    $stmtDelMes->bind_param('i', $idActividad);
                    $stmtDelMes->execute();
                    $stmtDelMes->close();
                } else {
                    // INSERTAR nueva actividad
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

        $primeraSeccionKey = array_key_first($numActividadArr);
        $primeraSeccion = strtolower($primeraSeccionKey);
        $primeraSeccion = str_replace('_', '.', $primeraSeccion);
        $primeraSeccionConPunto = str_replace('-', '.', $primeraSeccion);

        $numActividad = trim($numActividadArr[$primeraSeccionKey][0] ?? '');
        $actividad = trim($actividadArr[$primeraSeccionKey][0] ?? '');
        $observaciones = trim($observacionesArr[$primeraSeccionKey][0] ?? '');
        $responsable = trim($responsableArr[$primeraSeccionKey][0] ?? '');
        $participantes = is_array($participantesArr[$primeraSeccionKey][0] ?? null) ? $participantesArr[$primeraSeccionKey][0] : [];
        $meses = is_array($mesesArr[$primeraSeccionKey][0] ?? null) ? $mesesArr[$primeraSeccionKey][0] : [];

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
