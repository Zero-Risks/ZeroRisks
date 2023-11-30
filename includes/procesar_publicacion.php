<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
} else {
    echo "No se ha iniciado sesión.";
    exit(); // Terminar la ejecución si no hay sesión iniciada
}

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contenido = isset($_POST["contenido"]) ? $_POST["contenido"] : null;
    $fecha_de_publicacion = date("Y-m-d H:i:s");

    // Obtén el usuario_id de la sesión
    $usuario_id = $_SESSION['usuario_id'];

    $sql_check_user = "SELECT id FROM usuarios WHERE id = ?";
    $stmt_check_user = $conexion->prepare($sql_check_user);
    $stmt_check_user->bind_param('i', $usuario_id);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    if ($result_check_user->num_rows > 0) {
        try {
            $sql = "INSERT INTO publicaciones (usuario_id, contenido, fecha_de_publicacion) VALUES (?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param('iss', $usuario_id, $contenido, $fecha_de_publicacion);
            
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header("Location: ../views/admin/inicio.php");
            } else {
                echo "Hubo un error al crear la publicación.";
            }
        } catch(mysqli_sql_exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "El usuario no existe.";
    }
}
?>
