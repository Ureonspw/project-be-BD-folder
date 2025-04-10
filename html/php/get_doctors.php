<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour accéder à cette fonctionnalité'
    ]);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['specialite'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Spécialité non spécifiée'
    ]);
    exit;
}

$specialite = $_GET['specialite'];

try {
    $query = "SELECT id, nom, prenom, hopital FROM doctors WHERE specialite = :specialite";
    $stmt = $conn->prepare($query);
    $stmt->execute(['specialite' => $specialite]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'doctors' => $doctors
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des docteurs: ' . $e->getMessage()
    ]);
}
?> 