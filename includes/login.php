<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    try {
        $consulta = $conexion->prepare("SELECT usuarios.id, usuarios.contraseña, roles.nombre AS rol FROM usuarios INNER JOIN roles ON usuarios.rol_id = roles.id WHERE correo = ?");
        $consulta->execute([$correo]);
        $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirección basada en el rol
            switch ($_SESSION['rol']) {
                case 'Administrador':
                    header('Location: ../views/admin/inicio.html');
                    exit();
                case 'Moderador':
                    header('Location: moderator_dashboard.php');
                    exit();
                case 'Usuario Estándar':
                    header('Location: user_dashboard.php');
                    exit();
                // Añadir más casos según sea necesario
                default:
                    header('Location: index.php'); // Página de inicio o alguna otra página por defecto
                    exit();
            }
        } else {
            echo "Correo o contraseña incorrectos.";
        }
    } catch(PDOException $e) {
        echo "Error de inicio de sesión: " . $e->getMessage();
    }
}
?>
