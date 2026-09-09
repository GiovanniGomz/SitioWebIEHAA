window.addEventListener('load', function () {
    'use strict';

    var grid = document.getElementById('cedjag-grid');
    if (!grid) return;

    var buscar = document.getElementById('cedjag-buscar');
    var filtro = document.getElementById('cedjag-filtro');
    var sinResultados = document.getElementById('cedjag-sin-resultados');

    var modalEl = document.getElementById('cedjag-modal');
    var modal = new bootstrap.Modal(modalEl);
    var modalInfo = document.getElementById('cedjag-modal-info');
    var modalError = document.getElementById('cedjag-modal-error');
    var btnEnviar = document.getElementById('cedjag-btn-enviar');
    var spinner = document.getElementById('cedjag-spinner');

    var actual = { tipo: null, id: null };

    function filtrar() {
        var termino = (buscar.value || '').trim().toLowerCase();
        var tipo = filtro.value;
        var visibles = 0;

        grid.querySelectorAll('.cedjag-item').forEach(function (item) {
            var coincideTexto = item.dataset.nombre.indexOf(termino) !== -1;
            var coincideTipo = !tipo || item.dataset.tipo === tipo;
            var visible = coincideTexto && coincideTipo;

            item.style.display = visible ? '' : 'none';
            if (visible) visibles++;
        });

        sinResultados.classList.toggle('d-none', visibles !== 0);
    }

    buscar.addEventListener('input', filtrar);
    filtro.addEventListener('change', filtrar);

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-solicitar');
        if (!btn) return;

        actual = { tipo: btn.dataset.tipo, id: btn.dataset.id };

        modalInfo.innerHTML = '<strong>' + btn.dataset.nombre + '</strong><br>' +
            btn.dataset.coleccion + ' — ' + btn.dataset.ubicacion;
        modalError.classList.add('d-none');

        document.getElementById('cedjag-nombre').value = '';
        document.getElementById('cedjag-email').value = '';
        document.getElementById('cedjag-telefono').value = '';
        document.getElementById('cedjag-mensaje').value = '';

        modal.show();
    });

    btnEnviar.addEventListener('click', function () {
        var nombre = document.getElementById('cedjag-nombre').value.trim();
        var email = document.getElementById('cedjag-email').value.trim();

        modalError.classList.add('d-none');

        if (nombre.length < 3) {
            modalError.textContent = 'Escribí tu nombre completo.';
            modalError.classList.remove('d-none');
            return;
        }

        if (!email.includes('@')) {
            modalError.textContent = 'Escribí un correo válido.';
            modalError.classList.remove('d-none');
            return;
        }

        spinner.classList.remove('d-none');

        $.request('cedjagComponent::onEnviarSolicitud', {
            data: {
                tipo: actual.tipo,
                documento_id: actual.id,
                nombre_solicitante: nombre,
                email_solicitante: email,
                telefono_solicitante: document.getElementById('cedjag-telefono').value.trim(),
                mensaje: document.getElementById('cedjag-mensaje').value.trim()
            },
            success: function (data) {
                spinner.classList.add('d-none');
                modal.hide();
                Swal.fire('¡Listo!', data.mensaje, 'success');
            },
            error: function (jqXHR) {
                spinner.classList.add('d-none');
                try {
                    var json = JSON.parse(jqXHR.responseText);
                    modalError.textContent = json.message || 'Ocurrió un error, intentá de nuevo.';
                } catch (e) {
                    modalError.textContent = 'Ocurrió un error, intentá de nuevo.';
                }
                modalError.classList.remove('d-none');
            }
        });
    });
});
