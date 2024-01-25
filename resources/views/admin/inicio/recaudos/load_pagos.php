<?php
require_once '../../../../../controllers/conexion.php';

$fechaDesde = isset($_GET["fechaDesde"]) && !empty($_GET["fechaDesde"]) ? $_GET["fechaDesde"] : null;
$fechaHasta = isset($_GET["fechaHasta"]) && !empty($_GET["fechaHasta"]) ? $_GET["fechaHasta"] : null;
$usuario = isset($_GET["usuario"]) && !empty($_GET["usuario"]) ? $_GET["usuario"] : null;

$sqlBase = "SELECT hp.*, CONCAT(c.Nombre, ' ', c.Apellido) AS NombreCompletoCliente, c.IdentificacionCURP AS CURPCliente, u.Nombre AS NombreUsuario
            FROM historial_pagos hp
            LEFT JOIN clientes c ON hp.IDCliente = c.ID
            LEFT JOIN usuarios u ON hp.IDUsuario = u.ID";

$conditions = [];
$params = [];
$types = "";

if ($fechaDesde) {
    $conditions[] = "hp.FechaPago >= ?";
    $params[] = $fechaDesde;
    $types .= "s";
}

if ($fechaHasta) {
    $conditions[] = "hp.FechaPago <= ?";
    $params[] = $fechaHasta;
    $types .= "s";
}

if ($usuario) {
    $conditions[] = "u.ID = ?";
    $params[] = $usuario;
    $types .= "s";
}

if (count($conditions) > 0) {
    $sql = $sqlBase . " WHERE " . implode(" AND ", $conditions);
} else {
    $sql = $sqlBase;
}

$stmt = $conexion->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$totalPrestamos = 0;
$data = "";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalPrestamos += $row["MontoPagado"];
        $data .= "<tr>";
        $data .= "<td>" . $row["NombreCompletoCliente"] . "</td>";
        $data .= "<td>" . $row["CURPCliente"] . "</td>";
        $data .= "<td>" . $row["FechaPago"] . "</td>";
        $data .= "<td>" . $row["MontoPagado"] . "</td>";
        $data .= "<td>" . $row["NombreUsuario"] . "</td>";
        $data .= "</tr>";
    }
} else {
    $data .= "<tr><td colspan='5'>No se encontraron pagos</td></tr>";
}

$response = ["total" => $totalPrestamos, "data" => $data];
echo json_encode($response);

$conexion->close();
?>
