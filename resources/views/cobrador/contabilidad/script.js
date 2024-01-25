document
  .getElementById("generateReportBtn")
  .addEventListener("click", function () {
    var formData = new FormData(document.getElementById("reportForm"));

    fetch("reportGenerator.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error(
            "Respuesta del servidor no exitosa: " + response.status
          );
        }
      })
      .then((data) => {
        var dataTable = document.getElementById("dataTable");
        dataTable.innerHTML = ""; // Limpiar la tabla antes de agregar nuevos datos

        if (data && Array.isArray(data) && data.length > 0) {
          // Crear encabezados de la tabla a partir de las claves del primer objeto
          let headers = Object.keys(data[0]);
          let headerRow =
            "<thead><tr>" +
            headers.map((header) => `<th>${header}</th>`).join("") +
            "</tr></thead>";
          dataTable.innerHTML += headerRow;

          // Crear filas de la tabla
          let rows = data
            .map((row) => {
              let rowData = headers
                .map((header) => `<td>${row[header]}</td>`)
                .join("");
              return `<tr>${rowData}</tr>`;
            })
            .join("");

          dataTable.innerHTML += "<tbody>" + rows + "</tbody>";
        } else if (data && data.error) {
          // Manejar un error devuelto por el servidor
          dataTable.innerHTML =
            '<tr><td colspan="100%">Error: ' + data.error + "</td></tr>";
        } else {
          // Manejar una respuesta vacía
          dataTable.innerHTML =
            '<tr><td colspan="100%">No se encontraron datos.</td></tr>';
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        var dataTable = document.getElementById("dataTable");
        dataTable.innerHTML =
          '<tr><td colspan="100%">Error: ' + error.message + "</td></tr>";
      });
  });

document.getElementById("exportForm").addEventListener("submit", function () {
  // Configurar los valores de los campos ocultos para la exportación a Excel
  document.getElementById("exportStartDate").value =
    document.getElementById("startDate").value;
  document.getElementById("exportEndDate").value =
    document.getElementById("endDate").value;
  document.getElementById("exportDataTable").value =
    document.getElementById("dataTableSelect").value;
});
