CREATE TABLE stages (
    id INT PRIMARY KEY,
    type VARCHAR(50),
    date_soutenance DATE,
    id_encadrent INT,
    id_jury INT,
    FOREIGN KEY (id_encadrent) REFERENCES encadrants(id),
    FOREIGN KEY (id_jury) REFERENCES juries(id)
);

CREATE TABLE juries (
    id INT PRIMARY KEY,
    jury1_id INT,
    jury2_id INT,
    nbr_stage_actuelle INT,
    FOREIGN KEY (jury1_id) REFERENCES enseignants(id),
    FOREIGN KEY (jury2_id) REFERENCES enseignants(id)
);

CREATE TABLE enseignants (
    id INT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100)
);

CREATE TABLE encadrants (
    id INT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100),
    nbr_stage_associer INT
);

CREATE TABLE stagiaires (
    id INT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100)
);

CREATE TABLE stage_stagiaire (
    stage_id INT,
    stagiaire_id INT,
    PRIMARY KEY (stage_id, stagiaire_id),
    FOREIGN KEY (stage_id) REFERENCES stages(id),
    FOREIGN KEY (stagiaire_id) REFERENCES stagiaires(id)
);