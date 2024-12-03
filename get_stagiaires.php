<?php
include 'db_connection.php'; // Include your DB connection setup

// Check for the 'filieres' parameter
if (isset($_GET['filieres'])) {
    $filieres = explode(',', $_GET['filieres']); // Split the parameter into an array

    // Generate placeholders for the prepared statement
    $placeholders = implode(',', array_fill(0, count($filieres), '?'));

    // Query to fetch stagiaires for specified filières
    $stmt = $conn->prepare("
        SELECT stagiaires.id, stagiaires.nom, stagiaires.prenom 
        FROM stagiaires 
        JOIN filiere ON stagiaires.ID_FILIERE = filiere.id
        WHERE filiere.NOM_FILIERE IN ($placeholders)
    ");

    // Bind each filière dynamically
    $stmt->bind_param(str_repeat('s', count($filieres)), ...$filieres);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch stagiaires and return as JSON
    $stagiaires = [];
    while ($row = $result->fetch_assoc()) {
        $stagiaires[] = $row;
    }

    echo json_encode($stagiaires);
    exit;
}
