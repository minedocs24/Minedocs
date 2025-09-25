<?php

add_action('wp_enqueue_scripts', function() {
    
    wp_enqueue_style('swiper-css', get_stylesheet_directory_uri() . '/vendor/swiper/swiper-bundle.min.css');
});



add_action('wp_enqueue_scripts', function() {
    
    wp_enqueue_script('swiper-js', get_stylesheet_directory_uri() . '/vendor/swiper/swiper-bundle.min.js', array(), false, false);
});

