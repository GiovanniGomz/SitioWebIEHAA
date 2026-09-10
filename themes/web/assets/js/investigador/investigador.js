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
    $('#tabla-investigador').DataTable({
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
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');

        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }

        setTimeout(() => iniciarTabla(), 0);
    } else if (data.mensaje) {
        Swal.fire('Aviso', data.mensaje, 'info');
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: "¿Eliminar Investigador?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('investigadorComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    onEliminar(data);
                }
            });
        }
    });
}

function cargarFormulario(data) {
    const { investigador } = data;

    modoModificar();

    document.querySelector('#nombre').value = investigador.nombre;
    document.querySelector('#apellido').value = investigador.apellido;
    document.querySelector('#carnet').value = (investigador.carnet || '').toUpperCase();
    document.querySelector('#telefono').value = investigador.telefono;
    document.querySelector('#facultad').value = investigador.facultad_id;
    document.querySelector('#categoria_investigador').value = investigador.categoria_investigador_id;
    document.querySelector('#email').value = investigador.email;
    document.querySelector('#tipo_investigador').value = investigador.tipo_investigador_id;
    document.querySelector('#sexo').value = investigador.sexo;
    document.querySelector('#descripcion').value = investigador.descripcion;
    document.querySelector('#id').value = investigador.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar Investigador';
    document.querySelector('#btnRegistrar').textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar Investigador';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    ['#nombre', '#apellido', '#carnet', '#telefono', '#facultad', '#categoria_investigador',
        '#email', '#tipo_investigador', '#sexo', '#descripcion', '#id'].forEach(sel => {
            const el = document.querySelector(sel);
            if (el) el.value = '';
        });

    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(e => e.textContent = '');
}
