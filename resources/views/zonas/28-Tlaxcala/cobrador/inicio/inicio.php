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


// Incluye el archivo de conexión
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

date_default_timezone_set('America/Bogota');

// Ruta de permisos
include("../../../../../../controllers/verificar_permisos.php");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://kit.fontawesome.com/9454e88444.js" crossorigin="anonymous"></script>
    <title>Recaudo</title>
    <link rel="stylesheet" href="/public/assets/css/inicio.css">
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

            <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/inicio.php" class="selected">
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

    <main>
        <h1>Inicio cobrador de Tlaxcala</h1>
        <div class="cuadros-container">

            <!-- ULTIMO ID -->
            <?php
            // Suponiendo que ya tienes una conexión a la base de datos establecida ($conexion)

            function obtenerPrimerIDDeRuta()
            {
                // Construye la ruta al archivo 'ruta.txt' en el mismo directorio del script PHP
                $rutaArchivo = __DIR__ . '/../cartulinaV2/informacionCP/ruta.txt';

                // Verifica si el archivo existe
                if (!file_exists($rutaArchivo)) {
                    return null; // Archivo no encontrado
                }

                // Lee el contenido del archivo
                $contenidoRuta = file_get_contents($rutaArchivo);
                if ($contenidoRuta === false) {
                    return null; // No se pudo leer el archivo
                }

                // Divide el contenido del archivo en un array de IDs y devuelve el primero
                $idsClientesRuta = explode(',', $contenidoRuta);
                return $idsClientesRuta[0] ?? null;
            }

            $primerIDCliente = obtenerPrimerIDDeRuta();
            ?>

            <div class="cuadro cuadro-2">
                <div class="cuadro-1-1">
                    <?php if ($primerIDCliente) : ?>
                        <a href="../cartulinaV2/abonos.php?id=<?php echo $primerIDCliente; ?>" class="titulo">Abonos V2</a>
                        <p>Version beta v2</p>
                    <?php else : ?>
                        <p>No hay clientes disponibles en la ruta.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($tiene_permiso_lista_clavos) : ?>
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/Clavos/ListaClavos.php" class="titulo">Clavos </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_prest_cancelados) : ?>
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/Pcancelados/pcancelados.php" class="titulo">Prest Cancelados </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_ver_filtros) : ?>
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/prestadia/prestamos_del_dia.php" class="titulo">Filtros</a>
                        <p>Version beta</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_comision) : ?>
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/inicio/comision_inicio.php" class="titulo">Comision</a>
                        <p>Version beta</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_desatrasar) : ?>
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/desatrasar/agregar_clientes.php" class="titulo">Desatrasar </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_recaudos) : ?>
                <div class="cuadro cuadro-4">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/recaudos/recuado_admin.php" class="titulo">Recaudos</a><br>
                        <p>Version beta</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($tiene_permiso_gastos) : ?>
                <div class="cuadro cuadro-3">
                    <div class="cuadro-1-1">
                        <a href="/resources/views/zonas/28-Tlaxcala/cobrador/gastos/lista/lista_gastos.php" class="titulo">Gastos</a><br>

                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>


    <script src="/public/assets/js/MenuLate.js"></script>
</body>

</html>