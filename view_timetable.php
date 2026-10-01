<?php
// view_timetable.php
// Pas de traitement PHP ici → on inclut directement le header
require 'header.php';
?>

<h2>Consulter l'Emploi du Temps</h2>

<div id="timetableResult">
    <p class="text-muted">Chargement de l'emploi du temps…</p>
</div>

<script>
    // 1) Récupérer le XML généré côté serveur
    fetch('generate_timetable_xml.php')
        .then(response => response.text())
        .then(data => {
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(data, 'application/xml');

            // 2) Vérifier que le XML est valide
            if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
                document.getElementById('timetableResult').innerHTML =
                    '<div class="alert alert-danger">Erreur : XML invalide reçu du serveur.</div>';
                return;
            }

            // 3) Vérifier si le serveur a renvoyé une balise <erreur>
            const erreurNode = xmlDoc.getElementsByTagName('erreur');
            if (erreurNode.length > 0) {
                document.getElementById('timetableResult').innerHTML =
                    '<div class="alert alert-danger">Erreur serveur : ' +
                    erreurNode[0].textContent + '</div>';
                return;
            }

            // 4) Construire le tableau
            const seances = xmlDoc.getElementsByTagName('seance');

            if (seances.length === 0) {
                document.getElementById('timetableResult').innerHTML =
                    '<div class="alert alert-info">Aucune séance enregistrée pour le moment.</div>';
                return;
            }

            let html = '<table class="table table-bordered table-striped align-middle">';
            html += '<thead class="table-dark"><tr>';
            html += '<th>Jour</th><th>Début</th><th>Fin</th>';
            html += '<th>Prof</th><th>Module</th><th>Salle</th><th>Classe</th>';
            html += '</tr></thead><tbody>';

            for (let seance of seances) {
                const classInfo = `${seance.getAttribute('filiere')} - Niveau ${seance.getAttribute('niveau')}`;
                html += `<tr>
                    <td>${seance.getAttribute('jour')}</td>
                    <td>${seance.getAttribute('debut')}</td>
                    <td>${seance.getAttribute('fin')}</td>
                    <td>${seance.getAttribute('prof')}</td>
                    <td>${seance.getAttribute('module')}</td>
                    <td>${seance.getAttribute('salle')}</td>
                    <td>${classInfo}</td>
                </tr>`;
            }

            html += '</tbody></table>';
            document.getElementById('timetableResult').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('timetableResult').innerHTML =
                '<div class="alert alert-danger">Erreur : ' + error + '</div>';
        });
</script>

<?php require 'footer.php'; ?>