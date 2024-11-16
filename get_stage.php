<?php
include('db_connection.php');

$stage_id = $_GET['stage_id'];
$sql = "SELECT * FROM stages WHERE id = $stage_id";
$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $row['stagiaires'] = []; // Populate stagiaires
    $sql_stagiaires = "SELECT stagiaire_id FROM stage_stagiaire WHERE stage_id = $stage_id";
    $result_stagiaires = $conn->query($sql_stagiaires);
    while ($stagiaire = $result_stagiaires->fetch_assoc()) {
        $row['stagiaires'][] = $stagiaire['stagiaire_id'];
    }
    echo json_encode($row);
} else {
    echo json_encode(["error" => "Stage not found"]);
}
