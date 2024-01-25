function toggleDetalles(btn) {
    var detalles = document.querySelectorAll('.detalle-oculto');
    var isHidden = Array.from(detalles).some(d => d.style.display === 'none' || d.style.display === '');

    detalles.forEach(detalle => {
        detalle.style.display = isHidden ? 'block' : 'none';
    });

    btn.classList.toggle('activo'); // Agrega o quita la clase 'activo'
}