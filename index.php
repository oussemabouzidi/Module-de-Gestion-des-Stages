<?php include('db_connection.php'); ?>
<?php include('data.php'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Gestion des Stages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-column sidebar">
        <div class="container-fluid flex-column">
            <a class="navbar-brand" href="#">
                <pre>Gestion des 
Stages</pre>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="#">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link scroll-link" href="#section1">Stages</a></li>
                    <li class="nav-item"><a href="#section2" class="nav-link scroll-link">Statistiques</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Enseignants</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Etudiants</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Paramètres</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Déconnexion</a></li>
                </ul>
            </div>

        </div>
    </nav>

    <div id="section1">
        <div class="container my-5">
            <h1 class="text-center">Tableau de bord de gestion des stages</h1>

            <div class="row mt-5">
                <div class="col-md-12">
                    <h2>Liste des Stages</h2>
                    <form>
                        <div class="search">
                            <input type="text" id="searchStages" class="form-control" placeholder="Rechercher par étudiant" oninput="searchAll()">
                            <select name="searchTypeStage" id="search_type" oninput="searchAll()">
                                <option value="" selected disabled>Type</option>
                                <option value="initiation">Initiation</option>
                                <option value="perfectionnement">perfectionnement</option>
                                <option value="pfe">PFE</option>
                            </select>
                            <select name='encadrent_id' id='encadrent' oninput="searchAll()">
                                <option value='' selected disabled> Encadrant </option>
                                <?php foreach ($encadrants as $encadrant): ?>
                                    <option value="<?= $encadrant['nom'] ?>">
                                        <?= $encadrant['nom'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <input type="date" class="form-control" id="dateSoutenance_id" name="dateSoutenance" oninput="searchAll()" required>

                            <select name='jury' id='jury_id' oninput="searchAll()">
                                <option value='' selected disabled> Select jury </option>
                                <?php foreach ($juries as $jury):
                                    $result2 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury1_id']);
                                    $enseignant1 = $result2->fetch_assoc();
                                    $result3 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury2_id']);
                                    $enseignant2 = $result3->fetch_assoc();
                                ?>
                                    <option value='<?= $jury['id'] ?>'>
                                        <?= $enseignant1['nom'] . " " . $enseignant1['prenom'] ?> & <?= $enseignant2['nom'] . " " . $enseignant2['prenom'] ?> || nombre de stage: <?= $jury['nbr_of_stage_actuelle'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-success" id="resetButton" type="button">Annuler</button>
                        </div>
                    </form>
                    <hr>
                    <table id='data-table' class="table table-striped">
                        <thead>
                            <tr>
                                <th>Étudiant(s)</th>
                                <th>Sujet</th>
                                <th>Date de soutenance</th>
                                <th>Encadrant</th>
                                <th>Jury id</th>
                                <th>Intitulé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stages = $conn->query("SELECT * FROM stages");

                            while ($stage_info = $stages->fetch_assoc()) {

                                // Get stagiaires for the current stage
                                $stagiaires = $conn->query("SELECT s.nom, s.prenom FROM stagiaires s 
                                JOIN stage_stagiaire ss ON s.id = ss.stagiaire_id
                                WHERE ss.stage_id = " . $stage_info['id']);
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
                            ?>
                                <tr>
                                    <!-- Show concatenated stagiaire names in one column -->
                                    <td><?= implode(", ", $stagiaires_names) ?></td>
                                    <td><?= $stage_info['type'] ?></td>
                                    <td><?= $stage_info['date_soutenance'] ?></td>
                                    <td><?= $encadrant_name ?></td>
                                    <td><?= $stage_info['id_jery'] ?></td>
                                    <td><?= $stage_info['intitule'] ?></td>
                                    <td><button class='btn btn-warning edit-stage' data-id='<?= $stage_info['id'] ?>'><i class='fas fa-edit'></i> Modifier</button></td>
                                    <td><button class='btn btn-danger drop-stage' data-id='<?= $stage_info['id'] ?>'><i class='fas fa-trash'></i> Supprimer</button></td>
                                    <td>
                                        <button class='btn btn-info termine_btn'
                                            data-id='<?= $stage_info['id'] ?>'
                                            data-is-termine='<?= $stage_info['termine'] ?>'>
                                            <i class='fas fa-check'></i> <?= $stage_info['termine'] == 1 ? 'Terminé' : 'Non Terminé' ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>



                </div>
            </div>

            <form action="pdf.php" method="POST">
                <div class=" text-center mb-4 btn_ajouter">
                    <button type="button" class="btn btn-white" data-bs-toggle='modal' data-bs-target='#addStageModal'>
                        Ajouter un Stage
                    </button>
                </div>
                <div class=" text-center mb-4 btn_pdf">
                    <button type="submit" class="btn btn-white">
                        Exporter en PDF
                    </button>
                </div>
            </form>
        </div>

        <div id="section2">
            <div class="row mt-5">
                <div class="col-md-12">
                    <h2>Liste des Enseignants</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Département</th>
                                <th>Nombre de stages</th>
                                <th>Statut</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($encadrants as $encadrant): ?>
                                <tr>
                                    <td><?= $encadrant['nom'] . " " . $encadrant['prenom'] ?></td>
                                    <td>Informatique</td>
                                    <td><?= $encadrant['nbr_stage_associer_actuelle'] ?></td>
                                    <td>Encadrement</td>
                                    <td><?= $encadrant['email'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php
                            include('db_connection.php');
                            foreach ($enseignants as $enseignant):
                                $query = "SELECT nbr_of_stage_actuelle FROM juries WHERE jury1_id = " . $enseignant['id'] . " OR jury2_id = " . $enseignant['id'];
                                $result = $conn->query($query);
                                $nbr_stage = 0;

                                while ($row = $result->fetch_assoc()) {
                                    $nbr_stage += $row['nbr_of_stage_actuelle'];
                                }
                            ?>
                                <tr>
                                    <td><?= $enseignant['nom'] . " " . $enseignant['prenom'] ?></td>
                                    <td>Informatique</td>
                                    <td><?= $nbr_stage ?></td>
                                    <td>Enseignement</td>
                                    <td><?= $enseignant['email'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <?php
                            include('db_connection.php');

                            $query = "SELECT count(*) FROM stages";
                            $result = $conn->query($query);
                            $nbr_stages = $result->fetch_row()[0];

                            $q2 = "SELECT count(*) FROM stages WHERE termine = 1";
                            $r2 = $conn->query($q2);
                            $nbr_stage_non_termine = $r2->fetch_row()[0];

                            $q3 = "SELECT count(*) FROM stages WHERE termine = 0";
                            $r3 = $conn->query($q3);
                            $nbr_stage_termine = $r3->fetch_row()[0];

                            $q4 = "SELECT count(*) FROM enseignants";
                            $r4 = $conn->query($q4);
                            $nbr_enseignants = $r4->fetch_row()[0];

                            $q5 = "SELECT COUNT(*) FROM encadrants";
                            $r5 = $conn->query($q5);
                            $nbr_encadrants = $r5->fetch_row()[0];

                            $q6 = "SELECT count(*) from juries";
                            $r6 = $conn->query($q6);
                            $nombre_juries = $r6->fetch_row()[0];
                            ?>

                            <h5 class="card-title">Résumé des Stages</h5>
                            <p class="card-text">Nombre total de stages : <?= $nbr_stages ?></p>
                            <p class="card-text">Stages en cours : <?= $nbr_stage_non_termine ?></p>
                            <p class="card-text">Stages terminés : <?= $nbr_stage_termine ?></p>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Résumé des Enseignants / Encadrants</h5>
                            <p class="card-text">Nombre total d'enseignants : <?= $nbr_enseignants ?></p>
                            <p class="card-text">Nombre total d'encadrants : <?= $nbr_encadrants ?></p>
                            <p class="card-text">Nombre des juries : <?= $nombre_juries ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <div class="modal fade" id="addStageModal" tabindex="-1" aria-labelledby="addStageModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStageModalLabel">Ajouter un Stage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="stageForm" method="POST" action="add_stage.php">
                            <div class="mb-3">
                                <label for="stageType" class="form-label">Type de Stage</label>
                                <select name="type" id="type_id">
                                    <option value="" selected disabled>Select type</option>
                                    <option value="initiation">initiation</option>
                                    <option value="perfectionnement">perfectionnement</option>
                                    <option value="PFE">PFE</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="encadrantId" class="form-label">Encadrant</label>
                                <?php
                                $req = "select * from encadrants ;";
                                $encadrents = $conn->query($req);
                                echo "<select name='encadrent_id' id='encadrent_id'>";
                                echo "<option value='' selected disabled> Select option </option> ";
                                foreach ($encadrents as $encadrent) {
                                    echo "<option value='" . $encadrent['id'] . "'>" . $encadrent['nom'] . " || nombre de stage " . $encadrent['nbr_stage_associer_actuelle'] . "</option>";
                                }
                                echo "</select>";
                                ?>
                            </div>
                            <div class="mb-3">
                                <label for="intitule" class="form-label">Intitulé de Stage</label>
                                <input type="text" name="intitule" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="stagiaires" class="form-label">Stagiaire(s)</label>
                                <?php
                                $req = "select * from stagiaires;";
                                $stagiaires = $conn->query($req);
                                echo "<select name='stagiaires[]' id='list_stagiaires' multiple class='form-control'>";
                                echo "<option value='' selected disabled> Select stagiaire </option> ";
                                foreach ($stagiaires as $stagiaire) {
                                    echo "<option value=" . $stagiaire['id'] . ">" . $stagiaire['nom'] . " " . $stagiaire['prenom'] . "</option>";
                                }
                                ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dateSoutenance" class="form-label">Date de Soutenance</label>
                                <input type="date" class="form-control" id="dateSoutenance" name="dateSoutenance" required>
                            </div>
                            <div class="mb-3">
                                <label for="juryId" class="form-label">Jury</label>
                                <select name='jury' id='jury_id' onchange="searchJury()">
                                    <option value='' selected disabled> Select jury </option>
                                    <?php foreach ($juries as $jury):
                                        $result2 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury1_id']);
                                        $enseignant1 = $result2->fetch_assoc();
                                        $result3 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury2_id']);
                                        $enseignant2 = $result3->fetch_assoc();
                                    ?>
                                        <option value='<?= $jury['id'] ?>'>
                                            <?= $enseignant1['nom'] . " " . $enseignant1['prenom'] ?> & <?= $enseignant2['nom'] . " " . $enseignant2['prenom'] ?> || nombre de stage: <?= $jury['nbr_of_stage_actuelle'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" form="stageForm" class="btn btn-primary">Ajouter le Stage</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editStageModal" tabindex="-1" aria-labelledby="editStageModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStageModalLabel">Modifier le Stage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editStageForm">
                            <input type="hidden" id="editStageId">
                            <div class="mb-3">
                                <label for="editStageType" class="form-label">Type de Stage</label>
                                <select id="editStageType" class="form-select" name="type">
                                    <option value="initiation">initiation</option>
                                    <option value="perfectionnement">perfectionnement</option>
                                    <option value="PFE">PFE</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editEncadrantId" class="form-label">Encadrant</label>
                                <select id="editEncadrantId" class="form-select" name="encadrant">
                                    <?php foreach ($encadrents as $encadrent): ?>
                                        <option value="<?= $encadrent['id'] ?>"><?= $encadrent['nom'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="intitule" class="form-label">Intitulé de Stage</label>
                                <input type="text" class="form-control" name="intitule" id="intitule_id">
                            </div>
                            <div class="mb-3">
                                <label for="editStagiaires" class="form-label">Stagiaire(s)</label>
                                <select id="editStagiaires" name="stagiaires[]" class="form-select" multiple>
                                    <?php foreach ($stagiaires as $stagiaire): ?>
                                        <option value="<?= $stagiaire['id'] ?>">
                                            <?= $stagiaire['nom'] . " " . $stagiaire['prenom'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="editDateSoutenance" class="form-label">Date de Soutenance</label>
                                <input type="date" name="date" class="form-control" id="editDateSoutenance">
                            </div>
                            <div class="mb-3">
                                <label for="editJuryId" class="form-label">Jury</label>
                                <select id="editJuryId" name="jury" onchange="searchJury()">
                                    <option value="" selected disabled>Select jury</option>
                                    <?php foreach ($juries as $jury): ?>
                                        <?php
                                        $result2 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury1_id']);
                                        $enseignant1 = $result2->fetch_assoc();
                                        $result3 = $conn->query("SELECT * FROM enseignants WHERE id = " . $jury['jury2_id']);
                                        $enseignant2 = $result3->fetch_assoc();
                                        ?>
                                        <option value="<?= $jury['id'] ?>">
                                            <?= $enseignant1['nom'] . " " . $enseignant1['prenom'] ?> & <?= $enseignant2['nom'] . " " . $enseignant2['prenom'] ?> || nombre de stage: <?= $jury['nbr_of_stage_actuelle'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" onclick="saveStageEdits()">Sauvegarder les modifications</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="app.js"></script>
</body>

</html>