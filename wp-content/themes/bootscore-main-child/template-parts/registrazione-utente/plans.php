<?php
    $plans = [];
    $plan_slugs = ['pro-trimestrale', 'pro-annuale'];

    foreach ($plan_slugs as $slug) {
        $query = new WP_Query([
            'name' => $slug,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $prezzo_totale = get_post_meta(get_the_ID(), '_price', true);
                $prezzo_mensile = ($slug === 'pro-trimestrale') ? $prezzo_totale / 3 : $prezzo_totale / 12;
                $cadenza = ($slug === 'pro-trimestrale') ? '3 mesi' : '12 mesi';
                $slug_cadenza = ($slug === 'pro-trimestrale') ? 'trimestrale' : 'annuale';
                $plans[] = [
                    'nome' => get_the_title(),
                    'prezzo_totale' => $prezzo_totale,
                    'prezzo_mensile' => $prezzo_mensile,
                    'cadenza' => $cadenza,
                    'slug_cadenza' => $slug_cadenza
                ];
            }
            wp_reset_postdata();
        }
    }
?>




<?php 
foreach ($plans as $plan) {
    $is_selected = $plan['slug_cadenza'] == "annuale";
    $text_color = $is_selected ? "primary" : "danger";
?>
    <div class="plan-card flex-grow-1 mx-2 hover-card <?php echo $is_selected ? "selected" : ""; ?>" 
            data-plan="<?php echo $plan['slug_cadenza']; ?>" 
            id="plan-<?php echo $plan['slug_cadenza']; ?>">
        <input type="radio" 
                id="plan-<?php echo $plan['slug_cadenza']; ?>" 
                name="plan" 
                value="<?php echo $plan['slug_cadenza']; ?>" 
                class="d-none">
        <h5 class="mb-3">Piano <span class="text-<?php echo $text_color; ?>"><?php echo $plan['slug_cadenza']; ?></span></h5>
        <p class="price mb-2"><strong><?php echo $plan['prezzo_mensile']; ?> €</strong> /mese</p>
        <small class="text-muted">Addebito <?php echo $plan['prezzo_totale']; ?>€ ogni <?php echo $plan['cadenza']; ?></small>
        <p class="mt-2"><strong><?php echo ($plan['slug_cadenza'] === 'trimestrale') ? AMOUNT_PUNTI_PRO_ABBPRO090 . ' punti Pro' : AMOUNT_PUNTI_PRO_ABBPRO365 . ' punti Pro'; ?></strong> inclusi nel piano!</p>
    </div>
<?php } ?>


<style>
    @media (max-width: 768px) {
    .plan-card {
        min-width: 150px;
        padding: 1rem;
    }
    
    .plan-card h6 {
        font-size: 1rem;
    }
    
    .plan-card .price {
        font-size: 1.25rem;
    }
}
</style>