<?php

/**
 * Template Name: FAQ
 *
 * @package Bootscore
 * @version 6.0.0
 */


// Exit if accessed directly
defined('ABSPATH') || exit;

get_header();


$faq_json = json_decode( get_faqs_json());



?>



<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0 no-hover">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">FAQ - Domande Frequenti</h2>
                    <?php the_content(); ?>

                    <div class="faq-container row g-4">
                        <div class="col-md-3">
                            <div class="nav flex-column nav-pills bg-light rounded-3 p-3" id="faqTabs" role="tablist" aria-orientation="vertical">
                                <?php
                                $first = true;
                                foreach ($faq_json as $categoria_slug => $categoria) {
                                    $active = $first ? 'active' : '';
                                    echo "<button class='nav-link nav-link-faq $active mb-2' id='{$categoria_slug}-tab' data-bs-toggle='pill' data-bs-target='#{$categoria_slug}' type='button' role='tab'>{$categoria->name}</button>";
                                    $first = false;
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="tab-content" id="faqTabContent">
                                <?php
                                $first = true;
                                foreach ($faq_json as $categoria_slug => $categoria) {
                                    $show = $first ? 'show active' : '';
                                    echo "<div class='tab-pane fade $show' id='{$categoria_slug}' role='tabpanel'>";
                                    echo "<div class='lead mb-4'>{$categoria->description}</div>";
                                    echo "<div class='accordion' id='faqAccordion{$categoria_slug}'>";
                                    foreach ($categoria->faqs as $index => $faq) {
                                        $collapseShow = $index === 0 ? 'show' : '';
                                        echo "<div class='accordion-item border-0 mb-3 shadow-sm'>";
                                        echo "<h2 class='accordion-header'>";
                                        echo "<button class='accordion-button $collapseShow' type='button' data-bs-toggle='collapse' data-bs-target='#faq{$categoria_slug}{$index}'>{$faq->domanda}</button>";
                                        echo "</h2>";
                                        echo "<div id='faq{$categoria_slug}{$index}' class='accordion-collapse collapse $collapseShow' data-bs-parent='#faqAccordion{$categoria_slug}'>";
                                        echo "<div class='accordion-body'>{$faq->risposta}</div>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    echo "</div>";
                                    echo "</div>";
                                    $first = false;
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
get_footer();
?>
