
<?php

$host = 'localhost';
$dbname = 'schooldba';
$username = 'root';
$password = '';


try {
    $dba = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dba->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>

