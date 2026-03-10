<?php
include 'conn.php';

$id_usuario = $_POST['id_usuarioSGC'] ?? '';
$noEmpleado = $_POST['noEmpleadoSGC'] ?? '';
$usuario = $_POST['correoSGC'] ?? '';
$nombrePost = $_POST['nombredelusuarioSGC'] ?? '';
$sistema = $_POST['sistema'] ?? '';
$archivo = $_POST['archivo'] ?? '';
$rutaRedireccion = $_POST['rutaredireccion'] ?? '';
$idRegistro = $_POST['idRegistro'] ?? ($_POST['id'] ?? '');

if (empty($id_usuario) || empty($noEmpleado) || empty($usuario)) {
    http_response_code(400);
    echo 'Datos incompletos.';
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

$sqlOpciones = [
    "SELECT nombre, noEmpleado, TIMESTAMPDIFF(YEAR,fechaIngreso,CURDATE()) AS antiguedad, diasdisponibles, rol, departamento FROM mess_rrhh.usuarios WHERE (correo = ? OR usuario = ?) AND estatus = 1 LIMIT 1"
];

$row2 = null;
foreach ($sqlOpciones as $sql) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        continue;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $usuario, $usuario);
    mysqli_stmt_execute($stmt);
    $res2 = mysqli_stmt_get_result($stmt);

    if ($res2 && mysqli_num_rows($res2) === 1) {
        $row2 = mysqli_fetch_assoc($res2);
        mysqli_stmt_close($stmt);
        break;
    }

    mysqli_stmt_close($stmt);
}

// Si no hay tabla/catalogo de usuarios disponible, permite acceso con datos del POST.
if (!$row2) {
    $row2 = [
        'nombre' => $nombrePost !== '' ? $nombrePost : $usuario,
        'noEmpleado' => $noEmpleado,
        'antiguedad' => '0',
        'diasdisponibles' => '0',
        'rol' => $_COOKIE['rolL'] ?? '0',
        'departamento' => '0'
    ];
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SESSION['nombredelusuario'] = $row2['nombre'];
$_SESSION['noEmpleado'] = $row2['noEmpleado'];
$_SESSION['rol'] = $row2['rol'];
$_SESSION['correo'] = $usuario;
$_SESSION['id_usuario'] = $id_usuario;

$cookieOpts = [
    'expires' => time() + 86400,
    'path' => '/',
    'samesite' => 'Lax'
];

setcookie('id_usuario', (string)$id_usuario, $cookieOpts);
setcookie('antiguedad', (string)$row2['antiguedad'], $cookieOpts);
setcookie('nombredelusuario', (string)$row2['nombre'], $cookieOpts);
setcookie('noEmpleado', (string)$row2['noEmpleado'], $cookieOpts);
setcookie('diasD', (string)$row2['diasdisponibles'], $cookieOpts);
setcookie('departamento', (string)$row2['departamento'], $cookieOpts);
setcookie('rol', (string)$row2['rol'], $cookieOpts);

$sesionOpts = [
    'expires' => time() + 99999000,
    'path' => '/',
    'samesite' => 'Lax'
];
setcookie('SesionLogin', 'LoginMaster', $sesionOpts);

// Arma destino como /sistema/archivo usando valores de la notificacion.
$scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$sistemaLimpio = trim(str_replace(['..', '\\'], '', (string)$sistema), '/ ');
if ($sistemaLimpio === '' && $scriptDir !== '' && $scriptDir !== '.') {
    $sistemaLimpio = $scriptDir;
}
$archivoBase = !empty($rutaRedireccion) ? $rutaRedireccion : $archivo;
$archivoLimpio = trim(str_replace(['..', '\\'], '', (string)$archivoBase), '/ ');

if ($archivoLimpio === '') {
    // Fallback para flujo tradicional cuando no llega archivo.
    if (empty($_POST['id_usuarioSGC'])) {
        $archivoLimpio = 'index.php';
    } else {
        $archivoLimpio = 'detalles_actividades_SGC.php';
    }
}

if ($sistemaLimpio !== '') {
    if (strpos($archivoLimpio, $sistemaLimpio . '/') === 0) {
        $destino = '/' . $archivoLimpio;
    } else {
        $destino = '/' . $sistemaLimpio . '/' . $archivoLimpio;
    }
} else {
    // Si no llega sistema, redirige al archivo relativo del proyecto actual.
    $destino = $archivoLimpio;
}

if (!empty($idRegistro)) {
    $separador = (strpos($destino, '?') === false) ? '?' : '&';
    $destino .= $separador . 'id=' . urlencode($idRegistro);
}

header('Location: ' . $destino);
exit;
?>