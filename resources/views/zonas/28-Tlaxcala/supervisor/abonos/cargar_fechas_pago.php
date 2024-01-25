<?php
// Incluye tu archivo de conexión a la base de datos
include("../../../../../../controllers/conexion.php");

// Obtener la fecha actual en el formato de tu base de datos (por ejemplo, 'Y-m-d')
$fecha_actual = date('Y-m-d');

// Verificar si se ha proporcionado una zona válida desde la URL
if (isset($_GET['zona'])) {
    $nombreZona = $_GET['zona'];

    // Consulta SQL para seleccionar las filas de la tabla fechas_pago solo para la zona especificada y la fecha actual
    $sql = "SELECT * FROM fechas_pago WHERE Zona = '$nombreZona' AND FechaPago = '$fecha_actual' ORDER BY IDPrestamo";
    $resultado = $conexion->query($sql);

    $fechas_pago = array();

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $fechas_pago[] = $row;
        }
    } else {
        // Maneja el caso donde no se encontraron filas
        echo "No se encontraron filas para esta zona y fecha.";
    }
} else {
    // Maneja el caso donde no se proporcionó una zona válida
    echo "Zona no especificada.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Fechas de Pago</title>
    <link rel="stylesheet" href="/public/assets/css/abonosruta.css">
</head>
</head>
<body>
    <h1>Lista de Fechas de Pago para la Zona: <?= $nombreZona ?></h1>
    <button onclick="guardarCambios()">Guardar Cambios</button>
    <table id="lista-pagos">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID del Préstamo</th>
                <th>Fecha de Pago</th>
                <th></th> <!-- Espacio para el arrastre -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fechas_pago as $fecha) : ?>
                <tr data-id="<?= $fecha['IDPrestamo'] ?>">
                    <td><?= $fecha['ID'] ?></td>
                    <td><?= $fecha['IDPrestamo'] ?></td>
                    <td><?= $fecha['FechaPago'] ?></td>
                    <td class="drag-handle">|||</td> 
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script>
        // Variable para almacenar el orden actual de la lista
        var ordenActual = [];

        // Agrega el arrastre y suelte a las filas
        $(document).ready(function() {
            $("#lista-pagos tbody").sortable({
                handle: ".drag-handle", // El elemento de arrastre
                cursor: "move", // El cursor se convierte en una mano
                update: function(event, ui) {
                    // Actualiza el orden actual al reordenar
                    ordenActual = obtenerNuevoOrden();
                }
            });
        });

        function guardarCambios() {
            // Guarda el orden actual en el almacenamiento local del navegador
            localStorage.setItem('orden_' + '<?= $nombreZona ?>', JSON.stringify(ordenActual));
            alert("Cambios guardados con éxito.");
        }

        function obtenerNuevoOrden() {
            var nuevoOrden = $("#lista-pagos tbody tr").map(function() {
                return $(this).data("id");
            }).get();
            return nuevoOrden;
        }

        // Cargar el orden almacenado al cargar la página
        var ordenAlmacenado = localStorage.getItem('orden_' + '<?= $nombreZona ?>');
        if (ordenAlmacenado) {
            ordenActual = JSON.parse(ordenAlmacenado);
            // Aplicar el orden almacenado a la lista
            var $tbody = $("#lista-pagos tbody");
            var rows = $tbody.find("tr").get();
            $tbody.html(""); // Vacía el tbody
            for (var i = 0; i < ordenActual.length; i++) {
                var idPrestamo = ordenActual[i];
                var $row = rows.find(function(row) {
                    return $(row).data("id") == idPrestamo;
                });
                $tbody.append($row);
            }
        }
    </script> 
</body>
</html>
