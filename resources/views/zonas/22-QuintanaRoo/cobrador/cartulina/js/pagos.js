window.onload = function() {
    var formPago = document.getElementById('formPago');
    var campoCuota = document.getElementById('cuota');
    var campoResta = document.getElementById('campo2');
    var campoDeuda = document.getElementById('variable');
    var botonPagar = formPago.querySelector('input[name="action"][value="Pagar"]');
    var montoAPagar = parseFloat(document.getElementById('montoAPagar').value);

    campoCuota.addEventListener('input', function() {
        actualizarDeudaYResta();
        validarCuota();
        actualizarVisibilidadBotonPagar();
    });

    campoResta.addEventListener('input', function() {
        validarResta();
        actualizarVisibilidadBotonPagar();
    });

    function actualizarDeudaYResta() {
        var cuotaIngresada = parseFloat(campoCuota.value) || 0;
        var nuevaDeuda = montoAPagar - cuotaIngresada;
        campoDeuda.value = nuevaDeuda.toFixed(2);
        campoResta.value = nuevaDeuda.toFixed(2);
        validarResta();
    }

    function validarResta() {
        var valorResta = parseFloat(campoResta.value) || 0;
        var deudaActual = parseFloat(campoDeuda.value) || 0;
        campoResta.style.backgroundColor = (valorResta === deudaActual) ? 'green' : 'red';
    }

    function validarCuota() {
        var cuotaIngresada = parseFloat(campoCuota.value) || 0;
        campoCuota.style.backgroundColor = (cuotaIngresada <= montoAPagar) ? '' : 'red';
    }

    function actualizarVisibilidadBotonPagar() {
        var esCuotaInvalida = campoCuota.style.backgroundColor === 'red';
        var esRestaInvalida = campoResta.style.backgroundColor === 'red';
        botonPagar.style.display = (esCuotaInvalida || esRestaInvalida) ? 'none' : '';
    }

    formPago.addEventListener('submit', function(event) {
        var accion = formPago.querySelector('input[name="action"]:checked').value;
        var cuotaIngresada = parseFloat(campoCuota.value) || 0;
        var valorResta = parseFloat(campoResta.value) || 0;
        var deudaActual = parseFloat(campoDeuda.value) || 0;

        // Verificar si el campo de cuota está en rojo
        var esCuotaInvalida = campoCuota.style.backgroundColor === 'red';

        if (accion === 'Pagar') {
            if (esCuotaInvalida || cuotaIngresada > montoAPagar || valorResta !== deudaActual) {
                event.preventDefault();
                alert('Revisa los valores ingresados. La cuota no puede ser mayor al monto a pagar, no debe estar en rojo, y el valor en "Resta" debe ser igual al valor en "Deuda".');
            }
        }
    });
};