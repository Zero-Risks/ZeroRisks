<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];
    $telefono = $_POST['telefono'] ?? null;
    $biografia = $_POST['biografia'] ?? null;
    $rol_id = 1; // ID del rol de "Usuario Estándar"

    try {
        $consulta = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contraseña, fecha_nacimiento, genero, telefono, biografia, rol_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $consulta->execute([$nombre, $correo, $contraseña, $fecha_nacimiento, $genero, $telefono, $biografia, $rol_id]);
        echo "Usuario registrado con éxito.";
    } catch(PDOException $e) {
        echo "Error de registro: " . $e->getMessage();
    }
}
?>
