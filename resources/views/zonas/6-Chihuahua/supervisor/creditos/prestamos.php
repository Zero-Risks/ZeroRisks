<?php
date_default_timezone_set('America/Bogota');
session_start();

require_once '../../../../../../controllers/conexion.php';
include("../../../../../../controllers/verificar_permisos.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
    $usuario_id = $_SESSION["usuario_id"];

    $sql_nombre = "SELECT nombre FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql_nombre);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        $_SESSION["nombre_usuario"] = $fila["nombre"];
    }
    $stmt->close();

    // ID DEL CLIENTE
    if (isset($_GET['cliente_id'])) {
        $cliente_id = mysqli_real_escape_string($conexion, $_GET['cliente_id']);
        $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE ID = $cliente_id";
    } else {
        $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE Estado = 1";
    }

    // Si tienes un cliente_id, obtén su zona asignada
    $zona_cliente = "";
    if (isset($cliente_id)) {
        $query_zona_cliente = "SELECT ZonaAsignada FROM clientes WHERE ID = $cliente_id";
        $result_zona_cliente = $conexion->query($query_zona_cliente);
        if ($row = $result_zona_cliente->fetch_assoc()) {
            $zona_cliente = $row['ZonaAsignada'];
        }
    }


    // Preparar la consulta para obtener el rol del usuario
    $stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
    $stmt->bind_param("i", $usuario_id);

    // Ejecutar la consulta
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    // Verifica si el resultado es nulo, lo que significaría que el usuario no tiene un rol válido
    if (!$fila) {
        // Redirige al usuario a una página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }

    // Extrae el nombre del rol del resultado
    $rol_usuario = $fila['Nombre'];

    // Verifica si el rol del usuario corresponde al necesario para esta página
    if ($rol_usuario !== 'supervisor') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

// CLAVOS Y PRESTAMOS ACTIVOS
$cliente_id = $_GET['cliente_id'];

// Consulta para verificar el estado de morosidad del cliente
$sqlCliente = "SELECT EstadoMorosidad FROM clientes WHERE ID = ?";
$stmtCliente = $conexion->prepare($sqlCliente);
$stmtCliente->bind_param("i", $cliente_id);
$stmtCliente->execute();
$resultCliente = $stmtCliente->get_result();
$estadoMorosidad = $resultCliente->fetch_assoc();

// Consulta para verificar si el cliente tiene préstamos pendientes
$sqlPrestamos = "SELECT COUNT(*) AS PrestamosPendientes FROM prestamos WHERE IDCliente = ? AND Estado = 'pendiente'";
$stmtPrestamos = $conexion->prepare($sqlPrestamos);
$stmtPrestamos->bind_param("i", $cliente_id);
$stmtPrestamos->execute();
$resultPrestamos = $stmtPrestamos->get_result();
$prestamosPendientes = $resultPrestamos->fetch_assoc();

// Cerrar la conexión
$stmtCliente->close();
$stmtPrestamos->close();
$conexion->close();

include("../../../../../../controllers/conexion.php");

// ID DEL CLIENTE
if (isset($_GET['cliente_id'])) {
    $cliente_id = mysqli_real_escape_string($conexion, $_GET['cliente_id']);
    $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE ID = $cliente_id";
} else {
    $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE Estado = 1"; // Asegúrate de que solo se seleccionen los clientes activos
}

// Ejecutar las consultas para obtener la lista de clientes, monedas y zonas
$result_clientes = $conexion->query($query_clientes);
$query_monedas = "SELECT ID, Nombre, Simbolo FROM monedas";
$query_zonas = "SELECT Nombre FROM zonas";

$result_monedas = $conexion->query($query_monedas);
$result_zonas = $conexion->query($query_zonas);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #mensaje-clavo {
            display: none;
            color: #FF0000;
            background-color: #FFDADA;
            border-radius: 10px;
            border: 1px solid #990000;
            padding: 10px;
        }

        #mensaje-prestamo-activo {
            display: none;
            color: #FF0000;
            background-color: #FFDADA;
            margin-bottom: 10px;
            border-radius: 10px;
            border: 1px solid #990000;
            padding: 10px;
        }
    </style>
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

    <main class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Solicitud de Préstamo</h1>
                <form action="/controllers/super/procesar_prestamos/procesar_prestamo6.php" method="POST">

                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_cliente">Cliente:</label>
                                <select name="id_cliente" id="id_cliente" class="form-control" required>
                                    <?php
                                    while ($row = $result_clientes->fetch_assoc()) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="monto">Monto:</label>
                                <input type="text" name="monto" id="monto" class="form-control" required oninput="calcularMontoPagar()">
                            </div>

                            <div class="form-group">
                                <label for="tasa_interes">Tasa de Interés (%):</label>
                                <input type="text" name="TasaInteres" id="TasaInteres" class="form-control" required oninput="calcularMontoPagar()">
                            </div>

                            <div class="form-group">
                                <label for="frecuencia_pago">Frecuencia de Pago:</label>
                                <select name="frecuencia_pago" id="frecuencia_pago" class="form-control" required onchange="calcularMontoPagar()">
                                    <option value="diario">Diario</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="plazo">Plazo:</label>
                                <input type="text" name="plazo" id="plazo" class="form-control" required oninput="calcularMontoPagar()">
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="moneda_id">Moneda:</label>
                                <select name="moneda_id" id="moneda_id" class="form-control" required onchange="calcularMontoPagar()">
                                    <?php
                                    while ($row = $result_monedas->fetch_assoc()) {
                                        // Agregar el símbolo de la moneda como un atributo data-*
                                        echo "<option value='" . $row['ID'] . "' data-simbolo='" . $row['Simbolo'] . "'>" . $row['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fecha_inicio">Fecha de Inicio:</label>
                                <input type="text" name="fecha_inicio" id="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" class="form-control" readonly>
                            </div>

                            <div class="form-group">
                                <label for="zona">Zona:</label>
                                <select name="zona" id="zona" class="form-control" required>
                                    <?php
                                    if ($zona_cliente) {
                                        echo "<option value='" . $zona_cliente . "'>" . $zona_cliente . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="aplicar_comision">Aplicar Comisión:</label>
                                <select name="aplicar_comision" id="aplicar_comision" class="form-control" onchange="toggleComision()">
                                    <option value="no">No</option>
                                    <option value="si">Sí</option>
                                </select>
                            </div>

                            <div class="form-group" id="comision_container" style="display: none;">
                                <label for="valor_comision">Comisión:</label>
                                <input type="text" name="valor_comision" id="valor_comision" class="form-control">
                            </div>

                        </div>
                    </div>

                    <div class="row mt-4 justify-content-center">
                        <div class="col-md-6">
                            <div class="result-container">
                                <div id="mensaje-prestamo-activo" class="alert alert-danger" style="display: none;">
                                    Este cliente ya tiene un préstamo activo.
                                </div>

                                <div id="mensaje-clavo" class="alert alert-danger" style="display: none;">
                                    Este cliente es un "clavo".
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados y botón de envío -->
                    <div class="row mt-4 justify-content-center">
                        <div class="col-lg-8">
                            <div class="card border-secondary mb-3">
                                <div class="card-header text-white bg-secondary">Resultados</div>
                                <div class="card-body text-secondary">
                                    <h5 class="card-title">Detalles del Préstamo</h5>
                                    <p class="card-text">Monto Total a Pagar: <span class="font-weight-bold" id="monto_a_pagar">0.00</span></p>
                                    <p class="card-text">Plazo: <span class="font-weight-bold" id="plazo_mostrado">0 días</span></p>
                                    <p class="card-text">Frecuencia de Pago: <span class="font-weight-bold" id="frecuencia_pago_mostrada">Diario</span></p>
                                    <p class="card-text">Cantidad a Pagar por Cuota: <span class="font-weight-bold" id="cantidad_por_cuota">0.00</span></p>
                                    <p class="card-text">Moneda: <span class="font-weight-bold" id="moneda_simbolo">USD</span></p>
                                </div>
                            </div>
                            <button id="prestamo" type="submit" value="Hacer préstamo" class="btn btn-secondary mt-3 d-block mx-auto">Hacer prestamo</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <script>
        // Asume que los resultados de PHP se pasan a JavaScript
        var estadoMorosidad = "<?php echo $estadoMorosidad['EstadoMorosidad']; ?>";
        var prestamosPendientes = <?php echo $prestamosPendientes['PrestamosPendientes']; ?>;

        window.onload = function() {
            var mensajeClavo = document.getElementById('mensaje-clavo');
            var mensajePrestamoActivo = document.getElementById('mensaje-prestamo-activo');
            var botonPrestamo = document.getElementById('prestamo');

            // Verificar si el cliente es un "clavo"
            if (estadoMorosidad === 'clavo') {
                mensajeClavo.style.display = 'block';
                botonPrestamo.style.display = 'none'; // Ocultar el botón si el cliente es un "clavo"
            }

            // Verificar si el cliente tiene préstamos pendientes
            if (prestamosPendientes > 0) {
                mensajePrestamoActivo.style.display = 'block';
                botonPrestamo.style.display = 'none'; // Ocultar el botón si hay préstamos pendientes
            }
        }
    </script>



    <script>
        function toggleComision() {
            var aplicarComision = document.getElementById('aplicar_comision').value;
            var comisionContainer = document.getElementById('comision_container');
            comisionContainer.style.display = (aplicarComision === 'si') ? 'block' : 'none';
        }
    </script>

    <script>
        function calcularMontoPagar() {
            // Obtener los valores ingresados por el usuario
            var monto = parseFloat(document.getElementById('monto').value);
            var tasa_interes = parseFloat(document.getElementById('TasaInteres').value);
            var plazo = parseFloat(document.getElementById('plazo').value);
            var frecuencia_pago = document.getElementById('frecuencia_pago').value;
            var moneda_select = document.getElementById('moneda_id');
            var moneda_option = moneda_select.options[moneda_select.selectedIndex];
            var simbolo_moneda = moneda_option.getAttribute('data-simbolo');

            // Calcular el monto total, incluyendo el interés
            var monto_total = monto + (monto * (tasa_interes / 100));

            // Calcular la cantidad a pagar por cuota
            var cantidad_por_cuota = monto_total / plazo;

            // Actualizar los elementos HTML para mostrar los resultados en tiempo real
            document.getElementById('monto_a_pagar').textContent = monto_total.toFixed(2);
            document.getElementById('plazo_mostrado').textContent = plazo + ' ' + getPlazoText(frecuencia_pago);
            document.getElementById('frecuencia_pago_mostrada').textContent = frecuencia_pago;
            document.getElementById('cantidad_por_cuota').textContent = cantidad_por_cuota.toFixed(2);
            document.getElementById('moneda_simbolo').textContent = simbolo_moneda;
        }

        function getPlazoText(frecuencia_pago) {
            switch (frecuencia_pago) {
                case 'diario':
                    return 'día(s)';
                case 'semanal':
                    return 'semana(s)';
                case 'quincenal':
                    return 'quincena(s)';
                case 'mensual':
                    return 'mes(es)';
                default:
                    return 'día(s)';
            }
        }
    </script>


    <script>
        // Obtener el estado de morosidad del cliente desde PHP
        const estadoMorosidad = "<?php echo isset($estadoMorosidad) ? $estadoMorosidad : ''; ?>";

        // Verificar si el cliente es un "clavo" según el estado de morosidad
        if (estadoMorosidad === "clavo") {
            mostrarMensajeClavo();
        }

        function mostrarMensajeClavo() {
            const mensajeClavo = document.getElementById("mensaje-clavo");
            mensajeClavo.style.display = "block";

            // Llamar a la función para ocultar el botón después de mostrar el mensaje
            ocultarBotonPrestamo();
        }

        function ocultarBotonPrestamo() {
            const botonPrestamo = document.querySelector('#prestamo');
            if (botonPrestamo) {
                botonPrestamo.style.display = "none";
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>