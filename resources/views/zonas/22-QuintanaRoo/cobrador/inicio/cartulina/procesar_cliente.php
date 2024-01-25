
<!-- PASAR DE CLIENTE -->

<?php
// Verificar si se ha enviado el ID del cliente seleccionado
if(isset($_POST['cliente'])){
    // Obtener el ID del cliente seleccionado
    $cliente_id = $_POST['cliente'];

    // Redireccionar a perfil_abonos.php con el ID del cliente seleccionado
    header("Location: perfil_abonos.php?id=$cliente_id");
    exit(); // Asegúrate de detener la ejecución después de la redirección
} else {
    // Manejar el caso en que no se ha seleccionado ningún cliente
    echo "No se ha seleccionado ningún cliente.";
}
?>

 