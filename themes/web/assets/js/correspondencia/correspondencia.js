$(document).ready(function () {
    iniciarApp();
});

var corModal;

function iniciarApp() {
    corModal = new bootstrap.Modal(document.getElementById('modal-correspondencia'));
    iniciarTablaHistorial();
    eventos();
}

function iniciarTablaHistorial() {
    $('#tabla-historial').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple',
        order: []
    });
}

function eventos() {
    document.addEventListener('click', function (e) {
        var aceptar = e.target.closest('.btn-aceptar');
        var rechazar = e.target.closest('.btn-rechazar');
        var eliminar = e.target.closest('.btn-eliminar');
        var reenviar = e.target.closest('.btn-reenviar');

        if (aceptar) return abrirAceptar(aceptar.dataset.id);
        if (rechazar) return rechazarSolicitud(rechazar.dataset.id);
        if (eliminar) return eliminarCorrespondencia(eliminar.dataset.id);
        if (reenviar) return reenviarCorreo(reenviar.dataset.id);
    });
}

function abrirAceptar(idSolicitud) {
    $.request('correspondenciaComponent::onGetSolicitud', {
        data: { id: idSolicitud },
        success: function (data) {
            var s = data.solicitud;

            resetear();
            modoAceptar();

            document.getElementById('solicitud_id').value = s.id;
            document.getElementById('remitente').value = s.nombre_solicitante || '';
            document.getElementById('descripcion_documento').value =
                'Préstamo de "' + (s.documento || 'documento') + '"' + (s.mensaje ? ' — ' + s.mensaje : '');
            document.getElementById('fecha_atencion').value = fechaHoraLocal();

            document.getElementById('cor-info-solicitante').textContent = s.nombre_solicitante;
            document.getElementById('cor-info-email').textContent = s.email_solicitante + (s.telefono_solicitante ? ' · ' + s.telefono_solicitante : '');
            document.getElementById('cor-info-documento').textContent = s.coleccion + ' — ' + (s.documento || 'Documento no disponible') + (s.tiene_digital ? ' (con copia digital)' : ' (sin copia digital)');
            document.getElementById('cor-info-ubicacion').textContent = s.ubicacion;

            var mensajeWrap = document.getElementById('cor-info-mensaje-wrap');
            if (s.mensaje) {
                document.getElementById('cor-info-mensaje').textContent = '"' + s.mensaje + '"';
                mensajeWrap.classList.remove('d-none');
            } else {
                mensajeWrap.classList.add('d-none');
            }

            document.getElementById('cor-info-solicitud').classList.remove('d-none');

            corModal.show();
        }
    });
}

function fechaHoraLocal() {
    var d = new Date();
    var pad = function (n) { return String(n).padStart(2, '0'); };
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function rechazarSolicitud(id) {
    Swal.fire({
        title: '¿Rechazar esta solicitud?',
        input: 'textarea',
        inputPlaceholder: 'Motivo (opcional)...',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#b3261e'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.request('correspondenciaComponent::onRechazar', {
            data: { id: id, motivo: result.value || '' },
            success: function (data) {
                Swal.fire('Listo', data.mensaje, 'success');
                actualizarListados(data);
            }
        });
    });
}

function eliminarCorrespondencia(id) {
    Swal.fire({
        title: '¿Eliminar este registro de correspondencia?',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.request('correspondenciaComponent::onEliminar', {
            data: { id: id },
            success: function (data) {
                Swal.fire('Listo', data.mensaje, 'success');
                actualizarListados(data);
            }
        });
    });
}

function reenviarCorreo(id) {
    Swal.fire({
        title: '¿Reenviar el correo al solicitante?',
        showCancelButton: true,
        confirmButtonText: 'Sí, reenviar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.request('correspondenciaComponent::onReenviarCorreo', {
            data: { id: id },
            success: function (data) {
                Swal.fire(data.estado === 'exito' ? 'Listo' : 'Aviso', data.mensaje, data.estado === 'exito' ? 'success' : 'warning');
            }
        });
    });
}

function actualizarListados() {
    setTimeout(function () {
        iniciarTablaHistorial();
    }, 0);
}

function onRegistrar(data) {
    if (data.estado === 'exito') {
        Swal.fire('Listo', data.mensaje, 'success');
        corModal.hide();
        resetear();
        actualizarListados();
    }
}

function cargarFormularioEdicion(data) {
    var c = data.correspondencia;

    resetear();
    modoEditar();

    document.getElementById('id').value = c.id;
    document.getElementById('categoria_correspondencia_id').value = c.categoria_correspondencia_id;
    document.getElementById('remitente').value = c.remitente || '';
    document.getElementById('descripcion_documento').value = c.descripcion_documento || '';
    document.getElementById('fecha_atencion').value = c.fecha_atencion || '';

    corModal.show();
}

function modoAceptar() {
    document.getElementById('formulario-titulo').textContent = 'Aceptar solicitud';
    document.getElementById('btnRegistrar').textContent = 'Aceptar y enviar';
}

function modoEditar() {
    document.getElementById('formulario-titulo').textContent = 'Modificar correspondencia';
    document.getElementById('btnRegistrar').textContent = 'Guardar cambios';
    document.getElementById('cor-info-solicitud').classList.add('d-none');
}

function resetear() {
    document.getElementById('formulario').reset();
    document.getElementById('id').value = '';
    document.getElementById('solicitud_id').value = '';
    document.getElementById('cor-info-solicitud').classList.add('d-none');
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
