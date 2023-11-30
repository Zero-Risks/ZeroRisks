<?php
include '../../../includes/conexion.php'; // Asegúrate de que este archivo contiene la conexión a tu base de datos

session_start();
$usuario_id = $_SESSION['usuario_id']; // Asegúrate de que el ID del usuario esté almacenado en la sesión

// Obtener los datos del usuario de la base de datos
$sql = "SELECT nombre, apellido, correo_electronico, fecha_de_nacimiento, genero, foto_perfil FROM usuarios WHERE id = ?";
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($nombre, $apellido, $correo_electronico, $fecha_de_nacimiento, $genero, $foto_perfil);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Error al preparar la consulta: " . $conexion->error;
}

$conexion->close();

// Función para obtener la URL de la imagen
function obtenerUrlImagen($rutaRelativa) {
    $rutaBase = '../../../controllers/'; // ruta de la imagenes
    return $rutaBase . $rutaRelativa;
}

// Obtener la URL de la imagen
$urlImagen = obtenerUrlImagen($foto_perfil);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="/assets/css/admin/Inicioperfil.css" />
</head>
<body>
    <div class="perfil-container">
        <div class="foto-perfil">
            <img src="<?php echo htmlspecialchars($urlImagen); ?>" alt="Foto de perfil" />
        </div>
        <div class="informacion-usuario">
            <h2 id="nombre-usuario"><?php echo htmlspecialchars($nombre . ' ' . $apellido); ?></h2>
            <p>
                <strong>Email:</strong>
                <span id="email-usuario"><?php echo htmlspecialchars($correo_electronico); ?></span>
            </p>
            <p>
                <strong>Cumpleaños:</strong>
                <span id="cumpleanos-usuario"><?php echo htmlspecialchars($fecha_de_nacimiento); ?></span>
            </p>
            <p>
                <strong>Género:</strong>
                <span id="genero-usuario"><?php echo htmlspecialchars($genero); ?></span>
            </p>
            <!-- Botón de Edición de Perfil -->
            <button id="editar-perfil" onclick="editarPerfil()">Editar Perfil</button>
            <!-- Más información del usuario aquí -->
        </div>
        <div class="publicaciones">
            <h3>Publicaciones Recientes</h3>
            <!-- Lista de publicaciones aquí -->
        </div>
    </div>

    <script src="/assets/js/admin/perfil.js"></script>
</body>
</html>
