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
// Conectar a la base de datos
include("../../../../../../controllers/conexion.php");

// Consulta SQL
$sql = "SELECT prestamos.ID, clientes.Nombre AS NombreCliente, prestamos.MontoAPagar, prestamos.Comision
        FROM prestamos
        INNER JOIN clientes ON prestamos.IDCliente = clientes.ID
        WHERE clientes.ZonaAsignada  = 'Quintana Roo'";


// Ejecutar la consulta
$result = $conexion->query($sql);
// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comisiones </title>
    <link rel="stylesheet" href="/public/assets/css/comision_inicio.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
</head>

<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>
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

            <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/inicio.php" class="selected">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>


            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/clientes/lista_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-people-group" title=""></i>
                        <h4>Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/clientes/agregar_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-user-tag" title=""></i>
                        <h4>Registrar Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_list_de_prestamos) : ?>
                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/creditos/crudPrestamos.php">
                    <div class="option">
                        <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                        <h4>Prestamos</h4>
                    </div>
                </a>
            <?php endif; ?> 

            <?php if ($tiene_permiso_cobros) : ?>
                <a href="/resources/views/zonas/22-QuintanaRoo/cobrador/cartera/lista_cartera.php">
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
        <main>

            <h1>Comisiones totales</h1>

            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Buscar...">
            </div>


            <table id="prestamos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Cliente</th>
                        <th>Monto a Pagar</th>
                        <th>Comisión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) : ?>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['ID']; ?></td>
                                <td><?php echo $row['NombreCliente']; ?></td>
                                <td><?php echo number_format($row['MontoAPagar'], 0, '.', '.'); ?></td>
                                <td><?php echo number_format($row['Comision'], 0, '.', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No hay préstamos registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>




        </main>


        <script>
            // Función para filtrar las filas de la tabla
            function filterTable(event) {
                var filter = event.target.value.toUpperCase();
                var rows = document.querySelector("#prestamos-table tbody").rows;

                for (var i = 0; i < rows.length; i++) {
                    var firstCol = rows[i].cells[0].textContent.toUpperCase();
                    var secondCol = rows[i].cells[1].textContent.toUpperCase();
                    var thirdCol = rows[i].cells[2].textContent.toUpperCase();
                    var fourthCol = rows[i].cells[3].textContent.toUpperCase();
                    if (firstCol.indexOf(filter) > -1 || secondCol.indexOf(filter) > -1 || thirdCol.indexOf(filter) > -1 || fourthCol.indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }

            document.querySelector('#search-input').addEventListener('keyup', filterTable, false);
        </script>

        <script src="/public/assets/js/MenuLate.js"></script>
</body>

</html>