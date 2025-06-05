<?php
// view_students_modules.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_timetable";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT ID_CLASSE FROM classes");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étudiants et Modules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Étudiants et Modules</h2>
        <div class="mb-3">
            <label for="classSelect">Sélectionner une classe :</label>
            <select id="classSelect" class="form-select">
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo $class['ID_CLASSE']; ?>">Classe <?php echo $class['ID_CLASSE']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="result"></div>
    </div>

    <script>
        document.getElementById('classSelect').addEventListener('change', function() {
            const classId = this.value;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `generate_students_xml.php?class_id=${classId}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const xml = xhr.responseText;
                    const xsltProcessor = new XSLTProcessor();
                    const xsltDoc = new XMLHttpRequest();
                    xsltDoc.open('GET', 'students_modules.xslt', false);
                    xsltDoc.send(null);
                    xsltProcessor.importStylesheet(xsltDoc.responseXML);
                    const parser = new DOMParser();
                    const xmlDoc = parser.parseFromString(xml, 'application/xml');
                    const result = xsltProcessor.transformToFragment(xmlDoc, document);
                    document.getElementById('result').innerHTML = '';
                    document.getElementById('result').appendChild(result);
                }
            };
            xhr.send();
        });
    </script>
</body>
</html>