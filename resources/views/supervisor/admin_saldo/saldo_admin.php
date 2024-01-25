<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validacion de rol para ingresar a la pagina 
require_once '../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
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

    // Preparar la consulta para obtener el rol del usuario
    $stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
    $stmt->bind_param("i", $usuario_id);

    // Ejecutar la consulta
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    // Verifica si el resultado es nulo, lo que significaría que el usuario no tiene un rol válido
    if (!$fila) {
        // Redirige al usuario a una página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }

    // Extrae el nombre del rol del resultado
    $rol_usuario = $fila['Nombre'];

    // Verifica si el rol del usuario corresponde al necesario para esta página
    if ($rol_usuario !== 'supervisor') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_saldo'])) {
    // Obtén el monto del formulario
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0; // Asegúrate de tener un valor válido

    // Realiza la validación del monto si es necesario

    // Incluye el archivo de conexión a la base de datos
    include("../../../../controllers/conexion.php");

    // Comenzar transacción para asegurar la atomicidad de las operaciones
    $conexion->begin_transaction();

    try {
        // Supongamos que el ID de administrador es 1 (reemplaza con el valor correcto)
        $idAdmin = $_SESSION['usuario_id'];

        // Actualiza el saldo en la tabla usuarios
        $sqlUsuario = "UPDATE usuarios SET saldo = ? WHERE id = ?";
        $stmtUsuario = $conexion->prepare($sqlUsuario);
        if ($stmtUsuario) {
            $stmtUsuario->bind_param("di", $monto, $idAdmin);
            $stmtUsuario->execute();
            $stmtUsuario->close();
        } else {
            throw new Exception("Error al actualizar el saldo del usuario: " . $conexion->error);
        }

        // Inserta el saldo inicial en la tabla saldo_admin
        $sqlSaldoAdmin = "INSERT INTO saldo_admin (IDUsuario, Monto, Monto_Neto) VALUES (?, ?, ?)";
        $stmtSaldoAdmin = $conexion->prepare($sqlSaldoAdmin);
        if ($stmtSaldoAdmin) {
            $stmtSaldoAdmin->bind_param("idd", $idAdmin, $monto, $monto);
            $stmtSaldoAdmin->execute();
            $stmtSaldoAdmin->close();
        } else {
            throw new Exception("Error al guardar el saldo inicial: " . $conexion->error);
        }

        // Si todo fue bien, confirmar las operaciones
        $conexion->commit();
        $successMessage = "Saldo guardado con éxito.";
    } catch (Exception $e) {
        // Si hay algún error, revertir todas las operaciones
        $conexion->rollback();
        echo '<p class="error-message">Transacción fallida: ' . $e->getMessage() . '</p>';
    }

    $conexion->close();
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-message {
            color: #009900;
            /* Color de texto verde para mensajes de éxito */
            font-weight: bold;
            text-align: center;
        }

        .error-message {
            color: #FF0000;
            /* Color de texto rojo para mensajes de error */
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>

<header class="bg-white shadow-sm mb-4">

<div class="container d-flex justify-content-between align-items-center py-2">
   <div class="container mt-3">
<a href="../inicio/inicio.php" class="btn btn-secondary">Volver al Inicio</a>
</div>
    <div class="card" style="max-width: 180px; max-height: 75px;"> <!-- Ajusta el ancho máximo y el alto máximo de la tarjeta según tus preferencias -->
        <div class="card-body">
            <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                <p class="card-text" style="font-size: 15px;"> <!-- Ajusta el tamaño de fuente según tus preferencias -->
                    <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                        <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                    </span>
                    <span style="color: black;"> | </span> <!-- Divisor negro -->
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>
    
    <div class="container my-4">
        <h2>Asignar Saldo Inicial al Administrador</h2>
        <?php
        // Verifica si existe un mensaje de éxito y lo muestra
        if (isset($successMessage)) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($successMessage) . " Redireccionando en <span id='countdown'>5</span> segundos.</div>";
        }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="monto">Monto:</label>
                <input type="number" step="0.01" id="monto" name="monto" class="form-control" required>
            </div>
            <button type="submit" name="guardar_saldo" class="btn btn-primary">Guardar</button>
        </form>
    </div>

    <script>
        if (document.getElementById('countdown')) {
            var timeLeft = 5; // segundos
            var countdownElement = document.getElementById('countdown');

            var timerId = setInterval(countdown, 1000);

            function countdown() {
                if (timeLeft == 0) {
                    clearTimeout(timerId);
                    window.location.href = '../inicio/inicio.php'; // Redirige a inicio.php
                } else {
                    countdownElement.innerHTML = timeLeft;
                    timeLeft--;
                }
            }
        }
    </script>


    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>