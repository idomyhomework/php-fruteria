// Pide confirmación antes de enviar los formularios con data-confirm (p. ej. eliminar).
document.addEventListener('submit', function (ev) {
    var form = ev.target;
    var msg = form.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) {
        ev.preventDefault();
    }
});

// Búsqueda en vivo del catálogo: oculta las filas que no coinciden.
// Ignora mayúsculas y tildes ("pasion" encuentra "pasión").
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('buscar-fruta');
    if (!input) return;

    var filas = document.querySelectorAll('.tabla-frutas tbody tr');
    var aviso = document.getElementById('sin-resultados');

    function normalizar(texto) {
        return texto.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    }

    function filtrar() {
        var q = normalizar(input.value);
        var visibles = 0;
        filas.forEach(function (fila) {
            var coincide = normalizar(fila.dataset.nombre || '').indexOf(q) !== -1;
            fila.hidden = !coincide;
            if (coincide) visibles++;
        });
        if (aviso) aviso.hidden = visibles > 0 || filas.length === 0;
    }

    input.addEventListener('input', filtrar);
    filtrar(); // por si el navegador recuerda el texto al volver atrás
});
