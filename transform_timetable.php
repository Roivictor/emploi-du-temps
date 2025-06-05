<?php
// transform_timetable.php
$xml = new DOMDocument();
$xml->load('generate_timetable_xml.php?class_id=1');

$xsl = new DOMDocument();
$xsl->load('timetable.xslt');

$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);

echo $proc->transformToXML($xml);
?>