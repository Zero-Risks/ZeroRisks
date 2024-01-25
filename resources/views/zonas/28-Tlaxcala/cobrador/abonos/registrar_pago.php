<?php
session_start();
// Incluir el archivo de conexión a la base de datos
include '../../../../../../controllers/conexion.php';

// Obtener los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clienteId = $_POST['clienteId'];
    $montoPagado = $_POST['cantidadPago'];

    // Obtener la fecha actual en formato YYYY-MM-DD
    $fechaPago = date('Y-m-d');

    // Consulta SQL para obtener el monto actual del préstamo
    $sqlMontoPendiente = "SELECT MontoAPagar FROM prestamos WHERE IDCliente = '$clienteId' AND Estado = 'pendiente'";
    $result = $conexion->query($sqlMontoPendiente);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $montoActual = $row["MontoAPagar"];
        
        // Calcular el nuevo monto pendiente después del pago
        $nuevoMonto = $montoActual - $montoPagado;

        // Consulta SQL para registrar el pago en la tabla 'historial_pagos'
        $sqlRegistrarPago = "INSERT INTO historial_pagos (IDCliente, FechaPago, MontoPagado) VALUES ('$clienteId', '$fechaPago', '$montoPagado')";

        if ($conexion->query($sqlRegistrarPago) === TRUE) {
            // Actualizar el monto pendiente en la tabla 'prestamos'
            $sqlActualizarMonto = "UPDATE prestamos SET MontoAPagar = '$nuevoMonto' WHERE IDCliente = '$clienteId' AND Estado = 'pendiente'";
            if ($conexion->query($sqlActualizarMonto) === TRUE) {
                // Crear una nueva factura
                $sqlCrearFactura = "INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda) VALUES ('$clienteId', '$montoPagado', '$fechaPago', '$montoPagado', '$nuevoMonto')";
                if ($conexion->query($sqlCrearFactura) === TRUE) {
                    echo $nuevoMonto; // Devolver el nuevo monto pendiente al JavaScript
                } else {
                    echo "Error al crear la factura: " . $conexion->error;
                }
            } else {
                echo "Error al actualizar el monto pendiente: " . $conexion->error;
            }
        } else {
            echo "Error al registrar el pago: " . $conexion->error;
        }
    } else {
        echo "No se encontró un préstamo pendiente para este cliente.";
    }
}

// Cerrar la conexión a la base de datos
$conexion->close();
?>
