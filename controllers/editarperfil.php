<?php
include '../includes/conexion.php'; // Asegúrate de que este archivo contiene la conexión a tu base de datos

session_start();
$usuario_id = $_SESSION['usuario_id']; // Asegúrate de que el ID del usuario esté almacenado en la sesión

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los valores del formulario
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo_electronico = $_POST['correo_electronico'];
    $fecha_de_nacimiento = $_POST['fecha_de_nacimiento'];
    $genero = $_POST['genero'];

    // Manejar la carga de la foto de perfil
    $foto_perfil = '';
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        // Crear una subcarpeta basada en el ID del usuario
        $carpeta_destino = "uploads/" . $usuario_id . "/";
        if (!file_exists($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }

        $nombre_archivo = basename($_FILES["foto_perfil"]["name"]);
        $ruta_archivo = $carpeta_destino . $nombre_archivo;
        $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

        // Verificar si el archivo es una imagen
        $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
        if($check !== false) {
            // Verificar tamaño y tipo del archivo
            if ($_FILES["foto_perfil"]["size"] > 500000 || !in_array($tipo_archivo, ['jpg', 'png', 'jpeg', 'gif'])) {
                echo "Archivo no permitido.";
                exit;
            }

            // Intentar cargar el archivo
            if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $ruta_archivo)) {
                $foto_perfil = $ruta_archivo; // Guardar la ruta completa
            } else {
                echo "Error al cargar tu archivo.";
                exit;
            }
        } else {
            echo "El archivo no es una imagen.";
            exit;
        }
    }

    // Preparar la consulta SQL para actualizar los datos del usuario
    $sql = "UPDATE usuarios SET 
            nombre = ?, 
            apellido = ?, 
            correo_electronico = ?, 
            fecha_de_nacimiento = ?, 
            genero = ?, 
            foto_perfil = ? 
            WHERE id = ?";

    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("ssssssi", $nombre, $apellido, $correo_electronico, $fecha_de_nacimiento, $genero, $foto_perfil, $usuario_id);

        if ($stmt->execute()) {
            // Redireccionar a la página de perfil o mostrar mensaje de éxito
            header("Location: perfil.php");
            exit;
        } else {
            echo "Error al actualizar el perfil: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
}

$conexion->close();

// Función para obtener la URL de la imagen
function obtenerUrlImagen($rutaRelativa) {
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $rutaRelativa;
}

// En otro lugar del código, cuando necesites la URL de la imagen
// $urlImagen = obtenerUrlImagen($foto_perfil);

?>
