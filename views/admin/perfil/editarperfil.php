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
    $rutaBase = '../../../controllers/'; // ruta de las imágenes
    return $rutaBase . $rutaRelativa;
}

// Obtener la URL de la imagen
$urlImagen = obtenerUrlImagen($foto_perfil);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="/assets/css/admin/Inicioperfil.css">
</head>
<body>
    <div class="perfil-container">
        <form action="/controllers/editarperfil.php" method="post" enctype="multipart/form-data">
            <div class="foto-perfil">
                <!-- Mostrar la imagen actual del perfil -->
                <?php if ($foto_perfil): ?>
                    <img src="<?php echo $urlImagen; ?>" alt="Foto de perfil" style="max-width: 150px; max-height: 150px;">
                <?php endif; ?>
                <!-- Cargar nueva imagen -->
                <label for="foto_perfil">Cambiar foto de perfil:</label>
                <input type="file" name="foto_perfil" id="foto_perfil">
            </div>

            <div class="informacion-usuario">
                <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>">

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" value="<?php echo $apellido; ?>">

                <label for="correo_electronico">Correo Electrónico:</label>
                <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo $correo_electronico; ?>">

                <label for="fecha_de_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_de_nacimiento" name="fecha_de_nacimiento" value="<?php echo $fecha_de_nacimiento; ?>">

                <label for="genero">Género:</label>
                <select id="genero" name="genero">
                    <option value="Masculino" <?php echo $genero == 'Masculino' ? 'selected' : ''; ?>>Masculino</option>
                    <option value="Femenino" <?php echo $genero == 'Femenino' ? 'selected' : ''; ?>>Femenino</option>
                    <!-- Agregar más opciones de género si es necesario -->
                </select>

                <button type="submit" class="btn-actualizar">Actualizar Perfil</button>
            </div>
        </form>
    </div>
</body>
</html>
