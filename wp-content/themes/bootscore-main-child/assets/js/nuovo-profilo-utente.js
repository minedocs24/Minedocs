

jQuery(document).ready(function($) {

    //const menuItems = $('.menu-item');

    var menu_data = env_nuovo_profilo_utente.menu_data;
    const base_url = env_nuovo_profilo_utente.base_url;

    menu_data = JSON.parse(menu_data);

    menu_data.forEach(menu_section => {

        carica_azione_voce_menu(menu_section);
        //carica_asincrono(menu_section);
    
    });


    function carica_azione_voce_menu(menu_section){
        var menuItems = menu_section.voci;
        Object.keys(menuItems).forEach(chiave_voce => {
            var menuItem = menuItems[chiave_voce];
            var slug = menuItem.slug
            var container = $('#menu-item-'+slug);
            var attivo = menuItem.attivo;
            var _default = menuItem.default;
            var template_part = menuItem.contenuto;

            if(attivo){

                var voce_menu = $('#menu-item-'+slug);
                voce_menu.on('click', function(e){
                    e.preventDefault();
                    $('#content-section > div').css('display', 'none');
                    $('#section-'+slug).css('display', 'block');
                });
                //$('#content-section > div:visible').css('display', 'none');
            }


        });
    }

    function carica_asincrono(menu_section){
        var sezione = menu_section.sezione;
        var menuItems = menu_section.voci;
        //console.logog(menuItems);

        Object.keys(menuItems).forEach(chiave_voce => {
            var menuItem = menuItems[chiave_voce];
            var slug = menuItem.slug
            var container = $('#section-'+slug);
            var attivo = menuItem.attivo;
            var _default = menuItem.default;
            var template_part = menuItem.contenuto;
            var tipo = menuItem.tipo;

            if(attivo && !_default){
                fetch(`${base_url}/wp-json/profilo-utente/load-section?template_part=${template_part}`,
                    {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }
                )
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Errore nel caricamento della sezione');
                        }
                        return response.text();
                    })
                    .then(html => {
                        container.html(html);
                    })
                    .catch(error => console.error('Errore:', error));

            }

        });
    }






});