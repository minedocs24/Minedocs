

<?php

/**
 * Template Name: Quiz
 *
 * 
 *
 * @package Bootscore
 * @version 6.0.0
 */

get_header();
?>

<div class="container mt-5">
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="numQuestions" class="form-label">Numero di domande:</label>
            <input type="number" class="form-control" id="numQuestions" min="1" value="3">
        </div>
        <div class="col-md-6">
            <label for="timeLimit" class="form-label">Tempo (secondi):</label>
            <input type="number" class="form-control" id="timeLimit" min="10" value="60">
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <button id="startQuiz" class="btn btn-primary">Inizia Quiz</button>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <div id="timer" class="alert alert-info" role="alert"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="progress">
                <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-center">
            <div id="questionNumbers" class="btn-group" role="group"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <div id="quiz"></div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12">
            <div id="results"></div>
        </div>
    </div>
</div>

<?php
get_footer();
?>
<script>


    document.addEventListener("DOMContentLoaded", function () {
    let quizContainer = document.getElementById("quiz");
    let resultsContainer = document.getElementById("results");
    let startButton = document.getElementById("startQuiz");
    let timeInput = document.getElementById("timeLimit");
    let numQuestionsInput = document.getElementById("numQuestions");
    let progressBar = document.getElementById("progressBar");
    let questionNumbers = document.getElementById("questionNumbers");

    let questions = [];
    let currentQuestion = 0;
    let score = 0;
    let totalTime = 60;
    let timer;
    let userAnswers = [];

    startButton.addEventListener("click", startQuiz);

    var url_quiz = "<?php echo get_stylesheet_directory_uri(); ?>/inc/quiz.php";

    function startQuiz() {
        let numQuestions = numQuestionsInput.value || 3;
        totalTime = timeInput.value || 60;
        
        fetch(`${url_quiz}?num=${numQuestions}`)
            .then(response => response.json())
            .then(data => {
                questions = data;
                currentQuestion = 0;
                score = 0;
                resultsContainer.innerHTML = "";
                startButton.style.display = "none";
                loadQuestion();
                startTimer();
                updateProgressBar();
                renderQuestionNumbers();
            });
    }

    function startTimer() {
        let timeLeft = totalTime;
        let timerDisplay = document.getElementById("timer");
        timerDisplay.textContent = `Tempo rimasto: ${timeLeft}s`;
        
        timer = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = `Tempo rimasto: ${timeLeft}s`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                endQuiz();
            }
        }, 1000);
    }

    function loadQuestion() {
        if (currentQuestion >= questions.length) {
            endQuiz();
            return;
        }

        let q = questions[currentQuestion];
        quizContainer.innerHTML = `<h3>${q.question}</h3>`;
        
        q.options.forEach((option, index) => {
            let checkbox = document.createElement("input");
            checkbox.type = q.correct.length > 1 ? "checkbox" : "radio";
            checkbox.name = "answer";
            checkbox.value = index;
            checkbox.classList.add("form-check-input");
            
            if (userAnswers[currentQuestion] && userAnswers[currentQuestion].includes(index)) {
                checkbox.checked = true;
            }

            let label = document.createElement("label");
            label.classList.add("form-check-label");
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(" " + option));

            let div = document.createElement("div");
            div.classList.add("form-check");
            div.appendChild(label);

            quizContainer.appendChild(div);
        });

        let navButtons = document.createElement("div");
        navButtons.classList.add("d-flex", "justify-content-between", "mt-3");

        let prevButton = document.createElement("button");
        prevButton.textContent = "Indietro";
        prevButton.classList.add("btn", "btn-secondary");
        prevButton.addEventListener("click", prevQuestion);
        navButtons.appendChild(prevButton);

        let nextButton = document.createElement("button");
        nextButton.textContent = "Avanti";
        nextButton.classList.add("btn", "btn-secondary");
        nextButton.addEventListener("click", nextQuestion);
        navButtons.appendChild(nextButton);

        quizContainer.appendChild(navButtons);
    }

    function prevQuestion() {
        if (currentQuestion > 0) {
            saveAnswer();
            currentQuestion--;
            loadQuestion();
            updateProgressBar();
        }
    }

    function nextQuestion() {
        saveAnswer();
        if (currentQuestion < questions.length - 1) {
            currentQuestion++;
            loadQuestion();
            updateProgressBar();
        }
    }

    function saveAnswer() {
        let selectedAnswers = Array.from(document.querySelectorAll("input[name='answer']:checked"))
            .map(input => parseInt(input.value));
    }

    function checkAnswer() {
        let selectedAnswers = userAnswers[currentQuestion] || [];
        let correctAnswers = questions[currentQuestion].correct;
        
        if (selectedAnswers.length > 0 && selectedAnswers.sort().toString() === correctAnswers.sort().toString()) {
            score++;
        }
    }

    function endQuiz() {
        clearInterval(timer);
        questions.forEach((_, index) => checkAnswer(index));
        let finalScore = Math.round((score / questions.length) * 30);
        resultsContainer.innerHTML = `<h2>Punteggio finale: ${finalScore}/30</h2>`;
        startButton.style.display = "block";
    }

    function updateProgressBar() {
        let progress = ((currentQuestion + 1) / questions.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute("aria-valuenow", progress);
    }

    function renderQuestionNumbers() {
        questionNumbers.innerHTML = "";
        questions.forEach((_, index) => {
            let button = document.createElement("button");
            button.textContent = index + 1;
            button.classList.add("btn", "btn-outline-primary");
            button.addEventListener("click", () => {
                saveAnswer();
                currentQuestion = index;
                loadQuestion();
                updateProgressBar();
            });
            questionNumbers.appendChild(button);
        });
    }
});
</script>
