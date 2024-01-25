<!-- Dashboard del Supervisor -->

<?php
session_start();
require_once '../../../../../../controllers/conexion.php';

// Asegúrate de que solo los supervisores puedan acceder a esta página
if ($_SESSION["rol"] != 2) {
    header("Location: /error_o_inicio.php");
    exit();
}

$supervisor_id = $_SESSION['usuario_id']; // ID del supervisor
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Supervisor</title>
    <!-- Incluir el enlace a Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="bg-white shadow-sm mb-4">
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a class="navbar-brand" href="../inicio/inicio.php">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
            </div>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION["nombre_usuario"])) : ?>
                        <p class="card-text">
                            <span style="color: #6c757d;"> <!-- Gris de Bootstrap, puedes ajustar el código de color según sea necesario -->
                                <?php echo htmlspecialchars($_SESSION["nombre_usuario"]); ?>
                            </span>
                            <span style="color: black;"> | </span> <!-- Divisor negro -->
                            <span class="text-primary">Supervisor</span> <!-- Texto azul de Bootstrap -->
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center">Selecciona un Cobrador</h2>
                    </div>
                    <div class="card-body">
                        <form action="cambiar_a_cobrador.php" method="post">
                            <div class="form-group">
                                <label for="cobrador_id">Selecciona un cobrador:</label>
                                <select name="cobrador_id" id="cobrador_id" class="form-control">
                                    <?php
                                    $query = "SELECT ID, Nombre FROM usuarios WHERE SupervisorID = ?";
                                    if ($stmt = $conexion->prepare($query)) {
                                        $stmt->bind_param("i", $supervisor_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['ID'] . "'>" . $row['Nombre'] . "</option>";
                                        }
                                        $stmt->close();
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Iniciar sesión como Cobrador</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Incluir los scripts de Bootstrap al final del documento -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.7.0/dist/js/bootstrap.min.js"></script>
</body>

</html>