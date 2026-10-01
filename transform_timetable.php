<?php
// transform_timetable.php
// Génère le HTML de l'emploi du temps en transformant le XML (généré côté serveur) avec XSLT.

header('Content-Type: text/html; charset=utf-8');

// 1) Vérifier que l'extension XSL est disponible
if (!class_exists('XSLTProcessor')) {
    echo "<p style='color:red'>L'extension XSL n'est pas activée dans PHP. Activez 'xsl' dans php.ini.</p>";
    exit;
}

// 2) Récupérer l'ID de classe (par défaut 1)
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 1;

// 3) Construire l'URL absolue du générateur XML (via HTTP)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir      = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$xmlUrl   = "$protocol://$host$dir/generate_timetable_xml.php?class_id=$class_id";

// 4) Charger le XML (avec gestion d'erreur)
$xml = new DOMDocument();
$xml->preserveWhiteSpace = false;
$xml->formatOutput       = true;

$loaded = @$xml->load($xmlUrl);
if (!$loaded) {
    echo "<p style='color:red'>Impossible de charger le XML depuis : " . htmlspecialchars($xmlUrl) . "</p>";
    exit;
}

// 5) Charger le XSLT (chemin local, relatif au script)
$xslPath = __DIR__ . DIRECTORY_SEPARATOR . 'timetable.xslt';
if (!file_exists($xslPath)) {
    echo "<p style='color:red'>Fichier XSLT introuvable : " . htmlspecialchars($xslPath) . "</p>";
    exit;
}

$xsl = new DOMDocument();
if (!$xsl->load($xslPath)) {
    echo "<p style='color:red'>Impossible de charger le XSLT.</p>";
    exit;
}

// 6) Transformer
$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);

$html = $proc->transformToXML($xml);
if ($html === false) {
    echo "<p style='color:red'>Erreur lors de la transformation XSLT.</p>";
    exit;
}

echo $html;