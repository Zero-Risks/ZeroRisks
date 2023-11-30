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
    $usuario_id = isset($_POST["usuario_id"]) ? $_POST["usuario_id"] : null;
    $publicacion_original_id = isset($_POST["publicacion_original_id"]) ? $_POST["publicacion_original_id"] : null;
    $comentario = isset($_POST["comentario"]) ? $_POST["comentario"] : null;
    $fecha_de_compartir = date("Y-m-d H:i:s");

    $ruta_imagen = '';
    if (isset($_FILES['imagen']) && !empty($_FILES['imagen']['name'])) {
        $imagen_nombre = $_FILES['imagen']['name'];
        $imagen_temp = $_FILES['imagen']['tmp_name'];
        $ruta_imagen = 'ruta/donde/guardar/' . $imagen_nombre;
        move_uploaded_file($imagen_temp, $ruta_imagen);
    }

    $sql_check_user = "SELECT id FROM usuarios WHERE id = ?";
    $stmt_check_user = $conexion->prepare($sql_check_user);
    $stmt_check_user->bind_param('i', $usuario_id);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    if ($result_check_user->num_rows > 0) {
        try {
            $sql = "INSERT INTO publicaciones_compartidas (usuario_id, publicacion_original_id, comentario, fecha_de_compartir, imagen, contenido) VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conexion->prepare($sql);

            if (empty($ruta_imagen)) {
                $contenido = ''; // Asegúrate de obtener este valor si no hay imagen
                $stmt->bind_param('iissss', $usuario_id, $publicacion_original_id, $comentario, $fecha_de_compartir, $ruta_imagen, $contenido);
            } else {
                $stmt->bind_param('iissss', $usuario_id, $publicacion_original_id, $comentario, $fecha_de_compartir, $ruta_imagen, $contenido);
            }
            
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "La publicación compartida se ha creado correctamente.";
            } else {
                echo "Hubo un error al crear la publicación compartida.";
            }
        } catch(mysqli_sql_exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "El usuario no existe.";
    }
}
?>
