<div class="container mt-4">
  <div class="card shadow">
    <div class="card-body">
      <h5 class="card-title">Recensioni</h5>
      <p class="card-text">Leggi le recensioni degli altri utenti o scrivi la tua:</p>

      <!-- Elenco recensioni -->
      <div class="reviews-section mb-4">
        <div class="review-item border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <strong>Mario Rossi</strong>
            <small class="text-muted">15 gennaio 2025</small>
          </div>
          <div class="text-warning mb-2">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-half"></i>
            <i class="bi bi-star"></i>
          </div>
          <p class="mb-0">Documento molto interessante, scritto in modo chiaro e completo. Lo consiglio!</p>
        </div>

        <div class="review-item border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between align-items-center">
            <strong>Giulia Bianchi</strong>
            <small class="text-muted">12 gennaio 2025</small>
          </div>
          <div class="text-warning mb-2">
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star-fill"></i>
            <i class="bi bi-star"></i>
          </div>
          <p class="mb-0">Ho trovato tutto ciò di cui avevo bisogno, ma potrebbe essere più sintetico.</p>
        </div>
      </div>

      <!-- Scrivi una recensione -->
      <div class="write-review">
        <h6 class="mb-3">Scrivi una recensione</h6>
        <form>
          <!-- Campo stelle -->
          <div class="mb-3">
            <label for="rating" class="form-label">Valutazione</label>
            <div id="rating" class="d-flex align-items-center">
              <i class="bi bi-star text-secondary fs-4" onclick="setRating(1)"></i>
              <i class="bi bi-star text-secondary fs-4" onclick="setRating(2)"></i>
              <i class="bi bi-star text-secondary fs-4" onclick="setRating(3)"></i>
              <i class="bi bi-star text-secondary fs-4" onclick="setRating(4)"></i>
              <i class="bi bi-star text-secondary fs-4" onclick="setRating(5)"></i>
            </div>
            <input type="hidden" id="ratingValue" name="ratingValue" value="0">
          </div>
          <!-- Campo testo -->
          <div class="mb-3">
            <label for="reviewText" class="form-label">La tua recensione</label>
            <textarea id="reviewText" class="form-control" rows="3" placeholder="Scrivi qui la tua recensione..." required></textarea>
          </div>
          <!-- Pulsante invia -->
          <button type="submit" class="btn btn-primary">Invia recensione</button>
        </form>
      </div>
    </div>
  </div>
</div>
