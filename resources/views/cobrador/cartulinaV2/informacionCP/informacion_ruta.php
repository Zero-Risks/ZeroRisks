<?php

function obtenerClientesConPrestamoPendiente($usuario_id) {
    include("../../../../../controllers/conexion.php");

    // Consulta SQL para obtener clientes con préstamos pendientes
    // y que estén asociados con el usuario actual en la sesión
    $sql = "SELECT c.ID, c.Nombre, c.Apellido, p.ID AS PrestamoID
            FROM clientes c
            INNER JOIN prestamos p ON c.ID = p.IDCliente
            WHERE p.Estado = 'pendiente' AND c.IDUsuario = ?"; // Filtro por IDUsuario en la tabla clientes

    // Preparar la consulta
    if ($stmt = $conexion->prepare($sql)) {
        // Vincular el ID del usuario actual en la sesión
        $stmt->bind_param("i", $usuario_id);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $resultado = $stmt->get_result();

        // Verificar si se obtuvieron resultados
        if ($resultado->num_rows > 0) {
            $clientesConPrestamoPendiente = array();

            while ($fila = $resultado->fetch_assoc()) {
                $clientesConPrestamoPendiente[] = $fila;
            }

            // Cerrar el statement
            $stmt->close();

            // Cerrar la conexión
            $conexion->close();

            return $clientesConPrestamoPendiente;
        } else {
            // Cerrar el statement y la conexión
            $stmt->close();
            $conexion->close();

            return null; // No se encontraron clientes con préstamos pendientes
        }
    } else {
        echo "Error en la consulta: " . $conexion->error;
        $conexion->close();
        return null;
    }
}

?>
