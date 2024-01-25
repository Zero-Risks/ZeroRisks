<?php
// Incluye tu archivo de conexión a la base de datos
include '../../../../../../controllers/conexion.php';

// Obtén el ID del cliente desde la solicitud GET
$clienteId = $_GET["clienteId"];


$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.IdentificacionCURP, c.ZonaAsignada, c.Estado,
               p.ID AS IDPrestamo, p.TasaInteres, p.FechaInicio, p.FechaVencimiento, p.Zona, p.MontoAPagar, p.Cuota
        FROM clientes c
        LEFT JOIN prestamos p ON c.ID = p.IDCliente
        WHERE c.ID = $clienteId";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    if ($fila['Estado'] == 1) { // Verifica si el cliente está activo
        echo json_encode($fila);
    } else {
        echo json_encode(array("error" => "Cliente inactivo"));
    }
} else {
    echo json_encode(array("error" => "No se encontraron datos del cliente y préstamo."));
}

$conexion->close();
?>