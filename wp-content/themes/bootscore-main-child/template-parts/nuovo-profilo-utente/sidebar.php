<?php

$menu_data = $args['menu_data'];

?>


<nav id="fixed-sidebar" class="d-md-block bg-light sidebar-profilo">
    <div class="position-sticky">
        <?php
        foreach ($menu_data as $sezione) {

            $nome_sezione = isset($sezione['sezione']) ? $sezione['sezione'] : '';
            $mostra_nome_sezione = isset($sezione['mostra_nome_sezione']) ? $sezione['mostra_nome_sezione'] : false;
            
            ?>
            <?php if($mostra_nome_sezione) { ?>
                <h6 class="sidebar-heading"><?php echo $nome_sezione; ?></h6>
            <?php } ?>
                <ul class="nav flex-column mb-3">
            <?php
            foreach ($sezione['voci'] as $menu_item) {

                $active = $menu_item['attivo'] ? $menu_item['attivo'] : true;
                if(!$active) {
                    continue;
                }

                $icon = isset($menu_item['icona']) ? $menu_item['icona'] : 'fas fa-folder'; 
                $url = isset($menu_item['url']) ? $menu_item['url'] : '#';
                $nome = isset($menu_item['nome']) ? $menu_item['nome'] : '';
                $tipo = isset($menu_item['tipo']) ? $menu_item['tipo'] : null;
                $contenuto = isset($menu_item['contenuto']) ? $menu_item['contenuto'] : null;

                ?>  

                <?php if($tipo === 'link_esterno') { ?>
                    <li class="nav-item">
                        <a href="<?php echo $url; ?>" class="nav-link"><i class="<?php echo $icon; ?>"></i> <?php echo $nome; ?></a>
                    </li>
                <?php } else if($tipo === 'contenuto') { ?>
                    <li class="nav-item">
                        <a 
                        id = "menu-item-<?php echo $menu_item['slug']; ?>"
                        href="<?php echo $url; ?>"  
                        class="nav-link" 
                        data-template-part="<?php echo $menu_item['contenuto']; ?>">
                            <i class="<?php echo $icon; ?>"></i> 
                            <?php echo $nome; ?>
                        </a>
                    </li>
                <?php } elseif ($tipo === 'menu_template_part') { 

                    if($contenuto) {
                        get_template_part($contenuto);
                    }

                } ?>


                <?php
            }
            ?>
            </ul>
            <?php 


        }
        ?>
        <?php get_template_part('template-parts/profilo-utente/logout-button'); ?>
    </div>
</nav>

<style>

.sidebar-profilo {
    height: 100vh !important;
    background-color: #f1f3f5;
    padding-top: 20px;
    border-right: 1px solid #dee2e6;
    overflow-y: auto; /* Aggiungi questa riga */
    overflow-x: hidden; /* Aggiungi questa riga per evitare lo scorrimento orizzontale */
}

.sidebar-profilo .btn-primary {
    font-size: 0.9rem;
    padding: 10px;
    border-radius: 20px;
}

.sidebar-profilo small {
    color: #6c757d;
}

.sidebar-heading {
    font-size: 0.85rem;
    font-weight: bold;
    color: #6c757d;
    margin-top: 20px;
    margin-bottom: 10px;
    padding-left: 15px;
}

.sidebar-profilo .nav-link {
    font-size: 0.9rem;
    color: #333;
    padding: 8px 15px;
    transition: background 0.2s, color 0.2s;
    border-radius: 5px;
}

.sidebar-profilo .nav-link i {
    margin-right: 8px;
}

.sidebar-profilo .nav-link:hover,
.sidebar-profilo .nav-link.active {
    background-color: #e9f5ff;
    color: #007bff;
}

</style>
