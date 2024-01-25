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

include "../../../../../../controllers/conexion.php";

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


// Obtener el nombre de la zona desde la URL
if (isset($_GET['zona'])) {
    $nombreZona = $_GET['zona'];
}

// Verificar si se ha pasado un mensaje en la URL
$mensaje = "";
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}

// Consulta SQL para obtener los préstamos de la zona especificada con el nombre del cliente
$sql = $conexion->prepare("SELECT p.ID, c.Nombre AS nombreCliente, p.Zona, p.Monto FROM prestamos p INNER JOIN clientes c ON p.IDCliente = c.ID WHERE p.Zona = ?");
$sql->bind_param("s", $nombreZona);
$sql->execute();

// Verificar si la consulta se realizó con éxito
if ($sql === false) {
    die("Error en la consulta SQL: " . $conexion->error);
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
    <title>Listado de Préstamos</title>
</head>

<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>
        <a href="javascript:history.back()" class="back-link">Volver Atrás</a>

        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Supervisor<span>";
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

            <a href="/resources/views/zonas/20-Puebla/supervisor/inicio/inicio.php" class="selected">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>
            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/usuarios/crudusuarios.php">
                    <div class="option">
                        <i class="fa-solid fa-users" title=""></i>
                        <h4>Usuarios</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/usuarios/registrar.php">
                    <div class="option">
                        <i class="fa-solid fa-user-plus" title=""></i>
                        <h4>Registrar Usuario</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/clientes/lista_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-people-group" title=""></i>
                        <h4>Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>

                <a href="/resources/views/zonas/20-Puebla/supervisor/clientes/agregar_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-user-tag" title=""></i>
                        <h4>Registrar Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_list_de_prestamos) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/creditos/crudPrestamos.php">
                    <div class="option">
                        <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                        <h4>Prestamos</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_gastos) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/gastos/gastos.php">
                    <div class="option">
                        <i class="fa-solid fa-sack-xmark" title=""></i>
                        <h4>Gastos</h4>
                    </div>
                </a>
            <?php endif; ?>

        </div>

    </div>


    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <h1 class="text-center">Listado de Prestamos en Zona: <?= $nombreZona ?></h1>

        <div class="container-fluid">
            <div class="row">

                <div class="col-md-9">
                    <div class="table-scroll-container">
                        <table class="table-container">
                            <thead>
                                <tr>
                                    <th scope="col">ID del Préstamo</th>
                                    <th scope="col">Nombre del Cliente</th>
                                    <th scope="col">Zona</th>
                                    <th scope="col">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $sql->get_result();
                                $rowCount = 0; // Contador de filas
                                while ($datos = $result->fetch_assoc()) {
                                    $rowCount++; // Incrementar el contador de filas
                                ?>
                                    <tr class="row<?= $rowCount ?>">
                                        <td><?= "Prestamo " . $datos['ID'] ?></td>
                                        <td><?= $datos['nombreCliente'] ?></td>
                                        <td><?= $datos['Zona'] ?></td>
                                        <td><?= $datos['Monto'] ?></td>
                                    </tr>
                                <?php }
                                // Cerrar la consulta y la conexión a la base de datos
                                $sql->close();
                                $conexion->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Agregar un evento clic al botón
        document.getElementById("volverAtras").addEventListener("click", function() {
            window.history.back();
        });
    </script>
    <script src="/public/assets/js/MenuLate.js"></script>

</body>

</html>