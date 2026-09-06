$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    eventos();
    subMenuDinamico();
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

    let nombre = 'mostrarColeccion';
    guardarCookie(nombre, url);

    window.location.href = '/colecciones?id=' + url;
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
    const { anaquel } = data;

    modoModificar();

    document.querySelector('#codigo').value = anaquel.codigo;
    document.querySelector('#id').value = anaquel.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Modificar Anaquel';
    btnRegistrar.textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Registrar Anaquel';
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
        title: "¿Eliminar Anaquel?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('anaquelComponent::onEliminar', {
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