<?php
require('../../fpdf/fpdf.php');
session_start();
require_once '../../dataBase/connection.php';


if (!isset($_SESSION['id_etudiant'])) {
    die("error ...");
}

$id = $_SESSION['id_etudiant'];

// jib les info
$query = $dba->prepare("SELECT nom, prenom, date_naissance, id_filiere FROM etudiants WHERE id_etudiant = ?");
$query->execute([$id]);
$student = $query->fetch();

if (!$student) {
    die("cann't find the student");
}


$query_filiere = $dba->prepare("SELECT nom_filiere FROM filieres WHERE id_filiere = ?");
$query_filiere->execute([$student['id_filiere']]);
$filiere = $query_filiere->fetch();

// open new object de type pdf
// open page
$pdf = new FPDF();
$pdf->AddPage();

// logo
$pdf->Image('../../logo.png', 10, 5, 20); 
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(0, 10, 'Attestation de Scolarite', 0, 1, 'C');
$pdf->Ln(10);


$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "Nom : " . $student['nom'], 0, 1);
$pdf->Cell(0, 10, "Prenom : " . $student['prenom'], 0, 1);
$pdf->Cell(0, 10, "Date de naissance : " . $student['date_naissance'], 0, 1);
$pdf->Cell(0, 10, utf8_decode("Inscrit en : " . $filiere['nom_filiere']), 0, 1);
$pdf->Cell(0, 10, "Ecole: Ecole Superieure des Sciences et de l'Innovation", 0, 1);
$pdf->Cell(0, 10, "Adresse: Sale, Maroc", 0, 1);


$pdf->Ln(10);
$pdf->Cell(0, 10, "Telephone : +212 718 356 386", 0, 1);
$pdf->Cell(0, 10, "Fax : (00 212) 123 456 789", 0, 1);
$pdf->Ln(10);

$pdf->Cell(0, 10, "Le Directeur", 0, 1, 'R');
$pdf->Ln(10);
$pdf->Cell(0, 10, "Signature: ______________", 0, 1, 'R');


$pdf->Ln(10);
$pdf->Cell(0, 10, "Fait le: " . date('d/m/Y'), 0, 1);

ob_clean(); 
$pdf->Output('I', 'attestation.pdf'); //fin
?>