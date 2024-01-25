<?php
date_default_timezone_set('America/Bogota');
// Incluir el archivo de conexión a la base de datos
include 'conexion.php';

// Obtener el MonedaID de la solicitud
$moneda_id = $_GET['moneda_id'];

// Consulta SQL para obtener el símbolo de la moneda
$sql_moneda = "SELECT Simbolo FROM monedas WHERE ID = $moneda_id";
$resultado_moneda = $conexion->query($sql_moneda);

if ($resultado_moneda->num_rows > 0) {
    $fila_moneda = $resultado_moneda->fetch_assoc();
    $simbolo_moneda = $fila_moneda['Simbolo'];
    echo $simbolo_moneda;
} else {
    echo ''; // Manejar una situación sin resultados como desees
}

// Cerrar la conexión a la base de datos
$conexion->close();
?>
