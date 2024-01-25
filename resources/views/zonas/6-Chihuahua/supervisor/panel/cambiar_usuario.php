<?php
session_start();
require_once '../../../../../../controllers/conexion.php';

if (isset($_POST['usuario_id'])) {
    $usuarioID = $_POST['usuario_id'];

    // Obtener la zona y el rol del usuario seleccionado
    $queryInfoUsuario = "SELECT Zona, RolID FROM usuarios WHERE ID = ?";
    if ($stmt = $conexion->prepare($queryInfoUsuario)) {
        $stmt->bind_param("i", $usuarioID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($fila = $result->fetch_assoc()) {
            $zona = $fila['Zona'];
            $nuevoRol = $fila['RolID'];
        }
        $stmt->close();
    }

    // Guardar información original y establecer sesión temporal
    $_SESSION["original_user_id"] = $_SESSION["usuario_id"];
    $_SESSION["original_rol"] = $_SESSION["rol"];
    $_SESSION["original_rol_nombre"] = $_SESSION["rol"] == 1 ? "administrador" : "supervisor";
    $_SESSION["sesion_temporal"] = true;

    // Determinar y guardar la ruta de retorno
    if ($_SESSION["rol"] == 1) { // Administrador
        $_SESSION["ruta_retorno"] = "/ruta_dashboard_administrador.php";
    } else { // Supervisor
        $_SESSION["ruta_retorno"] = determinarRutaRetornoSupervisor($zona);
    }

    // Cambiar a la sesión del usuario seleccionado
    $_SESSION["usuario_id"] = $usuarioID;
    $_SESSION["rol"] = $nuevoRol;

    // Redireccionar según la zona y el rol
    $redirect = determinarRutaDashboard($zona, $nuevoRol);
    header("Location: $redirect");
    exit();
}

function determinarRutaRetornoSupervisor($zonaSupervisor)
{
    switch ($zonaSupervisor) {
        case '06':
            return "../../zonas/6-Chihuahua/supervisor/inicio/inicio.php";
        case '20':
            return "../../zonas/20-Puebla/supervisor/inicio/inicio.php";
            // Agrega más casos para otras zonas
        default:
            return "/error_o_inicio.php";
    }
}

function determinarRutaDashboard($zona, $rol)
{
    switch ($zona) {
        case '06':
            return $rol == 2 ? "../../zonas/6-Chihuahua/supervisor/inicio/inicio.php" : "../../cobrador/inicio/inicio.php";
        case '20':
            return $rol == 2 ? "../../zonas/20-Puebla/supervisor/inicio/inicio.php" : "../../zonas/20-Puebla/cobrador/inicio/inicio.php";
            // Agrega más casos según sea necesario
        default:
            return "/error_o_ibbbbbbbbbbbbbnicio.php";
    }
}
