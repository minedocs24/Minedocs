<?php
error_log('captcha-image.php');
session_start();

// Configura i parametri del CAPTCHA
$captcha_text = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6); // Codice di 6 caratteri
$_SESSION['captcha_code'] = $captcha_text; // Salva il codice nella sessione

// Dimensioni dell'immagine
$width = 150;
$height = 50;

// Crea l'immagine
$image = imagecreatetruecolor($width, $height);

// Colori
$background_color = imagecolorallocate($image, 240, 240, 240); // Grigio chiaro
$text_color = imagecolorallocate($image, 50, 50, 50);          // Grigio scuro
$line_color = imagecolorallocate($image, 220, 220, 220);       // Linee di disturbo

// Riempie lo sfondo
imagefill($image, 0, 0, $background_color);

// Aggiungi linee di disturbo
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand(0, $height), $width, rand(0, $height), $line_color);
}

// Aggiungi il testo
$font = get_stylesheet_directory_uri() . '/assets/arial.ttf'; // Percorso al font TTF
imagettftext($image, 20, rand(-10, 10), 20, 35, $text_color, $font, $captcha_text);

// Invia l'immagine come PNG
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);

