$(document).ready(function() {
    $('#filtroBusqueda').on('input', function() {
        var busqueda = $(this).val();
        if (busqueda.length > 2) {
            $.ajax({
                url: 'buscar_clientes.php', // Asegúrate de que la ruta sea correcta
                type: 'GET',
                dataType: 'json', 
                data: {
                    'busqueda': busqueda
                },
                success: function(clientes) {
                    var html = '';
                    for (var i = 0; i < clientes.length; i++) {
                        html += '<div onclick="seleccionarCliente(' + clientes[i].id + ')">' +
                                clientes[i].nombre + ' ' + clientes[i].apellido + ' - ' + clientes[i].telefono + '</div>';
                    }
                    $('#resultadosBusqueda').html(html);
                },
                error: function(xhr, status, error) {
                    console.error("Error en la solicitud AJAX: " + status + ", " + error);
                    $('#resultadosBusqueda').html('<p>Error al buscar clientes</p>');
                }
            });
        } else {
            $('#resultadosBusqueda').html('');
        }
    });
});

function seleccionarCliente(clienteId) {
    $('#selectedClientId').val(clienteId);
    $('#clienteForm').submit();
}

function navigate(direction) {
    var selectedClientId = direction === "prev" ? prevClientId : nextClientId;
    $('#selectedClientId').val(selectedClientId);
    $('#clienteForm').submit();
}
