<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../controllers/conexion.php';

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
if (!$fila || $fila['Nombre'] !== 'supervisor') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}


// Función para obtener la suma de una columna de una tabla
function obtenerSuma($conexion, $tabla, $columna)
{
    $sql = "SELECT SUM($columna) AS Total FROM $tabla";
    $resultado = mysqli_query($conexion, $sql);
    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
        mysqli_free_result($resultado);
        return $fila['Total'] ?? 0; // Devuelve 0 si es null
    } else {
        echo "Error en la consulta: " . mysqli_error($conexion);
        return 0;
    }
}


include("../../../../controllers/verificar_permisos.php");



// Obtener los totales
$totalMonto = obtenerSuma($conexion, "prestamos", "MontoAPagar");
$totalIngresos = obtenerSuma($conexion, "historial_pagos", "MontoPagado");
$totalComisiones = obtenerSuma($conexion, "prestamos", "Comision");

// OBTENER EL TOTAL DE PRESTAMOS 
$sql_total_prestamos = "SELECT COUNT(*) AS total FROM prestamos";
$resultado = $conexion->query($sql_total_prestamos);
$fila = $resultado->fetch_assoc();
$total_prestamos = $fila['total'];

//OBTENER EL TOTAL CLIENTES 
$sql_total_clientes = "SELECT COUNT(*) AS total FROM clientes";
$resultado = $conexion->query($sql_total_clientes);
$fila = $resultado->fetch_assoc();
$total_clientes = $fila['total'];


date_default_timezone_set('America/Bogota');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/public/assets/css/inicio.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>

</head>

<body id="body">

    <body class="bg-light">

        <header>
            <div class="container d-flex justify-content-between align-items-center py-2">

                <!-- Contenedor del select con tamaño ajustable y botones al lado -->
                <div class="d-flex align-items-center">
                    <!-- Botones de Volver y Agregar Retiro con margen significativamente aumentado -->
                    <div style="margin-left: 15px;">
                        <a href="../../../../controllers/cerrar_sesion.php" class="btn btn-outline-primary me-2">Cerrar sesión</a>
                    </div>
                </div>

                <!-- Contenedor de la tarjeta -->
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                            <p class="card-text">
                                <span style="color: #6c757d;">
                                    <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                                </span>
                                <span style="color: black;"> | </span>
                                <span class="text-primary">Supervisor</span>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <h1>Inicio Supervisor</h1>
            <div class="cuadros-container">
                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="../panel/seleccionar_cobrador.php" class="titulo">Ir A Cobradores</a>
                        <p>Version beta</p>
                    </div>
                </div>

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

                $primerIDCliente = $_SESSION['ultimoIDCliente'] ?? obtenerPrimerIDDeRuta();
                ?>

                <?php if ($tiene_permiso_abonos) : ?>
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
                <?php endif; ?>

                <?php if ($tiene_permiso_lista_clavos) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="../../clavos/clavos.php" class="titulo">Clavos V2</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_comision) : ?>
                    <div class="cuadro cuadro-4">
                        <div class="cuadro-1-1">
                            <a href="comision_inicio.php" class="titulo">Comision</a>
                            <p><?php echo "<strong>Total:</strong> <span class='com'>$ " . number_format($totalComisiones, 0, '.', '.') . "</span>"; ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="cuadro cuadro-2">
                    <div class="cuadro-1-1">
                        <a href="../cartera/lista_cartera.php" class="titulo">Cobros</a>
                        <p>Version beta v2</p>
                    </div>
                </div>

                <?php if ($tiene_permiso_contabilidad) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="../contabilidad/contabilidad.php" class="titulo">Contabilidad </a>
                            <p>Version Beta v1</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_desatrasar) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="../desatrasar/agregar_clientes.php" class="titulo">Desatrasar</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botón "VerFiltros" que se mostrará si el usuario tiene el permiso correspondiente -->
                <?php if ($tiene_permiso_ver_filtros) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="prestadia/prestamos_del_dia.php" class="titulo">Filtros </a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_gastos) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../gastos/lista/lista_gastos.php" class="titulo">Gastos</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_list_de_prestamos) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="../creditos/crudPrestamos.php" class="titulo">Listados De Prestamos</a>
                            <p>Total de Préstamos: <?= $total_prestamos ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_listar_clientes) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="../clientes/lista_clientes.php" class="titulo">Listados De Clientes</a>
                            <p>Total de Clientes: <?= $total_clientes ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_usuarios) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../usuarios/crudusuarios.php" class="titulo">Listado De Usuarios</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_prest_cancelados) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="Pcancelados/pcancelados.php" class="titulo">Prestamos Cancelados</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($tiene_permiso_recaudos) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../recaudos/recuado_admin.php" class="titulo">Recaudos</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_usuarios) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../usuarios/registrar.php" class="titulo">Registrar Usuario</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_listar_clientes) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../clientes/agregar_clientes.php" class="titulo">Registrar Clientes</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="cuadro cuadro-3">
                    <div class="cuadro-1-1">
                        <a href="../retiros/retiros.php" class="titulo">Retiros</a>
                        <p>Version beta v2</p>
                    </div>
                </div>

                <div class="cuadro cuadro-3">
                    <div class="cuadro-1-1">
                        <a href="../asignacionClientes/cambio_cliente.php" class="titulo">Asignacion clientes</a>
                        <p>Version beta v2</p>
                    </div>
                </div>

                <div class="cuadro cuadro-3">
                    <div class="cuadro-1-1">
                        <a href="../cobros/cobros.php" class="titulo">Zonas de cobro</a><br>
                        <p>Version beta v2</p>
                    </div>
                </div>

            </div>
        </main>
    </body>

</html>