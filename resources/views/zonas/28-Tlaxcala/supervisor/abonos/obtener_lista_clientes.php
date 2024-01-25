<?php
session_start();
// Incluye el archivo de conexión a la base de datos
include '../../../../../../controllers/conexion.php'; // Asegúrate de que 'conexion.php' sea el archivo que contiene la conexión a la base de datos

// Realiza una consulta SQL para obtener los IDs de los clientes en la Zona 1
$sql = "SELECT ID FROM clientes WHERE ZonaAsignada  = 'Tlaxcala' AND Estado = 1"; // Selecciona solo los clientes de la Zona 1 que están activos

$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error al obtener la lista de clientes: " . mysqli_error($conexion));
}

$listaClientes = array();

while ($fila = mysqli_fetch_assoc($resultado)) {
    $listaClientes[] = $fila['ID'];
}

// Convierte la lista de clientes a formato JSON y envíala como respuesta
echo json_encode($listaClientes);

// Cierra la conexión a la base de datos
mysqli_close($conexion);
?>
