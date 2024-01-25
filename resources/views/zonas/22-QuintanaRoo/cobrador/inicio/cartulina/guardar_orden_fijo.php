
<!-- GUARDAR ORDEN FIJO -->

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orden'])) {
    $orden = $_POST['orden'];
    file_put_contents('orden_fijo.txt', $orden);
}
?>
