<?php

function e($texto)
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function vista($archivo, $datos = [])
{
    extract($datos);
    require __DIR__ . '/../views/' . $archivo . '.php';
}

function calcularEdad($fechaNacimiento)
{
    if (empty($fechaNacimiento)) {
        return 0;
    }
    $nacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    return $hoy->diff($nacimiento)->y;
}

function esMenorDeEdad($fechaNacimiento)
{
    return calcularEdad($fechaNacimiento) < 18;
}

function etiquetaTipo($tipo)
{
    $tipos = [
        'medio_tiempo' => 'Medio tiempo',
        'practicas' => 'Prácticas',
        'fines_semana' => 'Fines de semana',
        'remoto' => 'Remoto',
    ];
    return $tipos[$tipo] ?? ucfirst(str_replace('_', ' ', (string) $tipo));
}

function etiquetaEstado($estado)
{
    $estados = [
        'pendiente' => 'Pendiente',
        'revision' => 'En revisión',
        'entrevista' => 'Entrevista',
        'contratado' => 'Contratado',
        'rechazado' => 'Rechazado',
    ];
    return $estados[$estado] ?? ucfirst((string) $estado);
}

function subirArchivo($campoArchivo, $carpetaDestino, $prefijo = 'doc')
{
    if (!isset($_FILES[$campoArchivo]) || $_FILES[$campoArchivo]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
    $extension = strtolower(pathinfo($_FILES[$campoArchivo]['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $permitidos, true)) {
        return false;
    }

    if ($_FILES[$campoArchivo]['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $nombreArchivo = $prefijo . '_' . uniqid('', true) . '.' . $extension;
    $directorioDestino = dirname(__DIR__) . '/uploads/' . trim($carpetaDestino, '/\\') . '/';

    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0755, true);
    }

    $rutaCompleta = $directorioDestino . $nombreArchivo;

    if (move_uploaded_file($_FILES[$campoArchivo]['tmp_name'], $rutaCompleta)) {
        return trim($carpetaDestino, '/\\') . '/' . $nombreArchivo;
    }

    return false;
}

function rutaArchivo($ruta)
{
    if (empty($ruta)) {
        return '';
    }
    if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://') || str_starts_with($ruta, 'data:')) {
        return $ruta;
    }
    $archivo = ltrim(preg_replace('#^(\.\./)?uploads/#', '', (string) $ruta), '/');
    if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/public/')) {
        return '../uploads/' . $archivo;
    }
    return 'uploads/' . $archivo;
}

function eliminarArchivo($ruta)
{
    if (empty($ruta)) {
        return false;
    }
    $rutaLimpia = str_replace(['..', '\\'], ['', '/'], $ruta);
    $rutaCompleta = dirname(__DIR__) . '/uploads/' . ltrim(preg_replace('#^uploads/#', '', $rutaLimpia), '/');
    if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
        return @unlink($rutaCompleta);
    }
    return false;
}

function accesoDenegado($mensaje = 'No tienes los permisos necesarios para acceder a esta sección.', $panelRequerido = 'Panel restringido')
{
    http_response_code(403);
    vista('acceso_denegado', [
        'mensaje' => $mensaje,
        'panelRequerido' => $panelRequerido,
    ]);
    exit;
}

function estaBaneado($cuenta)
{
    if (empty($cuenta) || empty($cuenta['baneado'])) {
        return false;
    }

    if (!empty($cuenta['baneado_hasta'])) {
        $fechaBaneo = new DateTime($cuenta['baneado_hasta']);
        $ahora = new DateTime();
        if ($ahora >= $fechaBaneo) {
            return false;
        }
    }

    return true;
}

function mensajeBaneo($cuenta)
{
    $motivo = !empty($cuenta['motivo_baneo']) ? ' Motivo: "' . $cuenta['motivo_baneo'] . '".' : '';
    if (empty($cuenta['baneado_hasta'])) {
        return 'Tu cuenta ha sido suspendida permanentemente por la administración.' . $motivo;
    }

    $hasta = date('d/m/Y H:i', strtotime($cuenta['baneado_hasta']));
    return 'Tu cuenta se encuentra suspendida temporalmente hasta el ' . $hasta . '.' . $motivo;
}

function formatoFechaGrupo($fecha)
{
    if (empty($fecha)) {
        return 'Fecha no especificada';
    }
    $timestamp = is_numeric($fecha) ? (int) $fecha : strtotime((string) $fecha);
    if (!$timestamp) {
        return (string) $fecha;
    }

    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('-1 day'));
    $diaYmd = date('Y-m-d', $timestamp);

    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $dias = [
        0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
        4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
    ];

    $diaNom = $dias[(int) date('w', $timestamp)];
    $diaNum = date('j', $timestamp);
    $mesNom = $meses[(int) date('n', $timestamp)];
    $anio = date('Y', $timestamp);

    if ($diaYmd === $hoy) {
        return "Hoy · $diaNom, $diaNum de $mesNom de $anio";
    } elseif ($diaYmd === $ayer) {
        return "Ayer · $diaNom, $diaNum de $mesNom de $anio";
    } else {
        return "$diaNom, $diaNum de $mesNom de $anio";
    }
}
