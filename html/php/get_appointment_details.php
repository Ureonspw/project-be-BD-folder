<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Vérifier si le docteur est connecté
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté en tant que docteur pour accéder à cette fonctionnalité'
    ]);
    exit;
}

header('Content-Type: application/json');

// Vérifier si l'ID du rendez-vous est fourni
if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID du rendez-vous manquant'
    ]);
    exit;
}

$appointment_id = intval($_GET['id']);
$doctor_id = $_SESSION['doctor_id'];

try {
    // Récupérer les détails du rendez-vous avec les informations du patient et du docteur
    $query = "SELECT a.*, 
                     u.nom as patient_nom, 
                     u.prenom as patient_prenom, 
                     u.email as patient_email, 
                     u.telephone as patient_telephone,
                     d.nom as doctor_nom,
                     d.prenom as doctor_prenom
              FROM appointments a 
              JOIN users u ON a.user_id = u.id 
              JOIN doctors d ON a.doctor_id = d.id
              WHERE a.id = :id AND a.doctor_id = :doctor_id";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'id' => $appointment_id,
        'doctor_id' => $doctor_id
    ]);
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($appointment) {
        echo json_encode([
            'success' => true,
            'appointment' => $appointment
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à le consulter'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des détails du rendez-vous: ' . $e->getMessage()
    ]);
}
?> 