<?php
session_start(); // Iniciar la sesión al principio del archivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../../../../../controllers/conexion.php';

    $usuario_id = $_POST["usuario_id"];
    $permisos = isset($_POST["permisos"]) ? $_POST["permisos"] : [];

    // Eliminar todos los permisos anteriores para el usuario
    $sql_delete = "DELETE FROM usuarios_permisos WHERE usuario_id = ?";
    $stmt_delete = $conexion->prepare($sql_delete);
    $stmt_delete->bind_param("i", $usuario_id);

    if (!$stmt_delete->execute()) {
        // Manejo del error
        $_SESSION['error_message'] = "Error al eliminar permisos anteriores: " . $stmt_delete->error;
        header("Location: permisos.php");
        exit();
    }

    // Insertar los nuevos permisos seleccionados
    foreach ($permisos as $permiso_id) {
        $sql_insert = "INSERT INTO usuarios_permisos (usuario_id, permiso_id) VALUES (?, ?)";
        $stmt_insert = $conexion->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $usuario_id, $permiso_id);

        if (!$stmt_insert->execute()) {
            // Manejo del error
            $_SESSION['error_message'] = "Error al asignar permisos: " . $stmt_insert->error;
            header("Location: permisos.php");
            exit();
        }
    }

    // Guardar mensaje de éxito en la sesión
    $_SESSION['message'] = "Permisos actualizados correctamente.";

    // Redirigir a la página de permisos
    header("Location: permisos.php");
    exit();
}
?>
