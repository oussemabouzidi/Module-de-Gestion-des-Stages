<?php
include('db_connection.php');

if (isset($_POST['stage_id']) && isset($_POST['is_termine'])) {
    $stage_id = (int)$_POST['stage_id'];
    $is_termine = (int)$_POST['is_termine'];

    $sql_update_termine = "UPDATE stages SET termine = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update_termine);

    if ($stmt) {
        $stmt->bind_param("ii", $is_termine, $stage_id);
        $stmt->execute();

        echo ($stmt->affected_rows > 0) ? "success" : "No rows updated";

        $stmt->close();
    } else {
        echo "Failed to prepare statement";
    }

    $conn->close();
} else {
    echo "Missing parameters";
}
