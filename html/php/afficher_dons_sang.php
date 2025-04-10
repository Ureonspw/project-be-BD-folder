<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour voir vos dons de sang']);
    exit();
}

// Connexion à la base de données
require_once '../../config/database.php';

try {
    // Récupérer les dons de sang de l'utilisateur
    $stmt = $conn->prepare("
        SELECT id, groupe_sanguin, lieu, date_don, statut 
        FROM blood_donations 
        WHERE user_id = :user_id 
        ORDER BY date_don DESC
    ");
    
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    
    $dons = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Formater la date et l'heure
        $date = new DateTime($row['date_don']);
        $row['date'] = $date->format('Y-m-d');
        $row['heure'] = $date->format('H:i');
        
        $dons[] = $row;
    }

    echo json_encode([
        'success' => true,
        'dons' => $dons
    ]);
} catch (Exception $e) {
    error_log("Erreur dans afficher_dons_sang.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des dons de sang: ' . $e->getMessage()
    ]);
}
?> 