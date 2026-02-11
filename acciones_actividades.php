<?php
include_once 'conn.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($accion === 'datos_formulario') {
    $usuarios = [];
    $resultUsuarios = $conn->query("SELECT noEmpleado, nombre FROM usuarios WHERE estatus = 1 ORDER BY nombre");
    if ($resultUsuarios) {
        while ($row = $resultUsuarios->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }

    $recurrencias = [];
    $resultRec = $conn->query("SELECT id, tipo_tiempo FROM recurrencia ORDER BY id");
    if ($resultRec) {
        while ($row = $resultRec->fetch_assoc()) {
            $recurrencias[] = $row;
        }
    }

    $categorias = [];
    $resultCat = $conn->query("SELECT id, nombre FROM categorias_actividades ORDER BY id");
    if ($resultCat) {
        while ($row = $resultCat->fetch_assoc()) {
            $categorias[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'usuarios' => $usuarios,
            'recurrencias' => $recurrencias,
            'categorias' => $categorias
        ]
    ]);
    exit;
}

if ($accion === 'listar') {
        $sql = "SELECT a.id, a.num_actividad, a.actividad, a.id_categoria, a.id_recurrencia, a.periodo_registro, a.observaciones,
               r.tipo_tiempo, c.nombre AS categoria
            FROM actividades a
            INNER JOIN recurrencia r ON r.id = a.id_recurrencia
            LEFT JOIN categorias_actividades c ON c.id = a.id_categoria
            ORDER BY a.id DESC";
    $result = $conn->query($sql);

    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $idActividad = (int)$row['id'];

            $meses = [];
            $stmtMeses = $conn->prepare("SELECT mes FROM actividad_meses WHERE id_actividad = ? AND activo = 1 ORDER BY mes");
            $stmtMeses->bind_param('i', $idActividad);
            $stmtMeses->execute();
            $resMeses = $stmtMeses->get_result();
            while ($m = $resMeses->fetch_assoc()) {
                $meses[] = (int)$m['mes'];
            }
            $stmtMeses->close();

            $labels = [
                1 => 'E', 2 => 'F', 3 => 'M', 4 => 'A', 5 => 'M', 6 => 'J',
                7 => 'J', 8 => 'A', 9 => 'S', 10 => 'O', 11 => 'N', 12 => 'D'
            ];
            $mesesLabels = [];
            foreach ($meses as $mes) {
                if (isset($labels[$mes])) {
                    $mesesLabels[] = $labels[$mes];
                }
            }

            $responsable = '';
            $stmtResp = $conn->prepare("SELECT u.nombre
                FROM involucrados i
                INNER JOIN usuarios u ON u.noEmpleado = i.id_usuario
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
                INNER JOIN usuarios u ON u.noEmpleado = i.id_usuario
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
                'num_actividad' => $row['num_actividad'],
                'actividad' => $row['actividad'],
                'id_categoria' => $row['id_categoria'],
                'categoria' => $row['categoria'],
                'recurrencia' => $row['tipo_tiempo'],
                'periodo_registro' => $row['periodo_registro'],
                'observaciones' => $row['observaciones'],
                'meses' => $meses,
                'meses_texto' => implode(' ', $mesesLabels),
                'responsable' => $responsable,
                'participantes' => $participantes
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

if ($accion === 'detalle') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, num_actividad, actividad, id_categoria, id_recurrencia, periodo_registro, observaciones
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

if ($accion === 'crear' || $accion === 'actualizar') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $numActividad = isset($_POST['num_actividad']) ? trim($_POST['num_actividad']) : '';
    $actividad = isset($_POST['actividad']) ? trim($_POST['actividad']) : '';
    $idCategoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;
    $idRecurrencia = isset($_POST['id_recurrencia']) ? (int)$_POST['id_recurrencia'] : 0;
    $periodoRegistro = isset($_POST['periodo_registro']) ? trim($_POST['periodo_registro']) : '';
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
    $responsable = isset($_POST['responsable']) ? trim($_POST['responsable']) : '';
    $participantes = isset($_POST['participantes']) ? $_POST['participantes'] : [];
    $meses = isset($_POST['meses']) ? $_POST['meses'] : [];

    if ($numActividad === '' || $actividad === '' || $idCategoria <= 0 || $idRecurrencia <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    if ($responsable === '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    if ($periodoRegistro === '') {
        $periodoRegistro = date('Y-m-d H:i:s');
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodoRegistro)) {
        $periodoRegistro .= ' 00:00:00';
    }

    $conn->begin_transaction();

    if ($accion === 'crear') {
        $stmt = $conn->prepare("INSERT INTO actividades (num_actividad, actividad, id_categoria, id_recurrencia, periodo_registro, observaciones)
                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiiss', $numActividad, $actividad, $idCategoria, $idRecurrencia, $periodoRegistro, $observaciones);
        $ok = $stmt->execute();
        if ($ok) {
            $id = $stmt->insert_id;
        }
        $stmt->close();

        if (!$ok) {
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
        $stmt = $conn->prepare("UPDATE actividades
                SET num_actividad = ?, actividad = ?, id_categoria = ?, id_recurrencia = ?, periodo_registro = ?, observaciones = ?
                WHERE id = ?");
        $stmt->bind_param('ssiissi', $numActividad, $actividad, $idCategoria, $idRecurrencia, $periodoRegistro, $observaciones, $id);
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
        $stmtMes = $conn->prepare("INSERT INTO actividad_meses (id_actividad, mes, activo)
                                   VALUES (?, ?, 1)");
        foreach ($meses as $m) {
            $m = (int)$m;
            if ($m < 1 || $m > 12) {
                continue;
            }
            $stmtMes->bind_param('ii', $id, $m);
            $stmtMes->execute();
        }
        $stmtMes->close();
    }

    $conn->commit();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

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

if ($accion === 'detalle_completo') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
        exit;
    }

    // Obtener datos de la actividad
    $stmt = $conn->prepare("SELECT a.*, c.nombre as categoria, r.tipo_tiempo as recurrencia
            FROM actividades a
            LEFT JOIN categorias_actividades c ON a.id_categoria = c.id
            LEFT JOIN recurrencia r ON a.id_recurrencia = r.id
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
               LEFT JOIN usuarios u ON i.id_usuario = u.noEmpleado 
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
