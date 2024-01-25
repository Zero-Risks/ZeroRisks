<?php
session_start();

function obtenerClientesConPrestamoPendiente() { 

    include("../../../../../../../controllers/conexion.php");

    $usuario_id = $_SESSION["usuario_id"];

    // Consulta SQL para obtener clientes con préstamos pendientes asignados al usuario actual
    $sql = "SELECT c.ID, c.Nombre, c.Apellido, p.ID AS PrestamoID
            FROM clientes c
            LEFT JOIN prestamos p ON c.ID = p.IDCliente
            WHERE p.Estado = 'pendiente' AND c.IDUsuario = ?";

    // Preparar la consulta
    if ($stmt = $conexion->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $usuario_id); // Asumiendo que 'usuario_id' es un entero

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener los resultados
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $clientesConPrestamoPendiente = array();

            while ($fila = $resultado->fetch_assoc()) {
                $clientesConPrestamoPendiente[] = $fila;
            }

            // Cerrar el statement
            $stmt->close();
        } else {
            $clientesConPrestamoPendiente = null; // No se encontraron clientes con préstamos pendientes
        }

        // Cerrar la conexión
        $conexion->close();

        return $clientesConPrestamoPendiente;
    } else {
        // Manejar el error de preparación de la consulta
        $error = $conexion->error;
        $conexion->close();
        return "Error preparando la consulta: " . $error;
    }
}
?>
