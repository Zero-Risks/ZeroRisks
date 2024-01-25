<!-- Archivo tabla_pagos.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
</body>
</html>

<?php
$fechaHoy = date("Y-m-d"); // Definir la fecha de hoy

// Consulta SQL para obtener el ID del primer préstamo pendiente del cliente y su plazo
$sqlPrimerPrestamo = "SELECT p.ID AS primer_prestamo_id, p.Plazo
                      FROM prestamos p
                      WHERE p.IDCliente = ? AND p.Estado = 'pendiente'
                      ORDER BY p.ID ASC
                      LIMIT 1";

$stmtPrimerPrestamo = $conexion->prepare($sqlPrimerPrestamo);
$stmtPrimerPrestamo->bind_param("i", $id_cliente);
$stmtPrimerPrestamo->execute();
$resultadoPrimerPrestamo = $stmtPrimerPrestamo->get_result();

if ($filaPrimerPrestamo = $resultadoPrimerPrestamo->fetch_assoc()) {
    $primerPrestamoID = $filaPrimerPrestamo['primer_prestamo_id'];
    $plazo = $filaPrimerPrestamo['Plazo'];

    // Lógica para mostrar los pagos
    if (isset($_GET['show_all']) && $_GET['show_all'] === 'true') {
        // Consulta SQL para mostrar todos los pagos
        $sql = "SELECT f.fecha, f.monto_pagado, f.monto_deuda
                FROM facturas f
                JOIN prestamos p ON f.id_prestamos = p.ID
                WHERE f.cliente_id = ? AND p.ID = ?
                ORDER BY f.fecha";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_cliente, $primerPrestamoID);
    } else {
        // Consulta SQL para mostrar solo el último pago
        $sql = "SELECT f.fecha, f.monto_pagado, f.monto_deuda
                FROM facturas f
                JOIN prestamos p ON f.id_prestamos = p.ID
                WHERE f.cliente_id = ? AND p.ID = ?
                ORDER BY f.fecha DESC
                LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_cliente, $primerPrestamoID);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    $num_rows = $resultado->num_rows;

    if ($num_rows > 0) {
        echo "<div class='container mt-4'>"; 
        echo "<div class='table-responsive'>";
        echo "<table class='table'>"; 
        echo "<thead>";
        echo "<tr><th>Cuota</th><th>Fecha</th><th>Abono</th><th>Resta</th></tr>";
        echo "</thead>";

        echo "<tbody>";
        $contadorCuota = 1; // Inicializamos el contador de cuotas

        if (isset($_GET['show_all']) && $_GET['show_all'] === 'true') {
            $totalCuotas = $plazo; // Total de cuotas es igual al plazo del préstamo
        } else {
            $totalCuotas = max($num_rows, 1); // Total de cuotas es igual al número de pagos mostrados o al menos 1
        }

        while ($fila = $resultado->fetch_assoc()) {
            $claseFecha = ($fila['fecha'] == $fechaHoy) ? "class='fecha-hoy'" : "";
            echo "<tr>";
            // Mostrar el contador de cuotas actual y el plazo total
            echo "<td>" . $contadorCuota . " / " . $plazo . "</td>";
            echo "<td " . $claseFecha . ">" . htmlspecialchars($fila['fecha']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['monto_pagado']) . "</td>";
            echo "<td>" . htmlspecialchars($fila['monto_deuda']) . "</td>";
            echo "</tr>";

            $contadorCuota++; // Incrementamos el contador de cuotas
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<p class='no-pagos-message'>No se encontraron pagos para este cliente en su primer préstamo pendiente.</p>";
    }

    $stmt->close();
} else {
    echo "<p class='no-pagos-message'>El cliente no tiene préstamos pendientes.</p>";
}

$stmtPrimerPrestamo->close();
?>
