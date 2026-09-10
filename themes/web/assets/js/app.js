document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    new List('publicacionesList', {
        valueNames: ['titulo', 'descripcion'],
        page: 6,
        pagination: true
    });

    new List('investigacionesList', {
        valueNames: ['titulo', 'descripcion'],
        page: 6,
        pagination: true
    });

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Al elegir una opción del menú en móvil, cerrar el panel lateral.
    if (sidebar) {
        sidebar.querySelectorAll('.sidebar-nav .nav-link').forEach(function (a) {
            a.addEventListener('click', function () {
                if (window.matchMedia('(max-width: 991.98px)').matches) closeSidebar();
            });
        });
    }

    // Cerrar con la tecla Escape.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) closeSidebar();
    });

    initTablasScrollables();
});

// Marca las tablas que tienen contenido oculto a la derecha para mostrar la
// pista de degradado, y lo actualiza al hacer scroll / redimensionar.
function initTablasScrollables() {
    function contenedores() {
        return document.querySelectorAll('.main-content .dt-layout-full, .main-content .table-responsive');
    }

    function actualizar(box) {
        var padre = box.closest('.dt-layout-table') || box.closest('.table-responsive-wrap') || box;
        var hayMas = box.scrollWidth - box.clientWidth - box.scrollLeft > 4;
        padre.classList.toggle('hay-mas-derecha', hayMas);
    }

    function refrescar() {
        contenedores().forEach(function (box) {
            if (!box.dataset.scrollListo) {
                box.dataset.scrollListo = '1';
                box.addEventListener('scroll', function () { actualizar(box); }, { passive: true });
            }
            actualizar(box);
        });
    }

    refrescar();
    window.addEventListener('resize', refrescar, { passive: true });

    // Re-evaluar tras cargas AJAX (DataTables se re-dibuja al filtrar/eliminar).
    var obj = document.querySelector('.main-content');
    if (obj && window.MutationObserver) {
        var mo = new MutationObserver(function () {
            clearTimeout(window.__tablasTimer);
            window.__tablasTimer = setTimeout(refrescar, 150);
        });
        mo.observe(obj, { childList: true, subtree: true });
    }

    jQuery(document).on('ajaxComplete', function () { setTimeout(refrescar, 200); });
}

// Mostrar los errores de las peticiones AJAX del panel con SweetAlert2 en
// lugar del alert() nativo del framework, sin romper la página.
jQuery(window).on('ajaxErrorMessage', function (event, message) {
    event.preventDefault();

    var texto = message || 'Ocurrió un problema y la acción no se completó.';

    if (window.Swal) {
        Swal.fire({
            icon: 'error',
            title: 'No se pudo completar la acción',
            text: texto,
            confirmButtonText: 'Entendido'
        });
    } else {
        alert(texto);
    }

    if (window.ocultarLoader) window.ocultarLoader();
});

