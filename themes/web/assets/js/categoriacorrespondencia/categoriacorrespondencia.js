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
        if (btn) avisoEliminar(btn.dataset.id);
    });
}

function iniciarTabla() {
    $('#tabla-categoria-correspondencia').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple'
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
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');
        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }
        setTimeout(function () { iniciarTabla(); }, 0);
    } else if (data.mensaje) {
        Swal.fire('Aviso', data.mensaje, 'info');
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: '¿Eliminar esta categoría?',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('categoriaCorrespondenciaComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    modoModificar();
    document.querySelector('#nombre').value = data.categoria.nombre;
    document.querySelector('#id').value = data.categoria.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar categoría';
    document.querySelector('#btnRegistrar').textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar categoría';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#nombre').value = '';
    document.querySelector('#id').value = '';
    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
