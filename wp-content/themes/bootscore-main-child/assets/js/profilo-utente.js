function goToUploadPage() {
    window.location.href = "/upload-2/";
}

function goToSection(event, sectionId) {
    event.preventDefault();

    // Rimuovi la classe active da tutti i link
    const sidebarLinks = document.querySelectorAll('.nav-link');
    sidebarLinks.forEach(link => link.classList.remove('active'));

    // Aggiungi la classe active al link cliccato
    event.currentTarget.classList.add('active');

    // Scorri fino alla sezione specificata
    const section = document.querySelector(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

function loadSettingsPage(event) {
    event.preventDefault();

    // Rimuovi la classe active da tutti i link
    const sidebarLinks = document.querySelectorAll('.nav-link');
    sidebarLinks.forEach(link => link.classList.remove('active'));

    // Aggiungi la classe active al link cliccato
    event.currentTarget.classList.add('active');

    // Carica il contenuto della pagina impostazioni
    const contentContainer = document.getElementById('main-profile-section');
    fetch('/wp1/wp-content/themes/bootscore-main-child/template-parts/profilo-utente/sezione-impostazioni.php') // URL della pagina impostazioni
        .then(response => response.text())
        .then(html => {
            contentContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Errore nel caricamento della pagina impostazioni:', error);
        });
}

