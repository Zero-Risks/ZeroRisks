<?php
session_start();

// Validacion de rol para ingresar a la pagina 
require_once '../../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../index.php");
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
    if ($rol_usuario !== 'cobrador') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

require 'filtrarPrestamos.php'; // Asegúrate de que este archivo contiene la función obtenerCuotas actualizada

// Obtener el filtro desde la URL si está presente, de lo contrario, usar 'todos'
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'pendiente';

// Obtener las cuotas del día con el filtro aplicado
$cuotasHoy = obtenerCuotas($conexion, $filtro, 'Quintana Roo');

// Obtener conteos de préstamos
$conteosPrestamos = contarPrestamosPorEstado($conexion, 'Quintana Roo');
date_default_timezone_set('America/Bogota');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos del dia </title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/public/assets/css/prestaDia.css">
</head>

<body>

    <header>
        <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/inicio.php" class="botonn">
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
    </header>




    <main>


        <h1>Cuotas del Día</h1>

        <!-- Canvas para el gráfico -->

        <!-- Contenedor para el gráfico con un estilo personalizado -->
        <div class="grafico-contenedor" style="width: 400px; height: 400px;">
            <canvas id="miGrafico"></canvas>
        </div>





        <form action="prestamos_del_dia.php" method="get">
            <select name="filtro">
                <option value="pendiente" <?php echo $filtro == 'pendiente' ? 'selected' : ''; ?>>Pendientes</option>
                <option value="pagado" <?php echo $filtro == 'pagado' ? 'selected' : ''; ?>>Pagados</option>
                <option value="nopagado" <?php echo $filtro == 'nopagado' ? 'selected' : ''; ?>>No Pagados</option>
                <option value="mas-tarde" <?php echo $filtro == 'mas-tarde' ? 'selected' : ''; ?>>Mas Tarde</option>


            </select>
            <input type="submit" value="Filtrar">


            <div class="header-actions">

                <input type="text" name="busqueda" placeholder="Buscar...">

                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/clientes/agregar_clientes.php" class="btn btn-success" style="margin-left: 10px;">Registrar Cliente</a>
            </div>

        </form>






        <?php if (count($cuotasHoy) > 0) : ?>
            <div class="table-scroll-container">
                <table id="myTable">
                    <thead>
                        <tr>
                            <th>PréstamoID</th>
                            <th>Nombre Cliente</th>
                            <th>CURP</th>
                            <th>Telefono</th>
                            <th>Perfil</th>
                            <th>Pagar</th>
                            <th>No pago</th>
                            <!-- <th>Pagar cantida</th> -->
                            <th>Posponer pago</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cuotasHoy as $cuota) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cuota['ID']); ?></td>
                                <td><?php echo htmlspecialchars($cuota['NombreCliente']); ?></td>
                                <td><?php echo htmlspecialchars($cuota['IdentificacionCURP']); ?></td>
                                <td><?php echo htmlspecialchars($cuota['TelefonoCliente']); ?></td>
                                <td>
                                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/cartulina/perfil_abonos.php?id=<?php echo $cuota['IDCliente']; ?>" class="btn btn-info">Perfil </a>
                                    <?php if ($filtro != 'pagado') : ?>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmPaymentModal" onclick="setPrestamoId(<?php echo $cuota['ID']; ?>, <?php echo $cuota['MontoCuota']; ?>, '<?php echo htmlspecialchars($cuota['NombreCliente']); ?>', '<?php echo htmlspecialchars($cuota['DireccionCliente']); ?>', '<?php echo htmlspecialchars($cuota['TelefonoCliente']); ?>', '<?php echo htmlspecialchars($cuota['IdentificacionCURP']); ?>', '<?php echo htmlspecialchars($cuota['MontoAPagar']); ?>')">
                                        Pagar
                                    </button>

                                <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($filtro != 'pagado' && !$cuota['Pospuesto']) : ?>
                                        <button type="button" class="btn btn-warning" onclick="abrirModalPosponerPago(<?php echo $cuota['ID']; ?>, '<?php echo $cuota['MontoCuota']; ?>', '<?php echo $cuota['NombreCliente']; ?>', '<?php echo $cuota['DireccionCliente']; ?>', '<?php echo $cuota['TelefonoCliente']; ?>', '<?php echo $cuota['IdentificacionCURP']; ?>', <?php echo $cuota['MontoAPagar']; ?>)">
                                            No pago
                                        </button>



                                    <?php endif; ?>
                                </td>
                                <!-- <td>
                                    <!-- <?php if ($filtro != 'pagado') : ?>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#customPaymentModal" data-cliente-telefono="<?php echo htmlspecialchars($cuota['TelefonoCliente']); ?>" onclick="abrirModalPago(<?php echo $cuota['ID']; ?>,<?php echo $cuota['MontoCuota']; ?>,'<?php echo htmlspecialchars($cuota['NombreCliente']); ?>','<?php echo htmlspecialchars($cuota['DireccionCliente']); ?>','<?php echo htmlspecialchars($cuota['TelefonoCliente']); ?>','<?php echo htmlspecialchars($cuota['IdentificacionCURP']); ?>',<?php echo $cuota['MontoAPagar']; ?>)">
                                            Cantidad
                                        </button> -->


                            <?php endif; ?>
                            <!-- </td>  -->
                            <td>
                                <?php if ($filtro != 'pagado') : ?>
                                    <button type="button" class="btn btn-secondary btn-mas-tarde boton-morado" data-prestamoid="<?php echo $cuota['ID']; ?>">
                                        Más Tarde
                                    </button>
                                <?php endif; ?>
                            </td>


                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            <?php else : ?>
                <p>No hay cuotas pendientes para hoy.</p>
            <?php endif; ?>
            </div>

            <!-- Modal de Confirmación de Pago -->
            <div class="modal fade" id="confirmPaymentModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabel">Confirmar Pago</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <strong> ¿Está seguro de que desea procesar este pago?</strong><br>
                            <div>
                                <strong>Cliente:</strong> <span id="modalClienteNombre"></span><br>
                                <strong>Dirección:</strong> <span id="modalClienteDireccion"></span><br>
                                <strong>Teléfono:</strong> <span id="modalClienteTelefono"></span><br>
                                <strong>CURP:</strong> <span id="modalClienteCURP"></span><br>
                                <strong>Total a Prestamo:</strong> <span id="modalMontoAPagar"></span><br>
                                <strong>Monto Cuota:</strong>
                                <strong><span id="modalMontoCuota" class="monto-cuota-rojo"></span><br></strong>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmPaymentButton">Confirmar Pago</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de WhatsApp -->
            <div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog" aria-labelledby="whatsappModalLabel" aria-hidden="true" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="whatsappModalLabel">Enviar a WhatsApp</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Enviar recibo a WhatsApp del cliente.</p>
                            <p id="clienteDetalles"></p> <!-- Detalles del cliente -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" id="sendWhatsappButton">Enviar a WhatsApp</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal de Pago Pospuesto -->
            <div class="modal fade" id="postponePaymentModal" tabindex="-1" role="dialog" aria-labelledby="postponePaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="postponePaymentModalLabel">Pago Pospuesto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- Modal de Pago Pospuesto -->

                        <!-- ... -->
                        <div class="modal-body">
                            <strong> ¿Está seguro de que desea posponer el pago de este préstamo?</strong><br>
                            <strong>Cliente:</strong> <span id="postponeModalClienteNombre"></span><br>
                            <strong>Dirección:</strong> <span id="postponeModalClienteDireccion"></span><br>
                            <strong>Teléfono:</strong> <span id="postponeModalClienteTelefono"></span><br>
                            <strong>CURP:</strong> <span id="postponeModalClienteCURP"></span><br>
                            <strong>Total a Prestamo:</strong> <span id="postponeModalMontoAPagar"></span>
                            <strong>Monto Cuota:</strong>
                            <strong><span id="postponeModalMontoCuota" class="monto-cuota-rojo"></span><br></strong>



                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-warning" id="confirmPostponePaymentButton">Posponer Pago</button>
                        </div>

                    </div>
                </div>
            </div>
            </div>
            <!-- Modal para ingresar la cantidad personalizada -->
            <div class="modal fade" id="customPaymentModal" tabindex="-1" role="dialog" aria-labelledby="customPaymentModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customPaymentModalLabel">Pagar Cantidad Personalizada</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div>
                                <strong>Cliente:</strong> <span id="customModalClienteNombre"></span><br>
                                <strong>Dirección:</strong> <span id="customModalClienteDireccion"></span><br>
                                <strong>Teléfono:</strong> <span id="customModalClienteTelefono"></span><br>
                                <strong>CURP:</strong> <span id="customModalClienteCURP"></span><br>
                                <strong>Total Prestamo:</strong> <strong><span id="customModalMontoAPagar" class="Total-pagar"></span></strong><br>
                                <strong>Monto Cuota:</strong>
                                <strong><span id="customModalMontoCuota" class="monto-cuota-rojo"></span><br></strong>

                            </div><br>
                            <form id="customPaymentForm">
                                <div class="form-group">
                                    <label for="customAmount">Ingrese la cantidad:</label>
                                    <input type="number" class="form-control" id="customAmount" name="customAmount" required>
                                </div>
                            </form>
                            <form id="">

                            </form>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" onclick="procesarPagoPersonalizado()">Pagar</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal para Pasar Préstamo a Más Tarde -->
            <div class="modal fade" id="postponeLoanModal" tabindex="-1" role="dialog" aria-labelledby="postponeLoanModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="postponeLoanModalLabel">Pasar Préstamo a Más Tarde</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ¿Está seguro de que desea pasar el préstamo <span id="postponeLoanId"></span> a más tarde?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" onclick="confirmPostponeLoan()">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
    </main>

    <script src="/public/assets/js/presDia.js"></script>


    <script>
        var conteosPrestamos = <?php echo json_encode($conteosPrestamos); ?>;
        console.log(conteosPrestamos); // Para depuración

        var maxPendiente = parseInt(conteosPrestamos.pendiente, 10);
        var maxPagado = parseInt(conteosPrestamos.pagado, 10);
        var maxNoPagado = parseInt(conteosPrestamos.nopagado, 10);
        var maxMasTarde = parseInt(conteosPrestamos['mas-tarde'], 10);
        var maxValor = Math.max(maxPendiente, maxPagado, maxNoPagado, maxMasTarde);

        var ctx = document.getElementById('miGrafico').getContext('2d');
        var miGrafico = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pendientes', 'Pagados', 'No Pagados', 'Mas Tarde'],
                datasets: [{
                    label: 'Estado de los Préstamos',
                    data: [maxPendiente, maxPagado, maxNoPagado, maxMasTarde],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(123, 123, 192, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(123, 123, 192, 4)'
                    ],
                    borderWidth: 1,
                    borderRadius: 5,
                    barThickness: 50,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: maxValor + 1
                    },
                    x: {
                        barPercentage: 0.5
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var campoBusqueda = document.querySelector('input[name="busqueda"]');

            campoBusqueda.addEventListener('input', function() {
                var textoBusqueda = this.value.toLowerCase();
                var filas = document.querySelectorAll('#myTable tbody tr');

                filas.forEach(function(fila) {
                    var coincide = fila.textContent.toLowerCase().includes(textoBusqueda);
                    fila.style.display = coincide ? '' : 'none';
                });
            });
        });
    </script>
</body>
<!-- #region -->
</html>