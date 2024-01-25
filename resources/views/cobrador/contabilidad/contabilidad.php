<?php

date_default_timezone_set('America/Bogota');
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../../../../../../index.php");
    exit();
}

// Incluye la configuración de conexión a la base de datos
require_once '../../../../controllers/conexion.php';

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

$stmt->close();

// Verifica si el resultado es nulo o si el rol del usuario no es 'admin'
if (!$fila || $fila['Nombre'] !== 'admin') {
    header("Location: /ruta_a_pagina_de_error_o_inicio.php");
    exit();
}




?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sistema de Contabilidad</title>
    <!-- Incluir Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="contabilidad.css">
</head>

<body>
    <header>
        <a href="/resources/views/admin/inicio/inicio.php" class="botonn">
            <i class="fa-solid fa-right-to-bracket fa-rotate-180"></i>
            <span class="spann">Volver al Inicio</span>
        </a>


        <div class="nombre-usuario">
            <?php
            if (isset($_SESSION["nombre_usuario"])) {
                echo htmlspecialchars($_SESSION["nombre_usuario"]) . "<br>" . "<span> Administrator<span>";
            }
            ?>





        </div>
    </header>


    <div class="container mt-5">
        <h1 class="mb-4">Sistema de Contabilidad</h1>

        <!-- Formulario para generar el reporte -->
        <form id="reportForm" class="mb-4" method="post">
            <div class="form-group">
                <label for="startDate">Fecha Inicio:</label>
                <input type="date" class="form-control" id="startDate" name="startDate" required>
            </div>

            <div class="form-group">
                <label for="endDate">Fecha Fin:</label>
                <input type="date" class="form-control" id="endDate" name="endDate" required>
            </div>

            <div class="form-group">
                <label for="dataTableSelect">Seleccionar Tabla:</label>
                <select class="form-control" id="dataTableSelect" name="dataTable">
                    <option value="clients">Clientes</option>
                    <option value="loans">Préstamos</option>
                    <option value="expenses">Gastos</option>
                    <option value="payments">Pagos</option>
                </select>
            </div>

            <button type="button" id="generateReportBtn" class="btn btn-primary">Generar Reporte</button>
        </form>

        <!-- Formulario para exportar a Excel -->
        <form id="exportForm" method="post" action="reportGenerator.php">
            <input type="hidden" id="exportStartDate" name="startDate">
            <input type="hidden" id="exportEndDate" name="endDate">
            <input type="hidden" id="exportDataTable" name="dataTable">
            <input type="hidden" name="export" value="true"> <!-- Input oculto para la exportación -->

            <button type="submit" class="btn btn-success">Exportar a Excel</button>
        </form>

        <!-- Tabla para mostrar los datos del reporte -->
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable">
                <!-- La tabla se llenará dinámicamente con los datos del reporte -->
            </table>
        </div>
    </div>

    <!-- Incluir Bootstrap JS y sus dependencias -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <!-- Script para manejar la generación y exportación de reportes -->
    <script>
        document.getElementById('generateReportBtn').addEventListener('click', function() {
            var formData = new FormData(document.getElementById('reportForm'));

            fetch('reportGenerator.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error('Respuesta del servidor no exitosa: ' + response.status);
                    }
                })
                .then(data => {
                    var dataTable = document.getElementById('dataTable');
                    dataTable.innerHTML = ''; // Limpiar la tabla antes de agregar nuevos datos

                    if (data && Array.isArray(data) && data.length > 0) {
                        // Crear encabezados de la tabla a partir de las claves del primer objeto
                        let headers = Object.keys(data[0]);
                        let headerRow = '<thead><tr>' + headers.map(header => `<th>${header}</th>`).join('') + '</tr></thead>';
                        dataTable.innerHTML += headerRow;

                        // Crear filas de la tabla
                        let rows = data.map(row => {
                            let rowData = headers.map(header => `<td>${row[header]}</td>`).join('');
                            return `<tr>${rowData}</tr>`;
                        }).join('');

                        dataTable.innerHTML += '<tbody>' + rows + '</tbody>';
                    } else if (data && data.error) {
                        // Manejar un error devuelto por el servidor
                        dataTable.innerHTML = '<tr><td colspan="100%">Error: ' + data.error + '</td></tr>';
                    } else {
                        // Manejar una respuesta vacía
                        dataTable.innerHTML = '<tr><td colspan="100%">No se encontraron datos.</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    var dataTable = document.getElementById('dataTable');
                    dataTable.innerHTML = '<tr><td colspan="100%">Error: ' + error.message + '</td></tr>';
                });
        });

        document.getElementById('exportForm').addEventListener('submit', function() {
            // Configurar los valores de los campos ocultos para la exportación a Excel
            document.getElementById('exportStartDate').value = document.getElementById('startDate').value;
            document.getElementById('exportEndDate').value = document.getElementById('endDate').value;
            document.getElementById('exportDataTable').value = document.getElementById('dataTableSelect').value;
        });
    </script>
</body>

</html>