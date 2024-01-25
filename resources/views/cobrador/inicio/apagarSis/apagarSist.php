<?php
date_default_timezone_set('America/Bogota');
session_start();


include("../../../../../controllers/conexion.php");

// Inicializa la variable
$estadoActual = 'inactivo'; // Valor por defecto

// Consulta para obtener el estado actual del sistema
$sql = "SELECT Estado FROM sistema_estado ORDER BY ID DESC LIMIT 1";
$resultado = $conexion->query($sql);

if ($resultado && $fila = $resultado->fetch_assoc()) {
    $estadoActual = $fila['Estado'];
}

// Cierra la conexión
$conexion->close();

// Mensaje de confirmación
$mensajeConfirmacion = '';
if (isset($_SESSION['cambio_estado_mensaje'])) {
    $mensajeConfirmacion = $_SESSION['cambio_estado_mensaje'];
    unset($_SESSION['cambio_estado_mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cambiar Estado del Sistema</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/Estadosistema.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
</head>

<body>
     
        <header>
    <div class="nombre-usuario">
              <?php
        if (isset($_SESSION["nombre_usuario"])) {
            echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Administrator<span>";
        }
        ?>
    </div>
    <a href="/resources/views/admin/inicio//inicio.php" class="botonn">
        <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
        <span class="spann">Volver al Inicio</span>
    </a>
</header><br><br>


    <div class="container">
        <h1>Apagar o Encender Sistema </h1>

        <?php if ($mensajeConfirmacion) : ?>
            <strong>
                <div id="mensajeConfirmacion" class="mensaje-confirmacion">
                    <?php echo $mensajeConfirmacion; ?>
                </div>
            </strong>
        <?php endif; ?>

        <strong>
            <p class="estado-actual">Estado actual del sistema: <?php echo $estadoActual === 'activo' ? 'Encendido' : 'Apagado'; ?></p>
        </strong>

        <form action="cambiar_estado.php" method="post" class="form-estado">
            <div class="radio-group">
                <label>
                    <input type="radio" name="estado" value="activo" <?php echo $estadoActual === 'activo' ? 'checked' : ''; ?>>
                    Encender Sistema
                </label>
                <label>
                    <input type="radio" name="estado" value="inactivo" <?php echo $estadoActual === 'inactivo' ? 'checked' : ''; ?>>
                    Apagar Sistema
                </label>
            </div>
            <button type="submit">Cambiar Estado</button>
        </form>
    </div>
    <script>
        // Oculta el mensaje después de 5 segundos (5000 milisegundos)
        setTimeout(function() {
            var mensaje = document.getElementById('mensajeConfirmacion');
            if (mensaje) {
                mensaje.style.display = 'none';
            }
        }, 2000);
    </script>
</body>

</html>