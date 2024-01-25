<?php
session_start();
date_default_timezone_set('America/Bogota');
require_once("conexion.php");
// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar el ID del usuario autenticado
    $id_usuario = $_SESSION["usuario_id"];

    // Recuperar datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $curp = $_POST["curp"];
    $domicilio = $_POST["domicilio"];
    $telefono = $_POST["telefono"];
    $historial = $_POST["historial"];
    $referencias = $_POST["referencias"];
    $moneda = $_POST["moneda"];
    $zona_id = $_POST["zona"]; // Recuperar el ID de la zona desde el formulario
    $ciudad = $_POST["ciudad"]; // Recuperar la ciudad desde el formulario
    $asentamiento = $_POST["asentamiento"]; // Recuperar el asentamiento desde el formulario

    // Procesar la imagen del cliente
    if ($_FILES["imagen"]["error"] === 0) {
        $imagen_nombre = $_FILES["imagen"]["name"];
        $imagen_temporal = $_FILES["imagen"]["tmp_name"];
        $ruta_imagen = "../public/assets/img/imgclient/imgclient" . $imagen_nombre;
        move_uploaded_file($imagen_temporal, $ruta_imagen);
    } else {
        $ruta_imagen = "";
    }

    // Consulta SQL para obtener el nombre de la zona
    $consultaZona = "SELECT Nombre FROM zonas WHERE ID = $zona_id";
    $resultZona = mysqli_query($conexion, $consultaZona);

    if ($rowZona = mysqli_fetch_assoc($resultZona)) {
        $nombre_zona = $rowZona['Nombre'];

        // Insertar los datos en la tabla de clientes, incluyendo el ID del usuario
        $sql = "INSERT INTO clientes (IDUsuario, Nombre, Apellido, IdentificacionCURP, Domicilio, Telefono, HistorialCrediticio, ReferenciasPersonales, MonedaPreferida, ZonaAsignada, Ciudad, Asentamiento, ImagenCliente)
                VALUES ('$id_usuario', '$nombre', '$apellido', '$curp', '$domicilio', '$telefono', '$historial', '$referencias', '$moneda', '$nombre_zona', '$ciudad', '$asentamiento', '$ruta_imagen')";

        if (mysqli_query($conexion, $sql)) {
            $ultimo_id_cliente = mysqli_insert_id($conexion);
            header('Location: ../resources/views/supervisor/desatrasar/hacerPrestamo.php?clienteId=' . $ultimo_id_cliente);
            exit();
        } else {
            echo "Error al registrar el cliente: " . mysqli_error($conexion);
        }
    } else {
        echo "Error al obtener el nombre de la zona.";
    }
    // Cerrar la conexión
    mysqli_close($conexion);
}
