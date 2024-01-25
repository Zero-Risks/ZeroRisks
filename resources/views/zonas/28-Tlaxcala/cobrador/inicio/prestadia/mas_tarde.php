<?php
session_start();
date_default_timezone_set('America/Bogota');
require_once  '../../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado y tiene permisos


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['prestamoId'])) {
    $prestamoId = $_POST['prestamoId'];

    // Actualizar el estado del préstamo en la base de datos
    $query = "UPDATE prestamos SET mas_tarde = 1 WHERE ID = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $prestamoId);

    if ($stmt->execute()) {
        // La actualización fue exitosa
        echo json_encode(['success' => true]);
    } else {
        // Hubo un error al actualizar
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos']);
    }

    $stmt->close();
} else {
    // Solicitud no válida
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
}
?>
