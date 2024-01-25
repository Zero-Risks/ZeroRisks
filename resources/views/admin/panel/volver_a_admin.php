<?php
session_start();

if (isset($_SESSION["original_user_id"], $_SESSION["original_rol"], $_SESSION["sesion_temporal"], $_SESSION["ruta_retorno"]) && $_SESSION["sesion_temporal"] === true) {
    // Restablecer la sesión original
    $_SESSION["usuario_id"] = $_SESSION["original_user_id"];
    $_SESSION["rol"] = $_SESSION["original_rol"];

    // Redireccionar al dashboard original
    $ruta_retorno = $_SESSION["ruta_retorno"];
    header("Location: $ruta_retorno");

    // Limpiar variables de sesión temporal
    unset($_SESSION["original_user_id"]);
    unset($_SESSION["original_rol"]);
    unset($_SESSION["original_rol_nombre"]);
    unset($_SESSION["ruta_retorno"]);
    unset($_SESSION["sesion_temporal"]);
    exit();
} else {
    header("Location: /error_o_inicio.php");
    exit();
}
