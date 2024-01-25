<?php
date_default_timezone_set('America/Bogota');
session_start();

// Destruir todas las variables de sesión
session_unset();
session_destroy();

// Redirigir a la página de inicio de sesión
header("Location: ../index.php");
exit();
?>
