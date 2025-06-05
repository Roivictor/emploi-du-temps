<?php
// setup_database.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");

    // Create tables
    $conn->exec("CREATE TABLE IF NOT EXISTS etudiants (
        NUM_INSCRIPTION VARCHAR(15) PRIMARY KEY,
        NOM_ET VARCHAR(25),
        PRENOM_ET VARCHAR(25),
        ADRESSE VARCHAR(70)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS filieres (
        ID_FILIERE INT(11) PRIMARY KEY,
        NOM_FILIERE VARCHAR(15),
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS salles (
        ID_SALLE INT(11) PRIMARY KEY,
        NOM_SALLE VARCHAR(25),
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS classes (
        ID_CLASSE INT(11) PRIMARY KEY,
        ID_FILIERE INT(11),
        NIVEAU INT(11),
        FOREIGN KEY (ID_FILIERE) REFERENCES filieres(ID_FILIERE)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS modules (
        ID_MODULE INT(11) PRIMARY KEY,
        NOM_MODULE VARCHAR(25),
        DESCRIPTION TEXT
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS professeurs (
        ID_PROF INT(11) PRIMARY KEY,
        NOM_PROF VARCHAR(25),
        TEL VARCHAR(12)
    )");

    $conn->exec("CREATE TABLE IF NOT EXISTS cours (
        ID_COURS INT(11) AUTO_INCREMENT PRIMARY KEY,
        ID_CLASSE INT(11),
        ID_PROF INT(11),
        ID_SALLE INT(11),
        ID_MODULE INT(11),
        JOUR VARCHAR(12),
        HEURE_DEBUT TIME,
        HEURE_FIN TIME,
        FOREIGN KEY (ID_CLASSE) REFERENCES classes(ID_CLASSE),
        FOREIGN KEY (ID_PROF) REFERENCES professeurs(ID_PROF),
        FOREIGN KEY (ID_SALLE) REFERENCES salles(ID_SALLE),
        FOREIGN KEY (ID_MODULE) REFERENCES modules(ID_MODULE)
    )");

    // Insert sample data
    $conn->exec("INSERT INTO filieres (ID_FILIERE, NOM_FILIERE, DESCRIPTION) VALUES 
        (1, 'SRI', 'Sciences et Réseaux Informatiques')");
    $conn->exec("INSERT INTO salles (ID_SALLE, NOM_SALLE, DESCRIPTION) VALUES 
        (1, 'lab4', 'Lab 4'), (2, 'londres', 'Salle Londres')");
    $conn->exec("INSERT INTO classes (ID_CLASSE, ID_FILIERE, NIVEAU) VALUES 
        (1, 1, 3, 5)");
    $conn->exec("INSERT INTO modules (ID_MODULE, NOM_MODULE, DESCRIPTION) VALUES 
        (1, 'Java', 'Programmation Java'), (2, 'Math', 'Mathématiques'), (3, 'C++', 'Programmation c++')");
    $conn->exec("INSERT INTO professeurs (ID_PROF, NOM_PROF, TEL) VALUES 
        (1, 'Prof A', '123456789'), (2, 'Prof B', '987654321')");
    $conn->exec("INSERT INTO etudiants (NUM_INSCRIPTION, NOM_ET, PRENOM_ET, ADRESSE) VALUES 
        ('E200', 'Smith', 'John', '123 Rue Exemple')");
    $conn->exec("INSERT INTO cours (ID_CLASSE, ID_PROF, ID_SALLE, ID_MODULE, JOUR, HEURE_DEBUT, HEURE_FIN) VALUES 
        (1, 1, 1, 1, 'lundi', '08:30:00', '10:00:00'),
        (1, 2, 2, 2, 'lundi', '10:15:00', '11:45:00')");

    echo "Database and tables created successfully with sample data.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>