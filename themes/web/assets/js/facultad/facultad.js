$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    iniciarTabla();
    eventos();
}

function eventos() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar');

        if (btn) {
            avisoEliminar(btn.dataset.id);
        }
    });
}

function iniciarTabla() {
    $('#tabla-facultades').DataTable({
        destroy: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
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

        iniciarTabla();
    }
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

        setTimeout(() => {
            iniciarTabla();
        }, 0);
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: "¿Eliminar Facultad?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('facultadComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    onEliminar(data);
                }
            });
        }
    });
}

function cargarFormulario(data) {
    const { facultad } = data;

    modoModificar();

    document.querySelector('#nombre').value = facultad.nombre;
    document.querySelector('#id').value = facultad.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Modificar Facultad';
    btnRegistrar.textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Registrar Facultad';
    btnRegistrar.textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#nombre').value = '';
    document.querySelector('#id').value = '';

    limpiarErrores();
}

function limpiarErrores() {
    const errores = document.querySelectorAll('.validacion-descripcion');

    errores.forEach(error => {
        error.textContent = '';
    });
}