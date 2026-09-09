$(document).ready(function () {
    iniciarTabla();
    eventos();
});

function iniciarTabla() {
    $('#tabla-solicitudes').DataTable({
        destroy: true,
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        pagingType: "simple",
        order: []
    });
}

function eventos() {
    document.addEventListener('click', function (e) {
        const aprobar = e.target.closest('.btn-aprobar');
        const rechazar = e.target.closest('.btn-rechazar');

        if (aprobar) accion('solicitudesComponent::onAprobar', aprobar.dataset.id, '¿Aprobar esta solicitud?');
        if (rechazar) accion('solicitudesComponent::onRechazar', rechazar.dataset.id, '¿Rechazar esta solicitud?');
    });
}

function accion(handler, id, titulo) {
    Swal.fire({
        title: titulo,
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.request(handler, {
            data: { id: id },
            success: function (data) {
                Swal.fire(data.mensaje, data.mensaje, 'success');
                if (data['#listado-solicitudes']) {
                    document.querySelector('#listado-solicitudes').innerHTML = data['#listado-solicitudes'];
                }
                setTimeout(() => iniciarTabla(), 0);
            }
        });
    });
}
