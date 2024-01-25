<?php
session_start();
require_once '../../../../../../controllers/conexion.php';

// Verificar si el usuario es supervisor y si se ha enviado el ID del cobrador
if ($_SESSION["rol"] == 2 && isset($_POST['cobrador_id'])) {
    $cobradorID = $_POST['cobrador_id']; // ID del cobrador seleccionado

    // Guardar el ID original del supervisor y el rol en la sesión
    $_SESSION["original_user_id"] = $_SESSION["usuario_id"];
    $_SESSION["original_rol"] = $_SESSION["rol"];

    // Indicar que la sesión es temporal
    $_SESSION["sesion_temporal"] = true;

    // Cambiar a la sesión del cobrador
    $_SESSION["usuario_id"] = $cobradorID;
    $_SESSION["rol"] = 3; // Asumiendo que 3 es el ID de rol para cobrador

    // Redireccionar al dashboard del cobrador
    header("Location: ../../cobrador/inicio/inicio.php");
    exit();
} else {
    // Redirigir a una página de error o inicio si no es supervisor o no se proporciona el ID del cobrador
    header("Location: /error_o_inicio.php");
    exit();
}
