<?php
// Genera il link di logout
$logout_url = wp_logout_url(home_url()); // Reindirizza alla homepage dopo il logout
?>

<button class="nav-link logout" onclick="location.href='<?php echo esc_url($logout_url); ?>'">
    <i class="fas fa-sign-out-alt"></i> Esci
</button>