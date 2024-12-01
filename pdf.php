<?php

// Get the GET parameters
$searchStages = $_GET['searchStages'] ?? ''; // Use null coalescing operator for default
$searchTypeStage = $_GET['searchTypeStage'] ?? '';
$encadrent = $_GET['encadrent'] ?? '';
$dateSoutenance = $_GET['dateSoutenance'] ?? '';
$juryId = $_GET['juryId'] ?? '';

// PDF generation logic here
require_once 'vendor/autoload.php';
include('db_connection.php');
include('data.php');

// Use the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize DOMPDF with options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);  // Enable HTML5 support
$options->set('isPhpEnabled', true);  // Enable PHP functions if necessary
$dompdf = new Dompdf($options);

// Convert image to base64
$imagePath = 'C:/wamp64/www/module_gestion_stages/src/img/Logo_ISET_Kélibia.jpg';
$imageData = '';

if (file_exists($imagePath)) {
    $imageData = base64_encode(file_get_contents($imagePath));
} else {
    echo "Image file does not exist at: " . $imagePath;
    exit;  // Stop execution if image isn't found
}

// Start building the HTML content
$html = "
<html>
<head>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header-section {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 20px;
            width: 100%;
        }

        .header-logo {
            margin-right: 20px;
        }

        .header-logo img {
            width: 80px;  
            height: auto;
        }

        .header-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .header-text h3,
        .header-text h4 {
            margin: 0;
            padding: 0;
            color: #003366;
        }

        h1 {
            text-align: center;
            font-size: 28px;
            margin-top: 20px;
            color: #003366;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 10px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
            color: #003366;
        }

        td {
            font-size: 12px;
            color: #333;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #003366;
            padding: 10px 0;
            background-color: #fff;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class='section'>
        <div class='header-section'>
        <div class='header-logo'>
            <img src='data:image/jpeg;base64,{$imageData}' alt='ISET Kélibia Logo'>
        </div>
        <div class='header-text'>
            <h3>Ministère de l'Enseignement Supérieur et de la Recherche Scientifique</h3>
            <h4>Direction Général des Études Technologiques</h4>
            <h3>Institut Supérieur des Études Technologiques de Kélibia</h3>
            <h4>Département Technologies de l'Informatique</h4>
        </div>
    </div>

    </div>
    <br><br>
    <h1>Soutenances des stages</h1>
    <table>
        <thead>
            <tr>
                <th>Étudiant(s)</th>
                <th>Sujet</th>
                <th>Date de soutenance</th>
                <th>Encadrant</th>
                <th>Jury ID</th>
                <th>Intitulé</th>
            </tr>
        </thead>
        <tbody>";

// Start building the query based on GET parameters
$whereConditions = [];

if ($searchStages) {
    $whereConditions[] = "(s.nom LIKE '%" . $conn->real_escape_string($searchStages) . "%' OR s.prenom LIKE '%" . $conn->real_escape_string($searchStages) . "%')";
}
if ($searchTypeStage) {
    $whereConditions[] = "stages.type LIKE '%" . $conn->real_escape_string($searchTypeStage) . "%'";
}

// If an encadrant's name is passed, retrieve the encadrant's ID first
if ($encadrent) {
    $encadrant_query = "SELECT id FROM encadrants WHERE nom LIKE '%" . $conn->real_escape_string($encadrent) . "%' LIMIT 1";
    $encadrant_result = $conn->query($encadrant_query);

    if ($encadrant_result && $encadrant_row = $encadrant_result->fetch_assoc()) {
        $encadrent_id = $encadrant_row['id'];
        $whereConditions[] = "stages.id_encadrant = " . (int)$encadrent_id;
    } else {
        $whereConditions[] = "stages.id_encadrant IS NULL";  // No matching encadrant found
    }
}

if ($dateSoutenance) {
    $whereConditions[] = "stages.date_soutenance = '" . $conn->real_escape_string($dateSoutenance) . "'";
}
if ($juryId) {
    $whereConditions[] = "stages.id_jery = " . (int)$juryId;
}

$whereSql = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

$query = "
    SELECT stages.*, s.nom AS stagiaire_nom, s.prenom AS stagiaire_prenom 
    FROM stages 
    JOIN stage_stagiaire ss ON stages.id = ss.stage_id
    JOIN stagiaires s ON ss.stagiaire_id = s.id
    " . $whereSql;

$stages = $conn->query($query);

if (!$stages) {
    die("Error retrieving stages: " . $conn->error);
}

if ($stages->num_rows > 0) {
    while ($stage_info = $stages->fetch_assoc()) {
        // Handle the case where id_encadrant might be null or empty
        $encadrant_name = 'None';
        if (!empty($stage_info['id_encadrant'])) {
            $encadrant = $conn->query("SELECT nom FROM encadrants WHERE id = " . $stage_info['id_encadrant']);
            if ($encadrant && $encadrant_row = $encadrant->fetch_assoc()) {
                $encadrant_name = $encadrant_row['nom'];
            }
        }

        // Constructing the table row for each stage
        $html .= "
        <tr>
            <td>" . htmlspecialchars($stage_info['stagiaire_nom']) . " " . htmlspecialchars($stage_info['stagiaire_prenom']) . "</td>
            <td>" . htmlspecialchars($stage_info['type']) . "</td>
            <td>" . htmlspecialchars($stage_info['date_soutenance']) . "</td>
            <td>" . htmlspecialchars($encadrant_name) . "</td>
            <td>" . htmlspecialchars($stage_info['id_jery']) . "</td>
            <td>" . htmlspecialchars($stage_info['intitule']) . "</td>
        </tr>";
    }
} else {
    $html .= "<tr><td colspan='6'>No stages found.</td></tr>";
}

// Close the table body and table tag
$html .= "</tbody></table>
<footer>
<hr>
Institut Supérieur des Études Technologiques de Kélibia, BP 139, 8090 Kélibia, Tunisie
</footer>
";

// Load HTML content to DOMPDF
$dompdf->loadHtml($html);

// Set paper size (A4)
$dompdf->setPaper('A4');

// Render PDF (first pass: calculate page sizes)
$dompdf->render();

// Output the PDF (inline view in the browser)
$dompdf->stream('soutenances_stages_isetkl.pdf', array("Attachment" => false));
