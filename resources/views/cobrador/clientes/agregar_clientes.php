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

// Obtener la zona del usuario actual
$sqlZonaUsuario = "SELECT zona FROM usuarios WHERE id = ?";
$stmtZona = $conexion->prepare($sqlZonaUsuario);
$stmtZona->bind_param("i", $usuario_id);
$stmtZona->execute();
$resultZona = $stmtZona->get_result();
$zonaUsuario = null;

if ($fila = $resultZona->fetch_assoc()) {
    $zonaUsuario = $fila["zona"];
}
$stmtZona->close();

// Consulta para obtener las zonas
$sqlZonas = "SELECT ID, Nombre FROM zonas WHERE ID = ?";
$stmtZonas = $conexion->prepare($sqlZonas);
$stmtZonas->bind_param("i", $zonaUsuario);
$stmtZonas->execute();
$resultZonas = $stmtZonas->get_result();

// Consulta para obtener las ciudades de la zona específica del usuario
$sqlCiudades = "SELECT ID, Nombre FROM ciudades WHERE IDZona = ?";
$stmtCiudades = $conexion->prepare($sqlCiudades);
$stmtCiudades->bind_param("i", $zonaUsuario);
$stmtCiudades->execute();
$resultCiudades = $stmtCiudades->get_result();
$ciudades = $resultCiudades->fetch_all(MYSQLI_ASSOC);
$stmtCiudades->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Clientes</title>
    <link rel="stylesheet" href="/public/assets/css/registrar_cliente.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body id="body">

    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">

            <!-- Contenedor del select con tamaño ajustable y botones al lado -->
            <div class="d-flex align-items-center">
                <!-- Botones de Volver y Agregar Retiro con margen significativamente aumentado -->
                <div style="margin-left: 15px;">
                    <a href="../inicio/inicio.php" class="btn btn-outline-primary me-2">Volver a Inicio</a>
                </div>
            </div>

            <!-- Contenedor de la tarjeta -->
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;">
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span>
                            <span class="text-primary">Supervisor</span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div> 
    </header>

    <main class="form-container">
        <h1 class="form-title">Registro de Clientes</h1>
        <form action="/controllers/validar_clientes_supervisor.php" method="POST" enctype="multipart/form-data">

            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" oninput="this.value = this.value.toUpperCase()" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" oninput="this.value = this.value.toUpperCase()" required autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="curp">Identificación CURP:</label>
                    <input type="text" id="curp" name="curp" oninput="this.value = this.value.toUpperCase()" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="domicilio">Domicilio:</label>
                    <input type="text" id="domicilio" name="domicilio" oninput="this.value = this.value.toUpperCase()" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="moneda">Moneda Preferida:</label>
                    <select id="moneda" name="moneda">
                        <?php
                        require_once("../../../../controllers/conexion.php");

                        $query = "SELECT * FROM monedas WHERE Nombre = 'Pesos_mexicanos'";
                        $result = mysqli_query($conexion, $query);

                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='" . $row['ID'] . "'>" . $row['Nombre'] . "</option>";
                        }

                        mysqli_close($conexion);
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zona">Estado:</label>
                    <select id="zona" name="zona" placeholder="Por favor ingrese la zona" required autocomplete="off">
                        <?php
                        while ($row = mysqli_fetch_assoc($resultZonas)) {
                            echo '<option value="' . $row['ID'] . '">' . $row['Nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ciudad">Municipio:</label>
                    <select id="ciudad" name="ciudad">
                        <?php
                        foreach ($ciudades as $ciudad) {
                            echo '<option value="' . $ciudad['ID'] . '">' . $ciudad['Nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="asentamiento">Colonia:</label>
                    <input type="text" id="asentamiento" name="asentamiento" oninput="this.value = this.value.toUpperCase()" required autocomplete="off">
                </div>


                <div class="form-group">
                    <label for="imagen">Imagen del Cliente:</label>
                    <input type="file" id="imagen" name="imagen">
                </div>
            </div>

            <div class="form-row">
                <div id="mensaje-emergente" style="display: none;">
                    <p id="mensaje-error">Este cliente ya existe. No se puede registrar.</p>
                    <a href="" id="enlace-perfil">Ir al perfil</a>
                </div>
            </div>

            <div class="form-actions">
                <button id="boton-registrar" type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </main>


    <script>
        document.getElementById('zona').addEventListener('change', function() {
            var IDZona = this.value;
            var ciudadSelect = document.getElementById('ciudad');

            // Clear existing options
            ciudadSelect.innerHTML = '';

            if (IDZona) {
                // AJAX request to fetch cities
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_cities.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        var cities = JSON.parse(this.responseText);
                        cities.forEach(function(city) {
                            var option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.nombre;
                            ciudadSelect.appendChild(option);
                        });
                    }
                };
                xhr.send('IDZona=' + IDZona);
            }
        });
    </script>


    <script>
        function verificarCliente() {
            const curp = document.getElementById("curp").value;
            const telefono = document.getElementById("telefono").value;
            const mensajeEmergente = document.getElementById("mensaje-emergente");
            const mensajeError = document.getElementById("mensaje-error");
            const enlacePerfil = document.getElementById("enlace-perfil");
            const botonRegistrar = document.getElementById("boton-registrar");

            if (curp || telefono) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "/controllers/verificar_cliente.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const respuesta = JSON.parse(xhr.responseText);

                        if (respuesta.existe) {
                            mensajeEmergente.style.display = "block";
                            mensajeError.textContent = "Este cliente ya existe. No se puede registrar.";
                            enlacePerfil.href = "../../../../controllers/perfil_cliente.php?id=" + respuesta.cliente_id;
                            botonRegistrar.style.display = "none"; // Ocultar el botón
                        } else {
                            mensajeEmergente.style.display = "none";
                            enlacePerfil.href = "";
                            botonRegistrar.style.display = "block"; // Mostrar el botón
                        }
                    }
                };

                xhr.send("curp=" + encodeURIComponent(curp) + "&telefono=" + encodeURIComponent(telefono));
            } else {
                mensajeEmergente.style.display = "none";
                enlacePerfil.href = "";
                botonRegistrar.style.display = "block"; // Mostrar el botón si ambos campos están vacíos
            }
        }

        document.getElementById("curp").addEventListener("input", verificarCliente);
        document.getElementById("telefono").addEventListener("input", verificarCliente);
    </script>

</body>

</html>