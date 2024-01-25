<?php
// Ruta del archivo ruta.txt
$rutaArchivo = 'informacionCP/ruta.txt';

// Leer el contenido del archivo en un array de líneas
$lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES);

// Contador de clientes y ID<?php
// Ruta del archivo ruta.txt
$rutaArchivo = 'informacionCP/ruta.txt';

// Leer el contenido del archivo en un array de líneas
$lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES);

// Contadores de clientes y ID
$contadorClientes = 0;
$contadorID = 0;

// Cliente actual y total
$clienteActual = 0;
$totalClientes = count($lineas);

// Recorrer cada línea del archivo
foreach ($lineas as $linea) {
    // Dividir la línea en partes, por ejemplo, si está separada por comas
    $datos = explode(',', $linea);

    // Verificar si la línea contiene datos válidos de cliente
    if (count($datos) >= 2) {
        $contadorClientes++;

        // Si el segundo elemento (posición 1) es un número, incrementar el contador de ID
        if (is_numeric(trim($datos[1]))) {
            $contadorID++;
        }

        // Actualizar el cliente actual
        $clienteActual++;

        // Mostrar el cliente actual y el total
        echo "Cliente $clienteActual de $totalClientes<br>";
    }
}

// Devolver los resultados como JSON
$resultados = [
    'totalClientes' => $contadorClientes,
    'totalID' => $contadorID,
];

echo json_encode($resultados);
?>

