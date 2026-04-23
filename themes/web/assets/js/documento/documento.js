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

function onCrear(data) {

    console.log(data);

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        document.querySelector('#btnRegistrar').click();
        limpiar('registrar');
    }
}

$(document).on('ajaxDone', '[data-request="documentocomponent::onActualizar"]', function (event, context, data) {

    if (data.status === 'success') {
        flashy.success('¡Modificado correctamente!');
        document.querySelector('#btn-close').click();
        limpiar('actualizar');
    }
});


function limpiar(accion) {
    document.querySelector(`#formulario-${accion} #nombre`).value = '';
    document.querySelector(`#formulario-${accion} #archivo`).value = '';
    document.querySelector(`#formulario-${accion} #archivo_tmp`).value = '';
    document.querySelector(`#formulario-${accion} #id`).value = '';

    if (accion === 'actualizar') {
        document.querySelector(`#formulario-actualizar #pdf-descripcion`).textContent = '';
    }
}

function cargarFormulario(data) {
    const { documento } = data;

    console.log(documento);

    document.querySelector('#nombre').value = documento.nombre;
    document.querySelector('#id').value = documento.id;
    document.querySelector('#archivo_tmp').value = documento.archivo;
    document.querySelector('#pdf-descripcion').textContent = documento.archivo;
}