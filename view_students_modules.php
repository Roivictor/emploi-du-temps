<?php
// view_students_modules.php

// 1) Initialisation AVANT le try → plus de warning si la BDD plante
$classes = [];

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT ID_CLASSE FROM classes ORDER BY ID_CLASSE");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur BDD : " . htmlspecialchars($e->getMessage()) . "</p>";
}
$conn = null;

// 2) Header inclus APRÈS le traitement PHP
require 'header.php';
?>

<h2>Étudiants et Modules</h2>

<div class="mb-3">
    <label for="classSelect" class="form-label">Sélectionner une classe :</label>
    <select id="classSelect" class="form-select">
        <?php if (!empty($classes)): ?>
            <?php foreach ($classes as $class): ?>
                <option value="<?= htmlspecialchars($class['ID_CLASSE']) ?>">
                    Classe <?= htmlspecialchars($class['ID_CLASSE']) ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">Aucune classe disponible</option>
        <?php endif; ?>
    </select>
</div>

<div id="result"></div>

<script>
    function loadClass(classId) {
        if (!classId) return;

        // 1) Récupérer le XML généré côté serveur
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `generate_students_xml.php?class_id=${classId}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4 || xhr.status !== 200) return;

            const xml = xhr.responseText;

            // 2) Charger le XSLT (une seule fois idéalement, mais OK ici)
            const xsltDoc = new XMLHttpRequest();
            xsltDoc.open('GET', 'students_modules.xslt', false); // synchrone = OK pour un petit fichier
            xsltDoc.send(null);

            const xsltProcessor = new XSLTProcessor();
            xsltProcessor.importStylesheet(xsltDoc.responseXML);

            // 3) Parser le XML et transformer
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xml, 'application/xml');

            // Vérifier que le XML est valide
            if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
                document.getElementById('result').innerHTML =
                    '<div class="alert alert-danger">Erreur : XML invalide reçu du serveur.</div>';
                return;
            }

            const result = xsltProcessor.transformToFragment(xmlDoc, document);
            const container = document.getElementById('result');
            container.innerHTML = '';
            container.appendChild(result);
        };
        xhr.send();
    }

    // Écouteur sur le select
    document.getElementById('classSelect').addEventListener('change', function() {
        loadClass(this.value);
    });

    // Charger automatiquement la première classe au démarrage
    window.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('classSelect');
        if (select.value) loadClass(select.value);
    });
</script>

<?php require 'footer.php'; ?>