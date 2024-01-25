<?php
// guardar_orden.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../../../index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orden'])) {
    $orden = $_POST['orden'];
    $usuarioId = $_SESSION['usuario_id'];
    
    // Nombre del archivo basado en el ID del usuario
    $nombreArchivo = "ruta_" . $usuarioId . ".txt";
    $rutaArchivo = __DIR__ . '/' . $nombreArchivo;

    file_put_contents($rutaArchivo, $orden);
}

?>
