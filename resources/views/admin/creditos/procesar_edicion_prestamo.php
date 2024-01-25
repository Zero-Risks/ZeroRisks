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
}

// Verificar si se ha enviado el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos enviados por el formulario
    $prestamo_id = mysqli_real_escape_string($conexion, $_POST['prestamo_id']);
    $monto = floatval($_POST['monto']);
    $tasa_interes = floatval($_POST['TasaInteres']);
    $plazo = intval($_POST['plazo']);
    $moneda_id = mysqli_real_escape_string($conexion, $_POST['moneda_id']);
    $fecha_inicio = mysqli_real_escape_string($conexion, $_POST['fecha_inicio']);
    $frecuencia_pago = mysqli_real_escape_string($conexion, $_POST['frecuencia_pago']);
    $zona = mysqli_real_escape_string($conexion, $_POST['zona']);
    $aplicar_comision = mysqli_real_escape_string($conexion, $_POST['aplicar_comision']);
    $comision = isset($_POST['comision']) ? floatval($_POST['comision']) : 0;

    // Validar que la tasa de interés sea un número válido
    if (!is_numeric($tasa_interes)) {
        header("Location: /ruta_a_pagina_de_error.php?mensaje=La tasa de interés no es válida.");
        exit();
    }

    // Calcular el monto total a pagar (sin la comisión)
    $monto_total = $monto + ($monto * $tasa_interes / 100);

    // Inicializar la comisión
    $comision_calculada = 0;

    // Si el usuario eligió aplicar comisión, calcularla basada en el valor ingresado
    if ($aplicar_comision === 'si') {
        $comision_calculada = $monto_total * ($comision / 100);
    }

    // Calcular el monto de cada cuota
    $cuota = $monto_total / $plazo;

    // Calcular la fecha de vencimiento en función de la frecuencia de pago y el plazo
    $fecha_vencimiento = calcularFechaVencimiento($fecha_inicio, $plazo, $frecuencia_pago);

    // Actualizar los datos del préstamo en la base de datos
    $sql = "UPDATE prestamos SET
            Monto = $monto,
            TasaInteres = $tasa_interes,
            Plazo = $plazo,
            MonedaID = '$moneda_id',
            FechaInicio = '$fecha_inicio',
            FechaVencimiento = '$fecha_vencimiento',
            FrecuenciaPago = '$frecuencia_pago',
            MontoAPagar = $monto_total,
            Cuota = $cuota,
            MontoCuota = $cuota,
            Comision = $comision_calculada
            WHERE ID = $prestamo_id";

    if ($conexion->query($sql) === TRUE) {
        // Actualizar las fechas de pago
        actualizarFechasPago($conexion, $prestamo_id, $fecha_inicio, $frecuencia_pago, $plazo, $zona);

        // Redirige a la página de éxito o a la página de detalles del préstamo editado
        header("Location: crudPrestamos.php");
        exit();
    } else {
        // Si ocurre un error en la actualización, muestra un mensaje de error
        echo "Error al actualizar el préstamo: " . $conexion->error;
    }
}

// Otras lógicas y verificaciones necesarias...

// Función para calcular la fecha de vencimiento en función del plazo y la frecuencia de pago
function calcularFechaVencimiento($fecha_inicio, $plazo, $frecuencia_pago) {
    $fecha = new DateTime($fecha_inicio);

    switch ($frecuencia_pago) {
        case 'diario':
            $fecha->add(new DateInterval('P' . $plazo . 'D'));
            break;
        case 'semanal':
            $fecha->add(new DateInterval('P' . ($plazo * 7) . 'D'));
            break;
        case 'quincenal':
            $fecha->add(new DateInterval('P' . ($plazo * 15) . 'D'));
            break;
        case 'mensual':
            $fecha->add(new DateInterval('P' . $plazo . 'M'));
            break;
        default:
            // Por defecto, se asume pago mensual
            $fecha->add(new DateInterval('P' . $plazo . 'M'));
            break;
    }

    return $fecha->format('Y-m-d');
}

// Función para actualizar las fechas de pago
function actualizarFechasPago($conexion, $prestamo_id, $fecha_inicio, $frecuencia_pago, $plazo, $zona) {
    // Eliminar las fechas de pago existentes para el préstamo
    $sql_eliminar_fechas = "DELETE FROM fechas_pago WHERE IDPrestamo = $prestamo_id";
    $conexion->query($sql_eliminar_fechas);

    // Calcular las nuevas fechas de pago
    $fechas_pago = calcularFechasPago($fecha_inicio, $frecuencia_pago, $plazo, $prestamo_id, $zona);

    foreach ($fechas_pago as $fecha_pago) {
        // Insertar cada fecha de pago en la tabla "fechas_pago"
        $sql_fecha_pago = "INSERT INTO fechas_pago (IDPrestamo, FechaPago, Zona) VALUES ('$prestamo_id', '" . $fecha_pago->format('Y-m-d') . "', '$zona')";
        $conexion->query($sql_fecha_pago);
    }
}

// Función para calcular las fechas de pago
function calcularFechasPago($fecha_inicio, $frecuencia_pago, $plazo, $id_prestamo, $zona) {
    $fechasPago = array();
    $fecha = new DateTime($fecha_inicio); 

    for ($i = 0; $i < $plazo; $i++) {
        $fechasPago[] = clone $fecha;
        
        switch ($frecuencia_pago) {
            case 'diario':
                $fecha->modify('+1 day');
                break;
            case 'semanal':
                $fecha->modify('+1 week');
                break;
            case 'quincenal':
                $fecha->modify('+2 weeks');
                break;
            case 'mensual':
                $fecha->modify('+1 month');
                break;
        }
    }

    return $fechasPago;
}
?>
