<?php
// Incluir el archivo de conexión a la base de datos
include("../../../controllers/conexion.php");

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asignar variables a los datos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $domicilio = mysqli_real_escape_string($conexion, $_POST['domicilio']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $historial = mysqli_real_escape_string($conexion, $_POST['historial']);
    $referencias = mysqli_real_escape_string($conexion, $_POST['referencias']);
    $moneda = mysqli_real_escape_string($conexion, $_POST['moneda']);
    $idZona = mysqli_real_escape_string($conexion, $_POST['zona']); 
    $curp = mysqli_real_escape_string($conexion, $_POST['curp']);
    $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
    $asentamiento = mysqli_real_escape_string($conexion, $_POST['asentamiento']);
    // Aquí puedes agregar la lógica para manejar la imagen del cliente

    // Validar si el ID de la zona existe en la base de datos
    $queryZona = "SELECT Nombre FROM zonas WHERE ID = '$idZona'";
    $resultZona = mysqli_query($conexion, $queryZona);
    if(mysqli_num_rows($resultZona) == 0){
        die("Error: El ID de la zona asignada no existe en la base de datos.");
    }

    // Obtener el nombre de la zona basado en el ID
    $rowZona = mysqli_fetch_assoc($resultZona);
    $nombreZona = $rowZona['Nombre'];

    // Crear la consulta SQL para insertar los datos
    $sql = "INSERT INTO clientes (Nombre, Apellido, Domicilio, Telefono, HistorialCrediticio, ReferenciasPersonales, MonedaPreferida, ZonaAsignada, IdentificacionCURP, ciudad, asentamiento) VALUES ('$nombre', '$apellido', '$domicilio', '$telefono', '$historial', '$referencias', '$moneda', '$nombreZona', '$curp', '$ciudad', '$asentamiento')";

    // Ejecutar la consulta
    if (mysqli_query($conexion, $sql)) {
        header('Location: ../../../resources/views/zonas/28-Tlaxcala/cobrador/clientes/lista_clientes.php?mensaje=Cliente guardado exitosamente');
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conexion);
    }

    // Cerrar la conexión
    mysqli_close($conexion);
}
?>
