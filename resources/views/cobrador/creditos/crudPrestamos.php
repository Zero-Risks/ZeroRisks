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
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();
    if (!$fila) {
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
    $rol_usuario = $fila['Nombre'];
    if ($rol_usuario !== 'supervisor') {
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

// Obtener usuarios supervisados por el supervisor actual
$sqlUsuarios = "SELECT ID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM usuarios WHERE SupervisorID = ?";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->bind_param("i", $usuario_id);
$stmtUsuarios->execute();
$resultadoUsuarios = $stmtUsuarios->get_result();
$usuarios = $resultadoUsuarios->fetch_all(MYSQLI_ASSOC);
$stmtUsuarios->close();

// Filtrar préstamos basado en el usuario seleccionado
$usuarioFiltrado = isset($_GET['usuarioFiltrado']) ? $_GET['usuarioFiltrado'] : '';

// Determina el ID del cobrador a utilizar en la consulta
$idCobrador = !empty($usuarioFiltrado) ? $usuarioFiltrado : $usuario_id;

$sql = "SELECT prestamos.ID AS IDPrestamo, clientes.Nombre AS NombreCliente, clientes.Apellido AS ApellidoCliente, clientes.IdentificacionCURP, prestamos.MontoAPagar
        FROM prestamos 
        JOIN clientes ON prestamos.IDCliente = clientes.ID 
        WHERE clientes.Estado = 1 AND prestamos.EstadoP = 1 AND prestamos.CobradorAsignado = ? 
        ORDER BY prestamos.ID DESC";

$stmt = $conexion->prepare($sql);

// Asegurarse de que $idCobrador sea una referencia
$stmt->bind_param("i", $idCobrador);

$stmt->execute();
$resultado = $stmt->get_result();

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

    <header>
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="../inicio/inicio.php" class="btn btn-outline-primary me-2">Volver a Inicio</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;">
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span>
                            <span class="text-primary">Supervisor</span>
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

                <!-- Contenedor del filtro -->
                <div class="filter-container my-3">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="form-inline">
                        <div class="form-group mb-2">
                            <label for="usuarioFiltrado" class="mr-2">Filtrar por Usuario:</label>
                            <select name="usuarioFiltrado" id="usuarioFiltrado" class="form-control mr-2">
                                <option value="">Todos</option>
                                <?php foreach ($usuarios as $usuario) : ?>
                                    <option value="<?php echo $usuario['ID']; ?>" <?php echo ($usuarioFiltrado == $usuario['ID']) ? 'selected' : ''; ?>>
                                        <?php echo $usuario['NombreCompleto']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary mb-2">Filtrar</button>
                    </form>
                </div>

                <!-- Contenedor de la barra de búsqueda -->
                <div class="search-container my-3">
                    <input type="text" id="search-input" class="form-control" placeholder="Buscar...">
                </div>


                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped" id="prestamos-table">
                        <thead class="thead-primary">
                            <tr>
                                <th scope="col">ID Préstamo</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">CURP</th>
                                <th scope="col">Deuda</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($datos = $resultado->fetch_object()) : ?>
                                <tr>
                                    <td><?= "REC 100" . $datos->IDPrestamo ?></td>
                                    <td><?= $datos->NombreCliente . " " . $datos->ApellidoCliente ?></td>
                                    <td><?= $datos->IdentificacionCURP ?></td>
                                    <td><?= number_format($datos->MontoAPagar, 0, '.', '.') ?></td>

                                    <td class="icon-td">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accionesDropdown<?= $datos->IDPrestamo ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Acciones
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="accionesDropdown<?= $datos->IDPrestamo ?>">

                                                <a class="dropdown-item desatrasar-btn" href="index.php" data-id="<?= $datos->IDPrestamo ?>" data-toggle="modal" data-target="#desatrasarModal">
                                                    <i class="fas fa-history"></i> Desatrasar
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var searchInput = document.getElementById('search-input');
            var tableRows = document.querySelectorAll("#prestamos-table tbody tr");

            searchInput.addEventListener('input', function() {
                var searchQuery = searchInput.value.toLowerCase();

                for (var i = 0; i < tableRows.length; i++) {
                    var row = tableRows[i];
                    var id = row.cells[0].textContent.toLowerCase();
                    var nombre = row.cells[1].textContent.toLowerCase();
                    var curp = row.cells[2].textContent.toLowerCase();

                    if (id.indexOf(searchQuery) > -1 || nombre.indexOf(searchQuery) > -1 || curp.indexOf(searchQuery) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    </script>

    <!-- Opcional: enlace a JavaScript de Bootstrap y sus dependencias -->
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