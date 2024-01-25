<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validación de rol para ingresar a la página
require_once '../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
    $usuario_id = $_SESSION["usuario_id"];
    // Resto de la lógica de autenticación...
}

// Verificar si se proporciona un ID de préstamo a editar a través de la URL
if (isset($_GET['prestamo_id'])) {
    $prestamo_id = mysqli_real_escape_string($conexion, $_GET['prestamo_id']);

    // Consultar la base de datos para obtener los detalles del préstamo a editar
    $sql = "SELECT * FROM prestamos WHERE ID = $prestamo_id";
    $result = $conexion->query($sql);

    if ($result->num_rows == 1) {
        // Obtener los datos del préstamo
        $row = $result->fetch_assoc();
        $cliente_id = $row['IDCliente'];
        $monto = isset($row['Monto']) ? floatval($row['Monto']) : 0;
        $tasa_interes = isset($row['TasaInteres']) ? floatval($row['TasaInteres']) : 0;
        $frecuencia_pago = isset($row['FrecuenciaPago']) ? $row['FrecuenciaPago'] : '';
        $plazo = isset($row['Plazo']) ? intval($row['Plazo']) : 0;
        $moneda_id = isset($row['MonedaID']) ? $row['MonedaID'] : '';
        $fecha_inicio = isset($row['FechaInicio']) ? $row['FechaInicio'] : '';
        $zona = isset($row['Zona']) ? $row['Zona'] : '';
        $aplicar_comision = isset($row['AplicarComision']) ? $row['AplicarComision'] : 'no'; // Valor por defecto si no existe la clave 'AplicarComision'
        $comision = isset($row['Comision']) ? floatval($row['Comision']) : 0; // 

        // Consultar la base de datos para obtener el nombre del cliente
        $query_cliente = "SELECT Nombre FROM clientes WHERE ID = $cliente_id";
        $result_cliente = $conexion->query($query_cliente);

        if ($result_cliente->num_rows == 1) {
            $row_cliente = $result_cliente->fetch_assoc();
            $nombre_cliente = $row_cliente['Nombre'];
        } else {
            $nombre_cliente = "Cliente Desconocido";
        }
    } else {
        // El préstamo no existe
        header("Location: /ruta_a_pagina_de_error.php");
        exit();
    }
}

// Otras lógicas y verificaciones necesarias...
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/resources/views/admin/desatrasar/css/prestamo.css">
    <link rel="stylesheet" href="/resources/views/admin/desatrasar/css/prestamo.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS y dependencias (jQuery y Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <title>Editar Préstamo</title>
    
</head>

