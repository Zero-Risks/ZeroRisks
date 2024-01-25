<?php
session_start();

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../../index.php");
    exit();
}


// Verificar si se ha pasado un mensaje en la URL
$mensaje = "";
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}

// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/public/assets/css/lista_super.css">
    <title>Abonos</title>
</head>

<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>
        <a href="/controllers/cerrar_sesion.php" class="botonn">
            <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
            <span class="spann">Cerrar Sesion</span>
        </a>
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
                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/cartera/lista_cartera.php">
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
        <h1 class="text-center">Listado de Cobradores</h1>

        <div class="container-fluid">

            <!-- Resto del código de la tabla -->
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellido</th>
                        <th scope="col">Zona</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include("../../../../../../controllers/conexion.php");
                    $sql = $conexion->prepare("SELECT Usuarios.ID, Usuarios.Nombre, Usuarios.Apellido, Usuarios.Email, Zonas.Nombre AS Zona, Roles.Nombre AS Rol FROM Usuarios JOIN Zonas ON Usuarios.Zona = Zonas.ID JOIN Roles ON Usuarios.RolID = Roles.ID WHERE Roles.ID = 3 AND Zonas.ID = 22"); // Filtra por el ID del rol de supervisor (2)

                    // Verificar si la preparación de la consulta fue exitosa
                    if ($sql === false) {
                        die("La preparación de la consulta SQL falló: " . $conexion->error);
                    }

                    // Ejecutar la consulta
                    if (!$sql->execute()) {
                        die("La ejecución de la consulta SQL falló: " . $sql->error);
                    }

                    $result = $sql->get_result();
                    $rowCount = 0; // Contador de filas
                    while ($datos = $result->fetch_object()) {
                        $rowCount++; // Incrementar el contador de filas
                    ?>
                        <tr class="row<?= $rowCount ?>">
                            <td><?= "REC 100" . $datos->ID ?></td>
                            <td><?= $datos->Nombre ?></td>
                            <td><?= $datos->Apellido ?></td>
                            <td><?= $datos->Zona ?></td>
                            <td><?= $datos->Rol ?></td>
                            <td>
                                <!-- Botón para ver los cobradores de la zona -->
                                <a href="ver_prestamos.php?zona=<?= urlencode($datos->Zona) ?>" class="btn btn-primary">Ver
                                    Prestamos</a>
                                <a href="ruta.php?zona=<?= urlencode($datos->Zona) ?>" class="btn btn-primary">Ruta</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div id="pagination" class="text-center">
            <ul class="pagination">
                <!-- Los botones de paginación se generarán aquí -->
            </ul>
        </div>
        </div>
    </main>

    <script src="/public/assets/js/MenuLate.js"></script>

</body>

</html>