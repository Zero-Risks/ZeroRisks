<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validacion de rol para ingresar a la pagina 
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

            <a href="/resources/views/admin/inicio/inicio.php">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>

            <a href="/resources/views/admin/usuarios/crudusuarios.php">
                <div class="option">
                    <i class="fa-solid fa-users" title=""></i>
                    <h4>Usuarios</h4>
                </div>
            </a>

            <a href="/resources/views/admin/usuarios/registrar.php">
                <div class="option">
                    <i class="fa-solid fa-user-plus" title=""></i>
                    <h4>Registrar Usuario</h4>
                </div>
            </a>

            <a href="/resources/views/admin/clientes/lista_clientes.php">
                <div class="option">
                    <i class="fa-solid fa-people-group" title=""></i>
                    <h4>Clientes</h4>
                </div>
            </a>

            <a href="/resources/views/admin/clientes/agregar_clientes.php">
                <div class="option">
                    <i class="fa-solid fa-user-tag" title=""></i>
                    <h4>Registrar Clientes</h4>
                </div>
            </a>
            <a href="/resources/views/admin/creditos/crudPrestamos.php">
                <div class="option">
                    <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                    <h4>Prestamos</h4>
                </div>
            </a>
            <a href="/resources/views/admin/cobros/cobros.php">
                <div class="option">
                    <i class="fa-solid fa-arrow-right-to-city" title=""></i>
                    <h4>Zonas de cobro</h4>
                </div>
            </a>

            <a href="/resources/views/admin/gastos/gastos.php">
                <div class="option">
                    <i class="fa-regular fa-address-book"></i>
                    <h4>Gastos</h4>
                </div>
            </a>

            <a href="/resources/views/admin/abonos/abonos.php" class="selected">
                <div class="option">
                    <i class="fa-solid fa-money-bill-trend-up" title=""></i>
                    <h4>Abonos</h4>
                </div>
            </a>
            <a href="/resources/views/admin/retiros/retiros.php">
                <div class="option">
                    <i class="fa-solid fa-scale-balanced" title=""></i>
                    <h4>Retiros</h4>
                </div>
            </a>

            <a href="/resources/views/admin/cartera/lista_cartera.php">
                <div class="option">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <h4>Cobros</h4>
                </div>
            </a>
        </div>

    </div>

    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <div class="container">
            <h1 class="mt-5">Abonos</h1>

            <!-- CARTULINAAAAAAAAA -->

            <div class="info-cliente">
                <div class="columna">
                    <p><strong>Nombre: </strong><?= $fila["Nombre"] ?></p>
                    <p><strong>Apellido: </strong><?= $fila["Apellido"] ?> </p>
                    <p><strong>Curp: </strong><?= $fila["IdentificacionCURP"] ?> </p>
                    <p><strong>Domicilio: </strong><?= $fila["Domicilio"] ?> </p>
                    <p><strong>Teléfono: </strong><?= $fila["Telefono"] ?> </p>
                    <p><strong>Cuota:</strong> <?= htmlspecialchars(number_format($info_prestamo['Cuota'])); ?></p>
                    <p><strong>Total:</strong> <?= htmlspecialchars(number_format($total_prestamo)); ?>
                </div>
                <div class="columna">
                    <p><strong>Estado: </strong><?= $fila["ZonaAsignada"] ?> </p>
                    <p><strong>Municipio: </strong><?= $fila["CiudadNombre"] ?> </p>
                    <p><strong>Cononia: </strong><?= $fila["asentamiento"] ?> </p>
                    <p><strong>Plazo:</strong> <?= htmlspecialchars($info_prestamo['Plazo']); ?></p>
                    <p><strong>Estado:</strong> <?= htmlspecialchars($info_prestamo['Estado']); ?></p>
                    <p><strong>Inicio:</strong> <?= htmlspecialchars($info_prestamo['FechaInicio']); ?></p>
                    <p><strong>Fin:</strong> <?= htmlspecialchars($info_prestamo['FechaVencimiento']); ?></p>
                    </p>
                </div>
            </div>

            <div class="profile-loans">
                <?php
                include("../../../../controllers/conexion.php");

                if (isset($_GET['show_all']) && $_GET['show_all'] === 'true') {
                    // Si se solicita mostrar todas las filas
                    $sql = "SELECT id, fecha, monto_pagado, monto_deuda FROM facturas WHERE cliente_id = ?";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("i", $id_cliente);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    $num_rows = $resultado->num_rows;
                    if ($num_rows > 0) {
                        echo "<table id='tabla-prestamos'>";
                        echo "<tr><th>Fecha</th><th>Abono</th><th>Resta</th><th>Editar</th></tr>";
                        $last_row = null;
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($fila['fecha']) . "</td>";
                            echo "<td>" . htmlspecialchars($fila['monto_pagado']) . "</td>";
                            echo "<td>" . htmlspecialchars($fila['monto_deuda']) . "</td>";
                            $last_row = $fila; // Actualizar la última fila en cada iteración
                            echo "</tr>";
                        }

                        // Mostrar el enlace de "Editar" solo para la última fila
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($last_row['fecha']) . "</td>";
                        echo "<td>" . htmlspecialchars($last_row['monto_pagado']) . "</td>";
                        echo "<td>" . htmlspecialchars($last_row['monto_deuda']) . "</td>";
                        echo "<td><a href='editar_pago.php?id=" . $last_row['id'] . "'>Editar</a></td>";
                        echo "</tr>";

                        echo "</table>";
                        echo "<button onclick='showLess()'>Ver menos</button>"; // Botón para mostrar menos
                    } else {
                        echo "<p>No se encontraron pagos para este cliente.</p>";
                    }

                    $stmt->close();
                } else {
                    // Mostrar solo la última fila inicialmente
                    $sql = "SELECT id, fecha, monto_pagado, monto_deuda FROM facturas WHERE cliente_id = ?";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("i", $id_cliente);
                    $stmt->execute();
                    $resultado = $stmt->get_result();

                    $num_rows = $resultado->num_rows;
                    if ($num_rows > 0) {
                        echo "<table id='tabla-prestamos'>";
                        echo "<tr><th>Fecha</th><th>Abono</th><th>Resta</th><th>Editar</th></tr>";
                        $last_row = null;
                        while ($fila = $resultado->fetch_assoc()) {
                            $last_row = $fila; // Actualizar la última fila en cada iteración
                        }

                        // Mostrar solo la última fila
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($last_row['fecha']) . "</td>";
                        echo "<td>" . htmlspecialchars($last_row['monto_pagado']) . "</td>";
                        echo "<td>" . htmlspecialchars($last_row['monto_deuda']) . "</td>";
                        echo "<td><a href='editar_pago.php?id=" . $last_row['id'] . "'>Editar</a></td>";
                        echo "</tr>";

                        echo "</table>";

                        echo "<button onclick='showMore()'>Ver más</button>";
                    } else {
                        echo "<p>No se encontraron pagos para este cliente.</p>";
                    }

                    $stmt->close();
                }
                ?>
            </div>

            <script>
                function showMore() {
                    window.location.href = '?id=<?= $id_cliente ?>&show_all=true';
                }

                function showLess() {
                    window.location.href = '?id=<?= $id_cliente ?>&show_all=false';
                }
            </script>


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
                            <input type="date" id="fecha-pago" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
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
        <div class="modal fade" id="confirmarPagoModal" tabindex="-1" role="dialog" aria-labelledby="confirmarPagoModalLabel" aria-hidden="true">
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
        <div class="modal fade" id="pagoConfirmadoModal" tabindex="-1" role="dialog" aria-labelledby="pagoConfirmadoModalLabel" aria-hidden="true">
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