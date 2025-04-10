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

// Vérifier si l'ID du docteur est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du docteur non spécifié'
    ]);
    exit;
}

$doctorId = intval($_GET['id']);

header('Content-Type: application/json');

try {
    // Récupérer les informations détaillées du docteur
    $query = "SELECT id, nom, prenom, specialite, hopital, telephone, email, adresse, description, photo, horaires 
              FROM doctors 
              WHERE id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $doctorId, PDO::PARAM_INT);
    $stmt->execute();
    
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        echo json_encode([
            'success' => true,
            'doctor' => $doctor
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Docteur non trouvé'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des informations du docteur: ' . $e->getMessage()
    ]);
}
?> 