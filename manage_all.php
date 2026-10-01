<?php
// manage_all.php — Gestion complète (filières, classes, modules, profs, salles)
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

$message = '';
$messageType = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ==================== TRAITEMENT DES FORMULAIRES ====================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // ---------- FILIÈRES ----------
        if ($action === 'add_filiere') {
            $nom   = trim($_POST['nom_filiere']);
            $sigle = strtoupper(trim($_POST['sigle'] ?? ''));
            $cycle = trim($_POST['cycle_filiere'] ?? '');
            $desc  = trim($_POST['description'] ?? '');

            if ($nom === '' || $sigle === '') {
                $message = "❌ Le nom et le sigle de la filière sont obligatoires.";
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM filieres WHERE NOM_FILIERE = ? OR SIGLE = ?");
                $stmt->execute([$nom, $sigle]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "❌ Cette filière (nom ou sigle) existe déjà.";
                    $messageType = 'danger';
                } else {
                    $next = (int) $conn->query("SELECT COALESCE(MAX(ID_FILIERE), 0) + 1 FROM filieres")->fetchColumn();
                    $stmt = $conn->prepare("INSERT INTO filieres (ID_FILIERE, SIGLE, CYCLE, NOM_FILIERE, DESCRIPTION) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$next, $sigle, $cycle, $nom, $desc]);
                    $message = "✅ Filière ajoutée ($sigle — $nom).";
                    $messageType = 'success';
                }
            }
        }
        if ($action === 'delete_filiere') {
            try {
                $conn->prepare("DELETE FROM filieres WHERE ID_FILIERE = ?")->execute([(int)$_POST['id_filiere']]);
                $message = "🗑️ Filière supprimée.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer : cette filière est utilisée.";
                $messageType = 'danger';
            }
        }

        // ---------- CLASSES ----------
        if ($action === 'add_classe') {
            $code       = strtoupper(trim($_POST['code_classe']));
            $cycle      = trim($_POST['cycle']);
            $niveau     = (int) $_POST['niveau'];
            $id_filiere = !empty($_POST['id_filiere']) ? (int) $_POST['id_filiere'] : null;

            // Primaire / collège → PAS de filière
            if (in_array($cycle, ['primaire', 'college'])) {
                $id_filiere = null;
            }

            // Lycée / Université → filière OBLIGATOIRE
            if (in_array($cycle, ['lycee', 'licence', 'master', 'doctorat']) && $id_filiere === null) {
                $message = "❌ La filière est obligatoire pour le cycle '$cycle'.";
                $messageType = 'danger';
            } elseif ($code === '') {
                $message = "❌ Le code de la classe est obligatoire.";
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM classes WHERE CODE_CLASSE = ?");
                $stmt->execute([$code]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "❌ Une classe avec le code '$code' existe déjà.";
                    $messageType = 'danger';
                } else {
                    $next = (int) $conn->query("SELECT COALESCE(MAX(ID_CLASSE), 0) + 1 FROM classes")->fetchColumn();
                    $stmt = $conn->prepare("
                        INSERT INTO classes (ID_CLASSE, CODE_CLASSE, ID_FILIERE, NIVEAU, CYCLE)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$next, $code, $id_filiere, $niveau, $cycle]);
                    $message = "✅ Classe ajoutée : <strong>$code</strong>";
                    $messageType = 'success';
                }
            }
        }
        if ($action === 'delete_classe') {
            try {
                $conn->prepare("DELETE FROM classes WHERE ID_CLASSE = ?")->execute([(int)$_POST['id_classe']]);
                $message = "🗑️ Classe supprimée.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer : cette classe est utilisée dans des cours.";
                $messageType = 'danger';
            }
        }

        // ---------- MODULES ----------
        if ($action === 'add_module') {
            $nom  = trim($_POST['nom_module']);
            $desc = trim($_POST['description'] ?? '');
            if ($nom === '') {
                $message = "❌ Le nom du module est obligatoire.";
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM modules WHERE NOM_MODULE = ?");
                $stmt->execute([$nom]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "❌ Ce module existe déjà.";
                    $messageType = 'danger';
                } else {
                    $next = (int) $conn->query("SELECT COALESCE(MAX(ID_MODULE), 0) + 1 FROM modules")->fetchColumn();
                    $conn->prepare("INSERT INTO modules (ID_MODULE, NOM_MODULE, DESCRIPTION) VALUES (?, ?, ?)")
                         ->execute([$next, $nom, $desc]);
                    $message = "✅ Module ajouté (ID = $next).";
                    $messageType = 'success';
                }
            }
        }
        if ($action === 'delete_module') {
            try {
                $conn->prepare("DELETE FROM modules WHERE ID_MODULE = ?")->execute([(int)$_POST['id_module']]);
                $message = "🗑️ Module supprimé.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer : ce module est utilisé dans des cours.";
                $messageType = 'danger';
            }
        }

        // ---------- PROFESSEURS ----------
        if ($action === 'add_prof') {
            $nom = trim($_POST['nom_prof']);
            $tel = trim($_POST['tel'] ?? '');
            if ($nom === '') {
                $message = "❌ Le nom du professeur est obligatoire.";
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM professeurs WHERE NOM_PROF = ?");
                $stmt->execute([$nom]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "❌ Ce professeur existe déjà.";
                    $messageType = 'danger';
                } else {
                    $next = (int) $conn->query("SELECT COALESCE(MAX(ID_PROF), 0) + 1 FROM professeurs")->fetchColumn();
                    $conn->prepare("INSERT INTO professeurs (ID_PROF, NOM_PROF, TEL) VALUES (?, ?, ?)")
                         ->execute([$next, $nom, $tel]);
                    $message = "✅ Professeur ajouté (ID = $next).";
                    $messageType = 'success';
                }
            }
        }
        if ($action === 'delete_prof') {
            try {
                $conn->prepare("DELETE FROM professeurs WHERE ID_PROF = ?")->execute([(int)$_POST['id_prof']]);
                $message = "🗑️ Professeur supprimé.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer : ce professeur a des cours programmés.";
                $messageType = 'danger';
            }
        }

        // ---------- SALLES ----------
        if ($action === 'add_salle') {
            $nom  = trim($_POST['nom_salle']);
            $desc = trim($_POST['description'] ?? '');
            if ($nom === '') {
                $message = "❌ Le nom de la salle est obligatoire.";
                $messageType = 'danger';
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM salles WHERE NOM_SALLE = ?");
                $stmt->execute([$nom]);
                if ($stmt->fetchColumn() > 0) {
                    $message = "❌ Cette salle existe déjà.";
                    $messageType = 'danger';
                } else {
                    $next = (int) $conn->query("SELECT COALESCE(MAX(ID_SALLE), 0) + 1 FROM salles")->fetchColumn();
                    $conn->prepare("INSERT INTO salles (ID_SALLE, NOM_SALLE, DESCRIPTION) VALUES (?, ?, ?)")
                         ->execute([$next, $nom, $desc]);
                    $message = "✅ Salle ajoutée (ID = $next).";
                    $messageType = 'success';
                }
            }
        }
        if ($action === 'delete_salle') {
            try {
                $conn->prepare("DELETE FROM salles WHERE ID_SALLE = ?")->execute([(int)$_POST['id_salle']]);
                $message = "🗑️ Salle supprimée.";
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = "❌ Impossible de supprimer : cette salle est utilisée dans des cours.";
                $messageType = 'danger';
            }
        }
    }

    // ==================== CHARGEMENT DES DONNÉES ====================
    $filieres = $conn->query("
        SELECT f.ID_FILIERE, f.NOM_FILIERE, f.SIGLE, f.CYCLE, f.DESCRIPTION,
               (SELECT COUNT(*) FROM classes WHERE ID_FILIERE = f.ID_FILIERE) AS nb_classes
        FROM filieres f ORDER BY f.CYCLE, f.SIGLE
    ")->fetchAll(PDO::FETCH_ASSOC);

    $classes = $conn->query("
        SELECT c.ID_CLASSE, c.CODE_CLASSE, c.CYCLE, c.NIVEAU,
               c.ID_FILIERE, f.NOM_FILIERE, f.SIGLE,
               (SELECT COUNT(*) FROM cours WHERE ID_CLASSE = c.ID_CLASSE) AS nb_cours
        FROM classes c
        LEFT JOIN filieres f ON c.ID_FILIERE = f.ID_FILIERE
        ORDER BY 
            FIELD(c.CYCLE, 'primaire','college','lycee','licence','master','doctorat'),
            f.SIGLE, c.NIVEAU
    ")->fetchAll(PDO::FETCH_ASSOC);

    $modules = $conn->query("
        SELECT m.ID_MODULE, m.NOM_MODULE, m.DESCRIPTION,
               (SELECT COUNT(*) FROM cours WHERE ID_MODULE = m.ID_MODULE) AS nb_cours
        FROM modules m ORDER BY m.NOM_MODULE
    ")->fetchAll(PDO::FETCH_ASSOC);

    $profs = $conn->query("
        SELECT p.ID_PROF, p.NOM_PROF, p.TEL,
               (SELECT COUNT(*) FROM cours WHERE ID_PROF = p.ID_PROF) AS nb_cours
        FROM professeurs p ORDER BY p.NOM_PROF
    ")->fetchAll(PDO::FETCH_ASSOC);

    $salles = $conn->query("
        SELECT s.ID_SALLE, s.NOM_SALLE, s.DESCRIPTION,
               (SELECT COUNT(*) FROM cours WHERE ID_SALLE = s.ID_SALLE) AS nb_cours
        FROM salles s ORDER BY s.NOM_SALLE
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Erreur BDD : " . $e->getMessage();
    $messageType = 'danger';
}
$conn = null;

require 'header.php';
?>

<h2>🏫 Gestion Complète</h2>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3" id="gestionTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-filieres" type="button">🎓 Filières <span class="badge bg-secondary"><?= count($filieres) ?></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-classes" type="button">📚 Classes <span class="badge bg-secondary"><?= count($classes) ?></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-modules" type="button">📦 Modules <span class="badge bg-secondary"><?= count($modules) ?></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-profs" type="button">👨‍🏫 Professeurs <span class="badge bg-secondary"><?= count($profs) ?></span></button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-salles" type="button">🏛️ Salles <span class="badge bg-secondary"><?= count($salles) ?></span></button></li>
</ul>

<div class="tab-content">

    <!-- ============ FILIÈRES ============ -->
    <div class="tab-pane fade show active" id="tab-filieres">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><strong>➕ Ajouter une filière</strong></div>
            <div class="card-body">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_filiere">
                    <div class="col-md-2"><input type="text" name="sigle" class="form-control" placeholder="Sigle (ex : D)" required></div>
                    <div class="col-md-3"><input type="text" name="nom_filiere" class="form-control" placeholder="Nom complet" required></div>
                    <div class="col-md-2">
                        <select name="cycle_filiere" class="form-select">
                            <option value="lycee">Lycée</option>
                            <option value="licence" selected>Licence</option>
                            <option value="master">Master</option>
                            <option value="doctorat">Doctorat</option>
                        </select>
                    </div>
                    <div class="col-md-3"><input type="text" name="description" class="form-control" placeholder="Description (optionnel)"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
                <small class="text-muted d-block mt-2">
                    ⚠️ Filières uniquement pour <strong>Lycée</strong> et <strong>Université</strong>. Primaire et collège n'ont pas de filière.
                </small>
            </div>
        </div>

        <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Sigle</th><th>Nom</th><th>Cycle</th><th>Description</th><th>Classes</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($filieres as $f): ?>
                    <tr>
                        <td><?= $f['ID_FILIERE'] ?></td>
                        <td><strong><?= htmlspecialchars($f['SIGLE'] ?? '—') ?></strong></td>
                        <td><?= htmlspecialchars($f['NOM_FILIERE']) ?></td>
                        <td><?= htmlspecialchars($f['CYCLE'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($f['DESCRIPTION']) ?></td>
                        <td><?= $f['nb_classes'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_filiere">
                                <input type="hidden" name="id_filiere" value="<?= $f['ID_FILIERE'] ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============ CLASSES ============ -->
    <div class="tab-pane fade" id="tab-classes">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><strong>➕ Ajouter une classe</strong></div>
            <div class="card-body">
                <form method="POST" class="row g-2" id="formClasse">
                    <input type="hidden" name="action" value="add_classe">

                    <div class="col-md-3">
                        <label class="form-label">Cycle :</label>
                        <select name="cycle" id="cycle" class="form-select" onchange="mettreAJourTout()" required>
                            <option value="primaire">Primaire</option>
                            <option value="college">Collège</option>
                            <option value="lycee">Lycée</option>
                            <option value="licence" selected>Licence</option>
                            <option value="master">Master</option>
                            <option value="doctorat">Doctorat</option>
                        </select>
                    </div>

                    <div class="col-md-3" id="bloc-filiere">
                        <label class="form-label">Filière <span id="ast-filiere" class="text-danger">*</span> :</label>
                        <select name="id_filiere" id="id_filiere" class="form-select" onchange="mettreAJourTout()">
                            <option value="">— Choisir —</option>
                            <?php foreach ($filieres as $f): ?>
                                <option value="<?= $f['ID_FILIERE'] ?>"
                                        data-sigle="<?= htmlspecialchars($f['SIGLE'] ?? '') ?>"
                                        data-cycle="<?= htmlspecialchars($f['CYCLE'] ?? '') ?>">
                                    <?= htmlspecialchars($f['NOM_FILIERE']) ?>
                                    <?= $f['SIGLE'] ? '(' . htmlspecialchars($f['SIGLE']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Niveau :</label>
                        <input type="number" name="niveau" id="niveau" class="form-control" min="1" max="7" value="1"
                               onchange="mettreAJourTout()" oninput="mettreAJourTout()" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Code :</label>
                        <input type="text" name="code_classe" id="code_classe" class="form-control"
                               placeholder="Ex : SRI1" readonly required>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Ajouter</button>
                    </div>
                </form>

                <small class="text-muted d-block mt-2">
                    💡 Le code est généré automatiquement. Ex : <code>CP1</code>, <code>4EME</code>,
                    <code>2NDD</code>, <code>SRI1</code>, <code>MSRI1</code>, <code>DSRI1</code>…
                </small>
            </div>
        </div>

        <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Code</th><th>Cycle</th><th>Filière</th><th>Niveau</th><th>Cours</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($classes as $c): ?>
                    <tr>
                        <td><?= $c['ID_CLASSE'] ?></td>
                        <td><strong><?= htmlspecialchars($c['CODE_CLASSE'] ?? '—') ?></strong></td>
                        <td><?= htmlspecialchars($c['CYCLE'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($c['SIGLE'])): ?>
                                <?= htmlspecialchars($c['SIGLE']) ?> —
                            <?php endif; ?>
                            <?= htmlspecialchars($c['NOM_FILIERE'] ?? '—') ?>
                        </td>
                        <td><?= $c['NIVEAU'] ?></td>
                        <td><?= $c['nb_cours'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_classe">
                                <input type="hidden" name="id_classe" value="<?= $c['ID_CLASSE'] ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============ MODULES ============ -->
    <div class="tab-pane fade" id="tab-modules">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><strong>➕ Ajouter un module</strong></div>
            <div class="card-body">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_module">
                    <div class="col-md-4"><input type="text" name="nom_module" class="form-control" placeholder="Nom (ex : Java)" required></div>
                    <div class="col-md-6"><input type="text" name="description" class="form-control" placeholder="Description (optionnel)"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
            </div>
        </div>
        <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Nom</th><th>Description</th><th>Cours</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($modules as $m): ?>
                    <tr>
                        <td><?= $m['ID_MODULE'] ?></td>
                        <td><?= htmlspecialchars($m['NOM_MODULE']) ?></td>
                        <td><?= htmlspecialchars($m['DESCRIPTION']) ?></td>
                        <td><?= $m['nb_cours'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_module">
                                <input type="hidden" name="id_module" value="<?= $m['ID_MODULE'] ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============ PROFS ============ -->
    <div class="tab-pane fade" id="tab-profs">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><strong>➕ Ajouter un professeur</strong></div>
            <div class="card-body">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_prof">
                    <div class="col-md-5"><input type="text" name="nom_prof" class="form-control" placeholder="Nom (ex : Prof C)" required></div>
                    <div class="col-md-5"><input type="text" name="tel" class="form-control" placeholder="Téléphone (optionnel)"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
            </div>
        </div>
        <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Nom</th><th>Téléphone</th><th>Cours</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($profs as $p): ?>
                    <tr>
                        <td><?= $p['ID_PROF'] ?></td>
                        <td><?= htmlspecialchars($p['NOM_PROF']) ?></td>
                        <td><?= htmlspecialchars($p['TEL']) ?></td>
                        <td><?= $p['nb_cours'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_prof">
                                <input type="hidden" name="id_prof" value="<?= $p['ID_PROF'] ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============ SALLES ============ -->
    <div class="tab-pane fade" id="tab-salles">
        <div class="card mb-3">
            <div class="card-header bg-primary text-white"><strong>➕ Ajouter une salle</strong></div>
            <div class="card-body">
                <form method="POST" class="row g-2">
                    <input type="hidden" name="action" value="add_salle">
                    <div class="col-md-4"><input type="text" name="nom_salle" class="form-control" placeholder="Nom (ex : amphi A)" required></div>
                    <div class="col-md-6"><input type="text" name="description" class="form-control" placeholder="Description (optionnel)"></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
            </div>
        </div>
        <table class="table table-sm table-striped">
            <thead class="table-dark"><tr><th>ID</th><th>Nom</th><th>Description</th><th>Cours</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($salles as $s): ?>
                    <tr>
                        <td><?= $s['ID_SALLE'] ?></td>
                        <td><?= htmlspecialchars($s['NOM_SALLE']) ?></td>
                        <td><?= htmlspecialchars($s['DESCRIPTION']) ?></td>
                        <td><?= $s['nb_cours'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Supprimer ?');" style="display:inline">
                                <input type="hidden" name="action" value="delete_salle">
                                <input type="hidden" name="id_salle" value="<?= $s['ID_SALLE'] ?>">
                                <button class="btn btn-sm btn-outline-danger">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
    // ============================================================
    // Génération automatique du code classe + filtrage filières
    // ============================================================

    const CODES_PRIMAIRE = ['CP1', 'CP2', 'CE1', 'CE2', 'CM1', 'CM2'];
    const CODES_COLLEGE  = ['6EME', '5EME', '4EME', '3EME'];
    const CODES_LYCEE    = ['2ND', '1ERE', 'TERM'];

    // Met à jour le code ET filtre les filières selon le cycle
    function mettreAJourTout() {
        const cycle       = document.getElementById('cycle').value;
        const filiere     = document.getElementById('id_filiere');
        const niveau      = document.getElementById('niveau').value;
        const code        = document.getElementById('code_classe');
        const blocFiliere = document.getElementById('bloc-filiere');
        const astFiliere  = document.getElementById('ast-filiere');

        // Primaire / collège → PAS de filière
        const sansFiliere = (cycle === 'primaire' || cycle === 'college');

        if (sansFiliere) {
            blocFiliere.style.display = 'none';
            filiere.disabled = true;
            filiere.value = '';
        } else {
            blocFiliere.style.display = '';
            filiere.disabled = false;
        }

        // Filtrer les filières selon le cycle
        const options = filiere.getElementsByTagName('option');
        let firstVisible = null;
        for (let i = 0; i < options.length; i++) {
            const optCycle = options[i].getAttribute('data-cycle');
            if (!optCycle) continue; // "— Choisir —"

            if (optCycle === cycle) {
                options[i].style.display = '';
                options[i].disabled = false;
                if (!firstVisible) firstVisible = options[i];
            } else {
                options[i].style.display = 'none';
                options[i].disabled = true;
            }
        }

        // Auto-sélectionner la première filière visible
        if (firstVisible && (filiere.value === '' || filiere.selectedOptions[0]?.disabled)) {
            filiere.value = firstVisible.value;
        }

        // Récupérer le sigle
        let sigle = '';
        if (filiere.selectedOptions.length > 0) {
            sigle = filiere.selectedOptions[0].getAttribute('data-sigle') || '';
        }

        // Générer le code
        let codeGenere = '';
        switch (cycle) {
            case 'primaire': {
                const idx = parseInt(niveau) - 1;
                codeGenere = CODES_PRIMAIRE[idx] || '';
                break;
            }
            case 'college': {
                const idx = parseInt(niveau) - 1;
                codeGenere = CODES_COLLEGE[idx] || '';
                break;
            }
            case 'lycee': {
                const idx = parseInt(niveau) - 1;
                const prefixe = CODES_LYCEE[idx] || '';
                codeGenere = sigle ? (prefixe + sigle) : '';
                break;
            }
            case 'licence':
                codeGenere = sigle ? (sigle + niveau) : '';
                break;
            case 'master':
                codeGenere = sigle ? ('M' + sigle + niveau) : '';
                break;
            case 'doctorat':
                codeGenere = sigle ? ('D' + sigle + niveau) : '';
                break;
        }
        code.value = codeGenere.toUpperCase();
    }

    document.addEventListener('DOMContentLoaded', () => {
        ['cycle', 'id_filiere', 'niveau'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', mettreAJourTout);
                el.addEventListener('input', mettreAJourTout);
            }
        });
        mettreAJourTout();
    });
</script>

<?php require 'footer.php'; ?>