<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

include("../../../../controllers/conexion.php");

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

// Inicializar variables para almacenar los valores de monto y monto_neto
$monto = 0;
$monto_neto = 0;

// Consulta SQL para obtener los datos del usuario específico
$sql = "SELECT Monto, Monto_Neto FROM saldo_admin WHERE IDUsuario = ?";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    // Enlazar el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Si esperas solo un registro por usuario, puedes hacer directamente fetch_assoc()
        $row = $result->fetch_assoc();
        $monto = $row["Monto"];
        $monto_neto = $row["Monto_Neto"];
    } else {
    }

    $stmt->close();
} else {
    echo "Error en la consulta: " . $conexion->error;
}

// Consulta SQL para obtener el saldo del usuario específico
$sql = "SELECT saldo FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    // Enlazar el parámetro y ejecutar la consulta
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $saldo = $row["saldo"];
    } else {
        // Manejar el caso en que el usuario no tiene saldo registrado
        $saldo = 0;
    }

    $stmt->close();
} else {
    echo "Error en la consulta: " . $conexion->error;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiros</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">

            <!-- Contenedor del select con tamaño ajustable y botones al lado -->
            <div class="d-flex align-items-center">
                <!-- Select -->
                <div class="d-flex align-items-center">
                    <select name="" id="Lista_total" class="form-control">
                        <option value="">Retiros</option>
                        <option value="../gastos/lista/lista_gastos.php">Gastos</option>
                        <option value="../inicio/recaudos/recuado_admin.php">Recaudo</option>
                    </select>
                </div>

                <!-- Botones de Volver y Agregar Retiro con margen significativamente aumentado -->
                <div style="margin-left: 15px;">
                    <a href="../inicio/inicio.php" class="btn btn-outline-primary me-2">Volver</a>
                    <a href="agregar_retiros.php" class="btn btn-outline-primary">Agregar retiro</a>
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
                            <span class="text-primary">Administrator</span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('Lista_total').addEventListener('change', function() {
                var url = this.value;
                if (url) {
                    window.location.href = url;
                }
            });
        </script>
    </header>

    <main class="container">
        <h1 class="text-center mb-4">Lista de Retiros</h1>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="bg-white p-3 shadow-sm">
                    <h2>Saldo Inicial</h2>
                    <p>$<?php echo number_format($monto, 2, '.', '.'); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-white p-3 shadow-sm">
                    <h2>Saldo Actual</h2>
                    <p>$<?php echo number_format($saldo, 2, '.', '.'); ?></p>
                </div>
            </div>
        </div>

        <?php
        $sql = "SELECT retiros.ID, retiros.Fecha, retiros.Monto, retiros.descripcion, usuarios.nombre, usuarios.apellido 
FROM retiros
JOIN usuarios ON retiros.IDUsuario = usuarios.id
WHERE IDUsuario = $usuario_id";
        $result = $conexion->query($sql);

        if ($result->num_rows > 0) {
            // Iniciar la tabla HTML con clases de Bootstrap
            echo "<div class='table-responsive'>";
            echo "<table class='table table-bordered table-hover'>";
            echo "<thead class='thead-light'>";
            echo "<tr><th>ID</th><th>Usuario</th><th>Fecha</th><th>Monto</th><th>Descripción</th></tr>";
            echo "</thead>";
            echo "<tbody>";

            // Recorrer los resultados y mostrar cada fila en la tabla
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["ID"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["nombre"]) . " " . htmlspecialchars($row["apellido"]) . "</td>";
                $descripcion = !is_null($row['descripcion']) ? htmlspecialchars($row['descripcion']) : 'Sin descripción';
                echo "<td>" . htmlspecialchars($row["Fecha"]) . "</td>";
                echo "<td>$" . number_format($row["Monto"], 2) . "</td>";
                echo "<td>" . $descripcion . "</td>";
                // echo "<td><a href='editar_retiros.php?id=" . $row["ID"] . "' class='btn btn-primary btn-sm'>Editar</a></td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p>No se encontraron resultados.</p>";
        }

        $conexion->close();
        ?>



    </main>

    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>