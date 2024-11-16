<?php
// Include Composer's autoload file
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
$imagePath = 'C:/wamp64/www/test_module_gestion_stages/src/img/Logo_ISET_Kélibia.jpg';
$imageData = base64_encode(file_get_contents($imagePath));

// Start building the HTML content
$html = "
<html>
<head>
    <style>
        /* Body styling */
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
    width: 100%; /* Ensure it takes up full width */
}

.header-logo {
    margin-right: 20px;
}

.header-logo img {
    width: 80px;  /* Adjust logo size */
    height: auto;
}

.header-text {
    display: flex;
    flex-direction: column; /* Stack text vertically */
    justify-content: center;
}

.header-text h3,
.header-text h4 {
    margin: 0;
    padding: 0;
    color: #003366; /* Ensure text color */
}

.header-text h3 {
    font-size: 18px;
}

.header-text h4 {
    font-size: 16px;
}


        h1 {
            text-align: center;
            font-size: 28px;
            margin-top: 20px;
            color: #003366;
        }

        h3,
        h4 {
            text-align: center;
            margin: 5px 0;
            color: #003366;
        }

        h3 {
            font-size: 18px;
        }

        h4 {
            font-size: 16px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
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

        tr:nth-child(even) {
            background-color: #f9f9f9;
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

        .footer-text {
            margin-top: 10px;
            font-size: 12px;
        }

        /* Spacing between sections */
        .section {
            margin-bottom: 20px;
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

$stages = $conn->query("SELECT * FROM stages"); // Adjust this query to your needs

if (!$stages) {
    die("Error retrieving stages: " . $conn->error); // Ensure you get an error message if the query fails
}

if ($stages->num_rows > 0) {
    while ($stage_info = $stages->fetch_assoc()) {
        // Get stagiaires (students) for the current stage
        $stagiaires = $conn->query("SELECT s.nom, s.prenom FROM stagiaires s 
                                    JOIN stage_stagiaire ss ON s.id = ss.stagiaire_id
                                    WHERE ss.stage_id = " . $stage_info['id']);

        // Store stagiaire names in an array
        $stagiaires_names = [];
        while ($stagiaire = $stagiaires->fetch_assoc()) {
            $stagiaires_names[] = $stagiaire['nom'] . " " . $stagiaire['prenom'];
        }

        // Handle the case where id_encadrant might be null or empty
        $encadrant_name = 'None';  // Default value in case of no encadrant
        if (!empty($stage_info['id_encadrant'])) {
            $encadrant = $conn->query("SELECT nom FROM encadrants WHERE id = " . $stage_info['id_encadrant']);
            if ($encadrant && $encadrant_row = $encadrant->fetch_assoc()) {
                $encadrant_name = $encadrant_row['nom'];
            }
        }

        // Constructing the table row for each stage
        $html .= "
        <tr>
            <td>" . implode(", ", $stagiaires_names) . "</td>
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
