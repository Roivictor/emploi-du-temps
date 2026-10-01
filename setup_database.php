<?php
// setup_database.php — version corrigée et complète
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

try {
    // 1) Connexion SANS base
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2) Repartir propre
    $conn->exec("DROP DATABASE IF EXISTS $dbname");
    $conn->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $conn->exec("USE $dbname");

    // 3) Création des tables
    $conn->exec("CREATE TABLE filieres (
        ID_FILIERE INT(11) PRIMARY KEY,
        SIGLE VARCHAR(10) UNIQUE,
        CYCLE VARCHAR(20),
        NOM_FILIERE VARCHAR(100),
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE salles (
        ID_SALLE INT(11) PRIMARY KEY,
        NOM_SALLE VARCHAR(50) UNIQUE,
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE modules (
        ID_MODULE INT(11) PRIMARY KEY,
        NOM_MODULE VARCHAR(50) UNIQUE,
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE professeurs (
        ID_PROF INT(11) PRIMARY KEY,
        NOM_PROF VARCHAR(50) UNIQUE,
        TEL VARCHAR(20)
    )");

    $conn->exec("CREATE TABLE etudiants (
        NUM_INSCRIPTION VARCHAR(15) PRIMARY KEY,
        NOM_ET VARCHAR(50),
        PRENOM_ET VARCHAR(50),
        ADRESSE VARCHAR(100)
    )");

    $conn->exec("CREATE TABLE classes (
        ID_CLASSE INT(11) PRIMARY KEY,
        CODE_CLASSE VARCHAR(20) UNIQUE,
        ID_FILIERE INT(11) NULL,
        NIVEAU INT(11),
        CYCLE VARCHAR(20),
        FOREIGN KEY (ID_FILIERE) REFERENCES filieres(ID_FILIERE)
    )");

    $conn->exec("CREATE TABLE cours (
        ID_COURS INT(11) AUTO_INCREMENT PRIMARY KEY,
        ID_CLASSE INT(11),
        ID_PROF INT(11),
        ID_SALLE INT(11),
        ID_MODULE INT(11),
        JOUR VARCHAR(12),
        HEURE_DEBUT TIME,
        HEURE_FIN TIME,
        FOREIGN KEY (ID_CLASSE) REFERENCES classes(ID_CLASSE),
        FOREIGN KEY (ID_PROF)   REFERENCES professeurs(ID_PROF),
        FOREIGN KEY (ID_SALLE)  REFERENCES salles(ID_SALLE),
        FOREIGN KEY (ID_MODULE) REFERENCES modules(ID_MODULE)
    )");

    // ==================== DONNÉES D'EXEMPLE ====================

    // --- Filières lycée ---
    $conn->exec("INSERT INTO filieres (ID_FILIERE, SIGLE, CYCLE, NOM_FILIERE, DESCRIPTION) VALUES
        (10, 'A4', 'lycee', 'Littéraire A4', 'Série littéraire'),
        (11, 'D',  'lycee', 'Scientifique D', 'Maths + SVT'),
        (12, 'G1', 'lycee', 'Gestion G1', 'Sciences de gestion'),
        (13, 'F',  'lycee', 'Technique F', 'Série technique')
    ");

    // --- Filières université (licence) ---
    $conn->exec("INSERT INTO filieres (ID_FILIERE, SIGLE, CYCLE, NOM_FILIERE, DESCRIPTION) VALUES
        (20, 'SRI', 'licence', 'Systèmes et Réseaux Informatiques', 'Réseaux et Systèmes'),
        (21, 'AL',  'licence', 'Anglais', 'Langue anglaise'),
        (22, 'SRS', 'licence', 'Sciences Réseaux et Sécurité', 'Cybersécurité')
    ");

    // --- Salles ---
    $conn->exec("INSERT INTO salles (ID_SALLE, NOM_SALLE, DESCRIPTION) VALUES
        (1, 'lab4',    'Lab 4'),
        (2, 'londres', 'Salle Londres'),
        (3, 'amphi A', 'Grand amphithéâtre')
    ");

    // --- Modules ---
    $conn->exec("INSERT INTO modules (ID_MODULE, NOM_MODULE, DESCRIPTION) VALUES
        (1, 'Java', 'Programmation Java'),
        (2, 'Math', 'Mathématiques'),
        (3, 'C++',  'Programmation C++'),
        (4, 'Anglais', 'Langue anglaise'),
        (5, 'Réseaux', 'Administration réseau')
    ");

    // --- Professeurs ---
    $conn->exec("INSERT INTO professeurs (ID_PROF, NOM_PROF, TEL) VALUES
        (1, 'Prof A', '123456789'),
        (2, 'Prof B', '987654321'),
        (3, 'Prof C', '111222333')
    ");

    // --- Étudiants ---
    $conn->exec("INSERT INTO etudiants (NUM_INSCRIPTION, NOM_ET, PRENOM_ET, ADRESSE) VALUES
        ('E200', 'Smith', 'John', '123 Rue Exemple')
    ");

    // --- Classes : exemple pour chaque cycle ---
    // Primaire : pas de filière (ID_FILIERE = NULL)
    // Collège  : pas de filière
    // Lycée    : filière D (ID 11)
    // Licence  : filière SRI (ID 20)
    $conn->exec("INSERT INTO classes (ID_CLASSE, CODE_CLASSE, ID_FILIERE, NIVEAU, CYCLE) VALUES
        (1, 'CP1',  NULL, 1, 'primaire'),
        (2, '6EME', NULL, 1, 'college'),
        (3, '2NDD', 11,   1, 'lycee'),
        (4, 'SRI1', 20,   1, 'licence'),
        (5, 'SRI3', 20,   3, 'licence')
    ");

    // --- Cours (2 séances pour SRI3 = classe 5) ---
    // ⚠️ On utilise ID_CLASSE = 5 (SRI3) et ID_SALLE = 2 (londres) pour cohérence
    $conn->exec("INSERT INTO cours (ID_CLASSE, ID_PROF, ID_SALLE, ID_MODULE, JOUR, HEURE_DEBUT, HEURE_FIN) VALUES
        (5, 1, 1, 1, 'lundi', '08:30:00', '10:00:00'),
        (5, 2, 2, 2, 'lundi', '10:15:00', '11:45:00')
    ");

    echo "<h2>✅ Base '$dbname' recréée avec succès !</h2>";
    echo "<ul>";
    echo "<li>Tables : filieres, salles, modules, professeurs, etudiants, classes, cours</li>";
    echo "<li>Filières lycée : A4, D, G1, F</li>";
    echo "<li>Filières licence : SRI, AL, SRS</li>";
    echo "<li>5 classes : CP1, 6EME, 2NDD, SRI1, SRI3</li>";
    echo "<li>3 professeurs, 3 salles, 5 modules</li>";
    echo "<li>2 cours d'exemple pour SRI3</li>";
    echo "</ul>";
    echo "<p><a href='dashboard.php'>→ Aller au dashboard</a></p>";
    echo "<p><a href='manage_all.php'>→ Gérer filières, classes, modules, profs, salles</a></p>";

} catch (PDOException $e) {
    echo "<h2>❌ Erreur</h2><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
$conn = null;