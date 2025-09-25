document.addEventListener('DOMContentLoaded', function() {
    var navbarToggler = document.querySelector('.navbar-toggler');
    var navbarMenu = document.querySelector('#navbarMenu');

    navbarToggler.addEventListener('click', function() {
        var bsCollapse = new bootstrap.Collapse(navbarMenu, {
            toggle: false
        });
        bsCollapse.toggle();
    });
});