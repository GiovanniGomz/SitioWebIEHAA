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
    $('#tabla-publicacion').DataTable({
        destroy: true,
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
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
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');
        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }
        setTimeout(() => iniciarTabla(), 0);
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: "¿Eliminar publicación?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('publicacionComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    const { publicacion } = data;

    modoModificar();

    document.querySelector('#titulo').value = publicacion.titulo;
    document.querySelector('#tipo_publicacion_id').value = publicacion.tipo_publicacion_id;
    document.querySelector('#investigador_id').value = publicacion.investigador_id;
    document.querySelector('#fecha').value = publicacion.fecha ? publicacion.fecha.substring(0, 10) : '';
    document.querySelector('#url').value = publicacion.url || '';
    document.querySelector('#descripcion').value = publicacion.descripcion;
    document.querySelector('#archivo').value = '';
    document.querySelector('#archivo').dispatchEvent(new Event('change', { bubbles: true }));
    document.querySelector('#id').value = publicacion.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar Publicación';
    document.querySelector('#btnRegistrar').textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar Publicación';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#titulo').value = '';
    document.querySelector('#tipo_publicacion_id').value = '';
    document.querySelector('#investigador_id').value = '';
    document.querySelector('#fecha').value = '';
    document.querySelector('#url').value = '';
    document.querySelector('#descripcion').value = '';
    document.querySelector('#archivo').value = '';
    document.querySelector('#archivo').dispatchEvent(new Event('change', { bubbles: true }));
    document.querySelector('#id').value = '';
    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(e => e.textContent = '');
}
