<?php
require_once '../../../../controllers/conexion.php';

if (isset($_GET['usuarioId'])) {
    $usuarioId = $_GET['usuarioId'];

    // Consulta para obtener los clientes asociados con el usuario
    $consultaClientes = "SELECT * FROM clientes WHERE IDUsuario = ?";
    $stmt = $conexion->prepare($consultaClientes);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $clientes = [];
    
    while ($cliente = $resultado->fetch_assoc()) {
        $clientes[] = $cliente;
    }

    echo json_encode($clientes);
    $conexion->close();
    exit;
}
