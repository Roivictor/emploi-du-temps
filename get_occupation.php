<?php
// get_occupation.php — renvoie l'occupation pour construire la grille
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

$jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ===== Récupération de tous les cours =====
    $stmt = $conn->prepare("
        SELECT 
            c.JOUR,
            c.HEURE_DEBUT,
            c.HEURE_FIN,
            c.ID_SALLE,
            s.NOM_SALLE,
            c.ID_CLASSE,
            c.ID_PROF,
            p.NOM_PROF,
            m.NOM_MODULE
        FROM cours c
        JOIN salles s 
            ON c.ID_SALLE = s.ID_SALLE
        JOIN classes cl 
            ON c.ID_CLASSE = cl.ID_CLASSE
        JOIN professeurs p 
            ON c.ID_PROF = p.ID_PROF
        JOIN modules m 
            ON c.ID_MODULE = m.ID_MODULE
        ORDER BY 
            FIELD(LOWER(c.JOUR), 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'),
            c.HEURE_DEBUT
    ");

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== Initialisation de la matrice =====
    $occupation = [];

    foreach ($jours as $j) {
        $occupation[$j] = [
            'salles'  => [],
            'classes' => []
        ];
    }

    // ===== Liste des professeurs =====
    $profs = [];

    // ===== Créneaux réellement utilisés =====
    $creneauxSet = [];

    foreach ($rows as $r) {

        $jour = strtolower(trim($r['JOUR']));

        if (!isset($occupation[$jour])) {
            continue;
        }

        // Mémoriser le professeur
        $profs[$r['ID_PROF']] = $r['NOM_PROF'];

        // Créneau réel du cours
        $debut = substr($r['HEURE_DEBUT'], 0, 5);
        $fin   = substr($r['HEURE_FIN'], 0, 5);

        $creneau = $debut . '-' . $fin;

        // Ajouter le créneau à la liste
        $creneauxSet[$creneau] = true;

        // Informations du cours
        $info = [
            'module'  => $r['NOM_MODULE'],
            'prof'    => $r['NOM_PROF'],
            'prof_id' => $r['ID_PROF'],
            'heure_debut' => $debut,
            'heure_fin'   => $fin
        ];

        // ===== Occupation salle =====
        $occupation[$jour]['salles'][$r['NOM_SALLE']][$creneau] = $info;

        // ===== Occupation classe =====
        $occupation[$jour]['classes'][$r['ID_CLASSE']][$creneau] = $info;
    }

    // ===== Trier les créneaux par heure de début =====
    $creneaux = array_keys($creneauxSet);

    usort($creneaux, function ($a, $b) {

        $debutA = substr($a, 0, 5);
        $debutB = substr($b, 0, 5);

        return strcmp($debutA, $debutB);
    });

    // ===== Réponse JSON =====
    echo json_encode([
        'status'   => 'success',
        'jours'    => $jours,
        'creneaux' => $creneaux,
        'profs'    => $profs,
        'data'     => $occupation
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn = null;