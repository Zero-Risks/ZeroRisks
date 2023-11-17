<?php
$hostname = "localhost"; // Nombre del servidor de la base de datos
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$database = "redsocial1"; // Nombre de la base de datos


$conexion = new mysqli($hostname, $username, $password, $database);

// Verificar si la conexión fue exitosa
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}


$conexion->set_charset("utf8");




?>
