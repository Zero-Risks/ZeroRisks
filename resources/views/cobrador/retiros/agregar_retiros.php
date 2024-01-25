<?php
date_default_timezone_set('America/Bogota');
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../index.php");
    exit();
}

require_once '../../../../controllers/conexion.php';

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

// Definir variables e inicializar con valores vacíos
$fecha = date('Y-m-d\TH:i'); // Fecha actual
$monto = $descripcion = "";
$fecha_err = $monto_err = $descripcion_err = "";

$usuarios_con_saldo = [];
$sql_usuarios = "SELECT id, nombre, saldo FROM usuarios WHERE RolID != 1 AND SupervisorID = ?";
$stmt_usuarios = $conexion->prepare($sql_usuarios);
$stmt_usuarios->bind_param("i", $usuario_id);
$stmt_usuarios->execute();
$resultado_usuarios = $stmt_usuarios->get_result();
while ($usuario = $resultado_usuarios->fetch_assoc()) {
    $usuarios_con_saldo[] = $usuario;
}
$stmt_usuarios->close();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Retiro</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <style>
        /* Estilos para el nombre de usuario y rol */
        .nombre-usuario {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            color: blue;
            max-width: 200px;
            word-wrap: break-word;
        }

        .nombre-usuario span {
            color: grey;
        }
    </style>
</head>

<body class="bg-light">

    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">

            <!-- Contenedor del select con tamaño ajustable y botones al lado -->
            <div class="d-flex align-items-center"> 
                <!-- Botones de Volver y Agregar Retiro con margen significativamente aumentado -->
                <div style="margin-left: 15px;">
                    <a href="retiros.php" class="btn btn-outline-primary me-2">Volver</a> 
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
        <h1 class="text-center mb-4">Agregar Retiro</h1>
        <form action="proceso_retiro.php" method="POST" class="card p-4 shadow">
            <div class="form-group">
                <label for="fecha">Fecha:</label>
                <input type="datetime-local" name="fecha" id="fecha" class="form-control" value="<?php echo $fecha; ?>" readonly>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <select name="descripcion" id="descripcion" class="form-control" onchange="mostrarOpcionesSaldo()">
                    <option value="">Seleciona retiro</option>
                    <option value="Retiro de banco">Retiro de banco</option>
                    <option value="Dar saldo">Dar saldo</option>
                </select>
            </div>
            <div class="form-group" id="opcionesSaldo" style="display: none;">
                <label for="usuario_id">Usuario:</label>
                <select name="usuario_id" id="usuario_id" class="form-control" onchange="mostrarOpcionesSaldo()">
                    <?php foreach ($usuarios_con_saldo as $usuario) : ?>
                        <option value="<?php echo $usuario['id']; ?>"><?php echo $usuario['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="saldoMensaje" class="alert alert-light border" role="alert" style="color: #6c757d;"></div>
            </div>
            <div class="form-group">
                <label for="monto">Monto:</label>
                <input type="text" name="monto" id="monto" class="form-control" value="<?php echo $monto; ?>" autocomplete="off">
                <div id="nuevoSaldoMensaje" class="alert alert-light border" role="alert" style="color: #6c757d;"></div>
            </div>
            <div class="form-group text-center">
                <input type="submit" class="btn btn-primary" value="Agregar Retiro">
            </div>
        </form>
    </main>

    <script>
        var usuariosConSaldo = <?php echo json_encode($usuarios_con_saldo); ?>;

        function mostrarOpcionesSaldo() {
            var descripcion = document.getElementById('descripcion').value;
            var opcionesSaldo = document.getElementById('opcionesSaldo');
            var saldoMensaje = document.getElementById('saldoMensaje');
            var nuevoSaldoMensaje = document.getElementById('nuevoSaldoMensaje'); // Obtener el elemento del nuevo saldo
            var usuarioSelect = document.getElementById('usuario_id');

            if (descripcion === 'Dar saldo') {
                opcionesSaldo.style.display = 'block';
                nuevoSaldoMensaje.style.display = 'block'; // Mostrar el mensaje de nuevo saldo
                actualizarMensajeSaldo(usuarioSelect.value);
            } else {
                opcionesSaldo.style.display = 'none';
                nuevoSaldoMensaje.style.display = 'none'; // Ocultar el mensaje de nuevo saldo
                saldoMensaje.innerHTML = '';
            }
        }

        function actualizarMensajeSaldo(usuarioId) {
            var saldoMensaje = document.getElementById('saldoMensaje');
            var usuarioSeleccionado = usuariosConSaldo.find(usuario => usuario.id == usuarioId);
            if (usuarioSeleccionado) {
                saldoMensaje.innerHTML = 'Este usuario ya tiene un saldo de: ' + usuarioSeleccionado.saldo;
            } else {
                saldoMensaje.innerHTML = '';
            }
        }

        function calcularNuevoSaldo() {
            var montoInput = document.getElementById('monto').value;
            var usuarioId = document.getElementById('usuario_id').value;
            var usuarioSeleccionado = usuariosConSaldo.find(usuario => usuario.id == usuarioId);
            var nuevoSaldo = usuarioSeleccionado ? parseFloat(usuarioSeleccionado.saldo) + parseFloat(montoInput) : parseFloat(montoInput);

            var nuevoSaldoMensaje = document.getElementById('nuevoSaldoMensaje');
            nuevoSaldoMensaje.innerHTML = 'Nuevo saldo: ' + nuevoSaldo.toFixed(2);
        }


        // Event listeners
        document.getElementById('usuario_id').addEventListener('change', function() {
            mostrarOpcionesSaldo();
            actualizarMensajeSaldo(this.value);
        });

        document.getElementById('monto').addEventListener('input', calcularNuevoSaldo);
    </script>


    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


</body>

</html>