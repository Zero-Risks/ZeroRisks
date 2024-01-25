<?php
session_start();
date_default_timezone_set('America/Bogota');

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
}

include("../../../../controllers/conexion.php");

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


include("../../../../controllers/conexion.php");

$clienteId = 0;
$cliente = null;
$zonaSeleccionada = '';
$clienteCiudadID = null;

// Verifica si el ID del cliente está en la URL y es numérico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $clienteId = $_GET['id'];

    // Consulta para obtener los datos del cliente
    $sql = "SELECT * FROM clientes WHERE ID = $clienteId";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $cliente = mysqli_fetch_assoc($resultado);
        $zonaSeleccionada = $cliente['ZonaAsignada'] ?? '';
    } else {
        die("Cliente no encontrado.");
    }
} else {
    die("ID de cliente no válido.");
}

// Consulta para obtener la ciudad del cliente
$consultaCiudadCliente = "SELECT Ciudad FROM clientes WHERE ID = $clienteId";
$resultCiudadCliente = mysqli_query($conexion, $consultaCiudadCliente);

if ($resultCiudadCliente && mysqli_num_rows($resultCiudadCliente) > 0) {
    $rowCiudadCliente = mysqli_fetch_assoc($resultCiudadCliente);
    $clienteCiudadID = $rowCiudadCliente['Ciudad'];
} else {
    // Manejar el error si no se puede obtener la ciudad del cliente
    die("Error al obtener la ciudad del cliente.");
}

// Verifica si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $curp = $_POST['curp'];
    $domicilio = $_POST['domicilio'];
    $telefono = $_POST['telefono'];
    $moneda = $_POST['moneda'];
    $zona = $_POST['zona'];
    $ciudad = $_POST['ciudad'];
    $asentamiento = $_POST['asentamiento'];

    // Prepara la consulta SQL para actualizar los datos
    $sql = "UPDATE clientes SET Nombre = '$nombre', Apellido = '$apellido', IdentificacionCURP = '$curp', Domicilio = '$domicilio', Telefono = '$telefono', MonedaPreferida = '$moneda', ZonaAsignada = (SELECT Nombre FROM zonas WHERE ID = '$zona'), Ciudad = '$ciudad', Asentamiento = '$asentamiento' WHERE ID = $clienteId";

    // Ejecuta la consulta
    if (mysqli_query($conexion, $sql)) {
        header("Location: lista_clientes.php");
    } else {
        echo "Error al actualizar los datos: " . mysqli_error($conexion);
    }
}

// Consulta para obtener las zonas
$consultaZonas = "SELECT ID, Nombre FROM zonas";
$resultZonas = mysqli_query($conexion, $consultaZonas);

// Consulta para obtener todas las ciudades
$consultaTodasLasCiudades = "SELECT * FROM ciudades";
$resultTodasLasCiudades = mysqli_query($conexion, $consultaTodasLasCiudades);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
 
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

    <main class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center">Editar Cliente</h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?id=' . $clienteId); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cliente_id" value="<?php echo $cliente['ID']; ?>">

                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" required value="<?php echo $cliente['Nombre']; ?>" class="form-control" oninput="this.value = this.value.toUpperCase()">
                            </div>

                            <div class="form-group">
                                <label for="apellido">Apellido:</label>
                                <input type="text" id="apellido" name="apellido" required value="<?php echo $cliente['Apellido']; ?>" class="form-control" oninput="this.value = this.value.toUpperCase()">
                            </div>

                            <div class="form-group">
                                <label for="curp">Identificación CURP:</label>
                                <input type="text" id="curp" name="curp" required value="<?php echo $cliente['IdentificacionCURP']; ?>" class="form-control">
                            </div>
                        </div>

                        <!-- Columna De la mitad -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="domicilio">Domicilio:</label>
                                <input type="text" id="domicilio" name="domicilio" required value="<?php echo $cliente['Domicilio']; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="telefono">Teléfono:</label>
                                <input type="text" id="telefono" name="telefono" required value="<?php echo $cliente['Telefono']; ?>" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="moneda">Moneda Preferida:</label>
                                <select id="moneda" name="moneda" class="form-control">
                                    <?php
                                    $query = "SELECT * FROM monedas";
                                    $result = mysqli_query($conexion, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $selected = ($row['ID'] == $cliente['Moneda']) ? 'selected' : '';
                                        echo "<option value='" . $row['ID'] . "' $selected>" . $row['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zona">Zona:</label>
                                <select id="zona" name="zona" class="form-control">
                                    <?php
                                    $consultaZonas = "SELECT ID, Nombre FROM zonas WHERE Nombre IN ('Chihuahua', 'Puebla', 'Quintana Roo', 'Tlaxcala')";
                                    $resultZonas = mysqli_query($conexion, $consultaZonas);

                                    while ($row = mysqli_fetch_assoc($resultZonas)) {
                                        $selected = ($row['Nombre'] == $zonaSeleccionada) ? 'selected' : '';
                                        echo '<option value="' . $row['ID'] . '" ' . $selected . '>' . $row['Nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="ciudad">Ciudad:</label>
                                <select id="ciudad" name="ciudad" class="form-control">
                                    <?php
                                    while ($row = mysqli_fetch_assoc($resultTodasLasCiudades)) {
                                        $selected = ($row['ID'] == $clienteCiudadID) ? 'selected' : '';
                                        echo '<option value="' . $row['ID'] . '" ' . $selected . '>' . $row['Nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="asentamiento">Asentamiento:</label>
                                <input type="text" id="asentamiento" name="asentamiento" required value="<?php echo $cliente['asentamiento'] ?? ''; ?>" class="form-control">
                            </div>

                        </div>
                    </div>
                    <div class="btn-container text-center mt-3">
                        <input id="boton-registrar" type="submit" value="Actualizar" class="btn btn-primary">
                    </div>

                </form>
            </div>
        </div>
    </main>
</body>

</html>