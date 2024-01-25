<?php
// informacionCP/informacion_todo_prestamo.php

function obtenerInformacionTodoPrestamoPorCliente($conexion, $id_cliente) {
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
            WHERE p.IDCliente = ?";  

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $prestamos = array(); // Inicializamos un array para almacenar los préstamos

    while ($fila = $resultado->fetch_assoc()) {
        // Calcular el total del préstamo
        $fila['Total'] = $fila['Monto'] + ($fila['Monto'] * $fila['TasaInteres'] / 100);
        
        // Agregar el préstamo al array de préstamos
        $prestamos[] = $fila;
    }

    return $prestamos;
}
?>
