<?php
session_start();

// Asegúrate de que el usuario está autenticado y tiene permiso para eliminar gastos
// Implementa aquí cualquier lógica de control de acceso que necesites

require_once "../../../../../controllers/conexion.php";

if (isset($_GET['id'])) {
    $idGasto = $_GET['id'];

    // Preparar la consulta SQL para eliminar el gasto
    $sql = "DELETE FROM gastos WHERE ID = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $idGasto);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir a la página de lista de gastos o a donde desees
            header("Location: ../lista/lista_gastos.php");
            exit();
        } else {
            echo "Error al eliminar el gasto.";
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
} else {
    echo "ID de gasto no proporcionado.";
}

$conexion->close();
?>
