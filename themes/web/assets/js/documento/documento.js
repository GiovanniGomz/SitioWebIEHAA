$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    inicarTabla();
}

function inicarTabla() {
    $('#tabla-documento').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        pagingType: "simple"
    });
}