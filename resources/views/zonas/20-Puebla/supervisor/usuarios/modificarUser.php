<?php
session_start();
include("../../../../controllers/conexion.php");
include("../../../../../../controllers/verificar_permisos.php");

// Verifica si el usuario está autenticado
if (isset($_SESSION["usuario_id"])) {
    // El usuario está autenticado, puede acceder a esta página
} else {
    // El usuario no está autenticado, redirige a la página de inicio de sesión
    header("Location: ../../../../index.php");
    exit();
} //PERMISO REGISTRO DE USUARIOS Y CRUDS USUARIOS 
if (!$tiene_permiso_usuarios) {
    // El usuario no tiene el permiso, redirige a una página de error o de inicio
    header("Location: ../../../../../../Nopermiso.html");
    exit();
}


include "../../../../../../controllers/conexion.php";

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


// Verificar si se ha proporcionado un ID de usuario para modificar
if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Verificar si se ha enviado un formulario de modificación
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recuperar los datos del formulario
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $email = $_POST['email'];
        $zona = $_POST['zona'];
        $rolID = $_POST['rolID'];
        $nuevaContrasena = $_POST['Password'];

        // Preparar la consulta SQL
        $sql = "UPDATE usuarios SET Nombre = ?, Apellido = ?, Email = ?, Zona = ?, RolID = ?";
        $params = "ssssi";
        $values = array($nombre, $apellido, $email, $zona, $rolID);

        // Verificar si se ha proporcionado una nueva contraseña
        if (!empty($nuevaContrasena)) {
            // Cifrar la nueva contraseña (puedes usar password_hash o tu algoritmo preferido)
            $contrasenaCifrada = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
            $sql .= ", Password = ?";
            $params .= "s";
            $values[] = $contrasenaCifrada;
        }

        $sql .= " WHERE ID = ?";
        $params .= "i";
        $values[] = $usuario_id;

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            // Error en la preparación de la consulta
            die("Error en la consulta SQL: " . $conexion->error);
        }

        // Vincular los parámetros y ejecutar la consulta
        if (!$stmt->bind_param($params, ...$values)) {
            // Error en la vinculación de parámetros
            die("Error en la vinculación de parámetros: " . $stmt->error);
        }

        if ($stmt->execute()) {
            // Redirigir de regreso a la lista de usuarios con un mensaje de éxito
            header("location: crudusuarios.php?mensaje=Usuario modificado con éxito");
            exit();
        } else {
            // Error en la ejecución de la consulta
            die("Error en la ejecución de la consulta: " . $stmt->error);
        }
    } else {
        // Consultar la información del usuario para mostrarla en el formulario de modificación
        $sql = "SELECT * FROM usuarios WHERE ID = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
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
// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="/public/assets/css/modificarUSER.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
</head>

<body id="body">

    <header>
        <div class="icon__menu">
            <i class="fas fa-bars" id="btn_open"></i>
        </div>

        <a href="javascript:history.back()" class="back-link">Volver Atrás</a>

        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Supervisor<span>";
            }
            ?>
        </div>
    </header>

    <div class="menu__side" id="menu_side">

        <div class="name__page">
            <img src="/public/assets/img/logo.png" class="img logo-image" alt="">
            <h4>Recaudo</h4>
        </div>

        <div class="options__menu">

            <a href="/controllers/cerrar_sesion.php">
                <div class="option">
                    <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
                    <h4>Cerrar Sesion</h4>
                </div>
            </a>

            <a href="/resources/views/zonas/20-Puebla/supervisor/inicio/inicio.php">
                <div class="option">
                    <i class="fa-solid fa-landmark" title="Inicio"></i>
                    <h4>Inicio</h4>
                </div>
            </a>
            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/usuarios/crudusuarios.php" class="selected">
                    <div class="option">
                        <i class="fa-solid fa-users" title=""></i>
                        <h4>Usuarios</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_usuarios) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/usuarios/registrar.php">
                    <div class="option">
                        <i class="fa-solid fa-user-plus" title=""></i>
                        <h4>Registrar Usuario</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/clientes/lista_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-people-group" title=""></i>
                        <h4>Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_listar_clientes) : ?>

                <a href="/resources/views/zonas/20-Puebla/supervisor/clientes/agregar_clientes.php">
                    <div class="option">
                        <i class="fa-solid fa-user-tag" title=""></i>
                        <h4>Registrar Clientes</h4>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($tiene_permiso_list_de_prestamos) : ?>
                <a href="/resources/views/zonas/20-Puebla/supervisor/creditos/crudPrestamos.php">
                    <div class="option">
                        <i class="fa-solid fa-hand-holding-dollar" title=""></i>
                        <h4>Prestamos</h4>
                    </div>
                </a>
            <?php endif; ?>
        </div>

    </div>
    <main>
        <h1>Modificar Usuario</h1>

        <!-- Muestra un mensaje de error si hay alguno -->
        <?php if (isset($mensaje)) {
            echo "<p>$mensaje</p>";
        } ?>

        <form method="post">
            <div>
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" value="<?= $usuario['Nombre'] ?>" required>
            </div>
            <div>
                <label for="apellido">Apellido:</label>
                <input type="text" name="apellido" value="<?= $usuario['Apellido'] ?>" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= $usuario['Email'] ?>" required>
            </div>
            <div>
                <label for="zona">Zona:</label>
                <input type="text" name="zona" value="<?= $usuario['Zona'] ?>" required>
            </div>
            <div>
                <label for="rolID">Rol:</label>
                <input type="text" name="rolID" value="<?= $usuario['RolID'] ?>" required>
            </div>
            <div>
                <label for="contrasena">Nueva Contraseña: <br>
                    <p class="pp">(dejar en blanco para no cambiar)</p>
                </label>
                <input type="password" name="contrasena">
            </div>
            <div>
                <input type="submit" value="Guardar Cambios">
            </div>
        </form>
    </main>

    <script>
        // Agregar un evento clic al botón
        document.getElementById("volverAtras").addEventListener("click", function() {
            window.history.back();
        });
    </script>
    <script src="/public/assets/js/MenuLate.js"></script>
</body>

</html>