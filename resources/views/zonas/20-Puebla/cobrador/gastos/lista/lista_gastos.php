<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validación de rol para ingresar a la página
require_once '../../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
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

// Obtener los valores de los filtros
$nombreFilter = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$fechaFilter = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$usuarioFilter = isset($_GET['usuario']) ? $_GET['usuario'] : '';

// Comenzar a construir la consulta SQL
$sql = "SELECT g.ID, g.Nombre, g.Fecha, g.IDUsuario, g.IDZona, g.GastoTotal, g.Saldo, COALESCE(SUM(hp.MontoPagado), 0) AS Recaudado
        FROM gastos g
        LEFT JOIN historial_pagos hp ON g.IDZona = hp.Zona AND DATE(g.Fecha) = DATE(hp.FechaPago)
        LEFT JOIN usuarios u ON g.IDUsuario = u.ID
        WHERE g.IDUsuario = $usuario_id";

// Agregar condiciones WHERE según los filtros
$whereConditions = [];
if (!empty($nombreFilter)) {
    $whereConditions[] = "g.Nombre LIKE '%" . $conexion->real_escape_string($nombreFilter) . "%'";
}
if (!empty($fechaFilter)) {
    $whereConditions[] = "DATE(g.Fecha) = '" . $conexion->real_escape_string($fechaFilter) . "'";
}
if (!empty($usuarioFilter)) {
    $whereConditions[] = "u.Nombre LIKE '%" . $conexion->real_escape_string($usuarioFilter) . "%'";
}

if (count($whereConditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $whereConditions);
}

$sql .= " GROUP BY g.ID ORDER BY g.ID DESC";
$resultado = $conexion->query($sql);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="lista_gastos.css">
    <title>Control de Gastos</title>
</head>

<body>
    <main>
        <div class="toolbar">

            <select name="" id="Lista_total" class="Lista_total">
                <option value="">Gastos</option>
                <option value="../../inicio/recaudos/recuado_admin.php">Recaudo</option>
                <option value="../../retiros/retiros.php">Retiros</option>
            </select>

            <script>
                document.getElementById('Lista_total').addEventListener('change', function() {
                    var url = this.value; // Obtiene la URL del valor seleccionado
                    if (url) { // Verifica si la URL no está vacía
                        window.location.href = url; // Redirige a la URL
                    }
                });
            </script>

            <a href="../../inicio/inicio.php" class="add-button">Volver</a>

            <a href="../agregar/agregar_gastos.php" class="add-button">+ Añadir
                gasto</a>
            <input type="text" id="nombre-filter" class="filter-input" placeholder="Nombre del gasto">
            <input type="date" id="fecha-filter" class="filter-input">
            <input type="text" id="usuario-filter" class="filter-input" placeholder="Nombre del usuario">
            <button id="apply-filters">Aplicar Filtros</button>
        </div>

        <div class="table-scroll-container">
            <table class="gastos-table">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Recaudado</th>
                    <th>Gastos</th>
                    <th>Saldo</th>
                </tr>
                <?php
                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $fila['ID'] . '</td>';
                        echo '<td><a href="/resources/views/admin/gastos/editar/editar_gasto.php?id=' . $fila['ID'] . '">' . $fila['Nombre'] . '</a></td>';
                        echo '<td>' . $fila['Fecha'] . '</td>';
                        echo '<td>' . number_format($fila['Recaudado'], 2) . '</td>';
                        echo '<td>' . number_format($fila['GastoTotal'], 2) . '</td>';
                        echo '<td>' . number_format($fila['Saldo'], 2) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No se encontraron gastos en la base de datos.</td></tr>';
                }
                ?>
            </table>
        </div>
    </main>

    <script>
        document.getElementById('apply-filters').addEventListener('click', function() {
            var nombre = document.getElementById('nombre-filter').value;
            var fecha = document.getElementById('fecha-filter').value;
            var usuario = document.getElementById('usuario-filter').value;

            window.location.href = '?nombre=' + encodeURIComponent(nombre) + '&fecha=' + encodeURIComponent(fecha) +
                '&usuario=' + encodeURIComponent(usuario);
        });
    </script>
</body>

</html>