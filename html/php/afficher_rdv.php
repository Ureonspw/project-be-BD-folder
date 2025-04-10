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
    // Récupérer les rendez-vous de l'utilisateur connecté
    $query = "SELECT a.*, d.nom as doctor_nom, d.prenom as doctor_prenom, d.specialite 
              FROM appointments a 
              JOIN doctors d ON a.doctor_id = d.id 
              WHERE a.user_id = :user_id 
              ORDER BY a.date_rdv DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des rendez-vous: ' . $e->getMessage()
    ]);
}
?> 