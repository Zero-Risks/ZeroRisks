<?php
session_start();
require_once '../../../../../../controllers/conexion.php';

// Asegúrate de que solo los administradores puedan acceder a esta página
if ($_SESSION["rol"] != 2) {
    header("Location: /error_o_inicio.php");
    exit();
}

// Obtener tanto supervisores como cobradores
$queryUsuarios = "SELECT ID, Nombre, RolID FROM usuarios WHERE Estado = 'activo' AND RolID = 3";
$resultUsuarios = $conexion->query($queryUsuarios);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center">Cambiar a Usuario</h2>
                        <form action="cambiar_usuario.php" method="post">
                            <div class="form-group">
                                <label for="usuario_id">Seleccionar Usuario:</label>
                                <select name="usuario_id" id="usuario_id" class="form-control">
                                    <?php
                                    while ($usuario = $resultUsuarios->fetch_assoc()) {
                                        $rol = $usuario['RolID'] == 3 ? 'Cobrador' : '';
                                        echo "<option value='" . $usuario['ID'] . "'>" . $usuario['Nombre'] . " - " . $rol . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Iniciar sesión como Usuario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias (Opcional, para componentes de Bootstrap como modales, dropdowns, etc.) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>