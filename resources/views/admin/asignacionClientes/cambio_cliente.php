<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../../../index.php');
    exit;
}

require_once '../../../../controllers/conexion.php';

$consultaUsuarios = "SELECT * FROM usuarios WHERE NOT RolID = 1";
$resultadoUsuarios = $conexion->query($consultaUsuarios);

// Preparar un array con los usuarios para evitar múltiples consultas a la base de datos
$usuarios = [];
while ($usuario = $resultadoUsuarios->fetch_assoc()) {
    array_push($usuarios, $usuario);
}

// Cerrar la conexión aquí no es recomendable si vas a necesitarla después
// $conexion->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cambio de Cliente</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .checkbox-grande {
            transform: scale(1.5);
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cambio de Cliente</h1>
        <div class="row">
            <!-- Filtro de usuarios 'De' -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="usuarioDe">De:</label>
                    <select class="form-control" id="usuarioDe" name="usuarioDe">
                        <option value="">Seleccione Usuario</option>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo $usuario['ID']; ?>">
                                <?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Filtro de usuarios 'Para' -->
            <div class="col-md-6">
                <div class="form-group">
                    <label for="usuarioPara">Para:</label>
                    <select class="form-control" id="usuarioPara" name="usuarioPara">
                        <option value="">Seleccione Usuario</option>
                        <?php foreach ($usuarios as $usuario) : ?>
                            <option value="<?php echo $usuario['ID']; ?>">
                                <?php echo htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div class="modal fade" id="modalConfirmacion" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Confirmar Cambio de Cliente</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Seguro que quieres pasar estos clientes:<br>
                        <div id="lista-clientes-modal"></div>
                        De: <span id="usuarioDeNombre"></span><br>
                        Para: <span id="usuarioParaNombre"></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmarCambio">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Lista de clientes -->
        <div class="row mt-4">
            <div class="col-md-12">
                <form action="procesar_seleccion.php" method="post">
                    <!-- Asegúrate de que el ID del usuario 'Para' se esté enviando -->
                    <input type="hidden" name="usuarioPara" id="inputUsuarioPara" value="">

                    <button type="submit" class="btn btn-primary">Procesar Selección</button>
                    <table class="table tabla-clientes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Curp</th>
                                <th>Zona</th>
                                <th>Seleccionar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los clientes se cargarán aquí mediante AJAX -->
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('usuarioDe').addEventListener('change', function() {
            var usuarioId = this.value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'obtener_clientes.php?usuarioId=' + usuarioId, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    var clientes = JSON.parse(this.responseText);
                    var tablaClientes = document.querySelector('.tabla-clientes tbody');
                    tablaClientes.innerHTML = '';

                    clientes.forEach(function(cliente) {
                        var fila = '<tr>' +
                            '<td>' + cliente.ID + '</td>' +
                            '<td>' + cliente.Nombre + ' ' + cliente.Apellido + '</td>' +
                            '<td>' + cliente.IdentificacionCURP + '</td>' +
                            '<td>' + cliente.ZonaAsignada + '</td>' +
                            '<td><input type="checkbox" name="clientes_seleccionados[]" value="' + cliente.ID + '" class="checkbox-grande"></td>' +
                            '</tr>';
                        tablaClientes.innerHTML += fila;
                    });
                }
            }
            xhr.send();
        });
    </script>

    <script>
        document.getElementById('confirmarCambio').addEventListener('click', function() {
            // Obtén el valor del usuario 'Para' seleccionado
            var usuarioParaId = document.getElementById('usuarioPara').value;
            // Establece el valor en el input oculto
            document.getElementById('inputUsuarioPara').value = usuarioParaId;
            // Envía el formulario
            document.querySelector('form').submit();
        });

        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault();
            var usuarioDeNombre = document.getElementById('usuarioDe').options[document.getElementById('usuarioDe').selectedIndex].text;
            var usuarioParaNombre = document.getElementById('usuarioPara').options[document.getElementById('usuarioPara').selectedIndex].text;
            var listaClientes = document.querySelectorAll('input[name="clientes_seleccionados[]"]:checked');
            var listaClientesModal = document.getElementById('lista-clientes-modal');
            listaClientesModal.innerHTML = '';

            listaClientes.forEach(function(cliente) {
                listaClientesModal.innerHTML += cliente.value + '<br>';
            });

            document.getElementById('usuarioDeNombre').textContent = usuarioDeNombre;
            document.getElementById('usuarioParaNombre').textContent = usuarioParaNombre;

            $('#modalConfirmacion').modal('show');
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>