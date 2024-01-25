<?php
// Archivo: agregar_cartera.php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluye la configuración de conexión a la base de datos
    require_once '../../../../../../controllers/conexion.php';

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

    // Obtener los datos del formulario
    $nombre = $_POST["nombre"];
    $idZona = $_POST["zona"];
    $idCiudad = $_POST["ciudad"];
    $asentamiento = $_POST["asentamiento"];

    // Preparar la consulta para insertar una nueva cartera
    $stmt = $conexion->prepare("INSERT INTO carteras (nombre, zona, ciudad, asentamiento) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $nombre, $idZona, $idCiudad, $asentamiento);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir a la página de la lista de carteras después de agregar exitosamente
        header("Location: lista_cartera.php");
        exit();
    } else {
        echo "Error al agregar la cartera: " . $stmt->error;
    }

    $stmt->close();
}

// Ruta a permisos
include("../../../../../../controllers/verificar_permisos.php");
?>

<!-- Resto del código HTML -->


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/assets/css/agregar_cartera.css">
    <script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script>
    <title>Agregar Cobro</title>
</head>

<body>

    <body id="body">

        <header>


            <div class="nombre-usuario">
                <?php
                if (isset($_SESSION["nombre_usuario"])) {
                    echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Cobrador<span>";
                }
                ?>
            </div>
        </header>





        <!-- ACA VA EL CONTENIDO DE LA PAGINA -->

        <main class="main2">
            <h2 class="h11">Agregar Nuevo Cobro</h2>

            <form method="post" action="agregar_cartera.php">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre"><br><br>

                <label for="zona">Estado:</label>
                <select id="zona" name="zona" placeholder="Por favor ingrese la zona" required>
                    <?php
                    require_once '../../../../../../controllers/conexion.php';
                    // Consulta SQL para obtener las zonas
                    $consultaZonas = "SELECT iD, nombre FROM zonas WHERE iD = 22";
                    $resultZonas = mysqli_query($conexion, $consultaZonas);
                    // Genera las opciones del menú desplegable para Zona
                    while ($row = mysqli_fetch_assoc($resultZonas)) {
                        echo '<option value="' . $row['iD'] . '">' . $row['nombre'] . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="ciudad">Ciudad:</label>
                <select id="ciudad" name="ciudad" required>

                    <?php
                    require_once '../../../../../../controllers/conexion.php';
                    // Consulta SQL para obtener las ciudades de la zona 22
                    $consultaCiudades = "SELECT ID, Nombre FROM ciudades WHERE IDZona = 22";
                    $resultCiudades = mysqli_query($conexion, $consultaCiudades);
                    // Genera las opciones del menú desplegable para Ciudad
                    while ($rowCiudad = mysqli_fetch_assoc($resultCiudades)) {
                        echo '<option value="' . $rowCiudad['ID'] . '">' . $rowCiudad['Nombre'] . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="asentamiento">Asentamiento:</label>
                <input type="text" id="asentamiento" name="asentamiento"><br><br>

                <div class="form-actions">
                    <input type="submit" value="Agregar">
                    <a href="javascript:history.back()" class="back-link1">Cancelar</a>
                </div>
            </form>
        </main>

        <script src="/public/assets/js/MenuLate.js"></script>
    </body>

    </html>