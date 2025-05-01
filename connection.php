<?php
$dba = new PDO('mysql:host=localhost;dbname=schooldba', "root","");
$dba->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>