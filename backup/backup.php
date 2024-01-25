<?php
// Configuración de la base de datos
$servername = "localhost"; // Nombre del servidor
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña del usuario de la base de datos
$dbname = "prestamos"; // Nombre de la base de datos

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Nombre del archivo de respaldo
$backup_file = 'backup-' . date("Y-m-d-H-i-s") . '.sql';

// Comando SQL para respaldar la base de datos
$command = "mysqldump --opt -h {$servername} -u {$username} -p{$password} {$dbname} > {$backup_file}";

// Ejecutar el comando para crear el respaldo
system($command, $output);

// Verificar si el respaldo se creó exitosamente
if ($output === 0) {
    echo "Copia de seguridad creada correctamente en el archivo: " . $backup_file;
} else {
    echo "Error al crear la copia de seguridad.";
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
