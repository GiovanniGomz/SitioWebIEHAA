$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    iniciarTabla();
    eventos();
    iniciarReportePorResponsable();
}

function iniciarTabla() {
    $('#tabla-activo-fijo').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple'
    });
}

function eventos() {
    document.addEventListener('click', function (e) {
        var btnEliminar = e.target.closest('.btn-eliminar');
        if (btnEliminar) { avisoEliminar(btnEliminar.dataset.id); return; }

        var btnVer = e.target.closest('.btn-ver-archivos');
        if (btnVer) { verArchivos(btnVer); return; }

        var btnQuitar = e.target.closest('.btn-quitar-archivo');
        if (btnQuitar) { quitarArchivoAdjunto(btnQuitar); return; }
    });
}

function iniciarReportePorResponsable() {
    var select = document.getElementById('af-responsable');
    var pdf = document.getElementById('af-responsable-pdf');
    var excel = document.getElementById('af-responsable-excel');
    if (!select || !pdf || !excel) return;

    function actualizar() {
        var valor = encodeURIComponent(select.value || '');
        pdf.href = select.value ? '/reportePDFActivoFijo?responsable=' + valor : '#';
        excel.href = select.value ? '/reporteExcelActivoFijo?responsable=' + valor : '#';
    }

    [pdf, excel].forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!select.value) {
                e.preventDefault();
                Swal.fire('Elegí un responsable', 'Seleccioná primero de quién querés el reporte.', 'info');
            }
        });
    });

    select.addEventListener('change', actualizar);
    actualizar();
}

// Repinta el selector de "reporte por responsable" con la lista más reciente
// (se llama tras registrar/eliminar un bien, sin esperar a recargar la página).
function actualizarSelectResponsables(lista) {
    var select = document.getElementById('af-responsable');
    if (!select || !lista) return;

    var valorPrevio = select.value;
    select.innerHTML = '<option value="">Reporte por responsable...</option>';

    lista.forEach(function (responsable) {
        var option = document.createElement('option');
        option.value = responsable;
        option.textContent = responsable;
        select.appendChild(option);
    });

    if (lista.indexOf(valorPrevio) !== -1) {
        select.value = valorPrevio;
    }

    select.dispatchEvent(new Event('change'));
}

function verArchivos(btn) {
    var lista = document.getElementById('af-archivos-lista');
    var titulo = document.getElementById('af-archivos-titulo');
    var archivos = [];

    try { archivos = JSON.parse(btn.dataset.archivos || '[]'); } catch (e) { archivos = []; }

    titulo.textContent = 'Archivos de "' + (btn.dataset.bien || '') + '"';
    lista.innerHTML = '';

    archivos.forEach(function (a) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML =
            '<span><i class="bi bi-file-earmark-text me-2"></i></span>' +
            '<a class="btn btn-sm btn-outline-secondary ms-2" data-loader></a>';
        li.querySelector('span').append(a.nombre);
        var link = li.querySelector('a');
        link.href = '/descarga-activo-fijo/' + a.id;
        link.innerHTML = '<i class="bi bi-download"></i>';
        lista.appendChild(li);
    });

    if (!archivos.length) {
        lista.innerHTML = '<li class="list-group-item text-muted small">No hay archivos.</li>';
    }

    new bootstrap.Modal(document.getElementById('af-modal-archivos')).show();
}

function quitarArchivoAdjunto(btn) {
    var id = btn.dataset.archivoId;
    var li = btn.closest('li');

    Swal.fire({
        title: '¿Quitar este archivo?',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (!result.isConfirmed) return;

        $.request('activoFijoComponent::onEliminarArchivo', {
            data: { archivo_id: id },
            success: function () {
                if (li) li.remove();
            }
        });
    });
}

function onRegistrar(data) {
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');
        document.querySelector('#btnModalUp').click();
        resetear();
        iniciarTabla();
        actualizarSelectResponsables(data.responsables);
    }
}

function onEliminar(data) {
    if (data.estado === 'exito') {
        Swal.fire(data.mensaje, data.mensaje, 'success');
        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }
        setTimeout(function () { iniciarTabla(); }, 0);
        actualizarSelectResponsables(data.responsables);
    } else if (data.mensaje) {
        Swal.fire('Aviso', data.mensaje, 'info');
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: '¿Eliminar este bien del inventario?',
        text: 'También se eliminarán sus archivos adjuntos.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('activoFijoComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    var bien = data.bien;

    modoModificar();

    var set = function (id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val == null ? '' : val;
    };

    set('id', bien.id);
    set('numero_inventario', bien.numero_inventario);
    set('descripcion_bien', bien.descripcion_bien);
    set('marca', bien.marca);
    set('modelo', bien.modelo);
    set('serie', bien.serie);
    set('responsable', bien.responsable);
    set('estado_bien', bien.estado_bien);
    set('estado', bien.estado);
    set('forma_adquisicion', bien.forma_adquisicion);
    set('fecha_adquisicion', bien.fecha_adquisicion ? String(bien.fecha_adquisicion).substring(0, 10) : '');
    set('precio', bien.precio);
    set('descripcion', bien.descripcion);
    set('observacion', bien.observacion);

    var archivo = document.getElementById('archivos');
    if (archivo) {
        archivo.value = '';
        archivo.dispatchEvent(new Event('change', { bubbles: true }));
    }

    var contenedor = document.getElementById('af-archivos-actuales');
    var lista = document.getElementById('af-lista-archivos-actuales');
    lista.innerHTML = '';

    var archivosBien = bien.archivos || [];

    if (archivosBien.length) {
        contenedor.classList.remove('d-none');
        archivosBien.forEach(function (a) {
            var li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML =
                '<span><i class="bi bi-file-earmark-text me-2"></i></span>' +
                '<span class="d-flex gap-2">' +
                '<a class="btn btn-sm btn-outline-secondary" data-loader><i class="bi bi-download"></i></a>' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-quitar-archivo" data-archivo-id="' + a.id + '"><i class="bi bi-x-lg"></i></button>' +
                '</span>';
            li.querySelector('span').append(a.nombre_original || a.archivo);
            li.querySelector('a').href = '/descarga-activo-fijo/' + a.id;
            lista.appendChild(li);
        });
    } else {
        contenedor.classList.add('d-none');
    }
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar bien';
    document.querySelector('#btnRegistrar').textContent = 'Guardar cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar bien';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.getElementById('formulario').reset();
    document.getElementById('id').value = '';

    var archivo = document.getElementById('archivos');
    if (archivo) archivo.dispatchEvent(new Event('change', { bubbles: true }));

    document.getElementById('af-archivos-actuales').classList.add('d-none');
    document.getElementById('af-lista-archivos-actuales').innerHTML = '';

    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
