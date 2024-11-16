<?php
include('db_connection.php');

// Check if stage_id is passed via POST
if (isset($_POST['stage_id'])) {
    $stage_id = $_POST['stage_id'];

    // First, delete the corresponding rows from stage_stagiaire table
    $sql_delete_stage_stagiaire = "DELETE FROM stage_stagiaire WHERE stage_id = ?";
    $stmt = $conn->prepare($sql_delete_stage_stagiaire);
    $stmt->bind_param("i", $stage_id); // "i" stands for integer
    $stmt->execute();

    // Then, delete the stage from the stages table
    $sql_delete_stage = "DELETE FROM stages WHERE id = ?";
    $stmt = $conn->prepare($sql_delete_stage);
    $stmt->bind_param("i", $stage_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Return success response
        echo "success";
    } else {
        // Return error if deletion failed
        echo "error";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo "error";
}
