<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

require_once '../../../../../../controllers/conexion.php';

$idPrestamo = $_GET['idPrestamo'] ?? 0;
$numCuotasEspecificadas = $_GET['numCuotas'] ?? null;

$consulta = "SELECT p.*, c.Nombre, c.Apellido, p.IDCliente, z.ID as IDZona
             FROM prestamos p
             INNER JOIN clientes c ON p.IDCliente = c.ID
             LEFT JOIN zonas z ON p.Zona = z.Nombre
             WHERE p.ID = ?";

$stmt = $conexion->prepare($consulta);
$stmt->bind_param("i", $idPrestamo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $prestamo = $resultado->fetch_assoc();
    $idCliente = $prestamo['IDCliente'];
    $idZona = $prestamo['IDZona']; // ID de la zona
    $montoCuota = $prestamo['MontoCuota'];
    $fechaInicio = new DateTime($prestamo['FechaInicio']);
    $fechaHoy = new DateTime();
    $datosCuotas = generarCuotasDiarias($fechaInicio, $montoCuota, $numCuotasEspecificadas, $fechaHoy);
    $cuotas = $datosCuotas['cuotas'];
    $montoTotal = $datosCuotas['montoTotal']; // Monto total de las cuotas
?>

    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Registro de Pagos</title>

        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../desatrasar/css/desatrasar.css">

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
                                <span class="text-primary">Supervisor</span> <!-- Texto azul de Bootstrap -->
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="container mt-5">
            <h1 class="text-center mb-4">Registro de Pagos Retroactivos</h1>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Cliente: <?php echo $prestamo['Nombre'] . ' ' . $prestamo['Apellido']; ?></h2>
                    <p class="card-text">Fecha de Inicio del Préstamo: <?php echo $fechaInicio->format('Y-m-d'); ?></p>
                    <p class="card-text">Monto a Pagar del Préstamo: <?php echo $prestamo['MontoAPagar']; ?></p>

                </div>
            </div>

            <form method="GET" id="formCuotas" class="form-inline justify-content-center my-4">
                <input type="hidden" name="idPrestamo" value="<?php echo $idPrestamo; ?>">
                <div class="form-group mx-sm-3 mb-2">
                    <label for="numCuotas" class="sr-only">Número de Cuotas</label>
                    <input type="number" id="numCuotas" name="numCuotas" class="form-control" min="1" value="<?php echo $numCuotasEspecificadas ?? count($cuotas); ?>" placeholder="Número de Cuotas">
                </div>
                <button type="submit" class="btn btn-info mb-2">Actualizar</button>
            </form>

            <form method="POST" action="procesar_pago.php">
                <input type="hidden" name="idPrestamo" value="<?php echo $idPrestamo; ?>">
                <input type="hidden" name="idCliente" value="<?php echo $idCliente; ?>">
                <input type="hidden" name="idZona" value="<?php echo $idZona; ?>">
                <?php foreach ($cuotas as $indice => $cuota) { ?>
                    <div class="cuota d-flex justify-content-between align-items-center">
                        <div class="form-group mb-0">
                            <label class="cuota-label">Cuota <?php echo $indice + 1; ?>:</label>
                            <input type="text" name="montoCuota[]" class="form-control" value="<?php echo $cuota['monto']; ?>" required>
                        </div>
                        <div class="form-group mb-0">
                            <label class="cuota-label">Fecha Cuota:</label>
                            <input type="date" name="fechaCuota[]" class="form-control" value="<?php echo $cuota['fecha']; ?>" required>
                        </div>
                    </div>
                <?php } ?>

                <div class="alert alert-info text-center mt-4">
                    <strong>Monto Total a Pagar: <?php echo $montoTotal; ?></strong>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-success mt-3"><i class="fas fa-dollar-sign"></i> Registrar Pago</button>
                </div>
            </form>
            <br>

        </div>

    <?php
} else {
    echo "<div class='alert alert-danger text-center'>No se encontró el préstamo.</div>";
}

$conexion->close();
    ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('numCuotas').addEventListener('change', function() {
            document.getElementById('formCuotas').submit();
        });
    </script>
    </body>

    </html>

    <?php
    function generarCuotasDiarias($fechaInicio, $montoCuota, $numCuotasEspecificadas, $fechaHoy)
    {
        $cuotas = [];
        $fechaActual = clone $fechaInicio;
        $numCuotasGeneradas = 0;
        $montoTotal = 0; // Inicializa el monto total

        while ($numCuotasEspecificadas === null || $numCuotasGeneradas < $numCuotasEspecificadas) {
            if ($numCuotasEspecificadas === null && $fechaActual > $fechaHoy) {
                break;
            }

            $cuotas[] = [
                'fecha' => $fechaActual->format('Y-m-d'),
                'monto' => $montoCuota
            ];
            $montoTotal += $montoCuota; // Suma al monto total
            $fechaActual->modify('+1 day');
            $numCuotasGeneradas++;
        }

        return ['cuotas' => $cuotas, 'montoTotal' => $montoTotal]; // Devuelve las cuotas y el monto total
    }
    ?>