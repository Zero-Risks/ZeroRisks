
// VALIDAR QUE LA COUTA NO ESTE EN 0

window.onload = function() {
    var formPago = document.getElementById('formPago');
    var campoCuota = document.getElementById('cuota');
    var campoResta = document.getElementById('campo2');
    var campoDeuda = document.getElementById('variable');

    var cuotaEsperada = parseFloat(document.getElementById('cuotaEsperada').value);
    var montoAPagar = parseFloat(document.getElementById('montoAPagar').value);

    campoResta.addEventListener('input', function() {
        var valorResta = parseFloat(campoResta.value.replace(',', '.'));
        var deudaActual = parseFloat(campoDeuda.value.replace(',', '.'));
        campoResta.style.backgroundColor = (Math.abs(valorResta - deudaActual) < 0.01) ? 'green' : 'red';
    });

    formPago.addEventListener('submit', function(event) {
        var accion = formPago.querySelector('input[name="action"]:checked').value;
        if (accion === 'Pagar') {
            var cuotaIngresada = parseFloat(campoCuota.value.replace(',', '.'));
            var valorResta = parseFloat(campoResta.value.replace(',', '.'));
            var deudaActual = parseFloat(campoDeuda.value.replace(',', '.'));
            var tolerancia = 0.01;

            if (deudaActual === 0 && valorResta === 0) {
                if (Math.abs(cuotaIngresada - montoAPagar) > tolerancia) {
                    event.preventDefault();
                    alert('La cuota ingresada debe ser igual al monto total a pagar.');
                    return;
                }
            } else {
                if (Math.abs(cuotaIngresada - cuotaEsperada) > tolerancia) {
                    event.preventDefault();
                    alert('La cuota ingresada no es correcta.');
                    return;
                }
                if (Math.abs(valorResta - deudaActual) > tolerancia) {
                    event.preventDefault();
                    alert('El saldo que resta que se ingresó no es correcto.');
                    return;
                }
            }
        }
    });
};
