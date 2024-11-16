<?php
include('db_connection.php'); // Include your database connection

// Fetch Encadrants
$encadrants = $conn->query("SELECT * FROM encadrants");

$enseignants = $conn->query("SELECT * FROM enseignants");

// Fetch Juries
$juries = $conn->query("SELECT * FROM juries");

// Fetch Stages Stagiaires
$stages_stagiaire = $conn->query("SELECT * FROM stage_stagiaire ");

// Fetch Stagiaires
$stagiaires = $conn->query("SELECT * FROM stagiaires");

/**
 * Generates dropdown options for juries.
 * 
 * @param mysqli_result $juries Result set from the query to fetch juries.
 * @return string HTML string of option elements.
 */
function generateJuryOptions($juries)
{
    $options = "";
    while ($jury = $juries->fetch_assoc()) {
        $options .= "<option value='{$jury['id']}'>{$jury['name']}</option>";
    }
    return $options;
}

/**
 * Generates dropdown options for encadrants.
 * 
 * @param mysqli_result $encadrants Result set from the query to fetch encadrants.
 * @return string HTML string of option elements.
 */
function generateEncadrantOptions($encadrants)
{
    $options = "";
    while ($encadrant = $encadrants->fetch_assoc()) {
        $options .= "<option value='{$encadrant['id']}'>{$encadrant['name']}</option>";
    }
    return $options;
}
