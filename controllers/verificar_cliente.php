<?php
date_default_timezone_set('America/Bogota');
require_once("conexion.php"); // Incluye el archivo de conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar el CURP y teléfono del formulario
    $curp = $_POST["curp"];
    $telefono = $_POST["telefono"];
 
    // Consultar si ya existe un cliente con el mismo CURP o teléfono
    $sql_verificar = "SELECT ID, IdentificacionCURP, Telefono FROM clientes WHERE IdentificacionCURP = '$curp' OR Telefono = '$telefono'";
    $resultado_verificar = mysqli_query($conexion, $sql_verificar);

    if (mysqli_num_rows($resultado_verificar) > 0) {
        // Existe un cliente con el mismo CURP o teléfono
        $row = mysqli_fetch_assoc($resultado_verificar);
        $cliente_id = $row['ID'];
        $cliente_curp = $row['IdentificacionCURP'];
        $cliente_telefono = $row['Telefono'];

        // Determinar si la coincidencia es por CURP, teléfono o ambos
        $coincidencia_por = ($cliente_curp == $curp) ? "CURP" : "";
        $coincidencia_por .= ($cliente_telefono == $telefono) ? (($coincidencia_por != "") ? " y Teléfono" : "Teléfono") : "";

        // Devuelve la respuesta en formato JSON
        $respuesta = array(
            "existe" => true,
            "cliente_id" => $cliente_id,
            "coincidencia_por" => $coincidencia_por
        );

        echo json_encode($respuesta);
    } else {
        // No existe un cliente con ese CURP o teléfono
        $respuesta = array(
            "existe" => false
        ); 

        echo json_encode($respuesta);
    }
    // Cerrar la conexión
    mysqli_close($conexion);
}
?>
