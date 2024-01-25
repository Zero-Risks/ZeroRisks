<?php
session_start();

// Validacion de rol para ingresar a la pagina 
require_once '../../../../controllers/conexion.php';

// Ruta a permisos
include("../../../../controllers/verificar_permisos.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
}
//PERMISOS PARA RECAUDOS
if (!$tiene_permiso_recaudos) {
    // El usuario no tiene el permiso, redirige a una página de error o de inicio
    header("Location: ../../../../Nopermiso.html");
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
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

    // Preparar la consulta para obtener el rol del usuario
    $stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
    $stmt->bind_param("i", $usuario_id);

    // Ejecutar la consulta
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    // Verifica si el resultado es nulo, lo que significaría que el usuario no tiene un rol válido
    if (!$fila) {
        // Redirige al usuario a una página de error o de inicio
        header("Location: ../inicio/inicio.php");
        exit();
    }

    // Extrae el nombre del rol del resultado
    $rol_usuario = $fila['Nombre'];

    // Verifica si el rol del usuario corresponde al necesario para esta página
    if ($rol_usuario !== 'supervisor') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: ../inicio/inicio.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/public/assets/css/recaudo_admin.css">
</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="../inicio/inicio.php" class="btn btn-outline-primary me-2">Volver a Inicio</a>
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
        <h1>Recaudos Totales</h1><br>

        <!-- Formulario para filtrar por fechas y/o usuario -->
        <form id="filter-form" class="mb-4">
            <h2>Filtrar Pagos</h2>
            <div class="row">
                <div class="col">
                    <label for="fechaDesde">Desde:</label>
                    <input type="date" class="form-control" id="fechaDesde" name="fechaDesde">
                </div>
                <div class="col">
                    <label for="fechaHasta">Hasta:</label>
                    <input type="date" class="form-control" id="fechaHasta" name="fechaHasta">
                </div>
                <div class="col">
                    <label for="usuario">Usuario:</label>
                    <select class="form-control" id="usuario" name="usuario">
                        <option value="">Todos los usuarios</option>
                        <!-- Las opciones se cargarán desde la base de datos -->
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
        </form>
        <div class="mb-3">
            <h3>Total Recaudado: <span id="totalPagado">0</span></h3>
        </div>
        <!-- Tabla para mostrar pagos -->
        <h2>Lista de Pagos</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>CURP</th>
                        <th>Fecha Pago</th>
                        <th>Monto Pagado</th>
                        <th>Quien Pago</th>
                    </tr>
                </thead>
                <tbody id="pagos-list">
                    <!-- Aquí se cargarán los datos de la base de datos -->
                </tbody>
            </table>
        </div>

        <script src="app.js"></script>
</body>

</html>