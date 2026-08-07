document.addEventListener('DOMContentLoaded', function () {
    new List('publicacionesList', {
        valueNames: ['titulo', 'descripcion'],
        page: 6,
        pagination: true
    });
});