<?php
session_start();
require_once("../../../../../../../controllers/conexion.php");

if (isset($_GET['cliente_id']) && isset($_GET['cartera_id'])) {
    $cliente_id = $_GET['cliente_id'];
    $cartera_id = $_GET['cartera_id'];

    // Verificar si el cartera_id existe en la tabla carteras
    $sql_verificar = "SELECT id FROM carteras WHERE id = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->bind_param("i", $cartera_id);
    $stmt_verificar->execute();
    $resultado_verificar = $stmt_verificar->get_result();

    if ($resultado_verificar->num_rows == 0) {
        echo "El ID de la cartera no existe.";
        exit();
    }

    $sql = "UPDATE clientes SET cartera_id = ? WHERE ID = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $cartera_id, $cliente_id);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Cliente agregado correctamente a la cartera.";
        header("Location: ../agregar_cliente.php?id=" . $cartera_id);
        exit();
    } else {
        echo "Error al asignar la cartera: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
} else {
    echo "Datos requeridos no proporcionados.";
}
?>
