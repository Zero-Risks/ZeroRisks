<?php
session_start(); // Iniciar la sesión (si no se ha iniciado)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir el archivo de conexión a la base de datos
    include("conexion.php");

    // Recuperar datos del formulario
    $correo = $_POST["correo"];
    $contrasena = $_POST["contrasena"];

    // Consulta SQL para buscar el usuario por correo electrónico
    $sql = "SELECT * FROM usuarios WHERE correo_electronico = '$correo'";

    $resultado = $conexion->query($sql);

    if ($resultado->num_rows == 1) {
        // El usuario existe, verificar la contraseña
        $fila = $resultado->fetch_assoc();
        if (password_verify($contrasena, $fila["contrasena"])) {
            // Contraseña válida, iniciar sesión
            $_SESSION["usuario_id"] = $fila["id"];
            $_SESSION["usuario_nombre"] = $fila["nombre"];
            header("Location: ../views/admin/inicio.html"); // Redirigir a la página de inicio después del inicio de sesión
        } else {
            // Contraseña incorrecta
            echo "Contraseña incorrecta. Inténtalo de nuevo.";
        }
    } else {
        // El usuario no existe
        echo "El usuario con ese correo electrónico no existe.";
    }

    // Cerrar la conexión a la base de datos
    $conexion->close();
}
?>
