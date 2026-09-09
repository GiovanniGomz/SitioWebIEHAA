window.addEventListener('load', function () {
    'use strict';

    var el = document.getElementById('explorador');
    if (!el) return;

    var modo = el.dataset.modo;
    var totalNiveles = parseInt(el.dataset.totalNiveles, 10);
    var handlerNavegar = el.dataset.handlerNavegar;
    var handlerCrear = el.dataset.handlerCrear;
    var handlerSubir = el.dataset.handlerSubir;
    var handlerEditarDoc = el.dataset.handlerEditarDoc;
    var handlerEliminarNodo = el.dataset.handlerEliminarNodo;
    var handlerEliminarDoc = el.dataset.handlerEliminarDoc;

    var tituloRaiz = document.querySelector('.explorador-titulo').textContent;

    var ETIQUETAS_NIVEL = {
        fabio: ['Archivero', 'Gaveta', 'Carpeta', 'Folder', 'Documento'],
        fondo: ['Estante', 'Anaquel', 'Colección', 'Documento']
    };

    var PLURALES = {
        archivero: 'archiveros', gaveta: 'gavetas', carpeta: 'carpetas', folder: 'folders',
        documento: 'documentos', estante: 'estantes', anaquel: 'anaqueles', 'colección': 'colecciones'
    };

    var estado = {
        nivel: 0,
        parentId: null,
        breadcrumb: [{ nivel: 0, id: null, nombre: tituloRaiz }],
        esHoja: false,
        etiquetaNivel: '',
        ultimosItems: [],
        editandoId: null
    };

    var grid = document.getElementById('explorador-grid');
    var cargando = document.getElementById('explorador-cargando');
    var vacio = document.getElementById('explorador-vacio');
    var dropzone = document.getElementById('explorador-dropzone');
    var buscarInput = document.getElementById('explorador-buscar');
    var btnNuevo = document.getElementById('explorador-btn-nuevo');
    var btnNuevoTexto = document.getElementById('explorador-btn-nuevo-texto');
    var btnSubir = document.getElementById('explorador-btn-subir');
    var subtitulo = document.getElementById('explorador-subtitulo');
    var stepperEl = document.getElementById('explorador-stepper');
    var modalEl = document.getElementById('explorador-modal');
    var modal = new bootstrap.Modal(modalEl);
    var modalTitulo = document.getElementById('explorador-modal-titulo');
    var formNodo = document.getElementById('explorador-form-nodo');
    var formArchivo = document.getElementById('explorador-form-archivo');
    var campoLabel = document.getElementById('explorador-campo-label');
    var campoValor = document.getElementById('explorador-campo-valor');
    var docNombre = document.getElementById('explorador-doc-nombre');
    var docArchivo = document.getElementById('explorador-doc-archivo');
    docArchivo.setAttribute('name', 'archivo');
    var docArchivoAyuda = document.getElementById('explorador-doc-archivo-ayuda');
    var formError = document.getElementById('explorador-form-error');
    var btnGuardar = document.getElementById('explorador-btn-guardar');
    var btnGuardarSpinner = document.getElementById('explorador-btn-guardar-spinner');

    function mostrarError(msg) {
        formError.textContent = msg;
        formError.classList.remove('d-none');
    }

    function limpiarError() {
        formError.classList.add('d-none');
        formError.textContent = '';
    }

    function etiquetaDe(nivel) {
        return (ETIQUETAS_NIVEL[modo] || [])[nivel] || '';
    }

    function navegar(nivel, parentId, breadcrumb, sinHistorial) {
        estado.nivel = nivel;
        estado.parentId = parentId;
        estado.breadcrumb = breadcrumb;

        if (!sinHistorial) {
            history.pushState({ nivel: nivel, parentId: parentId, breadcrumb: breadcrumb }, '', location.pathname);
        }

        cargarNivel();
    }

    function irABreadcrumb(idx) {
        var item = estado.breadcrumb[idx];
        navegar(item.nivel, item.id, estado.breadcrumb.slice(0, idx + 1));
    }

    // Botón atrás/adelante del navegador: restaura el nivel guardado en el historial
    window.addEventListener('popstate', function (e) {
        if (!e.state) return;
        estado.nivel = e.state.nivel;
        estado.parentId = e.state.parentId;
        estado.breadcrumb = e.state.breadcrumb;
        cargarNivel();
    });

    function pintarBreadcrumb() {
        var ol = document.getElementById('explorador-breadcrumb');
        ol.innerHTML = '';

        estado.breadcrumb.forEach(function (item, idx) {
            var li = document.createElement('li');
            var esUltimo = idx === estado.breadcrumb.length - 1;
            li.className = 'breadcrumb-item' + (esUltimo ? ' active' : '');

            var texto = idx === 0 ? item.nombre : (etiquetaDe(item.nivel) + ' ' + item.nombre);

            if (esUltimo) {
                li.textContent = texto;
            } else {
                var a = document.createElement('a');
                a.textContent = texto;
                a.addEventListener('click', function () { irABreadcrumb(idx); });
                li.appendChild(a);
            }

            ol.appendChild(li);
        });

        btnSubir.disabled = estado.breadcrumb.length <= 1;
    }

    function pintarStepper() {
        var etiquetas = ETIQUETAS_NIVEL[modo] || [];
        stepperEl.innerHTML = '';

        etiquetas.forEach(function (etiqueta, idx) {
            var li = document.createElement('li');
            var hecho = idx < estado.nivel;
            var activo = idx === estado.nivel;

            var plural = PLURALES[etiqueta.toLowerCase()] || etiqueta;
            var textoPlural = plural.charAt(0).toUpperCase() + plural.slice(1);

            li.className = 'explorador-stepper-item' + (hecho ? ' explorador-stepper-hecho' : '') + (activo ? ' explorador-stepper-activo' : '');
            li.innerHTML = '<span class="explorador-stepper-bolita">' +
                (hecho ? '<i class="bi bi-check-lg"></i>' : (idx + 1)) +
                '</span><span>' + textoPlural + '</span>';

            stepperEl.appendChild(li);

            if (idx < etiquetas.length - 1) {
                var flecha = document.createElement('li');
                flecha.className = 'explorador-stepper-flecha';
                flecha.innerHTML = '<i class="bi bi-chevron-right"></i>';
                stepperEl.appendChild(flecha);
            }
        });
    }

    function cargarNivel() {
        cargando.style.display = 'flex';
        grid.innerHTML = '';
        grid.appendChild(cargando);
        vacio.classList.add('d-none');
        pintarBreadcrumb();
        pintarStepper();

        $.request(handlerNavegar, {
            data: { modo: modo, nivel: estado.nivel, parent_id: estado.parentId },
            success: function (data) {
                estado.esHoja = data.esHoja;
                estado.etiquetaNivel = data.etiqueta || '';
                estado.ultimosItems = data.items || [];

                dropzone.hidden = !estado.esHoja;
                btnNuevoTexto.textContent = estado.esHoja ? 'Subir documento' : ('Nuevo ' + (data.etiqueta || ''));

                var dondeEstasKey = estado.esHoja ? 'documento' : data.etiqueta.toLowerCase();
                var dondeEstas = PLURALES[dondeEstasKey] || dondeEstasKey;

                if (estado.breadcrumb.length > 1) {
                    var padre = estado.breadcrumb[estado.breadcrumb.length - 1];
                    subtitulo.textContent = 'Viendo ' + dondeEstas + ' dentro de ' + etiquetaDe(padre.nivel) + ' ' + padre.nombre;
                } else {
                    subtitulo.textContent = 'Viendo ' + dondeEstas;
                }

                renderizar(estado.ultimosItems);
            },
            error: function () {
                cargando.style.display = 'none';
                vacio.classList.remove('d-none');
            }
        });
    }

    function renderizar(items) {
        grid.innerHTML = '';

        if (!items.length) {
            vacio.classList.remove('d-none');
            return;
        }

        vacio.classList.add('d-none');

        items.forEach(function (item) {
            grid.appendChild(estado.esHoja ? tarjetaDocumento(item) : tarjetaNodo(item));
        });
    }

    function tarjetaNodo(item) {
        var card = document.createElement('div');
        card.className = 'explorador-card';
        card.dataset.nombre = (item.nombre || '').toLowerCase();

        var etiquetaHijo = etiquetaDe(estado.nivel + 1).toLowerCase();
        var textoHijos = item.hijos + ' ' + (item.hijos === 1 ? etiquetaHijo : (PLURALES[etiquetaHijo] || etiquetaHijo));

        card.innerHTML =
            '<div class="explorador-card-acciones">' +
            '  <button type="button" class="explorador-btn-editar" title="Editar"><i class="bi bi-pencil-fill"></i></button>' +
            '  <button type="button" class="explorador-btn-eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>' +
            '</div>' +
            '<i class="bi ' + iconoNivelActual() + ' explorador-card-icono"></i>' +
            '<div class="explorador-card-nombre"></div>' +
            '<div class="explorador-card-meta">' + textoHijos + ' adentro</div>';

        card.querySelector('.explorador-card-nombre').textContent = item.nombre;

        card.addEventListener('click', function (e) {
            if (e.target.closest('.explorador-btn-eliminar') || e.target.closest('.explorador-btn-editar')) return;

            var nuevoBreadcrumb = estado.breadcrumb.concat([{ nivel: estado.nivel, id: item.id, nombre: item.nombre }]);
            navegar(estado.nivel + 1, item.id, nuevoBreadcrumb);
        });

        card.querySelector('.explorador-btn-eliminar').addEventListener('click', function () {
            confirmarEliminarNodo(item);
        });

        card.querySelector('.explorador-btn-editar').addEventListener('click', function () {
            abrirEdicionNodo(item);
        });

        return card;
    }

    function tarjetaDocumento(item) {
        var card = document.createElement('div');
        card.className = 'explorador-card explorador-card-doc';
        card.dataset.nombre = (item.nombre || '').toLowerCase();

        card.innerHTML =
            '<div class="explorador-card-acciones">' +
            '  <button type="button" class="explorador-btn-descargar" title="Descargar"><i class="bi bi-download"></i></button>' +
            '  <button type="button" class="explorador-btn-editar" title="Editar"><i class="bi bi-pencil-fill"></i></button>' +
            '  <button type="button" class="explorador-btn-eliminar" title="Eliminar"><i class="bi bi-trash"></i></button>' +
            '</div>' +
            '<i class="bi ' + item.icono + ' explorador-card-icono"></i>' +
            '<div class="explorador-card-nombre"></div>';

        card.querySelector('.explorador-card-nombre').textContent = item.nombre;

        card.querySelector('.explorador-btn-descargar').addEventListener('click', function () {
            var a = document.createElement('a');
            a.href = item.url;
            a.download = item.nombre;
            document.body.appendChild(a);
            a.click();
            a.remove();
        });

        card.querySelector('.explorador-btn-editar').addEventListener('click', function () {
            abrirEdicionDocumento(item);
        });

        card.querySelector('.explorador-btn-eliminar').addEventListener('click', function () {
            confirmarEliminarDocumento(item);
        });

        return card;
    }

    function iconoNivelActual() {
        var iconos = {
            fabio: ['bi-archive-fill', 'bi-inboxes-fill', 'bi-folder2', 'bi-folder-fill'],
            fondo: ['bi-bookshelf', 'bi-archive-fill', 'bi-folder2']
        };

        return (iconos[modo] || [])[estado.nivel] || 'bi-folder';
    }

    function confirmarEliminarNodo(item) {
        Swal.fire({
            title: '¿Eliminar "' + item.nombre + '"?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.request(handlerEliminarNodo, {
                data: { modo: modo, nivel: estado.nivel, id: item.id },
                success: function (data) {
                    if (data.estado === 'error') {
                        Swal.fire('No se pudo eliminar', data.mensaje, 'error');
                        return;
                    }
                    Swal.fire('Listo', data.mensaje, 'success');
                    cargarNivel();
                }
            });
        });
    }

    function confirmarEliminarDocumento(item) {
        Swal.fire({
            title: '¿Eliminar "' + item.nombre + '"?',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.request(handlerEliminarDoc, {
                data: { modo: modo, id: item.id },
                success: function (data) {
                    Swal.fire('Listo', data.mensaje, 'success');
                    cargarNivel();
                }
            });
        });
    }

    // Botón "Subir un nivel"
    btnSubir.addEventListener('click', function () {
        if (estado.breadcrumb.length > 1) {
            irABreadcrumb(estado.breadcrumb.length - 2);
        }
    });

    // Buscador (filtra el nivel actual en el DOM, sin recargar)
    buscarInput.addEventListener('input', function () {
        var termino = buscarInput.value.trim().toLowerCase();

        grid.querySelectorAll('.explorador-card').forEach(function (card) {
            card.style.display = card.dataset.nombre.indexOf(termino) === -1 ? 'none' : '';
        });
    });

    function esCampoCodigo(etiqueta) {
        return etiqueta === 'Archivero' || etiqueta === 'Gaveta' || etiqueta === 'Estante' || etiqueta === 'Anaquel';
    }

    // Botón "Nuevo"
    btnNuevo.addEventListener('click', function () {
        limpiarError();
        estado.editandoId = null;
        campoValor.value = '';
        docNombre.value = '';
        docArchivo.value = '';
        docArchivo.required = true;
        docArchivoAyuda.textContent = '';

        if (estado.esHoja) {
            modalTitulo.textContent = 'Subir documento';
            formNodo.classList.add('d-none');
            formArchivo.classList.remove('d-none');
        } else {
            modalTitulo.textContent = 'Nuevo ' + estado.etiquetaNivel;
            campoLabel.textContent = esCampoCodigo(estado.etiquetaNivel) ? 'Código' : 'Nombre';
            formArchivo.classList.add('d-none');
            formNodo.classList.remove('d-none');
        }

        modal.show();
    });

    // Editar un nodo intermedio (archivero, gaveta, carpeta, folder, estante, anaquel, colección)
    function abrirEdicionNodo(item) {
        limpiarError();
        estado.editandoId = item.id;

        modalTitulo.textContent = 'Editar ' + estado.etiquetaNivel;
        campoLabel.textContent = esCampoCodigo(estado.etiquetaNivel) ? 'Código' : 'Nombre';
        campoValor.value = item.nombre;

        formArchivo.classList.add('d-none');
        formNodo.classList.remove('d-none');

        modal.show();
    }

    // Editar un documento (renombrar y, opcionalmente, reemplazar el archivo)
    function abrirEdicionDocumento(item) {
        limpiarError();
        estado.editandoId = item.id;

        modalTitulo.textContent = 'Editar documento';
        docNombre.value = item.nombre;
        docArchivo.value = '';
        docArchivo.required = false;
        docArchivoAyuda.textContent = 'Dejalo en blanco para conservar el archivo actual (' + item.nombre + ').';

        formNodo.classList.add('d-none');
        formArchivo.classList.remove('d-none');

        modal.show();
    }

    // Guardar (crea/edita un nodo, o sube/edita un documento, según el nivel)
    btnGuardar.addEventListener('click', function () {
        limpiarError();

        if (estado.esHoja) {
            if (!docNombre.value.trim()) {
                mostrarError('Escribí un nombre para el documento.');
                return;
            }
            if (!estado.editandoId && !docArchivo.files.length) {
                mostrarError('Seleccioná un archivo.');
                return;
            }

            var handlerDoc = estado.editandoId ? handlerEditarDoc : handlerSubir;
            var datosDoc = { modo: modo, parent_id: estado.parentId, nombre: docNombre.value.trim() };
            if (estado.editandoId) datosDoc.id = estado.editandoId;

            btnGuardarSpinner.classList.remove('d-none');

            $(docArchivo).request(handlerDoc, {
                files: true,
                data: datosDoc,
                success: function () {
                    btnGuardarSpinner.classList.add('d-none');
                    modal.hide();
                    cargarNivel();
                },
                error: function (jqXHR) {
                    btnGuardarSpinner.classList.add('d-none');
                    mostrarError(extraerError(jqXHR));
                }
            });
        } else {
            var campo = campoLabel.textContent === 'Código' ? 'codigo' : 'nombre';

            if (!campoValor.value.trim()) {
                mostrarError('Este campo es obligatorio.');
                return;
            }

            var datos = { modo: modo, nivel: estado.nivel, parent_id: estado.parentId };
            datos[campo] = campoValor.value.trim();
            if (estado.editandoId) datos.id = estado.editandoId;

            btnGuardarSpinner.classList.remove('d-none');

            $.request(handlerCrear, {
                data: datos,
                success: function () {
                    btnGuardarSpinner.classList.add('d-none');
                    modal.hide();
                    cargarNivel();
                },
                error: function (jqXHR) {
                    btnGuardarSpinner.classList.add('d-none');
                    mostrarError(extraerError(jqXHR));
                }
            });
        }
    });

    function extraerError(jqXHR) {
        try {
            var json = JSON.parse(jqXHR.responseText);
            if (json.message) return json.message;
        } catch (e) { /* noop */ }

        return 'Ocurrió un error, intentá de nuevo.';
    }

    // Drag & drop de archivos (solo en el nivel hoja)
    ['dragenter', 'dragover'].forEach(function (evt) {
        el.addEventListener(evt, function (e) {
            if (!estado.esHoja) return;
            e.preventDefault();
            dropzone.classList.add('explorador-dropzone-activa');
        });
    });

    ['dragleave', 'drop'].forEach(function (evt) {
        el.addEventListener(evt, function (e) {
            if (!estado.esHoja) return;
            e.preventDefault();
            dropzone.classList.remove('explorador-dropzone-activa');
        });
    });

    el.addEventListener('drop', function (e) {
        if (!estado.esHoja) return;
        e.preventDefault();

        var archivos = e.dataTransfer.files;
        if (!archivos.length) return;

        var dt = new DataTransfer();
        dt.items.add(archivos[0]);
        docArchivo.files = dt.files;
        docNombre.value = archivos[0].name.replace(/\.[^/.]+$/, '');

        limpiarError();
        estado.editandoId = null;
        docArchivo.required = true;
        docArchivoAyuda.textContent = '';
        modalTitulo.textContent = 'Subir documento';
        formNodo.classList.add('d-none');
        formArchivo.classList.remove('d-none');
        modal.show();
    });

    history.replaceState({ nivel: 0, parentId: null, breadcrumb: estado.breadcrumb }, '', location.pathname);
    cargarNivel();
});
