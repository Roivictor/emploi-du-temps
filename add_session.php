<?php
// add_session.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $class_id = $_POST['class_id'];
        $prof_id = $_POST['prof_id'];
        $salle_id = $_POST['salle_id'];
        $module_id = $_POST['module_id'];
        $jour = $_POST['jour'];
        $heure_debut = $_POST['heure_debut'];
        $heure_fin = $_POST['heure_fin'];

        // Vérifier les conflits (professeur, salle, classe)
        $sql = "SELECT * FROM cours 
                WHERE JOUR = ? AND (
                    (ID_PROF = ? OR ID_SALLE = ? OR ID_CLASSE = ?) AND 
                    (
                        (HEURE_DEBUT < ? AND HEURE_FIN > ?) OR
                        (HEURE_DEBUT < ? AND HEURE_FIN > ?) OR
                        (HEURE_DEBUT >= ? AND HEURE_FIN <= ?)
                    )
                )";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $jour, $prof_id, $salle_id, $class_id,
            $heure_fin, $heure_fin,
            $heure_debut, $heure_debut,
            $heure_debut, $heure_fin
        ]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Conflit détecté avec un professeur, une salle ou une classe.']);
            exit;
        }

        // Insertion
        $stmt = $conn->prepare("INSERT INTO cours (ID_CLASSE, ID_PROF, ID_SALLE, ID_MODULE, JOUR, HEURE_DEBUT, HEURE_FIN) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$class_id, $prof_id, $salle_id, $module_id, $jour, $heure_debut, $heure_fin]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // Récupération des données pour formulaire
    $filieres = $conn->query("SELECT ID_FILIERE, NOM_FILIERE FROM filieres")->fetchAll(PDO::FETCH_ASSOC);
    $profs = $conn->query("SELECT ID_PROF, NOM_PROF FROM professeurs")->fetchAll(PDO::FETCH_ASSOC);
    $salles = $conn->query("SELECT ID_SALLE, NOM_SALLE FROM salles")->fetchAll(PDO::FETCH_ASSOC);
    $modules = $conn->query("SELECT ID_MODULE, NOM_MODULE FROM modules")->fetchAll(PDO::FETCH_ASSOC);
    $classes = $conn->query("SELECT ID_CLASSE, ID_FILIERE, NIVEAU FROM classes")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Séance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Ajouter une Nouvelle Séance</h2>
        <form id="sessionForm">
            <div class="mb-3">
                <label for="filiere_id">Filière:</label>
                <select name="filiere_id" id="filiere_id" class="form-select" onchange="updateClasses()">
                    <?php foreach ($filieres as $filiere): ?>
                        <option value="<?= $filiere['ID_FILIERE'] ?>"><?= $filiere['NOM_FILIERE'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="class_id">Classe:</label>
                <select name="class_id" id="class_id" class="form-select">
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['ID_CLASSE'] ?>" data-filiere="<?= $class['ID_FILIERE'] ?>">
                            Classe <?= $class['ID_CLASSE'] ?> (Niveau <?= $class['NIVEAU'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="prof_id">Professeur:</label>
                <select name="prof_id" id="prof_id" class="form-select">
                    <?php foreach ($profs as $prof): ?>
                        <option value="<?= $prof['ID_PROF'] ?>"><?= $prof['NOM_PROF'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="salle_id">Salle:</label>
                <select name="salle_id" id="salle_id" class="form-select">
                    <?php foreach ($salles as $salle): ?>
                        <option value="<?= $salle['ID_SALLE'] ?>"><?= $salle['NOM_SALLE'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="module_id">Module:</label>
                <select name="module_id" id="module_id" class="form-select">
                    <?php foreach ($modules as $module): ?>
                        <option value="<?= $module['ID_MODULE'] ?>"><?= $module['NOM_MODULE'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jour">Jour:</label>
                <select name="jour" id="jour" class="form-select">
                    <option value="lundi">Lundi</option>
                    <option value="mardi">Mardi</option>
                    <option value="mercredi">Mercredi</option>
                    <option value="jeudi">Jeudi</option>
                    <option value="vendredi">Vendredi</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="heure_debut">Heure Début:</label>
                <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="heure_fin">Heure Fin:</label>
                <input type="time" name="heure_fin" id="heure_fin" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
        <div id="result" class="mt-3"></div>
    </div>

    <script>
        function updateClasses() {
            const filiereId = document.getElementById('filiere_id').value;
            const classSelect = document.getElementById('class_id');
            const options = classSelect.getElementsByTagName('option');
            let firstVisible = null;

            for (let i = 0; i < options.length; i++) {
                const optionFiliere = options[i].getAttribute('data-filiere');
                if (filiereId === optionFiliere) {
                    options[i].style.display = '';
                    if (!firstVisible) firstVisible = options[i];
                } else {
                    options[i].style.display = 'none';
                }
            }
            if (firstVisible) {
                classSelect.value = firstVisible.value;
            }
        }

        document.getElementById('sessionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('add_session.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('result').innerHTML = '<div class="alert alert-success">Séance ajoutée avec succès!</div>';
                    document.getElementById('sessionForm').reset();
                } else {
                    document.getElementById('result').innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('result').innerHTML = '<div class="alert alert-danger">Erreur: ' + error + '</div>';
            });
        });

        // Initialiser l'affichage
        updateClasses();
    </script>
</body>
</html>
