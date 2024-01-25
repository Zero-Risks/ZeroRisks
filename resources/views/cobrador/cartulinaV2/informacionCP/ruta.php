<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}
// Incluye el archivo informacion_ruta.php que contiene la función obtenerClientesConPrestamoPendiente
include '../informacionCP/informacion_ruta.php';

// Llama a la función para obtener los datos de los clientes con préstamos pendientes
$clientesConPrestamoPendiente = obtenerClientesConPrestamoPendiente($_SESSION["usuario_id"]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Información de Ruta</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    <!-- Estilos personalizados -->
    <style>
        .drag-handle {
            cursor: move;
        }

        .aviso-guardado {
            display: none;
            border: 2px solid #28a745;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <?php
                // Suponiendo que ya tienes una conexión a la base de datos establecida ($conexion)

                function obtenerPrimerIDDeRuta()
                {
                    // Construye la ruta al archivo 'ruta.txt' en el mismo directorio del script PHP
                    $rutaArchivo = __DIR__ . '/ruta.txt';

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

                <?php if ($primerIDCliente) : ?>
                    <a href="../abonos.php?id=<?php echo $primerIDCliente; ?>" class="titulo">Volver Atrás</a>
                <?php endif; ?>

                <h1 class="my-4 text-center">Información de Ruta</h1>

                <div id="aviso-guardado" class="alert alert-success aviso-guardado">
                    Nuevo orden guardado.
                </div>
                <div class="container mt-4">
                    <?php if ($clientesConPrestamoPendiente) { ?>
                        <div class="table-responsive">
                            <table id="lista-pagos" class="table table-light table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Apellido</th>
                                        <th>ID Préstamo</th>
                                        <th>Mover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientesConPrestamoPendiente as $cliente) { ?>
                                        <tr>
                                            <td><?php echo $cliente['ID']; ?></td>
                                            <td><?php echo $cliente['Nombre']; ?></td>
                                            <td><?php echo $cliente['Apellido']; ?></td>
                                            <td><?php echo $cliente['PrestamoID']; ?></td>
                                            <td class="drag-handle">|||</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <p>No se encontraron clientes con préstamos pendientes.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const listaPagos = $("#lista-pagos tbody");

            // Recuperar el orden almacenado en el localStorage, si existe
            const savedOrder = localStorage.getItem('sortableClientesOrder');
            if (savedOrder) {
                // Reordenar los elementos de la tabla según el orden guardado
                const idsOrdenados = savedOrder.split(',');
                idsOrdenados.forEach(id => {
                    const row = listaPagos.find('tr').filter(function() {
                        return $(this).find('td:first').text() === id;
                    });
                    listaPagos.append(row);
                });
            }

            // Habilitar la función de arrastrar en la tabla
            listaPagos.sortable({
                helper: 'clone',
                axis: 'y',
                opacity: 0.5,
                update: function(event, ui) {
                    guardarCambios();

                    actualizarContador();
                }
            });

            function guardarCambios() {
                // Capturar los IDs en el nuevo orden
                var idsOrdenados = listaPagos.find('tr').map(function() {
                    return $(this).find("td:first").text();
                }).get();

                // Guardar el nuevo orden en localStorage
                localStorage.setItem('sortableClientesOrder', idsOrdenados.join(','));

                // Mostrar el mensaje "Nuevo orden guardado"
                $('#aviso-guardado').fadeIn().delay(3000).fadeOut();

                // Guardar el nuevo orden en ruta.txt
                $.ajax({
                    url: 'guardar_orden.php', // Ruta al script de guardado en el servidor
                    type: 'POST',
                    data: {
                        orden: idsOrdenados.join(',')
                    },
                    success: function(response) {
                        // Manejar la respuesta del servidor si es necesario
                        console.log(response);
                    },
                    error: function() {
                        console.log('Error al guardar el orden en ruta.txt.');
                    }
                });
            }
        });
    </script>

</body>

</html>