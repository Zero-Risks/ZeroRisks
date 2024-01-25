<?php
date_default_timezone_set('America/Bogota');
session_start();


include("../../../../controllers/conexion.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

// Obtener el ID del préstamo y el estado actual
$prestamoId = $_GET['id'];
$estadoActual = $_GET['estado'];

// Determinar el nuevo estado
$nuevoEstado = $estadoActual == 1 ? 0 : 1;

// Cambiar el estado del préstamo
$sql = "UPDATE prestamos SET EstadoP = ? WHERE ID = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $nuevoEstado, $prestamoId);
$stmt->execute();

// Redirigir de nuevo a la lista de préstamos
header("Location: /resources/views/admin/creditos/crudPrestamos.php");
exit();
?>
