<?php
include_once 'conn.php';

$accion = isset($_POST['accion']) ? trim((string)$_POST['accion']) : '';

function normalizarMesesImpresion($mesesTexto) {
    if ($mesesTexto === null || trim((string)$mesesTexto) === '') {
        return 'S/R';
    }

    $mapaMeses = [
        '1' => 'Enero', '01' => 'Enero', 'ENERO' => 'Enero',
        '2' => 'Febrero', '02' => 'Febrero', 'FEBRERO' => 'Febrero',
        '3' => 'Marzo', '03' => 'Marzo', 'MARZO' => 'Marzo',
        '4' => 'Abril', '04' => 'Abril', 'ABRIL' => 'Abril',
        '5' => 'Mayo', '05' => 'Mayo', 'MAYO' => 'Mayo',
        '6' => 'Junio', '06' => 'Junio', 'JUNIO' => 'Junio',
        '7' => 'Julio', '07' => 'Julio', 'JULIO' => 'Julio',
        '8' => 'Agosto', '08' => 'Agosto', 'AGOSTO' => 'Agosto',
        '9' => 'Septiembre', '09' => 'Septiembre', 'SEPTIEMBRE' => 'Septiembre',
        '10' => 'Octubre', 'OCTUBRE' => 'Octubre',
        '11' => 'Noviembre', 'NOVIEMBRE' => 'Noviembre',
        '12' => 'Diciembre', 'DICIEMBRE' => 'Diciembre'
    ];

    $partes = array_map('trim', explode(',', (string)$mesesTexto));
    $mesesNormalizados = [];

    foreach ($partes as $parte) {
        if ($parte === '' || $parte === '0') {
            continue;
        }

        $clave = strtoupper($parte);
        if (isset($mapaMeses[$parte])) {
            $mesesNormalizados[] = $mapaMeses[$parte];
            continue;
        }

        if (isset($mapaMeses[$clave])) {
            $mesesNormalizados[] = $mapaMeses[$clave];
            continue;
        }

        if (ctype_digit($parte)) {
            $numero = (int)$parte;
            if ($numero >= 1 && $numero <= 12) {
                $mesesNormalizados[] = $mapaMeses[(string)$numero];
            }
        }
    }

    $mesesUnicos = array_values(array_unique($mesesNormalizados));
    return empty($mesesUnicos) ? 'S/R' : implode(', ', $mesesUnicos);
}

function obtenerMesesMarcados($mesesTexto) {
    if ($mesesTexto === null || trim((string)$mesesTexto) === '') {
        return [];
    }

    $mapaMeses = [
        'ENERO' => 1,
        'FEBRERO' => 2,
        'MARZO' => 3,
        'ABRIL' => 4,
        'MAYO' => 5,
        'JUNIO' => 6,
        'JULIO' => 7,
        'AGOSTO' => 8,
        'SEPTIEMBRE' => 9,
        'OCTUBRE' => 10,
        'NOVIEMBRE' => 11,
        'DICIEMBRE' => 12
    ];

    $partes = array_map('trim', explode(',', (string)$mesesTexto));
    $marcados = [];

    foreach ($partes as $parte) {
        if ($parte === '' || $parte === '0') {
            continue;
        }

        if (ctype_digit($parte)) {
            $numero = (int)$parte;
            if ($numero >= 1 && $numero <= 12) {
                $marcados[$numero] = true;
            }
            continue;
        }

        $clave = strtoupper($parte);
        if (isset($mapaMeses[$clave])) {
            $marcados[$mapaMeses[$clave]] = true;
        }
    }

    return $marcados;
}

function normalizarSeccion($valor) {
    $texto = strtolower(trim((string)$valor));
    if ($texto === '') {
        return 'otras';
    }

    if (strpos($texto, 'iv') === 0) {
        return 'iv';
    }

    if (preg_match('/^(\d+\.\d+)/', $texto, $match)) {
        return $match[1];
    }

    return $texto;
}

