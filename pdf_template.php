<!DOCTYPE html>
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

        /* Header Section */
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

        .header-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-logo img {
            width: 120px;
            height: auto;
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

        /* Footer Section */
        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #003366;
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
    <div class="header-logo">
        <img src="src/img/Logo_ISET_Kélibia.jpg" alt="ISET Kélibia Logo">
    </div>

    <div class="section">
        <h3>Ministère de l'Enseignement Supérieur et de la Recherche Scientifique</h3>
        <h4>Direction Général des Études Technologiques</h4>
        <h3>Institu Supérieur des Études Technologiques de Kélibia</h3>
        <h4>Département Technologies de l'Informatique</h4>
    </div>

    <h1>Soutenances des stages</h1>

    <div class="section">
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
            <tbody>
                <?php
                // Fetch stages from database
                $stages = $conn->query("SELECT * FROM stages");

                if (!$stages) {
                    die("Error retrieving stages: " . $conn->error);
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

                        // Handle encadrant name
                        $encadrant_name = 'None';
                        if (!empty($stage_info['id_encadrant'])) {
                            $encadrant = $conn->query("SELECT nom FROM encadrants WHERE id = " . $stage_info['id_encadrant']);
                            if ($encadrant && $encadrant_row = $encadrant->fetch_assoc()) {
                                $encadrant_name = $encadrant_row['nom'];
                            }
                        }

                        // Display the row for the stage
                        echo "
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
                    echo "<tr><td colspan='6'>No stages found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>