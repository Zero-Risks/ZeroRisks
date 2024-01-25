<?php
require('../../../../../../controllers/conexion.php');
require('../../../../../../public/assets/fpdf/fpdf.php');
date_default_timezone_set('America/Bogota');

// Verificar si se ha proporcionado el ID de la factura en la URL y validar el valor
if (isset($_GET['facturaId']) && is_numeric($_GET['facturaId'])) {
    $facturaId = $_GET['facturaId'];

    // Consulta SQL para obtener los datos necesarios de la factura y el cliente
    $sql = "SELECT f.fecha, f.monto_pagado, f.monto_deuda, c.Nombre, c.Apellido, c.Domicilio, c.Telefono, c.ZonaAsignada, c.IdentificacionCURP FROM facturas f LEFT JOIN clientes c ON f.cliente_id = c.ID WHERE f.id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $facturaId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Verificar si la consulta se ejecutó correctamente y si se obtuvieron resultados
    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        // Crear una instancia de FPDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Configurar las cabeceras para PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="factura.pdf"');

        // Configurar la fuente y tamaño de letra
        $pdf->SetFont('Arial', '', 12);

        // Título del recibo
        $pdf->Cell(190, 10, utf8_decode('Recibo de Pago de Préstamo'), 0, 1, 'C');
        $pdf->Ln(10);

        // Datos de la factura
        $pdf->Cell(60, 10, 'Fecha:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['fecha']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Monto Pagado:', 0);
        $pdf->Cell(130, 10, '$' . number_format($fila['monto_pagado'], 2), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Monto Deuda:', 0);
        $pdf->Cell(130, 10, '$' . number_format($fila['monto_deuda'], 2), 0);
        $pdf->Ln(10);

        // Datos del cliente
        $pdf->Cell(190, 10, 'Datos del Cliente', 0, 1, 'C');
        
        $pdf->Cell(60, 10, 'Nombre:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['Nombre']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Apellido:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['Apellido']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Domicilio:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['Domicilio']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Teléfono:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['Telefono']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Zona Asignada:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['ZonaAsignada']), 0);
        $pdf->Ln();

        $pdf->Cell(60, 10, 'Identificación CURP:', 0);
        $pdf->Cell(130, 10, utf8_decode($fila['IdentificacionCURP']), 0);
        $pdf->Ln(20);

        // Generar el PDF
        $pdf->Output();
    } else {
        echo "No se encontraron resultados para la factura ID: $facturaId.";
    }
} else {
    echo "No se ha proporcionado un ID de factura válido.";
}
?>
