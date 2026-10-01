<?php
// visualize_hours.php

// 1) Initialisation AVANT le try → plus de warning si la BDD plante
$labels = [];
$hours  = [];

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT p.NOM_PROF, 
                                   SUM(TIMESTAMPDIFF(MINUTE, c.HEURE_DEBUT, c.HEURE_FIN))/60 AS total_hours 
                            FROM cours c 
                            JOIN professeurs p ON c.ID_PROF = p.ID_PROF 
                            GROUP BY p.ID_PROF, p.NOM_PROF
                            ORDER BY total_hours DESC");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as $row) {
        $labels[] = $row['NOM_PROF'];
        $hours[]  = (float) $row['total_hours'];
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur BDD : " . htmlspecialchars($e->getMessage()) . "</p>";
}
$conn = null;

// 2) Header inclus APRÈS le traitement PHP
require 'header.php';
?>

<h2>Charges Horaires des Professeurs</h2>

<?php if (empty($labels)): ?>
    <div class="alert alert-info">
        Aucune donnée à afficher. Ajoutez des séances via
        <a href="add_session.php">Ajouter une Séance</a>.
    </div>
<?php else: ?>
    <canvas id="hoursChart" height="100"></canvas>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // On n'instancie Chart que si le canvas est présent
    const canvas = document.getElementById('hoursChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Heures Enseignées (par semaine)',
                    data: <?= json_encode($hours) ?>,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Heures' }
                    }
                }
            }
        });
    }
</script>

<?php require 'footer.php'; ?>