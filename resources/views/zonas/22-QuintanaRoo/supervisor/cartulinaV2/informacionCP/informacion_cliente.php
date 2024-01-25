<?php
// informacion_prestamos.php 

function obtenerInformacionCliente($conexion, $id_cliente) {
    $sql = "SELECT 
                clientes.ID,
                clientes.Nombre,
                clientes.Apellido,
                clientes.Domicilio,
                clientes.Telefono,
                clientes.MonedaPreferida,
                clientes.ZonaAsignada,
                clientes.IdentificacionCURP,
                ciudades.Nombre AS NombreCiudad,
                clientes.asentamiento,
                clientes.IDUsuario
            FROM clientes
            LEFT JOIN ciudades ON clientes.ciudad = ciudades.ID
            WHERE clientes.ID = ?";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        return $fila;
    } else {
        // Manejar el caso en que el cliente no se encuentre
        return null;
    }
}