if ($accion === 'guardar_estatus_actividad') {
    $idPlanAnual = isset($_POST['id_plan_anual']) ? (int)$_POST['id_plan_anual'] : 0;
    $numActividad = isset($_POST['num_actividad']) ? trim((string)$_POST['num_actividad']) : '';
    $estatusActividad = isset($_POST['estatus_actividad']) ? strtoupper(trim((string)$_POST['estatus_actividad'])) : '';
    $observacion = isset($_POST['observacion']) ? trim((string)$_POST['observacion']) : '';
    $idUsrAprobo = isset($_POST['id_usr_aprobo']) ? (int)$_POST['id_usr_aprobo'] : 0;

    if ($idPlanAnual <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de plan invalido.']);
        exit;
    }

    if ($numActividad === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Numero de actividad requerido.']);
        exit;
    }

    if ($estatusActividad !== 'APROBADO' && $estatusActividad !== 'NO APROBADO') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Estatus de actividad invalido.']);
        exit;
    }

    if ($observacion === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'La observacion es obligatoria.']);
        exit;
    }

    if ($idUsrAprobo <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Usuario aprobador invalido.']);
        exit;
    }

    $observacionAprobado = null;
    $observacionNoAprobado = null;

    if ($estatusActividad === 'APROBADO') {
        $observacionAprobado = $observacion;
    } else {
        $observacionNoAprobado = $observacion;
    }

    $sqlUpdate = "UPDATE actividades
        SET estatus_actividad = ?,
            observaciones_aprobado = ?,
            observaciones_no_aprobado = ?,
            id_usr_aprobo = ?
        WHERE id_plan_anual = ?
          AND num_actividad = ?
        LIMIT 1";

    $stmtUpdate = $conn->prepare($sqlUpdate);
    if (!$stmtUpdate) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se pudo preparar la actualizacion.']);
        exit;
    }

    $stmtUpdate->bind_param(
        'sssiis',
        $estatusActividad,
        $observacionAprobado,
        $observacionNoAprobado,
        $idUsrAprobo,
        $idPlanAnual,
        $numActividad
    );

    $ok = $stmtUpdate->execute();
    $filasAfectadas = $stmtUpdate->affected_rows;
    $stmtUpdate->close();

    if (!$ok) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la actividad.']);
        exit;
    }

    if ($filasAfectadas === 0) {
        $stmtExiste = $conn->prepare("SELECT id FROM actividades WHERE id_plan_anual = ? AND num_actividad = ? LIMIT 1");
        if ($stmtExiste) {
            $stmtExiste->bind_param('is', $idPlanAnual, $numActividad);
            $stmtExiste->execute();
            $resExiste = $stmtExiste->get_result();
            $existe = $resExiste && $resExiste->num_rows > 0;
            $stmtExiste->close();

            if (!$existe) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'No se encontro la actividad para actualizar.']);
                exit;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Estatus de actividad actualizado correctamente.'
    ]);
    exit;
}

