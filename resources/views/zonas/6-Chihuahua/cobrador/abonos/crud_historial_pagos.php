<?php
session_start();
require_once '../../../../../../controllers/conexion.php';
include("../../../../../../controllers/verificar_permisos.php");

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../index.php");
    exit();
}

if (!$tiene_permiso_listar_clientes) {
    header("Location: ../../../../../../Nopermiso.html");
    exit();
} else {
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

    $stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    if (!$fila) {
        header("Location: ../inicio/inicio.php");
        exit();
    }

    $rol_usuario = $fila['Nombre'];

    if ($rol_usuario !== 'cobrador') {
        header("Location: ../inicio/inicio.php");
        exit();
    }
}

if (isset($_GET['clienteId'])) {
    $clienteId = $_GET['clienteId'];

    // Consulta para obtener los préstamos pendientes del cliente, ordenados por fecha de inicio
    $sql_prestamos = "SELECT ID, FechaInicio FROM prestamos WHERE IDCliente = ? AND Estado = 'pendiente' ORDER BY FechaInicio";
    $stmt_prestamos = $conexion->prepare($sql_prestamos);
    $stmt_prestamos->bind_param("i", $clienteId);
    $stmt_prestamos->execute();
    $result_prestamos = $stmt_prestamos->get_result();

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Crup pagos</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>

        <header class="bg-white shadow-sm mb-4">

            <div class="container d-flex justify-content-between align-items-center py-2">
                <div class="container mt-3">
                    <a href="../inicio/inicio.php" class="btn btn-secondary">Volver al Inicio</a>
                </div>
                <div class="card" style="max-width: 180px; max-height: 75px;"> <!-- Ajusta el ancho máximo y el alto máximo de la tarjeta según tus preferencias -->
                    <div class="card-body">
                        <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                            <p class="card-text" style="font-size: 15px;"> <!-- Ajusta el tamaño de fuente según tus preferencias -->
                                <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                    <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                                </span>
                                <span style="color: black;"> | </span> <!-- Divisor negro -->
                                <span class="text-primary">cobrador</span> <!-- Texto azul de Bootstrap -->
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="container my-4">
            <h1 class="text-center mb-4">
                <?php
                // Verificar si se ha proporcionado el clienteId en la URL
                if (isset($_GET['clienteId'])) {
                    $clienteId = $_GET['clienteId'];

                    // Consulta SQL para obtener el nombre y apellido del cliente
                    $sql_nombre_apellido = "SELECT nombre, apellido FROM clientes WHERE id = $clienteId";
                    $resultado_nombre_apellido = $conexion->query($sql_nombre_apellido);

                    // Verificar si se encontró el nombre y apellido del cliente
                    if ($fila_nombre_apellido = $resultado_nombre_apellido->fetch_assoc()) {
                        $nombre_cliente = htmlspecialchars($fila_nombre_apellido["nombre"]);
                        $apellido_cliente = htmlspecialchars($fila_nombre_apellido["apellido"]);
                        echo "Historial de Pagos de: " . $nombre_cliente . " " . $apellido_cliente;
                    } else {
                        echo "Cliente no encontrado";
                    }
                } else {
                    echo "No se ha proporcionado un ID de cliente válido.";
                }
                ?>
            </h1>

            <div class="mb-3">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar..." autocomplete="off">
            </div>

            <?php while ($row_prestamo = $result_prestamos->fetch_assoc()) {
                $prestamoId = $row_prestamo["ID"];
                $fechaInicioPrestamo = $row_prestamo["FechaInicio"];

                // Consulta para obtener los pagos relacionados con el préstamo actual
                $sql_pagos = "SELECT id, fecha, monto_pagado, monto_deuda FROM facturas WHERE id_prestamos = ?";
                $stmt_pagos = $conexion->prepare($sql_pagos);
                $stmt_pagos->bind_param("i", $prestamoId);
                $stmt_pagos->execute();
                $result_pagos = $stmt_pagos->get_result();
            ?>

                <h2 class="mb-3">Préstamo (Fecha de Inicio: <?= htmlspecialchars($fechaInicioPrestamo) ?>)</h2>

                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID de Factura</th>
                            <th>Fecha</th>
                            <th>Monto Pagado</th>
                            <th>Monto Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row_pago = $result_pagos->fetch_assoc()) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row_pago["id"]) ?></td>
                                <td><?= htmlspecialchars($row_pago["fecha"]) ?></td>
                                <td><?= htmlspecialchars($row_pago["monto_pagado"]) ?></td>
                                <td><?= htmlspecialchars($row_pago["monto_deuda"]) ?></td>
                                <td>
                                    <a href="generar_pdf.php?facturaId=<?= $row_pago['id'] ?>" class="btn btn-primary btn-sm">Generar PDF</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>
<?php
} else {
    echo "No se ha proporcionado un ID de cliente válido.";
}
?>