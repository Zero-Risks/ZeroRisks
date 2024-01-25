 // Manejador para el botón de confirmar pago
 $('#confirmPaymentButton').click(function() {
    procesarPago(globalPrestamoId, globalMontoCuota);
});

// Manejador para el botón de posponer pago
$('#confirmPostponePaymentButton').click(function() {
    posponerPago(globalPrestamoId);
});

// Evento al cerrar el modal de WhatsApp
$('#whatsappModal').on('hidden.bs.modal', function() {
    location.reload(); // Recargar la página
});

// Evento al cerrar el modal de Pago Pospuesto
$('#postponePaymentModal').on('hidden.bs.modal', function() {
    location.reload(); // Recargar la página
});

// Evento al cerrar el modal de Pago Personalizado
$('#customPaymentModal').on('hidden.bs.modal', function() {
    e.stopPropagation(); // Evita la propagación del evento para que no se cierre automáticamente
});

var globalPrestamoId = 0;
var globalMontoCuota = 0;

function setPrestamoId(prestamoId, montoCuota, nombreCliente, direccionCliente, telefonoCliente, identificacionCURP, montoAPagar) {
    globalPrestamoId = prestamoId;
    globalMontoCuota = montoCuota;
    // Actualizar el contenido del modal con los detalles del cliente
    $('#modalClienteNombre').text(nombreCliente);
    $('#modalClienteDireccion').text(direccionCliente);
    $('#modalClienteTelefono').text(telefonoCliente);
    $('#modalClienteCURP').text(identificacionCURP);
    $('#modalMontoAPagar').text(montoAPagar);
    $('#modalMontoCuota').text(montoCuota);
}
function procesarPago(prestamoId, montoCuota) {
    $.ajax({
        url: 'procesar_pago.php',
        type: 'POST',
        data: {
            prestamoId: prestamoId,
            montoPagado: montoCuota
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#confirmPaymentModal').modal('hide');
                // Mostrar modal de WhatsApp
                $('#whatsappModal').modal('show');
                // Agregar los detalles del cliente al modal
                $('#clienteDetalles').text('Nombre: ' + response.clienteNombre + ',\n Monto Pagado: ' +
                    response.montoPagado + '\nDetalles adicionales:\n' +
                    'CURP: ' + response.clienteCURP + '\n' +
                    'Domicilio: ' + response.clienteDireccion + '\n' +
                    'Monto pendiente: ' + response.montoPendiente);

                
                // Crear mensaje de WhatsApp con más detalles del cliente
                var mensajeWhatsapp = 'Hola ' + response.clienteNombre +
                    ', hemos recibido tu pago de ' + response.montoPagado + '.\n' +
                    'Detalles adicionales:\n' +
                    'CURP: ' + response.clienteCURP + '\n' +
                    'Domicilio: ' + response.clienteDireccion + '\n' +
                    'Monto pendiente: ' + response.montoPendiente;

                // Preparar el botón de WhatsApp
                $('#sendWhatsappButton').off('click').on('click', function() {
                    window.open('https://wa.me/' + response.clienteTelefono + '?text=' +
                        encodeURIComponent(mensajeWhatsapp));
                });
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            alert('Ocurrió un error al procesar el pago.');
        }
    });
}



function abrirModalPosponerPago(prestamoId, montoCuota, nombreCliente, direccionCliente, telefonoCliente, identificacionCURP, montoAPagar) {

    globalPrestamoId = prestamoId;

    // Establecer los datos del cliente en el modal de Posponer Pago
    $('#postponeModalClienteNombre').text(nombreCliente);
    $('#postponeModalClienteDireccion').text(direccionCliente);
    $('#postponeModalClienteTelefono').text(telefonoCliente);
    $('#postponeModalClienteCURP').text(identificacionCURP);
    $('#postponeModalMontoAPagar').text(montoAPagar);
    $('#postponeModalMontoCuota').text(montoCuota);

    // Mostrar el modal de Posponer Pago
    $('#postponePaymentModal').modal('show');
}


function posponerPago(prestamoId) {
    $.ajax({
        url: 'posponer_pago.php', // Verifica que esta URL sea correcta
        type: 'POST',
        data: {
            prestamoId: prestamoId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Código para manejar una respuesta exitosa
                $('#postponePaymentModal').modal('hide');
                // Otras actualizaciones de la interfaz de usuario
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            alert('Ocurrió un error al posponer el pago.');
        }
    });
}

// Función para abrir el modal de pago personalizado
function abrirModalPago(prestamoId, montoCuota, nombreCliente, direccionCliente, telefonoCliente, identificacionCURP, montoAPagar) {
    globalPrestamoId = prestamoId;
    globalMontoCuota = montoCuota;

    // Establecer los datos del cliente en el modal de Pagar Cantidad
    $('#customModalClienteNombre').text(nombreCliente);
    $('#customModalClienteDireccion').text(direccionCliente);
    $('#customModalClienteTelefono').text(telefonoCliente);
    $('#customModalClienteCURP').text(identificacionCURP);
    $('#customModalMontoAPagar').text(montoAPagar);
    $('#customModalMontoCuota').text(montoCuota);

    // Configurar el botón de WhatsApp para abrir el modal de WhatsApp con el número correcto
    $('#sendWhatsappButton').off('click').on('click', function() {
        var telefonoCliente = $('#customPaymentModal').data('cliente-telefono');
        var mensajeWhatsapp = 'Hola, hemos recibido tu pago de ' + montoAPagar + '.';
        window.open('https://wa.me/' + telefonoCliente + '?text=' + encodeURIComponent(mensajeWhatsapp));
    });

    // Mostrar el modal de Pagar Cantidad
    $('#customPaymentModal').modal('show');
}

// Función para procesar el pago personalizado
function procesarPagoPersonalizado() {
    var customAmount = $('#customAmount').val();

    if (customAmount <= 0) {
        alert('Ingrese una cantidad válida.');
        return;
    }

    // Realiza el pago personalizado
    procesarPago(globalPrestamoId, customAmount);
    $('#customPaymentModal').modal('hide');
}

// FUNCION DE PARA EL PRESTAMO PARA PASAR MAS TARDE 

document.addEventListener('DOMContentLoaded', function() {
    var botonesMasTarde = document.querySelectorAll('.btn-mas-tarde');

    botonesMasTarde.forEach(function(boton) {
        // Obtener el valor del filtro actual
        var filtroActual = document.querySelector('select[name="filtro"]').value;

        // Comprobar si el filtro es diferente de "mas-tarde" antes de mostrar el botón
        if (filtroActual !== 'mas-tarde') {
            boton.style.display = 'block'; // Mostrar el botón
        } else {
            boton.style.display = 'none'; // Ocultar el botón
        }

        boton.addEventListener('click', function() {
            var prestamoId = this.getAttribute('data-prestamoid');
            pasarMasTarde(prestamoId);
        });
    });
});


function pasarMasTarde(prestamoId) {
    $('#postponeLoanId').text(prestamoId); // Establece el ID del préstamo en el modal
    $('#postponeLoanModal').modal('show'); // Muestra el modal
}

function confirmPostponeLoan() {
    var prestamoId = $('#postponeLoanId').text();

    $.ajax({
        url: 'mas_tarde.php', // Asegúrate de que esta ruta sea correcta
        type: 'POST',
        data: {
            prestamoId: prestamoId
        },
        success: function(response) {
            // Manejar respuesta
            $('#postponeLoanModal').modal('hide');
            location.reload(); // O actualizar la tabla de préstamos según sea necesario
        },
        error: function() {
            alert('Error al procesar la solicitud.');
        }
    });
}