<?php
include("../includes/conexion.php");

    // Recuperar datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $correo = $_POST["correo"];
    $contrasena = password_hash($_POST["contrasena"], PASSWORD_DEFAULT); // Hash de la contraseña
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $genero = $_POST["genero"]; // Agregar el campo "género"

    // Preparar la consulta SQL
    $sql = "INSERT INTO usuarios (nombre, apellido, correo_electronico, contrasena, fecha_de_registro, fecha_de_nacimiento, genero) 
            VALUES ('$nombre', '$apellido', '$correo', '$contrasena', NOW(), '$fecha_nacimiento', '$genero')";

    // Ejecutar la consulta
    if ($conexion->query($sql) === TRUE) {
        header("Location: ../index.html");
    } else {
        echo "Error al registrar el usuario: " . $conexion->error;
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();

?>
