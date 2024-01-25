<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../../../../../../../controllers/conexion.php"); // Ajusta según la ubicación real del archivo de conexión

$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

$resultados = [];

if (!empty($busqueda)) {
    $query = "SELECT id, nombre, apellido, telefono FROM clientes WHERE CONCAT(nombre, ' ', apellido) LIKE CONCAT('%', ?, '%')";
    $stmt = $conexion->prepare($query);

    $stmt->bind_param('s', $busqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $resultados[] = $fila;
    }

    $stmt->close();
}

echo json_encode($resultados);
?>
