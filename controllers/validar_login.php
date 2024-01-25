<?php
date_default_timezone_set('America/Bogota');
session_start();

// Incluye el archivo de conexión a la base de datos
include("conexion.php");

// Obtiene los datos del formulario
$email = mysqli_real_escape_string($conexion, $_POST["email"]);
$contrasena = mysqli_real_escape_string($conexion, $_POST["contrasena"]);

// Verificar el estado del sistema antes de permitir el login
$sqlEstado = "SELECT Estado FROM sistema_estado ORDER BY ID DESC LIMIT 1";
$resultadoEstado = $conexion->query($sqlEstado);

if ($resultadoEstado === false) {
    $_SESSION['error_message'] = "No se pudo obtener el estado del sistema.";
    header("Location: /index.php");
    exit();
}

$filaEstado = $resultadoEstado->fetch_assoc();

// Realiza la consulta a la base de datos para verificar si el usuario es administrador
$queryAdmin = "SELECT RolID FROM usuarios WHERE Email = ? AND Password = ?";
$stmtAdmin = $conexion->prepare($queryAdmin);
$stmtAdmin->bind_param("ss", $email, $contrasena);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

if ($rowAdmin = $resultAdmin->fetch_assoc()) {
    if ($filaEstado['Estado'] == 'inactivo' && $rowAdmin["RolID"] != 1) {
        $_SESSION['error_message'] = "El sistema está actualmente deshabilitado. Por favor, inténtalo más tarde.";
        header("Location: /index.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "Credenciales incorrectas o usuario inactivo.";
    header("Location: /index.php");
    exit();
}

// Realiza la consulta a la base de datos para el login
$query = "SELECT * FROM usuarios WHERE Email = ? AND Password = ? AND estado = 'activo'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("ss", $email, $contrasena);
$stmt->execute();
$result = $stmt->get_result();

// Comprueba si la consulta devuelve algún registro
if ($row = $result->fetch_assoc()) {
    // Guarda los datos del usuario en la sesión
    $_SESSION["usuario_id"] = $row["ID"];
    $_SESSION["nombre"] = $row["Nombre"];
    $_SESSION['user_zone'] = $row['Zona'];
    $_SESSION["rol"] = $row["RolID"];
    $_SESSION['Monto_neto'] = $row['saldo'];

    // Registra el ingreso en la tabla historial_ingresos
    $usuario_id = $row["ID"];
    $fecha_ingreso = date("Y-m-d");
    $hora_ingreso = date("H:i:s");

    $queryHistorial = "INSERT INTO historial_ingresos (usuario_id, fecha_ingreso, hora_ingreso) VALUES (?, ?, ?)";
    $stmtHistorial = $conexion->prepare($queryHistorial);
    $stmtHistorial->bind_param("iss", $usuario_id, $fecha_ingreso, $hora_ingreso);
    $stmtHistorial->execute();
    // Redirige a la página correspondiente según el rol y la zona del usuario
    if ($_SESSION["rol"] == 1) { // ADMINISTRADOR
        header("Location: /resources/views/admin/inicio/inicio.php");

        // SUPERVISOR 
    } elseif ($_SESSION["rol"] == 2) { // SUPERVISOR
        header("Location: /resources/views/supervisor/inicio/inicio.php");

        // SUPERVISOR 
    } else if ($_SESSION["rol"] == 3) { // COBRADOR
        header("Location: /resources/views/cobrador/inicio/inicio.php");
    } else {
        // Redirige a una página por defecto si el rol no se encuentra
        header("Location: /default_dashboard.php");
    }
    exit();
} else {
    // Credenciales incorrectas o usuario inactivo
    $_SESSION['error_message'] = "Credenciales incorrectas o usuario inactivo.";
    header("Location: /index.php");
    exit();
}

// Cierra la declaración y la conexión a la base de datos
$stmt->close();
$conexion->close();
