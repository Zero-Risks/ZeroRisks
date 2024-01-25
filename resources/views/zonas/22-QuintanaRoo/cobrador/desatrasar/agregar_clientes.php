<?php
date_default_timezone_set('America/Bogota');
session_start();


// Validacion de rol para ingresar a la pagina 
require_once '../../../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
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
    if ($rol_usuario !== 'cobrador') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

// El usuario ha iniciado sesión, mostrar el contenido de la página aquí
?>

<!DOCTYPE html>
<html lang="en">

<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Clientes</title>

<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="/resources/views/admin/desatrasar/css/registrar_cliente.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</head>

<body style="background-color: #a8dbd6;">

    <!-- ACA VA EL CONTENIDO DEL MENU -->


    <header><br>
        <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/inicio.php" class="botonn">
            <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
            <span class="spann">Volver al Inicio</span>
        </a>

        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Administrator<span>";
            }
            ?>
        </div>
    </header>
    <br><br><br>



    <main>

        <?php


        if (isset($_SESSION['mensaje'])) {
            echo "<div id='mensaje' class='mensaje'>" . $_SESSION['mensaje'] . "</div>";
            unset($_SESSION['mensaje']); // Eliminar el mensaje de la sesión después de mostrarlo
        }
        ?>



        <h1>Registro de Clientes Atrasados </h1>
        <form action="/controllers/validar_cliente_QuintanaRooCob.php" method="POST" enctype="multipart/form-data">
            <div class="input-container">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="input-container">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>

            <div class="input-container">
                <label for="curp">Identificación CURP:</label>
                <input type="text" id="curp" name="curp" required>
            </div>

            <div class="input-container">
                <label for="domicilio">Domicilio:</label>
                <input type="text" id="domicilio" name="domicilio" required>
            </div>

            <div class="input-container">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required>
            </div>
            <div class="input-container">
                <label for="moneda">Moneda Preferida:</label>
                <select id="moneda" name="moneda">
                    <?php
                    require_once("../../../../../../controllers/conexion.php");

                    $query = "SELECT * FROM monedas";
                    $result = mysqli_query($conexion, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['ID'] . "'>" . $row['Nombre'] . "</option>";
                    }

                    mysqli_close($conexion);
                    ?>
                </select>
            </div>

            <div class="input-container">
                <label for="zona">Estado:</label>
                <select id="zona" name="zona" placeholder="Por favor ingrese la zona" required>
                    <?php
                    // Incluye el archivo de conexión a la base de datos
                    include("../../../../../../controllers/conexion.php");
                    // Consulta SQL para obtener las zonas
                    $consultaZonas = "SELECT ID, Nombre FROM zonas WHERE Nombre = 'Quintana Roo'";
                    $resultZonas = mysqli_query($conexion, $consultaZonas);
                    // Genera las opciones del menú desplegable para Zona
                    while ($row = mysqli_fetch_assoc($resultZonas)) {
                        echo '<option value="' . $row['ID'] . '">' . $row['Nombre'] . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="input-container">
                <label for="ciudad">Municipio:</label>
                <select id="ciudad" name="ciudad" required>
                    <?php
                    // Incluye el archivo de conexión a la base de datos
                    include("../../../../../../controllers/conexion.php");
                    // Consulta SQL para obtener las zonas
                    $consultaZonas = "SELECT * FROM ciudades WHERE iDZona = 22";
                    $resultZonas = mysqli_query($conexion, $consultaZonas);
                    // Genera las opciones del menú desplegable para Zona
                    while ($row = mysqli_fetch_assoc($resultZonas)) {
                        echo '<option value="' . $row['ID'] . '">' . $row['Nombre'] . '</option>';
                    }
                    ?>
                </select>
            </div>


            <div class="input-container">
                <label for="asentamiento">Asentamiento:</label>
                <input type="text" id="asentamiento" name="asentamiento" required>
            </div>


            <div class="input-container">
                <label for="imagen">Imagen del Cliente:</label>
                <input type="file" id="imagen" name="imagen">
            </div>
            <div id="mensaje-emergente" style="display: none;">
                <p id="mensaje-error">Este cliente ya existe. No se puede registrar.</p>
                <a href="" id="enlace-perfil">Ir al perfil</a>
            </div>

            <!-- modal -->
            <div id="modal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <h2>Confirmar Registro de Cliente</h2>
                    <p>¿Estás seguro de que deseas registrar este cliente?</p>
                    <div id="clienteInfo">
                        <!-- Aquí mostrarás los datos del cliente -->
                    </div>
                    <button id="confirmarRegistro">Confirmar</button>
                </div>
            </div>


            <div class="btn-container">
                <input id="boton-registrar" class="btn-container" type="submit" value="Registrar">

            </div>
        </form>


    </main>
</body>



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
    window.onload = function() {
        setTimeout(function() {
            var mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.display = 'none';
            }
        }, 5000); // El mensaje se ocultará después de 5000 milisegundos (5 segundos)
    };

    // modal de cliente 
    // Obtén una referencia al botón de confirmación y al modal
    var btnConfirmarRegistro = document.getElementById("btnConfirmarRegistro");
    var modal = document.getElementById("modal");

    // Agrega un evento click al botón de confirmación
    btnConfirmarRegistro.addEventListener("click", function() {
        // Aquí puedes obtener los datos del cliente que se ingresaron en el formulario
        var nombre = document.getElementById("nombre").value;
        var apellido = document.getElementById("apellido").value;
        var curp = document.getElementById("curp").value;
        var telefono = document.getElementById("telefono").value;
        var zona = document.getElementById("zona").options[document.getElementById("zona").selectedIndex].text;
        var ciudad = document.getElementById("ciudad").options[document.getElementById("ciudad").selectedIndex].text;
        var domicilio = document.getElementById("domicilio").value;
        // ... otros datos

        // Construye el contenido del modal con los datos del cliente
        var clienteInfo = document.getElementById("clienteInfo");
        clienteInfo.innerHTML = `
        <p><strong>Nombre:</strong> ${nombre}</p>
        <p><strong>Apellido:</strong> ${apellido}</p>
        <p><strong>CURP:</strong> ${curp}</p>
        <p><strong>Teléfono:</strong> ${telefono}</p>
        <p><strong>Zona:</strong> ${zona}</p>
        <p><strong>Ciudad:</strong> ${ciudad}</p>
        <p><strong>Domicilio:</strong> ${domicilio}</p>
        <!-- Agrega aquí más campos si es necesario -->
    `;

        // Muestra el modal
        modal.style.display = "block";
    });

    // Agrega un evento click al botón para cerrar el modal
    document.getElementById("closeModal").addEventListener("click", function() {
        modal.style.display = "none";
    });

    // Agrega un evento click al botón de confirmación dentro del modal (puedes implementar aquí la lógica de registro)
    document.getElementById("confirmarRegistro").addEventListener("click", function() {


        // Cierra el modal
        modal.style.display = "none";
    });
</script>
<script>
    function verificarCliente() {
        const curp = document.getElementById("curp").value;
        // const telefono = document.getElementById("telefono").value;
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


<script src="/public/assets/js/mensaje.js"></script>

</body>

</html>