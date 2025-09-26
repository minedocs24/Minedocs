</section> <!-- minedocs-content - si apre in header.php -->
<footer class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-6 col-md-2 mb-3 text-start">
          <h4 class="documents-title" style="color: var(--primary);">Servizi</h4>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="<?php echo RICERCA_PAGE;?>" class="nav-link p-0 text-dark">Documenti</a></li>
            <li class="nav-item mb-2"><a href="coming-soon-AI" class="nav-link p-0 text-dark">Studia con l'AI</a></li>
            <li class="nav-item mb-2"><a href="<?php echo UPLOAD_PAGE;?>" class="nav-link p-0 text-dark">Carica documenti</a></li>
          </ul>
        </div>

        <div class="col-6 col-md-2 mb-3">
          <h5 class="documents-title" style="color: var(--primary);">Contatti</h5>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="<?php echo FAQ_PAGE;?>" class="nav-link p-0 text-dark">FAQ</a></li>
            <li class="nav-item mb-2"><a href="<?php echo CONTATTI_PAGE;?>" class="nav-link p-0 text-dark">Contattaci</a></li>
            <li class="nav-item mb-2"><a href="<?php echo CHI_SIAMO_PAGE;?>" class="nav-link p-0 text-dark">Chi Siamo</a></li>
          </ul>
        </div>

        <div class="col-6 col-md-2 mb-3">
          <h5 class="documents-title" style="color: var(--primary);">Legal</h5>
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="<?php echo TERMINI_E_CONDIZIONI_PAGE;?>" class="nav-link p-0 text-dark">Termini e condizioni</a></li>
            <!-- <li class="nav-item mb-2"><a href="https://www.iubenda.com/privacy-policy/61111609" class="nav-link p-0 text-dark">Privacy Policy</a></li>
            <li class="nav-item mb-2"><a href="https://www.iubenda.com/privacy-policy/61111609/cookie-policy" class="nav-link p-0 text-dark">Cookie Policy</a></li> -->
            <!-- Privacy Policy -->
            <li class="nav-item mb-2"><a href="https://www.iubenda.com/privacy-policy/61111609" class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Privacy Policy ">Privacy Policy</a><script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script></li>
              <!-- Cookie Policy -->
              <li class="nav-item mb-2"><a href="https://www.iubenda.com/privacy-policy/61111609/cookie-policy" class="iubenda-white iubenda-noiframe iubenda-embed iubenda-noiframe " title="Cookie Policy ">Cookie Policy</a><script type="text/javascript">(function (w,d) {var loader = function () {var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0]; s.src="https://cdn.iubenda.com/iubenda.js"; tag.parentNode.insertBefore(s,tag);}; if(w.addEventListener){w.addEventListener("load", loader, false);}else if(w.attachEvent){w.attachEvent("onload", loader);}else{w.onload = loader;}})(window, document);</script></li>

          </ul>
        </div>

        <div class="col-6 col-md-2 mb-3">
          <h5 class="documents-title" style="color: var(--primary);">Social</h5>
          <div class="social-links d-flex gap-3">
            <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Facebook">
              <svg class="social-icon" width="24" height="24" fill="currentColor">
                <use href="#facebook"></use>
              </svg>
            </a>
            <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram">
              <svg class="social-icon" width="24" height="24" fill="currentColor">
                <use href="#instagram"></use>
              </svg>
            </a>
            <!-- <a href="<?php echo TIKTOK_URL; ?>" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="TikTok">
              <svg class="social-icon" width="24" height="24" fill="currentColor">
                <use href="#tiktok"></use>
              </svg>
            </a> -->
          </div>
        </div>
      </div>

      <div class="d-flex flex-column flex-sm-row justify-content-between py-4 my-4 border-top">
        <p class="text-gray-600">© 2025 LML Technologies S.r.l. Tutti i diritti riservati.</p>
        <p class="text-gray-600">P. IVA: 09002320720</p>
        <p class="text-gray-600">Email: minedocs@lmltech.it</p>
        <p class="text-gray-600">Indirizzo: Via John Fitzgerald Kennedy 24, 70020 Bitritto (BA)</p>
      </div>
    </div>
  </footer>
  <!-- To top button -->
  <a href="#"
      class="<?= apply_filters('bootscore/class/footer/to_top_button', 'btn btn-primary shadow'); ?> position-fixed zi-1000 top-button"><i
          class="fa-solid fa-chevron-up"></i><span class="visually-hidden-focusable">To top</span></a>
  </div><!-- #page -->

  <?php wp_footer(); ?>

  </body>

  
  </html>