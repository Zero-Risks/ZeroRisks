<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
}

// Verificar si se ha pasado un ID válido como parámetro GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirigir a una página de error o a la lista de clientes
    header("location: dias_pagos.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
include("conexion.php");

$usuario_id = $_SESSION["usuario_id"];

// Consulta SQL para obtener el nombre del usuario y el nombre del rol
$sql_nombre = "SELECT usuarios.nombre, roles.nombre AS nombre_rol FROM usuarios INNER JOIN roles ON usuarios.rolID = roles.id WHERE usuarios.id = ?";
$stmt = $conexion->prepare($sql_nombre);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $nombre_usuario = $fila["nombre"]; // Nombre del usuario
    $nombre_rol = $fila["nombre_rol"]; // Nombre del rol
} else {
    echo "Usuario no encontrado";
}


// Obtener el ID del cliente desde el parámetro GET
$id_cliente = $_GET['id'];

$user_zone = $_SESSION['user_zone'];
$user_role = $_SESSION['rol'];

// Verificar primero el rol
if ($_SESSION["rol"] == 1) {
    // Administrador
    $ruta_volver = "/resources/views/admin/inicio/inicio.php";
} elseif ($_SESSION["rol"] == 2) {
    // Supervisor
    // Aquí, verificar la zona específica para el supervisor
    if ($_SESSION["user_zone"] == 6) {
        $ruta_volver = "/resources/views/zonas/6-Chihuahua/supervisor/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 20) {
        $ruta_volver = "/resources/views/zonas/20-Puebla/supervisor/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 22) {
        $ruta_volver = "/resources/views/zonas/22-QuintanaRoo/supervisor/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 28) {
        $ruta_volver = "/resources/views/zonas/28-Tlaxcala/supervisor/inicio/inicio.php";
    } else {
        $ruta_volver = "/ruta_predeterminada_para_supervisor.php";
    }
} elseif ($_SESSION["rol"] == 3) {
    // Supervisor
    // Aquí, verificar la zona específica para el supervisor
    if ($_SESSION["user_zone"] == 6) {
        $ruta_volver = "/resources/views/zonas/6-Chihuahua/cobrador/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 20) {
        $ruta_volver = "/resources/views/zonas/20-Puebla/cobrador/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 22) {
        $ruta_volver = "/resources/views/zonas/22-QuintanaRoo/cobrador/inicio/inicio.php";
    } elseif ($_SESSION["user_zone"] == 28) {
        $ruta_volver = "/resources/views/zonas/28-Tlaxcala/cobrador/inicio/inicio.php";
    } else {
        $ruta_volver = "/ruta_predeterminada_para_cobrador.php";
    }
} else {
    // Si no hay un rol válido o no se cumple ninguna de las condiciones anteriores
    $ruta_volver = "/default_dashboard.php";
}


// Consulta SQL para obtener los detalles del cliente con el nombre de la moneda
$sql = "SELECT c.*, m.Nombre AS MonedaNombre, ciu.Nombre AS CiudadNombre
        FROM clientes c
        LEFT JOIN monedas m ON c.MonedaPreferida = m.ID
        LEFT JOIN ciudades ciu ON c.ciudad = ciu.ID
        WHERE c.ID = $id_cliente";

$resultado = $conexion->query($sql);

if ($resultado->num_rows === 1) {
    // Mostrar los detalles del cliente aquí
    $fila = $resultado->fetch_assoc();

    // Obtener la ruta de la imagen del cliente desde la base de datos
    $imagen_cliente = $fila["ImagenCliente"];

    // Si no hay imagen cargada, usar una imagen de reemplazo
    if (empty($imagen_cliente)) {
        $imagen_cliente = "../public/assets/img/perfil.png"; // Reemplaza con tu imagen por defecto
    }

    // Determinar si el cliente está en la lista de "Clavos"
    $esClavo = isClienteClavo($fila['ID'], $conexion);

    // Guardar el estado "Clavo" en la sesión (opcional)
    $_SESSION["es_clavo"] = $esClavo;
} else {
    // Cliente no encontrado en la base de datos, redirigir a una página de error o a la lista de clientes
    header("location: ../resources/views/admin/clientes/lista_clientes.php");
    exit();
}

// Obtener la zona y rol del usuario desde la sesión
$user_zone = $_SESSION['user_zone'];
$user_role = $_SESSION['rol'];

// ... (resto del código)

