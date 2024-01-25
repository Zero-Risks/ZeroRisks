<?php
date_default_timezone_set('America/Bogota');

// Incluir el archivo de conexión a la base de datos
include '../../../../../controllers/conexion.php';

header('Content-Type: application/json');

// Iniciar sesión (si aún no se ha iniciado)
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    echo json_encode(["success" => false, "message" => "Usuario no autenticado."]);
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
    $usuario_id = $_SESSION["usuario_id"];
}

// Verificar si se han enviado los datos necesarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prestamoId'], $_POST['montoPagado'])) {
    $prestamoId = $_POST['prestamoId'];
    $montoPagado = $_POST['montoPagado'];

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

            // Obtener el nombre, número de teléfono, CURP, dirección y zona del cliente
            $sqlCliente = "SELECT Nombre, Telefono, IdentificacionCURP, Domicilio, ZonaAsignada  FROM clientes WHERE ID = ?";
            $stmtCliente = $conexion->prepare($sqlCliente);
            $stmtCliente->bind_param("i", $clienteId);
            $stmtCliente->execute();
            $resultadoCliente = $stmtCliente->get_result();
            $filaCliente = $resultadoCliente->fetch_assoc();
            $clienteNombre = $filaCliente['Nombre'];
            $clienteTelefono = $filaCliente['Telefono'];
            $clienteCURP = $filaCliente['IdentificacionCURP'];
            $clienteDireccion = $filaCliente['Domicilio'];
            $clienteZona = $filaCliente['ZonaAsignada'];
            $stmtCliente->close();

            // Registrar el pago en la tabla 'historial_pagos' incluyendo el IDUsuario 
            $sqlRegistrarPago = "INSERT INTO historial_pagos (IDCliente, FechaPago, MontoPagado, IDPrestamo, IDUsuario, Zona) VALUES (?, CURDATE(), ?, ?, ?, ?)";
            $stmtPago = $conexion->prepare($sqlRegistrarPago);
            $stmtPago->bind_param("idiis", $clienteId, $montoPagado, $prestamoId, $usuario_id, $clienteZona);
            $stmtPago->execute();
            $stmtPago->close();

            // Descontar el monto pagado del total a pagar
            $montoRestante = $montoTotalAPagar - $montoPagado;

            // Insertar los detalles del pago en la tabla 'facturas'
            $sqlInsertarFactura = "INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda, id_prestamos) VALUES (?, ?, CURDATE(), ?, ?, ?)";
            $stmtFactura = $conexion->prepare($sqlInsertarFactura);
            $montoDeuda = $montoRestante > 0 ? $montoRestante : 0; // Si el monto restante es negativo, se establece a 0
            $stmtFactura->bind_param("idddi", $clienteId, $montoTotalAPagar, $montoPagado, $montoDeuda, $prestamoId);

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
                "clienteCURP" => $clienteCURP,
                "clienteDireccion" => $clienteDireccion,
                "montoPagado" => $montoPagado,
                "montoPendiente" => $montoRestante
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
