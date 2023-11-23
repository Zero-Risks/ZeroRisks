<?php
include '../includes/conexion.php'; // Asegúrate de que este archivo contiene la conexión a tu base de datos

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los valores del formulario
    $usuario_id = $_POST['usuario_id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo_electronico = $_POST['correo_electronico'];
    $fecha_de_nacimiento = $_POST['fecha_de_nacimiento'];
    $genero = $_POST['genero'];

    // Manejar la carga de la foto de perfil
    $foto_perfil = '';
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        $carpeta_destino = "uploads/"; // Asegúrate de que esta carpeta existe y tiene permisos de escritura
        $nombre_archivo = basename($_FILES["foto_perfil"]["name"]);
        $ruta_archivo = $carpeta_destino . $nombre_archivo;
        $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));

        // Verificar si el archivo es una imagen
        $check = getimagesize($_FILES["foto_perfil"]["tmp_name"]);
        if($check !== false) {
            // Verificar tamaño del archivo
            if ($_FILES["foto_perfil"]["size"] > 500000) { // 500 KB como límite
                echo "El archivo es demasiado grande.";
                exit;
            }

            // Permitir ciertos formatos de archivo
            if($tipo_archivo != "jpg" && $tipo_archivo != "png" && $tipo_archivo != "jpeg" && $tipo_archivo != "gif" ) {
                echo "Solo se permiten archivos JPG, JPEG, PNG y GIF.";
                exit;
            }

            // Intentar cargar el archivo
            if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $ruta_archivo)) {
                $foto_perfil = $ruta_archivo;
            } else {
                echo "Hubo un error al cargar tu archivo.";
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

    // Preparar y ejecutar la consulta
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("ssssssi", $nombre, $apellido, $correo_electronico, $fecha_de_nacimiento, $genero, $foto_perfil, $usuario_id);

        if ($stmt->execute()) {
            echo "Perfil actualizado correctamente";
        } else {
            echo "Error al actualizar el perfil: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
}

$conexion->close();
?>
