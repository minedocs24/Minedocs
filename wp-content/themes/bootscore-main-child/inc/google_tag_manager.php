<?php


// Google Tag Manager
// https://www.google.com/analytics/tag-manager/
// https://developers.google.com/tag-manager/web/csp
// https://developers.google.com/tag-manager/web/csp

/*add_action('wp_head', function () {
  ?>
<!-- Google Tag Manager - Iubenda Consent-Based -->
<script type="text/plain" class="_iub_cs_activate" data-iub-purposes="marketing">
(function(w,d,s,l,i){
  w[l]=w[l]||[];
  w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
  var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),
      dl=l!='dataLayer'?'&l='+l:'';
  j.async=true;
  j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
  f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PC9WKQ5S'); // <-- Sostituisci con il tuo ID GTM
</script>
  <?php
}, 30); // Priority 1 per metterlo subito nell'head
*/

/*
add_action('wp_head', function () {
  ?>
<!-- GTM + Iubenda Marketing Consent Check -->
<script>
(function waitForIubendaConsentAPI(attempt = 0) {
if (attempt > 5) {
  console.warn("❌ Iubenda API non disponibile dopo 5 secondi");
  return;
}

if (
  typeof _iub !== "undefined" &&
  _iub.cs &&
  _iub.cs.api &&
  typeof _iub.cs.api.isConsentGiven === "function"
) {
  console.log("✅ _iub.cs.api pronto");

  if (_iub.cs.api.isConsentGiven({ purpose: "marketing" })) {
    console.log("✅ Consenso marketing dato, carico GTM");

    (function(w,d,s,l,i){
      w[l]=w[l]||[];
      w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
      var f=d.getElementsByTagName(s)[0],
          j=d.createElement(s),
          dl=l!='dataLayer'?'&l='+l:'';
      j.async=true;
      j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
      f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PC9WKQ5S'); // <-- Inserisci il tuo ID GTM

  } else {
    console.log("❌ Consenso marketing non dato");
  }
} else {
  setTimeout(function () {
    waitForIubendaConsentAPI(attempt + 1);
  }, 100); // Retry ogni 100ms
}
})();
</script>
<!-- Fine controllo GTM -->
  <?php
}, 0);



/*
add_action('wp_head', function() {
    ?>
<!-- Google Tag Manager -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
      console.log("DOMContentLoaded");
    // Funzione di controllo del consenso marketing
    console.log("typeof _iub", typeof _iub);
    console.log("_iub", _iub);
    console.log("_iub.cs", _iub.cs);
    console.log("_iub.cs.api", _iub.cs.api);
    console.log("_iub.cs.api.isConsentGiven", _iub.cs.api.isConsentGiven);
    if (typeof _iub !== "undefined" && _iub.cs && _iub.cs.api && _iub.cs.api.isConsentGiven) {
      console.log("isConsentGiven");
      if (_iub.cs.api.isConsentGiven({ purpose: "marketing" })) {
        console.log("isConsentGiven true");
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PC9WKQ5S');
    } else {
      console.log("isConsentGiven false");
    }
  }
  else {
    console.log("isConsentGiven undefined");
  }
});
</script>
<!-- End Google Tag Manager -->

    <?php
}, 30);
*/






/*
add_action('wp_body_open', function() {
    ?>
<!-- Google Tag Manager (noscript) -->
<noscript>
        
<iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PC9WKQ5S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
});*/

/*
add_action('wp_body_open', function() {
    ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    function loadGTMnoscript() {
      var iframe = document.createElement("iframe");
      iframe.src = "https://www.googletagmanager.com/ns.html?id=GTM-PC9WKQ5S";
      iframe.height = 0;
      iframe.width = 0;
      iframe.style.display = "none";
      iframe.style.visibility = "hidden";
      document.body.appendChild(iframe);
    }

    if (typeof _iub !== "undefined" && _iub.cs && _iub.cs.api && _iub.cs.api.isConsentGiven) {
      if (_iub.cs.api.isConsentGiven({ purpose: "marketing" })) {
        loadGTMnoscript();
      }

      _iub.cs.api.onConsentGiven(function () {
        if (_iub.cs.api.isConsentGiven({ purpose: "marketing" })) {
          loadGTMnoscript();
        }
      });
    }
  });
</script>
    <?php
});
*/


add_action('wp_head', function() {
    ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-PC9WKQ5S');</script>
<!-- End Google Tag Manager -->
    <?php
}, 30);


add_action('wp_body_open', function() {
    ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PC9WKQ5S"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
    <?php
}, 0);



