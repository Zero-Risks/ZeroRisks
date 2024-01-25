
<!-- PASAR DE CLIENTE CON ANTERIOR Y SIGUIENTE -->

<?php
include '../../../../../../../controllers/conexion.php';

if (!function_exists('obtenerOrdenClientes')) {
    function obtenerOrdenClientes()
    {
        $rutaArchivo = __DIR__ . '/orden_fijo.txt'; // Asegúrate de que esta ruta sea correcta
        if (file_exists($rutaArchivo)) {
            $contenido = file_get_contents($rutaArchivo);
            return explode(',', $contenido);
        }
        return [];
    }
}

function obtenerClientes($conexion) {
    $ordenClientes = obtenerOrdenClientes();
    $clientes = [];

    if (count($ordenClientes) > 0) {
        // Si hay un orden definido, obtener los clientes en ese orden
        foreach ($ordenClientes as $idCliente) {
            $query = "SELECT id, Nombre, Apellido FROM clientes WHERE id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("i", $idCliente);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = mysqli_fetch_assoc($result)) {
                $clientes[] = [
                    'id' => $row['id'],
                    'nombre' => $row['Nombre'],
                    'apellido' => $row['Apellido']
                ];
            }
            $stmt->close();
        }
    } else {
        // Si no hay un orden definido, obtener todos los clientes
        $query = "SELECT id, Nombre, Apellido FROM clientes";
        $result = $conexion->query($query);
        if (!$result) {
            die("Error en la consulta: " . mysqli_error($conexion));
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $clientes[] = [
                'id' => $row['id'],
                'nombre' => $row['Nombre'],
                'apellido' => $row['Apellido']
            ];
        }
        mysqli_free_result($result);
    }

    return $clientes;
}

function obtenerIndicesClienteActual($clientes, $id_cliente_actual) {
    $currentIndex = array_search($id_cliente_actual, array_column($clientes, 'id'));
    $prevIndex = ($currentIndex === null || $currentIndex === 0) ? count($clientes) - 1 : $currentIndex - 1;
    $nextIndex = ($currentIndex === null || $currentIndex === count($clientes) - 1) ? 0 : $currentIndex + 1;

    return [$prevIndex, $currentIndex, $nextIndex];
}
?>
