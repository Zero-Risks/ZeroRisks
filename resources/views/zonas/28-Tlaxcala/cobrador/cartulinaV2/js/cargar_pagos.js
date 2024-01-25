// Archivo ver_mas_ver_menos.js
function showMore() {
    // Redirigir a la página con todos los pagos
    window.location.href = 'abonos.php?id=' + id_cliente + '&show_all=true';
}

function showLess() {
    // Redirigir a la página con pagos limitados
    window.location.href = 'abonos.php?id=' + id_cliente + '&show_all=false';
}
