<?php
session_start();
date_default_timezone_set('America/Bogota');

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../../../controllers/conexion.php';

// El usuario está autenticado, obtén el ID del usuario de la sesión
$usuario_id = $_SESSION["usuario_id"];

// Consulta SQL para obtener el nombre del usuario y el nombre del rol
$sql_nombre = "SELECT usuarios.nombre, roles.nombre AS nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rolID = roles.id WHERE usuarios.id = ?";
$stmt = $conexion->prepare($sql_nombre);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $nombre_usuario = $fila["nombre"]; // Nombre del usuario
    $nombre_rol = $fila["nombre_rol"]; // Nombre del rol
} else {
    echo "Usuario no encontrado";
}

// Preparar la consulta para obtener el rol del usuario
$stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
$stmt->bind_param("i", $usuario_id);

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

$stmt->close();

// Verifica si el resultado es nulo o si el rol del usuario no es 'admin'
if (!$fila || $fila['Nombre'] === 'admin') {
    header("Location: ../../../../../../error404.html");
    exit();
}

// Obtener el ID de la cartera desde el parámetro GET
if (isset($_GET['id'])) {
    $cartera_id = $_GET['id'];

    // Consulta SQL para obtener los clientes de una cartera específica
    $sql = "SELECT * FROM clientes WHERE cartera_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $cartera_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Si no se proporciona un ID de cartera, redirigir o manejar el caso según sea necesario
    // Por ejemplo, redirigir a una página de error o a la lista de carteras
    header("Location: /ruta_a_pagina_de_error_o_lista_de_carteras.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes por Carteras</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

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
                            <span class="text-primary">cobrador</span> <!-- Texto azul de Bootstrap -->
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <h1 class="text-center mb-4">Clientes de este Cobro</h1>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Curp</th>
                        <th>Telefono</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Mostrar los datos de los clientes en la tabla -->
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["ID"] . "</td>";
                            echo "<td>" . $row["Nombre"] . " " . $row["Apellido"] . "</td>";
                            echo "<td>" . $row["IdentificacionCURP"] . "</td>";
                            echo "<td>" . $row["Telefono"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No se encontraron clientes para esta cartera</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Opcional: JavaScript de Bootstrap y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>