<?php

?>


<div class="container form-group search-form my-5">

    <form role="search" id="search-form" method="get" class="search-form"
        action="<?php echo esc_url( RICERCA_PAGE ); ?>">

        <div class="d-flex justify-content-center">

            <div class="col-md-8 col-12">

                <div class="search-bar mb-3 position-relative">
                    
                    <img src="<?php echo get_stylesheet_directory_uri(  ); ?>/assets/img/search/libro-sorridente.svg"
                        class="fixed-booksearch-svg" alt="Macchia Verde">

                    <i class="fa-solid fa-search search-icon" style=""></i>

                    <input type="text" class="form-control pl-4" placeholder="Cerca documenti, corsi o libri"
                        aria-label="Ricerca" aria-describedby="basic-addon1" name="search">
                </div>

            </div>

        </div>
        <div class="offset-md-2 col-md-8 col-12 d-flex justify-content-around mt-3">
            <input type="hidden" name="post_type" id="post_type" value="">
            <input type="hidden" name="tipo_prodotto" id="tipo_prodotto" value="">
            <button disabled type="submit" class="btn-custom btn-custom-orange" onclick="setQueryVars('product', 'corso')" >Cerca in Corsi</button>
            <button type="submit" class="btn-custom btn-custom-blue" onclick="setQueryVars('product', 'documento')">Cerca in Documenti</button>
            <button disabled type="submit" class="btn-custom btn-custom-green" onclick="setQueryVars('product', 'libro')">Cerca in Libri</button>



        </div>

    </form>
</div>

<script>
function setQueryVars(type, tipo_prodotto) {
    document.getElementById('post_type').value = type;
    document.getElementById('tipo_prodotto').value = tipo_prodotto;

}
</script>



<style>



.btn-custom {
    border-radius: 15px;
    padding: 5px 5px;

    margin: 0 5px;
    width: 200px;
    border: none;
    color: white;
    cursor: pointer;
    box-shadow: 3px 3px 5px rgba(0, 0, 0, 0.2);
}

.btn-custom-orange {
    background-color: rgb(250, 133, 79);
}

.btn-custom-blue {
    background-color: rgb(26, 106, 255);
}

.btn-custom-green {
    background-color: rgb(56, 198, 139);
}

.fixed-booksearch-svg {
    position: absolute;
    right: -100px;
    top: -120px;
    transform: scale(0.5);
    z-index: -1;
    /* Assicurati che l'immagine sia dietro il contenuto della sezione */
}



</style>