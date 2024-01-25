<?php
date_default_timezone_set('America/Bogota');
session_start();
require_once "../../../../../../../controllers/conexion.php";

$idGasto = isset($_GET['id']) ? $_GET['id'] : null;
$datosGasto = null;
$nombreZona = '';
$recaudado = $porCobrar = $prestado = $promedioTasaInteres = 0.0;
$prestadoConInteres = 0.0;
$numeroClavos = 0;  // Inicializar variable
$totalDeudaClavos = 0.0;  // Inicializar variable

if ($idGasto) {
    // Cargar los datos del gasto existente
    $sql = "SELECT * FROM gastos WHERE ID = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $idGasto);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $datosGasto = $resultado->fetch_assoc();
            $idZona = $datosGasto['IDZona'];

            // Obtener el nombre de la zona de la tabla zonas
            $sql_nombre_zona = "SELECT Nombre FROM zonas WHERE ID = ?";
            if ($stmt_nombre_zona = $conexion->prepare($sql_nombre_zona)) {
                $stmt_nombre_zona->bind_param("i", $idZona);
                $stmt_nombre_zona->execute();
                $resultado_nombre_zona = $stmt_nombre_zona->get_result();
                if ($fila_nombre_zona = $resultado_nombre_zona->fetch_assoc()) {
                    $nombreZona = $fila_nombre_zona['Nombre'];
                }
                $stmt_nombre_zona->close();
            }
        }
        $stmt->close();
    }


    if ($nombreZona) {
        // Obtener la suma de MontoAPagar de la tabla prestamos
        $sql_prestamos = "SELECT SUM(MontoAPagar) AS TotalPorCobrar, SUM(Monto) AS TotalMonto, AVG(TasaInteres) AS PromedioTasaInteres FROM prestamos WHERE Zona = ?";
        if ($stmt_prestamos = $conexion->prepare($sql_prestamos)) {
            $stmt_prestamos->bind_param("s", $nombreZona);
            $stmt_prestamos->execute();
            $resultado_prestamos = $stmt_prestamos->get_result()->fetch_assoc();
            $porCobrar = $resultado_prestamos['TotalPorCobrar'] ?? 0.0;
            $prestado = $resultado_prestamos['TotalMonto'] ?? 0.0;
            $promedioTasaInteres = $resultado_prestamos['PromedioTasaInteres'] ?? 0.0;
            $stmt_prestamos->close();
        }

        // Calcular Prestado (Monto + TasaInteres)
        $prestadoConInteres = $prestado + ($prestado * ($promedioTasaInteres / 100));

        // Obtener la suma de MontoPagado de la tabla historial_pagos
        $sql_pagos = "SELECT SUM(MontoPagado) AS TotalRecaudado FROM historial_pagos WHERE IDZona = ?";
        if ($stmt_pagos = $conexion->prepare($sql_pagos)) {
            $stmt_pagos->bind_param("i", $idZona);
            $stmt_pagos->execute();
            $resultado_pagos = $stmt_pagos->get_result()->fetch_assoc();
            $recaudado = $resultado_pagos['TotalRecaudado'] ?? 0.0;
            $stmt_pagos->close();
        }

        $nombreZona = $datosGasto['Nombre'] ?? '';

        // Calcular clavos y total deuda
        $sqlClavos = "SELECT 
                    COUNT(DISTINCT p.ID) AS TotalClavos, 
                    SUM(CASE 
                          WHEN p.FechaVencimiento <= CURDATE() - INTERVAL 30 DAY THEN p.MontoAPagar
                          ELSE 0 
                        END) AS TotalDeuda
                  FROM 
                    prestamos p
                  LEFT JOIN 
                    historial_pagos hp ON p.ID = hp.IDPrestamo
                  WHERE 
                    p.Zona = ? AND 
                    p.Estado = 'pendiente' AND 
                    (hp.FechaPago IS NULL OR hp.FechaPago <= CURDATE() - INTERVAL 20 DAY)
                  GROUP BY 
                    p.Zona";

        if ($stmtClavos = $conexion->prepare($sqlClavos)) {
            $stmtClavos->bind_param("s", $nombreZona);  // Usar el nombre de la zona en la consulta
            if (!$stmtClavos->execute()) {
                echo "Error al ejecutar la consulta: " . $stmtClavos->error;
            }
            $resultadoClavos = $stmtClavos->get_result();

            if ($filaClavos = $resultadoClavos->fetch_assoc()) {
                $numeroClavos = $filaClavos['TotalClavos'];
                $totalDeudaClavos = $filaClavos['TotalDeuda'];
            } else {
                echo "No se encontraron datos.";
            }
            $stmtClavos->close();
        } else {
            echo "Error al preparar la consulta: " . $conexion->error;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Recoger y validar los nuevos datos del formulario
    $gasolina = $_POST['gasolina'];
    $viaticos = $_POST['viaticos'];
    $otros = $_POST['otros'];
    $avance = $_POST['avance'];
    $devuelta = $_POST['devuelta'];

    // Calcular totales
    $gastosTotales = $gasolina + $viaticos + $otros;
    $saldo = $avance - $gastosTotales;
    $diferencia = ($devuelta + $gastosTotales) - $avance;

    // Actualizar el gasto en la base de datos
    $sql = "UPDATE gastos SET Gasolina = ?, Viaticos = ?, Otros = ?, Avance = ?, Devuelta = ?, GastoTotal = ?, Saldo = ?, Diferencia = ? WHERE ID = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("ddddddddd", $gasolina, $viaticos, $otros, $avance, $devuelta, $gastosTotales, $saldo, $diferencia, $idGasto);
        $stmt->execute();
        $stmt->close();

        // Redireccionar de vuelta a la lista de gastos o alguna otra página
        header("Location: ../lista/lista_gastos.php");
        exit();
    }
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="editar_gastos.css"> <!-- Asegúrate de que la ruta al archivo CSS es correcta -->
    <title>Detalle de Gastos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="javascript:history.back()" class="btn btn-outline-primary">Volver</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span> <!-- Divisor negro -->
                            <span class="text-primary">Administrator</span> <!-- Texto azul de Bootstrap -->
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <?php if ($datosGasto) : ?>
        <div class="container">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $idGasto; ?>">
                <div class="section">
                    <div class="section-header">
                        <label>Cobro:</label>
                        <div class="section-header-info"><?php echo htmlspecialchars($datosGasto['Nombre']); ?></div>
                        <label>Fecha:</label>
                        <div class="section-header-info"><?php echo htmlspecialchars($datosGasto['Fecha']); ?>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="data-row">
                            <div class="data-group">
                                <label>Por cobrar:</label>
                                <div><?php echo '$' . number_format($porCobrar, 2); ?></div>
                            </div>
                            <div class="data-group">
                                <label>Total clavo:</label>
                                <div><?php echo $numeroClavos; ?></div>
                            </div>
                            <div class="data-group">
                                <label>Valor clavo:</label>
                                <div><?php echo '$' . number_format($totalDeudaClavos, 2); ?></div>
                            </div>
                            <div class="data-group">
                                <label>Recaudado:</label>
                                <div><?php echo '$' . number_format($recaudado, 2); ?></div>
                            </div>
                            <div class="data-group">
                                <label>Prestado:</label>
                                <div><?php echo '$' . number_format($prestadoConInteres, 2) . ' (' . number_format($promedioTasaInteres, 2) . '%)'; ?></div>
                            </div>

                        </div>
                    </div>
                    <div class="section">
                        <div class="section-header">Gastos</div>
                        <div class="section-body expenses">
                            <div class="data-group">
                                <label for="gasolina">Gasolina:</label>
                                <input type="number" name="gasolina" id="gasolina" value="<?php echo htmlspecialchars($datosGasto['Gasolina']); ?>">
                            </div>
                            <div class="data-group">
                                <label for="viaticos">Viaticos:</label>
                                <input type="number" name="viaticos" id="viaticos" value="<?php echo htmlspecialchars($datosGasto['Viaticos']); ?>">
                            </div>
                            <div class="data-group">
                                <label for="otros">Otros:</label>
                                <input type="number" name="otros" id="otros" value="<?php echo htmlspecialchars($datosGasto['Otros']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="section">
                        <div class="section-header">Adelantos y Devoluciones</div>
                        <div class="section-body expenses">
                            <div class="data-group">
                                <label for="avance">Avance:</label>
                                <input type="number" name="avance" id="avance" value="<?php echo htmlspecialchars($datosGasto['Avance']); ?>">
                            </div>
                            <div class="data-group">
                                <label for="devuelta">Devuelta:</label>
                                <input type="number" name="devuelta" id="devuelta" value="<?php echo htmlspecialchars($datosGasto['Devuelta']); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="section">
                        <div class="section-header">Totales</div>
                        <div class="section-body totals">
                            <div class="data-group">
                                <label>Gastos:</label>
                                <div id="total-gastos"><?php echo '$' . htmlspecialchars($datosGasto['GastoTotal']); ?></div>
                            </div>
                            <div class="data-group">
                                <label>Diferencia:</label>
                                <div id="diferencia"><?php echo '$' . htmlspecialchars($datosGasto['Diferencia']); ?></div>
                            </div>
                            <div class="data-group">
                                <label>Saldo:</label>
                                <div id="saldo"><?php echo '$' . htmlspecialchars($datosGasto['Saldo']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="section-footer">
                        <button type="submit" name="submit">Grabar</button>
                        <button type="button" onclick="location.href='eliminar_gasto.php?id=<?php echo $idGasto; ?>'">Eliminar</button> 
                    </div>

                    <script>
                        function calcularTotales() {
                            var gasolina = parseFloat(document.getElementById('gasolina').value) || 0;
                            var viaticos = parseFloat(document.getElementById('viaticos').value) || 0;
                            var otros = parseFloat(document.getElementById('otros').value) || 0;
                            var avance = parseFloat(document.getElementById('avance').value) || 0;
                            var devuelta = parseFloat(document.getElementById('devuelta').value) || 0;

                            var gastosTotales = gasolina + viaticos + otros;
                            var saldo = avance - gastosTotales;
                            var diferencia = (devuelta + gastosTotales) - avance;

                            document.getElementById('total-gastos').textContent = '$' + gastosTotales.toFixed(2);
                            document.getElementById('saldo').textContent = '$' + saldo.toFixed(2);
                            document.getElementById('diferencia').textContent = '$' + diferencia.toFixed(2);
                        }

                        document.getElementById('gasolina').addEventListener('input', calcularTotales);
                        document.getElementById('viaticos').addEventListener('input', calcularTotales);
                        document.getElementById('otros').addEventListener('input', calcularTotales);
                        document.getElementById('avance').addEventListener('input', calcularTotales);
                        document.getElementById('devuelta').addEventListener('input', calcularTotales);

                        // Calcular totales al cargar la página
                        calcularTotales();
                    </script>
                </div>
            </form>
        </div>
    <?php else : ?>
        <p>Error al cargar el gasto.</p>
    <?php endif; ?>

</body>

</html>