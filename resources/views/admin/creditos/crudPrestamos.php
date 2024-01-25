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

// Definir el número de registros por página y la página actual
$registrosPorPagina = 10000;
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el desplazamiento (offset) para la consulta SQL
$offset = ($paginaActual - 1) * $registrosPorPagina;

// Consulta para obtener los usuarios
$consultaUsuarios = "SELECT ID, Nombre FROM usuarios ORDER BY Nombre";
$resultadoUsuarios = $conexion->query($consultaUsuarios);

// Consulta para obtener las zonas
$consultaZonas = "SELECT ID, Nombre FROM zonas ORDER BY Nombre";
$resultadoZonas = $conexion->query($consultaZonas);

// Consulta para contar el número total de registros
$sqlTotalRegistros = "SELECT COUNT(*) as total FROM prestamos WHERE   prestamos.EstadoP = 1";

if (!empty($cobradorFilter)) {
    $sqlTotalRegistros .= " AND usuarios.Nombre LIKE '%" . $conexion->real_escape_string($cobradorFilter) . "%'";
}

if (!empty($zonaFilter)) {
    $sqlTotalRegistros .= " AND prestamos.Zona LIKE '%" . $conexion->real_escape_string($zonaFilter) . "%'";
}

$resultadoTotalRegistros = $conexion->query($sqlTotalRegistros);
$totalRegistros = $resultadoTotalRegistros->fetch_assoc()['total'];

