$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    iniciarTabla();
    eventos();
    actualizarEtiquetaFecha();
}

function eventos() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar');
        if (btn) avisoEliminar(btn.dataset.id);
    });

    document.querySelectorAll('input[name="tipo"]').forEach(function (radio) {
        radio.addEventListener('change', actualizarEtiquetaFecha);
    });
}

// La etiqueta del campo fecha se adapta según si el documento fue enviado o recibido.
function actualizarEtiquetaFecha() {
    var recibido = document.getElementById('tipo-recibido');
    var label = document.getElementById('fecha-label');
    if (label) label.textContent = recibido && recibido.checked ? 'Fecha de recepción' : 'Fecha de envío';
}

function iniciarTabla() {
    $('#tabla-correspondencia').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple',
        order: [[5, 'desc']]
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
        title: '¿Eliminar este registro?',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('registroCorrespondenciaComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    var registro = data.registro;

    modoModificar();

    document.getElementById('id').value = registro.id;
    document.getElementById('nombre').value = registro.nombre;
    document.getElementById('facultad_id').value = registro.facultad_id;
    document.getElementById('categoria_correspondencia_id').value = registro.categoria_correspondencia_id;
    document.getElementById('fecha').value = (registro.fecha || '').slice(0, 10);
    document.getElementById('archivo').value = '';

    var radio = document.getElementById(registro.tipo === 'recibido' ? 'tipo-recibido' : 'tipo-enviado');
    if (radio) radio.checked = true;
    actualizarEtiquetaFecha();

    var actual = document.getElementById('correspondencia-archivo-actual');
    if (registro.archivo_url) {
        document.getElementById('correspondencia-archivo-enlace').href = registro.archivo_url;
        actual.classList.remove('d-none');
    } else {
        actual.classList.add('d-none');
    }
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar correspondencia';
    document.querySelector('#btnRegistrar').textContent = 'Guardar cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar correspondencia';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.getElementById('formulario').reset();
    document.getElementById('id').value = '';
    document.getElementById('correspondencia-archivo-actual').classList.add('d-none');
    actualizarEtiquetaFecha();
    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
