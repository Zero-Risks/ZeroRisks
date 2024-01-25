<?php
// ruta_a_script_para_volver_a_supervisor.php

session_start();

if (isset($_SESSION["original_user_id"])) {
    // Restablecer la sesión del supervisor
    $_SESSION["usuario_id"] = $_SESSION["original_user_id"];
    $_SESSION["rol"] = $_SESSION["original_rol"];

    // Eliminar las variables de sesión temporales
    unset($_SESSION["original_user_id"]);
    unset($_SESSION["original_rol"]);
    unset($_SESSION["sesion_temporal"]);

    // Redireccionar al dashboard del supervisor
    header("Location: ../inicio/inicio.php");
    exit();
} else {
    // Redirigir a una página de error o inicio si no hay sesión original
    header("Location: /error_o_inicio.php");
    exit();
}
