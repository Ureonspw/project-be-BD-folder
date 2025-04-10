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

try {
    // Récupérer tous les docteurs
    $query = "SELECT id, nom, prenom, specialite, hopital, description, photo FROM doctors ORDER BY specialite, nom";
    $stmt = $conn->prepare($query);
    $stmt->execute();
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