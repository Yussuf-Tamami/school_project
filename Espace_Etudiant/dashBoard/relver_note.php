<?php
require('../../fpdf/fpdf.php');
session_start();
require_once '../../dataBase/connection.php';

if (!isset($_SESSION['id_etudiant'])) {
    die("Accès refusé.");
}

$id = $_SESSION['id_etudiant'];


$query = $dba->prepare("SELECT nom, prenom, date_naissance, id_filiere FROM etudiants WHERE id_etudiant = ?");
$query->execute([$id]);
$student = $query->fetch();

if (!$student) {
    die("Étudiant introuvable.");
}


$query_filiere = $dba->prepare("SELECT nom_filiere FROM filieres WHERE id_filiere = ?");
$query_filiere->execute([$student['id_filiere']]);
$filiere = $query_filiere->fetchColumn();


$query_matieres = $dba->prepare("SELECT id_matiere, nom_matiere FROM matieres WHERE id_filiere = ?");
$query_matieres->execute([$student['id_filiere']]);
$matieres = $query_matieres->fetchAll(PDO::FETCH_ASSOC);


$query_notes = $dba->prepare("SELECT id_matiere, num_devoir, note FROM evaluations WHERE id_etudiant = ?");
$query_notes->execute([$id]);
$raw_notes = $query_notes->fetchAll(PDO::FETCH_ASSOC);


$notes = [];
foreach ($raw_notes as $row) {
    $notes[$row['id_matiere']][$row['num_devoir']] = $row['note'];
}


function mention($moy)
{
    if ($moy >= 16)
        return "Très Bien";
    if ($moy >= 14)
        return "Bien";
    if ($moy >= 12)
        return "Assez Bien";
    if ($moy >= 10)
        return "Passable";
    return "Ajourné";
}

// PDF
$pdf = new FPDF();
$pdf->AddPage();


$pdf->Image('../../Images/logo.png', 10, 6, 25);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, utf8_decode("Royaume du Maroc"), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Ministère de l'Enseignement Supérieur"), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Direction Régionale Rabat - Salé - Kénitra"), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("Province de Salé"), 0, 1, 'C');
$pdf->Cell(0, 6, utf8_decode("École Supérieure des Sciences et de l'Innovation"), 0, 1, 'C');

$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Relevé de Notes"), 0, 1, 'C');
$pdf->Ln(4);


$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, "Nom : " . $student['nom'], 0, 1);
$pdf->Cell(0, 8, "Prenom : " . $student['prenom'], 0, 1);
$pdf->Cell(0, 8, "Date de naissance : " . $student['date_naissance'], 0, 1);
$pdf->Cell(0, 8, utf8_decode("Filière : " . $filiere), 0, 1);
$pdf->Ln(5);


$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(50, 8, "Matiere", 1);
$pdf->Cell(20, 8, "Devoir 1", 1);
$pdf->Cell(20, 8, "Devoir 2", 1);
$pdf->Cell(20, 8, "Devoir 3", 1);
$pdf->Cell(30, 8, "Moyenne", 1);
$pdf->Cell(40, 8, "Mention", 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 11);

$total = 0;
$count_valid = 0;

foreach ($matieres as $matiere) {
    $id_matiere = $matiere['id_matiere'];
    $nom_matiere = $matiere['nom_matiere'];
    $dev1 = isset($notes[$id_matiere][1]) ? $notes[$id_matiere][1] : '-';
    $dev2 = isset($notes[$id_matiere][2]) ? $notes[$id_matiere][2] : '-';
    $dev3 = isset($notes[$id_matiere][3]) ? $notes[$id_matiere][3] : '-';


    if (is_numeric($dev1) && is_numeric($dev2) && is_numeric($dev3)) {
        $moy = round(($dev1 + $dev2 + $dev3) / 3, 2);
        $ment = mention($moy);
        $total += $moy;
        $count_valid++;
    } else {
        $moy = '-';
        $ment = '-';
    }

    $pdf->Cell(50, 8, utf8_decode($nom_matiere), 1);
    $pdf->Cell(20, 8, $dev1, 1);
    $pdf->Cell(20, 8, $dev2, 1);
    $pdf->Cell(20, 8, $dev3, 1);
    $pdf->Cell(30, 8, $moy, 1);
    $pdf->Cell(40, 8, utf8_decode($ment), 1);
    $pdf->Ln();
}


if ($count_valid == count($matieres) && $count_valid > 0) {
    $moy_gen = round($total / $count_valid, 2);
    $pdf->Ln(5);
    $pdf->Cell(0, 8, "Moyenne Generale : " . number_format($moy_gen, 2), 0, 1);
    $pdf->Cell(0, 8, "Mention Generale : " . mention($moy_gen), 0, 1);
}

$pdf->Ln(10);
$pdf->Cell(0, 10, "Le Directeur", 0, 1, 'R');
$pdf->Cell(0, 10, "Signature : ____________________", 0, 1, 'R');
$pdf->Ln(5);
$pdf->Cell(0, 10, "Fait le : " . date('d/m/Y'), 0, 1);

ob_clean();
$pdf->Output('I', 'releve_de_notes.pdf');
?>