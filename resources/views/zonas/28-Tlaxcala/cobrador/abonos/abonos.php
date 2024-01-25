<?php
session_start();

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
    exit();
}


include "../../../../../../controllers/conexion.php";

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

// Verificar si se ha pasado un mensaje en la URL
$mensaje = "";
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/public/assets/css/abonos.css">
    <title>Abonos</title>
    <link rel="stylesheet" href="/public/assets/css/abonos.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
</head>
<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>
        
        <div class="nombre-usuario">
            <?php
        if (isset($_SESSION["nombre_usuario"])) {
            echo htmlspecialchars($_SESSION["nombre_usuario"])."<br>" . "<span> Cobrador<span>";
        }
        ?>
        </div>
    </header>

    <div class="menu__side" id="menu_side">

        <div class="name__page">
            <img src="/public/assets/img/logo.png" class="img logo-image" alt="">
            <h4>Recaudo</h4>
        </div>

        <div class="options__menu">

        <a href="/controllers/cerrar_sesion.php">
                <div class="option">
                    <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
                    <h4>Cerrar Sesion</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/inicio.php" class="selected">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>

           
            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/clientes/lista_clientes.php">
                <div class="option">
                    <i class="fa-solid fa-people-group" title=""></i>
                    <h4>Clientes</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/clientes/agregar_clientes.php">
                <div class="option">
                    <i class="fa-solid fa-user-tag" title=""></i>
                    <h4>Registrar Clientes</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/creditos/crudPrestamos.php">
                <div class="option">
                    <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                    <h4>Prestamos</h4>
                </div>
            </a> 

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/gastos/gastos.php">
                <div class="option">
                    <i class="fa-solid fa-sack-xmark" title=""></i>
                    <h4>Gastos</h4>
                </div>
            </a> 

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/cartera/lista_cartera.php">
                <div class="option">
                    <i class="fa-regular fa-address-book"></i>
                    <h4>Cobros</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/abonos/abonos.php">
                <div class="option">
                    <i class="fa-solid fa-money-bill-trend-up" title=""></i>
                    <h4>Abonos</h4>
                </div>
            </a>
 



        </div>

    </div>
    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <div class="container">
            <h1 class="mt-5">Formulario de Pago de Préstamos</h1>

          

            <!-- Información del cliente -->
            <div id="cliente-info" class="mt-4">
                <h2>Información del Cliente</h2> <br>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cliente-id"><strong>ID del Cliente: </strong></label>
                        <span id="cliente-id"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-nombre"><strong>Nombre: </strong></label>
                        <span id="cliente-nombre"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-apellido"><strong>Apellido: </strong></label>
                        <span id="cliente-apellido"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-domicilio"><strong>Domicilio:</strong></label>
                        <span id="cliente-domicilio"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-telefono"><strong>Teléfono:</strong></label>
                        <span id="cliente-telefono"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-curp"><strong>Identificación CURP:</strong></label>
                        <span id="cliente-curp"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cliente-zona"><strong>Zona Asignada:</strong></label>
                        <span id="cliente-zona"></span>
                    </div>
                </div>
            </div>

            <!-- Información del préstamo -->
            <div id="prestamo-info" class="mt-4">
                <h2>Información del Préstamo</h2> <br>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="prestamo-id"><strong>ID de Préstamo:</strong></label>
                        <span id="prestamo-id"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-tasa"><strong>Tasa de Interés:</strong></label>
                        <span id="prestamo-tasa"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-fecha-inicio"><strong>Fecha de Inicio:</strong></label>
                        <span id="prestamo-fecha-inicio"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-fecha-vencimiento"><strong>Fecha de Vencimiento:</strong></label>
                        <span id="prestamo-fecha-vencimiento"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-zona"><strong>Zona:</strong></label>
                        <span id="prestamo-zona"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-monto-pagar"><strong>Deuda:</strong></label>
                        <span id="prestamo-monto-pagar"></span>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="prestamo-cuota"><strong>Cuota:</strong></label>
                        <span id="prestamo-cuota"></span>
                    </div>
                </div>
            </div>

            <!-- Formulario de pago -->
            <div id="pago-form" class="mt-4">
                <h2>Registrar Pago</h2>
                <form id="formulario-pago">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="cantidad-pago">Cantidad a Pagar:</label>
                            <input type="number" id="cantidad-pago" class="form-control" required>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="fecha-pago">Fecha del Pago:</label>
                            <input type="date" id="fecha-pago" class="form-control" value="<?php echo date('Y-m-d'); ?>"
                                readonly>
                        </div>
                    </div>
                    <button type="button" id="registrarPago" class="btn btn-primary">Registrar Pago</button>
                </form>
            </div>

            <!-- Botones para navegar entre clientes -->
            <div class="mt-4">
                <button id="anteriorCliente" class="btn btn-secondary mr-2">Anterior</button>
                <button id="siguienteCliente" class="btn btn-secondary">Siguiente</button>
            </div>
        </div>

        <!-- Modal para confirmar el pago -->
        <div class="modal fade" id="confirmarPagoModal" tabindex="-1" role="dialog"
            aria-labelledby="confirmarPagoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmarPagoModalLabel">Confirmar Pago</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ¿Desea agregar este pago?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmarPago" class="btn btn-primary">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para pago confirmado -->
        <div class="modal fade" id="pagoConfirmadoModal" tabindex="-1" role="dialog"
            aria-labelledby="pagoConfirmadoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pagoConfirmadoModalLabel">Pago Confirmado</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        El pago ha sido confirmado exitosamente.
                    </div>
                    <div class="modal-footer">
                        <!-- Agregar el botón para generar la factura -->
                        <button id="generarFacturaButton" class="btn btn-primary">Generar Factura</button>

                        <!-- Agregar un botón para compartir la factura por WhatsApp -->

                        <button type="button" class="btn btn-primary" id="compartirPorWhatsAppButton">
                            Compartir por WhatsApp 
                        </button>

                        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="/public/assets/js/abonos.js"></script>
    <script src="/public/assets/js/MenuLate.js"></script>

</body>

</html>