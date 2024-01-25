<?php
date_default_timezone_set('America/Bogota');

// Incluye tu archivo de conexión a la base de datos
include("../../../../controllers/conexion.php");

// Verifica si se ha proporcionado una zona válida
if (isset($_GET['zona'])) {
    $nombreZona = $_GET['zona'];
} else {
    // Maneja el caso donde no se proporcionó una zona válida
    echo "Zona no especificada.";
    exit;  // Termina el script si no se especifica una zona válida
}

// Verifica si se ha enviado un nuevo orden a través de POST
if (isset($_POST['nuevoOrden'])) {
    $nuevoOrden = $_POST['nuevoOrden'];
    
    // Inserta el nuevo orden en la tabla de cambios_lista_pagos
    $sqlInsert = "INSERT INTO cambios_lista_pagos (IDPago, NuevoOrden) VALUES (?, ?)";
    $stmt = $conexion->prepare($sqlInsert);
    $stmt->bind_param("ii", $idPago, $nuevoOrden);

    // Reemplaza $idPago con el ID del pago correspondiente, que deberás obtener
    // de tu lógica según cómo identifiques el pago al que se refiere el nuevo orden.

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Redirige a abonos.php
        header("Location: abonos.php?zona=$nombreZona");
    } else {
        echo "Error al guardar el nuevo orden: " . $conexion->error;
    }
    $stmt->close();
} else {
    // Maneja el caso donde no se envió un nuevo orden
    echo "Nuevo orden no especificado.";
}
$conexion->close();
?>
