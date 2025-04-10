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

// Vérifier si l'ID du rendez-vous est fourni
if (!isset($_GET['appointment_id']) || empty($_GET['appointment_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du rendez-vous non fourni.'
    ]);
    exit;
}

$appointment_id = intval($_GET['appointment_id']);

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
    
    // Récupérer le dossier médical
    $record_query = "SELECT * FROM medical_records 
                    WHERE user_id = :user_id 
                    AND doctor_id = :doctor_id 
                    AND date_consultation = :date_consultation";
    $record_stmt = $conn->prepare($record_query);
    $record_stmt->execute([
        'user_id' => $user_id,
        'doctor_id' => $_SESSION['doctor_id'],
        'date_consultation' => $date_consultation
    ]);
    
    if ($record_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => 'Aucun dossier médical trouvé pour ce rendez-vous.'
        ]);
    } else {
        $medical_record = $record_stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'exists' => true,
            'medical_record' => $medical_record
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération du dossier médical: ' . $e->getMessage()
    ]);
}
?> 