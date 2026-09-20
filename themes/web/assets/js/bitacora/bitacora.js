$(document).ready(function () {
    iniciarTabla();

    document.getElementById('btn-filtrar').addEventListener('click', filtrar);
    document.getElementById('btn-limpiar-filtros').addEventListener('click', function () {
        document.getElementById('filtros-bitacora').reset();
        filtrar();
    });
});

function iniciarTabla() {
    $('#tabla-bitacora').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple',
        order: [[0, 'desc']],
        pageLength: 25
    });
}

function valoresFiltro() {
    var form = document.getElementById('filtros-bitacora');
    var datos = {};
    ['modulo', 'accion', 'desde', 'hasta'].forEach(function (campo) {
        datos[campo] = form.elements[campo].value;
    });
    return datos;
}

function filtrar() {
    var datos = valoresFiltro();

    $.request('bitacoraComponent::onFiltrar', {
        data: datos,
        success: function (data) {
            if (!data['#listado']) return;

            if ($.fn.DataTable.isDataTable('#tabla-bitacora')) {
                $('#tabla-bitacora').DataTable().destroy();
            }
            document.querySelector('#listado').innerHTML = data['#listado'];
            iniciarTabla();
            actualizarEnlacesReporte(datos);
        }
    });
}

// Los reportes respetan los filtros que se están viendo en pantalla.
function actualizarEnlacesReporte(datos) {
    var qs = Object.keys(datos).filter(function (k) { return datos[k]; }).map(function (k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(datos[k]);
    }).join('&');

    document.getElementById('bitacora-pdf').href = '/reportePDFBitacora' + (qs ? '?' + qs : '');
    document.getElementById('bitacora-excel').href = '/reporteExcelBitacora' + (qs ? '?' + qs : '');
}
