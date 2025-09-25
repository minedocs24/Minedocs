<?php
/**
 * Template Name: Coming Soon Courses
 * Description: Una pagina "Coming Soon" personalizzata per WordPress.
 */

// Test del sistema di gestione errori
if (isset($_GET['test_error']) && $_GET['test_error'] === 'true') {
    test_critical_error();
}

?>

<body>
    <div class="container">
        <div class="content">
            <h1>STUDIA CON I NOSTRI CORSI</h1>
            <p class="coming-soon">Coming soon</p>
            <p>Studia con i nostri corsi e scopri quanto gli argomenti più complessi possano diventare estremamente semplici da comprendere grazie al supporto di persone competenti nella materia specifica!</p>
            <a href="<?php echo home_url(); ?>" class="back-link">Torna alla home</a>
        </div>
        <div class="book-image"></div>
    </div>
</body>


<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #ffffff;
        color: #333;
        height: 100vh;
        overflow: hidden; /* Evita lo scroll della pagina */
        display: flex;
        justify-content: flex-start; /* Allinea il contenuto a sinistra */
        align-items: center; /* Centra verticalmente il contenuto */
    }

    .container {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        width: 100%;
        height: 100%;
        padding: 20px;
        position: relative;
    }

    .content {
        max-width: 1000px;
        padding-left: 150px;
    }

    h1 {
        font-size: 70px;
        margin: 0;
        font-weight: bold;
        color:rgb(0, 0, 0);
    }

    .coming-soon {
        font-size: 35px;
        color: gray;
        font-weight: bold;
        margin: 0px 0;
    }

    p {
        font-size: 18px;
        line-height: 1.5;
        margin: 10px 0;
        color: gray;
    }

    .back-link {
        display: inline-block;
        margin-top: 10px;
        font-size: 18px;
        color:rgb(0, 0, 0);
        text-decoration: none;
        position: relative; /* Necessario per il posizionamento di ::after */
        overflow: hidden;
    }

    .back-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%; /* Posiziona il punto di partenza al centro */
        width: 0; /* Inizia con larghezza zero */
        height: 1px; /* Altezza della linea */
        background-color:rgb(0, 0, 0); /* Colore della linea */
        transition: all 0.2s ease; /* Transizione per l'animazione */
        transform: translateX(-50%); /* Centra la linea */
    }

    .back-link:hover::after {
        width: 100%; /* Espande la linea fino ai bordi del link */
    }

    .book-image {
        background-image: url('<?php echo get_theme_file_uri('assets/img/home/Iconarchive-Fairy-Tale-Hero-Magic-Book_512.webp'); ?>'); /* Sostituisci con il percorso dell'immagine */
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        position: absolute; /* Posizionamento assoluto */
        bottom: -200px;      /* Sposta metà immagine fuori dallo schermo in basso */
        right: -180px;       /* Sposta metà immagine fuori dallo schermo a destra */
        width: 600px;
        height: 600px;
        filter: blur(5px);   /* Effetto sfocatura */
    }

    @media (max-width: 768px) {
        body {
            justify-content: center; /* Centra tutto al centro per dispositivi più piccoli */
            text-align: center;
        }

        .container {
            flex-direction: column;
        }

        .content {
            padding-left: 0;
        }

        .robot-image {
            bottom: -50px;
            right: -150px;
            width: 300px;
            height: 300px;
        }
    }
</style>
