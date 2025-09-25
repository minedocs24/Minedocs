<?php
/**
 * Template part for displaying an icon.
 *
 * @param string $icon_name The name of the SVG file (without extension).
 * @param int $size The size of the icon in pixels.
 */

// Ensure the parameters are set
$icon_name = isset($args['icon_name']) ? $args['icon_name'] : 'default-icon';
$size = isset($args['size']) ? intval($args['size']) : 24;
$inline = isset($args['inline']) ? $args['inline'] : false;

// Path to the SVG file
$icon_path = get_stylesheet_directory() . '/assets/icons/' . $icon_name . '.svg';

// Check if the SVG file exists
if (file_exists($icon_path)) {
    $icon_svg = file_get_contents($icon_path);
} else {
    $icon_svg = '<!-- Icon not found -->';
}
?>

<div class="icon" style="width: <?php echo esc_attr($size); ?>px; height: <?php echo esc_attr($size); ?>px; <?php echo $inline ? 'display: inline-block;' : ''; ?>">
    <?php echo $icon_svg; // Output the SVG content ?>
</div>