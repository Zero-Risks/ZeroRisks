<?php
date_default_timezone_set('America/Bogota');
session_start();

require_once '../../../../../controllers/conexion.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

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

$stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();
if (!$fila || $fila['Nombre'] !== 'supervisor') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}

$consultaUsuarios = "SELECT ID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM usuarios WHERE SupervisorID = ?";
$stmt = $conexion->prepare($consultaUsuarios);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultadoUsuarios = $stmt->get_result();

$nombreFilter = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$fechaFilter = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$usuarioFilter = isset($_GET['usuario']) ? $_GET['usuario'] : '';

$idUsuarioParaConsulta = !empty($usuarioFilter) ? $usuarioFilter : $usuario_id;

$sql = "SELECT g.ID, g.Nombre, g.Fecha, g.IDZona, g.GastoTotal, g.Saldo, COALESCE(SUM(hp.MontoPagado), 0) AS Recaudado
        FROM gastos g
        LEFT JOIN historial_pagos hp ON g.IDZona = hp.Zona AND DATE(g.Fecha) = DATE(hp.FechaPago)
        WHERE g.IDUsuario = ?";

$whereConditions = [];
$bindTypes = 'i';
$bindValues = [$idUsuarioParaConsulta];

if (!empty($nombreFilter)) {
    $whereConditions[] = "g.Nombre LIKE CONCAT('%', ?, '%')";
    $bindTypes .= 's';
    $bindValues[] = $nombreFilter;
}
if (!empty($fechaFilter)) {
    $whereConditions[] = "DATE(g.Fecha) = ?";
    $bindTypes .= 's';
    $bindValues[] = $fechaFilter;
}

if (count($whereConditions) > 0) {
    $sql .= " AND " . implode(' AND ', $whereConditions);
}

$sql .= " GROUP BY g.ID ORDER BY g.ID DESC";

$bindValuesRef = [];
foreach ($bindValues as $key => $value) {
    $bindValuesRef[$key] = &$bindValues[$key];
}

$stmt = $conexion->prepare($sql);
call_user_func_array([$stmt, 'bind_param'], array_merge([$bindTypes], $bindValuesRef));
$stmt->execute();
$resultadoGastos = $stmt->get_result();

$sqlTotal = "SELECT SUM(GastoTotal) AS TotalGastos FROM gastos g 
             WHERE g.IDUsuario = ?";

$bindTypesTotal = 'i';
$bindValuesTotal = [$idUsuarioParaConsulta];

if (!empty($nombreFilter)) {
    $sqlTotal .= " AND g.Nombre LIKE CONCAT('%', ?, '%')";
    $bindTypesTotal .= 's';
    $bindValuesTotal[] = $nombreFilter;
}
if (!empty($fechaFilter)) {
    $sqlTotal .= " AND DATE(g.Fecha) = ?";
    $bindTypesTotal .= 's';
    $bindValuesTotal[] = $fechaFilter;
}

$refsTotal = [];
foreach ($bindValuesTotal as $key => $value) {
    $refsTotal[$key] = &$bindValuesTotal[$key];
}

$stmtTotal = $conexion->prepare($sqlTotal);
call_user_func_array([$stmtTotal, 'bind_param'], array_merge([$bindTypesTotal], $refsTotal));
$stmtTotal->execute();
$resultadoTotal = $stmtTotal->get_result();
$totalGastos = 0.0;
if ($filaTotal = $resultadoTotal->fetch_assoc()) {
    $totalGastos = $filaTotal['TotalGastos'] ?? 0.0;
}
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

            <a href="../../inicio/inicio.php" class="add-button">Volver a Inicio</a>

            <a href="../agregar/agregar_gastos.php" class="add-button">+ Añadir gasto</a>

            <input type="text" id="nombre-filter" class="filter-input" placeholder="Nombre del gasto" autocomplete="off">

            <input type="date" id="fecha-filter" class="filter-input">

            <select id="usuario-filter" class="filter-input">
                <option value="">Seleccione un usuario</option>
                <?php
                if ($resultadoUsuarios) {
                    while ($fila = $resultadoUsuarios->fetch_assoc()) {
                        echo '<option value="' . $fila['ID'] . '">' . htmlspecialchars($fila['NombreCompleto']) . '</option>';
                    }
                }
                ?>
            </select>
            <button id="apply-filters">Aplicar Filtros</button>
        </div>

        <div class="total-gastos">
        <span class="total-amount"><?php echo number_format((float)$totalGastos, 2); ?></span>
        </div>

        <div class="table-scroll-container">
            <table class="gastos-table">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Recaudado</th>
                    <th>Gastos</th>
                    <th>Saldo</th>
                </tr>
                <?php
                if ($resultadoGastos && $resultadoGastos->num_rows > 0) {
                    while ($fila = $resultadoGastos->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $fila['ID'] . '</td>';
                        echo '<td><a href="../editar/editar_gasto.php?id=' . $fila['ID'] . '">' . $fila['Nombre'] . '</a></td>';
                        echo '<td>' . $fila['Fecha'] . '</td>';
                        echo '<td>' . $fila['IDZona'] . '</td>';
                        echo '<td>' . number_format($fila['Recaudado'], 2) . '</td>';
                        echo '<td>' . number_format($fila['GastoTotal'], 2) . '</td>';
                        echo '<td>' . number_format($fila['Saldo'], 2) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">No se encontraron gastos en la base de datos.</td></tr>';
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