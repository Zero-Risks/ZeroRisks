<!-- FORMULARIO PARA EDITAR ULTIMO PAGO -->

<?php
session_start();
date_default_timezone_set('America/Bogota');

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
}

include("../../../../../../controllers/conexion.php");

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

// Verificar la existencia del ID de la factura en la URL
if (!isset($_GET['id'])) {
    echo "ID de factura no proporcionado.";
    exit();
}

// Incluir el archivo de conexión a la base de datos
include("../../../../../../controllers/conexion.php");

// Obtener el ID de la factura de la URL
$id_factura = intval($_GET['id']);

// Consulta para obtener la factura basada en el ID
$sql = "SELECT * FROM facturas WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_factura);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "No se encontró la factura para editar.";
    exit();
}

$factura = $resultado->fetch_assoc();

// Cerrar la consulta
$stmt->close();

// Si se envió el formulario para actualizar la factura 

// ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $monto_pagado = $_POST['monto_pagado'];
    $nuevo_monto_deuda = $monto - $monto_pagado;

    // Consulta para actualizar la factura
    $sql_update = "UPDATE facturas SET monto = ?, fecha = ?, monto_pagado = ?, monto_deuda = ? WHERE id = ?";
    $stmt_update = $conexion->prepare($sql_update);
    $stmt_update->bind_param("isiii", $monto, $fecha, $monto_pagado, $nuevo_monto_deuda, $id_factura);
    $stmt_update->execute();
    $stmt_update->close();

    // Actualizar MontoAPagar en la tabla prestamos
    $sql_update_prestamos = "UPDATE prestamos SET MontoAPagar = ? WHERE IDCliente = ?";
    $stmt_update_prestamos = $conexion->prepare($sql_update_prestamos);
    $stmt_update_prestamos->bind_param("di", $nuevo_monto_deuda, $factura['cliente_id']);
    $stmt_update_prestamos->execute();
    $stmt_update_prestamos->close();

    // Consulta para actualizar el MontoPagado en la tabla historial_pagos
    $sql_update_historial_pagos = "UPDATE historial_pagos SET MontoPagado = ? WHERE IDCliente = ?"; // Ajusta la condición WHERE según tu diseño de base de datos
    $stmt_update_historial_pagos = $conexion->prepare($sql_update_historial_pagos);
    $stmt_update_historial_pagos->bind_param("di", $monto_pagado, $factura['cliente_id']);
    $stmt_update_historial_pagos->execute();
    $stmt_update_historial_pagos->close();

    // Redireccionar después de la actualización
    header("Location: perfil_abonos.php?id=" . $factura['cliente_id']);
    exit();
}

date_default_timezone_set('America/Bogota');

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Factura</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span> <!-- Divisor negro -->
                            <span class="text-primary">Administrator</span> <!-- Texto azul de Bootstrap -->
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header p-3">
                        <h1 class="card-title">Editar Factura</h1>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="monto" class="form-label">Deuda antes:</label>
                                <input type="text" id="monto" name="monto" class="form-control" value="<?= $factura['monto'] ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha:</label>
                                <input type="date" id="fecha" name="fecha" class="form-control" value="<?= $factura['fecha'] ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="monto_pagado" class="form-label">Monto Pagado:</label>
                                <input type="text" id="monto_pagado" name="monto_pagado" class="form-control" value="<?= $factura['monto_pagado'] ?>">
                            </div>

                            <div class="mb-3">
                                <label for="monto_deuda" class="form-label">Deuda ahora:</label>
                                <input type="text" id="monto_deuda" name="monto_deuda" class="form-control" value="<?= $factura['monto_deuda'] ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <input type="submit" value="Actualizar" class="btn btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php
$conexion->close();
?>