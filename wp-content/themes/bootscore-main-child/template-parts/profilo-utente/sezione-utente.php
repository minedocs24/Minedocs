<?php

$user_id = get_current_user_id();
$user = get_user_by( "id", $user_id );
$user_information = getUserInformationJSON($user_id);
$user_name = get_user_meta( $user->ID, 'first_name', true ) . " " .get_user_meta( $user->ID, 'last_name', true );
$userInformationArray = json_decode($user_information, true);
$icon_url = get_stylesheet_directory_uri() . '/assets/img/search/istituto.svg';
$nome_istituto = get_user_istituto($user_id);
error_log("nome istituto: " . $nome_istituto);
?>

<div id="section-profilo" class="text-center">
    <!--<div>
        <?php //get_template_part( 'template-parts/home/search-bar' ); ?>
    </div>-->
    <div class="row align-items-center">
        <div class="col-md-12">
            <div class="d-flex justify-content-center align-items-center">
                <img src="<?php echo get_user_avatar_url($user->ID) ?>" alt="Immagine profilo" class="logo mb-3">
                <div class="text-left">
                    <h3 class="title"><?php echo $user_name;?></h3>
                    <p class="subtitle mb-1"><img src="<?php echo esc_url($icon_url);?>" alt="Icona Istituto" class="icon-instituto"><?php echo $nome_istituto;?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between mt-3">
        <div class="flex-grow-1 text-center">
            <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_review.svg' /* '/assets/img/search/stella.svg' */; ?>" alt="Icona Recensioni" class="icon-recensioni">
            <h2 class="metric"><?php echo isset($userInformationArray['reviews_on_user_documents_avg']) ? $userInformationArray['reviews_on_user_documents_avg'] : '0';?></h2>
            <p class="metric-label"><?php echo isset($userInformationArray['reviews_on_user_documents']) ? $userInformationArray['reviews_on_user_documents'] : '0';?> recensioni</p>
        </div>
        <div class="flex-grow-1 text-center">
            <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_documenti.svg'/* '/assets/img/home/books.webp' */; ?>" alt="Icona Upload" class="icon-upload">
            <h2 class="metric"><?php echo isset($userInformationArray['documents_count']) ? $userInformationArray['documents_count'] : '0';?></h2>
            <p class="metric-label">Upload</p>
        </div>
        <div class="flex-grow-1 text-center">
            <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/user/Icone_Minedocs_download.svg'/* '/assets/img/search/download.svg' */; ?>" alt="Icona Download" class="icon-download">
            <h2 class="metric"><?php echo isset($userInformationArray['documents_downloaded_count']) ? $userInformationArray['documents_downloaded_count'] : '0';?></h2>
            <p class="metric-label">Download</p>
        </div>
        <div <?php if(!MOSTRA_FOLLOWERS) {echo "hidden"; } ?> class="flex-grow-1 text-center">
            <img src="<?php echo get_stylesheet_directory_uri() . '/assets/img/icons/followers.svg'; ?>" alt="Icona Followers" class="icon-followers">
            <h2 class="metric">290</h2>
            <p class="metric-label">Followers (to remove)</p>
        </div>
    </div>
</div>

<?php

// function get_user_istituto($user_id) {
//     return "AO"
// }
?>
