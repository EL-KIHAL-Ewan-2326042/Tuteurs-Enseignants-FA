/**
 * Initialisation du Menu Hamburger(sidenav) Materialize
 */
document.addEventListener(
    'DOMContentLoaded', function () {
        const elems = document.querySelectorAll('.sidenav');
        M.Sidenav.init(elems);
    }
);
