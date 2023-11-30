<?php
// Conexión a la base de datos (incluye tu archivo de conexión)
include("../../includes/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_POST["usuario_id"];
    $publicacion_id = $_POST["publicacion_id"];
    $tipo_reaccion = $_POST["tipo_reaccion"];
    $fecha_reaccion = date("Y-m-d H:i:s");

    // Verifica si el usuario ha reaccionado previamente a la publicación
    $sql_check_reaction = "SELECT * FROM me_gusta WHERE usuario_id = ? AND publicacion_id = ?";
    $stmt_check_reaction = $conexion->prepare($sql_check_reaction);
    $stmt_check_reaction->bind_param('ii', $usuario_id, $publicacion_id);
    $stmt_check_reaction->execute();
    $result_check_reaction = $stmt_check_reaction->get_result();

    if ($result_check_reaction->num_rows === 0) {
        // Si el usuario no ha reaccionado previamente, inserta la nueva reacción
        $sql_insert_reaction = "INSERT INTO me_gusta (usuario_id, publicacion_id, tipo_reaccion, fecha_reaccion) VALUES (?, ?, ?, ?)";
        $stmt_insert_reaction = $conexion->prepare($sql_insert_reaction);
        $stmt_insert_reaction->bind_param('iiss', $usuario_id, $publicacion_id, $tipo_reaccion, $fecha_reaccion);

        if ($stmt_insert_reaction->execute()) {
            echo "Reacción guardada correctamente.";
        } else {
            echo "Error al guardar la reacción.";
        }
    } else {
        echo "El usuario ya ha reaccionado a esta publicación.";
    }
} else {
    echo "Solicitud no válida.";
}
?>
