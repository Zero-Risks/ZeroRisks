<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require '../../../../../../../controllers/conexion.php';

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

// Verifica si el resultado es nulo o si el rol del usuario no es 'admin'
if (!$fila || $fila['Nombre'] !== 'cobrador') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}
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
        <div class="container mt-3">
            <!-- Botón para ir al inicio en el encabezado -->
            <button class="btn btn-primary" onclick="goBack()">Volver</button>
            <script>
                function goBack() {
                    window.history.back();
                }
            </script>
        </div>
    </header>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Prestamos Cancelados</h1>


        <!-- Barra de búsqueda en tiempo real -->
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Buscar ">
        </div>

        <!-- Tabla responsiva con scroll horizontal en dispositivos móviles -->
        <div class="table-responsive">
            <?php
            // Incluir el archivo de conexión a la base de datos
            require_once '../../../../../../../controllers/conexion.php';

            // Consulta SQL para seleccionar préstamos pagados con datos del cliente (incluyendo apellido)
            $sql = "SELECT p.*, CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCompleto, c.IdentificacionCURP
        FROM prestamos AS p
        INNER JOIN clientes AS c ON p.IDCliente = c.ID
        WHERE p.Estado = 'pagado' AND p.Zona = 'Tlaxcala'";

            $result = $conexion->query($sql);

            if ($result->num_rows > 0) {
                echo "<table id='prestamosTable' class='display table table-striped table-bordered'>
                        <thead class='thead-dark'>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Cliente</th>
                                <th>CURP</th>
                                <th>Perfil</th>
                                <th>Hacer Prestamo</th>
                            </tr>
                        </thead>
                        <tbody>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . $row["ID"] . "</td>
                            <td>" . $row["NombreCompleto"] . "</td>
                            <td>" . $row["IdentificacionCURP"] . "</td>
                            <td><a href='/controllers/perfil_cliente.php?id=" . $row["IDCliente"] . "' class='btn btn-primary btn-sm'>Ver Perfil</a></td>
                            <td><a href='/resources/views/zonas/28-Tlaxcala/cobrador/creditos/prestamos.php?cliente_id=" . $row["IDCliente"] . "' class='btn btn-primary btn-sm'>Hacer Préstamo</a></td>
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

    <!-- Agrega Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>