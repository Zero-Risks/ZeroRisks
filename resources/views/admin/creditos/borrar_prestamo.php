<?php
date_default_timezone_set('America/Bogota');
session_start();

require_once '../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Verificación del rol del usuario
$stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

if (!$fila || $fila['Nombre'] !== 'admin') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}

// El usuario es administrador, procede con el borrado del préstamo
if (isset($_GET["id"])) {
    $prestamo_id = $_GET["id"];

    $sql_borrar = "DELETE FROM prestamos WHERE ID = ?";
    $stmt_borrar = $conexion->prepare($sql_borrar);
    $stmt_borrar->bind_param("i", $prestamo_id);

    if ($stmt_borrar->execute()) {
        // Establece un mensaje de éxito en la sesión
        $_SESSION["mensaje_borrado"] = "Préstamo borrado exitosamente.";
        header("Location: crudPrestamos.php");
        exit();
    } else {
        echo "Error al borrar el préstamo.";
    }

    $stmt_borrar->close();
}
?>
