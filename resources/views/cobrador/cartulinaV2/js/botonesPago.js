document.addEventListener("DOMContentLoaded", function() {
    inicializarCampos();
    document.getElementById("cuota").addEventListener("input", function() {
        actualizarDeudaYResta();
        validarResta();
    });
    document.getElementById("resta").addEventListener("input", function() {
        actualizarDeudaYResta();
        validarResta();
    });
});
function inicializarCampos() {
    var montoAPagar = obtenerMontoAPagar();
    var campoDeuda = document.getElementById("deuda");
    campoDeuda.readOnly = true; // El campo deuda es de solo lectura
    campoDeuda.value = montoAPagar.toFixed(2); // Establecer el monto a pagar por defecto en deuda

    var campoResta = document.getElementById("resta");
    campoResta.value = ""; // Inicializar 'resta' vacío
    ocultarBotonPagar(); // Ocultar el botón Pagar al inicializar
}

function obtenerMontoAPagar() {
    var elementoMonto = document.getElementById("montoAPagar");
    return parseFloat(elementoMonto.getAttribute('data-montoapagar'));
}

function ocultarBotonPagar() {
    var btnPagar = document.getElementById("btnPagar");
    btnPagar.style.display = "none"; // Ocultar el botón Pagar
}

function actualizarDeudaYResta() {
    var cuota = parseFloat(document.getElementById("cuota").value);
    var montoAPagar = obtenerMontoAPagar();
    var campoDeuda = document.getElementById("deuda");
    var btnPagar = document.getElementById("btnPagar");

    if (isNaN(cuota) || cuota <= 0) {
        cuota = 0; // Si 'cuota' es NaN o menor o igual a 0, establece cuota a 0
        document.getElementById("cuota").style.backgroundColor = ""; // Restablecer el estilo
        document.getElementById("cuota").style.borderColor = "";
        ocultarBotonPagar(); // Ocultar el botón Pagar
    } else {
        if (cuota > montoAPagar) {
            // Si la cuota es mayor que el monto a pagar
            document.getElementById("cuota").style.backgroundColor = "#ffcccc";
            document.getElementById("cuota").style.borderColor = "#cc0000";
            ocultarBotonPagar(); // Ocultar el botón Pagar
        } else {
            // Si la cuota es menor o igual al monto a pagar
            var nuevaDeuda = montoAPagar - cuota;
            campoDeuda.value = nuevaDeuda.toFixed(2); // Actualizar el campo deuda en tiempo real
            document.getElementById("cuota").style.backgroundColor = "";
            document.getElementById("cuota").style.borderColor = "";

            if (isNaN(resta) || resta <= 0) {
                ocultarBotonPagar(); // Ocultar el botón Pagar si 'resta' es NaN o menor o igual a 0
            } else {
                document.getElementById("btnPagar").style.display = ""; // Mostrar el botón Pagar
            }
        }
    }
}

function validarResta() {
    var deuda = parseFloat(document.getElementById("deuda").value);
    var resta = parseFloat(document.getElementById("resta").value);

    if (isNaN(resta) || resta <= 0) {
        resta = 0; // Si 'resta' es NaN o menor o igual a 0, establece resta a 0
        document.getElementById("resta").style.backgroundColor = ""; // Restablecer el estilo
        document.getElementById("resta").style.borderColor = "";
        ocultarBotonPagar(); // Ocultar el botón Pagar
    } else {
        if (resta !== deuda) {
            // Si resta y deuda no coinciden
            document.getElementById("resta").style.backgroundColor = "#ffcccc";
            document.getElementById("resta").style.borderColor = "#cc0000";
            ocultarBotonPagar(); // Ocultar el botón Pagar
        } else {
            // Si resta y deuda coinciden
            document.getElementById("resta").style.backgroundColor = "#d4edda";
            document.getElementById("resta").style.borderColor = "#28a745";
            document.getElementById("btnPagar").style.display = ""; // Mostrar el botón Pagar
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    inicializarCampos();
    document.getElementById("cuota").addEventListener("input", actualizarDeudaYResta);
    document.getElementById("resta").addEventListener("input", validarResta);
});