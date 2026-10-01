<?php
// generate_students_xml.php
header('Content-Type: application/xml; charset=utf-8');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 1;

    // 1) Infos de la classe
    $stmt = $conn->prepare("SELECT f.NOM_FILIERE, c.NIVEAU 
                            FROM classes c 
                            JOIN filieres f ON c.ID_FILIERE = f.ID_FILIERE 
                            WHERE c.ID_CLASSE = ?");
    $stmt->execute([$class_id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la classe n'existe pas → on renvoie un XML d'erreur propre
    if (!$class) {
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<erreur>Classe introuvable (ID=' . htmlspecialchars($class_id) . ')</erreur>';
        exit;
    }

    // 2) Étudiants (tous — à filtrer plus tard si vous ajoutez une table d'inscription)
    $stmt = $conn->prepare("SELECT NUM_INSCRIPTION, NOM_ET, PRENOM_ET FROM etudiants");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Modules de la classe
    $stmt = $conn->prepare("SELECT DISTINCT m.ID_MODULE, m.NOM_MODULE 
                            FROM modules m 
                            JOIN cours c ON m.ID_MODULE = c.ID_MODULE 
                            WHERE c.ID_CLASSE = ?");
    $stmt->execute([$class_id]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4) Construction du XML
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><classe/>');
    $xml->addAttribute('filiere', $class['NOM_FILIERE']);
    $xml->addAttribute('niveau',  $class['NIVEAU']);

    $etudiants = $xml->addChild('etudiants');
    foreach ($students as $student) {
        $etudiant = $etudiants->addChild('etudiant');
        $etudiant->addAttribute('numInscription', $student['NUM_INSCRIPTION']);
        $etudiant->addAttribute('nom',            $student['NOM_ET']);
        $etudiant->addAttribute('prenom',         $student['PRENOM_ET']);
    }

    $modules_xml = $xml->addChild('modules');
    foreach ($modules as $module) {
        $mod = $modules_xml->addChild('module');
        $mod->addAttribute('idModule',  $module['ID_MODULE']);
        $mod->addAttribute('nomModule', $module['NOM_MODULE']);
    }

    echo $xml->asXML();

} catch (PDOException $e) {
    // En cas d'erreur BDD, on renvoie un XML d'erreur (pas du HTML)
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<erreur>' . htmlspecialchars($e->getMessage()) . '</erreur>';
}
$conn = null;