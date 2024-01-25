<?php
date_default_timezone_set('America/Bogota');

require('../../../../public/assets/fpdf/fpdf.php'); // Asegúrate de que el archivo FPDF se encuentre en la ubicación correcta

// Asegúrate de que el archivo FPDF se encuentre en la ubicación correcta

// Verificar la autenticación del usuario y la existencia de los parámetros necesarios
// Asegúrate de realizar una verificación de autenticación adecuada

if (!isset($_GET['clienteId']) || !isset($_GET['prestamoId']) || !isset($_GET['cantidadPago'])) {
    // Redirige al usuario o muestra un mensaje de error si faltan parámetros
    die("Faltan parámetros necesarios para generar la factura.");
}

$clienteId = $_GET['clienteId'];
$prestamoId = $_GET['prestamoId'];
$cantidadPago = isset($_GET['cantidadPago']) ? $_GET['cantidadPago'] : 'No se proporcionó una cantidad de pago.';

require('../../../../controllers/conexion.php');

// Consulta SQL para obtener los datos del cliente y el préstamo
$query = "SELECT c.Nombre AS cliente_nombre, c.Apellido AS cliente_apellido, 
                 p.TasaInteres, p.FechaInicio, p.FechaVencimiento, 
                 p.MontoAPagar, p.Cuota 
          FROM clientes c
          INNER JOIN prestamos p ON c.ID = p.IDCliente
          WHERE c.ID = $clienteId AND p.ID = $prestamoId";

$result = $conexion->query($query);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Crear una instancia de FPDF con orientación vertical y tamaño de página
    $pdf = new FPDF();
    $pdf->AddPage();

    // Configurar fuente y tamaño
    $pdf->SetFont('Arial', '', 12);

    // Título de la factura
    $pdf->Cell(190, 10, 'Factura', 0, 1, 'C');

    // Línea separadora
    $pdf->SetLineWidth(0.5);
    $pdf->Cell(190, 0, '', 'T');

    // Datos del cliente y préstamo
    $pdf->Ln(10); // Salto de línea
    $pdf->Cell(100, 10, 'Cliente: ' . $row['cliente_nombre'] . ' ' . $row['cliente_apellido']);
    $pdf->Cell(90, 10, 'Fecha: ' . date('Y-m-d'), 0, 1);
    $pdf->Cell(100, 10, 'Tasa de Interés: ' . $row['TasaInteres']);
    $pdf->Cell(90, 10, 'Hora: ' . date('H:i:s'), 0, 1);
    $pdf->Cell(100, 10, 'Fecha de Inicio: ' . $row['FechaInicio']);
    $pdf->Ln(10); // Salto de línea
    $pdf->Cell(100, 10, 'Fecha de Vencimiento: ' . $row['FechaVencimiento']);
    $pdf->Ln(10); // Salto de línea
    $pdf->Cell(100, 10, 'Monto a Pagar: $' . $row['MontoAPagar'], 0, 1);
    $pdf->Cell(100, 10, 'Cuota: $' . $row['Cuota'], 0, 1);
    $pdf->Ln(10); // Salto de línea
    $pdf->Cell(100, 10, 'Cantidad Pagada: $' . $cantidadPago, 0, 1);

    // Generar el archivo PDF
    $pdf->Output('factura.pdf', 'D'); // Descargar el PDF con el nombre 'factura.pdf'
}
else {
    echo "No se encontraron datos para generar la factura.";
}

$conexion->close();
?>