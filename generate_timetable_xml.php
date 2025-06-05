<?php
// generate_timetable_xml.php
header('Content-Type: application/xml');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT c.JOUR, c.HEURE_DEBUT, c.HEURE_FIN, p.NOM_PROF, m.NOM_MODULE, s.NOM_SALLE, c.ID_CLASSE, f.NOM_FILIERE, cl.NIVEAU 
                            FROM cours c 
                            JOIN professeurs p ON c.ID_PROF = p.ID_PROF 
                            JOIN modules m ON c.ID_MODULE = m.ID_MODULE 
                            JOIN salles s ON c.ID_SALLE = s.ID_SALLE 
                            JOIN classes cl ON c.ID_CLASSE = cl.ID_CLASSE 
                            JOIN filieres f ON cl.ID_FILIERE = f.ID_FILIERE");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><emploi></emploi>');

    foreach ($courses as $course) {
        $seance = $xml->addChild('seance');
        $seance->addAttribute('jour', $course['JOUR']);
        $seance->addAttribute('debut', $course['HEURE_DEBUT']);
        $seance->addAttribute('fin', $course['HEURE_FIN']);
        $seance->addAttribute('prof', $course['NOM_PROF']);
        $seance->addAttribute('module', $course['NOM_MODULE']);
        $seance->addAttribute('salle', $course['NOM_SALLE']);
        $seance->addAttribute('class_id', $course['ID_CLASSE']);
        $seance->addAttribute('filiere', $course['NOM_FILIERE']); // Add filiere
        $seance->addAttribute('niveau', $course['NIVEAU']); // Add niveau
    }

    echo $xml->asXML();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>