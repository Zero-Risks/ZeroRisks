<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../index.php");
    exit();
}

require_once '../../../../../controllers/conexion.php';

$usuario_id = $_SESSION["usuario_id"];

// Obtener el ID del usuario filtrado o usar el ID de la sesión por defecto
$idUsuarioFiltrado = isset($_GET['usuarioFiltro']) && $_GET['usuarioFiltro'] !== '' ? $_GET['usuarioFiltro'] : $usuario_id;

// Consulta SQL para obtener los préstamos pagados
$sql = "SELECT p.ID, CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCliente, c.IdentificacionCURP, p.MontoAPagar
        FROM prestamos p
        INNER JOIN clientes c ON p.IDCliente = c.ID
        WHERE p.Estado = 'pagado' AND p.CobradorAsignado = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $idUsuarioFiltrado);
$stmt->execute();
$resultado = $stmt->get_result();

// Consulta para obtener usuarios supervisados por el supervisor actual
$sqlUsuarios = "SELECT ID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM usuarios WHERE SupervisorID = ?";
$stmtUsuarios = $conexion->prepare($sqlUsuarios);
$stmtUsuarios->bind_param("i", $usuario_id);
$stmtUsuarios->execute();
$resultadoUsuarios = $stmtUsuarios->get_result();

$usuariosFiltro = array();
if ($resultadoUsuarios->num_rows > 0) {
    while ($fila = $resultadoUsuarios->fetch_assoc()) {
        $usuariosFiltro[] = $fila;
    }
}

$stmtUsuarios->close();
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
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="../inicio.php" class="btn btn-outline-primary me-2">Volver a Inicio</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;">
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span>
                            <span class="text-primary">Supervisor</span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Prestamos Cancelados</h1>

        <!-- Formulario de filtro por usuario -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-3 d-flex justify-content-between">
            <div class="form-group">
                <label for="usuarioFiltro" class="mr-2">Filtrar por Usuario:</label>
                <select name="usuarioFiltro" id="usuarioFiltro" class="form-control mr-2">
                    <option value="">Mi Usuario</option>
                    <?php foreach ($usuariosFiltro as $usuario) : ?>
                        <option value="<?php echo $usuario['ID']; ?>" <?php echo $idUsuarioFiltrado == $usuario['ID'] ? 'selected' : ''; ?>>
                            <?php echo $usuario['NombreCompleto']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary align-self-end">Aplicar Filtro</button>
        </form>


        <!-- Barra de búsqueda en tiempo real -->
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Buscar ">
        </div>

        <!-- Tabla responsiva con scroll horizontal en dispositivos móviles -->
        <div class="table-responsive">
            <table id='prestamosTable' class='display table table-striped table-bordered'>
                <thead class='thead-dark'>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Cliente</th>
                        <th>CURP</th>
                        <th>Monto Pagado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Verifica si la consulta fue exitosa y tiene resultados
                    if ($resultado && $resultado->num_rows > 0) :
                        while ($row = $resultado->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row["ID"]; ?></td>
                                <td><?php echo $row["NombreCliente"]; ?></td>
                                <td><?php echo $row["IdentificacionCURP"]; ?></td>
                                <td><?php echo number_format($row["MontoAPagar"], 2); ?></td>
                            </tr>
                        <?php endwhile;
                    else : ?>
                        <tr>
                            <td colspan="4">No se encontraron préstamos pagados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            var searchInput = $('#search');
            var tableRows = $('#prestamosTable tbody tr');

            searchInput.on('keyup', function() {
                var searchText = searchInput.val().toLowerCase();

                tableRows.each(function() {
                    var row = $(this);
                    var idText = row.find('td:eq(0)').text().toLowerCase();
                    var nombreText = row.find('td:eq(1)').text().toLowerCase();
                    var curpText = row.find('td:eq(2)').text().toLowerCase();

                    if (idText.includes(searchText) || nombreText.includes(searchText) || curpText.includes(searchText)) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            });
        });
    </script>


    <!-- Agrega Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>