// Consulta SQL para obtener los préstamos del cliente
$sql_prestamos = "SELECT * FROM prestamos WHERE IDCliente = $id_cliente";
$resultado_prestamos = $conexion->query($sql_prestamos);

// Función para verificar si un cliente está en la lista de "Clavos"
function isClienteClavo($clienteID, $conexion)
{
    $query = "SELECT COUNT(*) AS total FROM clientes WHERE ID = $clienteID AND EstadoID = 2";
    $result = $conexion->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'] > 0;
    }

    return false;
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/perfil_cliente.css">
    <!-- Asegúrate de incluir tu hoja de estilos CSS -->
    <title>Perfil del Cliente</title>
    <!-- Incluir Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
</head>

<body>


    <body id="body">

        <header class="bg-white shadow-sm mb-4">
            <div class="container d-flex justify-content-between align-items-center py-2">

                <!-- Contenedor del select con tamaño ajustable y botones al lado -->
                <div class="d-flex align-items-center">
                    <div style="margin-left: 15px;">
                        <a href="<?= $ruta_volver ?>" class="btn btn-outline-primary me-2">Volver</a>
                    </div>
                </div>

                <!-- Contenedor de la tarjeta -->
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                            <p class="card-text">
                                <span style="color: #6c757d;">
                                    <?php echo $nombre_usuario; ?>
                                </span>
                                <span style="color: black;"> | </span>
                                <span class="text-primary"><?php echo $nombre_rol; ?></span>

                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- ACA VA EL CONTENIDO DE LA PAGINA -->
        <br><br><br>
        <main>
            <div class="profile-container">
                <div class="profile-image"><br><br><br>
                    <!-- Mostrar la foto del cliente -->
                    <img src="<?= $imagen_cliente ?>" alt="Foto del Cliente">
                </div>
                <div class="profile-details">
                    <!-- Mostrar los datos del cliente -->
                    <h1><strong><?= $fila["Nombre"] ?></strong></h1>
                    <p>Apellido: <strong><?= $fila["Apellido"] ?></strong></p>
                    <p>Curp: <strong><?= $fila["IdentificacionCURP"] ?></strong></p>
                    <p>Domicilio: <strong><?= $fila["Domicilio"] ?></strong></p>
                    <p>Teléfono: <strong><?= $fila["Telefono"] ?></strong> </p>
                    <p>Moneda Preferida: <strong><?= $fila["MonedaNombre"] ?></strong></p> <!-- Nombre de la moneda -->
                    <p>Estado: <strong><?= $fila["ZonaAsignada"] ?></strong></p>
                    <p>Municipio: <strong><?= $fila["CiudadNombre"] ?></strong></p>
                    <p>Colonia: <strong><?= $fila["asentamiento"] ?></strong></p>
                    <p>Clavo: <strong><?= $esClavo ? 'Sí' : 'No' ?></strong></p>
                </div>
            </div>

            <!-- Agregar una sección para mostrar los préstamos del cliente -->
            <div class="profile-loans">
                <h2>Préstamos del Cliente</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID del Préstamo</th>
                            <th>Deuda</th>
                            <th>Plazo</th>
                            <th>Fecha de Inicio</th>
                            <th>Fecha de Vencimiento</th>
                            <th>Estado</th>
                            <th>Pagos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila_prestamo = $resultado_prestamos->fetch_assoc()) : ?>
                            <tr>
                                <td><?= "REC 100" . $fila_prestamo["ID"] ?></a></td>
                                <td><?= $fila_prestamo["MontoAPagar"] ?></td>
                                <td><?= $fila_prestamo["Plazo"] ?></td>
                                <td><?= $fila_prestamo["FechaInicio"] ?></td>
                                <td><?= $fila_prestamo["FechaVencimiento"] ?></td>
                                <td><?= $fila_prestamo["Estado"] ?></td>
                                <td><a href="dias_pago.php?id=<?= $fila_prestamo["ID"] ?>" class="currency-button"><i class="fa-solid fa-sack-dollar"></i> Pagos</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <script>
            // Agregar un evento clic al botón
            document.getElementById("volverAtras").addEventListener("click", function() {
                window.history.back();
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const profileImage = document.querySelector('.profile-image img');

                // Agrega un controlador de eventos para hacer clic en la imagen
                profileImage.addEventListener('click', function() {
                    profileImage.classList.toggle('zoomed'); // Alterna la clase 'zoomed'
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
        </script>
    </body>

</html>