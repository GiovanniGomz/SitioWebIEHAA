$(document).ready(function () {
    iniciarTabla();

    document.getElementById('btn-respaldar').addEventListener('click', respaldarAhora);

    document.addEventListener('click', function (e) {
        var eliminar = e.target.closest('.btn-eliminar');
        if (eliminar) return avisoEliminar(eliminar.dataset.archivo);

        var descargar = e.target.closest('.btn-descargar');
        if (descargar) return descargarConSelector(descargar.dataset.archivo);
    });
});

function iniciarTabla() {
    $('#tabla-respaldos').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple',
        order: [[2, 'desc']]
    });
}

function refrescarListado(data) {
    if (data['#listado']) {
        if ($.fn.DataTable.isDataTable('#tabla-respaldos')) {
            $('#tabla-respaldos').DataTable().destroy();
        }
        document.querySelector('#listado').innerHTML = data['#listado'];
        iniciarTabla();
    }
    if (data['#estado-automatico']) {
        document.querySelector('#estado-automatico').innerHTML = data['#estado-automatico'];
    }
}

function respaldarAhora() {
    var boton = document.getElementById('btn-respaldar');
    boton.disabled = true;

    $.request('respaldoComponent::onRespaldar', {
        success: function (data) {
            boton.disabled = false;
            refrescarListado(data);

            if (data.estado !== 'exito') {
                Swal.fire('Aviso', data.mensaje, 'error');
                return;
            }

            Swal.fire({
                title: data.mensaje,
                text: 'Ya se guardó en la carpeta del proyecto. ¿Querés guardar también una copia en tu PC?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Descargar a mi PC',
                cancelButtonText: 'Ahora no'
            }).then(function (r) {
                if (r.isConfirmed) descargarConSelector(data.archivo);
            });
        },
        error: function () {
            boton.disabled = false;
        }
    });
}

/**
 * Chrome y Edge permiten elegir en qué carpeta guardar el archivo
 * (showSaveFilePicker). En otros navegadores se usa la descarga normal, que
 * guarda en la carpeta de descargas configurada en el navegador.
 */
function descargarConSelector(nombre) {
    var url = '/respaldos/descargar/' + encodeURIComponent(nombre);

    if (!window.showSaveFilePicker) {
        window.location.href = url;
        return;
    }

    window.showSaveFilePicker({
        suggestedName: nombre,
        types: [{ description: 'Respaldo de base de datos', accept: { 'application/octet-stream': ['.dump'] } }]
    }).then(function (handle) {
        return fetch(url, { credentials: 'same-origin' }).then(function (resp) {
            var tipo = resp.headers.get('Content-Type') || '';
            if (!resp.ok || tipo.indexOf('text/html') === 0) {
                throw new Error('descarga');
            }
            return resp.blob();
        }).then(function (blob) {
            return handle.createWritable().then(function (writable) {
                return writable.write(blob).then(function () { return writable.close(); });
            });
        }).then(function () {
            Swal.fire('Guardado', 'El respaldo se guardó en tu PC.', 'success');
        });
    }).catch(function (err) {
        if (err && err.name === 'AbortError') return; // el usuario canceló el selector
        Swal.fire('Aviso', 'No se pudo guardar el respaldo. Intentá de nuevo.', 'error');
    });
}

function avisoEliminar(nombre) {
    Swal.fire({
        title: '¿Eliminar este respaldo?',
        text: nombre,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.request('respaldoComponent::onEliminar', {
            data: { archivo: nombre },
            success: function (data) {
                refrescarListado(data);
                if (data.estado === 'exito') {
                    Swal.fire(data.mensaje, data.mensaje, 'success');
                } else if (data.mensaje) {
                    Swal.fire('Aviso', data.mensaje, 'info');
                }
            }
        });
    });
}

function onGuardarAjustes(data) {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
    refrescarListado(data);

    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');
    }
}
