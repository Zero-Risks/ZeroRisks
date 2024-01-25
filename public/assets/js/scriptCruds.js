$(document).ready(function () {
    // Función para realizar la búsqueda
    $('#search-button').on('click', function () {
        var searchTerm = $('#search-input').val().toLowerCase();
        $('tbody tr').each(function () {
            var currentRowText = $(this).text().toLowerCase();
            if (currentRowText.indexOf(searchTerm) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Configuración de la paginación Bootstrap
    var itemsPerPage = 10; // Cambia esto según la cantidad de elementos por página que desees mostrar
    var $table = $('table.table');
    $table.page = 1;

    $table.bind('repaginate', function () {
        $table.find('tbody tr').hide()
            .slice(($table.page - 1) * itemsPerPage, $table.page * itemsPerPage).show();
    });

    var numRows = $table.find('tbody tr').length;
    var numPages = Math.ceil(numRows / itemsPerPage);
    var $pager = $('<ul class="pagination"></ul>');

    for (var page = 1; page <= numPages; page++) {
        $('<li><a href="#" class="page-link">' + page + '</a></li>')
            .bind('click', {
                newPage: page
            }, function (event) {
                $table.page = event.data['newPage'];
                $table.trigger('repaginate');
                $(this).addClass('active').siblings().removeClass('active');
            }).appendTo($pager);
    }

    $pager.appendTo('#pagination').find('li:first').addClass('active');
});
