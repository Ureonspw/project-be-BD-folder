<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Code de débogage pour vérifier la session
error_log("Session data: " . print_r($_SESSION, true));

// Vérifier si le docteur est connecté
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté en tant que docteur pour accéder à cette fonctionnalité',
        'debug' => 'Session doctor_id non trouvé'
    ]);
    exit;
}

header('Content-Type: application/json');

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée',
        'debug' => 'Méthode: ' . $_SERVER['REQUEST_METHOD']
    ]);
    exit;
}

// Code de débogage pour vérifier les données POST
error_log("POST data: " . print_r($_POST, true));

// Vérifier les données requises
if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Paramètres manquants',
        'debug' => 'POST data: ' . print_r($_POST, true)
    ]);
    exit;
}

$appointment_id = intval($_POST['appointment_id']);
$status = $_POST['status'];
$doctor_id = $_SESSION['doctor_id'];

// Code de débogage pour vérifier les paramètres
error_log("Appointment ID: " . $appointment_id);
error_log("Status: " . $status);
error_log("Doctor ID: " . $doctor_id);

// Vérifier si le statut est valide
$valid_statuses = ['en_attente', 'confirme', 'annule', 'termine'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Statut invalide',
        'debug' => 'Status: ' . $status . ', Valid statuses: ' . implode(', ', $valid_statuses)
    ]);
    exit;
}

try {
    // Vérifier que le rendez-vous appartient au docteur connecté
    $check_query = "SELECT id FROM appointments WHERE id = :id AND doctor_id = :doctor_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([
        'id' => $appointment_id,
        'doctor_id' => $doctor_id
    ]);
    
    // Code de débogage pour vérifier la requête SQL
    error_log("Check query: " . $check_query);
    error_log("Parameters: " . print_r(['id' => $appointment_id, 'doctor_id' => $doctor_id], true));
    error_log("Row count: " . $check_stmt->rowCount());
    
    if ($check_stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Rendez-vous non trouvé ou vous n\'êtes pas autorisé à le modifier',
            'debug' => 'Appointment ID: ' . $appointment_id . ', Doctor ID: ' . $doctor_id
        ]);
        exit;
    }
    
    // Mettre à jour le statut du rendez-vous
    $update_query = "UPDATE appointments SET statut = :status WHERE id = :id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([
        'id' => $appointment_id,
        'status' => $status
    ]);
    
    // Code de débogage pour vérifier la mise à jour
    error_log("Update query: " . $update_query);
    error_log("Parameters: " . print_r(['id' => $appointment_id, 'status' => $status], true));
    error_log("Rows affected: " . $update_stmt->rowCount());
    
    echo json_encode([
        'success' => true,
        'message' => 'Statut du rendez-vous mis à jour avec succès',
        'debug' => [
            'appointment_id' => $appointment_id,
            'status' => $status,
            'rows_affected' => $update_stmt->rowCount()
        ]
    ]);
} catch (PDOException $e) {
    error_log("Erreur PDO: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du statut du rendez-vous: ' . $e->getMessage()
    ]);
}
?> 