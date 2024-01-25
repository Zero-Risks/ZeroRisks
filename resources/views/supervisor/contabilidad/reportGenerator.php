<?php
date_default_timezone_set('America/Bogota');

// Incluir el autoload de Composer si estás usando PhpSpreadsheet a través de Composer
require '../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Incluir el archivo de conexión a la base de datos
include '../../../../controllers/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $dataTable = $_POST['dataTable'];

    // Valida las fechas
    if (!validateDate($startDate) || !validateDate($endDate) || $startDate > $endDate) {
        echo json_encode(["error" => "Rango de fechas no válido"]);
        exit;
    }

    // Construir la consulta SQL basada en la tabla seleccionada
    $query = "";
    switch ($dataTable) {
        case "clients":
            $query = "SELECT c.ID, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.IdentificacionCURP, c.fecha_registro, c.Estado, c.ZonaAsignada, m.Nombre as MonedaPreferida 
                      FROM clientes c 
                      LEFT JOIN monedas m ON c.MonedaPreferida = m.ID 
                      WHERE c.fecha_registro BETWEEN '$startDate' AND '$endDate'";
            break;
        case "loans":
            $query = "SELECT p.ID, cl.Nombre as NombreCliente, p.Monto, p.TasaInteres, p.Plazo 
                      FROM prestamos p 
                      LEFT JOIN clientes cl ON p.IDCliente = cl.ID 
                      WHERE p.FechaInicio BETWEEN '$startDate' AND '$endDate'";
            break;
        case "expenses":
            $query = "SELECT * FROM gastos WHERE Fecha BETWEEN '$startDate' AND '$endDate'";
            break;
        case "payments":
            $query = "SELECT hp.ID, cl.Nombre as NombreCliente, hp.FechaPago, hp.MontoPagado, hp.IDPrestamo 
                      FROM historial_pagos hp 
                      LEFT JOIN clientes cl ON hp.IDCliente = cl.ID 
                      WHERE hp.FechaPago BETWEEN '$startDate' AND '$endDate'";
            break;
        default:
            echo json_encode(["error" => "Selección de tabla no válida"]);
            exit;
    }
    
    // Ejecutar la consulta
    $result = $conexion->query($query);

    // Verificar si es una solicitud de exportación
    $isExporting = isset($_POST['export']) && $_POST['export'] === 'true';

    if (!$isExporting) {
        // Si no es exportación, devolver los datos en formato JSON
        if ($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Error en la consulta SQL: " . $conexion->error]);
        }
    } else {
        // Si es exportación, generar y enviar el archivo Excel
        if (!$result) {
            echo json_encode(["error" => "Error en la consulta SQL: " . $conexion->error]);
            exit;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Añadir encabezados
        $column = 1;
        if (!empty($data)) {
            foreach ($data[0] as $key => $value) {
                $sheet->setCellValueByColumnAndRow($column, 1, $key);
                $column++;
            }

            // Añadir datos
            $row = 2;
            foreach ($data as $rowData) {
                $column = 1;
                foreach ($rowData as $value) {
                    $sheet->setCellValueByColumnAndRow($column, $row, $value);
                    $column++;
                }
                $row++;
            }
        }

        // Establecer nombre del archivo con la fecha actual
        $fechaActual = date("Ymd_His"); // Formato de fecha y hora: AñoMesDía_HoraMinutoSegundo
        $fileName = "reporte_$fechaActual.xlsx";

        // Redirigir la salida al navegador
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
} else {
    echo json_encode(["error" => "Método de solicitud no válido"]);
}

// Función para validar el formato de la fecha
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>
