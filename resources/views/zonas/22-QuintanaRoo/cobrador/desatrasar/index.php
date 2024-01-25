<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../../../controllers/conexion.php';

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

// Preparar la consulta para obtener el rol del usuario
$stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
$stmt->bind_param("i", $usuario_id);

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

$stmt->close();

// Verifica si el resultado es nulo o si el rol del usuario no es 'admin'
if (!$fila || $fila['Nombre'] !== 'cobrador') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>


    </script>

    <title>Registrar Pagos Retroactivos</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/resources/views/admin/desatrasar/css/desatrasar.css">


</head>

<body>
    <header>
        <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/inicio.php" class="botonn">
            <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
            <span class="spann">Volver al Inicio</span>
        </a>
    </header>



    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Pagos Retroactivos</h2>

        <?php
        include '../../../../../../controllers/conexion.php';
        $id_cliente_url = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;
        $id_prestamo_url = isset($_GET['id_prestamo']) ? $_GET['id_prestamo'] : null;

        if ($id_prestamo_url) {
            // Consultar los datos del préstamo y del cliente
            $query_prestamo = "SELECT prestamos.ID, MontoAPagar, FechaInicio, MontoCuota, Plazo, clientes.ID as ClienteID, clientes.Nombre FROM prestamos INNER JOIN clientes ON prestamos.IDCliente = clientes.ID WHERE prestamos.ID = ?";
            $stmt_prestamo = $conexion->prepare($query_prestamo);
            $stmt_prestamo->bind_param("i", $id_prestamo_url);
            $stmt_prestamo->execute();
            $result_prestamo = $stmt_prestamo->get_result();
            if ($prestamo = $result_prestamo->fetch_assoc()) {
                $id_cliente_url = $prestamo['ClienteID']; // Obtener el ID del cliente para usarlo más adelante
            }
            $stmt_prestamo->close();
        }
        


        if ($id_cliente_url) {
            // Cargar los datos del cliente
            $query_cliente = "SELECT ID, Nombre FROM clientes WHERE ID = ?";
            $stmt_cliente = $conexion->prepare($query_cliente);
            $stmt_cliente->bind_param("i", $id_cliente_url);
            $stmt_cliente->execute();
            $result_cliente = $stmt_cliente->get_result();
            $cliente = $result_cliente->fetch_assoc();
            $stmt_cliente->close();

            if ($cliente) {
                // Cargar los préstamos del cliente
                $query_prestamos = "SELECT ID, MontoAPagar, FechaInicio, MontoCuota, Plazo FROM prestamos WHERE IDCliente = ?";
                $stmt_prestamos = $conexion->prepare($query_prestamos);
                $stmt_prestamos->bind_param("i", $id_cliente_url);
                $stmt_prestamos->execute();
                $result_prestamos = $stmt_prestamos->get_result();
            }
        }

        if (isset($result_prestamos)) {
            while ($row_prestamo = $result_prestamos->fetch_assoc()) {
                $fechaInicio = strtotime($row_prestamo['FechaInicio']);
                $fechaActual = strtotime(date("Y-m-d"));
                $plazoPrestamo = $row_prestamo['Plazo'];

                // Limitar el número de cuotas al plazo del préstamo
                $numCuotas = min($plazoPrestamo, floor(($fechaActual - strtotime('+1 day', $fechaInicio)) / (60 * 60 * 24)));
                echo '<div class="boton-contenedor">';
                echo '<a href="editar_prestamo.php?prestamo_id=' . $row_prestamo['ID'] . '" class="btn btn-editar-prestamo">Editar Préstamo</a>';
                echo '</div>';

                echo '<form action="procesar_pagos.php" method="post" class="card card-body" data-monto-a-pagar="' . $row_prestamo['MontoAPagar'] . '">';

                echo '<input type="hidden" name="prestamo_id" value="' . $row_prestamo['ID'] . '">';
                echo '<input type="hidden" name="cliente_id" value="' . $id_cliente_url . '">';
                echo '<div class="form-group">';
                echo '<label for="cliente_nombre">Cliente:</label>';
                echo '<input type="text" id="cliente_nombre" class="form-control" value="' . $cliente['Nombre'] . '" readonly>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="prestamo_detalle">Préstamo:</label>';
                echo '<input type="text" id="prestamo_detalle" class="form-control" value="Préstamo: ' . number_format($row_prestamo['MontoAPagar'], 0, ',', '') . ' - Fecha: ' . date("d/m/Y", $fechaInicio) . '" readonly>';
                echo '</div>';

                echo '<div class="form-group">';
                echo '<label for="num_cuotas">Número de Cuotas a Pagar:</label>';
                echo '<input type="number" id="num_cuotas" name="num_cuotas" min="1" max="' . $plazoPrestamo . '" class="form-control" value="' . $numCuotas . '">';
                echo '<input type="hidden" id="monto_cuota_oculto" value="' . $row_prestamo['MontoCuota'] . '">';

                echo '</div>';

                echo '<div id="formularios_cuotas" class="mb-3">';
                // Generar automáticamente los formularios de cuotas
                $fechaHoy = strtotime("today");
                $fechaCuota = strtotime("+1 day", $fechaInicio); // Ajustar la fecha para que comience un día después
                $totalCuotas = 0; // Inicializa la variable para el total de cuotas
                for ($i = 1; $i <= $numCuotas; $i++) {
                    if ($fechaCuota <= $fechaHoy) {
                        $montoCuota = $row_prestamo['MontoCuota'];
                        echo '<div class="cuota-group mb-3">';
                        echo '<h5>Cuota ' . $i . '</h5>';
                        echo '<div class="form-row">';
                        echo '<div class="col"><label>Monto:</label><input type="text" name="monto_cuota[]" class="form-control cuota-monto" placeholder="Monto" value="' . number_format($montoCuota, 0, ',', '') . '" oninput="this.value = this.value.replace(/\D/g, \'\')"></div>';
                        echo '<div class="col"><label>Fecha:</label><input type="date" name="fecha_cuota[]" class="form-control cuota-fecha" value="' . date("Y-m-d", $fechaCuota) . '"></div>';
                        echo '</div></div>';
                        $totalCuotas += $montoCuota; // Sumar al total de cuotas
                    }
                    $fechaCuota = strtotime("+1 day", $fechaCuota); // Incrementar la fecha para la próxima cuota
                }
                echo '</div>';

                echo '<div class="total-cuotas">Total a pagar de las cuotas mostradas: <span id="total_cuotas" class="font-weight-bold h4 text-success">' . number_format($totalCuotas, 0, ',', '') . '</span></div>';

                echo '<button type="button" id="botonRegistrarPagos" class="btn btn-primary" data-toggle="modal" data-target="#myModal" onclick="mostrarDetalles()">Registrar Pagos</button>';

                echo '</form>';
            }
        }
        ?>
    </div>

    <!-- Modal para mostrar los detalles de los pagos -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Detalles de los Pagos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- Dentro del div modal-body en tu modal existente -->
                <div class="modal-body">
                    <p><strong>Cliente:</strong> <span id="modal-cliente"></span></p>
                    <p><strong>Préstamo:</strong> <span id="modal-prestamo"></span></p>

                    <h5>Detalle de las Cuotas:</h5>
                    <ul id="modal-cuotas"></ul>
                    <p><strong>Total de las Cuotas Pagadas:</strong> <span id="modal-total-cuotas"></span></p>
                    <p><strong>Número de Cuotas a Pagar:</strong> <span id="modal-num-cuotas"></span></p </div>

                    <div class="modal-footer">
                        <!-- Botón para pagar las cuotas -->
                        <button type="button" class="btn btn-success" id="botonPagarCuotas" onclick="procesarPagos()">Pagar Cuotas</button>

                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap JS -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>





        <script>
            $(document).ready(function() {
                // Listener para cambios en el número de cuotas
                $('#num_cuotas').change(function() {
                    ajustarFormularioCuotas();
                });

                // Listener para cambios en los montos de las cuotas
                $(document).on('input', '.cuota-monto', function() {
                    recalcularTotalCuotas();
                });

                // Llamar a la función de recálculo al cargar la página
                recalcularTotalCuotas();
            });

            function obtenerMontoCuota() {
                return $('#monto_cuota_oculto').val() || '0';
            }

            function ajustarFormularioCuotas() {
                var numCuotasDeseadas = parseInt($('#num_cuotas').val(), 10);
                var numCuotasActuales = $('.cuota-group').length;

                if (numCuotasDeseadas > numCuotasActuales) {
                    for (var i = numCuotasActuales + 1; i <= numCuotasDeseadas; i++) {
                        var fechaCuota = calcularFechaCuota(i, numCuotasActuales);
                        agregarCuota(i, fechaCuota);
                    }
                } else if (numCuotasDeseadas < numCuotasActuales) {
                    while (numCuotasActuales > numCuotasDeseadas) {
                        $('.cuota-group').last().remove();
                        numCuotasActuales--;
                    }
                }

                recalcularTotalCuotas();
            }

            function agregarCuota(numeroCuota, fechaCuota) {
                var montoCuota = parseInt(obtenerMontoCuota(), 10); // Convertir a entero para eliminar decimales
                var cuotaHtml = '<div class="cuota-group mb-3">' +
                    '<h5>Cuota ' + numeroCuota + '</h5>' +
                    '<div class="form-row">' +
                    '<div class="col"><label>Monto:</label><input type="text" name="monto_cuota[]" class="form-control cuota-monto" placeholder="Monto" value="' + montoCuota + '" oninput="this.value = this.value.replace(/\D/g, \'\')"></div>' +
                    '<div class="col"><label>Fecha:</label><input type="date" name="fecha_cuota[]" class="form-control cuota-fecha" value="' + fechaCuota + '"></div>' +
                    '</div></div>';
                $('#formularios_cuotas').append(cuotaHtml);
            }

            function calcularFechaCuota(numeroCuota, numCuotasActuales) {
                var fechaUltimaCuota;
                if (numCuotasActuales > 0) {
                    fechaUltimaCuota = new Date($('.cuota-fecha').last().val());
                } else {
                    fechaUltimaCuota = new Date($('#fecha_inicio_prestamo').val());
                }

                fechaUltimaCuota.setDate(fechaUltimaCuota.getDate() + 1);
                return fechaUltimaCuota.toISOString().split('T')[0];
            }

            function recalcularTotalCuotas() {
                let total = 0;
                $('.cuota-monto').each(function() {
                    let monto = parseInt($(this).val(), 10);
                    if (!isNaN(monto)) {
                        total += monto;
                    }
                });
                $('#total_cuotas').text(total.toLocaleString());
                verificarMontoTotal();
            }

            function verificarMontoTotal() {
                var montoAPagar = parseFloat($('form').data('monto-a-pagar')) || 0;
                var totalCuotas = 0;
                $('.cuota-monto').each(function() {
                    totalCuotas += parseFloat($(this).val().replace(/,/g, '')) || 0;
                });

                if (totalCuotas > montoAPagar) {
                    $('#botonRegistrarPagos').prop('disabled', true);
                    alert('El monto total de las cuotas excede el monto a pagar del préstamo.');
                } else {
                    $('#botonRegistrarPagos').prop('disabled', false);
                }
            }


            function mostrarDetalles() {
                let cliente = $("#cliente_nombre").val();
                let prestamo = $("#prestamo_detalle").val();
                let numCuotas = $("#num_cuotas").val();

                let cuotas = [];
                $(".cuota-group").each(function(index) {
                    let monto = $(this).find(".cuota-monto").val();
                    let fecha = $(this).find(".cuota-fecha").val();
                    cuotas.push("Cuota " + (index + 1) + ": Monto: " + monto + ", Fecha: " + fecha);
                });

                let totalCuotas = $("#total_cuotas").text();

                $("#modal-cliente").text(cliente);
                $("#modal-prestamo").text(prestamo);
                $("#modal-num-cuotas").text(numCuotas);
                $("#modal-cuotas").html("<li>" + cuotas.join("</li><li>") + "</li>");
                $("#modal-total-cuotas").text(totalCuotas);
            }

            function procesarPagos() {
                var datosPago = {
                    prestamo_id: $('input[name="prestamo_id"]').val(),
                    cliente_id: $('input[name="cliente_id"]').val(),
                    monto_cuota: $('input[name="monto_cuota[]"]').map(function() {
                        return $(this).val();
                    }).get(),
                    fecha_cuota: $('input[name="fecha_cuota[]"]').map(function() {
                        return $(this).val();
                    }).get()
                };

                $.ajax({
                    type: "POST",
                    url: "procesar_pagos.php",
                    data: datosPago,
                    success: function(response) {
                        alert("Pagos procesados correctamente");
                        $('#myModal').modal('hide');
                        window.location.href = "agregar_clientes.php";
                    },
                    error: function() {
                        alert("Error al procesar el pago");
                    }
                });
            }
        </script>


</body>

</html>