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

        if (btn) {
            avisoEliminar(btn.dataset.id);
        }
    });
}

function iniciarTabla() {
    $('#tabla-investigador').DataTable({
        destroy: true,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        pagingType: "simple"
    });
}

function onRegistrar(data) {

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        document.querySelector('#btnModalUp').click();
        resetear();

        iniciarTabla();
    }
}

function onEliminar(data) {

    if (data.estado === 'exito') {
        Swal.fire(
            data.mensaje,
            data.mensaje,
            'success'
        );

        if (data['#listado']) {
            document.querySelector('#listado').innerHTML = data['#listado'];
        }

        setTimeout(() => {
            iniciarTabla();
        }, 0);
    }
}

function avisoEliminar(id) {
    Swal.fire({
        title: "¿Eliminar Investigador?",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            $.request('investigadorComponent::onEliminar', {
                data: { id: id },
                success: function (data) {
                    onEliminar(data);
                }
            });
        }
    });
}

function cargarFormulario(data) {
    const { investigador } = data;

    modoModificar();

    document.querySelector('#nombre').value = investigador.nombre;
    document.querySelector('#apellido').value = investigador.apellido;
    document.querySelector('#carnet').value = investigador.carnet;
    document.querySelector('#telefono').value = investigador.telefono;
    document.querySelector('#facultad').value = investigador.facultad;
    document.querySelector('#grado').value = investigador.grado;
    document.querySelector('#email').value = investigador.email;
    document.querySelector('#id').value = investigador.id;
}

function resetear() {
    modoRegistrar();
    limpiar();
}

function modoModificar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Modificar Investigador';
    btnRegistrar.textContent = 'Guardar Cambios';
}

function modoRegistrar() {
    const titulo = document.querySelector('#formulario-titulo');
    const btnRegistrar = document.querySelector('#btnRegistrar');

    titulo.textContent = 'Registrar Investigador';
    btnRegistrar.textContent = 'Registrar';
}

function limpiar() {
    const contenedorPDF = document.querySelector('#contenedor-pdf');

    if (contenedorPDF) {
        contenedorPDF.classList.add('ocultar');
    }

    document.querySelector('#nombre').value = '';
    document.querySelector('#apellido').value = '';
    document.querySelector('#carnet').value = '';
    document.querySelector('#telefono').value = '';
    document.querySelector('#facultad').value = '';
    document.querySelector('#grado').value = '';
    document.querySelector('#email').value = '';
    document.querySelector('#id').value = '';

    limpiarErrores();
}

function limpiarErrores() {
    const errores = document.querySelectorAll('.validacion-descripcion');

    errores.forEach(error => {
        error.textContent = '';
    });
}