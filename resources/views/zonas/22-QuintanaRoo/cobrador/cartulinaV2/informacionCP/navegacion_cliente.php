<?php
function obtenerIdAnteriorSiguiente($idActual) {
    // Construye la ruta al archivo 'ruta.txt' en el mismo directorio del script PHP
    $rutaArchivo = __DIR__ . '/ruta.txt';

    // Verifica si el archivo existe
    if (!file_exists($rutaArchivo)) {
        die("Error: El archivo ruta.txt no se encuentra en el directorio esperado.");
    }

    // Lee el contenido del archivo
    $contenidoRuta = file_get_contents($rutaArchivo);
    if ($contenidoRuta === false) {
        die("Error: No se pudo leer el archivo ruta.txt.");
    }

    // Divide el contenido del archivo en un array de IDs
    $idsClientesRuta = explode(',', $contenidoRuta);

    // Encuentra la posición actual y calcula los IDs anterior y siguiente
    $posicionActual = array_search($idActual, $idsClientesRuta);
    $idAnterior = null;
    $idSiguiente = null;

    // Verifica si hay un ID anterior
    if ($posicionActual > 0) {
        $idAnterior = $idsClientesRuta[$posicionActual - 1];
    }

    // Verifica si hay un ID siguiente
    if ($posicionActual !== false && $posicionActual < count($idsClientesRuta) - 1) {
        $idSiguiente = $idsClientesRuta[$posicionActual + 1];
    }

    return ['anterior' => $idAnterior, 'siguiente' => $idSiguiente];
}
?>
