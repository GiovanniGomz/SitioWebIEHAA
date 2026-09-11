$(document).ready(function () {
    iniciarApp();
});

var mensajeActualId = null;

function iniciarApp() {
    iniciarTabla();
    eventos();
}

function iniciarTabla() {
    $('#tabla-mensajes-contacto').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple',
        order: []
    });
}

function eventos() {
    document.addEventListener('click', function (e) {
        var btnVer = e.target.closest('.btn-ver-mensaje');
        if (btnVer) { verMensaje(btnVer); return; }

        var btnEliminar = e.target.closest('.btn-eliminar');
        if (btnEliminar) { avisoEliminar(btnEliminar.dataset.id); return; }

        if (e.target.closest('#msj-btn-eliminar')) {
            bootstrap.Modal.getInstance(document.getElementById('modal-mensaje')).hide();
            avisoEliminar(mensajeActualId);
        }
    });
}

function verMensaje(btn) {
    mensajeActualId = btn.dataset.id;

    document.getElementById('msj-asunto').textContent = btn.dataset.asunto;
    document.getElementById('msj-nombre').textContent = btn.dataset.nombre;
    document.getElementById('msj-email').textContent = btn.dataset.email;
    document.getElementById('msj-email').href = 'mailto:' + btn.dataset.email;
    document.getElementById('msj-texto').textContent = btn.dataset.mensaje;
    document.getElementById('msj-fecha').textContent = btn.dataset.fecha;
    document.getElementById('msj-responder').href =
        'mailto:' + btn.dataset.email + '?subject=' + encodeURIComponent('Re: ' + btn.dataset.asunto);

    new bootstrap.Modal(document.getElementById('modal-mensaje')).show();

    // Marcar como leído en segundo plano, sin interrumpir la lectura.
    $.request('mensajeContactoComponent::onMarcarLeido', {
        data: { id: mensajeActualId },
        success: function (data) {
            if (data['#listado']) {
                document.querySelector('#listado').innerHTML = data['#listado'];
                setTimeout(function () { iniciarTabla(); }, 0);
            }
            actualizarBadgeSidebar(data.noLeidos);
        }
    });
}

function avisoEliminar(id) {
    Swal.fire({
        title: '¿Eliminar este mensaje?',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('mensajeContactoComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    if (data.estado === 'exito') {
                        Swal.fire(data.mensaje, data.mensaje, 'success');
                    } else if (data.mensaje) {
                        Swal.fire('Aviso', data.mensaje, 'info');
                    }
                    if (data['#listado']) {
                        document.querySelector('#listado').innerHTML = data['#listado'];
                    }
                    setTimeout(function () { iniciarTabla(); }, 0);
                    actualizarBadgeSidebar(data.noLeidos);
                }
            });
        }
    });
}

function actualizarBadgeSidebar(noLeidos) {
    if (typeof noLeidos !== 'number') return;
    var badge = document.getElementById('mensajes-badge');
    if (!badge) return;
    badge.textContent = noLeidos;
    badge.classList.toggle('d-none', noLeidos <= 0);
}
