<?php
// add_session.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

$filieres = $profs = $salles = $modules = $classes = [];

function normaliserHeure($h) {
    $h = trim($h);
    if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $h, $m)) {
        return sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3] ?? 0);
    }
    return $h;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $class_id    = (int) $_POST['class_id'];
        $prof_id     = (int) $_POST['prof_id'];
        $salle_id    = (int) $_POST['salle_id'];
        $module_id   = (int) $_POST['module_id'];
        $jour        = $_POST['jour'];
        $heure_debut = normaliserHeure($_POST['heure_debut']);
        $heure_fin   = normaliserHeure($_POST['heure_fin']);

        if ($heure_debut >= $heure_fin) {
            echo json_encode(['status' => 'error', 'message' => "L'heure de fin doit être après l'heure de début."]);
            exit;
        }

        $conflits = [];

        // 1) Conflit professeur
        $stmt = $conn->prepare(
            "SELECT HEURE_DEBUT, HEURE_FIN FROM cours
             WHERE JOUR = ? AND ID_PROF = ?
               AND HEURE_DEBUT < ? AND HEURE_FIN > ? LIMIT 1"
        );
        $stmt->execute([$jour, $prof_id, $heure_fin, $heure_debut]);
        if ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conflits[] = "Le professeur est occupé de {$c['HEURE_DEBUT']} à {$c['HEURE_FIN']}.";
        }

        // 2) Conflit salle
        $stmt = $conn->prepare(
            "SELECT HEURE_DEBUT, HEURE_FIN FROM cours
             WHERE JOUR = ? AND ID_SALLE = ?
               AND HEURE_DEBUT < ? AND HEURE_FIN > ? LIMIT 1"
        );
        $stmt->execute([$jour, $salle_id, $heure_fin, $heure_debut]);
        if ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conflits[] = "La salle est occupée de {$c['HEURE_DEBUT']} à {$c['HEURE_FIN']}.";
        }

        // 3) Conflit classe
        $stmt = $conn->prepare(
            "SELECT HEURE_DEBUT, HEURE_FIN FROM cours
             WHERE JOUR = ? AND ID_CLASSE = ?
               AND HEURE_DEBUT < ? AND HEURE_FIN > ? LIMIT 1"
        );
        $stmt->execute([$jour, $class_id, $heure_fin, $heure_debut]);
        if ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conflits[] = "La classe est occupée de {$c['HEURE_DEBUT']} à {$c['HEURE_FIN']}.";
        }

        if (!empty($conflits)) {
            echo json_encode(['status' => 'error', 'message' => implode(' ', $conflits)]);
            exit;
        }

        $stmt = $conn->prepare(
            "INSERT INTO cours (ID_CLASSE, ID_PROF, ID_SALLE, ID_MODULE, JOUR, HEURE_DEBUT, HEURE_FIN) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$class_id, $prof_id, $salle_id, $module_id, $jour, $heure_debut, $heure_fin]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    // ===== Récupération des données =====
    $profs   = $conn->query("SELECT ID_PROF, NOM_PROF FROM professeurs ORDER BY NOM_PROF")->fetchAll(PDO::FETCH_ASSOC);
    $salles  = $conn->query("SELECT ID_SALLE, NOM_SALLE FROM salles ORDER BY NOM_SALLE")->fetchAll(PDO::FETCH_ASSOC);
    $modules = $conn->query("SELECT ID_MODULE, NOM_MODULE FROM modules ORDER BY NOM_MODULE")->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Récupération avec SIGLE et tri par cycle puis sigle puis niveau
    $classes = $conn->query("
        SELECT c.ID_CLASSE, c.CODE_CLASSE, c.CYCLE, c.NIVEAU,
               c.ID_FILIERE, f.NOM_FILIERE, f.SIGLE
        FROM classes c
        LEFT JOIN filieres f ON c.ID_FILIERE = f.ID_FILIERE
        ORDER BY 
            FIELD(c.CYCLE, 'primaire','college','lycee','licence','master','doctorat'),
            f.SIGLE, c.NIVEAU
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Erreur BDD : ' . $e->getMessage()]);
    } else {
        echo "<p style='color:red'>Erreur BDD : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    exit;
}
$conn = null;

require 'header.php';
?>

<h2>Ajouter une Nouvelle Séance</h2>
<form id="sessionForm">
    <div class="mb-3">
        <label for="class_id">Classe :</label>
        <select name="class_id" id="class_id" class="form-select" required>
            <?php if (empty($classes)): ?>
                <option value="">— Aucune classe disponible. Ajoutez-en dans <a href="manage_all.php">Gestion</a> —</option>
            <?php else: ?>
                <?php
                $classesParCycle = [];
                foreach ($classes as $class) {
                    $classesParCycle[$class['CYCLE'] ?? 'autre'][] = $class;
                }
                $libellesCycle = [
                    'primaire' => '🏫 Primaire',
                    'college'  => '🏫 Collège',
                    'lycee'    => '🏫 Lycée',
                    'licence'  => '🎓 Licence',
                    'master'   => '🎓 Master',
                    'doctorat' => '🎓 Doctorat',
                    'autre'    => 'Autres',
                ];
                foreach ($classesParCycle as $cycle => $liste):
                ?>
                    <optgroup label="<?= $libellesCycle[$cycle] ?? $cycle ?>">
                        <?php foreach ($liste as $class): ?>
                            <option value="<?= $class['ID_CLASSE'] ?>">
                                <?= htmlspecialchars($class['CODE_CLASSE'] ?? ('Classe ' . $class['ID_CLASSE'])) ?>
                                <?php if (!empty($class['SIGLE'])): ?>
                                    — <?= htmlspecialchars($class['SIGLE']) ?>
                                <?php endif; ?>
                                <?php if (!empty($class['NOM_FILIERE'])): ?>
                                    (<?= htmlspecialchars($class['NOM_FILIERE']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <small class="text-muted">
            💡 Cycles disponibles :
            <?php
            $cyclesPresents = array_unique(array_column($classes, 'CYCLE'));
            echo implode(', ', $cyclesPresents) ?: 'aucun';
            ?>
        </small>
    </div>

    <div class="mb-3">
        <label for="prof_id">Professeur :</label>
        <select name="prof_id" id="prof_id" class="form-select" required>
            <?php if (empty($profs)): ?>
                <option value="">— Aucun professeur —</option>
            <?php else: ?>
                <?php foreach ($profs as $prof): ?>
                    <option value="<?= $prof['ID_PROF'] ?>"><?= htmlspecialchars($prof['NOM_PROF']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="salle_id">Salle :</label>
        <select name="salle_id" id="salle_id" class="form-select" required>
            <?php if (empty($salles)): ?>
                <option value="">— Aucune salle —</option>
            <?php else: ?>
                <?php foreach ($salles as $salle): ?>
                    <option value="<?= $salle['ID_SALLE'] ?>"><?= htmlspecialchars($salle['NOM_SALLE']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="module_id">Module :</label>
        <select name="module_id" id="module_id" class="form-select" required>
            <?php if (empty($modules)): ?>
                <option value="">— Aucun module —</option>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['ID_MODULE'] ?>"><?= htmlspecialchars($module['NOM_MODULE']) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="jour">Jour :</label>
        <select name="jour" id="jour" class="form-select" required>
            <option value="lundi">Lundi</option>
            <option value="mardi">Mardi</option>
            <option value="mercredi">Mercredi</option>
            <option value="jeudi">Jeudi</option>
            <option value="vendredi">Vendredi</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="heure_debut">Heure Début :</label>
        <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="heure_fin">Heure Fin :</label>
        <input type="time" name="heure_fin" id="heure_fin" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Ajouter</button>
</form>

<div id="result" class="mt-3"></div>

<hr class="my-4">

<h4>📅 Occupation hebdomadaire</h4>

<div class="row mb-3">
    <div class="col-md-3">
        <label for="vueType" class="form-label">Afficher par :</label>
        <select id="vueType" class="form-select">
            <option value="salles">Salles</option>
            <option value="classes">Classes</option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="vueCible" class="form-label">Sélectionner :</label>
        <select id="vueCible" class="form-select">
            <option value="">—</option>
        </select>
    </div>
</div>

<div class="filtre-jour mb-3">
    <strong>Filtrer par jour :</strong><br>
    <button class="btn btn-sm btn-outline-primary active" data-jour="tous">Tous</button>
    <button class="btn btn-sm btn-outline-primary" data-jour="lundi">Lundi</button>
    <button class="btn btn-sm btn-outline-primary" data-jour="mardi">Mardi</button>
    <button class="btn btn-sm btn-outline-primary" data-jour="mercredi">Mercredi</button>
    <button class="btn btn-sm btn-outline-primary" data-jour="jeudi">Jeudi</button>
    <button class="btn btn-sm btn-outline-primary" data-jour="vendredi">Vendredi</button>
</div>

<div id="legendeProfs" class="mb-3"></div>

<div id="grilleOccupation" class="grille-wrapper">
    <p class="text-muted p-3">Chargement…</p>
</div>

<hr class="my-4">

<h5>🏫 Occupation des salles (vue synthétique)</h5>
<div id="grilleSalles" class="grille-wrapper">
    <p class="text-muted p-3">Chargement…</p>
</div>

<script>
    const PALETTE = [
        '#007bff', '#28a745', '#dc3545', '#fd7e14', '#6f42c1',
        '#20c997', '#e83e8c', '#17a2b8', '#ffc107', '#6610f2',
        '#198754', '#d63384', '#0dcaf0', '#adb5bd', '#fd7e14'
    ];
    function couleurProf(profId) {
        const id = String(profId);
        let hash = 0;
        for (let i = 0; i < id.length; i++) {
            hash = ((hash << 5) - hash) + id.charCodeAt(i);
            hash |= 0;
        }
        return PALETTE[Math.abs(hash) % PALETTE.length];
    }

    let occupationData = null;
    let filtreJour = 'tous';

    document.getElementById('sessionForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('add_session.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                const box = document.getElementById('result');
                if (data.status === 'success') {
                    box.innerHTML = '<div class="alert alert-success">Séance ajoutée avec succès !</div>';
                    document.getElementById('sessionForm').reset();
                    chargerOccupation();
                } else {
                    box.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
            })
            .catch(err => {
                document.getElementById('result').innerHTML =
                    '<div class="alert alert-danger">Erreur : ' + err + '</div>';
            });
    });

    function chargerOccupation() {
        fetch('get_occupation.php')
            .then(r => r.json())
            .then(resp => {
                if (resp.status !== 'success') {
                    document.getElementById('grilleOccupation').innerHTML =
                        '<div class="alert alert-danger m-3">Erreur : ' + resp.message + '</div>';
                    return;
                }
                occupationData = resp;
                remplirCibles();
                dessinerLegende();
                dessinerGrille();
                dessinerGrilleSalles();
            })
            .catch(err => {
                document.getElementById('grilleOccupation').innerHTML =
                    '<div class="alert alert-danger m-3">Erreur : ' + err + '</div>';
            });
    }

    function remplirCibles() {
        if (!occupationData) return;
        const type  = document.getElementById('vueType').value;
        const cible = document.getElementById('vueCible');
        const set   = new Set();

        for (const jour in occupationData.data) {
            const groupe = occupationData.data[jour][type] || {};
            for (const key in groupe) set.add(key);
        }
        const options = Array.from(set).sort();
        cible.innerHTML = options.length
            ? options.map(o => `<option value="${o}">${o}</option>`).join('')
            : '<option value="">—</option>';
    }

    function dessinerLegende() {
        if (!occupationData || !occupationData.profs) return;
        const container = document.getElementById('legendeProfs');
        const profs = occupationData.profs;
        const keys = Object.keys(profs);

        if (keys.length === 0) {
            container.innerHTML = '';
            return;
        }

        let html = '<strong>Professeurs :</strong><br>';
        keys.forEach(id => {
            const couleur = couleurProf(id);
            html += `<span class="legende-prof">
                        <span class="pastille" style="background:${couleur}"></span>
                        ${profs[id]}
                     </span>`;
        });
        container.innerHTML = html;
    }

    function dessinerGrille() {
        if (!occupationData) return;

        const type  = document.getElementById('vueType').value;
        const cible = document.getElementById('vueCible').value;
        const { creneaux } = occupationData;

        const jours = filtreJour === 'tous' ? occupationData.jours : [filtreJour];

        if (!cible) {
            document.getElementById('grilleOccupation').innerHTML =
                '<div class="alert alert-info m-3">Aucune donnée à afficher.</div>';
            return;
        }

        let html = '<table class="grille-occupation">';
        html += '<thead><tr>';
        html += '<th rowspan="2">Créneau</th>';
        html += `<th colspan="${jours.length}">${type === 'salles' ? 'Salle' : 'Classe'} : ${cible}</th>`;
        html += '</tr><tr>';
        jours.forEach(j => {
            html += `<th>${j.charAt(0).toUpperCase() + j.slice(1)}</th>`;
        });
        html += '</tr></thead><tbody>';

        creneaux.forEach(c => {
            html += `<tr><th>${c}</th>`;
            jours.forEach(j => {
                const cellule = (occupationData.data[j]?.[type]?.[cible]?.[c]) || null;
                if (cellule) {
                    const couleur = couleurProf(cellule.prof_id);
                    html += `<td class="occupe" style="background:${couleur}">
                                ${cellule.module}
                                <small>${cellule.prof}</small>
                             </td>`;
                } else {
                    html += `<td class="libre">Libre</td>`;
                }
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        document.getElementById('grilleOccupation').innerHTML = html;
    }

    function dessinerGrilleSalles() {
        if (!occupationData) return;

        const { creneaux } = occupationData;
        const jourAffiche = filtreJour === 'tous' ? 'lundi' : filtreJour;

        const sallesSet = new Set();
        for (const j in occupationData.data) {
            const salles = occupationData.data[j]['salles'] || {};
            for (const s in salles) sallesSet.add(s);
        }
        const salles = Array.from(sallesSet).sort();

        if (salles.length === 0) {
            document.getElementById('grilleSalles').innerHTML =
                '<div class="alert alert-info m-3">Aucune salle enregistrée.</div>';
            return;
        }

        let html = '<table class="grille-occupation">';
        html += `<thead><tr><th>Créneau</th>`;
        salles.forEach(s => {
            html += `<th>${s}</th>`;
        });
        html += `</tr></thead><tbody>`;

        creneaux.forEach(c => {
            html += `<tr><th>${c}</th>`;
            salles.forEach(s => {
                const cellule = occupationData.data[jourAffiche]?.['salles']?.[s]?.[c] || null;
                if (cellule) {
                    const couleur = couleurProf(cellule.prof_id);
                    html += `<td class="occupe" style="background:${couleur}">
                                ${cellule.module}
                                <small>${cellule.prof}</small>
                             </td>`;
                } else {
                    html += `<td class="libre">Libre</td>`;
                }
            });
            html += '</tr>';
        });
        html += '</tbody></table>';

        document.getElementById('grilleSalles').innerHTML =
            `<p class="text-muted mb-1 p-2">Jour affiché : <strong>${jourAffiche}</strong></p>` + html;
    }

    document.getElementById('vueType').addEventListener('change', () => {
        remplirCibles();
        dessinerGrille();
    });
    document.getElementById('vueCible').addEventListener('change', dessinerGrille);

    document.querySelectorAll('.filtre-jour button').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filtre-jour button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filtreJour = btn.dataset.jour;
            dessinerGrille();
            dessinerGrilleSalles();
        });
    });

    window.addEventListener('DOMContentLoaded', () => {
        chargerOccupation();
    });
</script>

<?php require 'footer.php'; ?>