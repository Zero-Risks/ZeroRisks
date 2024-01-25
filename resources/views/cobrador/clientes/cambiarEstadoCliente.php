<?php
date_default_timezone_set('America/Bogota');
session_start();


include("../../../../controllers/conexion.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['estado'])) {
    $id = $_GET['id'];
    $estado = $_GET['estado'] == 1 ? 0 : 1; // Cambia el estado

    $sql = "UPDATE clientes SET Estado = $estado WHERE ID = $id";
    if ($conexion->query($sql) === TRUE) {
        header("Location: lista_clientes.php?mensaje=Estado cambiado con éxito");
    } else {
        header("Location: lista_clientes.php?mensaje=Error al cambiar el estado");
    }
} else {
    header("Location: lista_clientes.php");
}
?>
