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


