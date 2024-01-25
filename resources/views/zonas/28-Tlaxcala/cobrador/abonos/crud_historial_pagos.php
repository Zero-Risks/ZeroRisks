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


// Incluir el archivo de conexión a la base de datos
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

// Verificar si se ha proporcionado el clienteId en la URL
if (isset($_GET['clienteId'])) {
    $clienteId = $_GET['clienteId'];

    // Consulta SQL para obtener las facturas de un cliente específico
    $sql = "SELECT * FROM facturas WHERE cliente_id = $clienteId";
    $resultado = $conexion->query($sql);

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
        <link rel="stylesheet" href="/public/assets/css/curdFaturas.css">
        <title>Historial de Pagos</title>
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

                <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/inicio.php">
                    <div class="option">
                        <i class="fa-solid fa-landmark" title="Inicio"></i>
                        <h4>Inicio</h4>
                    </div>
                </a>


                <?php if ($tiene_permiso_listar_clientes) : ?>
                    <a href="/resources/views/zonas/28-Tlaxcala/cobrador/clientes/lista_clientes.php" class="selected">
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
            <h1>Historial de Pagos del Cliente</h1>
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Buscar...">
            </div>

            <table>
                <tr>
                    <th>ID de Factura</th>
                    <!-- <th>M Prestado</th> -->
                    <th>Fecha</th>
                    <th>Monto Pagado</th>
                    <th>M Pendiente</th>
                    <th>Generar PDF</th>

                </tr>
                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $fila["id"] ?></td>
                        <!-- <td><?= $fila["monto"] ?></td> -->
                        <td><?= $fila["fecha"] ?></td>
                        <td><?= $fila["monto_pagado"] ?></td>
                        <td><?= $fila["monto_deuda"] ?></td>
                        <td><a href="generar_pdf.php?facturaId=<?= $fila['id'] ?>">Generar PDF</a></td>

                    </tr>
                <?php } ?>
            </table>
        </main>

        <script>
            // Agregar un evento clic al botón
            document.getElementById("volverAtras").addEventListener("click", function() {
                window.history.back();
            });
        </script>

        <script>
            // JavaScript para la búsqueda en tiempo real
            const searchInput = document.getElementById('search-input');
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tbody tr');

            searchInput.addEventListener('input', function() {
                const searchTerm = searchInput.value.toLowerCase();

                rows.forEach((row) => {
                    const rowData = Array.from(row.children)
                        .map((cell) => cell.textContent.toLowerCase())
                        .join('');

                    if (rowData.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>
        <script src="/public/assets/js/MenuLate.js"></script>

    </body>
<?php
} else {
    echo "No se ha proporcionado un ID de cliente válido.";
}
?>

    </html>