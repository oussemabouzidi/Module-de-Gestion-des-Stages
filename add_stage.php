<?php
include('db_connection.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$type_stage = isset($_POST['type']) ? $_POST['type'] : '';
$intitule = isset($_POST['intitule']) ? $_POST['intitule'] : '';
$encadrant_id = isset($_POST['encadrent_id']) ? $_POST['encadrent_id'] : 'N/A';
$stagiaires = isset($_POST['stagiaires']) ? $_POST['stagiaires'] : [];
$date_soutenance = isset($_POST['dateSoutenance']) ? $_POST['dateSoutenance'] : 'N/A';
$jury = isset($_POST['jury']) ? $_POST['jury'] : 'N/A';

// Insert into 'stages' table
$sql = "INSERT INTO stages (type, intitule, id_encadrant, date_soutenance, id_jery) 
        VALUES ('$type_stage', '$intitule', '$encadrant_id', '$date_soutenance', '$jury')";

if ($conn->query($sql) === TRUE) {
        // Get the last inserted stage_id
        $stage_id = $conn->insert_id;

        // Update the number of stages for encadrant
        if ($encadrant_id !== 'N/A') {
                $update_encadrant_sql = "UPDATE encadrants SET nbr_stage_associer_actuelle = nbr_stage_associer_actuelle + 1 WHERE id = '$encadrant_id'";
                $conn->query($update_encadrant_sql);
        }

        // Update the number of stages for jury
        if ($jury !== 'N/A') {
                $update_jury_sql = "UPDATE juries SET nbr_of_stage_actuelle = nbr_of_stage_actuelle + 1 WHERE id = '$jury'";
                $conn->query($update_jury_sql);
        }

        // Insert into 'stage_stagiaire' table and retrieve emails
        foreach ($stagiaires as $stagiaire_id) {
                $sql = "INSERT INTO stage_stagiaire (stage_id, stagiaire_id) 
                VALUES ('$stage_id', '$stagiaire_id')";
                $conn->query($sql);

                // Get the email of each stagiaire
                $email_sql = "SELECT email FROM stagiaires WHERE id = '$stagiaire_id'";
                $result = $conn->query($email_sql);

                if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $email = $row['email'];

                        // Initialize PHPMailer
                        $mail = new PHPMailer(true);

                        try {
                                // SMTP server configuration
                                $mail->isSMTP();
                                $mail->Host = 'smtp.gmail.com';
                                $mail->SMTPAuth = true;
                                $mail->Username = 'bouzidioussema16@gmail.com';       // Your Gmail address
                                $mail->Password =
                                        'dpah sbwl prit ganv
';               // Your Gmail app password
                                $mail->SMTPSecure = "tls";
                                $mail->Port = 587;

                                // Sender and subject
                                $mail->setFrom('bouzidioussema16@gmail.com', 'Admin');
                                $mail->Subject = "Informations sur le stage et date de soutenance";

                                // Load the email template and replace placeholders
                                $template = file_get_contents('email_template.html');
                                $template = str_replace('{{type_stage}}', $type_stage, $template);
                                $template = str_replace('{{date_soutenance}}', $date_soutenance, $template);
                                $template = str_replace('{{jury}}', $jury, $template);
                                $template = str_replace('{{encadrant_id}}', $encadrant_id, $template);

                                // HTML email content
                                $mail->isHTML(true);
                                $mail->Body = $template;

                                // Send email to the current stagiaire
                                $mail->addAddress($email);

                                // Attempt to send the email
                                if ($mail->send()) {
                                        echo "<script>alert('Email successfully sent to $email');</script>";
                                } else {
                                        echo "<script>alert('Error: Failed to send email to $email');</script>";
                                }

                                // Clear all addresses for the next iteration
                                $mail->clearAddresses();
                        } catch (Exception $e) {
                                echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
                        }
                }
        }

        // JavaScript alert and reload the page
        echo "<script>
        alert('Stage et stagiaires ajoutés avec succès!');
        window.location.href = 'index.php';
      </script>";
} else {
        echo "<script>
        alert('Erreur : " . addslashes($conn->error) . "');
        window.location.href = 'index.php';
      </script>";
}

$conn->close();
