<?php
date_default_timezone_set('America/Bogota');
session_start();
include("../../../../controllers/conexion.php");



// Validacion de rol para ingresar a la pagina 
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
// Consulta para obtener la lista de usuarios
$usuariosSQL = $conexion->query("SELECT * FROM usuarios WHERE estado = 'inactivo'");

if ($usuariosSQL === false) {
    die("Error en la consulta SQL: " . $conexion->error);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/public/assets/css/lista_usuarios.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
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

    <div class="container mt-4">
        <main>
            <h1 class="mb-4">Usuarios Activados</h1>
            <div class="input-group mb-3">
                <input type="text" id="search-input" class="form-control" placeholder="Buscar...">

            </div>
            <div class="mb-3">
                <a href="crudusuarios.php" class="btn btn-secondary">
                    Activados
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Zona</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th> <!-- Columna de menú desplegable -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($usuariosSQL->num_rows > 0) {
                            while ($datos = $usuariosSQL->fetch_object()) {
                        ?>
                                <tr>
                                    <td><?= "REC 100" . $datos->ID ?></td>
                                    <td><?= $datos->Nombre ?></td>
                                    <td><?= $datos->Apellido ?></td>
                                    <td><?= $datos->Email ?></td>
                                    <td><?= $datos->Zona ?></td>
                                    <td><?= $datos->RolID ?></td>
                                    <td><?= $datos->Estado ?></td>
                                    <td>
                                        <!-- Menú desplegable para las acciones -->
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accionesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Acciones
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="accionesDropdown">
                                                <li><a class="dropdown-item" href="modificarUser.php?id=<?= $datos->ID ?>"><i class="fas fa-pencil-alt"></i> Modificar</a></li>
                                                <li><a class="dropdown-item <?= $datos->Estado == 'activo' ? 'text-danger' : 'text-success' ?>" href="cambiarEstado.php?id=<?= $datos->ID ?>&estado=<?= $datos->Estado ?>&vista=activos"><i class="fas <?= $datos->Estado == 'activo' ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i> <?= $datos->Estado == 'activo' ? 'Desactivar' : 'Activar' ?></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='8'>No se encontraron resultados.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>