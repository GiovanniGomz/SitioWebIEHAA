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
    $('#tabla-proyecto').DataTable({
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
        title: "¿Eliminar proyecto?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('proyectoComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    const { proyecto } = data;

    modoModificar();

    document.querySelector('#titulo').value = proyecto.titulo;
    document.querySelector('#investigador_id').value = proyecto.investigador_id;
    document.querySelector('#descripcion').value = proyecto.descripcion;
    document.querySelector('#detalle').value = proyecto.detalle || '';
    document.querySelector('#id').value = proyecto.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar Proyecto';
    document.querySelector('#btnRegistrar').textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar Proyecto';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.querySelector('#titulo').value = '';
    document.querySelector('#investigador_id').value = '';
    document.querySelector('#descripcion').value = '';
    document.querySelector('#detalle').value = '';
    document.querySelector('#id').value = '';
    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(e => e.textContent = '');
}
