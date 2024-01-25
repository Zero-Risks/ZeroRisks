<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../../../../../controllers/conexion.php';

    $usuario_id = $_POST["usuario_id"];

    // Consultas para permisos y permisos asignados
    $sql_permisos = "SELECT ID, nombre, descripcion FROM permisos";
    $result_permisos = $conexion->query($sql_permisos);

    $sql_asignados = "SELECT permiso_id FROM usuarios_permisos WHERE usuario_id = ?";
    $stmt = $conexion->prepare($sql_asignados);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result_asignados = $stmt->get_result();

    $permisos_asignados = [];
    while ($row = $result_asignados->fetch_assoc()) {
        $permisos_asignados[] = $row["permiso_id"];
    }

    // Comienza la generación de la tabla
    $html = "<div class='responsive-table'><table class='table table-hover'>";
    $html .= "<thead class='thead-dark'><tr><th>Permiso</th><th>Descripción</th><th>Asignado</th></tr></thead><tbody>";

    // Iterar sobre cada permiso
    foreach ($result_permisos as $row) {
        $permiso_id = $row["ID"];
        $nombre = htmlspecialchars($row["nombre"]);
        $descripcion = htmlspecialchars($row["descripcion"]);
        $checked = in_array($permiso_id, $permisos_asignados) ? "checked" : "";

        $html .= "<tr>";
        $html .= "<td data-label='Permiso'><div class='form-check'><input type='checkbox' class='form-check-input' name='permisos[]' value='$permiso_id' $checked id='permiso_$permiso_id'>";
        $html .= "<label class='form-check-label' for='permiso_$permiso_id'>$nombre</label></div></td>";
        $html .= "<td data-label='Descripción'><div class='descripcion-larga'>$descripcion</div></td>";
        $html .= "<td data-label='Asignado'>" . ($checked ? "<span class='badge badge-success'>Asignado</span>" : "") . "</td>";
        $html .= "</tr>";
    }

    $html .= "</tbody></table></div>";

    echo $html;
}
?>
