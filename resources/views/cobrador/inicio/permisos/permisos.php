<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../../controllers/conexion.php';

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

$stmt->close();

// Verifica si el resultado es nulo o si el rol del usuario no es 'admin'
if (!$fila || $fila['Nombre'] !== 'admin') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Permisos</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <br><br><br>
    <div class="header">
        <a href="/resources/views/admin/inicio/inicio.php" class="btn btn-inicio">Inicio</a>
    </div>
    <div class="message-container">
        <?php
        // Mostrar el mensaje de error si existe
        if (isset($_SESSION['error_message'])) {
            echo "<div class='error-message'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }

        // Mostrar el mensaje de éxito si existe
        if (isset($_SESSION['message'])) {
            echo "<div class='success-message'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }
        ?>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">Asignar Permisos a Usuarios</h1>
                    </div>
                    <div class="card-body">
                        <form method="post" action="assignPermissions.php">
                            <div class="form-group">
                                <label for="usuario_id">Selecciona un Usuario:</label>
                                <select class="form-control" name="usuario_id" id="usuario_id">
                                    <option value="">Seleccionar</option> <!-- Opción predeterminada para seleccionar -->
                                    <?php
                                    include "../../../../../controllers/conexion.php";

                                    $sql = "SELECT ID, nombre FROM usuarios";
                                    $result = $conexion->query($sql);

                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Permisos Disponibles:</label>
                                <div id="permisos-list">
                                    <!-- Los permisos se cargarán aquí a través de Ajax -->
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="seleccionarTodo">
                                    <label class="form-check-label" for="seleccionarTodo">Seleccionar todo</label>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary">Guardar Permisos</button>
                            </div>
                        </form>

                    </div>
                </div>


            </div>
        </div>
    </div>

    <script>
        // Esta función se ejecuta cuando se carga la página
        window.onload = function() {
            // Encuentra los elementos de mensaje
            var errorMessages = document.getElementsByClassName('error-message');
            var successMessages = document.getElementsByClassName('success-message');

            // Establece un temporizador para ocultar los mensajes de error
            setTimeout(function() {
                for (var i = 0; i < errorMessages.length; i++) {
                    if (errorMessages[i]) {
                        errorMessages[i].style.display = 'none';
                    }
                }
            }, 3000); // Oculta después de 3 segundos

            // Establece un temporizador para ocultar los mensajes de éxito
            setTimeout(function() {
                for (var i = 0; i < successMessages.length; i++) {
                    if (successMessages[i]) {
                        successMessages[i].style.display = 'none';
                    }
                }
            }, 3000); // Oculta después de 3 segundos
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Evento para manejar cambios en la selección del usuario
            document.getElementById('usuario_id').addEventListener('change', function() {
                var usuario_id = this.value;

                fetch('cargar_permisos.php', {
                        method: 'POST',
                        body: new URLSearchParams('usuario_id=' + usuario_id),
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                    })
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('permisos-list').innerHTML = data;

                        // Manejar la selección de todos los checkboxes
                        document.getElementById('seleccionarTodo').addEventListener('change', function() {
                            var checkboxes = document.querySelectorAll('#permisos-list input[type="checkbox"]');
                            for (var i = 0; i < checkboxes.length; i++) {
                                checkboxes[i].checked = this.checked;
                            }
                        });
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
</body>

</html>