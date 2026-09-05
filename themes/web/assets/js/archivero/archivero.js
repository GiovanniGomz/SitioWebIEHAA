$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    eventos();
}

function eventos() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.boton-eliminar');
        const btnRedireccionar = e.target.closest('.btnRedireccionar');

        if (btn) {
            avisoEliminar(btn.dataset.id);
        }

        if (btnRedireccionar) {
            redireccionar(btnRedireccionar.dataset.id);
        }
    });
}

function redireccionar(url) {
    //Almacenar cookie
    let nombre = 'mostrarGaveta';
    guardarCookie(nombre, url);

    window.location.href = '/gavetas?id=' + url;
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
    }
}

function cargarFormulario(data) {
    const { archivero } = data;

    modoModificar();

    document.querySelector('#codigo').value = archivero.codigo;
    document.querySelector('#id').value = archivero.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Modificar Archivero';
    btnRegistrar.textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Registrar Archivero';
    btnRegistrar.textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#codigo').value = '';
    document.querySelector('#id').value = '';

    limpiarErrores();
}

function limpiarErrores() {
    const errores = document.querySelectorAll('.validacion-descripcion');

    errores.forEach(error => {
        error.textContent = '';
    });
}

function avisoEliminar(id) {

    Swal.fire({
        title: "¿Eliminar Archivero?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('archiveroComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    onEliminar(data);
                }
            });
        }
    });
}

function onEliminar(data) {

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }
    }
}