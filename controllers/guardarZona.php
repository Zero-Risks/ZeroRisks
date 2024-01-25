<?php
date_default_timezone_set('America/Bogota');
// Incluye el archivo de conexión a la base de datos
include('conexion.php');

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$capital = $_POST['capital'];
$codigo_postal = $_POST['codigo_postal'];

// Crear la consulta SQL para insertar el nuevo registro en la tabla "zonas"
$sql = "INSERT INTO zonas (Nombre, Capital, CodigoPostal)
        VALUES ('$nombre', '$capital', '$codigo_postal')";

if ($conexion->query($sql) === TRUE) {
    // Redirige al usuario a la página de agregar zona con un mensaje de confirmación
    header('Location: ../resources/views/admin/cobros/agregar_cobro.php?mensaje=Zona guardada exitosamente');
    exit;
} else {
    echo "Error al agregar el registro: " . $conexion->error;
}

// Cerrar la conexión a la base de datos
$conexion->close();
?>
