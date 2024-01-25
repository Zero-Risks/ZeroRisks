<?php
session_start();
date_default_timezone_set('America/Bogota');
// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../../../controllers/conexion.php';

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
if (!$fila || $fila['Nombre'] !== 'cobrador') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}

// Consulta SQL para obtener las carteras
$sql = "SELECT carteras.id, carteras.nombre, carteras.zona, ciudades.nombre AS nombre_ciudad, carteras.asentamiento 
        FROM carteras 
        JOIN ciudades ON carteras.ciudad = ciudades.id 
        WHERE carteras.zona = 28";
$result = $conexion->query($sql);

// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");
?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/lista_super.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <title>Lista de Carteras</title>
    <style>
        .back-link1 {
            display: inline-block;
            padding: 10px 15px;
            margin: 10px 5px;
            border: 1px solid #74d8d8;
            background-color: #a9f0f0;
            color: rgb(0, 0, 0);
            text-decoration: none;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }

        .back-link1:hover {
            background-color: #2cc0c0;
        }

        .back-link3 {
            display: inline-block;
            padding: 10px 11px;
            margin: 10px 5px;
            border: 1px solid #73e773;
            background-color: #a3e4a3;
            color: rgb(0, 0, 0);
            text-decoration: none;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 16px;
        }

        .back-link3:hover {
            background-color: #93cc93;
        }
    </style>
</head>




<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>
        <a href="agregar_cartera.php?" class="back-link1">
            <span>Agregar Cobro</span>
        </a>

        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Cobrador<span>";
            }
            ?>
        </div>
    </header>


    <div class="menu__side" id="menu_side">

        <div class="name__page">
            <img src="/public/assets/img/logo.png" class="img logo-image" alt="">
            <h4>Recaudo</h4>
        </div>

        <div class="options__menu">

            <a href="/controllers/cerrar_sesion.php">
                <div class="option">
                    <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
                    <h4>Cerrar Sesion</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/inicio.php">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>


            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/clientes/lista_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-people-group" title=""></i>
                        <h4>Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/clientes/agregar_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-user-tag" title=""></i>
                        <h4>Registrar Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_list_de_prestamos) : ?>
                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/creditos/crudPrestamos.php">
                    <div class="option">
                        <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                        <h4>Prestamos</h4>
                    </div>
                </a>
            <?php endif; ?> 
            
            <?php if ($tiene_permiso_cobros) : ?>
                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/cartera/lista_cartera.php" class="selected">
                    <div class="option">
                        <i class="fa-regular fa-address-book"></i>
                        <h4>Cobros</h4>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </div>




    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <!-- Botón para volver a la página anterior -->
        <h1 class="text-center">Cobros</h1>

        <div class="container-fluid">

            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Municipio</th>
                    <th>Colonia</th>
                </tr>
                <?php
                // Mostrar los resultados en la tabla
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . "REC-100" . $row["id"] . "</td>";
                        echo "<td><a href='clientes_por_cartera.php?id=" . $row["id"] . "'  class='back-link3'>" . $row["nombre"] . "</a></td>";
                        echo "<td>" . $row["nombre_ciudad"] . "</td>";
                        echo "<td>" . $row["asentamiento"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No se encontraron resultados</td></tr>";
                }
                ?>
            </table>

        </div>


    </main>

    <script src="/public/assets/js/MenuLate.js"></script>

</body>

</html>