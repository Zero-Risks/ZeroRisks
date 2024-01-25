<?php
date_default_timezone_set('America/Bogota');
session_start();

require_once '../../../../../../../controllers/conexion.php';

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

require_once '../../../../../../../controllers/conexion.php';

function actualizarFechaHora()
{
    return date('Y-m-d H:i:s');
}

function existeGastoConNombreYFecha($conexion, $nombreZona, $fechaActual)
{
    $sql = "SELECT COUNT(*) FROM gastos WHERE Nombre = ? AND Fecha LIKE CONCAT(?, '%')";
    if ($stmt = $conexion->prepare($sql)) {
        $fechaSinHora = explode(' ', $fechaActual)[0];
        $stmt->bind_param("ss", $nombreZona, $fechaSinHora);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_row();
        $stmt->close();
        return $fila[0] > 0;
    }
    return false;
}

function obtenerSaldoUsuario($conexion, $usuarioId)
{
    $sql = "SELECT saldo FROM usuarios WHERE id = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            return $fila['saldo'];
        }
        $stmt->close();
    }
    return 0;
}

function actualizarSaldoUsuario($conexion, $usuarioId, $nuevoSaldo)
{
    $sql = "UPDATE usuarios SET saldo = ? WHERE id = ?";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("di", $nuevoSaldo, $usuarioId);
        $stmt->execute();
        $stmt->close();
    }
}

$gastoDuplicado = false;

// Recuperar el saldo del usuario
$saldoUsuario = obtenerSaldoUsuario($conexion, $_SESSION['usuario_id']);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $idZona = $_POST['zona'];
    $fechaActual = actualizarFechaHora();

    $consultaNombreZona = "SELECT Nombre FROM zonas WHERE ID = ?";
    if ($stmt = $conexion->prepare($consultaNombreZona)) {
        $stmt->bind_param("i", $idZona);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $nombreZona = $fila['Nombre'];
        $stmt->close();
    }

    if (!existeGastoConNombreYFecha($conexion, $nombreZona, $fechaActual)) {
        $gasolina = $_POST['gasolina'];
        $viaticos = $_POST['viaticos'];
        $otros = $_POST['otros'];
        $avance = $_POST['avance'];
        $devuelta = $_POST['devuelta'];
        $gastosTotales = $gasolina + $viaticos + $otros;
        $saldo = $avance - $gastosTotales;
        $diferencia = ($devuelta + $gastosTotales) - $avance;

        $sql = "INSERT INTO gastos (Nombre, IDUsuario, IDZona, Fecha, GastoTotal, Saldo, Diferencia, Gasolina, Viaticos, Otros, Avance, Devuelta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("siisdddddddd", $nombreZona, $_SESSION['usuario_id'], $idZona, $fechaActual, $gastosTotales, $saldo, $diferencia, $gasolina, $viaticos, $otros, $avance, $devuelta);
            if ($stmt->execute()) {
                // Actualizar saldo del usuario
                $saldoActual = obtenerSaldoUsuario($conexion, $_SESSION['usuario_id']);
                $nuevoSaldo = $saldoActual - $gastosTotales;
                actualizarSaldoUsuario($conexion, $_SESSION['usuario_id'], $nuevoSaldo);
                header("Location: ../lista/lista_gastos.php");
                exit();
            } else {
                echo "<p>Error al agregar el gasto: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Error de consulta: " . $conexion->error . "</p>";
        }
    } else {
        $gastoDuplicado = true;
    }
    $conexion->close();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="agregar_gasto.css">
    <title>Añadir Gasto</title>
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
                    <span class="text-primary">Cob</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <div class="content">
        <?php if ($gastoDuplicado) : ?>
            <div class="modal-background" id="modal">
                <div class="modal-content">
                    <p>Gasto registrado anteriormente.</p>
                    <button onclick="closeModal()">Aceptar</button>
                </div>
            </div>
            <script>
                function closeModal() {
                    window.location.href = '../lista/lista_gastos.php'; // Redirecciona a la lista de gastos
                }

                window.onload = function() {
                    if (document.getElementById('modal')) {
                        document.getElementById('modal').style.display = 'block';
                    }
                };
            </script>

        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="header">
                <div class="input-group">
                    <label><strong>Cobro:</label>
                    <select name="zona" id="zona" class="zona">
                        <option value=""> --------- </option>
                        <?php
                        $consultaZonas = "SELECT ID, Nombre FROM zonas WHERE Nombre = 'Chihuahua'";
                        $resultZonas = mysqli_query($conexion, $consultaZonas);
                        while ($row = mysqli_fetch_assoc($resultZonas)) {
                            echo '<option value="' . $row['ID'] . '">' . $row['Nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group">
                    <label><strong>Fecha y Hora:</label>
                    <input type="text" id="fecha-actual" name="fecha" value="<?php echo actualizarFechaHora(); ?>" disabled>
                </div>
            </div>

            <div class="section-title">Gastos</div>
            <div class="data-row">
                <div class="input-group">
                    <label>Gasolina:</label>
                    <input type="number" name="gasolina" id="gasolina" value="0">
                </div>
                <div class="input-group">
                    <label>Viaticos:</label>
                    <input type="number" name="viaticos" id="viaticos" value="0">
                </div>
                <div class="input-group">
                    <label>Otros:</label>
                    <input type="number" name="otros" id="otros" value="0">
                </div>
            </div>

            <div class="section-title"></div>
            <div class="data-row">
                <div class="input-group">
                    <label>Avance:</label>
                    <input type="number" name="avance" id="avance" value="<?php echo htmlspecialchars($saldoUsuario); ?>" readonly>
                </div>
                <div class="input-group">
                    <label>Devuelta:</label>
                    <input type="number" name="devuelta" id="devuelta" value="0">
                </div>
            </div>

            <div class="section-title">Totales</div>
            <div class="data-row">
                <div class="input-group">
                    <label>Gastos:</label>
                    <input type="text" id="total-gastos" value="$0.00" disabled>
                </div>
                <div class="input-group">
                    <label>Diferencia:</label>
                    <input type="text" id="diferencia" value="$0.00" disabled>
                </div>
                <div class="input-group">
                    <label>Saldo:</strong></label>
                    <input type="text" id="saldo" value="$0.00" disabled>
                </div>
            </div>

            <div class="buttons">
                <button type="submit" name="submit" class="boton">Grabar</button>
            </div>


            <script>
                function calcularTotales() {
                    var gasolina = parseFloat(document.getElementById('gasolina').value) || 0;
                    var viaticos = parseFloat(document.getElementById('viaticos').value) || 0;
                    var otros = parseFloat(document.getElementById('otros').value) || 0;
                    var avance = parseFloat(document.getElementById('avance').value) || 0;
                    var devuelta = parseFloat(document.getElementById('devuelta').value) || 0;

                    var gastosTotales = gasolina + viaticos + otros;
                    var saldo = avance - gastosTotales;
                    var diferencia = (devuelta + gastosTotales) - avance;

                    document.getElementById('total-gastos').value = '$' + gastosTotales.toFixed(2);
                    document.getElementById('saldo').value = '$' + saldo.toFixed(2);
                    document.getElementById('diferencia').value = '$' + diferencia.toFixed(2);
                }

                // Agregar event listeners
                ['gasolina', 'viaticos', 'otros', 'avance', 'devuelta'].forEach(id => {
                    document.getElementById(id).addEventListener('input', calcularTotales);
                });

                // Calcular totales inicialmente
                calcularTotales();
            </script>

        </form>
    </div>
</body>

</html>