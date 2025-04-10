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

// Traitement de l'annulation d'un rendez-vous
if (isset($_POST['action']) && $_POST['action'] === 'annuler') {
    if (!isset($_POST['appointment_id']) || empty($_POST['appointment_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de rendez-vous manquant'
        ]);
        exit;
    }

    $appointment_id = intval($_POST['appointment_id']);
    
    try {
        // Vérifier que le rendez-vous appartient à l'utilisateur connecté
        $check_query = "SELECT id FROM appointments WHERE id = :id AND user_id = :user_id";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            'id' => $appointment_id,
            'user_id' => $_SESSION['user_id']
        ]);
        
        if ($check_stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à l\'annuler'
            ]);
            exit;
        }
        
        // Mettre à jour le statut du rendez-vous
        $update_query = "UPDATE appointments SET statut = 'annule' WHERE id = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute(['id' => $appointment_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rendez-vous annulé avec succès'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'annulation du rendez-vous: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Traitement de la création d'un nouveau rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier les données requises
    if (!isset($_POST['doctor_id']) || !isset($_POST['date']) || !isset($_POST['time']) || !isset($_POST['motif'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Toutes les informations sont requises'
        ]);
        exit;
    }
    
    $doctor_id = intval($_POST['doctor_id']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $motif = $_POST['motif'];
    
    // Combiner la date et l'heure
    $date_rdv = $date . ' ' . $time;
    
    try {
        // Vérifier si le docteur existe
        $doctor_query = "SELECT id FROM doctors WHERE id = :id";
        $doctor_stmt = $conn->prepare($doctor_query);
        $doctor_stmt->execute(['id' => $doctor_id]);
        
        if ($doctor_stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Docteur non trouvé'
            ]);
            exit;
        }
        
        // Vérifier si la date est valide (pas dans le passé)
        $date_obj = new DateTime($date_rdv);
        $now = new DateTime();
        
        if ($date_obj < $now) {
            echo json_encode([
                'success' => false,
                'message' => 'La date du rendez-vous doit être dans le futur'
            ]);
            exit;
        }
        
        // Vérifier si le créneau est disponible
        $check_query = "SELECT COUNT(*) FROM appointments WHERE doctor_id = :doctor_id AND date_rdv = :date_rdv AND statut != 'annule'";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            'doctor_id' => $doctor_id,
            'date_rdv' => $date_rdv
        ]);
        
        if ($check_stmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Ce créneau est déjà réservé'
            ]);
            exit;
        }
        
        // Insérer le nouveau rendez-vous
        $insert_query = "INSERT INTO appointments (user_id, doctor_id, date_rdv, motif, statut) 
                        VALUES (:user_id, :doctor_id, :date_rdv, :motif, 'en_attente')";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'doctor_id' => $doctor_id,
            'date_rdv' => $date_rdv,
            'motif' => $motif
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Rendez-vous créé avec succès'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création du rendez-vous: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Si on arrive ici, c'est que la méthode n'est pas POST
echo json_encode([
    'success' => false,
    'message' => 'Méthode non autorisée'
]);
?> 