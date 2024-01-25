<?php
date_default_timezone_set('America/Bogota');
// Incluir el archivo de conexión a la base de datos
require_once  '../../../../../../../controllers/conexion.php';

header('Content-Type: application/json');

// Verificar si se han enviado los datos necesarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prestamoId'], $_POST['montoPagado'])) {
    $prestamoId = $_POST['prestamoId'];
    $montoPagado = $_POST['montoPagado']; // Asegúrate de validar y limpiar esta entrada

    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // Consulta SQL para obtener el monto pendiente del préstamo
        $sqlPrestamo = "SELECT MontoAPagar, IDCliente, Pospuesto FROM prestamos WHERE ID = ? AND Estado = 'pendiente'";
        $stmtPrestamo = $conexion->prepare($sqlPrestamo);
        $stmtPrestamo->bind_param("i", $prestamoId);
        $stmtPrestamo->execute();
        $resultadoPrestamo = $stmtPrestamo->get_result();
        $filaPrestamo = $resultadoPrestamo->fetch_assoc();
        $stmtPrestamo->close();

        if ($filaPrestamo) {
            $clienteId = $filaPrestamo['IDCliente'];
            $montoTotalAPagar = $filaPrestamo['MontoAPagar'];
            $esPospuesto = $filaPrestamo['Pospuesto'];

            // Obtener el nombre y el número de teléfono del cliente
            $sqlCliente = "SELECT Nombre, Telefono FROM clientes WHERE ID = ?";
            $stmtCliente = $conexion->prepare($sqlCliente);
            $stmtCliente->bind_param("i", $clienteId);
            $stmtCliente->execute();
            $resultadoCliente = $stmtCliente->get_result();
            $filaCliente = $resultadoCliente->fetch_assoc();
            $clienteNombre = $filaCliente['Nombre'];
            $clienteTelefono = $filaCliente['Telefono'];
            $stmtCliente->close();

            // Registrar el pago en la tabla 'historial_pagos'
            $sqlRegistrarPago = "INSERT INTO historial_pagos (IDCliente, FechaPago, MontoPagado, IDPrestamo) VALUES (?, CURDATE(), ?, ?)";
            $stmtPago = $conexion->prepare($sqlRegistrarPago);
            $stmtPago->bind_param("idi", $clienteId, $montoPagado, $prestamoId);
            $stmtPago->execute();
            $stmtPago->close();

            // Descontar el monto pagado del total a pagar
            $montoRestante = $montoTotalAPagar - $montoPagado;

            // Insertar los detalles del pago en la tabla 'facturas'
            $sqlInsertarFactura = "INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda) VALUES (?, ?, CURDATE(), ?, ?)";
            $stmtFactura = $conexion->prepare($sqlInsertarFactura);
            $montoDeuda = $montoRestante > 0 ? $montoRestante : 0; // Si el monto restante es negativo, se establece a 0
            $stmtFactura->bind_param("iddi", $clienteId, $montoTotalAPagar, $montoPagado, $montoDeuda);
            $stmtFactura->execute();
            $stmtFactura->close();

            // Preparar la consulta para actualizar el monto total a pagar y, si es necesario, el estado del préstamo
            if ($montoRestante <= 0) {
                // Si el monto restante es cero o menor, el préstamo se considera pagado
                $sqlActualizarPrestamo = "UPDATE prestamos SET MontoAPagar = 0, Estado = 'pagado' WHERE ID = ?";
                $stmtActualizarPrestamo = $conexion->prepare($sqlActualizarPrestamo);
                $stmtActualizarPrestamo->bind_param("i", $prestamoId);
            } else {
                // Si aún queda monto por pagar, solo actualizamos el monto
                $sqlActualizarPrestamo = "UPDATE prestamos SET MontoAPagar = ? WHERE ID = ?";
                $stmtActualizarPrestamo = $conexion->prepare($sqlActualizarPrestamo);
                $stmtActualizarPrestamo->bind_param("di", $montoRestante, $prestamoId);
            }
            $stmtActualizarPrestamo->execute();
            $stmtActualizarPrestamo->close();
            
            // Actualizar el campo 'mas_tarde' en la tabla 'prestamos'
            $sqlActualizarMasTarde = "UPDATE prestamos SET mas_tarde = 0 WHERE ID = ?";
            $stmtActualizarMasTarde = $conexion->prepare($sqlActualizarMasTarde);
            $stmtActualizarMasTarde->bind_param("i", $prestamoId);
            $stmtActualizarMasTarde->execute();
            $stmtActualizarMasTarde->close();

            // Actualizar el estado de 'Pospuesto' si el préstamo estaba pospuesto
            if ($esPospuesto) {
                $sqlActualizarPospuesto = "UPDATE prestamos SET Pospuesto = 0 WHERE ID = ?";
                $stmtActualizarPospuesto = $conexion->prepare($sqlActualizarPospuesto);
                $stmtActualizarPospuesto->bind_param("i", $prestamoId);
                $stmtActualizarPospuesto->execute();
                $stmtActualizarPospuesto->close();
            }

            // Confirmar la transacción
            $conexion->commit();
            echo json_encode([
                "success" => true,
                "message" => "Pago procesado correctamente.",
                "clienteNombre" => $clienteNombre,
                "clienteTelefono" => $clienteTelefono,
                "montoPagado" => $montoPagado
            ]);
        } else {
            throw new Exception("No se encontró el préstamo o ya está pagado.");
        }
    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(["success" => false, "message" => "Error al procesar el pago: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos POST necesarios no recibidos."]);
}

$conexion->close();
?>
