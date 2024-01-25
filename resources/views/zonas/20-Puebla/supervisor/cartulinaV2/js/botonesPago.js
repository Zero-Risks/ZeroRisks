document.addEventListener("DOMContentLoaded", function() {
    inicializarCampos();
    document.getElementById("cuota").addEventListener("input", actualizarDeudaYResta);
    document.getElementById("resta").addEventListener("input", validarResta);
});

function inicializarCampos() {
    var montoAPagar = obtenerMontoAPagar();
    var campoDeuda = document.getElementById("deuda");
    campoDeuda.readOnly = true; // El campo deuda es de solo lectura
    campoDeuda.value = montoAPagar.toFixed(2); // Establecer el monto a pagar por defecto en deuda

    var campoResta = document.getElementById("resta");
    campoResta.value = ""; // Inicializar 'resta' vacío
}

function obtenerMontoAPagar() {
    var elementoMonto = document.getElementById("montoAPagar");
    return parseFloat(elementoMonto.getAttribute('data-montoapagar'));
}

function actualizarDeudaYResta() {
    var cuota = parseFloat(document.getElementById("cuota").value);
    var montoAPagar = obtenerMontoAPagar();
    var campoDeuda = document.getElementById("deuda");
    var btnPagar = document.getElementById("btnPagar");

    if (isNaN(cuota)) {
        cuota = 0; // Si 'cuota' es NaN, establece cuota a 0
        document.getElementById("cuota").style.backgroundColor = ""; // Restablecer el estilo
        document.getElementById("cuota").style.borderColor = "";
        btnPagar.style.display = ""; // Mostrar el botón Pagar
    }

    if (cuota > montoAPagar) {
        // Si la cuota es mayor que el monto a pagar
        document.getElementById("cuota").style.backgroundColor = "#ffcccc";
        document.getElementById("cuota").style.borderColor = "#cc0000";
        btnPagar.style.display = "none"; // Ocultar el botón Pagar
    } else {
        // Si la cuota es menor o igual al monto a pagar
        var nuevaDeuda = montoAPagar - cuota;
        campoDeuda.value = nuevaDeuda.toFixed(2);
        document.getElementById("resta").value = ""; // Restablecer el valor de resta
        document.getElementById("cuota").style.backgroundColor = "";
        document.getElementById("cuota").style.borderColor = "";
        btnPagar.style.display = ""; // Mostrar el botón Pagar
    }
}

function validarResta() {
    var deuda = parseFloat(document.getElementById("deuda").value);
    var resta = parseFloat(document.getElementById("resta").value);
    var btnPagar = document.getElementById("btnPagar");

    if (resta !== deuda) {
        // Si resta y deuda no coinciden
        document.getElementById("resta").style.backgroundColor = "#ffcccc";
        document.getElementById("resta").style.borderColor = "#cc0000";
        btnPagar.style.display = "none"; // Ocultar el botón Pagar
    } else {
        // Si resta y deuda coinciden
        document.getElementById("resta").style.backgroundColor = "#d4edda";
        document.getElementById("resta").style.borderColor = "#28a745";
        btnPagar.style.display = ""; // Mostrar el botón Pagar
    }
}
