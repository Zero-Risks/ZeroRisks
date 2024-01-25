<?php
session_start();
include("../../../../controllers/conexion.php");
include("informacionCP/navegacion_cliente.php");

// Asegúrate de que el ID del usuario está almacenado en la sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); // Cambia a la página de login si no hay usuario en sesión
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$idActual = $_GET['idCliente'] ?? null; // Asegúrate de que estás obteniendo el ID del cliente actual correctamente

if ($idActual === null) {
    header("Location: error_1.php"); // Redirige si no hay un ID de cliente
    exit();
}

$idsNavegacion = obtenerIdAnteriorSiguiente($idActual, $usuarioId);
$idSiguienteCliente = $idsNavegacion['siguiente'];

function actualizarEstadoNoPago($conexion, $idPrestamo)
{
    $consulta = $conexion->prepare("UPDATE prestamos SET Pospuesto = 1 WHERE ID = ?");
    $consulta->bind_param("i", $idPrestamo);

    if (!$consulta->execute()) {
        echo "Error al actualizar el estado a 'No pago': " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

function actualizarEstadoMasTarde($conexion, $idPrestamo)
{
    $consulta = $conexion->prepare("UPDATE prestamos SET mas_tarde = 1 WHERE ID = ?");
    $consulta->bind_param("i", $idPrestamo);

    if (!$consulta->execute()) {
        echo "Error al actualizar el estado a 'Más tarde': " . $consulta->error;
        $consulta->close();
        return false;
    }

    $consulta->close();
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $idPrestamo = $_GET['idPrestamo'] ?? null;
    $accion = $_GET['accion'] ?? null;

    if ($idPrestamo === null || $accion === null) {
        header("Location: error_2.php");
        exit();
    }

    if ($accion == 'no_pago') {
        $actualizacionExitosa = actualizarEstadoNoPago($conexion, $idPrestamo);
    } elseif ($accion == 'mas_tarde') {
        $actualizacionExitosa = actualizarEstadoMasTarde($conexion, $idPrestamo);
    } else {
        header("Location: error_3.php"); // Acción no reconocida
        exit();
    }

    if ($actualizacionExitosa) {
        // Si hay un siguiente cliente en la ruta
        if ($idSiguienteCliente !== null) {
            header("Location: abonos.php?id=" . $idSiguienteCliente);
            exit();
        } else {
            header("Location: informacionCP/todo.php");
            exit();
        }
    } else {
        header("Location: error_4.php"); // Error en la actualización
        exit();
    }
}

// Asegúrate de cerrar la conexión si ya no la necesitas
$conexion->close();
?>
