<?php
global $product;
?>

<button type="button" class="btn btn-primary my-2" id="showReviewFormButton">Scrivi una recensione</button>

<div id="scrivi-recensione" class="card shadow border-0 my-4 rounded" style="display: none;">
    <div id="cardHeader" class="card-header card-header-scrivi-recensione" style="cursor: pointer;">
        <h5 class="card-title">Scrivi una recensione</h5>
    </div>
    <div id="cardBody">
        <div class="card-body">
            <form id="reviewForm">
                <h6 class="card-title"><strong>Scrivi una recensione per questo documento</strong></h6>
                <div class="mb-3">
                    <label for="reviewRating" class="form-label">Voto</label>
                    <div id="reviewRating" class="rating-box">
                        <input type="radio" id="star5" name="rating" value="5">
                        <label for="star5" title="5 stars" tooltip="Eccezionale 🤩">&#9733;</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars" tooltip="Molto buono 😄">&#9733;</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars" tooltip="Migliorabile 🫤">&#9733;</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars" tooltip="Deludente ☹️">&#9733;</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star" tooltip="Davvero scadente 😵‍💫">&#9733;</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reviewTextarea" class="form-label">Scrivi la tua recensione</label>
                    <textarea class="form-control" id="reviewTextarea" rows="4" placeholder="Inserisci la tua recensione qui..."></textarea>
                </div>
                <input type="hidden" id="postId" value="<?php echo $product->get_id(); ?>">
            </form>
        </div>
        <div class="card-footer bg-lightgray text-end">
            <button type="button" class="btn btn-primary" id="submitReviewButton">
                <div id="icon-loading-send-review" class="btn-loader mx-2" hidden style="display: inline-block;">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                Invia Recensione
            </button>
        </div>
    </div>
</div>

<style>
    .card-header-scrivi-recensione {
        background-color: #007bff;
        color: white;
    }
</style>

<script>


</script>
