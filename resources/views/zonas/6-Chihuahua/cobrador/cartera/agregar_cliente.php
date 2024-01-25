<?php
session_start();
require_once("../../../../../../controllers/conexion.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

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
// Obtener el cartera_id de la URL
$cartera_id_actual = isset($_GET['id']) ? $_GET['id'] : null;

// Consulta SQL para obtener todos los clientes sin cartera en una zona específica
$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.HistorialCrediticio, c.ReferenciasPersonales, m.Nombre AS Moneda, c.ZonaAsignada 
        FROM clientes c
        LEFT JOIN monedas m ON c.MonedaPreferida = m.ID
        WHERE c.cartera_id IS NULL";

$resultado = $conexion->query($sql);

date_default_timezone_set('America/Bogota');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Clientes</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body id="body" class="bg-light">

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
        <h1 class="text-center mb-4">Agregar cliente a cobro</h1>
        <div class="mb-3">
            <input type="text" id="search-input" class="form-control" placeholder="Buscar..." autocomplete="off">
        </div>
        <?php if ($resultado->num_rows > 0) { ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Domicilio</th>
                            <th>Teléfono</th>
                            <th>Zona Asignada</th>
                            <th>Agregar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?= "REC 100" . $fila["ID"] ?></td>
                                <td><?= $fila["Nombre"] ?></td>
                                <td><?= $fila["Apellido"] ?></td>
                                <td><?= $fila["Domicilio"] ?></td>
                                <td><?= $fila["Telefono"] ?></td>
                                <td><?= $fila["ZonaAsignada"] ?></td>
                                <td>
                                    <form action="opciones/asignar_cartera.php" method="GET">
                                        <input type="hidden" name="cliente_id" value="<?= $fila['ID'] ?>">
                                        <input type="hidden" name="cartera_id" value="<?= $cartera_id_actual ?>">
                                        <input type="submit" value="Asignar a Cobro" class="btn btn-primary btn-sm">
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-center">No se encontraron clientes en la base de datos.</p>
        <?php } ?>
    </main>

    <!-- Opcional: JavaScript de Bootstrap y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Tu script de JavaScript -->
    <script>
        // JavaScript para la búsqueda en tiempo real
        const searchInput = document.getElementById('search-input');
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = searchInput.value.toLowerCase();

            rows.forEach((row) => {
                const rowData = Array.from(row.children)
                    .map((cell) => cell.textContent.toLowerCase())
                    .join('');

                if (rowData.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>