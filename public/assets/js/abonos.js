// Variables globales para llevar un registro del ID del cliente actual
var clienteIds = [];
var clienteActualIndex = 0;

// Variable para mantener un registro de si se ha ingresado la cantidad de pago
var cantidadPagoIngresada = false;

// Variable global para la cantidad de pago
var cantidadPago;

// Variable global para el número de teléfono del cliente
var numeroTelefonoCliente;

// Función para cargar los datos del cliente
function cargarDatosCliente(clienteId) {
    var clienteId = clienteIds[clienteActualIndex]; 
    $.ajax({
        url: "consulta.php?clienteId=" + clienteId,
        dataType: "json",
        success: function(data) {
            if (data.error) {
                if (data.error === "Cliente inactivo") {
                    // Si el cliente está inactivo, elimina el ID del cliente de la lista y carga el siguiente cliente
                    clienteIds.splice(clienteActualIndex, 1); // Elimina el cliente inactivo de la lista
                    if (clienteIds.length > 0) {
                        // Si aún hay clientes en la lista, carga el siguiente
                        cambiarCliente(1);
                    } else {
                        alert("No hay más clientes activos que mostrar");
                    }
                } else {
                    alert(data.error);
                }
            } else {
                // Rellenar los campos con los datos obtenidos
                $("#cliente-id").text(data.ID);
                $("#cliente-nombre").text(data.Nombre);
                $("#cliente-apellido").text(data.Apellido);
                $("#cliente-domicilio").text(data.Domicilio);
                $("#cliente-telefono").text(data.Telefono);
                $("#cliente-curp").text(data.IdentificacionCURP);
                $("#cliente-zona").text(data.ZonaAsignada);

                $("#prestamo-id").text(data.IDPrestamo);
                $("#prestamo-tasa").text(data.TasaInteres);
                $("#prestamo-fecha-inicio").text(data.FechaInicio);
                $("#prestamo-fecha-vencimiento").text(data.FechaVencimiento);
                $("#prestamo-zona").text(data.Zona);
                $("#prestamo-monto-pagar").text(data.MontoAPagar);
                $("#prestamo-cuota").text(data.Cuota);

                // Almacena el número de teléfono del cliente en una variable global
                numeroTelefonoCliente = data.Telefono; // Asegúrate de que el nombre del campo sea correcto
            }
        },
        error: function() {
            alert("No hay más clientes que mostrar");
        }
    });
}

// Función para registrar el pago
function registrarPago() {
    if (cantidadPagoIngresada) {
        var fechaPago = $("#fecha-pago").val();
        $.ajax({
            url: "registrar_pago.php",
            method: "POST",
            data: {
                clienteId: $("#cliente-id").text(),
                cantidadPago: cantidadPago,
                fechaPago: fechaPago,
            },
            success: function(response) {
                // Actualiza el monto a pagar en la página
                $("#prestamo-monto-pagar").text(response);

                // Limpiar los campos de cantidad de pago
                $("#cantidad-pago").val("");
                // Cierra el modal de confirmación
                $("#confirmarPagoModal").modal("hide");
                // Abre el modal de pago confirmado
                $("#pagoConfirmadoModal").modal("show");

                // Cambia automáticamente al siguiente cliente después de 2 segundos
                setTimeout(function() {
                    cambiarCliente(1);
                }, 2000);
            },
            error: function() {
                alert("Error al registrar el pago");
            }
        });
    } else {
        alert("Por favor, ingrese la cantidad de pago antes de registrarlo.");
    }
}

// Función para cambiar al cliente anterior o siguiente
function cambiarCliente(delta) {
    clienteActualIndex += delta;
    if (clienteActualIndex < 0) {
        clienteActualIndex = 0;
    } else if (clienteActualIndex >= clienteIds.length) {
        clienteActualIndex = clienteIds.length - 1;
    }
    cargarDatosCliente(clienteIds[clienteActualIndex]);
}

