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
    if ($rol_usuario !== 'supervisor') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

include("../../../../controllers/verificar_permisos.php");

// Incluir el archivo de conexión a la base de datos
include("../../../../controllers/conexion.php");

// Verificar si se han enviado fechas de filtro
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';

// Consulta SQL para obtener todos los clientes con el nombre de la moneda y filtro por fecha de registro
// Consulta SQL para obtener todos los clientes con el nombre de la moneda y filtro por fecha de registro
$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.HistorialCrediticio, c.ReferenciasPersonales, m.Nombre AS Moneda, c.ZonaAsignada, c.Estado, c.fecha_registro 
        FROM clientes c
        LEFT JOIN monedas m ON c.MonedaPreferida = m.ID 
        WHERE c.Estado = 1 AND c.IDUsuario = ?";

// Aplicar el filtro por rango de fechas si se proporcionan fechas válidas
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND c.fecha_registro BETWEEN ? AND ?";
}

$sql .= " ORDER BY c.ID DESC";

// Preparar la consulta SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros
$stmt->bind_param("i", $usuario_id);

// Vincular parámetros adicionales si se proporcionan fechas válidas
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $stmt->bind_param("iss", $usuario_id, $fechaInicio, $fechaFin);
}

// Ejecutar la consulta SQL
$stmt->execute();

$resultado = $stmt->get_result();

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
    <!-- Tu hoja de estilos personalizada -->
    <link rel="stylesheet" href="/public/assets/css/lista_clientes.css">
    <!-- Enlace a la fuente de FontAwesome -->
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>

</head>

<body>
    <header>
        <div class="container d-flex justify-content-between align-items-center py-2">

            <!-- Contenedor del select con tamaño ajustable y botones al lado -->
            <div class="d-flex align-items-center">
                <!-- Botones de Volver y Agregar Retiro con margen significativamente aumentado -->
                <div style="margin-left: 15px;">
                    <a href="../inicio/inicio.php" class="btn btn-outline-primary me-2">Volver a Inicio</a>
                </div>
            </div>

            <!-- Contenedor de la tarjeta -->
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

    <!-- Contenedor principal -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4">Lista de Clientes Activados</h1>
            </div>
        </div>
        <br>
        <form method="post" class="mb-4">
            <div class="input-group">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar cliente...">
                <button type="button" id="search-button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
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
                    <?php
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . "REC 100" . $fila["ID"] . '</td>';
                        echo '<td>' . $fila["Nombre"] . '</td>';
                        echo '<td>' . $fila["Apellido"] . '</td>';
                        echo '<td>' . $fila["Domicilio"] . '</td>';
                        echo '<td>' . $fila["Telefono"] . '</td>';
                        echo '<td>';
                        echo '<div class="dropdown">';
                        echo '<button class="btn btn-outline-primary dropdown-toggle" type="button" id="accionesDropdown' . $fila["ID"] . '" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                        echo '<i class="fas fa-cogs"></i> Acciones';
                        echo '</button>';
                        echo '<div class="dropdown-menu" aria-labelledby="accionesDropdown' . $fila["ID"] . '">';
                        // Aquí va la opción de acción corregida
                        echo '<a class="dropdown-item" href="../desatrasar/hacerPrestamo.php?clienteId=' . $fila["ID"] . '">
                               <i class="fas fa-hand-holding-usd"></i> Prest Atrasado
                              </a>';
                        echo '</div></div></td></tr>';
                    }

                    $stmt->close();
                    ?>
                </tbody>

            </table>

        </div>
    </div>

    <!-- JavaScript para búsqueda en tiempo real -->
    <script>
        const searchInput = document.getElementById('search-input');
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        document.getElementById('search-button').addEventListener('click', function() {
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