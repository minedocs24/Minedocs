<?php

$features = $args['features'];


?>


<div class="features-grid row my-5">
    <?php foreach ($features as $feature) : ?>
        <?php /* get_template_part('template-parts/search/feature', null, array('feature' => $feature)); */ ?>
        <div class="feature-item">
            <div class="feature-icon bg-<?php echo $feature['color']; ?>-subtle">
                <i class="fas fa-<?php echo $feature['icon']; ?> text-<?php echo $feature['color']; ?>"></i>
            </div>

            <span class="feature-text"><?php echo esc_html($feature['text']); ?></span>

        </div>
    <?php endforeach; ?>
</div>