<?php
// Conexión a la base de datos
require_once "../../../../../controllers/conexion.php";

// Si se selecciona un usuario desde el filtro, usar su ID. Si no, usar el ID del usuario de la sesión.
$idUsuarioFiltrado = $_GET['idUsuario'] ?? $_SESSION['usuario_id'] ?? null;

function obtenerClientesConPrestamoPendiente($conexion, $idUsuario)
{
    $sql = "SELECT c.ID, c.Nombre, c.Apellido, c.IDUsuario, p.ID AS PrestamoID
            FROM clientes c
            LEFT JOIN prestamos p ON c.ID = p.IDCliente
            WHERE p.Estado = 'pendiente' AND c.IDUsuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $clientes = [];
    while ($fila = $resultado->fetch_assoc()) {
        $clientes[] = $fila;
    }
    $stmt->close();
    return $clientes;
}

$clientesConPrestamoPendiente = obtenerClientesConPrestamoPendiente($conexion, $idUsuarioFiltrado);

$usuarios = [];
$consultaUsuarios = $conexion->query("SELECT ID, Nombre, Apellido FROM usuarios");
if ($consultaUsuarios) {
    while ($fila = $consultaUsuarios->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}
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

                $primerIDCliente = obtenerPrimerIDDeRuta();
                ?>
                <a href="../abonos.php?id=<?php echo $primerIDCliente; ?>" class="titulo">Volver Atrás</a>

                <h1 class="my-4 text-center">Ruta de Usuarios</h1>

                <!-- Campo de filtro con lista desplegable -->
                <div class="form-group">
                    <select class="form-control" id="filtroUsuario">
                        <option value="">Seleccione un usuario...</option>
                        <?php foreach ($usuarios as $usuario) { ?>
                            <option value="<?php echo $usuario['ID']; ?>">
                                <?php echo $usuario['Nombre'] . " " . $usuario['Apellido']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

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
                                        <th>Usuario</th>
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
                                            <td><?php echo $cliente['IDUsuario']; ?></td>
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

        // Habilitar la función de arrastrar y soltar en la tabla
        listaPagos.sortable({
            helper: 'clone',
            axis: 'y',
            opacity: 0.5,
            update: function(event, ui) {
                guardarCambios();
            }
        });

        // Guardar los cambios en el orden de los elementos
        function guardarCambios() {
            // Capturar los IDs en el nuevo orden
            var idsOrdenados = listaPagos.find('tr').map(function() {
                return $(this).find("td:first").text();
            }).get();

            // Guardar el nuevo orden en localStorage
            localStorage.setItem('sortableClientesOrder', idsOrdenados.join(','));

            // Mostrar el mensaje "Nuevo orden guardado"
            $('#aviso-guardado').fadeIn().delay(3000).fadeOut();

            // Guardar el nuevo orden en el servidor
            $.ajax({
                url: 'guardar_orden.php', // Ruta al script de guardado en el servidor
                type: 'POST',
                data: {
                    orden: idsOrdenados.join(',')
                },
                success: function(response) {
                    console.log(response);
                },
                error: function() {
                    console.log('Error al guardar el orden en ruta.txt.');
                }
            });
        }

        // Cambio en el filtro de usuarios
        $("#filtroUsuario").on("change", function() {
            var idUsuarioSeleccionado = $(this).val() || '';
            window.location.href = 'filtro_abonos.php?idUsuario=' + idUsuarioSeleccionado; // Asegúrate de ajustar esta URL
        });
    });
</script>


</body>

</html>