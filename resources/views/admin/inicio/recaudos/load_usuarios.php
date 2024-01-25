<?php
require_once '../../../../../controllers/conexion.php';

$sql = "SELECT ID, Nombre FROM usuarios ORDER BY Nombre";
$stmt = $conexion->prepare($sql);
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
