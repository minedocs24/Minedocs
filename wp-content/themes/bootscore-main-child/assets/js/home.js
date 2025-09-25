
document.addEventListener('DOMContentLoaded', function () {
    // Funzione per animare il numero
    function animateCounter(element, start, end, duration) {
        let startTime = null;

        function animation(currentTime) {
            if (!startTime) startTime = currentTime;
            let progress = currentTime - startTime;
            let currentNumber = Math.easeOutQuad(progress, start, end - start, duration);
            element.innerText = Math.floor(currentNumber);
            if (progress < duration) {
                requestAnimationFrame(animation);
            } else {
                element.innerText = end;
            }
        }

        requestAnimationFrame(animation);
    }

    // Funzione di easing per rendere l'animazione più fluida
    Math.easeOutQuad = function (t, b, c, d) {
        t /= d;
        return -c * t * (t - 2) + b;
    };

    // Seleziona tutti gli elementi con la classe 'counter'
    let counterElements = document.querySelectorAll('.counter');

    // Utilizzo di IntersectionObserver per avviare l'animazione quando gli elementi diventano visibili
    let observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                let endValue = parseInt(entry.target.innerText);
                animateCounter(entry.target, 0, endValue, 2000); // Durata: 2000ms (2 secondi)
                observer.unobserve(entry.target); // Interrompe l'osservazione una volta avviata l'animazione
            }
        });
    });

    // Osserva ciascun elemento con la classe 'counter'
    counterElements.forEach(function (element) {
        observer.observe(element);
    });
});
