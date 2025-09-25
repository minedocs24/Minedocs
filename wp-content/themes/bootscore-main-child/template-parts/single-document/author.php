<?php

$user_id = $args['user_id'];
$user = get_user_by( "id", $user_id );
$user_information = getUserInformationJSON($user_id, false);
$icon_url = get_stylesheet_directory_uri() . '/assets/img/search/istituto.svg';
$nome_istituto = get_user_istituto($user_id);

$features = array (
  array('icon' => 'istituto.svg', 'text' => 'UniPegaso'),
)


?>


<div class="my-4">
    <h4 class="h5">Pubblicato da:</h4>

    <div class="card card-profile shadow-lg rounded-lg p-4">
        <div class="d-flex align-items-center mb-4 mt-2">
            <img src="<?php echo get_user_avatar_url($user->ID) ?>" alt="Immagine profilo" class="profile-image me-3 rounded-circle border border-primary">
            <div>
                <h5 class="mb-0 text-lg font-semibold">
                    <?php echo $user->nickname . (get_current_user_id() == $user->ID ? " (Tu)" : ""); ?> 
                </h5>
                <span class="text-muted d-flex align-items-center">
                    <i class="bi bi-building me-2"></i>
                    <p class="subtitle mb-0 text-sm">
                        <img src="<?php echo esc_url($icon_url);?>" alt="Icona Istituto" class="icon-size me-1" style="width: 20px; height: 20px; margin-right: 5px;"><?php echo $nome_istituto;?>
                    </p>
                </span>
            </div>
        </div>
        <div class="d-flex justify-content-between text-center flex-wrap">
            <div class="flex-grow-1 mb-3">
                <div class="position-relative">
                    <img class="icona-feature-card" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_review.svg' /* '/assets/img/search/stella.svg' */; ?>">
                    <p class="position-absolute mb-0 rating">
                        <?php
                        $userInformationArray = json_decode($user_information, true);
                        echo isset($userInformationArray['reviews_on_user_documents_avg']) ? $userInformationArray['reviews_on_user_documents_avg'] : '0';
                        ?>
                    </p>
                </div>
                <p class="mb-0 fs-5">
                    <?php
                    echo isset($userInformationArray['reviews_on_user_documents']) ? $userInformationArray['reviews_on_user_documents'] + ($user_id == 54 ? 664 : 0) : '0';
                    ?>
                </p>
                <p class="text-muted fs-5">Recensioni</p>
            </div>
            <div class="flex-grow-1 mb-3">
                <img class="icona-feature-card" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_documenti.svg'/* '/assets/img/home/books.webp' */; ?>">
                <p class="mb-0 fs-5">
                    <?php
                    echo isset($userInformationArray['documents_count']) ? $userInformationArray['documents_count'] : '0';
                    ?>
                </p>
                <p class="text-muted fs-5">Documenti</p>
            </div>
            <div class="flex-grow-1 mb-3" <?php if(!defined('MOSTRA_FOLLOWERS') || !MOSTRA_FOLLOWERS) {echo "hidden"; } ?>>
                <img class="icona-feature-card" src="<?php echo get_stylesheet_directory_uri() ?>/assets/img/documento/followers.png">
                <p class="mb-0 fs-5">290</p>
                <p class="text-muted fs-5">Followers</p>
            </div>
            <div class="flex-grow-1 mb-3">
                <img class="icona-feature-card" src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_download.svg'/* '/assets/img/search/download.svg' */; ?>">
                <p class="mb-0 fs-5">
                    <?php
                    echo isset($userInformationArray['documents_downloaded_count']) ? $userInformationArray['documents_downloaded_count'] + ($user_id == 54 ? 1712 : 0) : '0';
                    ?>
                </p>
                <p class="text-muted fs-5">Download</p>
            </div>
        </div>
    </div>


</div>

<style>
.card-profile {
    border: 0px;
    border-radius: 15px;
    padding: 20px;
    background-color: var(--secondary-gray);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.profile-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    aspect-ratio: 1 / 1;
}

.icon-size {
    font-size: 48px;
}

.stat-icon {
    font-size: 2rem;
}

/* Rimuovi il punto elenco */
.custom-list {
    list-style-type: none;
    padding: 0;
}

.custom-list li {
    display: flex;
    align-items: center;
}

.custom-list li .icon {
    margin-right: 5px;
    /* Spazio tra l'icona e il testo */
    display: inline-block;
    width: 18px;
    margin-top: -3px;
}

.custom-list li a {
    font-size: 16px;
    text-decoration: none;
    color: #404040;
}

.istituti {
    margin-top: 10px;
}

.icona-feature-card {
    width: 40px;
    height: 40px;
}

.rating {
    font-size: 16px !important;
    left: 49% !important;
    top: 53% !important;
    transform: translate(-50%, -50%) !important
}
</style>