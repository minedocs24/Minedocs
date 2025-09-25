<?php
/**
 * Template Name: Coming Soon AI
 * Description: Una pagina "Coming Soon" personalizzata per WordPress.
 */

?>


<body>
    <div class="coming-soon-ai-container">
        <div class="coming-soon-ai-content">
            <h1>STUDIA CON L'AI</h1>
            <p class="coming-soon-ai-coming-soon">Coming soon</p>
            <p>Studia con la nostra intelligenza artificiale e scopri quanto gli argomenti più complessi possano diventare estremamente semplici da comprendere!</p>
            <a href="<?php echo home_url(); ?>" class="coming-soon-ai-back-link">Torna alla home</a>
        </div>
        <div class="coming-soon-ai-robot-image"></div>
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

    .coming-soon-ai-container {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        width: 100%;
        height: 100%;
        padding: 20px;
        position: relative;
    }

    .coming-soon-ai-content {
        max-width: 700px;
        padding-left: 150px;
    }

    h1 {
        font-size: 70px;
        margin: 0;
        font-weight: bold;
        color:rgb(0, 0, 0);
    }

    .coming-soon-ai-coming-soon {
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

    .coming-soon-ai-back-link {
        display: inline-block;
        margin-top: 10px;
        font-size: 18px;
        color:rgb(0, 0, 0);
        text-decoration: none;
        position: relative; /* Necessario per il posizionamento di ::after */
        overflow: hidden;
    }

    .coming-soon-ai-back-link::after {
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

    .coming-soon-ai-back-link:hover::after {
        width: 100%; /* Espande la linea fino ai bordi del link */
    }

    .coming-soon-ai-robot-image {
        background-image: url('<?php echo get_theme_file_uri('assets/img/home/robot.webp'); ?>'); /* Sostituisci con il percorso dell'immagine */
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        position: absolute; /* Posizionamento assoluto */
        bottom: -50px;      /* Sposta metà immagine fuori dallo schermo in basso */
        right: -180px;       /* Sposta metà immagine fuori dallo schermo a destra */
        width: 500px;
        height: 500px;
        filter: blur(5px);   /* Effetto sfocatura */
    }

    @media (max-width: 768px) {
        body {
            justify-content: center; /* Centra tutto al centro per dispositivi più piccoli */
            text-align: center;
        }

        .coming-soon-ai-container {
            flex-direction: column;
        }

        .coming-soon-ai-content {
            padding-left: 0;
        }

        .coming-soon-ai-robot-image {
            bottom: -50px;
            right: -150px;
            width: 300px;
            height: 300px;
        }
    }
</style>
