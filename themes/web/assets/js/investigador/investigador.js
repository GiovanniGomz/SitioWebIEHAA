$(document).ready(function () {
    iniciarApp();
});

let publicaciones = [];

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

    console.log(investigador);

    modoModificar();

    document.querySelector('#nombre').value = investigador.nombre;
    document.querySelector('#apellido').value = investigador.apellido;
    document.querySelector('#carnet').value = investigador.carnet;
    document.querySelector('#telefono').value = investigador.telefono;
    document.querySelector('#facultad').value = investigador.facultad_id;
    document.querySelector('#categoria_investigador').value = investigador.categoria_investigador_id;
    document.querySelector('#email').value = investigador.email;
    document.querySelector('#tipo_investigador').value = investigador.tipo_investigador_id;
    document.querySelector('#sexo').value = investigador.sexo;
    document.querySelector('#descripcion').value = investigador.descripcion;
    document.querySelector('#id').value = investigador.id;

    llenarPublicaciones(investigador.publicaciones);


    //Este método se activa cuando queremos obtener los datos del registro a modificar
    function llenarPublicaciones(listaPublicaciones) {

        console.log(`Valor de lista Publicacion: ${listaPublicaciones}`);

        if (listaPublicaciones !== '') {
            publicaciones = JSON.parse(listaPublicaciones);
        }
        console.log(publicaciones);

        actualizarPublicacionesJSON();
        mostrarPublicaciones();
    }
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
    document.querySelector('#nombre').value = '';
    document.querySelector('#apellido').value = '';
    document.querySelector('#carnet').value = '';
    document.querySelector('#telefono').value = '';
    document.querySelector('#facultad').value = '';
    document.querySelector('#categoria_investigador').value = '';
    document.querySelector('#email').value = '';
    document.querySelector('#tipo_investigador').value = '';
    document.querySelector('#sexo').value = '';
    document.querySelector('#publicaciones').value = '';
    document.querySelector('#descripcion').value = '';
    document.querySelector('#id').value = '';

    limpiarPublicaciones();

    limpiarErrores();
}

function limpiarErrores() {
    const errores = document.querySelectorAll('.validacion-descripcion');

    errores.forEach(error => {
        error.textContent = '';
    });
}

function agregarPublicacion() {

    const nombre = document.getElementById('publicacion_nombre').value.trim();
    const url = document.getElementById('publicacion_url').value.trim();

    // Validar que ambos campos tengan información
    if (!nombre || !url) {
        Swal.fire(
            'Error',
            'Debe ingresar el nombre y la URL de la publicación',
            'error'
        );
        return;
    }

    // Crear publicación
    const publicacion = {
        id: Date.now(),
        nombre: nombre,
        url: url
    };

    // Agregar al arreglo
    publicaciones.push(publicacion);

    // Actualizar la interfaz
    mostrarPublicaciones();

    // Actualizar hidden
    actualizarPublicacionesJSON();

    // Limpiar campos
    document.getElementById('publicacion_nombre').value = '';
    document.getElementById('publicacion_url').value = '';

    document.getElementById('publicacion_nombre').focus();
}


function eliminarPublicacion(id) {

    publicaciones = publicaciones.filter(
        publicacion => publicacion.id !== id
    );

    mostrarPublicaciones();

    actualizarPublicacionesJSON();
}

function limpiarPublicaciones() {
    const lista = document.getElementById('lista-publicaciones');

    lista.innerHTML = '';

    publicaciones = [];

}


function mostrarPublicaciones() {

    const lista = document.getElementById('lista-publicaciones');

    lista.innerHTML = '';

    if (publicaciones.length === 0) {
        lista.innerHTML = `
                <div class="alert alert-light border">
                    No hay publicaciones agregadas.
                </div>
            `;

        return;
    }

    publicaciones.forEach(publicacion => {

        lista.innerHTML += `
                <div class="d-flex justify-content-between align-items-center
                            border rounded p-2 mb-2">

                    <div>
                        <strong style="color: gray;">${escapeHTML(publicacion.nombre)}</strong>
                        <br>
                        <a style="color:gray;" href="${escapeHTML(publicacion.url)}"
                           target="_blank">
                            ${escapeHTML(publicacion.url)}
                        </a>
                    </div>

                    <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="eliminarPublicacion(${publicacion.id})">
                        Eliminar
                    </button>

                </div>
            `;
    });
}


function actualizarPublicacionesJSON() {

    document.querySelector('#publicaciones').value =
        JSON.stringify(publicaciones);
}


function escapeHTML(text) {

    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;
}