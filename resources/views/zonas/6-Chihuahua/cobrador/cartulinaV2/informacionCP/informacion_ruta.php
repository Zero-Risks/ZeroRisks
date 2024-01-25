<?php
session_start();

function obtenerClientesConPrestamoPendiente() {
    // Asumiendo que 'conexion.php' contiene una instancia de mysqli llamada $conexion
    include("../../../../../../../controllers/conexion.php");

    $usuario_id = $_SESSION["usuario_id"];

    // Consulta SQL para obtener clientes con préstamos pendientes
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

        $clientesConPrestamoPendiente = array();
        if ($resultado->num_rows > 0) {
            // Recorrer y almacenar los resultados
            while ($fila = $resultado->fetch_assoc()) {
                $clientesConPrestamoPendiente[] = $fila;
            }
        }

        // Cerrar el statement
        $stmt->close();

        // Si no hay clientes, retorna null o un array vacío según la lógica que prefieras.
        return $clientesConPrestamoPendiente;
    } else {
        // Manejar el error de preparación de la consulta
        $error = $conexion->error;
        $conexion->close();
        die("Error preparando la consulta: " . $error);
    }
}

// Uso de la función
$clientes = obtenerClientesConPrestamoPendiente();
?>
