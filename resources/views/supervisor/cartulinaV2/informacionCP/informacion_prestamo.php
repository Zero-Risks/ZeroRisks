<?php
// informacionCP/informacion_prestamo.php

function obtenerInformacionPrestamoPorCliente($conexion, $id_cliente) {
    $sql = "SELECT 
                p.ID,
                p.Monto,
                p.TasaInteres,
                p.Plazo,
                p.FechaInicio,
                p.FechaVencimiento,
                p.Estado,
                p.Zona,
                p.MontoAPagar,
                p.MontoCuota
            FROM prestamos p
            WHERE p.IDCliente = ? 
            AND p.Estado = 'pendiente' LIMIT 1"; // Asumiendo que quieres el primer préstamo del cliente

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        // Calcular el total del préstamo
        $fila['Total'] = $fila['Monto'] + ($fila['Monto'] * $fila['TasaInteres'] / 100);
        return $fila;
    } else {
        // Manejar el caso en que no se encuentre ningún préstamo para ese cliente
        return null;
    }
}

