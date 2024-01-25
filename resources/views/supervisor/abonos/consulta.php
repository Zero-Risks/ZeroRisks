<?php
date_default_timezone_set('America/Bogota');

// Incluye tu archivo de conexión a la base de datos
include '../../../../controllers/conexion.php';

// Comprueba si se ha establecido el ID del cliente y es un número
if (isset($_GET["clienteId"]) && is_numeric($_GET["clienteId"])) {
    $clienteId = (int)$_GET["clienteId"];
} else {
    echo json_encode(array("error" => "ID de cliente no proporcionado o inválido."));
    exit;
}

$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.IdentificacionCURP, c.ZonaAsignada, c.Estado,
               p.ID AS IDPrestamo, p.TasaInteres, p.FechaInicio, p.FechaVencimiento, p.Zona, p.MontoAPagar, p.Cuota
        FROM clientes c
        LEFT JOIN prestamos p ON c.ID = p.IDCliente
        WHERE c.ID = $clienteId";

if ($resultado = $conexion->query($sql)) {
    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        if ($fila['Estado'] == 1) {
            echo json_encode($fila);
        } else {
            echo json_encode(array("error" => "Cliente inactivo"));
        }
    } else {
        echo json_encode(array("error" => "No se encontraron datos del cliente y préstamo."));
    }
} else {
    echo json_encode(array("error" => "Error en la consulta SQL: " . $conexion->error));
}

$conexion->close();
?>
