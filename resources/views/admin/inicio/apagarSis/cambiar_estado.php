<?php
date_default_timezone_set('America/Bogota');
session_start();



// Incluye el archivo de conexión a la base de datos
include("../../../../../controllers/conexion.php");

// Verifica si el usuario es administrador
if ($_SESSION["rol"] != 1) {
    die("Acceso denegado. Debes ser administrador para realizar esta acción.");
}

// Verifica si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevoEstado = $_POST['estado'] == 'activo' ? 'activo' : 'inactivo';

    // Prepara la consulta para actualizar el estado del sistema
    $sql = "INSERT INTO sistema_estado (Estado, CambiadoPor) VALUES (?, ?)";
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

    $stmt->bind_param("si", $nuevoEstado, $_SESSION["usuario_id"]);
    if ($stmt->execute() === false) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }

    // Establece un mensaje de confirmación
    $_SESSION['cambio_estado_mensaje'] = "El sistema se ha " . ($nuevoEstado == 'activo' ? 'encendido' : 'apagado') . " correctamente.";

    // Redirige a la página de cambio de estado
    header("Location: apagarSist.php");
    exit();
}

$stmt->close();
$conexion->close();
?>
