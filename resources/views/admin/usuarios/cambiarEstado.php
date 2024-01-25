<?php
date_default_timezone_set('America/Bogota');
session_start();

include("../../../../controllers/conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = $_GET['id'];
    $estadoActual = $_GET['estado'];
    
    // Validación adicional para el estado
    if ($estadoActual !== 'activo' && $estadoActual !== 'inactivo') {
        header("Location: crudusuarios.php?mensaje=Estado inválido");
        exit();
    }

    $nuevoEstado = $estadoActual == 'activo' ? 'inactivo' : 'activo';

    $consulta = $conexion->prepare("UPDATE usuarios SET Estado = ? WHERE ID = ?");
    $consulta->bind_param("si", $nuevoEstado, $id);

    if ($consulta->execute()) {
        header("Location: crudusuarios.php?mensaje=Estado cambiado correctamente");
    } else {
        // Mensaje de error más descriptivo
        header("Location: crudusuarios.php?mensaje=Error al cambiar el estado: " . $consulta->error);
    }
}
?>
