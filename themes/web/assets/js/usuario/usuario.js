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
    $('#tabla-usuarios').DataTable({
        destroy: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        pagingType: "simple"
    });
}

function onRegistrar(data) {
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');

        document.querySelector('#btnModalUp').click();
        resetear();

        iniciarTabla();
    }
}

function onEliminar(data) {
    if (data.estado === 'error') {
        Swal.fire('No se pudo eliminar', data.mensaje, 'error');
        return;
    }

    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');

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
        title: "¿Eliminar usuario?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('usuarioComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    onEliminar(data);
                }
            });
        }
    });
}

function cargarFormulario(data) {
    const { usuario } = data;

    modoModificar();

    document.querySelector('#nombre').value = usuario.nombre;
    document.querySelector('#email').value = usuario.email;
    document.querySelector('#password').value = '';
    document.querySelector('#rol').value = usuario.rol;
    document.querySelector('#activo').checked = !!usuario.activo;
    document.querySelector('#id').value = usuario.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar Usuario';
    document.querySelector('#btnRegistrar').textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar Usuario';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#nombre').value = '';
    document.querySelector('#email').value = '';
    document.querySelector('#password').value = '';
    document.querySelector('#rol').value = 'editor';
    document.querySelector('#activo').checked = true;
    document.querySelector('#id').value = '';

    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(error => {
        error.textContent = '';
    });
}
