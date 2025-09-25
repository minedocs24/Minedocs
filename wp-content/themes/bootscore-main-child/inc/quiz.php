<?php
header("Content-Type: application/json");

$questions = [
    [
        "question" => "Qual è il colore del cielo?",
        "options" => ["Rosso", "Blu", "Verde", "Giallo"],
        "correct" => [1] // Indice della risposta corretta
    ],
    [
        "question" => "Quali sono i colori primari?",
        "options" => ["Rosso", "Blu", "Verde", "Giallo"],
        "correct" => [0, 1, 3] // Indici delle risposte corrette
    ],
    [
        "question" => "Quanto fa 5 + 3?",
        "options" => ["5", "8", "10", "6"],
        "correct" => [1]
    ]
];

// Numero di domande richiesto (se specificato)
$num_questions = isset($_GET['num']) ? (int)$_GET['num'] : count($questions);
shuffle($questions);
$selected_questions = array_slice($questions, 0, $num_questions);

echo json_encode($selected_questions);
?>
