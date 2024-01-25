<?php
date_default_timezone_set('America/Bogota');
require_once '../../../../controllers/conexion.php';



// Verifica si se ha enviado el IDZona
if (isset($_POST['IDZona'])) {
    $IDZona = $_POST['IDZona'];

    // Prepara la consulta SQL para obtener las ciudades de la zona especificada
    $query = "SELECT ID, Nombre FROM ciudades WHERE IDZona = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $IDZona);
    $stmt->execute();
    $result = $stmt->get_result();

    $cities = array();
    while ($row = $result->fetch_assoc()) {
        // Agrega cada ciudad al array
        $cities[] = array("id" => $row['ID'], "nombre" => $row['Nombre']);
    }

    // Devuelve el array de ciudades en formato JSON
    echo json_encode($cities);
} else {
    // Si no se proporciona IDZona, devuelve un error
    echo json_encode(array("error" => "No se proporcionó IDZona"));
}

$conexion->close();
?>
