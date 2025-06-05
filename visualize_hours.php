<?php
// visualize_hours.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT p.NOM_PROF, 
                            SUM(TIMESTAMPDIFF(MINUTE, c.HEURE_DEBUT, c.HEURE_FIN))/60 AS total_hours 
                            FROM cours c 
                            JOIN professeurs p ON c.ID_PROF = p.ID_PROF 
                            GROUP BY p.ID_PROF, p.NOM_PROF");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $hours = [];
    foreach ($data as $row) {
        $labels[] = $row['NOM_PROF'];
        $hours[] = $row['total_hours'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visualisation des Heures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Charges Horaires des Professeurs</h2>
        <canvas id="hoursChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('hoursChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Heures Enseignées (par semaine)',
                    data: <?php echo json_encode($hours); ?>,
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>