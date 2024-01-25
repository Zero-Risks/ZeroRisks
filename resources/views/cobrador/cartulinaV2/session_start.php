<?php
// session_start.php

// Iniciar sesión
session_start();

// Incluir archivo de conexión a la base de datos
include("../../../../controllers/conexion.php");

// Validar si el usuario está autenticado
if (!isset($_SESSION["usuario_id"])) {
    // Redirigir al usuario a la página de inicio de sesión si no está autenticado
    header("Location: ../../../../../index.php");
    exit();
}

// Obtener y validar el ID del cliente desde la URL
if (!isset($_GET['id']) || $_GET['id'] === '' || !is_numeric($_GET['id'])) {
    // Redirigir a la página de error o principal si el ID no es válido
    header("location: ../../../../../index.php");
    exit();
}

// Asignar el ID del cliente a una variable
$id_cliente = $_GET['id'];

// Continúa con el resto del script...