<body style="background-color: #a8dbd6;">
    <header>
        <!-- Encabezado (similar al formulario original) -->
    </header>
    <br><br><br><br>
    

    <main>

        <h1>Editar Préstamo</h1><br><br>
        <!-- Formulario de edición de préstamo -->
        <form action="procesar_edicion_prestamo.php" method="POST" class="form-container">
            <input type="hidden" name="prestamo_id" value="<?php echo $prestamo_id; ?>">

            <!-- Campo para editar el cliente -->
            <!-- Campo para mostrar el nombre del cliente -->
            <label for="nombre_cliente">Nombre del Cliente:</label>
            <input type="text" name="nombre_cliente" id="nombre_cliente" value="<?php echo $nombre_cliente; ?>" readonly><br>

            <!-- Campo para editar el monto -->
            <label for="monto">Monto:</label>
            <input type="text" name="monto" id="monto" required value="<?php echo $monto; ?>"><br>

            <!-- Campo para editar la tasa de interés -->
            <label for="TasaInteres">Tasa de Interés (%):</label>
            <input type="text" name="TasaInteres" id="TasaInteres" required value="<?php echo $tasa_interes; ?>"><br>

            <!-- Campo para editar la frecuencia de pago -->
            <label for="frecuencia_pago">Frecuencia de Pago:</label>
            <select name="frecuencia_pago" id="frecuencia_pago" required>
                <option value="diario" <?php if ($frecuencia_pago == 'diario') echo 'selected'; ?>>Diario</option>
                <option value="semanal" <?php if ($frecuencia_pago == 'semanal') echo 'selected'; ?>>Semanal</option>
                <option value="quincenal" <?php if ($frecuencia_pago == 'quincenal') echo 'selected'; ?>>Quincenal</option>
                <option value="mensual" <?php if ($frecuencia_pago == 'mensual') echo 'selected'; ?>>Mensual</option>
            </select><br>

            <!-- Campo para editar el plazo -->
            <label for="plazo">Plazo:</label>
            <input type="text" name="plazo" id="plazo" required value="<?php echo $plazo; ?>"><br>

            <!-- Campo para editar la moneda -->
            <label for="moneda_id">Moneda:</label>
            <select name="moneda_id" id="moneda_id" required>
                <?php
                // Lógica para cargar opciones de monedas desde la base de datos
                $query_monedas = "SELECT ID, Nombre, Simbolo FROM monedas";
                $result_monedas = $conexion->query($query_monedas);
                while ($moneda = $result_monedas->fetch_assoc()) {
                    $selected = ($moneda['ID'] == $moneda_id) ? "selected" : "";
                    echo "<option value='" . $moneda['ID'] . "' data-simbolo='" . $moneda['Simbolo'] . "' $selected>" . $moneda['Nombre'] . "</option>";
                }
                ?>
            </select><br>

            <!-- Campo para editar la fecha de inicio -->
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <span style="color: red; font-weight: bold;">POR FAVOR INGRESA LA FECHA</span>
            <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>" required><br>

            <!-- Campo para editar la zona -->
            <label for="zona">Zona:</label>
            <input type="text" name="zona" id="zona" required value="<?php echo $zona; ?>"><br>

            <!-- Campo para editar si se aplica comisión -->
            <label for="aplicar_comision">Aplicar Comisión:</label>
            <select name="aplicar_comision" id="aplicar_comision">
                <option value="no" <?php if ($aplicar_comision == 'no') echo 'selected'; ?>>No</option>
                <option value="si" <?php if ($aplicar_comision == 'si') echo 'selected'; ?>>Sí</option>
            </select><br>

            <!-- Campo para editar el valor de la comisión -->
            <div id="comision_container" <?php if ($aplicar_comision != 'si') echo 'style="display: none;"'; ?>>
                <label for="comision">Comisión (%):</label>
                <input type="text" name="comision" id="comision" value="<?php echo $comision; ?>"><br>

            </div>

            <!-- Resultados (mostrar información actual) -->
            <div class="result-container">
                <h2>Resultados</h2>
                <p>Monto Total a Pagar: <span id="monto_a_pagar">0.00</span></p>
                <p>Plazo: <span id="plazo_mostrado">0 días</span></p>
                <p>Frecuencia de Pago: <span id="frecuencia_pago_mostrada">Diario</span></p>
                <p>Cantidad a Pagar por Cuota: <span id="cantidad_por_cuota">0.00</span></p>
                <p>Moneda: <span id="moneda_simbolo">USD</span></p>
            </div>

            <input type="submit" value="Guardar cambios" class="calcular-button">
        </form>
    </main>
    <script>
        // Función para manejar la visibilidad del campo de comisión
        function toggleComision() {
            var aplicarComision = document.getElementById('aplicar_comision').value;
            var comisionContainer = document.getElementById('comision_container');
            comisionContainer.style.display = (aplicarComision === 'si') ? 'block' : 'none';
        }

        // Función para calcular los resultados en tiempo real
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

        // Función para obtener el texto del plazo en función de la frecuencia de pago
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

        // Asignar eventos a los elementos del formulario
        document.getElementById('aplicar_comision').addEventListener('change', toggleComision);
        document.getElementById('monto').addEventListener('input', calcularMontoPagar);
        document.getElementById('TasaInteres').addEventListener('input', calcularMontoPagar);
        document.getElementById('plazo').addEventListener('input', calcularMontoPagar);
        document.getElementById('frecuencia_pago').addEventListener('change', calcularMontoPagar);
        document.getElementById('moneda_id').addEventListener('change', calcularMontoPagar);

        // Calcular resultados al cargar la página
        calcularMontoPagar();
        toggleComision();
    </script>

</body>

</html>