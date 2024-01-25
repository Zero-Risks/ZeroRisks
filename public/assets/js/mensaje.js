document.addEventListener('DOMContentLoaded', function() {
    const mensaje = document.getElementById('mensaje');

    // Función para mostrar el mensaje con animación
    function mostrarMensaje() {
        mensaje.style.display = 'block';
        setTimeout(function() {
            mensaje.style.opacity = '1';
        }, 10);
    }

    // Función para ocultar el mensaje con animación
    function ocultarMensaje() {
        mensaje.style.opacity = '0';
        setTimeout(function() {
            mensaje.style.display = 'none';
        }, 300);
    }

    // Detecta si hay un mensaje en la URL (puedes pasarlo como parámetro)
    const urlParams = new URLSearchParams(window.location.search);
    const mensajeParam = urlParams.get('mensaje');

    if (mensajeParam) {
        mostrarMensaje(); // Muestra el mensaje si está presente en la URL
    }

    // Agrega un manejador de eventos para ocultar el mensaje al hacer clic en él
    mensaje.addEventListener('click', function() {
        ocultarMensaje();
    });
});
