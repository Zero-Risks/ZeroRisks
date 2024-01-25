<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../index.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once("conexion.php");

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

date_default_timezone_set('America/Bogota');

// El usuario ha iniciado sesión, mostrar el contenido de la página aquí
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Incluir Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <title>Fechas de Pago</title>
</head>

<body class="bg-light">

    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">

            <!-- Contenedor del select con tamaño ajustable y botones al lado -->
            <div class="d-flex align-items-center">
                <div style="margin-left: 15px;">
                    <a href="javascript:history.back()" class="btn btn-outline-primary me-2">Volver</a>
                </div>
            </div>

            <!-- Contenedor de la tarjeta -->
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;">
                                <?php echo $nombre_usuario; ?>
                            </span>
                            <span style="color: black;"> | </span>
                            <span class="text-primary"><?php echo $nombre_rol; ?></span>

                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <?php
        // Obtener el ID del préstamo desde el parámetro GET
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $idPrestamo = $_GET['id'];

            // Consulta SQL para obtener los pagos de la tabla facturas para el ID del préstamo
            $sql_pagos = "SELECT * FROM facturas WHERE id_prestamos = ?";
            $stmt_pagos = $conexion->prepare($sql_pagos);
            $stmt_pagos->bind_param("i", $idPrestamo);
            $stmt_pagos->execute();
            $result_pagos = $stmt_pagos->get_result();

            if ($result_pagos->num_rows > 0) {
                echo "<table class='table table-bordered table-striped'>";
                echo "<tr><th>ID</th><th>Fecha de Pago</th><th>Monto</th><th>Dueda</th></tr>";
                while ($fila_pago = $result_pagos->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($fila_pago["id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila_pago["fecha"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila_pago["monto_pagado"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila_pago["monto_deuda"]) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "No hay pagos registrados para este préstamo.";
            }
            $stmt_pagos->close();
        } else {
            echo "ID de préstamo no proporcionado.";
        }

        $conexion->close();
        ?>
    </main>

</body>

</html>