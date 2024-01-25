<?php
date_default_timezone_set('America/Bogota');
session_start();
include '../../../../../../controllers/conexion.php';

header('Content-Type: application/json'); // Indicar que la respuesta será en formato JSON

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prestamo_id'], $_POST['monto_cuota'], $_POST['fecha_cuota'])) {
    $prestamoId = $_POST['prestamo_id'];
    $montosCuota = $_POST['monto_cuota'];
    $fechasCuota = $_POST['fecha_cuota'];
    $usuarioId = $_SESSION['usuario_id']; // Asumiendo que el ID del usuario está almacenado en la sesión

    $conexion->begin_transaction();

    try {
        $stmtPrestamo = $conexion->prepare("SELECT MontoAPagar, IDCliente FROM prestamos WHERE ID = ?");
        $stmtPrestamo->bind_param("i", $prestamoId);
        $stmtPrestamo->execute();
        $resultadoPrestamo = $stmtPrestamo->get_result();
        $filaPrestamo = $resultadoPrestamo->fetch_assoc();
        $stmtPrestamo->close();

        if (!$filaPrestamo) {
            throw new Exception("Préstamo no encontrado.");
        }

        $clienteId = $filaPrestamo['IDCliente'];
        $montoRestante = $filaPrestamo['MontoAPagar'];

        foreach ($montosCuota as $index => $monto) {
            $fecha = $fechasCuota[$index];

            // Insertar la cuota en el historial de pagos sin verificar el monto
            $stmtPago = $conexion->prepare("INSERT INTO historial_pagos (IDCliente, IDPrestamo, MontoPagado, FechaPago, IDUsuario) VALUES (?, ?, ?, ?, ?)");
            $stmtPago->bind_param("iidsi", $clienteId, $prestamoId, $monto, $fecha, $usuarioId);
            $stmtPago->execute();
            $stmtPago->close();

            // Actualizar el monto restante y la factura
            $montoRestante -= $monto;
            $montoDeuda = max($montoRestante, 0);

            // Actualizar el campo MontoAPagar en la tabla prestamos basado en la cuota pagada
            $stmtActualizarMontoPrestamo = $conexion->prepare("UPDATE prestamos SET MontoAPagar = ? WHERE ID = ?");
            $stmtActualizarMontoPrestamo->bind_param("di", $montoRestante, $prestamoId);
            $stmtActualizarMontoPrestamo->execute();
            $stmtActualizarMontoPrestamo->close();

            // Insertar la factura sin verificar el monto
            $stmtFactura = $conexion->prepare("INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda, id_prestamos) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtFactura->bind_param("idssii", $clienteId, $monto, $fecha, $monto, $montoDeuda, $prestamoId);
            $stmtFactura->execute();
            $stmtFactura->close();
        }

        // Verificar si el monto restante es igual a cero y actualizar el estado del préstamo a "pagado" si es necesario
        if ($montoRestante == 0) {
            // Actualizar el estado del préstamo a "pagado"
            $stmtActualizarEstadoPrestamo = $conexion->prepare("UPDATE prestamos SET Estado = 'pagado' WHERE ID = ?");
            $stmtActualizarEstadoPrestamo->bind_param("i", $prestamoId);
            $stmtActualizarEstadoPrestamo->execute();
            $stmtActualizarEstadoPrestamo->close();
        }

        $conexion->commit();
        $response['success'] = true;
        $response['message'] = "Pagos procesados correctamente.";

        // Redirigir al usuario a otro formulario después del procesamiento exitoso
        header("Location: agregar_clientes.php");
        exit();
    } catch (Exception $e) {
        $conexion->rollback();
        $response['message'] = "Error al procesar los pagos: " . $e->getMessage();
    }
} else {
    $response['message'] = "Datos POST necesarios no recibidos.";
}

echo json_encode($response);
?>
