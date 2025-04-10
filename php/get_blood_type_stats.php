<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour voir les statistiques']);
    exit();
}

// Connexion à la base de données
require_once '../../config/database.php';

try {
    // Récupérer les statistiques des groupes sanguins
    $stmt = $conn->prepare("
        SELECT groupe_sanguin, COUNT(*) as count 
        FROM blood_donations 
        GROUP BY groupe_sanguin
    ");
    
    $stmt->execute();
    
    $bloodTypes = [
        'A+' => 0,
        'A-' => 0,
        'B+' => 0,
        'B-' => 0,
        'AB+' => 0,
        'AB-' => 0,
        'O+' => 0,
        'O-' => 0
    ];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bloodTypes[$row['groupe_sanguin']] = (int)$row['count'];
    }

    // Récupérer les statistiques générales
    $stats = [
        'pendingRequests' => 0,
        'completedDonations' => 0,
        'totalDonations' => 0
    ];
    
    // Compter les demandes en attente
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_donations WHERE statut = 'en_attente'");
    $stmt->execute();
    $stats['pendingRequests'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Compter les dons complétés
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_donations WHERE statut = 'termine'");
    $stmt->execute();
    $stats['completedDonations'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Compter le total des dons
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM blood_donations");
    $stmt->execute();
    $stats['totalDonations'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'success' => true,
        'bloodTypes' => $bloodTypes,
        'stats' => $stats
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
    ]);
}