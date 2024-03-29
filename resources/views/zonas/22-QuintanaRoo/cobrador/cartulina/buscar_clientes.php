<?php 
// buscar_clientes.php

header('Content-Type: application/json'); // Indica que la respuesta será en formato JSON

include '../../../../../../controllers/conexion.php'; // Ajusta la ruta a tu script de conexión

$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : ''; // Obtiene el término de búsqueda de la URL

// Leer los IDs de clientes desde orden_fijo.txt
$contenido = file_get_contents('orden_fijo.txt'); // Ajusta la ruta al archivo
$idsClientes = explode(',', $contenido);
$idsClientes = array_filter($idsClientes); // Filtra IDs vacíos

$resultados = []; // Array para almacenar los resultados

if ($busqueda != '' && strlen($busqueda) >= 3 && !empty($idsClientes)) {
    // Preparar la lista de marcadores de posición para la consulta SQL
    $placeholders = implode(',', array_fill(0, count($idsClientes), '?'));

    // Consulta SQL para buscar clientes por nombre y apellido concatenados
    $sql = "SELECT id, nombre, apellido, telefono FROM clientes WHERE CONCAT(nombre, ' ', apellido) LIKE CONCAT('%', ?, '%') AND id IN ($placeholders)";
    $stmt = $conexion->prepare($sql);

    // Agrega el término de búsqueda y los IDs al array de parámetros
    $params = array_merge([$busqueda], $idsClientes);
    
    // Necesitas especificar los tipos de datos de los parámetros de manera dinámica
    $types = 's' . str_repeat('i', count($idsClientes));
    $stmt->bind_param($types, ...$params);

    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $resultados[] = $fila; // Agrega cada fila a los resultados
    }

    $stmt->close();
}

$conexion->close(); // Cierra la conexión a la base de datos

echo json_encode($resultados); // Devuelve los resultados en formato JSON
?>
