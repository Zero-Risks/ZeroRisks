<?php
session_start();
include("../../../../controllers/conexion.php");
include("informacionCP/navegacion_cliente.php");

$usuarioId = $_SESSION['usuario_id']; // Asumiendo que el ID del usuario está almacenado en la sesión
$idActual = $_POST['idCliente']; // ID del cliente actual

$idsNavegacion = obtenerIdAnteriorSiguiente($idActual, $usuarioId);
$idSiguienteCliente = $idsNavegacion['siguiente'];

function obtenerMontoAPagarActual($conexion, $idCliente)
{
    $montoAPagar = 0;
    $consulta = $conexion->prepare("SELECT MontoAPagar FROM prestamos WHERE IDCliente = ? AND Estado = 'pendiente' ORDER BY FechaInicio ASC LIMIT 1");
    $consulta->bind_param("i", $idCliente);
    $consulta->execute();
    $consulta->store_result();
    $consulta->bind_result($montoAPagar);
    if ($consulta->num_rows > 0) {
        $consulta->fetch();
        return $montoAPagar;
    } else {
        return false;
    }
}

function actualizarMontoAPagar($conexion, $idCliente, $nuevoMonto)
{
    $consulta = $conexion->prepare("UPDATE prestamos SET MontoAPagar = ?, Pospuesto = 0, mas_tarde = 0 WHERE IDCliente = ? AND Estado = 'pendiente' ORDER BY FechaInicio ASC LIMIT 1");
    $consulta->bind_param("di", $nuevoMonto, $idCliente);

    if (!$consulta->execute()) {
        echo "Error al actualizar el MontoAPagar y resetear los estados: " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

function actualizarEstadoPrestamo($conexion, $idPrestamo)
{
    $consulta = $conexion->prepare("UPDATE prestamos SET Estado = 'pagado' WHERE ID = ?");
    $consulta->bind_param("i", $idPrestamo);

    if (!$consulta->execute()) {
        echo "Error al actualizar el estado del préstamo: " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

function insertarEnFacturas($conexion, $idCliente, $montoAntesDePago, $montoPagado, $montoDeuda, $idPrestamo, $idUsuario)
{
    $fecha = date('Y-m-d'); // Fecha actual

    $consulta = $conexion->prepare("INSERT INTO facturas (cliente_id, monto, fecha, monto_pagado, monto_deuda, id_prestamos, IDUsuario) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $consulta->bind_param("idsssii", $idCliente, $montoAntesDePago, $fecha, $montoPagado, $montoDeuda, $idPrestamo, $idUsuario);

    if (!$consulta->execute()) {
        echo "Error al insertar en facturas: " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

function insertarEnHistorialPagos($conexion, $idCliente, $montoPagado, $idPrestamo, $idUsuario, $zonaCliente)
{
    $fechaPago = date('Y-m-d'); // Fecha actual
    $consulta = $conexion->prepare("INSERT INTO historial_pagos (IDCliente, FechaPago, MontoPagado, IDPrestamo, IDUsuario, Zona) VALUES (?, ?, ?, ?, ?, ?)");
    $consulta->bind_param("isdiis", $idCliente, $fechaPago, $montoPagado, $idPrestamo, $idUsuario, $zonaCliente);

    if (!$consulta->execute()) {
        echo "Error al insertar en historial_pagos: " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

function actualizarSaldoUsuario($conexion, $idUsuario, $montoCuota)
{
    // Primero, obtener el saldo actual del usuario
    $saldoActual = 0;
    $consultaSaldo = $conexion->prepare("SELECT saldo FROM usuarios WHERE ID = ?");
    $consultaSaldo->bind_param("i", $idUsuario);
    $consultaSaldo->execute();
    $consultaSaldo->bind_result($saldoActual);
    $consultaSaldo->fetch();
    $consultaSaldo->close();

    // Calcular el nuevo saldo
    $nuevoSaldo = $saldoActual + $montoCuota;

    // Ahora, actualizar el saldo en la base de datos
    $consultaActualizar = $conexion->prepare("UPDATE usuarios SET saldo = ? WHERE ID = ?");
    $consultaActualizar->bind_param("di", $nuevoSaldo, $idUsuario);

    if (!$consultaActualizar->execute()) {
        echo "Error al actualizar el saldo: " . $consultaActualizar->error;
        $consultaActualizar->close();
        return false;
    }

    $consultaActualizar->close();
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idCliente = $_POST['idCliente'] ?? null;
    $idPrestamo = $_POST['idPrestamo'] ?? null;
    $cuota = $_POST['cuota'] ?? null;
    $resta = $_POST['resta'] ?? null;
    $zonaCliente = $_POST['zonaCliente'] ?? null;

    if ($idCliente === null || $idPrestamo === null || $cuota === null || $resta === null || $zonaCliente === null) {
        header("Location: error.php");
        exit();
    }

    // Obtener MontoAPagar actual antes de la actualización
    $montoAPagarActual = obtenerMontoAPagarActual($conexion, $idCliente);

    if ($montoAPagarActual === false) {
        echo "Error al obtener el MontoAPagar actual.";
        exit();
    }

    // Procesar el pago
    $pagoExitoso = actualizarMontoAPagar($conexion, $idCliente, $resta);

    if ($pagoExitoso) {
        // Verificar si el monto a pagar ha llegado a 0 y actualizar el estado del préstamo
        if ($resta == 0) {
            $estadoActualizado = actualizarEstadoPrestamo($conexion, $idPrestamo);
            if (!$estadoActualizado) {
                echo "Error al actualizar el estado del préstamo.";
                exit();
            }
        }
 
        if ($zonaCliente === null) {
            echo "Error: Nombre de zona no disponible.";
            exit();
        }

        $actualizacionSaldoExitosa = actualizarSaldoUsuario($conexion, $_SESSION['usuario_id'], $cuota);

        if ($actualizacionSaldoExitosa) {
            $insertadoEnFacturas = insertarEnFacturas($conexion, $idCliente, $montoAPagarActual, $cuota, $resta, $idPrestamo, $_SESSION['usuario_id']);
            $insertadoEnHistorial = insertarEnHistorialPagos($conexion, $idCliente, $cuota, $idPrestamo, $_SESSION['usuario_id'], $zonaCliente);

            $idsNavegacion = obtenerIdAnteriorSiguiente($idActual, $usuarioId);
            $idSiguienteCliente = $idsNavegacion['siguiente'];

            // Después del procesamiento del pago:
            if ($idSiguienteCliente !== null) {
                // Redirige al siguiente cliente
                header("Location: abonos.php?id=" . $idSiguienteCliente);
                exit();
            } else {
                // No hay siguiente cliente, redirigir a la página de confirmación
                header("Location: informacionCP/todo.php");
                exit();
            }
        } else {
            // Error en el pago, redirigir a la página de error
            header("Location: error.php");
            exit();
        }
    }
}
