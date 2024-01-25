<?php
session_start();
require_once("../../../../../../controllers/conexion.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

$usuario_id = $_SESSION["usuario_id"];

// Obtener el nombre del usuario
$sql_nombre = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql_nombre);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
if ($fila = $resultado->fetch_assoc()) {
    $_SESSION["nombre_usuario"] = $fila["nombre"];
}
$stmt->close();

// Obtener el cartera_id de la URL
$cartera_id_actual = isset($_GET['id']) ? $_GET['id'] : null;

// Consulta SQL para obtener todos los clientes sin cartera en una zona específica
$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.HistorialCrediticio, c.ReferenciasPersonales, m.Nombre AS Moneda, c.ZonaAsignada 
        FROM clientes c
        LEFT JOIN monedas m ON c.MonedaPreferida = m.ID
        WHERE c.ZonaAsignada = 'Quinatan Roo' AND c.cartera_id IS NULL";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/lista_clientes.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <title>Listado de Clientes</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .buttom {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .buttom:hover {
            background-color: #45a049;
        }

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
    </style>
</head>

<body id="body">
    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>

        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/cartera/clientes_por_cartera.php?id=<?= $cartera_id_actual ?>" class="back-link1">
            Volver
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



    <main>
        <h1>Agregar cliente a cobro</h1>
        <div class="search-container">
            <input type="text" id="search-input" class="search-input" placeholder="Buscar...">
        </div>
        <div class="table-scroll-container">
            <?php if ($resultado->num_rows > 0) { ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Domicilio</th>
                        <th>Teléfono</th>
                        <th>Zona Asignada</th>
                        <th>Agregar</th>
                    </tr>
                    <?php while ($fila = $resultado->fetch_assoc()) { ?>
                        <tr>
                            <td><?= "REC 100" . $fila["ID"] ?></td>
                            <td><?= $fila["Nombre"] ?></td>
                            <td><?= $fila["Apellido"] ?></td>
                            <td><?= $fila["Domicilio"] ?></td>
                            <td><?= $fila["Telefono"] ?></td>
                            <td><?= $fila["ZonaAsignada"] ?></td>
                            <td>
                                <form action="opciones/asignar_cartera.php" method="GET">
                                    <input type="hidden" name="cliente_id" value="<?= $fila['ID'] ?>">
                                    <input type="hidden" name="cartera_id" value="<?= $cartera_id_actual ?>">
                                    <input type="submit" value="Asignar a Cobro" class="buttom">
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } else { ?>
                <p>No se encontraron clientes en la base de datos.</p>
            <?php } ?>
        </div>
    </main>

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

</html>