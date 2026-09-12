$(document).ready(function () {
    iniciarApp();
});

function iniciarApp() {
    iniciarTabla();
    eventos();
}

function eventos() {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-eliminar');
        if (btn) avisoEliminar(btn.dataset.id);
    });

    var btnQuitarImagen = document.getElementById('btn-quitar-imagen');
    if (btnQuitarImagen) {
        btnQuitarImagen.addEventListener('click', function () {
            document.getElementById('imagen').value = '';
            document.getElementById('quitar_imagen').value = '1';
            document.getElementById('anuncio-preview-contenedor').classList.add('d-none');
        });
    }

    var inputImagen = document.getElementById('imagen');
    if (inputImagen) {
        inputImagen.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;

            document.getElementById('quitar_imagen').value = '';
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('anuncio-preview').src = e.target.result;
                document.getElementById('anuncio-preview-contenedor').classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        });
    }
}

function iniciarTabla() {
    $('#tabla-anuncios').DataTable({
        destroy: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pagingType: 'simple'
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
        title: '¿Eliminar este anuncio?',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (result) {
        if (result.isConfirmed) {
            $.request('anuncioComponent::onEliminar', {
                data: { id: id },
                success: function (data) { onEliminar(data); }
            });
        }
    });
}

function cargarFormulario(data) {
    modoModificar();

    var anuncio = data.anuncio;
    document.getElementById('id').value = anuncio.id;
    document.getElementById('titulo').value = anuncio.titulo;
    document.getElementById('texto').value = anuncio.texto;
    document.getElementById('video_url').value = anuncio.video_url || '';
    document.getElementById('orden').value = anuncio.orden || 0;
    document.getElementById('activo').checked = !!anuncio.activo;
    document.getElementById('quitar_imagen').value = '';
    document.getElementById('imagen').value = '';

    var preview = document.getElementById('anuncio-preview-contenedor');
    if (anuncio.imagen_url) {
        document.getElementById('anuncio-preview').src = anuncio.imagen_url;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
    }
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    document.querySelector('#formulario-titulo').textContent = 'Modificar anuncio';
    document.querySelector('#btnRegistrar').textContent = 'Guardar cambios';
}

function modoRegistrar() {
    document.querySelector('#formulario-titulo').textContent = 'Registrar anuncio';
    document.querySelector('#btnRegistrar').textContent = 'Registrar';
}

function limpiar() {
    document.getElementById('formulario').reset();
    document.getElementById('id').value = '';
    document.getElementById('quitar_imagen').value = '';
    document.getElementById('anuncio-preview-contenedor').classList.add('d-none');
    limpiarErrores();
}

function limpiarErrores() {
    document.querySelectorAll('.validacion-descripcion').forEach(function (e) { e.textContent = ''; });
}
