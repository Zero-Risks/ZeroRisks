<?php
include("../../../../../../controllers/conexion.php");

// Zona específica preestablecida
$zonaEspecifica = 22; 

// Modificar la consulta para filtrar por la zona específica
$sql = "SELECT ID, Nombre FROM usuarios WHERE Zona = ? ORDER BY Nombre";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $zonaEspecifica); // Asegúrate de que el tipo de dato coincida con el de la columna
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        array_push($usuarios, $row);
    }
}
echo json_encode($usuarios);

$conexion->close();
?>
