<?php
date_default_timezone_set('America/Bogota');
session_start();
require_once '../../../../../../controllers/conexion.php';

header('Content-Type: application/json'); // Indicar que la respuesta será en formato JSON

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idPrestamo'], $_POST['montoCuota'], $_POST['fechaCuota'])) {
    $prestamoId = $_POST['idPrestamo'];
    $montosCuota = $_POST['montoCuota'];
    $fechasCuota = $_POST['fechaCuota'];
    $idCliente = $_POST['idCliente'];
    $idZona = $_POST['idZona']; // Asegúrate de que este es un número entero
    $idZonaInt = intval($idZona);
    $usuarioId = $_SESSION['usuario_id'];

    $conexion->begin_transaction();

    try {
        $montoTotalPagado = array_sum($montosCuota);

        // Obtener el monto actual a pagar
        $stmtPrestamo = $conexion->prepare("SELECT MontoAPagar FROM prestamos WHERE ID = ?");
        $stmtPrestamo->bind_param("i", $prestamoId);
        $stmtPrestamo->execute();
        $resultadoPrestamo = $stmtPrestamo->get_result();
        $filaPrestamo = $resultadoPrestamo->fetch_assoc();
        $stmtPrestamo->close();

        if (!$filaPrestamo) {
            throw new Exception("Préstamo no encontrado.");
        }

        $montoRestante = $filaPrestamo['MontoAPagar'];

        foreach ($montosCuota as $index => $monto) {
            $fecha = $fechasCuota[$index];
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj) {
                throw new Exception("Fecha inválida: $fecha");
            }
            $fechaFormateada = $fechaObj->format('Y-m-d');

            // Insertar la cuota en el historial de pagos
            $stmtPago = $conexion->prepare("INSERT INTO historial_pagos (IDCliente, IDPrestamo, MontoPagado, FechaPago, IDUsuario, Zona) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtPago->bind_param("iidsii", $idCliente, $prestamoId, $monto, $fechaFormateada, $usuarioId, $idZonaInt);
            $stmtPago->execute();
            $stmtPago->close();

            // Actualizar el monto restante
            $montoRestante -= $monto;

            // Insertar la factura
            $montoDeuda = max($montoRestante, 0);
            $stmtFactura = $conexion->prepare("INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda, id_prestamos) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtFactura->bind_param("idssdi", $idCliente, $monto, $fechaFormateada, $monto, $montoDeuda, $prestamoId);
            $stmtFactura->execute();
            $stmtFactura->close();
        }

        // Actualizar el campo MontoAPagar en la tabla prestamos
        $stmtActualizarMontoPrestamo = $conexion->prepare("UPDATE prestamos SET MontoAPagar = ? WHERE ID = ?");
        $stmtActualizarMontoPrestamo->bind_param("di", $montoRestante, $prestamoId);
        $stmtActualizarMontoPrestamo->execute();
        $stmtActualizarMontoPrestamo->close();

        // Verificar si el monto restante es igual a cero y actualizar el estado del préstamo si es necesario
        if ($montoRestante <= 0) {
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
