<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include('db_connection.php');

// Get the GET parameters
$searchStages = $_GET['searchStages'] ?? '';
$searchTypeStage = $_GET['searchTypeStage'] ?? '';
$encadrent = $_GET['encadrent'] ?? '';
$dateSoutenance = $_GET['dateSoutenance'] ?? '';
$juryId = $_GET['juryId'] ?? '';

// Fetch data similar to the PDF logic
$whereConditions = [];
if ($searchStages) $whereConditions[] = "(s.nom LIKE '%" . $conn->real_escape_string($searchStages) . "%')";
if ($searchTypeStage) $whereConditions[] = "stages.type LIKE '%" . $conn->real_escape_string($searchTypeStage) . "%'";
if ($encadrent) {
    $encadrant_query = "SELECT id FROM encadrants WHERE nom LIKE '%" . $conn->real_escape_string($encadrent) . "%' LIMIT 1";
    $encadrant_result = $conn->query($encadrant_query);
    if ($encadrant_result && $encadrant_row = $encadrant_result->fetch_assoc()) {
        $whereConditions[] = "stages.id_encadrant = " . (int)$encadrant_row['id'];
    }
}
if ($dateSoutenance) $whereConditions[] = "stages.date_soutenance = '" . $conn->real_escape_string($dateSoutenance) . "'";
if ($juryId) $whereConditions[] = "stages.id_jery = " . (int)$juryId;

$whereSql = count($whereConditions) > 0 ? "WHERE " . implode(" AND ", $whereConditions) : "";

$query = "
    SELECT stages.*, s.nom AS stagiaire_nom, s.prenom AS stagiaire_prenom 
    FROM stages 
    JOIN stage_stagiaire ss ON stages.id = ss.stage_id
    JOIN stagiaires s ON ss.stagiaire_id = s.id
    " . $whereSql;

$stages = $conn->query($query);

// Create a new Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Stages');

// Add headers
$headers = ['Étudiant(s)', 'Sujet', 'Date de soutenance', 'Encadrant', 'Jury ID', 'Intitulé'];
$sheet->fromArray($headers, NULL, 'A1');

// Add data rows
if ($stages->num_rows > 0) {
    $row = 2; // Starting row for data
    while ($stage_info = $stages->fetch_assoc()) {
        $sheet->fromArray([
            $stage_info['stagiaire_nom'] . ' ' . $stage_info['stagiaire_prenom'],
            $stage_info['type'],
            $stage_info['date_soutenance'],
            $stage_info['id_encadrant'] ?? 'None',
            $stage_info['id_jery'],
            $stage_info['intitule']
        ], NULL, "A$row");
        $row++;
    }
}

// Set headers to trigger download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="soutenances_stages_isetkl.xlsx"');
header('Cache-Control: max-age=0');

// Write the file and send it to the browser
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
