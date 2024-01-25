<?php
// Iniciar sesión
session_start();

// Comprobar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Redirigir al usuario a la página de inicio de sesión si no está logueado
    header('Location: login.php');
    exit();
}

// Conexión a la base de datos
require_once "../../../../../../../controllers/conexion.php";

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

// Recuperar el ID del usuario de la sesión
$id_usuario_sesion = $_SESSION['usuario_id'];

// Obtener la fecha actual
$fecha_actual = date('Y-m-d'); // Formato año-mes-día

// consulta para lBase
$consulta_saldo = "SELECT saldo FROM usuarios WHERE id = ?";
// Consulta para Presto
$consulta_prestamo = "SELECT COALESCE(SUM(Monto), 0) as total_prestado FROM prestamos WHERE CobradorAsignado = ? AND DATE(FechaInicio) = ?";

// Consulta para Recaudo
$consulta_pago = "SELECT COALESCE(SUM(MontoPagado), 0) as total_recaudado FROM historial_pagos WHERE IDUsuario = ? AND DATE(FechaPago) = ?";

// Consulta para Gasto
$consulta_gasto = "SELECT COALESCE(SUM(GastoTotal), 0) as total_gastos FROM gastos WHERE IDUsuario = ? AND DATE(Fecha) = ?";

// Consulta para efectivo
$base = $presto = $recaudo = $gasto = 0;

// Consulta para Base
$stmt_base = $conexion->prepare($consulta_saldo);
$stmt_base->bind_param('i', $id_usuario_sesion);
$stmt_base->execute();
$resultado = $stmt_base->get_result();
$base = ($resultado->num_rows > 0) ? $resultado->fetch_assoc()['saldo'] : 0;
$stmt_base->close();

// Consulta para Presto
$stmt_presto = $conexion->prepare($consulta_prestamo);
$stmt_presto->bind_param('is', $id_usuario_sesion, $fecha_actual);
$stmt_presto->execute();
$resultado = $stmt_presto->get_result();
$presto = ($resultado->num_rows > 0) ? $resultado->fetch_assoc()['total_prestado'] : 0;
$stmt_presto->close();

// Consulta para Recaudo
$stmt_recaudo = $conexion->prepare($consulta_pago);
$stmt_recaudo->bind_param('is', $id_usuario_sesion, $fecha_actual);
$stmt_recaudo->execute();
$resultado = $stmt_recaudo->get_result();
$recaudo = ($resultado->num_rows > 0) ? $resultado->fetch_assoc()['total_recaudado'] : 0;
$stmt_recaudo->close();

// Consulta para Gasto
$stmt_gasto = $conexion->prepare($consulta_gasto);
$stmt_gasto->bind_param('is', $id_usuario_sesion, $fecha_actual);
$stmt_gasto->execute();
$resultado = $stmt_gasto->get_result();
$gasto = ($resultado->num_rows > 0) ? $resultado->fetch_assoc()['total_gastos'] : 0;
$stmt_gasto->close();

// Cálculo del efectivo
$efectivo = $base + $recaudo - $presto - $gasto;
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Estado del Día</title>
</head>

<body class="bg-light">
    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="javascript:history.back()" class="btn btn-outline-primary">Volver</a>
            </div>
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 class="text-center mb-0">Información de Hoy: <?php echo $fecha_actual; ?></h1>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Base: <span class="font-weight-bold"><?php echo number_format($base, 2); ?></span></li>
                            <li class="list-group-item">Presto: <span class="font-weight-bold"><?php echo number_format($presto, 2); ?></span></li>
                            <li class="list-group-item">Recaudo: <span class="font-weight-bold"><?php echo number_format($recaudo, 2); ?></span></li>
                            <li class="list-group-item">Gasto: <span class="font-weight-bold"><?php echo number_format($gasto, 2); ?></span></li>
                            <li class="list-group-item">Efectivo: <span class="font-weight-bold"><?php echo number_format($efectivo, 2); ?></span></li>
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <!-- <a href="#" class="btn btn-primary">Realizar otra operación</a>
                        <a href="#" class="btn btn-secondary">Cerrar sesión</a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Opcional: enlace a JavaScript de Bootstrap y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>