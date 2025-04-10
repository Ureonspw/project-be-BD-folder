<?php
require_once __DIR__ . '/../../config/database.php';

// Vérifier si l'utilisateur est connecté en tant que docteur
session_start();
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Accès non autorisé. Vous devez être connecté en tant que docteur.'
    ]);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
    exit;
}

// Récupérer les données du formulaire
$appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
$diagnostic = isset($_POST['diagnostic']) ? trim($_POST['diagnostic']) : '';
$prescription = isset($_POST['prescription']) ? trim($_POST['prescription']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Vérifier si les données requises sont présentes
if ($appointment_id <= 0 || empty($diagnostic) || empty($prescription)) {
    echo json_encode([
        'success' => false,
        'message' => 'Données incomplètes. Veuillez fournir toutes les informations requises.'
    ]);
    exit;
}

try {
    // Récupérer les informations du rendez-vous
    $appointment_query = "SELECT user_id, date_rdv FROM appointments WHERE id = :appointment_id AND doctor_id = :doctor_id";
    $appointment_stmt = $conn->prepare($appointment_query);
    $appointment_stmt->execute([
        'appointment_id' => $appointment_id,
        'doctor_id' => $_SESSION['doctor_id']
    ]);
    
    if ($appointment_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à y accéder.'
        ]);
        exit;
    }
    
    $appointment = $appointment_stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $appointment['user_id'];
    $date_consultation = $appointment['date_rdv'];
    
    // Vérifier si un dossier médical existe déjà pour ce rendez-vous
    $check_query = "SELECT id FROM medical_records WHERE user_id = :user_id AND doctor_id = :doctor_id AND date_consultation = :date_consultation";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([
        'user_id' => $user_id,
        'doctor_id' => $_SESSION['doctor_id'],
        'date_consultation' => $date_consultation
    ]);
    
    if ($check_stmt->rowCount() > 0) {
        // Mettre à jour le dossier médical existant
        $record = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $update_query = "UPDATE medical_records SET diagnostic = :diagnostic, prescription = :prescription, notes = :notes WHERE id = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([
            'diagnostic' => $diagnostic,
            'prescription' => $prescription,
            'notes' => $notes,
            'id' => $record['id']
        ]);
        
        $message = 'Dossier médical mis à jour avec succès.';
    } else {
        // Créer un nouveau dossier médical
        $insert_query = "INSERT INTO medical_records (user_id, doctor_id, date_consultation, diagnostic, prescription, notes) 
                        VALUES (:user_id, :doctor_id, :date_consultation, :diagnostic, :prescription, :notes)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->execute([
            'user_id' => $user_id,
            'doctor_id' => $_SESSION['doctor_id'],
            'date_consultation' => $date_consultation,
            'diagnostic' => $diagnostic,
            'prescription' => $prescription,
            'notes' => $notes
        ]);
        
        $message = 'Dossier médical créé avec succès.';
    }
    
    // Mettre à jour le statut du rendez-vous à "termine"
    $update_appointment_query = "UPDATE appointments SET statut = 'termine' WHERE id = :appointment_id";
    $update_appointment_stmt = $conn->prepare($update_appointment_query);
    $update_appointment_stmt->execute([
        'appointment_id' => $appointment_id
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement du dossier médical: ' . $e->getMessage()
    ]);
}
?> 