<?php
function obtenerIdAnteriorSiguiente($idActual) {
    $rutaArchivo = __DIR__ . '/ruta.txt';

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo ruta.txt no se encuentra en el directorio esperado.");
    }

    $contenidoRuta = file_get_contents($rutaArchivo);
    if ($contenidoRuta === false) {
        die("Error: No se pudo leer el archivo ruta.txt.");
    }

    $idsClientesRuta = explode(',', $contenidoRuta);
    $posicionActual = array_search($idActual, $idsClientesRuta);
    $idAnterior = null;
    $idSiguiente = null;

    if ($posicionActual > 0) {
        $idAnterior = $idsClientesRuta[$posicionActual - 1];
    }

    if ($posicionActual !== false && $posicionActual < count($idsClientesRuta) - 1) {
        $idSiguiente = $idsClientesRuta[$posicionActual + 1];
    }

    return ['anterior' => $idAnterior, 'siguiente' => $idSiguiente];
}

function obtenerPosicionYTotalClientes($idActual, $conexion) {
    $rutaArchivo = __DIR__ . '/ruta.txt';

    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo ruta.txt no se encuentra en el directorio esperado.");
    }

    $contenidoRuta = file_get_contents($rutaArchivo);
    if ($contenidoRuta === false) {
        die("Error: No se pudo leer el archivo ruta.txt.");
    }

    $idsClientesRuta = explode(',', $contenidoRuta);
    $totalClientes = count($idsClientesRuta);
    $posicionActual = array_search($idActual, $idsClientesRuta) + 1;

    return ['posicion' => $posicionActual, 'total' => $totalClientes];
}

// Ejemplo de uso:
$idActual = $_GET['id'] ?? 'ID_predeterminado'; // Reemplaza 'ID_predeterminado' con un valor por defecto o lógica para obtener el ID actual

$resultadoNavegacion = obtenerIdAnteriorSiguiente($idActual);
$datosContador = obtenerPosicionYTotalClientes($idActual, $conexion);

// Puedes utilizar $resultadoNavegacion y $datosContador según sea necesario en tu aplicación
?>
