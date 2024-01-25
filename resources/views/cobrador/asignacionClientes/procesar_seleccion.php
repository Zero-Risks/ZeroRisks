<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../../../index.php');
    exit;
}

require_once '../../../../controllers/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuarioPara = $_POST['usuarioPara'];
    $clientesSeleccionados = $_POST['clientes_seleccionados'] ?? [];

    // Verifica si se recibió un usuario 'Para' y hay clientes seleccionados
    if ($usuarioPara && count($clientesSeleccionados) > 0) {
        foreach ($clientesSeleccionados as $clienteId) {
            $stmt = $conexion->prepare("UPDATE clientes SET IDUsuario = ? WHERE ID = ?");
            $stmt->bind_param("ii", $usuarioPara, $clienteId);
            if (!$stmt->execute()) {
                // Manejo del error
                error_log("Error en la actualización: " . $stmt->error);
                header('Location: error5.php');
                exit;
            }
            $stmt->close();
        }
        header('Location: cambio_cliente.php');
    } else {
        // Redirige a una página de error si no hay usuario 'Para' o clientes seleccionados
        header('Location: error2.php');
    }
    exit;
}

$conexion->close();
?>
