$(document).ready(function () {
    // Carga los usuarios de la base de datos y los agrega al menú desplegable
    function loadUsuarios() {
        $.ajax({
            url: "load_usuarios.php",
            type: "GET",
            success: function (response) {
                const usuarios = JSON.parse(response);
                const selectUsuario = $("#usuario");
                usuarios.forEach(function (usuario) {
                    selectUsuario.append(new Option(usuario.Nombre, usuario.ID));
                });
            },
            error: function () {
                alert("Error al cargar los usuarios.");
            }
        });
    }

    // Carga la lista de pagos desde la base de datos
    function loadPagos() {
        $.ajax({
            url: "load_pagos.php",
            type: "GET",
            success: function (response) {
                const result = JSON.parse(response);
                $("#pagos-list").html(result.data);
                $("#totalPagado").text(result.total);
            },
            error: function () {
                alert("Error al cargar la lista de pagos.");
            }
        });
    }

    // Maneja la presentación de los datos recibidos
    function handleData(response) {
        const result = JSON.parse(response);
        $("#pagos-list").html(result.data);
        $("#totalPagado").text(result.total);
    }

    // Evento de envío del formulario de filtro
    $("#filter-form").submit(function (e) {
        e.preventDefault();
        const fechaDesde = $("#fechaDesde").val();
        const fechaHasta = $("#fechaHasta").val();
        const usuario = $("#usuario").val();

        $.ajax({
            url: "load_pagos.php",
            type: "GET",
            data: { fechaDesde: fechaDesde, fechaHasta: fechaHasta, usuario: usuario },
            success: function (response) {
                handleData(response);
            },
            error: function () {
                alert("Error al cargar la lista de pagos.");
            }
        });
    });

    // Cargar los usuarios y los pagos al iniciar la página
    loadUsuarios();
    loadPagos();
});
