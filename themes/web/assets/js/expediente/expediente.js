$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    iniciarTabla();
    eventos();
    sincronizarUnidad();
    sincronizarFormato();
}

function eventos() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar');
        if (btn) avisoEliminar(btn.dataset.id);
    });

    var unidad = document.getElementById('unidad_instalacion');
    if (unidad) unidad.addEventListener('change', sincronizarUnidad);

    document.querySelectorAll('input[name="formato"]').forEach(function (r) {
        r.addEventListener('change', sincronizarFormato);
    });
}

function iniciarTabla() {
    $('#tabla-expediente').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple'
    });
}

function sincronizarUnidad() {
    var unidad = document.getElementById('unidad_instalacion');
    var grupo = document.getElementById('grupo-unidad-otro');
    if (!unidad || !grupo) return;
    grupo.classList.toggle('d-none', unidad.value !== 'Otro');
}

function sincronizarFormato() {
    var digital = document.getElementById('formato-digital');
    var label = document.getElementById('archivo-label');
    if (!label) return;
    label.textContent = (digital && digital.checked)
        ? 'Archivo del documento (obligatorio para formato digital)'
        : 'Archivo del documento (opcional)';
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
        title: '¿Eliminar expediente?',
        text: 'Se eliminará también el documento adjunto, si tiene.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('expedienteComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    const { expediente } = data;

    modoModificar();

    var set = function (id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val == null ? '' : val;
    };

    set('id', expediente.id);
    set('serie', expediente.serie);
    set('subserie', expediente.subserie);
    set('asunto', expediente.asunto);
    set('cantidad_folios', expediente.cantidad_folios);
    set('fecha_inicial', expediente.fecha_inicial ? String(expediente.fecha_inicial).substring(0, 10) : '');
    set('fecha_final', expediente.fecha_final ? String(expediente.fecha_final).substring(0, 10) : '');
    set('unidad_instalacion', expediente.unidad_instalacion);
    set('unidad_instalacion_otro', expediente.unidad_instalacion_otro);
    set('volumen', expediente.volumen);
    set('soporte', expediente.soporte);
    set('estado_conservacion', expediente.estado_conservacion);
    set('sig_fila', expediente.sig_fila);
    set('sig_estante', expediente.sig_estante);
    set('sig_anaquel', expediente.sig_anaquel);
    set('sig_posicion', expediente.sig_posicion);

    var radio = document.getElementById(expediente.formato === 'digital' ? 'formato-digital' : 'formato-impreso');
    if (radio) radio.checked = true;

    var archivo = document.getElementById('archivo');
    if (archivo) {
        archivo.value = '';
        archivo.dispatchEvent(new Event('change', { bubbles: true }));
    }

    var actual = document.getElementById('archivo-actual');
    if (actual) {
        actual.textContent = expediente.archivo
            ? 'Documento actual: ' + expediente.archivo + ' (subí uno nuevo para reemplazarlo).'
            : '';
    }

    sincronizarUnidad();
    sincronizarFormato();
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar expediente';
    document.querySelector('#btnRegistrar').textContent = 'Guardar cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar expediente';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.getElementById('formulario').reset();
    document.getElementById('id').value = '';

    var archivo = document.getElementById('archivo');
    if (archivo) archivo.dispatchEvent(new Event('change', { bubbles: true }));

    var actual = document.getElementById('archivo-actual');
    if (actual) actual.textContent = '';

    limpiarErrores();
    sincronizarUnidad();
    sincronizarFormato();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
