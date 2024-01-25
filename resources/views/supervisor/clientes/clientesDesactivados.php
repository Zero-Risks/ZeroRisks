<?php
date_default_timezone_set('America/Bogota');
session_start();

include("../../../../controllers/verificar_permisos.php");

// Validacion de rol para ingresar a la página
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

// El usuario ha iniciado sesión, mostrar el contenido de la página aquí
?>

<?php
// Incluir el archivo de conexión a la base de datos
include("../../../../controllers/conexion.php");

// Verificar si se han enviado fechas de filtro
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

// Número máximo de páginas anteriores y siguientes a mostrar
$maxPaginasMostrar = 3;

// Página actual obtenida del parámetro "pagina" en la URL
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;

// Registros por página
$registrosPorPagina = 10;

// Consulta SQL para obtener todos los clientes con el nombre de la moneda y filtro por fecha de registro
$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.HistorialCrediticio, c.ReferenciasPersonales, m.Nombre AS Moneda, c.ZonaAsignada, c.Estado, c.fecha_registro 
        FROM clientes c
        LEFT JOIN monedas m ON c.MonedaPreferida = m.ID 
        WHERE c.Estado = 0";

// Aplicar el filtro por rango de fechas si se proporcionan fechas válidas
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND c.fecha_registro BETWEEN ? AND ?";
}

$sql .= " ORDER BY c.ID DESC";

// Preparar la consulta SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros si se proporcionan fechas válidas
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
}

// Ejecutar la consulta SQL
$stmt->execute();

$resultado = $stmt->get_result();

// Calcular el total de registros
$totalRegistros = $resultado->num_rows;

// Calcular el total de páginas
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Calcular el índice de inicio para la consulta SQL
$indiceInicio = ($paginaActual - 1) * $registrosPorPagina;

// Consulta SQL con límite de registros por página
$sqlPaginacion = $sql . " LIMIT ?, ?";
$stmt = $conexion->prepare($sqlPaginacion);
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $stmt->bind_param("ssii", $fechaInicio, $fechaFin, $indiceInicio, $registrosPorPagina);
} else {
    $stmt->bind_param("ii", $indiceInicio, $registrosPorPagina);
}
$stmt->execute();
$resultadoPaginacion = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Clientes</title>
    <!-- Enlace a Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Enlace a la fuente de FontAwesome -->
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <!-- Enlace a DataTables CSS y JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="/public/assets/css/crudpresta.css">
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
    <!-- Contenedor principal -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4">Lista de Clientes Desactivados</h1>
            </div>
            <div class="col-md-4 text-end">
                <a href="lista_clientes.php" class="btn btn-secondary"><i class="fa-solid fa-check"></i></i> Ver Activados</a>
            </div>
        </div>
        <br>
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar cliente...">
            </div>
        </form>
        <?php if ($resultadoPaginacion->num_rows > 0) { ?>
            <div class="table-responsive">
                <table id="tabla-clientes" class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Domicilio</th>
                            <th>Teléfono</th>

                            <th>Acciones</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultadoPaginacion->fetch_assoc()) { ?>
                            <tr>
                                <td><?= "REC 100" . $fila["ID"] ?></td>
                                <td><?= $fila["Nombre"] ?></td>
                                <td><?= $fila["Apellido"] ?></td>
                                <td><?= $fila["Domicilio"] ?></td>
                                <td><?= $fila["Telefono"] ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accionesDropdown<?= $fila["ID"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Acciones
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="accionesDropdown<?= $fila["ID"] ?>">
                                            <?php if ($tiene_permiso_hacer_prestamo) : ?>
                                                <a class="dropdown-item" href="/resources/views/admin/creditos/prestamos.php?cliente_id=<?= $fila["ID"] ?>">
                                                    <i class="fas fa-hand-holding-usd"></i> Hacer Préstamo
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($tiene_permiso_desatrasar) : ?>
                                                <a class="dropdown-item" href="/resources/views/admin/desatrasar/hacerPrestamo.php?clienteId=<?= $fila["ID"] ?>">
                                                    <i class="fas fa-hand-holding-usd"></i> Prest Atrasado
                                                </a>
                                            <?php endif; ?>
                                            <a class="dropdown-item" href="editar_cliente.php?id=<?= $fila["ID"] ?>">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a class="dropdown-item" href="../../../../controllers/perfil_cliente.php?id=<?= $fila["ID"] ?>">
                                                <i class="fas fa-user"></i> Perfil
                                            </a>
                                            <a class="dropdown-item" href="/resources/views/admin/abonos/crud_historial_pagos.php?clienteId=<?= $fila["ID"] ?>">
                                                <i class="fa-solid fa-money-bills"></i> Pagos
                                            </a>
                                            <a class="dropdown-item" href="cambiarEstadoCliente.php?id=<?= $fila["ID"] ?>&estado=<?= $fila["Estado"] ?>">
                                                <i class="fas <?= $fila["Estado"] == 1 ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                                                <?= $fila["Estado"] == 1 ? 'Desactivar' : 'Activar' ?>
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
                <div class="col-md-12">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Calcular la página inicial y final
                        $paginaInicial = max(1, $paginaActual - $maxPaginasMostrar);
                        $paginaFinal = min($totalPaginas, $paginaActual + $maxPaginasMostrar);

                        // Enlace a la página anterior
                        if ($paginaActual > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($paginaActual - 1) . '&fecha_inicio=' . $fechaInicio . '&fecha_fin=' . $fechaFin . '">Anterior</a></li>';
                        }

                        // Enlaces a las páginas
                        for ($i = $paginaInicial; $i <= $paginaFinal; $i++) {
                            $activeClass = ($i == $paginaActual) ? 'active' : '';
                            echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="?pagina=' . $i . '&fecha_inicio=' . $fechaInicio . '&fecha_fin=' . $fechaFin . '">' . $i . '</a></li>';
                        }

                        // Enlace a la página siguiente
                        if ($paginaActual < $totalPaginas) {
                            echo '<li class="page-item"><a class="page-link" href="?pagina=' . ($paginaActual + 1) . '&fecha_inicio=' . $fechaInicio . '&fecha_fin=' . $fechaFin . '">Siguiente</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        <?php } else { ?>
            <p class="text-center">No se encontraron clientes en la base de datos.</p>
        <?php } ?>
    </div>
    <!-- JavaScript para DataTables y búsqueda en tiempo real -->
    <script>
        // JavaScript para la búsqueda en tiempo real
        const searchInput = document.getElementById('search-input');
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value.toLowerCase();

            rows.forEach((row) => {
                const rowData = Array.from(row.children)
                    .map((cell) => cell.textContent.toLowerCase())
                    .join('');

                if (rowData.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>

    <!-- Enlace a Bootstrap JS y jQuery (si es necesario) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>