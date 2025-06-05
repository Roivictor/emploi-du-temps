<?php
// generate_students_xml.php
header('Content-Type: application/xml');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 1;
    $stmt = $conn->prepare("SELECT f.NOM_FILIERE, c.NIVEAU FROM classes c JOIN filieres f ON c.ID_FILIERE = f.ID_FILIERE WHERE c.ID_CLASSE = ?");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT NUM_INSCRIPTION, NOM_ET, PRENOM_ET FROM etudiants");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT DISTINCT m.ID_MODULE, m.NOM_MODULE 
                            FROM modules m 
                            JOIN cours c ON m.ID_MODULE = c.ID_MODULE 
                            WHERE c.ID_CLASSE = ?");
    $stmt->execute([$class_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $xml = new SimpleXMLElement('<classe/>');
    $xml->addAttribute('filiere', $class['NOM_FILIERE']);
    $xml->addAttribute('niveau', $class['NIVEAU']);

    $etudiants = $xml->addChild('etudiants');
    foreach ($students as $student) {
        $etudiant = $etudiants->addChild('etudiant');
        $etudiant->addAttribute('numInscription', $student['NUM_INSCRIPTION']);
        $etudiant->addAttribute('nom', $student['NOM_ET']);
        $etudiant->addAttribute('prenom', $student['PRENOM_ET']);
    }

    $modules_xml = $xml->addChild('modules');
    foreach ($modules as $module) {
        $mod = $modules_xml->addChild('module');
        $mod->addAttribute('idModule', $module['ID_MODULE']);
        $mod->addAttribute('nomModule', $module['NOM_MODULE']);
    }

    echo $xml->asXML();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>