<?php
date_default_timezone_set('America/Bogota');
require_once  '../../../../../../../controllers/conexion.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['prestamoId'])) {
    $prestamoId = $_POST['prestamoId'];

    // Modificación aquí: actualizar también el campo correspondiente a "Más tarde"
    $sql = "UPDATE prestamos SET Pospuesto = 1, mas_tarde = 0 WHERE ID = ?";
    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $prestamoId);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Préstamo actualizado a No Pagado y Más Tarde reseteado"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar el préstamo"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error en la preparación de la consulta"]);
    }

    $conexion->close();
} else {
    echo json_encode(["success" => false, "message" => "Datos POST necesarios no recibidos."]);
}
?>
