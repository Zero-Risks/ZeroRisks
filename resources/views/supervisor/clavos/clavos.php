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

<header class="bg-white shadow-sm mb-4">

<div class="container d-flex justify-content-between align-items-center py-2">
   <div class="container mt-3">
<a href="../inicio/inicio.php" class="btn btn-secondary">Volver al Inicio</a>
</div>
    <div class="card" style="max-width: 180px; max-height: 75px;"> <!-- Ajusta el ancho máximo y el alto máximo de la tarjeta según tus preferencias -->
        <div class="card-body">
            <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                <p class="card-text" style="font-size: 15px;"> <!-- Ajusta el tamaño de fuente según tus preferencias -->
                    <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                        <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                    </span>
                    <span style="color: black;"> | </span> <!-- Divisor negro -->
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <div class="container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4" style="align-items: center;">
            <h1 style="font-size: 2em;">Clavos</h1>
            <span id="totalClavos" style="font-size: 1.5em; color: red;">
                Total: <strong><?php echo count($todosLosClavos); ?></strong>
            </span>
        </div>


        <!-- Filtro de Zona -->
        <select id="filtro-zona" class="form-control mb-4">
            <option value="">Todo</option>
            <?php foreach ($zonas as $zona) { ?>
                <option value="<?php echo htmlspecialchars($zona); ?>"><?php echo htmlspecialchars($zona); ?></option>
            <?php } ?>
        </select>

        <!-- Tabla de Clavos -->
        <div style="overflow-x: auto;">
            <table class="table table-bordered table-striped">
                <thead>
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
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.getElementById('filtro-zona').addEventListener('change', function() {
            var zonaSeleccionada = this.value;
            var totalVisible = 0;
            document.querySelectorAll('.fila-zona').forEach(function(fila) {
                if (zonaSeleccionada === '' || fila.dataset.zona === zonaSeleccionada) {
                    fila.style.display = '';
                    totalVisible++;
                } else {
                    fila.style.display = 'none';
                }
            });
            document.getElementById('totalClavos').innerHTML = 'Total: <strong style="color: red;">' + totalVisible + '</strong>';
        });
    </script>
</body>

</html>