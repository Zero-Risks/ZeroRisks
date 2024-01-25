<?php
// Incluir el archivo de navegación si es necesario
include_once 'navegacion_cliente.php';

function obtenerPosicionYTotalClientes($idClienteActual, $conexion) {
    // Utiliza la función del archivo de navegación para obtener todos los IDs
    $idsClientesRuta = explode(',', file_get_contents(__DIR__ . '/ruta.txt'));

    // Filtrar solo los clientes con préstamos pendientes
    $clientesPendientes = array_filter($idsClientesRuta, function ($idCliente) use ($conexion) {
        $sql = "SELECT c.ID FROM clientes c INNER JOIN prestamos p ON c.ID = p.IDCliente WHERE c.ID = ? AND p.Estado = 'pendiente'";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();
        return $existe;
    });

    // Contar el total de clientes pendientes
    $totalClientes = count($clientesPendientes);

    // Determinar la posición actual del cliente en la lista
    $posicionActual = array_search($idClienteActual, array_values($clientesPendientes)) + 1; // +1 para ajustar el índice base 0

    return ['posicion' => $posicionActual, 'total' => $totalClientes];
}
?>