// Calcular el número total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
// RUTA PARA LOS PERMISOS
include("../../../../controllers/verificar_permisos.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Prestamos</title>
    <!-- Enlace a Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Tu hoja de estilos personalizada -->
    <link rel="stylesheet" href="/public/assets/css/crudpresta.css">
    <!-- Enlace a la fuente de FontAwesome -->
    <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
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
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">Listado de préstamos</h1>

                <?php
                if (isset($_GET['mensaje'])) {
                    $claseMensaje = strpos($_GET['mensaje'], 'Error') !== false ? 'alert-danger' : 'alert-success';
                    echo "<div class='alert $claseMensaje'>" . $_GET['mensaje'] . "</div>";
                }
                ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <input type="text" id="search-input" class="form-control" placeholder="Buscar...">
                            <div class="input-group-append">
                                <a href="prestamosDesactivados.php" class="btn btn-secondary"><i class="fas fa-ban"></i> Desactivados</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <select id="nombre-cobrador-filter" class="form-control">
                                <option value="">Todos los usuarios</option>
                                <?php while ($usuario = $resultadoUsuarios->fetch_assoc()) : ?>
                                    <option value="<?php echo htmlspecialchars($usuario['Nombre']); ?>">
                                        <?php echo htmlspecialchars($usuario['Nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <select id="zona-filter" class="form-control">
                                <option value="">Todas las zonas</option>
                                <?php while ($zona = $resultadoZonas->fetch_assoc()) : ?>
                                    <option value="<?php echo htmlspecialchars($zona['Nombre']); ?>">
                                        <?php echo htmlspecialchars($zona['Nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <button id="apply-filters" class="btn btn-primary"><i class="fas fa-filter"></i> Aplicar Filtros</button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-primary">
                            <tr>
                                <th scope="col">ID Préstamo</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">CURP</th>
                                <th scope="col">Zona</th>
                                <th scope="col">Deuda</th>

                                <th>Acciones</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include("../../../../controllers/conexion.php");

                            $cobradorFilter = isset($_GET['cobrador']) ? $_GET['cobrador'] : '';
                            $zonaFilter = isset($_GET['zona']) ? $_GET['zona'] : '';

                            $sql = "SELECT prestamos.ID AS IDPrestamo, clientes.Nombre AS NombreCliente, clientes.Apellido AS ApellidoCliente, clientes.IdentificacionCURP, prestamos.Monto, prestamos.TasaInteres, prestamos.Plazo, prestamos.Zona, prestamos.MontoAPagar, prestamos.Cuota AS MontoCuota, prestamos.EstadoP, usuarios.Nombre AS NombreCobrador 
                              FROM prestamos 
                              JOIN clientes ON prestamos.IDCliente = clientes.ID 
                              LEFT JOIN usuarios ON prestamos.CobradorAsignado = usuarios.ID 
                              WHERE clientes.Estado = 1 AND prestamos.EstadoP = 1";

                            if (!empty($cobradorFilter)) {
                                $sql .= " AND usuarios.Nombre LIKE '%" . $conexion->real_escape_string($cobradorFilter) . "%'";
                            }

                            if (!empty($zonaFilter)) {
                                $sql .= " AND prestamos.Zona LIKE '%" . $conexion->real_escape_string($zonaFilter) . "%'";
                            }

                            $sql .= " ORDER BY prestamos.ID DESC LIMIT $registrosPorPagina OFFSET $offset";
                            $resultado = $conexion->query($sql);

                            while ($datos = $resultado->fetch_object()) { ?>
                                <tr>
                                    <td><?= "REC 100" . $datos->IDPrestamo ?></td>
                                    <td><?= $datos->NombreCliente . " " . $datos->ApellidoCliente ?></td>
                                    <td><?= $datos->IdentificacionCURP ?></td>
                                    <td><?= $datos->Zona ?></td>
                                    <td><?= number_format($datos->MontoAPagar, 0, '.', '.') ?></td>

                                    <td class="icon-td">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accionesDropdown<?= $datos->IDPrestamo ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Acciones
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="accionesDropdown<?= $datos->IDPrestamo ?>">
                                                <a class="dropdown-item" href="cambiarEstado.php?id=<?= $datos->IDPrestamo ?>&estado=<?= $datos->EstadoP ?>">
                                                    <i class="fas <?= $datos->EstadoP == 1 ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                                    <?= $datos->EstadoP == 1 ? ' Desactivar' : ' Activar' ?>
                                                </a>
                                                <?php if ($tiene_permiso_desatrasar) : ?>
                                                    <a class="dropdown-item desatrasar-btn" href="#" data-id="<?= $datos->IDPrestamo ?>" data-toggle="modal" data-target="#desatrasarModal">
                                                        <i class="fas fa-history"></i> Desatrasar
                                                    </a>
                                                <?php endif; ?>
                                                <a class="dropdown-item delete-btn" href="#" data-id="<?= $datos->IDPrestamo ?>" data-toggle="modal" data-target="#borrarModal">
                                                    <i class="fas fa-trash"></i> Borrar
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="row">
                    <div class="col-md-12 text-center">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($paginaActual > 1) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $paginaActual - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true"> Anterior</span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                // Define cuántos números de página se mostrarán a cada lado de la página actual
                                $numPaginasMostrar = 3;
                                $inicio = max(1, $paginaActual - $numPaginasMostrar);
                                $fin = min($paginaActual + $numPaginasMostrar, $totalPaginas);

                                for ($i = $inicio; $i <= $fin; $i++) :
                                ?>
                                    <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($paginaActual < $totalPaginas) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?pagina=<?= $paginaActual + 1 ?>" aria-label="Next">
                                            <span aria-hidden="true">Siguiente </span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>


                <div class="modal fade" id="borrarModal" tabindex="-1" role="dialog" aria-labelledby="borrarModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="borrarModalLabel">Borrar préstamo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>¿Estás seguro de que quieres borrar este préstamo?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <a href="#" id="borrar-confirm" class="btn btn-danger">Borrar</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="desatrasarModal" tabindex="-1" role="dialog" aria-labelledby="desatrasarModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="desatrasarModalLabel">Desatrasar préstamo</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>TENGA CUIDADO AL DESATRASAR CLIENTE. YA QUE SI HIZO PAGOS DE ESTE CLIENTE LE SALDRA ERROR Y DAÑARA LA LOGICA.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <a href="#" id="desatrasar-confirm" class="btn btn-warning">Desatrasar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-KyZXEAg3QhqLMpG8r+J/T4Aj4Or5M5L6f4dOMu1zC5z5OIn5S/4ro5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z5D02F5z" crossorigin="anonymous"></script>
            <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
            <script>
                $(document).ready(function() {
                    $(".delete-btn").click(function() {
                        var prestamoID = $(this).data("id");
                        $("#borrar-confirm").attr("href", "borrar_prestamo.php?id=" + prestamoID);
                    });

                    $(".desatrasar-btn").click(function() {
                        var prestamoID = $(this).data("id");
                        $("#desatrasar-confirm").attr("href", "index.php?idPrestamo=" + prestamoID);
                    });

                    $('#search-input').on('input', function() {
                        var searchTerm = $(this).val().toLowerCase();
                        $('tbody tr').each(function() {
                            var rowText = $(this).text().toLowerCase();
                            if (rowText.indexOf(searchTerm) !== -1) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    });

                    var mensajeExito = document.getElementById('mensaje-exito');
                    if (mensajeExito) {
                        setTimeout(function() {
                            mensajeExito.style.display = 'none';
                        }, 2000);
                    }
                });

                document.getElementById('apply-filters').addEventListener('click', function() {
                    var cobrador = document.getElementById('nombre-cobrador-filter').value;
                    var zona = document.getElementById('zona-filter').value;

                    window.location.href = '?cobrador=' + encodeURIComponent(cobrador) + '&zona=' + encodeURIComponent(zona);
                });
            </script>
</body>

</html>