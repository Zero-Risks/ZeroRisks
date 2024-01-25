<?php
// Incluye la configuración de conexión a la base de datos
require '../../../../../../../controllers/conexion.php';

// Verificar si se han enviado fechas de filtro y el ID del usuario creador
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
$usuarioCreador = isset($_POST['usuario_creador']) ? $_POST['usuario_creador'] : '';

// Consulta SQL para obtener los préstamos con la última fecha de pago y el usuario creador

$sql = "SELECT p.*, CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCompleto, c.IdentificacionCURP, MAX(f.fecha) AS ultima_fecha_pago, u.Nombre AS NombreCreador, u.Apellido AS ApellidoCreador
        FROM prestamos AS p
        INNER JOIN clientes AS c ON p.IDCliente = c.ID
        LEFT JOIN facturas AS f ON p.ID = f.id_prestamos
        LEFT JOIN usuarios AS u ON p.CobradorAsignado = u.ID
        WHERE p.Estado = 'pagado' AND p.Zona = 'Quintana Roo'"; // Agregar la condición de ubicación




// Aplicar el filtro de rango de fechas si se proporcionan fechas válidas
if (!empty($fechaInicio) && !empty($fechaFin)) {
    $sql .= " AND f.fecha BETWEEN ? AND ?";
}

// Aplicar el filtro por usuario creador si se selecciona un usuario válido
if (!empty($usuarioCreador)) {
    $sql .= " AND u.ID = ?";
}

// Agrupar por ID de préstamo
$sql .= " GROUP BY p.ID";

// Preparar la consulta SQL
$stmt = $conexion->prepare($sql);

// Vincular parámetros si se proporcionan fechas y usuario válidos
if (!empty($fechaInicio) && !empty($fechaFin) && !empty($usuarioCreador)) {
    $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $usuarioCreador);
} elseif (!empty($fechaInicio) && !empty($fechaFin)) {
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
} elseif (!empty($usuarioCreador)) {
    $stmt->bind_param("i", $usuarioCreador);
}

// Ejecutar la consulta SQL
$stmt->execute();

$result = $stmt->get_result();

session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../../index.php");
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestamos Pagados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.10/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.10/js/jquery.dataTables.js"></script>
    <link rel="stylesheet" href="Pcancelados.css">
</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="javascript:history.back()" class="btn btn-outline-primary">Volver</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span> <!-- Divisor negro -->
                            <span class="text-primary">Supervisor</span> <!-- Texto azul de Bootstrap -->
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Prestamos Cancelados</h1>

        <!-- Formulario para filtrar por rango de fechas y usuario creador -->
        <form method="post">
            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo $fechaInicio; ?>">
            </div>
            <div class="mb-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo $fechaFin; ?>">
            </div>
            <div class="mb-3">
                <label for="usuario_creador" class="form-label">Usuario Creador:</label>
                <!-- Select para seleccionar el usuario creador -->
                <select id="usuario_creador" name="usuario_creador" class="form-control">
                    <option value="">Seleccione un usuario</option>
                    <?php

                    // Consulta SQL para obtener la lista de usuarios de Quintana Roo
                    $sqlUsuarios = "SELECT ID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM usuarios WHERE Zona = 22";
                    $resultUsuarios = $conexion->query($sqlUsuarios);

                    // Generar opciones para el select
                    while ($rowUsuario = $resultUsuarios->fetch_assoc()) {
                        $selected = ($usuarioCreador == $rowUsuario['ID']) ? 'selected' : '';
                        echo "<option value='" . $rowUsuario['ID'] . "' $selected>" . $rowUsuario['NombreCompleto'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form><br>

        <!-- Barra de búsqueda en tiempo real -->
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Buscar ">
        </div>

        <!-- Tabla responsiva con scroll horizontal en dispositivos móviles -->
        <div class="table-responsive">
            <?php
            if ($result->num_rows > 0) {
                echo "<table id='prestamosTable' class='display table table-striped table-bordered'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Cliente</th>
                            <th>CURP</th>
                            <th>Fecha Pago</th>
                            <th>Usuario Creador</th>
                            <th>Hacer Préstamo</th>
                            <th>Hacer Perfil</th>
                            
                        </tr>
                    </thead>
                    <tbody>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . $row["ID"] . "</td>
                        <td>" . $row["NombreCompleto"] . "</td>
                        <td>" . $row["IdentificacionCURP"] . "</td>
                        <td>" . $row["ultima_fecha_pago"] . "</td>
                        <td>" . $row["NombreCreador"] . " " . $row["ApellidoCreador"] . "</td>
                        <td><a href='../../creditos/prestamos.php?cliente_id=" . $row["IDCliente"] . "' class='btn btn-primary btn-sm'>Hacer Préstamo</a></td>
                           <td><a href='/controllers/perfil_cliente.php?id=" . $row["IDCliente"] . "' class='btn btn-primary btn-sm'>Ver Perfil</a></td>
                    </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p class='mt-3 text-center'>No se encontraron préstamos pagados.</p>";
            }

            // Cerrar la conexión
            $conexion->close();
            ?>
        </div>

    </div>

    <!-- Agrega JavaScript para la búsqueda en tiempo real y DataTables -->
    <script>
        $(document).ready(function() {
            $('#search').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
                });
            });
        });
    </script>

    <!-- Agrega DataTables -->
    <script>
        $(document).ready(function() {
            $('#prestamosTable').DataTable();
        });
    </script>

    <!-- Agrega Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>