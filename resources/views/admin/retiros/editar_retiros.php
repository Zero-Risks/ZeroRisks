<?php
date_default_timezone_set('America/Bogota');
session_start();

// Verifica la autenticación del usuario
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

include "../../../../controllers/conexion.php";

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

$fecha = $monto = $descripcion = "";
$fecha_err = $monto_err = $descripcion_err = "";
$monto_original = 0;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $retiro_id = $_GET["id"];

    $sql = "SELECT Fecha, Monto, Descripcion FROM Retiros WHERE ID = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $retiro_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $fecha = $row["Fecha"];
                $monto = $row["Monto"];
                $descripcion = $row["Descripcion"];
                $monto_original = $monto; // Guardar el valor original del monto del retiro
            } else {
                header("location: algun_lugar.php");
                exit();
            }
        } else {
            echo "Error al ejecutar la consulta.";
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Procesamiento de los datos del formulario...
    $retiro_id = $_POST['retiro_id'];
    $fecha = $_POST['fecha'];
    $monto = $_POST['monto'];
    $descripcion = $_POST['descripcion'];

    // Validación de los datos del formulario...

    if (empty($fecha_err) && empty($monto_err) && empty($descripcion_err)) {
        // Obtener el monto original del retiro
        $sql_monto_original = "SELECT Monto FROM Retiros WHERE ID = ?";
        if ($stmt_monto_original = $conexion->prepare($sql_monto_original)) {
            $stmt_monto_original->bind_param("i", $retiro_id);
            $stmt_monto_original->execute();
            $stmt_monto_original->store_result();
            if ($stmt_monto_original->num_rows == 1) {
                $stmt_monto_original->bind_result($monto_original);
                $stmt_monto_original->fetch();
            }
            $stmt_monto_original->close();
        }

        // Actualizar el retiro en la base de datos
        $sql_actualizar_retiro = "UPDATE Retiros SET Fecha = ?, Monto = ?, Descripcion = ? WHERE ID = ?";
        if ($stmt_actualizar_retiro = $conexion->prepare($sql_actualizar_retiro)) {
            $stmt_actualizar_retiro->bind_param("sssi", $fecha, $monto, $descripcion, $retiro_id);
            if ($stmt_actualizar_retiro->execute()) {
                // Calcular la diferencia entre el monto original y el monto editado
                $diferencia_monto = $monto_original - $monto;

                // Actualizar el saldo neto con la diferencia
                $sql_update_saldo = "UPDATE saldo_admin SET Monto_Neto = Monto_Neto + ?";
                if ($stmt_update_saldo = $conexion->prepare($sql_update_saldo)) {
                    $stmt_update_saldo->bind_param("d", $diferencia_monto);
                    $stmt_update_saldo->execute();
                    $stmt_update_saldo->close();
                }

                // Redirección a la lista de retiros después de actualizar
                header("location: retiros.php?mensaje=Edición exitosa");
                exit();
            } else {
                echo "Error al actualizar el retiro.";
            }
            $stmt_actualizar_retiro->close();
        }
    }

    $conexion->close();
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Editar Retiro</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
   
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
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <main class="container">
        <h1 class="text-center my-4">Editar Retiro</h1>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="card p-4 shadow">
            <input type="hidden" name="retiro_id" value="<?php echo $retiro_id; ?>">

            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="datetime-local" name="fecha" id="fecha" class="form-control" value="<?php echo $fecha; ?>" readonly>
                <span class="help-block text-danger"><?php echo $fecha_err; ?></span>
            </div>

            <div class="form-group">
                <label for="monto">Monto:</label>
                <input type="text" name="monto" id="monto" class="form-control" value="<?php echo $monto; ?>">
                <span class="help-block text-danger"><?php echo $monto_err; ?></span>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" class="form-control" readonly><?php echo $descripcion; ?></textarea>
                <span class="help-block text-danger"><?php echo $descripcion_err; ?></span>
            </div>

            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Guardar Cambios">
            </div>
        </form>
    </main>

    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>