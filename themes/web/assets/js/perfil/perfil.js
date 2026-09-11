document.addEventListener('DOMContentLoaded', function () {
    var inputFoto = document.getElementById('foto');
    if (inputFoto) {
        inputFoto.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            subirFoto();
        });
    }

    var btnQuitar = document.getElementById('btn-quitar-foto');
    if (btnQuitar) {
        btnQuitar.addEventListener('click', function () {
            Swal.fire({
                title: '¿Quitar tu foto de perfil?',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                $.request('perfilComponent::onQuitarFoto', {
                    success: function () { window.location.reload(); }
                });
            });
        });
    }
});

function subirFoto() {
    $('#formulario-foto').request('perfilComponent::onGuardarFoto', {
        files: true,
        success: function (data) {
            Swal.fire(data.mensaje, data.mensaje, 'success').then(function () {
                window.location.reload();
            });
        },
        error: function (jqXHR) {
            var texto = 'No se pudo actualizar la foto.';
            try {
                var json = JSON.parse(jqXHR.responseText);
                texto = json.X_WINTER_ERROR_MESSAGE || json.message || texto;
            } catch (err) { /* usar mensaje genérico */ }
            Swal.fire('Aviso', texto, 'error');
        }
    });
}

function onPasswordExito(data) {
    var exito = document.getElementById('password-exito');
    exito.textContent = data.mensaje;
    exito.classList.remove('d-none');
    document.getElementById('formulario-password').reset();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
