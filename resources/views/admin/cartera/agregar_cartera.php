<?php
// Archivo: agregar_cartera.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluye la configuración de conexión a la base de datos
    require_once '../../../../controllers/conexion.php';

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

    // Obtener los datos del formulario
    $nombre = $_POST["nombre"];
    $idZona = $_POST["zona"];
    $idCiudad = $_POST["ciudad"];
    $asentamiento = $_POST["asentamiento"];

    // Preparar la consulta para insertar una nueva cartera
    $stmt = $conexion->prepare("INSERT INTO carteras (nombre, zona, ciudad, asentamiento, id_usuario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisi", $nombre, $idZona, $idCiudad, $asentamiento, $usuario_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir a la página de la lista de carteras después de agregar exitosamente
        header("Location: lista_cartera.php");
        exit();
    } else {
        echo "Error al agregar la cartera: " . $stmt->error;
    }

    $stmt->close();
}

require_once '../../../../controllers/conexion.php';

// Consulta SQL para obtener todas las zonas
$consultaZonas = "SELECT ID, nombre FROM zonas";
$resultZonas = mysqli_query($conexion, $consultaZonas);

// Zonas permitidas
$zonasPermitidas = ['Chihuahua', 'Puebla', 'Quintana Roo', 'Tlaxcala'];

function obtenerTodasLasCiudades()
{
    global $conexion;  // Asegúrate de que estás utilizando la variable global $conexion

    // Array para almacenar las ciudades
    $ciudades = array();

    // Consulta SQL para obtener todas las ciudades y sus zonas
    $consultaCiudades = "SELECT ciudades.ID, ciudades.Nombre, ciudades.IDZona FROM ciudades JOIN zonas ON ciudades.IDZona = zonas.ID";
    $resultCiudades = mysqli_query($conexion, $consultaCiudades);

    // Rellenar el array con los datos de las ciudades
    while ($rowCiudad = mysqli_fetch_assoc($resultCiudades)) {
        $ciudades[] = array(
            'ID' => $rowCiudad['ID'],
            'Nombre' => $rowCiudad['Nombre'],
            'IDZona' => $rowCiudad['IDZona']
        );
    }

    return $ciudades;
}

$ciudadesJSON = json_encode(obtenerTodasLasCiudades());
?>

<!-- Resto del código HTML -->

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Cobro</title>
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
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <main class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Agregar Nuevo Cobro</h2>

                        <form method="post" action="agregar_cartera.php">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="zona" class="form-label">Estado:</label>
                                <select id="zona" name="zona" class="form-control" required onchange="cargarCiudades()">
                                    <!-- Filtro para mostrar solo las zonas específicas -->
                                    <?php
                                    while ($row = mysqli_fetch_assoc($resultZonas)) {
                                        if (in_array($row['nombre'], $zonasPermitidas)) {
                                            echo '<option value="' . $row['ID'] . '">' . $row['nombre'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad:</label>
                                <select id="ciudad" name="ciudad" class="form-control" required>
                                    <!-- Inicialmente vacío, se llenará con JavaScript -->
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="asentamiento" class="form-label">Asentamiento:</label>
                                <input type="text" id="asentamiento" name="asentamiento" class="form-control">
                            </div>

                            <div class="d-grid">
                                <input type="submit" value="Agregar" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </main>

    <script>
        var todasLasCiudades = <?php echo $ciudadesJSON; ?>;

        function filtrarCiudadesPorZona() {
            var zonaSeleccionada = document.getElementById('zona').value;
            var selectCiudad = document.getElementById('ciudad');
            selectCiudad.innerHTML = '';

            todasLasCiudades.forEach(function(ciudad) {
                if (ciudad.IDZona.toString() === zonaSeleccionada) {
                    var option = document.createElement('option');
                    option.value = ciudad.ID;
                    option.textContent = ciudad.Nombre;
                    selectCiudad.appendChild(option);
                }
            });
        }

        // Vincula la función al evento change del select de zona
        document.getElementById('zona').addEventListener('change', filtrarCiudadesPorZona);

        // Llama a la función inicialmente para llenar las ciudades según la zona predeterminada
        filtrarCiudadesPorZona();
    </script>


    <!-- Opcional: JavaScript de Bootstrap y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>