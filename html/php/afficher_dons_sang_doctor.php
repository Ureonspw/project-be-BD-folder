<?php
session_start();

// Vérifier si l'utilisateur est connecté en tant que docteur
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté en tant que docteur pour accéder à cette page']);
    exit();
}

// Connexion à la base de données
require_once '../../config/database.php';

try {
    // Récupérer tous les dons de sang
    $stmt = $conn->prepare("
        SELECT bd.*, u.nom, u.prenom, u.telephone, u.email
        FROM blood_donations bd
        JOIN users u ON bd.user_id = u.id
        ORDER BY bd.date_don DESC
    ");
    
    $stmt->execute();
    
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
    error_log("Erreur dans afficher_dons_sang_doctor.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des dons de sang: ' . $e->getMessage()
    ]);
}
?> 