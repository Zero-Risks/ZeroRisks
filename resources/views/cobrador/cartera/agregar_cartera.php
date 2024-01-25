<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

require_once '../../../../controllers/conexion.php';

$usuario_id = $_SESSION["usuario_id"];

// Obtener el nombre de la zona del usuario actual
$sqlZonaUsuario = "SELECT zonas.nombre AS nombre_zona FROM usuarios 
                   JOIN zonas ON usuarios.zona = zonas.ID 
                   WHERE usuarios.id = ?";
$stmtZona = $conexion->prepare($sqlZonaUsuario);
$stmtZona->bind_param("i", $usuario_id);
$stmtZona->execute();
$resultZona = $stmtZona->get_result();
$nombreZona = null;

if ($fila = $resultZona->fetch_assoc()) {
    $nombreZona = $fila["nombre_zona"];
}
$stmtZona->close();

// Consulta SQL para obtener las ciudades de la zona específica del usuario
$sqlCiudades = "SELECT ID, Nombre FROM ciudades WHERE IDZona = (SELECT zona FROM usuarios WHERE id = ?)";
$stmtCiudades = $conexion->prepare($sqlCiudades);
$stmtCiudades->bind_param("i", $usuario_id);
$stmtCiudades->execute();
$resultCiudades = $stmtCiudades->get_result();
$ciudades = $resultCiudades->fetch_all(MYSQLI_ASSOC);
$stmtCiudades->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $idCiudad = $_POST["ciudad"];
    $asentamiento = $_POST["asentamiento"];

    // Obtener el ID de la zona a partir del nombre de la zona
    $sqlObtenerZona = "SELECT ID FROM zonas WHERE nombre = ?";
    $stmtObtenerZona = $conexion->prepare($sqlObtenerZona);
    $stmtObtenerZona->bind_param("s", $nombreZona);
    $stmtObtenerZona->execute();
    $resultObtenerZona = $stmtObtenerZona->get_result();
    $filaZona = $resultObtenerZona->fetch_assoc();
    $idZona = $filaZona["ID"];
    $stmtObtenerZona->close();

    $stmt = $conexion->prepare("INSERT INTO carteras (nombre, zona, ciudad, asentamiento, id_usuario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisi", $nombre, $idZona, $idCiudad, $asentamiento, $usuario_id);

    if ($stmt->execute()) {
        header("Location: lista_cartera.php");
        exit();
    } else {
        echo "Error al agregar la cartera: " . $stmt->error;
    }

    $stmt->close();
}
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
                                <select id="zona" name="zona" class="form-control" required>
                                    <option value="<?= $idZona; ?>" selected><?= htmlspecialchars($nombreZona); ?></option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad:</label>
                                <select id="ciudad" name="ciudad" class="form-control" required>
                                    <?php foreach ($ciudades as $ciudad) : ?>
                                        <option value="<?= htmlspecialchars($ciudad['ID']); ?>">
                                            <?= htmlspecialchars($ciudad['Nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
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

    <!-- Opcional: JavaScript de Bootstrap y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>