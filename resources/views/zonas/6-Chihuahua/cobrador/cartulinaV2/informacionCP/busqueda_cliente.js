$(document).ready(function() { 
    $('#filtroBusqueda').on('input', function() {
        var busqueda = $(this).val();
        if (busqueda.length > 2) {
            $.ajax({
                url: 'informacionCP/buscar_clientes.php', // Ajusta la ruta según tu estructura de directorios
                type: 'GET',
                dataType: 'json',
                data: { 'busqueda': busqueda },
                success: function(clientes) {
                    var html = '';
                    clientes.forEach(function(cliente) {
                        var enlace = 'abonos.php?id=' + cliente.id;
                        html += '<a href="' + enlace + '" class="list-group-item list-group-item-action">' +
                                cliente.nombre + ' ' + cliente.apellido + ' - ' + cliente.telefono +
                                '</a>';
                    });
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
