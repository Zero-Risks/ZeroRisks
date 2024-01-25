<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once("../../../../../../../controllers/conexion.php");

$usuario_id = $_SESSION["usuario_id"];

// Asumiendo que la tabla de roles se llama 'roles' y tiene las columnas 'id' y 'nombre_rol'
$sql_nombre = "SELECT usuarios.nombre, roles.nombre FROM usuarios INNER JOIN roles ON usuarios.rolID = roles.id WHERE usuarios.id = ?";
$stmt = $conexion->prepare($sql_nombre);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($fila = $resultado->fetch_assoc()) {
    $_SESSION["nombre_usuario"] = $fila["nombre"];
    $_SESSION["nombre"] = $fila["nombre"]; // Guarda el nombre del rol en la sesión
}
$stmt->close();
date_default_timezone_set('America/Bogota');

// El usuario ha iniciado sesión, mostrar el contenido de la página aquí
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/dias_pago.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <title>Fechas de Pago</title>
</head>

<body>

    <body id="body">

        <!-- <header>

            <a href="javascript:history.back()" class="back-link">Volver Atrás</a>

            <div class="nombre-usuario">
                <?php
    if (isset($_SESSION["nombre_usuario"], $_SESSION["nombre"])) {
        echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span>" . htmlspecialchars($_SESSION["nombre"]) . "</span>";
    }
    ?>
            </div>

        </header> -->
        <main>
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
                echo "<table>";
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