if ($accion === 'detalle_plan') {
    $idPlan = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($idPlan <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'ID de plan invalido.']);
        exit;
    }

    $stmtPlan = $conn->prepare("SELECT pa.*, 
            CONCAT_WS(' - ', up.nombre, pp.puesto) AS presenta_text,
            CONCAT_WS(' - ', ua.nombre, pa2.puesto) AS aprueba_text,
            CONCAT_WS(' - ', ur.nombre, pr.puesto) AS registra_text,
            ar.CDAREA AS area_codigo,
            ar.AREA AS area_text
        FROM planes_anuales pa
        LEFT JOIN mess_rrhh.usuarios up ON up.id_usuario = pa.id_presenta
        LEFT JOIN mess_rrhh.usuarios ua ON ua.id_usuario = pa.id_aprueba
        LEFT JOIN mess_rrhh.puesto pp ON pp.id = up.puesto
        LEFT JOIN mess_rrhh.puesto pa2 ON pa2.id = ua.puesto
        LEFT JOIN mess_rrhh.usuarios ur ON ur.noEmpleado = pa.id_registra
        LEFT JOIN mess_rrhh.puesto pr ON pr.id = ur.puesto
        LEFT JOIN mess_rrhh.areas ar ON ar.id = pa.id_area
        WHERE pa.id = ?");

    if (!$stmtPlan) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se pudo preparar la consulta del plan.']);
        exit;
    }

    $stmtPlan->bind_param('i', $idPlan);
    $stmtPlan->execute();
    $resPlan = $stmtPlan->get_result();
    $plan = $resPlan ? $resPlan->fetch_assoc() : null;
    $stmtPlan->close();

    if (!$plan) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No se encontro el plan anual solicitado.']);
        exit;
    }

    $sqlActividades = "SELECT 
            a.id,
            a.seccion,
            a.num_actividad,
            a.actividad,
            a.estatus_actividad,
            a.observaciones,
            a.periodo_registro,
            (
                SELECT GROUP_CONCAT(am.mes ORDER BY am.mes SEPARATOR ', ')
                FROM actividad_meses am
                WHERE am.id_actividad = a.id AND am.activo = 1
            ) AS meses,
            (
                SELECT u.nombre
                FROM involucrados i
                LEFT JOIN mess_rrhh.usuarios u ON u.id_usuario = i.id_usuario
                WHERE i.id_actividad = a.id AND i.tipo_involucrado = 'Responsable'
                LIMIT 1
            ) AS responsable,
            (
                SELECT GROUP_CONCAT(u.nombre ORDER BY u.nombre SEPARATOR ', ')
                FROM involucrados i
                LEFT JOIN mess_rrhh.usuarios u ON u.id_usuario = i.id_usuario
                WHERE i.id_actividad = a.id AND i.tipo_involucrado = 'Participante'
            ) AS participantes
        FROM actividades a
        WHERE a.id_plan_anual = ?
        ORDER BY 
            CASE a.seccion
                WHEN '6.2' THEN 1
                WHEN '6.3' THEN 2
                WHEN '6.4' THEN 3
                WHEN '7.2' THEN 4
                WHEN '7.6' THEN 5
                WHEN '7.7' THEN 6
                WHEN '7.11' THEN 7
                WHEN '8.8' THEN 8
                WHEN 'iv' THEN 9
                ELSE 99
            END,
            a.id";

    $actividades = [];
    $stmtAct = $conn->prepare($sqlActividades);
    if ($stmtAct) {
        $stmtAct->bind_param('i', $idPlan);
        $stmtAct->execute();
        $resAct = $stmtAct->get_result();
        if ($resAct) {
            while ($row = $resAct->fetch_assoc()) {
                $row['meses_marcados'] = obtenerMesesMarcados($row['meses'] ?? '');
                $row['meses'] = normalizarMesesImpresion($row['meses'] ?? '');
                $actividades[] = $row;
            }
        }
        $stmtAct->close();
    }

    $seccionesTitulos = [
        '6.2' => '6.2 Personal',
        '6.3' => '6.3 Infraestructura',
        '6.4' => '6.4 Ambiente de Trabajo',
        '7.2' => '7.2 Determinacion de los Requisitos',
        '7.6' => '7.6 Control de Cambios',
        '7.7' => '7.7 Control de Salidas no Conformes',
        '7.11' => '7.11 Control de Dispositivos de Seguimiento y Medicion',
        '8.8' => '8.8 Revision por la Direccion',
        'iv' => 'IV. Otras'
    ];

    $ordenSecciones = ['6.2', '6.3', '6.4', '7.2', '7.6', '7.7', '7.11', '8.8', 'iv'];
    $actividadesPorSeccion = [];

    foreach ($actividades as $actividad) {
        $seccionOriginal = trim((string)$actividad['seccion']);
        $clave = normalizarSeccion($seccionOriginal);

        if (!isset($seccionesTitulos[$clave])) {
            $seccionesTitulos[$clave] = ($seccionOriginal !== '') ? $seccionOriginal : strtoupper($clave);
        }

        if (!in_array($clave, $ordenSecciones, true)) {
            $ordenSecciones[] = $clave;
        }

        if (!isset($actividadesPorSeccion[$clave])) {
            $actividadesPorSeccion[$clave] = [];
        }

        $actividadesPorSeccion[$clave][] = $actividad;
    }

    $anioFolio = trim((string)($plan['anio'] ?? ''));
    $areaFolio = trim((string)($plan['area_codigo'] ?? ''));
    if ($areaFolio === '') {
        $areaFolio = trim((string)($plan['id_area'] ?? 'S/R'));
    }

    $folioPlan =str_pad((string)$idPlan, 4, '0', STR_PAD_LEFT); /*'SGC-' . ($anioFolio !== '' ? $anioFolio : 'S/R') . '-' . ($areaFolio !== '' ? $areaFolio : 'S/R') . '-' .*/ 

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'plan' => $plan,
            'folio' => $folioPlan,
            'orden_secciones' => $ordenSecciones,
            'secciones_titulos' => $seccionesTitulos,
            'actividades_por_seccion' => $actividadesPorSeccion
        ]
    ]);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Accion no valida.']);
