<?php
// Incluir conexión a la base de datos
include("../../../includes/conexion.php");

$publicacion_id = isset($_GET['id']) ? $_GET['id'] : die("Error: No se encontró el ID de la publicación.");

// Verificar si la solicitud es AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Insertar un nuevo comentario
if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_ajax) {
    $contenido_comentario = $_POST['contenido_comentario'];
    $usuario_id = 3; // Asegúrate de reemplazar esto con el ID del usuario actual en tu sesión

    $sql_insert = "INSERT INTO comentarios (usuario_id, publicacion_id, contenido) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql_insert);
    $stmt->bind_param("iis", $usuario_id, $publicacion_id, $contenido_comentario);
    $stmt->execute();

    // Devolver la respuesta para AJAX
    echo htmlspecialchars($contenido_comentario);
    exit; // Finalizar la ejecución del script
}

// Obtener comentarios existentes
$sql_select = "SELECT comentarios.*, usuarios.nombre FROM comentarios JOIN usuarios ON comentarios.usuario_id = usuarios.id WHERE publicacion_id = ?";
$stmt = $conexion->prepare($sql_select);
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();
$resultado = $stmt->get_result();

$comentarios = [];
while ($fila = $resultado->fetch_assoc()) {
    $comentarios[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comentarios</title>
    <link rel="stylesheet" href="comentarios.css">
</head>

<body>
    <h1>Comentarios</h1>

    <!-- Sección para mostrar comentarios existentes -->
    <div class="comentarios">
        <?php foreach ($comentarios as $comentario) : ?>
            <div class="comentario">
                <h5><?php echo htmlspecialchars($comentario['nombre']); ?></h5>
                <p><?php echo htmlspecialchars($comentario['contenido']); ?></p>

                <!-- Opciones para cada comentario -->
                <div class="opciones-comentario">
                    <a href="#" class="responder">Responder</a>
                    <a href="#" class="reaccionar">Reaccionar</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


    <!-- Formulario para agregar un nuevo comentario -->
    <form id="formComentario">
        <textarea name="contenido_comentario" required></textarea>
        <button type="submit">Publicar Comentario</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#formComentario').on('submit', function(e) {
                e.preventDefault();
                var datos = $(this).serialize() + "&publicacion_id=" + <?php echo $publicacion_id; ?>;
                $.ajax({
                    type: 'POST',
                    url: '', // La URL está vacía para enviar la solicitud al mismo archivo
                    data: datos,
                    success: function(response) {
                        $('.comentarios').prepend('<div class="comentario">' + response + '</div>');
                        $('#formComentario').trigger('reset');
                    }
                });
            });
        });
    </script>

</body>

</html>