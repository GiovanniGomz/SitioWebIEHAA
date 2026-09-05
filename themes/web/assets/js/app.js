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
});

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
        document.getElementById('loader').classList.remove('ocultar');
        document.getElementById('loader').classList.add('mostrar');
    }

    function ocultarLoader() {
        document.getElementById('loader').classList.remove('mostrar');
        document.getElementById('loader').classList.add('ocultar');
    }

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

    //Elementos
    const elementoMostrarGaveta = document.querySelector('#mostrarGaveta');
    const elementoMostrarCarpeta = document.querySelector('#mostrarCarpeta');
    const elementoMostrarFolder = document.querySelector('#mostrarFolder');
    const elementoMostrarFabio = document.querySelector('#mostrarFabio');

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
}
