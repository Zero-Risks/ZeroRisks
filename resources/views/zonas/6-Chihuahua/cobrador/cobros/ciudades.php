<?php
date_default_timezone_set('America/Bogota');
session_start();
require_once '../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
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

$idZona = isset($_GET['zona']) ? $_GET['zona'] : null;



?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Ciudades</title>
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
        <h2 class="text-center my-4">Listado de Ciudades</h2>

        <div class="search-container">
            <input type="text" id="search-input" class="form-control" placeholder="Buscar..." autocomplete="off">
        </div>

        <?php
        if ($idZona) {

            // Realizar la consulta SQL para obtener las ciudades de la zona especificada
            $sql = "SELECT * FROM ciudades WHERE IDZona = ?";
            if ($stmt = $conexion->prepare($sql)) {
                $stmt->bind_param("i", $idZona);
                $stmt->execute();
                $resultado = $stmt->get_result();

                echo "<div class='table-responsive'>";
                echo "<table class='table table-bordered table-hover'>";
                echo "<thead class='thead-light'><tr><th>ID</th><th>Ciudad</th><th>CD Postal</th></tr></thead>";

                // Verifica si hay ciudades en la base de datos para esta zona
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo "<tr 'zona-row'>";
                        echo "<td>" . 'REC-10' . $row['ID'] . "</td>";
                        echo "<td>" . $row['Nombre'] . "</td>";
                        echo "<td>" . "N/a" . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay ciudades registradas para esta zona.</td></tr>";
                }
                echo "</table>";
                echo "</div>";

                $stmt->close();
            }
        } else {
            echo "<p>Por favor, seleccione una zona para ver las ciudades correspondientes.</p>";
        }
        $conexion->close();
        ?>

        <script>
            document.getElementById('search-input').addEventListener('keyup', function(event) {
                var searchQuery = event.target.value.toLowerCase();
                var rows = document.querySelectorAll('table tr');

                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    if (text.includes(searchQuery)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>
        <!-- Bootstrap JavaScript and dependencies -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>