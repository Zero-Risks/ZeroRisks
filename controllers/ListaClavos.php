<?php
date_default_timezone_set('America/Bogota');
session_start();
require_once "../../../../controllers/conexion.php";

$zonas = ['Chihuahua', 'Puebla', 'Quintana Roo', 'Tlaxcala'];
$todosLosClavos = [];

foreach ($zonas as $zona) {
    $sqlClavos = "SELECT 
                    p.ID, 
                    p.IDCliente, 
                    p.MontoAPagar, 
                    p.FechaVencimiento, 
                    MAX(hp.FechaPago) AS UltimaFechaPago 
                  FROM 
                    prestamos p 
                  LEFT JOIN 
                    historial_pagos hp ON p.ID = hp.IDPrestamo 
                  WHERE 
                    p.Zona = ? AND 
                    p.Estado = 'pendiente' AND 
                    (hp.FechaPago IS NULL OR hp.FechaPago <= CURDATE() - INTERVAL 20 DAY)
                  GROUP BY 
                    p.ID, p.IDCliente, p.MontoAPagar, p.FechaVencimiento";

    if ($stmtClavos = $conexion->prepare($sqlClavos)) {
        $stmtClavos->bind_param("s", $zona);
        $stmtClavos->execute();
        $resultadoClavos = $stmtClavos->get_result();
        while ($fila = $resultadoClavos->fetch_assoc()) {
            $fila['Zona'] = $zona; // Añadir la zona a cada fila
            $todosLosClavos[] = $fila;
        }
        $stmtClavos->close();
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clavos con Filtro</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Lista de Clavos con Filtro</h1>

        <!-- Filtro de Zona -->
        <select id="filtro-zona" class="form-control mb-4">
            <option value="">Todo</option>
            <?php foreach ($zonas as $zona) { ?>
                <option value="<?php echo htmlspecialchars($zona); ?>"><?php echo htmlspecialchars($zona); ?></option>
            <?php } ?>
        </select>

        <!-- Tabla de Clavos -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID Préstamo</th>
                    <th>ID Cliente</th>
                    <th>Monto a Pagar</th>
                    <th>Fecha Vencimiento</th>
                    <th>Última Fecha de Pago</th>
                    <th>Zona</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todosLosClavos as $clavo) { ?>
                    <tr class="fila-zona" data-zona="<?php echo htmlspecialchars($clavo['Zona']); ?>">
                        <td><?php echo htmlspecialchars($clavo['ID'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($clavo['IDCliente'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($clavo['MontoAPagar'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($clavo['FechaVencimiento'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($clavo['UltimaFechaPago'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($clavo['Zona'] ?? ''); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('filtro-zona').addEventListener('change', function() {
            var zonaSeleccionada = this.value;
            document.querySelectorAll('.fila-zona').forEach(function(fila) {
                if (zonaSeleccionada === '' || fila.dataset.zona === zonaSeleccionada) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>