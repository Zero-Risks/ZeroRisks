<?php
date_default_timezone_set('America/Bogota');
session_start();

// Validación de rol para ingresar a la página 
require_once '../../../../controllers/conexion.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
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
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }

    // Extrae el nombre del rol del resultado
    $rol_usuario = $fila['Nombre'];

    // Verifica si el rol del usuario corresponde al necesario para esta página
    if ($rol_usuario !== 'admin') {
        // El usuario no tiene el rol correcto, redirige a la página de error o de inicio
        header("Location: /ruta_a_pagina_de_error_o_inicio.php");
        exit();
    }
}

// Verificar si se ha proporcionado un ID de usuario para modificar
if (isset($_GET['id'])) {
    $usuario_id_modificar = $_GET['id'];

    // Verificar si se ha enviado un formulario de modificación
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recuperar los datos del formulario
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $email = $_POST['email'];
        $zona = $_POST['zona'];
        $rolID = $_POST['rolID'];
        $contrasena = $_POST['contrasena'];

        // Preparar la consulta SQL para actualizar los datos del usuario
        $sql = "UPDATE usuarios SET Nombre = ?, Apellido = ?, Email = ?, Zona = ?, RolID = ?, Password = ? WHERE ID = ?";
        $stmt = $conexion->prepare($sql);
        if (!$stmt) {
            die("Error en la consulta SQL: " . $conexion->error);
        }

        if (!$stmt->bind_param("ssssisi", $nombre, $apellido, $email, $zona, $rolID, $contrasena, $usuario_id_modificar)) {
            die("Error en la vinculación de parámetros: " . $stmt->error);
        }


        if ($stmt->execute()) {
            header("location: crudusuarios.php?mensaje=Usuario modificado con éxito");
            exit();
        } else {
            die("Error en la ejecución de la consulta: " . $stmt->error);
        }
    } else {
        // Consultar la información del usuario para mostrarla en el formulario de modificación
        $sql = "SELECT * FROM usuarios WHERE ID = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id_modificar);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
        } else {
            // Usuario no encontrado, redirigir a la lista de usuarios
            header("location: crudusuarios.php?mensaje=Usuario no encontrado");
            exit();
        }
    }
} else {
    // ID de usuario no proporcionado, redirigir a la lista de usuarios
    header("location: crudusuarios.php?mensaje=ID de usuario no proporcionado");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/public/assets/css/modificarUSER.css">

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
                    <span class="text-primary">Admin</span> <!-- Texto azul de Bootstrap -->
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
</header>

    <main class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">
                        <h1 class="card-title text-center">Modificar Usuario</h1>
                        <h2 class="card-subtitle text-center">Complete el formulario</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje)) : ?>
                            <div class="alert alert-success" role="alert">
                                <?= $mensaje ?>
                            </div>
                        <?php endif; ?>
                        <form method="post">
                            <!-- Campos del formulario -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre:</label>
                                <input type="text" class="form-control" name="nombre" value="<?= $usuario['Nombre'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido:</label>
                                <input type="text" class="form-control" name="apellido" value="<?= $usuario['Apellido'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" value="<?= $usuario['Email'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="zona" class="form-label">Zona:</label>
                                <input type="text" class="form-control" name="zona" value="<?= $usuario['Zona'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="rolID" class="form-label">Rol:</label>
                                <input type="text" class="form-control" name="rolID" value="<?= $usuario['RolID'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña:</label>
                                <input type="password" class="form-control" name="contrasena" value="<?= $usuario['Password'] ?>" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>