<!-- view_timetable.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consulter l'Emploi du Temps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Consulter l'Emploi du Temps</h2>
        <div id="timetableResult"></div>
    </div>

    <script>
        // Fetch all courses directly
        fetch('generate_timetable_xml.php')
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(data, 'application/xml');
                const seances = xmlDoc.getElementsByTagName('seance');
                let html = '<table class="table table-bordered"><tr><th>Jour</th><th>Début</th><th>Fin</th><th>Prof</th><th>Module</th><th>Salle</th><th>Classe</th></tr>';
                for (let seance of seances) {
                    const classInfo = `${seance.getAttribute('filiere')} - Niveau ${seance.getAttribute('niveau')}`; // Display filiere and niveau
                    html += `<tr>
                        <td>${seance.getAttribute('jour')}</td>
                        <td>${seance.getAttribute('debut')}</td>
                        <td>${seance.getAttribute('fin')}</td>
                        <td>${seance.getAttribute('prof')}</td>
                        <td>${seance.getAttribute('module')}</td>
                        <td>${seance.getAttribute('salle')}</td>
                        <td>${classInfo}</td> <!-- Display detailed class info -->
                    </tr>`;
                }
                html += '</table>';
                document.getElementById('timetableResult').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('timetableResult').innerHTML = '<div class="alert alert-danger">Erreur: ' + error + '</div>';
            });
    </script>
</body>
</html>