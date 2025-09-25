
<?php

$elemento = $args['elemento'];


$style = '';
foreach ($elemento['style'] as $key => $value) {
    if ($value !== null) {
        $style .= $key . ': ' . $value . '; ';
    }
}
$style = rtrim($style, '; ');

?>

<div class="box-informazioni d-flex flex-column align-content-scretch <?php echo $elemento['classes']?>"
    style="filter: drop-shadow(3px 3px 8px #0000004d); <?php echo $style ?>">

    <div>
        <h3 class="h2"><?php echo $elemento['title']; ?></h3>
    </div>

        <?php echo $elemento['text']; ?>

</div>