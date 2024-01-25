<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

include "../../../../controllers/conexion.php";
$usuario_id = $_SESSION["usuario_id"];

$sql_nombre = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql_nombre);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
if ($fila = $resultado->fetch_assoc()) {
    $_SESSION["nombre_usuario"] = $fila["nombre"];
}
$stmt->close();

// Definir variables e inicializar con valores vacíos
$fecha = date('Y-m-d\TH:i'); // Fecha actual
$monto = $descripcion = "";
$fecha_err = $monto_err = $descripcion_err = "";

// Cargar la lista de usuarios
$usuarios_con_saldo = [];
$sql_usuarios = "SELECT id, nombre, saldo FROM usuarios WHERE RolID != 1";
$resultado_usuarios = $conexion->query($sql_usuarios);
while ($usuario = $resultado_usuarios->fetch_assoc()) {
    $usuarios_con_saldo[] = $usuario;
}

// Procesar datos del formulario cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["monto"]))) {
        $monto_err = "Por favor, ingrese el monto.";
    } elseif (!is_numeric($_POST["monto"])) {
        $monto_err = "Por favor, ingrese un valor numérico.";
    } else {
        $monto = trim($_POST["monto"]);
    }

    $descripcion = $_POST["descripcion"];

    if ($descripcion == "Retiro de banco" && empty($monto_err)) {
        // Restar el saldo del usuario actual
        $sql_update_saldo = "UPDATE usuarios SET saldo = saldo - ? WHERE id = ?";
        if ($stmt = $conexion->prepare($sql_update_saldo)) {
            $stmt->bind_param("di", $monto, $usuario_id);
            $stmt->execute();
            $stmt->close();
        }

        // Insertar en la tabla de retiros
        $sql_retiro = "INSERT INTO Retiros (IDUsuario, Fecha, Monto, Descripcion) VALUES (?, ?, ?, ?)";
        if ($stmt = $conexion->prepare($sql_retiro)) {
            $stmt->bind_param("isds", $usuario_id, $fecha, $monto, $descripcion);
            $stmt->execute();
            $stmt->close();
        }

        // Redirigir a la página de retiros
        header("location:retiros.php");
        exit();
    } elseif ($descripcion == "Dar saldo" && !empty($_POST["usuario_id"]) && !empty($_POST["monto"])) {
        $usuario_id_destino = $_POST["usuario_id"];
        $saldo_a_dar = $_POST["monto"];
    
        // Restar saldo al usuario de la sesión
        $sql_update_saldo_origen = "UPDATE usuarios SET saldo = saldo - ? WHERE id = ?";
        if ($stmt = $conexion->prepare($sql_update_saldo_origen)) {
            $stmt->bind_param("di", $saldo_a_dar, $usuario_id);
            $stmt->execute();
            $stmt->close();
        }
    
        // Sumar saldo al usuario destino
        $sql_update_saldo_destino = "UPDATE usuarios SET saldo = saldo + ? WHERE id = ?";
        if ($stmt = $conexion->prepare($sql_update_saldo_destino)) {
            $stmt->bind_param("di", $saldo_a_dar, $usuario_id_destino);
            $stmt->execute();
            $stmt->close();
        }
    
        // Registrar en la tabla de retiros
        $descripcion_retiro = "Dar saldo a usuario";
        $sql_retiro = "INSERT INTO Retiros (IDUsuario, Fecha, Monto, Descripcion) VALUES (?, ?, ?, ?)";
        if ($stmt = $conexion->prepare($sql_retiro)) {
            $stmt->bind_param("isds", $usuario_id, $fecha, $saldo_a_dar, $descripcion_retiro);
            $stmt->execute();
            $stmt->close();
        }
    
        // Registrar en la tabla saldo_admin
        $sql_saldo_admin = "INSERT INTO saldo_admin (IDUsuario, Monto, Monto_Neto) VALUES (?, ?, ?)";
        if ($stmt = $conexion->prepare($sql_saldo_admin)) {
            $stmt->bind_param("idd", $usuario_id_destino, $saldo_a_dar, $saldo_a_dar);
            $stmt->execute();
            $stmt->close();
        }
    
        // Redirigir a la página de retiros
        header("location:retiros.php");
        exit();
    }    
}
