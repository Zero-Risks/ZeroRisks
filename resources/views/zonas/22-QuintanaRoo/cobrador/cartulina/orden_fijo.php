<!-- ORDEN FIJO -->

<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit();
}

include("../../../../../../controllers/conexion.php");

// Obtener el ID del préstamo de la URL
$id_prestamo_resaltado = isset($_GET['id_prestamo']) ? $_GET['id_prestamo'] : null;

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

// Consulta para obtener todos los clientes con préstamos pendientes
$sql = "SELECT c.ID, c.Nombre, c.Apellido, c.ZonaAsignada, p.ID as IDPrestamo
        FROM clientes c
        INNER JOIN prestamos p ON c.ID = p.IDCliente
        WHERE p.Estado = 'pendiente'
        AND c.ZonaAsignada = 'Chihuahua'
        AND c.IDUsuario = $usuario_id";


$stmt = $conexion->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['orden'])) {
    $orden = $_POST['orden'];
    file_put_contents('orden_clientes.txt', $orden);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruta fija</title>
    <link rel="stylesheet" href="/public/assets/css/abonosruta.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    <style>
        #lista-pagos tbody tr {
            cursor: move;
        }
    </style>
</head>

<body>

    <?php
    function obtenerPrimerIDDeOrdenFijo()
    {
        $rutaArchivo = 'orden_fijo.txt'; // Asegúrate de que esta ruta sea correcta
        if (file_exists($rutaArchivo)) {
            $contenido = file_get_contents($rutaArchivo);
            $ids = explode(',', $contenido);
            if (count($ids) > 0) {
                return trim($ids[0]); // Devuelve el primer ID, asegurándose de eliminar espacios en blanco
            }
        }
        return null; // Retorna null si el archivo no existe o está vacío
    }

    // Obtener el primer ID de orden_fijo.txt
    $primer_id = obtenerPrimerIDDeOrdenFijo();

    ?>

    <header>
        <?php if ($primer_id !== null) : ?>
            <a href="perfil_abonos.php?id=<?= htmlspecialchars($primer_id) ?>" class="back-link1">Volver</a>
        <?php else : ?>
            <p>No hay clientes disponibles.</p>
        <?php endif; ?>

        <!-- <a href="/resources/views/admin/inicio/cartulina/orden_abonos.php" class="back-link1">Pendintes hoy</a> -->

        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Cobrador<span>";
            }
            ?>
        </div>
    </header>

    <main>
        <h2>Orden de pagos</h2>

        <div id="aviso-guardado" class="aviso">
            Nuevo orden guardado.
        </div><br>

        <div class="table-scroll-container">
            <table id="lista-pagos">
                <thead>
                    <tr>
                        <th>ID Cliente</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>ID Préstamo</th>
                        <th>Estado</th>
                        <th>Ordenar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        $claseResaltada = ($row["IDPrestamo"] == $id_prestamo_resaltado) ? "class='fila-resaltada'" : "";
                        echo "<tr $claseResaltada>";
                        echo "<td>" . htmlspecialchars($row["ID"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Nombre"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Apellido"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["ZonaAsignada"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["IDPrestamo"]) . "</td>";
                        echo "<td class='drag-handle'>|||</td>";
                        echo "</tr>";
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

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
                }
            });

            function guardarCambios() {
                // Capturar los IDs en el nuevo orden
                var idsOrdenados = listaPagos.find('tr').map(function() {
                    return $(this).find("td:first").text();
                }).get();

                // Guardar el nuevo orden en localStorage
                localStorage.setItem('sortableClientesOrder', idsOrdenados.join(','));

                // Enviar el nuevo orden al servidor
                $.ajax({
                    url: 'guardar_orden_fijo.php', // Asegúrate de que esta URL sea correcta
                    type: 'POST',
                    data: {
                        orden: idsOrdenados.join(',')
                    },
                    success: function(response) {
                        // Mostrar el mensaje de confirmación
                        $('#aviso-guardado').fadeIn().delay(3000).fadeOut();
                    }
                });
            }
        });
    </script>


</body>

</html>