<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour gérer vos rendez-vous'
    ]);
    exit;
}

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$rdv_id = isset($_POST['rdv_id']) ? intval($_POST['rdv_id']) : 0;

// Vérifier si l'action et l'ID du rendez-vous sont valides
if (empty($action) || $rdv_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres invalides'
    ]);
    exit;
}

try {
    // Vérifier si le rendez-vous appartient à l'utilisateur connecté
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$rdv_id, $user_id]);
    $rdv = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$rdv) {
        echo json_encode([
            'success' => false,
            'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à le modifier'
        ]);
        exit;
    }
    
    // Vérifier si le rendez-vous n'est pas déjà annulé ou terminé
    if ($rdv['statut'] === 'annule' || $rdv['statut'] === 'termine') {
        echo json_encode([
            'success' => false,
            'message' => 'Ce rendez-vous ne peut plus être modifié'
        ]);
        exit;
    }
    
    // Traiter l'action demandée
    if ($action === 'annuler') {
        // Annuler le rendez-vous
        $stmt = $conn->prepare("UPDATE appointments SET statut = 'annule' WHERE id = ?");
        $stmt->execute([$rdv_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Votre rendez-vous a été annulé avec succès'
        ]);
    } elseif ($action === 'reprogrammer') {
        // Vérifier si la nouvelle date est fournie
        if (!isset($_POST['date_rdv']) || empty($_POST['date_rdv'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Veuillez spécifier une nouvelle date et heure'
            ]);
            exit;
        }
        
        $new_date_rdv = $_POST['date_rdv'];
        
        // Vérifier si la nouvelle date est valide (pas dans le passé)
        $date_rdv = new DateTime($new_date_rdv);
        $now = new DateTime();
        
        if ($date_rdv < $now) {
            echo json_encode([
                'success' => false,
                'message' => 'La nouvelle date du rendez-vous doit être dans le futur'
            ]);
            exit;
        }
        
        // Vérifier si le nouveau créneau est disponible
        $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND date_rdv = ? AND statut != 'annule' AND id != ?");
        $stmt->execute([$rdv['doctor_id'], $new_date_rdv, $rdv_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ce créneau est déjà réservé, veuillez en choisir un autre'
            ]);
            exit;
        }
        
        // Mettre à jour le rendez-vous
        $stmt = $conn->prepare("UPDATE appointments SET date_rdv = ? WHERE id = ?");
        $stmt->execute([$new_date_rdv, $rdv_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Votre rendez-vous a été reprogrammé avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Action non reconnue'
        ]);
    }
} catch(PDOException $e) {
    error_log("Erreur lors de la gestion du rendez-vous: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue lors de la gestion du rendez-vous'
    ]);
}
?> 