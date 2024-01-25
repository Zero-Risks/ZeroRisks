<?php
include("../../../../controllers/conexion.php");
session_start();

$supervisor_id = $_SESSION["usuario_id"];

// Modificar la consulta para filtrar por el supervisor actual
$sql = "SELECT ID, CONCAT(Nombre, ' ', Apellido) AS NombreCompleto FROM usuarios WHERE SupervisorID = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $supervisor_id); // Asegúrate de que el tipo de dato coincida con el de la columna
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
