
<!-- PROCESAO PARA GUARDAR RUTA -->

<?php
if (isset($_POST['orden'])) {
    $orden = $_POST['orden'];

    // Ruta al archivo donde se guardará el orden
    $rutaArchivo = 'orden_clientes.txt';

    // Sobrescribir el archivo con el nuevo orden
    file_put_contents($rutaArchivo, $orden);
}
?>
