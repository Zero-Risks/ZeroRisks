
<!-- FORMULARIO PARA EDITAR ULTIMO PAGO -->

<?php
session_start();
date_default_timezone_set('America/Bogota');

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
    exit();
}

// Verificar la existencia del ID de la factura en la URL
if (!isset($_GET['id'])) {
    echo "ID de factura no proporcionado.";
    exit();
}

// Incluir el archivo de conexión a la base de datos
include("../../../../../../../controllers/conexion.php");

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
    <link rel="stylesheet" href="/public/assets/css/editar_cartulina.css">
</head>

<body>
    <h1>Editar Factura</h1>
    <form method="POST">
        <label for="monto">Deuda antes:</label>
        <input type="text" id="monto" name="monto" value="<?= $factura['monto'] ?>"><br>

        <label for="fecha">Fecha:</label>
        <input type="text" id="fecha" name="fecha" value="<?= $factura['fecha'] ?>"><br>

        <label for="monto_pagado">Monto Pagado:</label>
        <input type="text" id="monto_pagado" name="monto_pagado" value="<?= $factura['monto_pagado'] ?>"><br>

        <label for="monto_deuda">Deuda ahora:</label>
        <input type="text" id="monto_deuda" name="monto_deuda" value="<?= $factura['monto_deuda'] ?>"><br>

        <input type="submit" value="Actualizar">
    </form>
</body>

</html>

<?php
$conexion->close();
?>