<?php
// informacion_pagos.php

function obtenerPagosPorCliente($conexion, $cliente_id, $soloUltimo = false) {
    if ($soloUltimo) {
        $sql = "SELECT * FROM facturas WHERE cliente_id = ? ORDER BY fecha DESC LIMIT 1";
    } else {
        $sql = "SELECT * FROM facturas WHERE cliente_id = ?";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $pagos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $pagos[] = $fila;
    }
    return $pagos;
}
