<header class="piani-pro-header position-relative">

    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/premium-plans/moon.svg"
        class="piani-pro-moon-header" style="filter: drop-shadow(1px 1px 3px #0000004d);">
    <h1>Passa al PIANO <span class="pro">PRO</span></h1>
    <h3>Non dare limiti alla tua conoscenza, impara con i Punti <span class="pro">PRO</span></h3>
</header>



<style>
    .piani-pro-header {

        padding: 50px;

        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 300px;
    }





    .piani-pro-header .piani-pro-moon-header {
        position: absolute;
        right: 150px;
        top: 50px;
        transform: scale(1.6);
        transition: right 0.5s ease, transform 0.5s ease;
        z-index: -1;

    }



    /* Media queries per spostare l'immagine a destra su schermi più piccoli */
    @media (max-width: 1200px) {
        .piani-pro-header .piani-pro-moon-header {
            right: 20px;
            transform: scale(1.3);
        }
    }

    @media (max-width: 992px) {
        .piani-pro-header .piani-pro-moon-header {
            right: -25px;
            transform: scale(1.1);
        }
    }

    @media (max-width: 768px) {
        .piani-pro-header .piani-pro-moon-header {
            right: -25px;
            transform: scale(0.9);
        }
    }

    @media (max-width: 576px) {
        .piani-pro-header .piani-pro-moon-header {
            right: -100px;
            transform: scale(0.7);
        }
    }
</style>

<!-- Start Generation Here -->
<style>
    @media (max-width: 576px) {

        .piani-pro-header h1,
        .piani-pro-header h3 {
            text-align: center;
            /* Centra il testo per schermi mobili */
        }
    }
</style>
<!-- End Generation Here -->