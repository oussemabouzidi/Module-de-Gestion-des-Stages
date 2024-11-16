<?php
header('Content-Type: application/json');
include('db_connection.php'); // Include database connection

// Enable error logging
ini_set('display_errors', 0);  // Do not display errors directly on the page
ini_set('log_errors', 1);      // Log errors to a file
error_reporting(E_ALL);        // Report all types of errors

$stage_id = isset($_POST['stage_id']) ? (int)$_POST['stage_id'] : 0;
$type_stage = isset($_POST['type']) ? $_POST['type'] : '';
$encadrant_id = isset($_POST['encadrant']) ? (int)$_POST['encadrant'] : null;
$date_soutenance = isset($_POST['date']) ? $_POST['date'] : null;
$jury = isset($_POST['jury']) ? (int)$_POST['jury'] : null;
$intitule = isset($_POST['intitule']) ? $_POST['intitule'] : '';

$stagiaires_json = $_POST['stagiaires'];

// Log the received data for debugging
error_log("Stagiaires received: " . $stagiaires_json);

// Decode the JSON string into a PHP array
$stagiaires = json_decode($stagiaires_json, true);

// Check if decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON Decode Error: " . json_last_error_msg());
    // Handle the error appropriately, e.g., return an error response
    echo json_encode(['success' => false, 'message' => 'Invalid stagiaires data.']);
    exit;
}

error_log("Stagiaires received: " . json_encode($stagiaires));

$response = ['success' => false];

if ($stage_id <= 0) {
    $response['error'] = "Invalid stage ID";
    echo json_encode($response);
    exit;
}

if ($stage_id > 0) {
    $conn->begin_transaction();
    try {
        // Get the current jury ID associated with the stage (to check if it has changed)
        $sql_current_jury = "SELECT id_jery FROM stages WHERE id = ?";
        $stmt = $conn->prepare($sql_current_jury);
        $stmt->bind_param("i", $stage_id);
        $stmt->execute();
        $stmt->bind_result($current_jury);
        $stmt->fetch();
        $stmt->close();

        if ($current_jury !== $jury) {
            // Update old jury stage count if jury has changed
            $sql_update_old_jury = "UPDATE juries SET nbr_of_stage_actuelle = nbr_of_stage_actuelle - 1 WHERE id = ?";
            $stmt = $conn->prepare($sql_update_old_jury);
            $stmt->bind_param("i", $current_jury);
            $stmt->execute();
            $stmt->close();

            // Update new jury stage count
            $sql_update_new_jury = "UPDATE juries SET nbr_of_stage_actuelle = nbr_of_stage_actuelle + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql_update_new_jury);
            $stmt->bind_param("i", $jury);
            $stmt->execute();
            $stmt->close();
        }

        // Update `stages` table
        $sql_update_stage = "UPDATE stages SET type = ?, intitule = ?, id_encadrant = ?, date_soutenance = ?, id_jery = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update_stage);
        $stmt->bind_param("ssisii", $type_stage, $intitule, $encadrant_id, $date_soutenance, $jury, $stage_id);
        $stmt->execute();
        $stmt->close();

        // Clear existing entries in `stage_stagiaire`
        $sql_clear_stage_stagiaires = "DELETE FROM stage_stagiaire WHERE stage_id = ?";
        $stmt_clear = $conn->prepare($sql_clear_stage_stagiaires);
        $stmt_clear->bind_param("i", $stage_id);
        $stmt_clear->execute();
        $stmt_clear->close();

        // Insert new entries in `stage_stagiaire`
        $sql_insert_stage_stagiaire = "INSERT INTO stage_stagiaire (stage_id, stagiaire_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert_stage_stagiaire);

        foreach ($stagiaires as $stagiaire_id) {
            $stmt_insert->bind_param("ii", $stage_id, $stagiaire_id);
            if ($stmt_insert->execute()) {
                error_log("Inserted stagiaire_id: $stagiaire_id for stage_id: $stage_id");
            } else {
                error_log("Failed to insert stagiaire_id: $stagiaire_id for stage_id: $stage_id. Error: " . $stmt_insert->error);
            }
        }

        $stmt_insert->close();

        // Commit transaction
        $conn->commit();
        $response['success'] = true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction failed: " . $e->getMessage());
        $response['error'] = "Transaction failed: " . $e->getMessage();
    }
} else {
    $response['error'] = "Invalid stage ID";
}

// Output JSON response

try {
    echo json_encode($response);
} catch (Exception $e) {
    error_log("JSON encoding failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'JSON encoding failed']);
}
$conn->close();
exit;
