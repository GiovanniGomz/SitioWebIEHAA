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

function onRegistrar(data) {

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        document.querySelector('#btnModalUp').click();
        resetear();

        inicarTabla();
    }
}

function onEliminar(data) {

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        inicarTabla();
    }
}

function avisoEliminar() {

}

function cargarFormulario(data) {
    const { documento } = data;
    const contenedorPDF = document.querySelector('#contenedor-pdf');

    modoModificar();

    document.querySelector('#nombre').value = documento.nombre;
    document.querySelector('#id').value = documento.id;
    document.querySelector('#archivo_tmp').value = documento.archivo;

    if (contenedorPDF) {
        contenedorPDF.classList.remove('ocultar');
    }

    document.querySelector('#pdf-descripcion').textContent = documento.archivo;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Modificar Documento';
    btnRegistrar.textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Registrar Documento';
    btnRegistrar.textContent = 'Registrar';
}

function limpiar() {
    const contenedorPDF = document.querySelector('#contenedor-pdf');

    if (contenedorPDF) {
        contenedorPDF.classList.add('ocultar');
    }

    document.querySelector('#nombre').value = '';
    document.querySelector('#archivo').value = '';
    document.querySelector('#archivo_tmp').value = '';
    document.querySelector('#id').value = '';
}