// Asocia el evento click del botón "Registrar Pago" a la función que muestra el modal de confirmación
$("#registrarPago").click(function() {
    // Verifica si se ha ingresado una cantidad de pago
    if ($("#cantidad-pago").val() !== "") {
        cantidadPagoIngresada = true;
        // Asigna el valor de la cantidad de pago a la variable global
        cantidadPago = $("#cantidad-pago").val();
        // Abre el modal de confirmación
        $("#confirmarPagoModal").modal("show");
    } else {
        alert("Por favor, ingrese la cantidad de pago antes de registrarlo.");
    }
});

// Asocia el evento click del botón "Confirmar" en el modal de confirmación a la función para registrar el pago
$("#confirmarPago").click(function() {
    registrarPago(); // Registra el pago
});

// Asocia el evento clic a los botones de navegación
$("#anteriorCliente").click(function() {
    cambiarCliente(-1); // Cambiar al cliente anterior
});

$("#siguienteCliente").click(function() {
    cambiarCliente(1); // Cambiar al siguiente cliente
});

// Función para cargar la lista de IDs de clientes desde el servidor
function cargarListaDeClientes() {
    $.ajax({
        url: "obtener_lista_clientes.php",
        dataType: "json",
        success: function(data) {
            clienteIds = data;
            if (clienteIds.length > 0) {
                cargarDatosCliente(clienteIds[0]);
            } else {
                alert("No hay clientes en la base de datos.");
            }
        },
        error: function() {
            alert("Error al cargar la lista de clientes.");
        }
    });
}

// Asocia el evento click del botón "Generar Factura" al enlace a la página de facturación
$("#generarFacturaButton").click(function() {
    // Obtener los datos necesarios para la factura
    var clienteId = $("#cliente-id").text();
    var clienteNombre = $("#cliente-nombre").text();
    var clienteApellido = $("#cliente-apellido").text();
    var prestamoId = $("#prestamo-id").text();

    // Redireccionar a la página de facturación con los parámetros necesarios, incluyendo la cantidad de pago
    window.location.href = "facturacionAbonos.php?clienteId=" + clienteId + "&clienteNombre=" + clienteNombre + "&clienteApellido=" + clienteApellido + "&prestamoId=" + prestamoId + "&cantidadPago=" + cantidadPago;
});

// Asocia el evento click del botón "Compartir por WhatsApp" al enlace de WhatsApp
$("#compartirPorWhatsAppButton").click(function() {
    // Obtener los datos necesarios para compartir por WhatsApp
    var clienteNombre = $("#cliente-nombre").text();
    var clienteApellido = $("#cliente-apellido").text();
    var mensaje = `Hola *${clienteNombre} ${clienteApellido}*, has pagado *${cantidadPago}* de tu préstamo.\nDetalles del préstamo:\n`;

    // Agregar detalles del préstamo al mensaje
    mensaje += `*ID del Préstamo:* ${$("#prestamo-id").text()}\n`;
    mensaje += `*Tasa de Interés:* % ${$("#prestamo-tasa").text()}\n`;
    mensaje += `*Fecha de Inicio:* ${$("#prestamo-fecha-inicio").text()}\n`;
    mensaje += `*Fecha de Vencimiento:* ${$("#prestamo-fecha-vencimiento").text()}\n`;
    mensaje += `*Monto Total del Préstamo:* ${$("#prestamo-monto-pagar").text()}\n`;  // Agregar el monto total del préstamo aquí

    // Obtener otros detalles del cliente y agregarlos al mensaje
    mensaje += `*Domicilio:* ${$("#cliente-domicilio").text()}\n`;
    mensaje += `*Teléfono:* ${$("#cliente-telefono").text()}\n`;
    mensaje += `*Identificación CURP:* ${$("#cliente-curp").text()}\n`;
    mensaje += `*Zona Asignada:* ${$("#cliente-zona").text()}\n`;

    // Obtener el número de teléfono del cliente desde la variable global
    var numeroTelefonoCliente = window.numeroTelefonoCliente;

    // Construye la URL de WhatsApp con el número de teléfono y el mensaje
    var whatsappURL = `https://wa.me/${numeroTelefonoCliente}?text=${encodeURIComponent(mensaje)}`;

    // Abre la URL en una nueva ventana o pestaña
    window.open(whatsappURL, '_blank');
});

// Llama a la función para cargar la lista de IDs de clientes al cargar la página
cargarListaDeClientes();
