<?php
// Lógica PHP para obtener datos de la base de datos, procesar información, etc.
include("../../includes/conexion.php");

$sql = "SELECT * FROM publicaciones ORDER BY fecha_de_publicacion DESC";
$resultado = $conexion->query($sql);

$publicaciones = array();

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $publicaciones[] = $row;
    }
}

// Obtener detalles del usuario para cada publicación
foreach ($publicaciones as &$publicacion) {
    $usuario_id = $publicacion['usuario_id'];
    $sql_user = "SELECT nombre FROM usuarios WHERE id = $usuario_id";
    $result_user = $conexion->query($sql_user);

    if ($result_user->num_rows > 0) {
        $row = $result_user->fetch_assoc();
        $publicacion['nombre_usuario'] = $row['nombre']; // Cambia 'nombre' por el nombre de tu columna
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script defer src="/assets/js/admin/inicio.js"></script>
    <script src="https://kit.fontawesome.com/9d12871b61.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/assets/css/admin/inicio.css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700&display=swap" rel="stylesheet" />
</head>

<body>
    <nav class="navbar">
        <ul class="navbar-nav">
            <li class="logo">
                <a href="#" class="nav-link">
                    <span class="link-text logo-text">ZeroRisks</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path d="M256 48C141.31 48 48 141.31 48 256s93.31 208 208 208 208-93.31 208-208S370.69 48 256 48zm2 96a72 72 0 11-72 72 72 72 0 0172-72zm-2 288a175.55 175.55 0 01-129.18-56.6C135.66 329.62 215.06 320 256 320s120.34 9.62 129.18 55.39A175.52 175.52 0 01256 432z" />
                    </svg>
                    <span class="link-text">Perfil</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path d="M256 48C141.31 48 48 141.31 48 256s93.31 208 208 208 208-93.31 208-208S370.69 48 256 48zm69.3 96.17a72.5 72.5 0 11-72.6 72.5 72.55 72.55 0 0172.6-72.5zm-155.1 26.36a59.32 59.32 0 11-59.4 59.32 59.35 59.35 0 0159.4-59.32zm-75.85 155c24.5-13.29 55.87-19.94 75.85-19.94 15 0 34.32 3 53.33 10.2a133.05 133.05 0 00-34 27.11c-13.19 15-20.76 32.92-20.76 50.83v15a177.06 177.06 0 01-74.42-83.15zM256 432a175.12 175.12 0 01-59.4-10.33v-27.05c0-52.59 85.75-79.09 128.7-79.09 23 0 58.38 7.63 86.21 22.81A176.14 176.14 0 01256 432z" />
                    </svg>
                    <span class="link-text">Amigos</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path d="M475.22 206.52c-10.34-48.65-37.76-92.93-77.22-124.68A227.4 227.4 0 00255.82 32C194.9 32 138 55.47 95.46 98.09 54.35 139.33 31.82 193.78 32 251.37a215.66 215.66 0 0035.65 118.76l4.35 6.05L48 480l114.8-28.56s2.3.77 4 1.42 16.33 6.26 31.85 10.6c12.9 3.6 39.74 9 60.77 9 59.65 0 115.35-23.1 156.83-65.06C457.36 365.77 480 310.42 480 251.49a213.5 213.5 0 00-4.78-44.97zM160 288a32 32 0 1132-32 32 32 0 01-32 32zm96 0a32 32 0 1132-32 32 32 0 01-32 32zm96 0a32 32 0 1132-32 32 32 0 01-32 32z" />
                    </svg>
                    <span class="link-text">chats</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path d="M256 176a80 80 0 1080 80 80.24 80.24 0 00-80-80zm172.72 80a165.53 165.53 0 01-1.64 22.34l48.69 38.12a11.59 11.59 0 012.63 14.78l-46.06 79.52a11.64 11.64 0 01-14.14 4.93l-57.25-23a176.56 176.56 0 01-38.82 22.67l-8.56 60.78a11.93 11.93 0 01-11.51 9.86h-92.12a12 12 0 01-11.51-9.53l-8.56-60.78A169.3 169.3 0 01151.05 393L93.8 416a11.64 11.64 0 01-14.14-4.92L33.6 331.57a11.59 11.59 0 012.63-14.78l48.69-38.12A174.58 174.58 0 0183.28 256a165.53 165.53 0 011.64-22.34l-48.69-38.12a11.59 11.59 0 01-2.63-14.78l46.06-79.52a11.64 11.64 0 0114.14-4.93l57.25 23a176.56 176.56 0 0138.82-22.67l8.56-60.78A11.93 11.93 0 01209.94 26h92.12a12 12 0 0111.51 9.53l8.56 60.78A169.3 169.3 0 01361 119l57.2-23a11.64 11.64 0 0114.14 4.92l46.06 79.52a11.59 11.59 0 01-2.63 14.78l-48.69 38.12a174.58 174.58 0 011.64 22.66z" />
                    </svg>
                    <span class="link-text">Ajustes</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                        <path d="M336 376V272H191a16 16 0 010-32h145V136a56.06 56.06 0 00-56-56H88a56.06 56.06 0 00-56 56v240a56.06 56.06 0 0056 56h192a56.06 56.06 0 0056-56zM425.37 272l-52.68 52.69a16 16 0 0022.62 22.62l80-80a16 16 0 000-22.62l-80-80a16 16 0 00-22.62 22.62L425.37 240H336v32z" />
                    </svg>
                    <span class="link-text">cerrar sesion</span>
                </a>
            </li>

            <li class="nav-item" id="themeButton">
                <a href="#" class="nav-link">
                    <svg class="theme-icon" id="lightIcon" aria-hidden="true" focusable="false" data-prefix="fad" data-icon="moon-stars" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-moon-stars fa-w-16 fa-7x">
                        <g class="fa-group">
                            <path fill="currentColor" d="M320 32L304 0l-16 32-32 16 32 16 16 32 16-32 32-16zm138.7 149.3L432 128l-26.7 53.3L352 208l53.3 26.7L432 288l26.7-53.3L512 208z" class="fa-secondary"></path>
                            <path fill="currentColor" d="M332.2 426.4c8.1-1.6 13.9 8 8.6 14.5a191.18 191.18 0 0 1-149 71.1C85.8 512 0 426 0 320c0-120 108.7-210.6 227-188.8 8.2 1.6 10.1 12.6 2.8 16.7a150.3 150.3 0 0 0-76.1 130.8c0 94 85.4 165.4 178.5 147.7z" class="fa-primary"></path>
                        </g>
                    </svg>
                    <svg class="theme-icon" id="solarIcon" aria-hidden="true" focusable="false" data-prefix="fad" data-icon="sun" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-sun fa-w-16 fa-7x">
                        <g class="fa-group">
                            <path fill="currentColor" d="M502.42 240.5l-94.7-47.3 33.5-100.4c4.5-13.6-8.4-26.5-21.9-21.9l-100.4 33.5-47.41-94.8a17.31 17.31 0 0 0-31 0l-47.3 94.7L92.7 70.8c-13.6-4.5-26.5 8.4-21.9 21.9l33.5 100.4-94.7 47.4a17.31 17.31 0 0 0 0 31l94.7 47.3-33.5 100.5c-4.5 13.6 8.4 26.5 21.9 21.9l100.41-33.5 47.3 94.7a17.31 17.31 0 0 0 31 0l47.31-94.7 100.4 33.5c13.6 4.5 26.5-8.4 21.9-21.9l-33.5-100.4 94.7-47.3a17.33 17.33 0 0 0 .2-31.1zm-155.9 106c-49.91 49.9-131.11 49.9-181 0a128.13 128.13 0 0 1 0-181c49.9-49.9 131.1-49.9 181 0a128.13 128.13 0 0 1 0 181z" class="fa-secondary"></path>
                            <path fill="currentColor" d="M352 256a96 96 0 1 1-96-96 96.15 96.15 0 0 1 96 96z" class="fa-primary"></path>
                        </g>
                    </svg>
                    <svg class="theme-icon" id="darkIcon" aria-hidden="true" focusable="false" data-prefix="fad" data-icon="sunglasses" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" class="svg-inline--fa fa-sunglasses fa-w-18 fa-7x">
                        <g class="fa-group">
                            <path fill="currentColor" d="M574.09 280.38L528.75 98.66a87.94 87.94 0 0 0-113.19-62.14l-15.25 5.08a16 16 0 0 0-10.12 20.25L395.25 77a16 16 0 0 0 20.22 10.13l13.19-4.39c10.87-3.63 23-3.57 33.15 1.73a39.59 39.59 0 0 1 20.38 25.81l38.47 153.83a276.7 276.7 0 0 0-81.22-12.47c-34.75 0-74 7-114.85 26.75h-73.18c-40.85-19.75-80.07-26.75-114.85-26.75a276.75 276.75 0 0 0-81.22 12.45l38.47-153.8a39.61 39.61 0 0 1 20.38-25.82c10.15-5.29 22.28-5.34 33.15-1.73l13.16 4.39A16 16 0 0 0 180.75 77l5.06-15.19a16 16 0 0 0-10.12-20.21l-15.25-5.08A87.95 87.95 0 0 0 47.25 98.65L1.91 280.38A75.35 75.35 0 0 0 0 295.86v70.25C0 429 51.59 480 115.19 480h37.12c60.28 0 110.38-45.94 114.88-105.37l2.93-38.63h35.76l2.93 38.63c4.5 59.43 54.6 105.37 114.88 105.37h37.12C524.41 480 576 429 576 366.13v-70.25a62.67 62.67 0 0 0-1.91-15.5zM203.38 369.8c-2 25.9-24.41 46.2-51.07 46.2h-37.12C87 416 64 393.63 64 366.11v-37.55a217.35 217.35 0 0 1 72.59-12.9 196.51 196.51 0 0 1 69.91 12.9zM512 366.13c0 27.5-23 49.87-51.19 49.87h-37.12c-26.69 0-49.1-20.3-51.07-46.2l-3.12-41.24a196.55 196.55 0 0 1 69.94-12.9A217.41 217.41 0 0 1 512 328.58z" class="fa-secondary"></path>
                            <path fill="currentColor" d="M64.19 367.9c0-.61-.19-1.18-.19-1.8 0 27.53 23 49.9 51.19 49.9h37.12c26.66 0 49.1-20.3 51.07-46.2l3.12-41.24c-14-5.29-28.31-8.38-42.78-10.42zm404-50l-95.83 47.91.3 4c2 25.9 24.38 46.2 51.07 46.2h37.12C489 416 512 393.63 512 366.13v-37.55a227.76 227.76 0 0 0-43.85-10.66z" class="fa-primary"></path>
                        </g>
                    </svg>
                    <span class="link-text">fondo</span>
                </a>
            </li>
        </ul>
    </nav>

    <main>
        <!-- Sección para Crear Publicación -->
        <section class="crear-publicacion">
            <a href="/views/admin/publicaciones/crear_publi.html" class="cuadro-crear-publicacion">
                <div>
                    <p>¿Qué estás pensando?</p>
                </div>
            </a>
        </section>

        <!-- Sección de Publicaciones de Usuarios -->
        <section class="publicaciones">
            <!-- Publicaciones de usuarios aquí -->
            <?php foreach ($publicaciones as $publicacion) : ?>
                <article class="publicacion">
                    <div class="usuario-info">
                        <!-- Mostrar el nombre de usuario -->
                        <h2><?php echo $publicacion['nombre_usuario']; ?></h2>
                    </div>
                    <p><?php echo $publicacion['contenido']; ?></p>
                    <div class="interacciones">
                        <!-- Enlace para la reacción -->
                        <div class="interacciones">
                            <a href="#" class="reaccion">Reaccionar</a>
                            <div class="opciones-reaccion">
                                <a href="#" class="opcion-reaccion reaccion-like">👍</a>
                                <a href="#" class="opcion-reaccion reaccion-love">❤️</a>
                                <a href="#" class="opcion-reaccion reaccion-laugh">😂</a>
                            </div>
                        </div>


                        <a href="publicaciones/comentarios.php?id=<?php echo $publicacion['id']; ?>">Comentarios</a>
                        <a href="otro_apartado.php?id=<?php echo $publicacion['id']; ?>">Compartir</a>
                    </div>
                </article>
            <?php endforeach; ?>
            <!-- Otras publicaciones similares -->
        </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"> </script>



    <!-- Agrega jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $('.opcion-reaccion').on('click', function(e) {
            e.preventDefault();
            const publicacionId = obtenerPublicacionId(); // Obtener el ID de la publicación
            const tipoReaccion = $(this).data('tipo-reaccion'); // Obtener el tipo de reacción de los atributos de datos

            // Realizar una petición AJAX para guardar la reacción en la base de datos
            $.ajax({
                type: 'POST',
                url: 'guardar_reaccion.php', // Ruta al archivo PHP que procesa la inserción en la tabla me_gusta
                data: {
                    usuario_id: 1, // Reemplaza con el ID del usuario actual
                    publicacion_id: publicacionId,
                    tipo_reaccion: tipoReaccion
                },
                success: function(response) {
                    // Manejar la respuesta del servidor (puede ser un mensaje de éxito o error)
                    console.log(response);
                },
                error: function(err) {
                    console.error('Error:', err);
                }
            });
        });

        // Función para obtener el ID de la publicación
        function obtenerPublicacionId() {
            // Lógica para obtener el ID de la publicación
            // Puedes recuperar el ID desde el DOM, por ejemplo:
            return $('.publicacion').data('publicacion-id'); // Suponiendo que la clase "publicacion" contiene el ID de la publicación
        }
    </script>



</body>

</html>