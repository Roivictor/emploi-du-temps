<?php
// dashboard.php — Version moderne avec stats dynamiques
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

// Valeurs par défaut
$stats = [
    'classes'  => 0,
    'profs'    => 0,
    'salles'   => 0,
    'cours'    => 0,
    'modules'  => 0,
    'filieres' => 0,
];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stats['classes']  = (int) $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $stats['profs']    = (int) $conn->query("SELECT COUNT(*) FROM professeurs")->fetchColumn();
    $stats['salles']   = (int) $conn->query("SELECT COUNT(*) FROM salles")->fetchColumn();
    $stats['cours']    = (int) $conn->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    $stats['modules']  = (int) $conn->query("SELECT COUNT(*) FROM modules")->fetchColumn();
    $stats['filieres'] = (int) $conn->query("SELECT COUNT(*) FROM filieres")->fetchColumn();
} catch (PDOException $e) {
    // Silencieux : on garde les valeurs par défaut
}
$conn = null;

require 'header.php';
?>

<!-- ===== Hero ===== -->
<div class="dashboard-hero">
    <h1>👋 Bienvenue sur School Timetable</h1>
    <p>Gérez les emplois du temps, les classes, les professeurs et bien plus.</p>
</div>

<!-- ===== Statistiques ===== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['classes'] ?></div>
                    <div class="stat-label">Classes</div>
                </div>
                <div class="stat-icon">📚</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['profs'] ?></div>
                    <div class="stat-label">Professeurs</div>
                </div>
                <div class="stat-icon">👨‍🏫</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['salles'] ?></div>
                    <div class="stat-label">Salles</div>
                </div>
                <div class="stat-icon">🏛️</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['modules'] ?></div>
                    <div class="stat-label">Modules</div>
                </div>
                <div class="stat-icon">📦</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['cours'] ?></div>
                    <div class="stat-label">Séances</div>
                </div>
                <div class="stat-icon">📅</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value"><?= $stats['filieres'] ?></div>
                    <div class="stat-label">Filières</div>
                </div>
                <div class="stat-icon">🎓</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== Actions rapides ===== -->
<div class="section-title">⚡ Actions rapides</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <a href="view_timetable.php" class="feature-card">
            <div class="feature-icon icon-blue">🗓️</div>
            <h5>Consulter l'Emploi du Temps</h5>
            <p>Visualisez les séances par classe, professeur ou salle.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="visualize_hours.php" class="feature-card">
            <div class="feature-icon icon-green">📊</div>
            <h5>Visualisation des Heures</h5>
            <p>Graphique des heures enseignées par professeur.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-4">
        <a href="view_students_modules.php" class="feature-card">
            <div class="feature-icon icon-purple">👥</div>
            <h5>Étudiants et Modules</h5>
            <p>Liste des étudiants et modules par classe.</p>
        </a>
    </div>
</div>

<!-- ===== Gestion ===== -->
<div class="section-title">🛠️ Gestion</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-3">
        <a href="add_session.php" class="feature-card">
            <div class="feature-icon icon-orange">➕</div>
            <h5>Ajouter une Séance</h5>
            <p>Programmez un nouveau cours avec détection de conflits.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="manage_all.php" class="feature-card">
            <div class="feature-icon icon-pink">⚙️</div>
            <h5>Gestion Complète</h5>
            <p>Filières, classes, modules, professeurs, salles.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="setup_database.php" class="feature-card">
            <div class="feature-icon icon-blue">🔄</div>
            <h5>Réinitialiser la BDD</h5>
            <p>Recrée la base de données avec des données d'exemple.</p>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="add_session.php" class="feature-card">
            <div class="feature-icon icon-green">📈</div>
            <h5>Occupation</h5>
            <p>Voir la grille d'occupation des salles en un coup d'œil.</p>
        </a>
    </div>
</div>

<?php require 'footer.php'; ?>