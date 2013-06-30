<?php
header("Cache-Control: public");
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="DARSD.crx"');
header("Content-Type: text/plain"); 
readfile('DARSD.crx');
?>
