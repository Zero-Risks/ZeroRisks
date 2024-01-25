<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validación de rol para ingresar a la página
require_once("C:/laragon/www/PrestamosV2/controllers/conexion.php");


// Resto de tu código...


// Realiza la consulta para obtener los datos de historial_ingresos y usuarios
$query = "SELECT hi.id, u.id as usuario_id, u.Nombre, u.Apellido, hi.fecha_ingreso, hi.hora_ingreso
          FROM historial_ingresos hi
          JOIN usuarios u ON hi.usuario_id = u.ID";
$result = $conexion->query($query);

// Verifica si hay resultados
if ($result === false) {
    die("Error en la consulta: " . $conexion->error);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Historial de Ingresos</title>
</head>
<body>

<div class="container mt-5">
    <h2>Historial de Ingresos</h2>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Usuario ID</th>
            <th>Nombre Completo</th>
            <th>Fecha de Ingreso</th>
            <th>Hora de Ingreso</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Itera sobre los resultados y construye las filas de la tabla
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['usuario_id']}</td>";
            echo "<td>{$row['Nombre']} {$row['Apellido']}</td>";
            echo "<td>{$row['fecha_ingreso']}</td>";
            echo "<td>{$row['hora_ingreso']}</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Scripts de Bootstrap y jQuery (asegúrate de tener conexión a internet para cargarlos) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Cierra la conexión a la base de datos
$conexion->close();
?>
