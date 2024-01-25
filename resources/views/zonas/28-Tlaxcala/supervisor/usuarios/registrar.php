<?php
session_start();

// Validacion de rol para ingresar a la pagina 
require_once '../../../../../../controllers/conexion.php';

// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../../index.php");
    exit();

    //PERMISOS PARA VER LOS LISTADOS DE CLIENTES Y REGISTRAR CLIENTES
}
//PERMISOS A VER DESATRASAR
if (!$tiene_permiso_desatrasar) {
    // El usuario no tiene el permiso, redirige a una página de error o de inicio
    header("Location: ../../../../../../Nopermiso.html");
    exit();
} else {
    // El usuario está autenticado, obtén el ID del usuario de la sesión
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

    // Preparar la consulta para obtener el rol del usuario
    $stmt = $conexion->prepare("SELECT roles.Nombre FROM usuarios INNER JOIN roles ON usuarios.RolID = roles.ID WHERE usuarios.ID = ?");
    $stmt->bind_param("i", $usuario_id);

    // Ejecutar la consulta
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fila = $resultado->fetch_assoc();

    // Verifica si el resultado es nulo, lo que significaría que el usuario no tiene un rol válido
    if (!$fila) {
        // Redirige al usuario a una página de error o de inicio
        header("Location: ../inicio/inicio.php");
        exit();
    }

    // Extrae el nombre del rol del resultado
    $rol_usuario = $fila['Nombre'];

    // Verifica si el rol del usuario corresponde al necesario para esta página
    if ($rol_usuario !== 'supervisor') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: ../inicio/inicio.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <!-- Bootstrap CSS -->

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/assets/css/registrar_usuarios.css">


</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-2">
            <div>
                <a href="../inicio/inicio.php" class="btn btn-outline-primary">Volver</a>
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

    <main class="container mt-4">
        <h2 class="text-center">Registro de Usuario</h2>
        <form action="/controllers/super/validar_registro/validar_registro-28.php" method="post">
            <div class="form-row">
                <div class="col-md-6 mb-4">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingresa tu nombre" oninput="this.value = this.value.toUpperCase()" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ingresa tu apellido" oninput="this.value = this.value.toUpperCase()" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Ingresa tu correo" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Ingresa tu clave" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="zona">Estado:</label>
                    <select id="zona" name="zona" class="form-control" required>
                        <?php
                        // Incluye el archivo de conexión a la base de datos
                        include("../../../../../../controllers/conexion.php");
                        // Consulta SQL para obtener las zonas
                        $consultaZonas = "SELECT ID, Nombre FROM zonas WHERE Nombre = 'Tlaxcala'";
                        $resultZonas = mysqli_query($conexion, $consultaZonas);
                        // Genera las opciones del menú desplegable para Zona
                        while ($row = mysqli_fetch_assoc($resultZonas)) {
                            echo '<option value="' . $row['ID'] . '">' . $row['Nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6 mb-4">
                    <label for="RolID">Rol:</label>
                    <select id="RolID" name="RolID" class="form-control" required>
                        <?php
                        // Consulta SQL para obtener las opciones de roles
                        $consultaRoles = "SELECT iD, nombre FROM roles WHERE iD = 3";
                        $resultRoles = mysqli_query($conexion, $consultaRoles);
                        // Genera las opciones del menú desplegable para Rol
                        while ($row = mysqli_fetch_assoc($resultRoles)) {
                            echo '<option value="' . $row['iD'] . '">' . $row['nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12 text-center">
                    <button type="submit" name="registrar_usuario" class="btn btn-primary mt-3">Registrar</button>
                </div>
            </div>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>