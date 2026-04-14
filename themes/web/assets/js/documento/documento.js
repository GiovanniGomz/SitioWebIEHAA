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

$(document).on('ajaxDone', '[data-request="documentocomponent::onCreate"]', function (event, context, data) {

    if (data.status === 'success') {
        flashy.success('¡Almacenado correctamente!');
        document.querySelector('#btnRegistrar').click();
        limpiar();
    }
});

function limpiar() {
    document.querySelector('#nombre').value = '';
    document.querySelector('#archivo').value = '';
}