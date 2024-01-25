<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validación de rol para ingresar a la página
require_once '../../../../controllers/conexion.php';

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
    $cliente_id = isset($_GET['clienteId']) ? $_GET['clienteId'] : null;

    // Si tienes un cliente_id, obtén su zona asignada
    $zona_cliente = "";
    if ($cliente_id) {
        $query_zona_cliente = "SELECT ZonaAsignada FROM clientes WHERE ID = ?";
        $stmt = $conexion->prepare($query_zona_cliente);
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $result_zona_cliente = $stmt->get_result();
        if ($row = $result_zona_cliente->fetch_assoc()) {
            $zona_cliente = $row['ZonaAsignada'];
        }
        $stmt->close();
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
    if ($rol_usuario !== 'admin') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos</title>

    <link rel="stylesheet" href="/resources/views/admin/desatrasar/css/prestamo.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS y dependencias (jQuery y Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>

<body style="background-color: #a8dbd6;">

    <header><br>
        <a href="/resources/views/admin/inicio/inicio.php" class="botonn">
            <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
            <span class="spann">Volver al Inicio</span>
        </a>
        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Administrator<span>";
            }
            ?>
        </div>
    </header><br><br><br>




    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <br>
        <h1>Solicitud de Préstamo Atrasados</h1><br>
        <?php
        // Verificar si se pasó un mensaje de error y el ID del cliente
        if (isset($_GET['error']) && $_GET['error'] == 'prestamo_reciente' && isset($_GET['clienteId'])) {
            $clienteId = htmlspecialchars($_GET['clienteId']);
            echo '<div class="alert alert-danger" role="alert">Este cliente ya tiene un préstamo realizado. Editalo en desatrasar</div>';
            echo '<a href="index.php?id_cliente=' . $clienteId . '" class="btn btn-primary">Ir a desatrasar</a>';
        }
        ?>
        <!-- Formulario de solicitud de préstamo (prestamo.html) -->
        <form action="procesar_prestamo.php" method="POST" class="form-container">
            <?php
            // Obtener el nombre del cliente seleccionado a través de la URL
            if ($cliente_id) {
                $query_cliente = "SELECT Nombre FROM clientes WHERE ID = ?";
                $stmt = $conexion->prepare($query_cliente);
                $stmt->bind_param("i", $cliente_id);
                $stmt->execute();
                $result_cliente = $stmt->get_result();

                if ($row_cliente = $result_cliente->fetch_assoc()) {
                    $nombre_cliente = $row_cliente['Nombre'];
                }
                $stmt->close();
            }
            ?>
            <?php
            // Incluir el archivo de conexión a la base de datos
            include("../../../../controllers/conexion.php");

            // ID DEL CLIENTE
            if ($cliente_id) {
                $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE ID = ?";
            } else {
                $query_clientes = "SELECT ID, Nombre, ZonaAsignada FROM clientes WHERE Estado = 1"; // Asegúrate de que solo se seleccionen los clientes activos
            }

            // Ejecutar las consultas para obtener la lista de clientes, monedas y zonas
            $stmt = $conexion->prepare($query_clientes);
            if ($cliente_id) {
                $stmt->bind_param("i", $cliente_id);
            }
            $stmt->execute();
            $result_clientes = $stmt->get_result();
            $stmt->close();

            $query_monedas = "SELECT ID, Nombre, Simbolo FROM monedas";
            $query_zonas = "SELECT Nombre FROM zonas";

            $result_monedas = $conexion->query($query_monedas);
            $result_zonas = $conexion->query($query_zonas);
            ?>

            <label for="id_cliente">Cliente:</label>
            <select name="id_cliente" required>
                <?php
                while ($row = $result_clientes->fetch_assoc()) {
                    echo "<option value='" . $row['ID'] . "'>" . $row['Nombre'] . "</option>";
                }
                ?>
            </select><br>


            <label for="monto">Monto:</label>
            <input type="text" name="monto" id="monto" required oninput="calcularMontoPagar()"><br>

            <label for="tasa_interes">Tasa de Interés (%):</label>
            <input type="text" name="TasaInteres" id="TasaInteres" required oninput="calcularMontoPagar()"><br>

            <label for="frecuencia_pago">Frecuencia de Pago:</label>
            <select name="frecuencia_pago" id="frecuencia_pago" required onchange="calcularMontoPagar()">
                <option value="diario">Diario</option>
                <option value="semanal">Semanal</option>
                <option value="quincenal">Quincenal</option>
                <option value="mensual">Mensual</option>
            </select><br>

            <label for="plazo">Plazo:</label>
            <input type="text" name="plazo" id="plazo" required oninput="calcularMontoPagar()"><br>


            <label for="moneda_id">Moneda:</label>
            <select name="moneda_id" id="moneda_id" required onchange="calcularMontoPagar()">
                <?php
                while ($row = $result_monedas->fetch_assoc()) {
                    // Agregar el símbolo de la moneda como un atributo data-*
                    echo "<option value='" . $row['ID'] . "' data-simbolo='" . $row['Simbolo'] . "'>" . $row['Nombre'] . "</option>";
                }
                ?>
            </select><br>

            <!-- Reemplaza el campo de fecha de inicio con un campo de texto readonly -->

            <label for="fecha_inicio">Fecha de Inicio:</label>
            <span style="color: red; font-weight: bold;">POR FAVOR INGRESA LA FECHA</span>
            <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required><br>

            <label for="zona">Zona:</label>
            <select name="zona" required>
                <?php
                if ($zona_cliente) {
                    echo "<option value='" . $zona_cliente . "'>" . $zona_cliente . "</option>";
                }
                ?>
            </select><br>


            <label for="aplicar_comision">Aplicar Comisión:</label>
            <select name="aplicar_comision" id="aplicar_comision" onchange="toggleComision()">
                <option value="no">No</option>
                <option value="si">Sí</option>
            </select><br>

            <div id="comision_container" style="display: none;">
                <label for="valor_comision">Comisión (%):</label>
                <input type="text" name="valor_comision" id="valor_comision"><br>
            </div>

            <div class="result-container">
                <h2>Resultados</h2>
                <p>Monto Total a Pagar: <span id="monto_a_pagar">0.00</span></p>
                <p>Plazo: <span id="plazo_mostrado">0 días</span></p>
                <p>Frecuencia de Pago: <span id="frecuencia_pago_mostrada">Diario</span></p>
                <p>Cantidad a Pagar por Cuota: <span id="cantidad_por_cuota">0.00</span></p>
                <p>Moneda: <span id="moneda_simbolo">USD</span></p>
            </div>

            <button type="button" onclick="mostrarModalPrestamo()" class="calcular-button">Hacer préstamo</button>

            <!-- Modal para Confirmación de Préstamo -->
            <div id="modalPrestamo" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="cerrarModal()">&times;</span>

                    <h2>Confirmar Préstamo</h2>
                    <p>Cliente: <strong><span id="modalCliente"></span></strong></p>
                    <p>Monto: <strong><span id="modalMonto"></span></strong></p>
                    <p>Tasa de Interés:<strong> <span id="modalTasaInteres"></span></strong></p>
                    <p>Frecuencia de Pago:<strong> <span id="modalFrecuenciaPago"></strong></span></p>
                    <p>Plazo:<strong> <span id="modalPlazo"></span></strong></p>
                    <p>Moneda: <strong><span id="modalMoneda"></span></strong></p>
                    <p><strong><span style="color: red;">Fecha de Inicio:</span></strong> <span id="modalFechaInicio"></span></p>
                    <p>Zona:<strong> <span id="modalZona"></span></strong></p>
                    <p>Aplicar Comisión: <strong><span id="modalAplicarComision"></span></strong></p>
                    <p>Valor Comisión:<strong> <span id="modalValorComision"></span></strong></p>




                    <button onclick="confirmarPrestamo()">Confirmar</button>
                </div>
            </div>

        </form>

    </main>
    <script>
        function toggleComision() {
            var aplicarComision = document.getElementById('aplicar_comision').value;
            var comisionContainer = document.getElementById('comision_container');
            comisionContainer.style.display = (aplicarComision === 'si') ? 'block' : 'none';
        }

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

        //modal
        function cerrarModal() {
            document.getElementById('modalPrestamo').style.display = 'none';
        }

        function mostrarModalPrestamo() {
            // Capturar los valores del formulario
            var cliente = document.querySelector('select[name="id_cliente"]').selectedOptions[0].text;
            var monto = document.getElementById('monto').value;
            var tasaInteres = document.getElementById('TasaInteres').value;
            var frecuenciaPago = document.getElementById('frecuencia_pago').value;
            var plazo = document.getElementById('plazo').value;
            var moneda = document.querySelector('select[name="moneda_id"]').selectedOptions[0].text;
            var fechaInicio = document.getElementById('fecha_inicio').value;
            var zona = document.querySelector('select[name="zona"]').selectedOptions[0].text;
            var aplicarComision = document.getElementById('aplicar_comision').value;
            var valorComision = document.getElementById('valor_comision').value;

            // Mostrar los valores en el modal
            document.getElementById('modalCliente').textContent = cliente;
            document.getElementById('modalMonto').textContent = monto;
            document.getElementById('modalTasaInteres').textContent = tasaInteres;
            document.getElementById('modalFrecuenciaPago').textContent = frecuenciaPago;
            document.getElementById('modalPlazo').textContent = plazo;
            document.getElementById('modalMoneda').textContent = moneda;
            document.getElementById('modalFechaInicio').textContent = fechaInicio;
            document.getElementById('modalZona').textContent = zona;
            document.getElementById('modalAplicarComision').textContent = aplicarComision;
            document.getElementById('modalValorComision').textContent = valorComision;

            // Mostrar el modal
            document.getElementById('modalPrestamo').style.display = 'block';


        }
    </script>



</body>

</html>