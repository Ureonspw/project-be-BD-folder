<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour gérer vos dons de sang.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $user_id = $_SESSION['user_id'];
        $don_id = $_POST['don_id'];
        $action = $_POST['action'];
        
        // Vérifier que le don appartient bien à l'utilisateur connecté
        $stmt = $conn->prepare("SELECT * FROM blood_donations WHERE id = ? AND user_id = ?");
        $stmt->execute([$don_id, $user_id]);
        $don = $stmt->fetch();
        
        if (!$don) {
            throw new Exception("Don de sang non trouvé ou vous n'avez pas le droit de le modifier.");
        }
        
        if ($action === 'annuler') {
            // Annuler le don
            $stmt = $conn->prepare("UPDATE blood_donations SET statut = 'annule' WHERE id = ?");
            $stmt->execute([$don_id]);
            
            echo json_encode(['success' => true, 'message' => 'Votre don de sang a été annulé avec succès.']);
        } elseif ($action === 'reprogrammer') {
            // Récupérer les nouvelles informations
            $nouvelle_date = $_POST['date'] . ' ' . $_POST['heure'] . ':00';
            
            // Mettre à jour le don
            $stmt = $conn->prepare("UPDATE blood_donations SET date_don = ? WHERE id = ?");
            $stmt->execute([$nouvelle_date, $don_id]);
            
            echo json_encode(['success' => true, 'message' => 'Votre don de sang a été reprogrammé avec succès.']);
        } else {
            throw new Exception("Action non reconnue.");
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la gestion du don de sang: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?> 