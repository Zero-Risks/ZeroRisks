<?php
include("session_start.php");
include("informacionCP/informacion_cliente.php");
include("informacionCP/informacion_prestamo.php");
include("informacionCP/informacion_todos_prestamos.php");
include("informacionCP/rutas.php");
include 'informacionCP/navegacion_cliente.php';
include("../../../../../../controllers/conexion.php");

// CLIENTES
$datosCliente = obtenerInformacionCliente($conexion, $id_cliente);

// Si no se encuentra el cliente, redirigir o manejar el error
if (!$datosCliente) {
    header("location: error.php");
    exit();
}

$idActual = isset($_GET['id']) ? $_GET['id'] : null;

// Inicializar $idsNavegacion solo si $idActual está definido
$idsNavegacion = null;
if ($idActual !== null) {
    $idsNavegacion = obtenerIdAnteriorSiguiente($idActual);
}

// PRESTAMOS
$datosPrestamo = obtenerInformacionPrestamoPorCliente($conexion, $id_cliente);
$prestamos = obtenerInformacionTodoPrestamoPorCliente($conexion, $id_cliente);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Cliente</title>
    <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/abonos.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <!-- Título de la Barra de Navegación -->
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Abonos</a>
            </div>

            <!-- Botón Toggler para Pantallas Pequeñas -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Enlaces de Navegación -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto"> <!-- ml-auto para alinear los enlaces a la derecha -->
                    <li class="nav-item active">
                        <a class="nav-link" href="<?= $ruta_inicio ?>">Inicio<span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $ruta_filtros ?>">Filtros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $ruta_cliente ?>">R CLiente</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $ruta_enrutar ?>">Enrutar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $ruta_pres_cancelados ?>">Pres. Cancelados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $ruta_info_hoy ?>">Inf. Hoy</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Columna de Información del Cliente -->
                    <div class="col-md-6">
                        <p class="card-text">
                            <!-- Datos siempre visibles -->
                            <strong>Nombre:</strong> <?php echo htmlspecialchars($datosCliente['Nombre']) . ' ' . htmlspecialchars($datosCliente['Apellido']); ?><br>
                            <strong>Domicilio:</strong> <?php echo htmlspecialchars($datosCliente['Domicilio']); ?><br>
                            <strong>Teléfono:</strong> <?php echo htmlspecialchars($datosCliente['Telefono']); ?><br>

                            <!-- Datos ocultos en móviles -->
                            <span class="detalle-oculto">
                                <strong>CURP:</strong> <?php echo htmlspecialchars($datosCliente['IdentificacionCURP']); ?><br>
                                <strong>Moneda:</strong> <?php echo htmlspecialchars($datosCliente['MonedaPreferida']); ?><br>
                                <strong>Estado:</strong> <?php echo htmlspecialchars($datosCliente['ZonaAsignada']); ?><br>
                                <strong>Municipio:</strong> <?php echo htmlspecialchars($datosCliente['NombreCiudad']); ?><br>
                                <strong>Colonia:</strong> <?php echo htmlspecialchars($datosCliente['asentamiento']); ?><br>
                            </span>
                        </p>
                    </div>

                    <!-- Columna de Información del Préstamo -->
                    <div class="col-md-6">
                        <?php if ($datosPrestamo) : ?>
                            <p class="card-text">
                                <span class="detalle-oculto">
                                    <strong>Total:</strong> <?php echo htmlspecialchars($datosPrestamo['Total']); ?><br>
                                    <strong>Plazo:</strong> <?php echo htmlspecialchars($datosPrestamo['Plazo']); ?><br>
                                    <strong>Inicio:</strong> <?php echo htmlspecialchars($datosPrestamo['FechaInicio']); ?><br>
                                    <strong>Fin:</strong> <?php echo htmlspecialchars($datosPrestamo['FechaVencimiento']); ?><br>
                                    <strong>Estado:</strong> <?php echo htmlspecialchars($datosPrestamo['Estado']); ?><br>
                                </span>
                                <strong>Deuda:</strong>
                                <span id="montoAPagar" data-montoapagar="<?php echo htmlspecialchars($datosPrestamo['MontoAPagar']); ?>">
                                    <?php echo htmlspecialchars($datosPrestamo['MontoAPagar']); ?>
                                </span><br>
                                <strong>Cuota:</strong> <?php echo htmlspecialchars($datosPrestamo['MontoCuota']); ?><br>
                            </p>
                        <?php else : ?>
                            <p>No se encontró información del préstamo.</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 text-right">
                        <button id="hola" class="btn btn-primary btn-sm btn-detalle" onclick="toggleDetalles(this)">
                            <span class="icono">&#x25BC;</span> <!-- Carácter Unicode para flecha hacia abajo -->
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mt-4">
        <div class="table-responsive">
            <table class="table">
                <!-- Incluir el contenido de la tabla de pagos desde un archivo separado -->
                <?php include("informacionCP/tabla_pagos.php"); ?>

                <div class="text-center">
                    <!-- Botones "Ver más" y "Ver menos" con estilos de Bootstrap -->
                    <button class="btn btn-primary" onclick="showMore(id_cliente)">Ver más</button>
                    <button class="btn btn-secondary" onclick="showLess(id_cliente)">Ver menos</button>
                </div>
            </table>
        </div>
    </div>

    <!-- Campo de Búsqueda -->
    <div class="container mt-4">
        <form action="informacionCP/procesar_cliente.php" method="post" id="formularioBusqueda">
            <input type="text" id="filtroBusqueda" placeholder="Buscar cliente" class="form-control" autocomplete="off">
            <input type="hidden" name="clienteSeleccionado" id="clienteSeleccionado" value="">
            <div id="resultadosBusqueda" class="list-group mt-2">
                <!-- Los resultados de la búsqueda se mostrarán aquí -->
            </div>
        </form>
    </div>


    <div class="container mt-3">
        <div class="d-flex justify-content-center">
            <!-- Botón Anterior -->
            <?php if ($idsNavegacion['anterior'] !== null) : ?>
                <a href="abonos.php?id=<?php echo $idsNavegacion['anterior']; ?>" class="btn btn-primary mr-2">Anterior</a>
            <?php endif; ?>

            <!-- Botón Siguiente -->
            <?php if ($idsNavegacion['siguiente'] !== null) : ?>
                <a href="abonos.php?id=<?php echo $idsNavegacion['siguiente']; ?>" class="btn btn-primary ml-2">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>


    <form action="procesar_pagos.php" method="POST">
        <!-- Campos ocultos para el ID del cliente y el ID del préstamo -->
        <input type="hidden" name="idCliente" value="<?php echo htmlspecialchars($datosCliente['ID']); ?>">
        <input type="hidden" name="idPrestamo" value="<?php echo htmlspecialchars($datosPrestamo['ID']); ?>">
        <input type="hidden" name="montoAPagar" value="<?php echo htmlspecialchars($datosPrestamo['MontoAPagar']); ?>">
        <input type="hidden" name="zonaCliente" value="<?= htmlspecialchars($datosCliente['ZonaAsignada']) ?>">

        <!-- Campos para Gestionar Pagos -->
        <div class="container mt-4">
            <div class="row">
                <div class="col">
                    <input type="text" name="cuota" id="cuota" class="form-control" placeholder="Cuota">
                </div>
                <div class="col">
                    <input type="text" name="resta" id="resta" class="form-control" placeholder="Resta">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="container mt-4">
            <button type="submit" id="btnPagar" class="btn btn-success">Pagar</button>
            <button type="button" id="btnNoPago" class="btn btn-warning">No pago</button>
            <button type="button" id="btnMasTarde" class="btn btn-secondary">Más tarde</button>
        </div>
    </form>


    <div class="container mt-4">
        <h2>Historial</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Deuda</th>
                        <th>Plazo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th>Zona</th>
                        <th>Pagos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestamos as $prestamo) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prestamo['ID'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['MontoAPagar'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['Plazo'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['FechaInicio'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['FechaVencimiento'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['Estado'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($prestamo['Zona'] ?? ''); ?></td>
                            <td><a href="../../../../controllers/dias_pago.php?id=<?= $prestamo["ID"] ?>" class="currency-button"><i class="fa-solid fa-sack-dollar"></i> Pagos</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <!-- Informacion del cliente -->
    <script src="js/informacion_cliente.js"></script>
    <!-- Incluir el Script JS para la Búsqueda -->
    <script src="informacionCP/busqueda_cliente.js"></script>
    <!-- Incluir el Script JS para la ver mas y ver menos -->
    <script>
        var id_cliente = <?= $id_cliente ?>;
    </script>
    <script src="js/cargar_pagos.js"></script>
    <!-- Incluir el Script JS para los campos de pago -->
    <script src="js/botonesPago.js"></script>
</body>

</html>