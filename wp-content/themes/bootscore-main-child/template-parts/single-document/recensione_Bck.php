<?php

$recensione = $args['recensione'];


$user_id = $recensione->user_id;


?>

<a href="" style="text-decoration: none;">
<div >

        <h5 class="card-title text-dark" ><?php echo "5" ?>⭐ <?php echo "Da " .get_user_by( 'id', $user_id )->user_nicename ; ?></h5>
        <p class="card-text text-muted mt-2"><?php echo $recensione->comment_content; ?></p>

</div>
</a>

singola recensione da caricare