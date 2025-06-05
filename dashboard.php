<!-- dashboard.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion de l'Emploi du Temps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">School Timetable</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="view_timetable.php">Consulter Emploi du Temps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visualize_hours.php">Visualisation des Heures</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_students_modules.php">Étudiants et Modules</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_session.php">Ajouter une Séance</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Bienvenue sur le Dashboard</h1>
        <p>Utilisez la barre de navigation pour accéder aux différentes fonctionnalités :</p>
        <ul>
            <li><a href="view_timetable.php">Consulter l'Emploi du Temps</a> : Visualisez l'emploi du temps d'une classe avec Ajax.</li>
            <li><a href="visualize_hours.php">Visualisation des Heures</a> : Consultez le graphique des heures enseignées par professeur.</li>
            <li><a href="view_students_modules.php">Étudiants et Modules</a> : Affichez la liste des étudiants et modules d'une classe.</li>
            <li><a href="add_session.php">Ajouter une Séance</a> : Ajoutez une nouvelle séance à l'emploi du temps.</li>
        </ul>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>