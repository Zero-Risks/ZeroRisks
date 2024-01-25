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

// Conectar a la base de datos
include("../../../../../../controllers/conexion.php");

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

// Consulta SQL para obtener los préstamos de la zona especificada con el nombre del cliente
$sql = $conexion->prepare("SELECT P.ID, C.Nombre AS NombreCliente, P.Zona, P.Monto FROM prestamos P INNER JOIN clientes C ON P.IDCliente = C.ID ORDER BY ID DESC WHERE Zona = 'Aguascalientes'");
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
    <title>Recaudo</title>
    <link rel="stylesheet" href="/public/assets/css/cobro_inicio.css">
</head>

<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>

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

            <a href="/resources/views/zonas/6-Chihuahua/supervisor/inicio/inicio.php" class="selected">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>

            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/6-Chihuahua/supervisor/usuarios/crudusuarios.php">
                    <div class="option">
                        <i class="fa-solid fa-users" title=""></i>
                        <h4>Usuarios</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/6-Chihuahua/supervisor/usuarios/registrar.php">
                    <div class="option">
                        <i class="fa-solid fa-user-plus" title=""></i>
                        <h4>Registrar Usuario</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/6-Chihuahua/supervisor/clientes/lista_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-people-group" title=""></i>
                        <h4>Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/6-Chihuahua/supervisor/clientes/agregar_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-user-tag" title=""></i>
                        <h4>Registrar Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_list_de_prestamos) : ?>
                <a href="/resources/views/zonas/6-Chihuahua/supervisor/creditos/crudPrestamos.php">
                    <div class="option">
                        <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                        <h4>Prestamos</h4>
                    </div>
                </a>
            <?php endif; ?> 

        </div>

    </div>




    <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

    <main>
        <h1>Cobros totales</h1>

        <div class="search-container">
            <input type="text" id="search-input" class="search-input" placeholder="Buscar..." autocomplete="off">
        </div>

        <div class="container-fluid">
            <div class="row">

                <div class="col-md-9">
                    <table class="table">
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
                                $montoFormateado = number_format($datos['Monto'], 0, '.', '.'); // Formatear Monto
                            ?>
                                <tr class="row<?= $rowCount ?>">
                                    <td><?= "Cobro REC-10" . $datos['ID'] ?></td>
                                    <td><?= $datos['NombreCliente'] ?></td>
                                    <td><?= $datos['Zona'] ?></td>
                                    <td><?= $montoFormateado ?></td> <!-- Mostrar Monto formateado -->
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
    </main>

    <script>
        // Agregar un evento clic al botón
        document.getElementById("volverAtras").addEventListener("click", function() {
            window.history.back();
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("search-input");
            const rows = document.querySelectorAll(".table tbody tr");

            searchInput.addEventListener("input", function() {
                const searchTerm = searchInput.value.trim().toLowerCase();

                rows.forEach(function(row) {
                    const columns = row.querySelectorAll("td");
                    let found = false;

                    columns.forEach(function(column, index) {
                        const text = column.textContent.toLowerCase();
                        if (index === 0 && text.includes(searchTerm)) {
                            found = true; // Search only in the first (ID) column
                        } else if (index !== 0 && text.includes(searchTerm)) {
                            found = true; // Search in other columns
                        }
                    });

                    if (found) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>


    <script src="/public/assets/js/MenuLate.js"></script>

</body>

</html>