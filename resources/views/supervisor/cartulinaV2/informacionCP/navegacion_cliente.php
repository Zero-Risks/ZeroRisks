<?php
function obtenerRutaUsuario($usuarioId) {
    $nombreArchivo = "ruta_" . $usuarioId . ".txt";
    $rutaArchivo = __DIR__ . '/' . $nombreArchivo;

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo de ruta para el usuario $usuarioId no se encuentra.");
    }

    $contenidoRuta = file_get_contents($rutaArchivo);
    if ($contenidoRuta === false) {
        die("Error: No se pudo leer el archivo de ruta para el usuario $usuarioId.");
    }

    return explode(',', $contenidoRuta);
}

function obtenerIdAnteriorSiguiente($idActual, $usuarioId) {
    $idsClientesRuta = obtenerRutaUsuario($usuarioId);
    $posicionActual = array_search($idActual, $idsClientesRuta);

    $idAnterior = $posicionActual > 0 ? $idsClientesRuta[$posicionActual - 1] : null;
    $idSiguiente = $posicionActual !== false && $posicionActual < count($idsClientesRuta) - 1 ? $idsClientesRuta[$posicionActual + 1] : null;

    return ['anterior' => $idAnterior, 'siguiente' => $idSiguiente];
}

function obtenerPosicionYTotalClientes($idActual, $usuarioId) {
    $idsClientesRuta = obtenerRutaUsuario($usuarioId);
    $totalClientes = count($idsClientesRuta);
    $posicionActual = array_search($idActual, $idsClientesRuta) + 1;

    return ['posicion' => $posicionActual, 'total' => $totalClientes];
}

// Ejemplo de uso:
$usuarioId = $_SESSION['usuario_id'] ?? 'ID_usuario_predeterminado'; 
$idActual = $_GET['id'] ?? 'ID_cliente_predeterminado'; 

$resultadoNavegacion = obtenerIdAnteriorSiguiente($idActual, $usuarioId);
$datosContador = obtenerPosicionYTotalClientes($idActual, $usuarioId);

// Usar $resultadoNavegacion y $datosContador según sea necesario

?>
