document.addEventListener("DOMContentLoaded", function() {
    inicializarCampos();
    document.getElementById("cuota").addEventListener("input", actualizarResta);
});

function inicializarCampos() {
    var montoAPagar = obtenerMontoAPagar();
    var campoDeuda = document.getElementById("resta");
    campoDeuda.readOnly = true;
    campoDeuda.value = montoAPagar.toFixed(2);

    // No establecemos un valor inicial para 'cuota' aquí
}

function obtenerMontoAPagar() {
    var elementoMonto = document.getElementById("montoAPagar");
    return parseFloat(elementoMonto.getAttribute('data-montoapagar'));
}

function actualizarResta() {
    var cuota = parseFloat(document.getElementById("cuota").value);
    var montoAPagar = obtenerMontoAPagar();

    // Si 'cuota' está vacío o es NaN, resetea los campos 'resta' y 'deuda' al monto a pagar
    if (isNaN(cuota) || cuota === 0) {
        document.getElementById("resta").value = montoAPagar.toFixed(2);
        return;
    }

    // Verificar que la cuota no exceda el monto a pagar
    if (cuota > montoAPagar) {
        document.getElementById("cuota").style.backgroundColor = "#ffcccc"; // Fondo rojo claro
        document.getElementById("cuota").style.borderColor = "#cc0000"; // Borde rojo oscuro
        return;
    } else {
        document.getElementById("cuota").style.backgroundColor = ""; // Restablecer el estilo
        document.getElementById("cuota").style.borderColor = "";
    }

    var nuevaResta = montoAPagar - cuota;
    document.getElementById("resta").value = nuevaResta.toFixed(2); // Ajustar a 2 decimales
}
