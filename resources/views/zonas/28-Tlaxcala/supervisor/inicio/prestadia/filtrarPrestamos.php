<?php
require '../../../../../../../controllers/conexion.php';
date_default_timezone_set('America/Bogota');

function obtenerCuotas($conexion, $filtro, $zona)
{
    $fechaHoy = date('Y-m-d');
    $cuotas = array();

    // Obtener el ID del usuario en sesión
    $usuario_id = $_SESSION["usuario_id"];

    // Consulta SQL base
    $sql = "SELECT p.ID, p.IDCliente, p.MontoCuota, p.FechaInicio, p.FrecuenciaPago, p.Pospuesto,
    c.Nombre AS NombreCliente, c.Domicilio AS DireccionCliente, c.Telefono AS TelefonoCliente,
    c.IdentificacionCURP, p.MontoCuota AS MontoAPagar,
    (SELECT COUNT(*) FROM historial_pagos WHERE IDPrestamo = p.ID AND FechaPago = ?) as PagadoHoy,
    (SELECT SUM(MontoPagado) FROM historial_pagos WHERE IDPrestamo = p.ID) as TotalPagado
    FROM prestamos p
    INNER JOIN clientes c ON p.IDCliente = c.ID
    WHERE p.FechaInicio <= ? AND p.Zona = ? AND p.CobradorAsignado = ?";

    // Modificar la consulta según el filtro
    switch ($filtro) {
        case 'pagado':
            $sql .= " AND (p.Estado = '' OR EXISTS (SELECT 1 FROM historial_pagos WHERE IDPrestamo = p.ID AND FechaPago = ?))";
            break;
        case 'pendiente':
            $sql .= " AND p.Estado = 'pendiente' AND p.Pospuesto = 0 AND NOT EXISTS (SELECT 1 FROM historial_pagos WHERE IDPrestamo = p.ID AND FechaPago = ?) AND p.mas_tarde = 0";
            break;
        case 'nopagado':
            $sql .= " AND p.Pospuesto = 1";
            break;
        case 'mas-tarde':
            $sql .= " AND p.mas_tarde = 1";
            break;
    }

    // Preparar la consulta SQL
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo "Error al preparar la consulta: " . $conexion->error;
        return array();
    }

    // Ajustar el bind_param según el filtro
    if ($filtro == 'pagado' || $filtro == 'pendiente') {
        $stmt->bind_param("ssssi", $fechaHoy, $fechaHoy, $zona, $usuario_id, $fechaHoy); // Cinco parámetros para 'pagado' y 'pendiente'
    } else {
        $stmt->bind_param("sssi", $fechaHoy, $fechaHoy, $zona, $usuario_id); // Cuatro parámetros para 'nopagado' y 'mas-tarde'
    }

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            // Verificar si la cuota ha sido pagada hoy (solo para filtro 'pendiente')
            if ($filtro == 'pendiente' && $fila['PagadoHoy'] > 0) continue;

            // Calcular si hoy es un día de pago según la frecuencia (solo para filtro 'pendiente')
            if ($filtro == 'pendiente' && !esDiaDePago($fila['FechaInicio'], $fila['FrecuenciaPago'], $fechaHoy)) continue;

            // Agregar la fila al array de cuotas
            $cuotas[] = $fila;
        }
    } else {
        echo "Error al ejecutar la consulta: " . $stmt->error;
    }

    $stmt->close();
    return $cuotas;
}

function esDiaDePago($fechaInicio, $frecuenciaPago, $fechaHoy)
{
    $fechaInicio = new DateTime($fechaInicio);
    $fechaHoy = new DateTime($fechaHoy);

    $intervalo = $fechaInicio->diff($fechaHoy);

    switch ($frecuenciaPago) {
        case 'diario':
            return true;
        case 'semanal':
            return $intervalo->days % 7 == 0;
        case 'quincenal':
            return $intervalo->days % 15 == 0;
        case 'mensual':
            return $fechaInicio->format('d') == $fechaHoy->format('d');
        default:
            return false;
    }
}

function contarPrestamosPorEstado($conexion, $zona)
{
    $conteos = ['pendiente' => 0, 'pagado' => 0, 'nopagado' => 0, 'mas-tarde' => 0];
    $fechaHoy = date('Y-m-d');

    // Obtener el ID del usuario en sesión
    $usuario_id = $_SESSION["usuario_id"];

    // Contar préstamos pendientes
    $sqlPendiente = "SELECT COUNT(*) AS conteo FROM prestamos p
                 WHERE p.FechaInicio <= ? AND p.Zona = ? AND p.Estado = 'pendiente' AND p.Pospuesto = 0 
                 AND NOT EXISTS (SELECT 1 FROM historial_pagos WHERE IDPrestamo = p.ID AND FechaPago = ?) AND p.mas_tarde = 0 AND p.CobradorAsignado = ?";
    $stmt = $conexion->prepare($sqlPendiente);
    $stmt->bind_param("sssi", $fechaHoy, $zona, $fechaHoy, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $conteos['pendiente'] = $fila['conteo'];
    }

    // Contar préstamos pagados
    $sqlPagado = "SELECT COUNT(*) AS conteo FROM prestamos p
                  WHERE p.Zona = ? AND EXISTS (SELECT 1 FROM historial_pagos WHERE IDPrestamo = p.ID AND FechaPago = ?) AND p.CobradorAsignado = ?  AND p.estado ='pendiente'";
    $stmt = $conexion->prepare($sqlPagado);
    $stmt->bind_param("ssi", $zona, $fechaHoy, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $conteos['pagado'] = $fila['conteo'];
    }

    // Contar préstamos no pagados
    $sqlNoPagado = "SELECT COUNT(*) AS conteo FROM prestamos p
                    WHERE p.Zona = ? AND p.Pospuesto = 1 AND p.CobradorAsignado = ? ";
    $stmt = $conexion->prepare($sqlNoPagado);
    $stmt->bind_param("si", $zona, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $conteos['nopagado'] = $fila['conteo'];
    }

    // Contar préstamos 'mas-tarde'
    $sqlMasTarde = "SELECT COUNT(*) AS conteo FROM prestamos p
                    WHERE p.Zona = ? AND p.mas_tarde = 1 AND p.CobradorAsignado = ?";
    $stmt = $conexion->prepare($sqlMasTarde);
    $stmt->bind_param("si", $zona, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $conteos['mas-tarde'] = $fila['conteo'];
    }

    $stmt->close();
    return $conteos;
}
