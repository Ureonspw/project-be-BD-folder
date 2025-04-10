<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

try {
    // Marquer toutes les notifications non lues comme lues
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET `read` = 1 
        WHERE user_id = :user_id 
        AND `read` = 0
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Notifications marquées comme lues'
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour des notifications: ' . $e->getMessage()
    ]);
}
?> 