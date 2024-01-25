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


// Ruta de permisos
include("../../../../../../controllers/verificar_permisos.php");

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
// Obtener los totales
$totalMonto = obtenerSuma($conexion, "prestamos", "MontoAPagar");
$totalIngresos = obtenerSuma($conexion, "historial_pagos", "MontoPagado");
$totalComisiones = obtenerSuma($conexion, "prestamos", "Comision");

// OBTENER EL TOTAL DE PRESTAMOS 
$sql_total_prestamos = "SELECT COUNT(*) AS total FROM prestamos WHERE Zona = 'Tlaxcala'";
$resultado = $conexion->query($sql_total_prestamos);
$fila = $resultado->fetch_assoc();
$total_prestamos = $fila['total'];

//OBTENER EL TOTAL CLIENTES 
$sql_total_clientes = "SELECT COUNT(*) AS total FROM clientes  WHERE ZonaAsignada = 'Tlaxcala'";
$resultado = $conexion->query($sql_total_clientes);
$fila = $resultado->fetch_assoc();
$total_clientes = $fila['total'];


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title>Recaudo</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/public/assets/css/inicio.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>

</head>

<body id="body">

    <body class="bg-light">

        <header class="bg-white shadow-sm mb-4">
            <div class="container d-flex justify-content-between align-items-center py-2">
                <div>
                    <a href="/controllers/cerrar_sesion.php" class="btn btn-outline-primary">Cerrar sesion</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                            <p class="card-text">
                                <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                    <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                                </span>
                                <span style="color: black;"> | </span> <!-- Divisor negro -->
                                <span class="text-primary">Supervisor</span> <!-- Texto azul de Bootstrap -->
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
                            <a href="Clavos/ListaClavos.php" class="titulo">Clavos </a>
                            <p>Version beta</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_comision) : ?>
                    <div class="cuadro cuadro-4">
                        <div class="cuadro-1-1">
                            <a href="comision_inicio.php" class="titulo">Comision</a><br>
                            <p>Version beta</p>
                        </div>
                    </div>

                <?php endif; ?>

                <?php if ($tiene_permiso_desatrasar) : ?>
                    <div class="cuadro cuadro-4">
                        <div class="cuadro-1-1">
                            <a href="../desatrasar/agregar_clientes.php" class="titulo">Desatrasar</a><br>
                            <p>Version beta</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_ver_filtros) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="prestadia/prestamos_del_dia.php" class="titulo">Filtros </a>
                            <p>Version beta</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_gastos) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../gastos/lista/lista_gastos.php" class="titulo">Gastos</a><br>
                            <p>Version beta</p>

                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tiene_permiso_prest_cancelados) : ?>
                    <div class="cuadro cuadro-2">
                        <div class="cuadro-1-1">
                            <a href="Pcancelados/pcancelados.php" class="titulo">Prestamos Cancelados </a>
                            <p>Version beta</p>
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

                <?php if ($tiene_permiso_recaudos) : ?>
                    <div class="cuadro cuadro-4">
                        <div class="cuadro-1-1">
                            <a href="../recaudos/recuado_admin.php" class="titulo">Recaudos</a><br>
                            <p>Version beta</p>
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



                <?php if ($tiene_permiso_usuarios) : ?>
                    <div class="cuadro cuadro-3">
                        <div class="cuadro-1-1">
                            <a href="../usuarios/registrar.php" class="titulo">Registrar Usuario</a>
                            <p>Version beta v2</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>


        <script src="/public/assets/js/MenuLate.js"></script>
    </body>

</html>