// Zonas de "arrastrar o seleccionar archivo": cualquier
// <label class="file-drop"> que contenga un <input type="file">.
function initFileDrop(scope) {
    const root = scope || document;
    root.querySelectorAll('.file-drop').forEach(function (zona) {
        if (zona.dataset.fileDropReady) return;
        zona.dataset.fileDropReady = '1';

        const input = zona.querySelector('input[type="file"]');
        const texto = zona.querySelector('.file-drop-text');
        const textoOriginal = texto ? texto.textContent : '';

        function pintar() {
            if (!texto) return;
            texto.textContent = (input.files && input.files.length)
                ? input.files[0].name
                : textoOriginal;
            zona.classList.toggle('has-file', !!(input.files && input.files.length));
        }

        ['dragenter', 'dragover'].forEach(ev => zona.addEventListener(ev, e => {
            e.preventDefault();
            zona.classList.add('is-dragover');
        }));

        ['dragleave', 'dragend', 'drop'].forEach(ev => zona.addEventListener(ev, e => {
            e.preventDefault();
            zona.classList.remove('is-dragover');
        }));

        zona.addEventListener('drop', function (e) {
            if (!e.dataTransfer || !e.dataTransfer.files.length) return;
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        input.addEventListener('change', pintar);
        pintar();
    });
}

document.addEventListener('DOMContentLoaded', () => initFileDrop());

function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
}

(function () {

    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;

    let requests = 0;

    XMLHttpRequest.prototype.open = function (method, url) {
        this._url = url;

        return originalOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function () {

        requests++;

        mostrarLoader();

        this.addEventListener('loadend', function () {

            requests--;

            if (requests <= 0) {
                requests = 0;
                ocultarLoader();
            }

        });

        return originalSend.apply(this, arguments);
    };

    function mostrarLoader() {
        const l = document.getElementById('loader');
        if (l) { l.classList.remove('ocultar'); l.classList.add('mostrar'); }
    }

    function ocultarLoader() {
        const l = document.getElementById('loader');
        if (l) { l.classList.remove('mostrar'); l.classList.add('ocultar'); }
    }

    // Exponer para otros scripts / handlers
    window.mostrarLoader = mostrarLoader;
    window.ocultarLoader = ocultarLoader;

    // Mostrar el loader al descargar reportes o documentos (navegaciones que
    // no disparan XHR). Se oculta solo tras unos segundos porque el navegador
    // no notifica cuando termina una descarga.
    document.addEventListener('click', function (e) {
        const enlace = e.target.closest('a[href]');
        if (!enlace) return;

        const href = enlace.getAttribute('href') || '';
        const esDescarga =
            enlace.hasAttribute('download') ||
            enlace.dataset.loader !== undefined ||
            /reporte(pdf|excel)/i.test(href) ||
            /\/reporte[-_]?(pdf|excel)/i.test(href) ||
            /\/(descargar|download)\//i.test(href);

        if (esDescarga) {
            mostrarLoader();
            setTimeout(ocultarLoader, 6000);
        }
    });

})();

function getCookie(nombre) {
    const cookies = document.cookie.split("; ");

    for (const cookie of cookies) {
        const [key, value] = cookie.split("=");

        if (key === nombre) {
            return decodeURIComponent(value);
        }
    }

    return null;
}

function guardarCookie(nombre, url) {
    document.cookie = `${nombre}=${url}; path=/`;
}

function subMenuDinamico() {
    //Cookies
    const mostrarGaveta = getCookie('mostrarGaveta');
    const mostrarCarpeta = getCookie('mostrarCarpeta');
    const mostrarFolder = getCookie('mostrarFolder');
    const mostrarFabio = getCookie('mostrarFabio');

    const mostrarAnaquel = getCookie('mostrarAnaquel');
    const mostrarColeccion = getCookie('mostrarColeccion');
    const mostrarFondo = getCookie('mostrarFondo');

    //Elementos
    const elementoMostrarGaveta = document.querySelector('#mostrarGaveta');
    const elementoMostrarCarpeta = document.querySelector('#mostrarCarpeta');
    const elementoMostrarFolder = document.querySelector('#mostrarFolder');
    const elementoMostrarFabio = document.querySelector('#mostrarFabio');

    const elementoMostrarAnaquel = document.querySelector('#mostrarAnaquel');
    const elementoMostrarColeccion = document.querySelector('#mostrarColeccion');
    const elementoMostrarFondo = document.querySelector('#mostrarFondo');

    const enlacePDF = document.querySelector('#reportePDF');
    const enlaceExcel = document.querySelector('#reporteExcel');

    if (mostrarGaveta && elementoMostrarGaveta) {
        elementoMostrarGaveta.href = "/gavetas?id=" + mostrarGaveta;

        enlacePDF.href = "/reportePDFGaveta?id=" + mostrarGaveta;
        enlaceExcel.href = "/reporteExcelGaveta?id=" + mostrarGaveta;
    }

    if (mostrarCarpeta && elementoMostrarCarpeta) {
        elementoMostrarCarpeta.href = "/carpetas?id=" + mostrarCarpeta;

        enlacePDF.href = "/reportePDFCarpeta?id=" + mostrarCarpeta;
        enlaceExcel.href = "/reporteExcelCarpeta?id=" + mostrarCarpeta;
    }

    if (mostrarFolder && elementoMostrarFolder) {
        elementoMostrarFolder.href = "/folders?id=" + mostrarFolder;

        enlacePDF.href = "/reportePDFFolder?id=" + mostrarFolder;
        enlaceExcel.href = "/reporteExcelFolder?id=" + mostrarFolder;
    }

    if (mostrarFabio && elementoMostrarFabio) {
        elementoMostrarFabio.href = "/documentoFabio?id=" + mostrarFabio;

        enlacePDF.href = "/reportePDFFabio?id=" + mostrarFabio;
        enlaceExcel.href = "/reporteExcelFabio?id=" + mostrarFabio;
    }

    /* Fondo Bibliográfico */
    if (mostrarAnaquel && elementoMostrarAnaquel) {
        elementoMostrarAnaquel.href = "/anaqueles?id=" + mostrarAnaquel;

        enlacePDF.href = "/reportePDFAnaquel?id=" + mostrarAnaquel;
        enlaceExcel.href = "/reporteExcelAnaquel?id=" + mostrarAnaquel;
    }

    if (mostrarColeccion && elementoMostrarColeccion) {
        elementoMostrarColeccion.href = "/colecciones?id=" + mostrarColeccion;

        enlacePDF.href = "/reportePDFColeccion?id=" + mostrarColeccion;
        enlaceExcel.href = "/reporteExcelColeccion?id=" + mostrarColeccion;
    }

    if (mostrarFondo && elementoMostrarFondo) {
        elementoMostrarFondo.href = "/documentoFondo?id=" + mostrarFondo;

        enlacePDF.href = "/reportePDFFondo?id=" + mostrarFondo;
        enlaceExcel.href = "/reporteExcelFondo?id=" + mostrarFondo;
    }
}
